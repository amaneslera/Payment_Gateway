<?php
/**
 * Simple Transaction Count Debug
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Transaction Count Debug - " . date('Y-m-d H:i:s') . "</h2>";

try {
    // Direct database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=pos_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✓ Database connection successful</p>";
    
    $today = '2025-05-27';  // Current date
    $yesterday = '2025-05-26'; // Yesterday when you said you had transactions
    
    echo "<h3>Testing Different Dates:</h3>";
    echo "<p><strong>Today (System):</strong> $today</p>";
    echo "<p><strong>Yesterday:</strong> $yesterday</p>";
    
    // Check payments for both dates
    foreach ([$today, $yesterday] as $testDate) {
        echo "<h4>Payments for $testDate:</h4>";
        
        $stmt = $pdo->prepare("
            SELECT 
                p.payment_id,
                p.order_id,
                p.payment_method,
                p.transaction_status,
                p.payment_time,
                o.total_amount,
                o.payment_status as order_status
            FROM payments p
            JOIN orders o ON p.order_id = o.order_id
            WHERE DATE(p.payment_time) = ?
            ORDER BY p.payment_time DESC
        ");
        $stmt->execute([$testDate]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($payments) {
            echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
            echo "<tr>
                    <th>Payment ID</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Time</th>
                  </tr>";
            
            foreach ($payments as $payment) {
                echo "<tr>";
                echo "<td>" . $payment['payment_id'] . "</td>";
                echo "<td>$" . number_format($payment['total_amount'], 2) . "</td>";
                echo "<td>" . ($payment['transaction_status'] ?: 'EMPTY') . "</td>";
                echo "<td>" . $payment['order_status'] . "</td>";
                echo "<td>" . $payment['payment_time'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Count for this date
            $count = count($payments);
            echo "<p><strong>Total transactions for $testDate: $count</strong></p>";
        } else {
            echo "<p><em>No transactions found for $testDate</em></p>";
        }
    }
    
    echo "<h3>Dashboard Query Test:</h3>";
    
    // Test dashboard query for both dates
    foreach ([$today, $yesterday] as $testDate) {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT p.payment_id) as transaction_count,
                COALESCE(SUM(o.total_amount), 0) as total_sales
            FROM orders o 
            JOIN payments p ON o.order_id = p.order_id
            WHERE DATE(p.payment_time) = ?
        ");
        $stmt->execute([$testDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Dashboard query for $testDate:</strong> " . $result['transaction_count'] . " transactions, $" . number_format($result['total_sales'], 2) . " sales</p>";
    }
    
    echo "<h3>Recent 10 Payments (All Dates):</h3>";
    $stmt = $pdo->query("
        SELECT 
            p.payment_id,
            p.order_id,
            p.payment_time,
            DATE(p.payment_time) as date_only,
            o.total_amount,
            p.transaction_status,
            o.payment_status
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        ORDER BY p.payment_time DESC
        LIMIT 10
    ");
    $recentPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr>
            <th>Payment ID</th>
            <th>Date</th>
            <th>Time</th>
            <th>Amount</th>
            <th>Payment Status</th>
            <th>Order Status</th>
          </tr>";
    
    foreach ($recentPayments as $payment) {
        $highlight = ($payment['date_only'] === $today || $payment['date_only'] === $yesterday) ? 'background-color: yellow;' : '';
        echo "<tr style='$highlight'>";
        echo "<td>" . $payment['payment_id'] . "</td>";
        echo "<td>" . $payment['date_only'] . "</td>";
        echo "<td>" . date('H:i:s', strtotime($payment['payment_time'])) . "</td>";
        echo "<td>$" . number_format($payment['total_amount'], 2) . "</td>";
        echo "<td>" . ($payment['transaction_status'] ?: 'EMPTY') . "</td>";
        echo "<td>" . $payment['payment_status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
