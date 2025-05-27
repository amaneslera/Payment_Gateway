<?php
/**
 * FINAL TRANSACTION COUNT DIAGNOSTIC
 * This will show exactly what's happening with your transaction counts
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔍 FINAL TRANSACTION COUNT DIAGNOSTIC</h2>";
echo "<p><strong>Current System Date:</strong> " . date('Y-m-d H:i:s') . "</p>";

try {
    // Direct database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=pos_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Database connection successful</p>";
    
    echo "<h3>📊 RAW DATA ANALYSIS</h3>";
    
    // 1. Count ALL payments in database
    $stmt = $pdo->query("SELECT COUNT(*) as total_payments FROM payments");
    $totalPayments = $stmt->fetch(PDO::FETCH_ASSOC)['total_payments'];
    echo "<p><strong>Total payments in database:</strong> $totalPayments</p>";
    
    // 2. Count payments by date (all dates)
    echo "<h4>Payments by Date:</h4>";
    $stmt = $pdo->query("
        SELECT 
            DATE(payment_time) as payment_date,
            COUNT(*) as payment_count
        FROM payments 
        GROUP BY DATE(payment_time)
        ORDER BY payment_date DESC
        LIMIT 10
    ");
    $paymentsByDate = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Date</th><th>Payment Count</th></tr>";
    foreach ($paymentsByDate as $row) {
        $highlight = ($row['payment_date'] === '2025-05-26') ? 'background-color: yellow;' : '';
        echo "<tr style='$highlight'><td>" . $row['payment_date'] . "</td><td><strong>" . $row['payment_count'] . "</strong></td></tr>";
    }
    echo "</table>";
    echo "<p><em>Yellow = May 26, 2025 (your transaction date)</em></p>";
    
    // 3. Show actual payments for May 26, 2025
    echo "<h4>May 26, 2025 Payments Details:</h4>";
    $stmt = $pdo->query("
        SELECT 
            p.payment_id,
            p.order_id,
            p.payment_time,
            p.transaction_status,
            o.total_amount,
            o.payment_status
        FROM payments p
        LEFT JOIN orders o ON p.order_id = o.order_id
        WHERE DATE(p.payment_time) = '2025-05-26'
        ORDER BY p.payment_time
    ");
    $may26Payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($may26Payments) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Payment ID</th><th>Order ID</th><th>Time</th><th>Amount</th><th>Payment Status</th><th>Order Status</th></tr>";
        foreach ($may26Payments as $payment) {
            echo "<tr>";
            echo "<td>" . $payment['payment_id'] . "</td>";
            echo "<td>" . ($payment['order_id'] ?: 'NULL') . "</td>";
            echo "<td>" . $payment['payment_time'] . "</td>";
            echo "<td>$" . ($payment['total_amount'] ?: '0.00') . "</td>";
            echo "<td>" . ($payment['transaction_status'] ?: 'EMPTY') . "</td>";
            echo "<td>" . ($payment['payment_status'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Count for May 26, 2025: " . count($may26Payments) . " payments</strong></p>";
    } else {
        echo "<p>❌ No payments found for May 26, 2025</p>";
    }
    
    echo "<h3>🧪 TESTING DIFFERENT COUNTING METHODS</h3>";
    
    // Test different counting approaches for May 26
    $testDate = '2025-05-26';
    
    // Method 1: Simple payment count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payments WHERE DATE(payment_time) = ?");
    $stmt->execute([$testDate]);
    $method1 = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Method 2: Payment count with JOIN to orders
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM payments p 
        JOIN orders o ON p.order_id = o.order_id 
        WHERE DATE(p.payment_time) = ?
    ");
    $stmt->execute([$testDate]);
    $method2 = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Method 3: DISTINCT payment_id
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT p.payment_id) as count FROM payments p WHERE DATE(p.payment_time) = ?");
    $stmt->execute([$testDate]);
    $method3 = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Method 4: Current dashboard query
    $stmt = $pdo->prepare("
        SELECT COUNT(p.payment_id) as count
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        WHERE DATE(p.payment_time) = ?
    ");
    $stmt->execute([$testDate]);
    $method4 = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Method</th><th>Query</th><th>Result</th></tr>";
    echo "<tr><td>Method 1</td><td>COUNT(*) FROM payments</td><td><strong>$method1</strong></td></tr>";
    echo "<tr><td>Method 2</td><td>COUNT(*) FROM payments JOIN orders</td><td><strong>$method2</strong></td></tr>";
    echo "<tr><td>Method 3</td><td>COUNT(DISTINCT payment_id)</td><td><strong>$method3</strong></td></tr>";
    echo "<tr><td>Method 4</td><td>Current dashboard query</td><td><strong>$method4</strong></td></tr>";
    echo "</table>";
    
    echo "<h3>🔍 CHECKING FOR DATA ISSUES</h3>";
    
    // Check for payments without orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments p LEFT JOIN orders o ON p.order_id = o.order_id WHERE o.order_id IS NULL");
    $orphanPayments = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p><strong>Payments without matching orders:</strong> $orphanPayments</p>";
    
    // Check for orders without payments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders o LEFT JOIN payments p ON o.order_id = p.order_id WHERE p.payment_id IS NULL");
    $orphanOrders = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p><strong>Orders without matching payments:</strong> $orphanOrders</p>";
    
    // Check current dashboard API query for today (May 27)
    echo "<h3>📈 DASHBOARD API TEST</h3>";
    $today = date('Y-m-d');
    echo "<p><strong>System 'today':</strong> $today</p>";
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(p.payment_id) as today_transactions,
            COALESCE(SUM(o.total_amount), 0) as today_sales
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        WHERE DATE(p.payment_time) = ?
    ");
    $stmt->execute([$today]);
    $todayResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Dashboard result for today ($today):</strong> {$todayResult['today_transactions']} transactions, $" . number_format($todayResult['today_sales'], 2) . "</p>";
    
    // Test for yesterday (May 26)
    $yesterday = '2025-05-26';
    $stmt->execute([$yesterday]);
    $yesterdayResult = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Dashboard result for May 26 ($yesterday):</strong> {$yesterdayResult['today_transactions']} transactions, $" . number_format($yesterdayResult['today_sales'], 2) . "</p>";
    
    echo "<h3>💡 SOLUTION RECOMMENDATIONS</h3>";
    
    if ($method1 == 4 && $method2 == 4 && $method3 == 4 && $method4 == 4) {
        echo "<p>✅ <strong>All counting methods return 4 transactions for May 26.</strong></p>";
        echo "<p>🎯 <strong>The issue is DATE MISMATCH:</strong></p>";
        echo "<ul>";
        echo "<li>Your transactions are on <strong>May 26, 2025</strong></li>";
        echo "<li>Your dashboard is looking for <strong>today (May 27, 2025)</strong></li>";
        echo "<li>Solution: Either adjust the date logic or create transactions for today</li>";
        echo "</ul>";
    } else {
        echo "<p>❌ <strong>Counting methods return different results. There are data integrity issues.</strong></p>";
        echo "<p>🔧 <strong>Data Issues Found:</strong></p>";
        echo "<ul>";
        if ($orphanPayments > 0) echo "<li>$orphanPayments payments have no matching orders</li>";
        if ($orphanOrders > 0) echo "<li>$orphanOrders orders have no matching payments</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
