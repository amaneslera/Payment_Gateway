<?php
echo "=== Simple Cash Payment Debug ===\n";

try {
    require_once 'config/db.php';
    echo "✅ Database connected\n";
    
    // Test what the backend logic produces
    $data = [
        'payment_method' => 'Cash',
        'transaction_status' => 'Success'  // What frontend sends
    ];
    
    // This is the exact logic from payments.php line 216
    $status = $data['payment_method'] === 'Cash' ? 'Success' : 
             ($data['payment_method'] === 'PayPal' && isset($data['status']) ? $data['status'] : 'Pending');
    
    echo "Frontend sends transaction_status: " . $data['transaction_status'] . "\n";
    echo "Backend calculates status: " . $status . "\n";
    
    // Test if the status variable is actually being set to empty string somewhere
    echo "Status variable type: " . gettype($status) . "\n";
    echo "Status variable length: " . strlen($status) . "\n";
    echo "Status variable === 'Success': " . ($status === 'Success' ? 'true' : 'false') . "\n";
    echo "Status variable === '': " . ($status === '' ? 'true' : 'false') . "\n";
    
    // Test direct insert to see if parameter binding is the issue
    echo "\n=== Direct Insert Test ===\n";
    $direct_stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, transaction_status, cashier_id) VALUES (?, ?, ?, ?)");
    $result = $direct_stmt->execute([9999, 'Cash', 'Success', 101]);
    
    if ($result) {
        $payment_id = $pdo->lastInsertId();
        echo "✅ Direct insert successful - Payment ID: $payment_id\n";
        
        // Check what was inserted
        $check = $pdo->prepare("SELECT payment_id, payment_method, transaction_status FROM payments WHERE payment_id = ?");
        $check->execute([$payment_id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        
        echo "Inserted payment_method: '" . $row['payment_method'] . "'\n";
        echo "Inserted transaction_status: '" . $row['transaction_status'] . "'\n";
        echo "Status is empty: " . ($row['transaction_status'] === '' ? 'YES' : 'NO') . "\n";
        
        // Clean up
        $pdo->prepare("DELETE FROM payments WHERE payment_id = ?")->execute([$payment_id]);
        echo "🧹 Test data cleaned\n";
    } else {
        echo "❌ Direct insert failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "=== End Debug ===\n";
?>
