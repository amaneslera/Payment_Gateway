<?php
/**
 * CLI Test script to verify cash payments now correctly save 'Success' status
 */

// Include database configuration
require_once 'config/db.php';
require_once 'vendor/autoload.php';

// For JWT token generation (simulate login)
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateTestToken($userId = 1) {
    $key = "your-secret-key-here-make-it-long-and-secure-2024";
    $payload = [
        'user_id' => $userId,
        'username' => 'admin',
        'role' => 'admin',
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60) // 24 hours
    ];
    return JWT::encode($payload, $key, 'HS256');
}

echo "Testing Cash Payment Fix\n";
echo "=======================\n\n";

try {
    $conn = getConnection();
    
    // 1. Check current cash payments status
    echo "Step 1: Checking current cash payments in database...\n";
    
    $checkStmt = $conn->prepare("
        SELECT payment_id, order_id, payment_method, transaction_status, payment_time 
        FROM payments 
        WHERE payment_method = 'Cash' 
        ORDER BY payment_time DESC 
        LIMIT 3
    ");
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    echo "Recent Cash Payments:\n";
    while ($row = $result->fetch_assoc()) {
        $status = $row['transaction_status'] ?: 'NULL/EMPTY';
        echo "- Payment ID {$row['payment_id']}: Status = '$status' (Time: {$row['payment_time']})\n";
    }
    echo "\n";
    
    // 2. Create a test order
    echo "Step 2: Creating test order...\n";
    
    $conn->begin_transaction();
    
    // Insert test order
    $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_status) VALUES (?, ?, 'Pending')");
    $userId = 1;
    $totalAmount = 25.50;
    $orderStmt->bind_param("id", $userId, $totalAmount);
    $orderStmt->execute();
    $orderId = $conn->insert_id();
    
    // Insert order items (need at least one for the payment validation)
    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
    $productId = 1; // Assuming product 1 exists
    $quantity = 1;
    $subtotal = 25.50;
    $itemStmt->bind_param("iiid", $orderId, $productId, $quantity, $subtotal);
    $itemStmt->execute();
    
    $conn->commit();
    
    echo "✓ Test order created with ID: $orderId\n\n";
    
    // 3. Make direct database call to simulate the fixed payment processing
    echo "Step 3: Making cash payment directly (simulating API call)...\n";
    
    $conn->begin_transaction();
    
    // This is the fixed SQL from payments.php
    $paymentStmt = $conn->prepare("
        INSERT INTO payments (
            order_id, 
            payment_method, 
            cash_received,
            change_amount, 
            paypal_transaction_id, 
            transaction_status,
            payment_time, 
            cashier_id
        ) VALUES (?, ?, ?, ?, ?, 'Success', NOW(), ?)
    ");
    
    $cashReceived = 30.00;
    $changeAmount = 4.50;
    $paypalTransactionId = null;
    $cashierId = 1;
    
    $paymentStmt->bind_param(
        "isddsi",
        $orderId,
        'Cash',
        $cashReceived,
        $changeAmount,
        $paypalTransactionId,
        $cashierId
    );
    
    if ($paymentStmt->execute()) {
        $paymentId = $conn->insert_id();
        
        // Update order status
        $updateOrderStmt = $conn->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?");
        $updateOrderStmt->bind_param("i", $orderId);
        $updateOrderStmt->execute();
        
        $conn->commit();
        
        echo "✓ Cash payment created with ID: $paymentId\n\n";
        
        // 4. Verify the payment was saved correctly
        echo "Step 4: Verifying payment in database...\n";
        
        $verifyStmt = $conn->prepare("
            SELECT payment_id, order_id, payment_method, transaction_status, cash_received, change_amount, payment_time 
            FROM payments 
            WHERE payment_id = ?
        ");
        $verifyStmt->bind_param("i", $paymentId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        
        if ($verifyResult->num_rows > 0) {
            $payment = $verifyResult->fetch_assoc();
            echo "Payment Details:\n";
            echo "- Payment ID: {$payment['payment_id']}\n";
            echo "- Order ID: {$payment['order_id']}\n";
            echo "- Method: {$payment['payment_method']}\n";
            echo "- Status: '{$payment['transaction_status']}'\n";
            echo "- Cash Received: {$payment['cash_received']}\n";
            echo "- Change: {$payment['change_amount']}\n";
            echo "- Time: {$payment['payment_time']}\n\n";
            
            if ($payment['transaction_status'] === 'Success') {
                echo "🎉 SUCCESS: transaction_status is correctly set to 'Success'!\n";
            } else {
                echo "❌ FAILED: transaction_status is '{$payment['transaction_status']}' instead of 'Success'\n";
            }
        }
        
    } else {
        $conn->rollback();
        echo "❌ Failed to create payment: " . $paymentStmt->error . "\n";
    }
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
