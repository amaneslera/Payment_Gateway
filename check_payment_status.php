<?php
/**
 * Check Payment Status Distribution
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Direct database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=pos_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Payment Status Distribution</h2>";
    
    // Check status distribution
    $stmt = $pdo->query("
        SELECT payment_method, transaction_status, COUNT(*) as count 
        FROM payments 
        GROUP BY payment_method, transaction_status 
        ORDER BY payment_method, transaction_status
    ");
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Payment Method</th><th>Transaction Status</th><th>Count</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['payment_method']}</td>";
        echo "<td>{$row['transaction_status']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>Recent 10 Payments</h3>";
    $stmt = $pdo->query("
        SELECT payment_id, payment_method, transaction_status, cash_received, payment_time
        FROM payments 
        ORDER BY payment_time DESC 
        LIMIT 10
    ");
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Payment ID</th><th>Method</th><th>Status</th><th>Cash Received</th><th>Time</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['payment_id']}</td>";
        echo "<td>{$row['payment_method']}</td>";
        echo "<td>{$row['transaction_status']}</td>";
        echo "<td>{$row['cash_received']}</td>";
        echo "<td>{$row['payment_time']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
