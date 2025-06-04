<?php
require_once 'config/db.php';

echo "=== Cash Payment Debug Test ===\n";

// Test the exact parameter binding that's failing
try {
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
    
    // Test data
    $order_id = 999;
    $payment_method = 'Cash';
    $cash_received = 100.00;
    $change_amount = 10.00;
    $paypal_transaction_id = null;
    $transaction_status = 'Success';
    $cashier_id = 1;
    
    echo "Binding parameters:\n";
    echo "order_id: $order_id (integer)\n";
    echo "payment_method: $payment_method (string)\n";
    echo "cash_received: $cash_received (double)\n";
    echo "change_amount: $change_amount (double)\n";
    echo "paypal_transaction_id: " . ($paypal_transaction_id ?? 'NULL') . " (string)\n";
    echo "transaction_status: $transaction_status (string)\n";
    echo "cashier_id: $cashier_id (integer)\n";
    
    $result = $stmt->bind_param(
        "isddssi",
        $order_id,
        $payment_method,
        $cash_received,
        $change_amount,
        $paypal_transaction_id,
        $transaction_status,
        $cashier_id
    );
    
    if (!$result) {
        echo "bind_param FAILED: " . $stmt->error . "\n";
        exit;
    }
    
    echo "bind_param SUCCESS\n";
    
    if ($stmt->execute()) {
        echo "INSERT SUCCESS - Payment ID: " . $conn->insert_id . "\n";
        
        // Check what was actually inserted
        $check_stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $payment_id = $conn->insert_id;
        $check_stmt->bind_param("i", $payment_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        echo "Inserted data:\n";
        print_r($row);
        
    } else {
        echo "INSERT FAILED: " . $stmt->error . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== End Debug Test ===\n";
?>
