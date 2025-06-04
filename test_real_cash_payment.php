<?php
echo "=== Cash Payment Real Order Test ===\n";

try {
    require_once 'config/db.php';
    echo "✅ Database connected\n";
    
    // Get a real order ID
    $stmt = $pdo->query("SELECT order_id FROM orders ORDER BY order_id DESC LIMIT 1");
    $latest_order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$latest_order) {
        echo "❌ No orders found in database\n";
        exit;
    }
    
    $real_order_id = $latest_order['order_id'];
    echo "Using real order ID: $real_order_id\n";
    
    // Test the exact backend logic with a real order
    echo "\n=== Backend Logic Test ===\n";
    $data = [
        'payment_method' => 'Cash',
        'transaction_status' => 'Success'  // What frontend sends
    ];
    
    // Exact logic from payments.php line 216
    $status = $data['payment_method'] === 'Cash' ? 'Success' : 
             ($data['payment_method'] === 'PayPal' && isset($data['status']) ? $data['status'] : 'Pending');
    
    echo "Status calculated: '$status'\n";
    echo "Status length: " . strlen($status) . "\n";
    
    // Test the EXACT parameter binding from payments.php
    echo "\n=== Exact Parameter Binding Test ===\n";
    
    $stmt = $pdo->prepare("
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
    
    // Exact same variables as in payments.php
    $order_id = $real_order_id;
    $payment_method = 'Cash';
    $cash_received = 100.00;
    $change_amount = 0.00;
    $paypal_transaction_id = null;
    $transaction_status = $status; // This should be 'Success'
    $cashier_id = 101;
    
    echo "Before binding:\n";
    echo "  transaction_status value: '$transaction_status'\n";
    echo "  transaction_status type: " . gettype($transaction_status) . "\n";
    echo "  transaction_status length: " . strlen($transaction_status) . "\n";
    
    // Use PDO instead of mysqli to see if that's the issue
    $pdo_result = $stmt->execute([
        $order_id,
        $payment_method,
        $cash_received,
        $change_amount,
        $paypal_transaction_id,
        $transaction_status,
        $cashier_id
    ]);
    
    if ($pdo_result) {
        $payment_id = $pdo->lastInsertId();
        echo "✅ PDO INSERT successful - Payment ID: $payment_id\n";
        
        // Check what was inserted
        $check = $pdo->prepare("SELECT payment_id, payment_method, transaction_status, CHAR_LENGTH(transaction_status) as status_length FROM payments WHERE payment_id = ?");
        $check->execute([$payment_id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        
        echo "\nActual inserted data:\n";
        echo "  payment_method: '" . $row['payment_method'] . "'\n";
        echo "  transaction_status: '" . $row['transaction_status'] . "'\n";
        echo "  status_length: " . $row['status_length'] . "\n";
        echo "  is_empty: " . ($row['transaction_status'] === '' ? 'YES' : 'NO') . "\n";
        echo "  is_null: " . ($row['transaction_status'] === null ? 'YES' : 'NO') . "\n";
        
        // Clean up
        $pdo->prepare("DELETE FROM payments WHERE payment_id = ?")->execute([$payment_id]);
        echo "\n🧹 Test data cleaned\n";
        
    } else {
        echo "❌ PDO INSERT failed\n";
        print_r($stmt->errorInfo());
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "=== End Test ===\n";
?>
