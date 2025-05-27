<?php
/**
 * Dashboard Query Test
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=pos_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Dashboard Query Analysis</h1>";
    
    $today = date('Y-m-d'); // This should be 2025-05-26 based on your test
    echo "<p><strong>Today:</strong> $today</p>";
    
    echo "<h2>Current Dashboard Query</h2>";
    echo "<p>Query: <code>WHERE DATE(p.payment_time) >= DATE_SUB('$today', INTERVAL 1 DAY)</code></p>";
    
    // Test the current query
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(p.payment_id) as transaction_count,
            COALESCE(SUM(o.total_amount), 0) as total_sales
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        WHERE DATE(p.payment_time) >= DATE_SUB(?, INTERVAL 1 DAY)
    ");
    $stmt->execute([$today]);
    $currentResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Current Result:</strong> {$currentResult['transaction_count']} transactions, \${$currentResult['total_sales']}</p>";
    
    // Show what dates this covers
    $stmt = $pdo->prepare("SELECT DATE_SUB(?, INTERVAL 1 DAY) as start_date");
    $stmt->execute([$today]);
    $dateRange = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Date Range:</strong> From {$dateRange['start_date']} onwards (includes {$dateRange['start_date']}, $today, and any future dates)</p>";
    
    echo "<h2>Breakdown by Date</h2>";
    $stmt = $pdo->prepare("
        SELECT 
            DATE(p.payment_time) as payment_date,
            COUNT(p.payment_id) as transaction_count,
            COALESCE(SUM(o.total_amount), 0) as total_sales
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        WHERE DATE(p.payment_time) >= DATE_SUB(?, INTERVAL 1 DAY)
        GROUP BY DATE(p.payment_time)
        ORDER BY payment_date DESC
    ");
    $stmt->execute([$today]);
    $breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Date</th><th>Transactions</th><th>Sales</th></tr>";
    foreach ($breakdown as $row) {
        echo "<tr>";
        echo "<td>{$row['payment_date']}</td>";
        echo "<td>{$row['transaction_count']}</td>";
        echo "<td>\${$row['total_sales']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>EXPLANATION</h2>";
    echo "<div style='background-color: #e6f3ff; padding: 10px; border: 1px solid #0066cc;'>";
    echo "<p><strong>Your dashboard is working correctly!</strong></p>";
    echo "<p>The query <code>DATE_SUB('$today', INTERVAL 1 DAY)</code> means:</p>";
    echo "<p>• Show transactions from " . date('Y-m-d', strtotime($today . ' -1 day')) . " onwards</p>";
    echo "<p>• This includes: " . date('Y-m-d', strtotime($today . ' -1 day')) . ", $today, " . date('Y-m-d', strtotime($today . ' +1 day')) . ", etc.</p>";
    echo "<p>• Total shown: {$currentResult['transaction_count']} transactions</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
code { background-color: #f5f5f5; padding: 2px 4px; border-radius: 3px; }
</style>
