<?php
/**
 * Simple database test to verify the payment table structure and test our fix
 */

echo "Simple Database Test for Cash Payment Fix\n";
echo "==========================================\n\n";

// Database connection settings
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'pos_system';

try {
    // Connect to database
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✓ Connected to database successfully\n\n";
    
    // 1. Check current cash payments
    echo "Step 1: Checking current cash payments...\n";
    
    $result = $conn->query("
        SELECT payment_id, order_id, payment_method, transaction_status, payment_time 
        FROM payments 
        WHERE payment_method = 'Cash' 
        ORDER BY payment_time DESC 
        LIMIT 5
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "Recent Cash Payments:\n";
        while ($row = $result->fetch_assoc()) {
            $status = $row['transaction_status'] ?: 'NULL/EMPTY';
            echo "- Payment ID {$row['payment_id']}: Status = '$status'\n";
        }
    } else {
        echo "No cash payments found.\n";
    }
    echo "\n";
    
    // 2. Test the fix by inserting a cash payment directly
    echo "Step 2: Testing the fixed SQL statement...\n";
    
    $conn->begin_transaction();
    
    // First create a test order if needed
    $orderResult = $conn->query("SELECT order_id FROM orders ORDER BY order_id DESC LIMIT 1");
    if ($orderResult && $orderResult->num_rows > 0) {
        $lastOrder = $orderResult->fetch_assoc();
        $testOrderId = $lastOrder['order_id'];
        echo "Using existing order ID: $testOrderId\n";
    } else {
        // Create a test order
        $conn->query("INSERT INTO orders (user_id, total_amount, payment_status) VALUES (1, 25.50, 'Pending')");
        $testOrderId = $conn->insert_id;
        echo "Created test order ID: $testOrderId\n";
    }
    
    // Now test the fixed payment insertion
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
        ) VALUES (?, ?, ?, ?, ?, 'Success', NOW(), ?)
    ");
    
    $cashReceived = 30.00;
    $changeAmount = 4.50;
    $paypalTransactionId = null;
    $cashierId = 1;
    
    $stmt->bind_param(
        "isddsi",
        $testOrderId,
        'Cash',
        $cashReceived,
        $changeAmount,
        $paypalTransactionId,
        $cashierId
    );
    
    if ($stmt->execute()) {
        $newPaymentId = $conn->insert_id;
        $conn->commit();
        
        echo "✓ Test payment created with ID: $newPaymentId\n\n";
        
        // 3. Verify the new payment
        echo "Step 3: Verifying the new payment...\n";
        
        $verifyStmt = $conn->prepare("
            SELECT payment_id, order_id, payment_method, transaction_status, cash_received, change_amount 
            FROM payments 
            WHERE payment_id = ?
        ");
        $verifyStmt->bind_param("i", $newPaymentId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        
        if ($verifyResult && $verifyResult->num_rows > 0) {
            $payment = $verifyResult->fetch_assoc();
            echo "New Payment Details:\n";
            echo "- Payment ID: {$payment['payment_id']}\n";
            echo "- Order ID: {$payment['order_id']}\n";
            echo "- Method: {$payment['payment_method']}\n";
            echo "- Status: '{$payment['transaction_status']}'\n";
            echo "- Cash Received: {$payment['cash_received']}\n";
            echo "- Change: {$payment['change_amount']}\n\n";
            
            if ($payment['transaction_status'] === 'Success') {
                echo "🎉 SUCCESS: The fix works! Transaction status is correctly set to 'Success'\n";
            } else {
                echo "❌ FAILED: Transaction status is '{$payment['transaction_status']}' instead of 'Success'\n";
            }
        } else {
            echo "❌ Could not retrieve the new payment\n";
        }
        
    } else {
        $conn->rollback();
        echo "❌ Failed to create test payment: " . $stmt->error . "\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
