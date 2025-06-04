<?php
/**
 * Test Real Cash Payment Flow
 * Simulate the exact flow from frontend to database
 */

require_once 'src/config/db.php';

echo "=== Real Cash Payment Flow Test ===\n";

// 1. Create a test order first (like the frontend does)
echo "1. Creating test order...\n";

$pdo = new PDO("mysql:host=127.0.0.1;dbname=pos_system;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create order (using PDO like the order creation API)
$orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, payment_status) VALUES (?, ?, 'Pending')");
$orderStmt->execute([101, 75.00]);
$orderId = $pdo->lastInsertId();
echo "Created order ID: $orderId\n";

// 2. Now test the exact payment flow
echo "\n2. Processing payment using exact payments.php logic...\n";

// Simulate the exact JSON data the frontend sends
$frontendData = [
    'order_id' => $orderId,
    'payment_method' => 'Cash',
    'cash_received' => 100.00,
    'transaction_status' => 'Success'  // Frontend sends this but it's ignored!
];

echo "Frontend data:\n";
print_r($frontendData);

// Get MySQLi connection (like payments.php does)
$conn = getConnection();

try {
    $conn->autocommit(false);
    
    // Get order amount (exact logic from payments.php)
    $orderStmt = $conn->prepare("SELECT total_amount FROM orders WHERE order_id = ?");
    $orderStmt->bind_param("i", $frontendData['order_id']);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    
    if ($orderResult->num_rows === 0) {
        throw new Exception("Order not found");
    }
    
    $orderData = $orderResult->fetch_assoc();
    $totalAmount = $orderData['total_amount'];
    
    // Calculate cash amounts (exact logic from payments.php)
    $cashReceived = null;
    $changeAmount = null;
    
    if ($frontendData['payment_method'] === 'Cash') {
        $cashReceived = $frontendData['cash_received'];
        
        if ($cashReceived < $totalAmount) {
            throw new Exception("Cash received is less than total amount");
        }
        
        $changeAmount = $cashReceived - $totalAmount;
    }
    
    // Create the payment record (EXACT SQL from payments.php line 202-214)
    $stmt = $conn->prepare("
        INSERT INTO payments (
            order_id, 
            payment_method, 
            cash_received,
            change_amount, 
            paypal_transaction_id, 
            transaction_status,
            payment_time, 
            cashier_id
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    
    // Calculate status (EXACT logic from payments.php line 216)
    $status = $frontendData['payment_method'] === 'Cash' ? 'Success' : 
             ($frontendData['payment_method'] === 'PayPal' && isset($frontendData['status']) ? $frontendData['status'] : 'Pending');
    
    echo "\nBackend calculations:\n";
    echo "  totalAmount: $totalAmount\n";
    echo "  cashReceived: $cashReceived\n";
    echo "  changeAmount: $changeAmount\n";
    echo "  status: '$status'\n";
    
    // Mock user data
    $userId = 101;
    $paypalTransactionId = null;
    
    echo "\nParameter binding:\n";
    echo "  1. order_id: {$frontendData['order_id']} (integer)\n";
    echo "  2. payment_method: '{$frontendData['payment_method']}' (string)\n";
    echo "  3. cash_received: $cashReceived (double)\n";
    echo "  4. change_amount: $changeAmount (double)\n";
    echo "  5. paypal_transaction_id: " . ($paypalTransactionId ?? 'NULL') . " (string/null)\n";
    echo "  6. transaction_status: '$status' (string)\n";
    echo "  7. cashier_id: $userId (integer)\n";
    
    // EXACT bind_param call from payments.php line 230-238
    $result = $stmt->bind_param(
        "isddssi",
        $frontendData['order_id'],
        $frontendData['payment_method'],
        $cashReceived,
        $changeAmount,
        $paypalTransactionId,
        $status,
        $userId
    );
    
    if (!$result) {
        throw new Exception("bind_param failed: " . $stmt->error);
    }
    
    echo "\n✅ bind_param successful\n";
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $paymentId = $conn->insert_id;
    echo "✅ Payment inserted with ID: $paymentId\n";
    
    // Update order status (exact logic from payments.php)
    $updateOrderStmt = $conn->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?");
    $updateOrderStmt->bind_param("i", $frontendData['order_id']);
    
    if (!$updateOrderStmt->execute()) {
        throw new Exception("Failed to update order status: " . $updateOrderStmt->error);
    }
    
    $conn->commit();
    echo "✅ Transaction committed\n";
    
    // Check what was actually stored
    echo "\n3. Checking stored data:\n";
    $checkStmt = $conn->prepare("
        SELECT payment_id, payment_method, transaction_status, 
               CHAR_LENGTH(transaction_status) as status_length,
               cash_received, change_amount
        FROM payments 
        WHERE payment_id = ?
    ");
    $checkStmt->bind_param("i", $paymentId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $storedData = $result->fetch_assoc();
    
    echo "Stored payment data:\n";
    print_r($storedData);
    
    if ($storedData['transaction_status'] === '') {
        echo "🚨 PROBLEM: transaction_status is EMPTY STRING!\n";
    } elseif ($storedData['transaction_status'] === null) {
        echo "🚨 PROBLEM: transaction_status is NULL!\n";
    } elseif ($storedData['transaction_status'] === 'Success') {
        echo "✅ transaction_status correctly stored as 'Success'\n";
    } else {
        echo "❓ transaction_status has unexpected value: '{$storedData['transaction_status']}'\n";
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Cleanup
echo "\n4. Cleaning up test data...\n";
try {
    $pdo->prepare("DELETE FROM payments WHERE order_id = ?")->execute([$orderId]);
    $pdo->prepare("DELETE FROM orders WHERE order_id = ?")->execute([$orderId]);
    echo "✅ Test data cleaned\n";
} catch (Exception $e) {
    echo "⚠️ Cleanup warning: " . $e->getMessage() . "\n";
}

echo "\n=== End Real Cash Flow Test ===\n";
?>
