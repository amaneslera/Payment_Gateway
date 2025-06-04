<?php
$host = '127.0.0.1';
$dbname = 'pos_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n\n";
    
    // Check recent payments
    $stmt = $pdo->query("
        SELECT payment_id, order_id, payment_method, transaction_status, payment_time 
        FROM payments 
        ORDER BY payment_id DESC 
        LIMIT 5
    ");
    
    echo "Recent 5 payments:\n";
    echo "ID\tOrder\tMethod\tStatus\t\tTime\n";
    echo "--------------------------------------------------------\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['payment_id'] . "\t" . 
             $row['order_id'] . "\t" . 
             $row['payment_method'] . "\t" . 
             $row['transaction_status'] . "\t\t" . 
             $row['payment_time'] . "\n";
    }
    
    // Try to insert a test cash payment
    echo "\nTesting cash payment insert...\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO payments (
            order_id, 
            payment_method, 
            transaction_status,
            cash_received,
            change_amount, 
            payment_time, 
            cashier_id
        ) VALUES (74, 'Cash', 'Success', 50.00, 25.00, NOW(), 114)
    ");
    
    if ($stmt->execute()) {
        $paymentId = $pdo->lastInsertId();
        echo "✓ Cash payment inserted successfully with ID: $paymentId\n";
        
        // Verify
        $verifyStmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $verifyStmt->execute([$paymentId]);
        $payment = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Status: " . $payment['transaction_status'] . "\n";
        echo "Method: " . $payment['payment_method'] . "\n";
        echo "Amount: " . $payment['cash_received'] . "\n";
        
    } else {
        echo "✗ Failed to insert cash payment\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
