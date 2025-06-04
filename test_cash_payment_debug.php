<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting cash payment debug test...\n";

// Test database connection and cash payment status
require_once 'config/db.php';

echo "Required config files loaded.\n";

try {
    $conn = getConnection();
    echo "Database connection successful!\n";
    
    // Check the most recent cash payments to see their status
    $stmt = $conn->prepare("
        SELECT payment_id, order_id, payment_method, transaction_status, cash_received, change_amount, payment_time
        FROM payments 
        WHERE payment_method = 'Cash'
        ORDER BY payment_time DESC 
        LIMIT 10
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "\nRecent Cash Payments:\n";
    echo "ID | Order | Method | Status | Cash | Change | Time\n";
    echo "---|-------|--------|--------|------|--------|-----\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%d | %d | %s | %s | %.2f | %.2f | %s\n",
            $row['payment_id'],
            $row['order_id'], 
            $row['payment_method'],
            $row['transaction_status'],
            $row['cash_received'] ?? 0,
            $row['change_amount'] ?? 0,
            $row['payment_time']
        );
    }
    
    // Test the table structure
    echo "\nPayments table structure:\n";
    $result = $conn->query("DESCRIBE payments");
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Default'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
