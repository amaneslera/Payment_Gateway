<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting cash payment test...\n";

// Test config loading
echo "1. Loading config...\n";
$configPath = __DIR__ . '/src/config/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
    echo "✓ Config loaded from: $configPath\n";
    echo "  DB_HOST: " . DB_HOST . "\n";
    echo "  DB_NAME: " . DB_NAME . "\n";
    echo "  DB_USER: " . DB_USER . "\n";
} else {
    echo "✗ Config file not found at: $configPath\n";
    exit;
}

// Test direct database connection
echo "\n2. Testing database connection...\n";
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✓ Direct MySQLi connection successful\n";
    
    // Check if we can query payments table
    $result = $conn->query("SELECT COUNT(*) as count FROM payments");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✓ Payments table accessible, current count: " . $row['count'] . "\n";
    } else {
        echo "✗ Cannot query payments table: " . $conn->error . "\n";
    }
    
    // Test simple insert
    echo "\n3. Testing cash payment insert...\n";
    
    $testOrderId = 74;
    $testUserId = 114;
    $cashReceived = 50.00;
    $changeAmount = 25.00;
    
    $stmt = $conn->prepare("
        INSERT INTO payments (
            order_id, 
            payment_method, 
            transaction_status,
            cash_received,
            change_amount, 
            payment_time, 
            cashier_id
        ) VALUES (?, 'Cash', 'Success', ?, ?, NOW(), ?)
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iddi", $testOrderId, $cashReceived, $changeAmount, $testUserId);
    
    if ($stmt->execute()) {
        $paymentId = $conn->insert_id;
        echo "✓ Cash payment inserted with ID: $paymentId\n";
        
        // Verify
        $verifyStmt = $conn->prepare("SELECT transaction_status, payment_method FROM payments WHERE payment_id = ?");
        $verifyStmt->bind_param("i", $paymentId);
        $verifyStmt->execute();
        $result = $verifyStmt->get_result();
        $payment = $result->fetch_assoc();
        
        echo "✓ Verification: Status = '" . $payment['transaction_status'] . "', Method = '" . $payment['payment_method'] . "'\n";
        
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
?>
