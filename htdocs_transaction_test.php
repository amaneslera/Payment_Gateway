<?php
/**
 * HTDOCS Transaction Count Test
 * Direct test for XAMPP/htdocs setup
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>HTDOCS Transaction Count Test</h1>";
echo "<p>Current Date: " . date('Y-m-d H:i:s') . "</p>";

try {
    // Direct database connection for htdocs
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=pos_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
    
    echo "<h2>1. Raw Payment Count</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total_payments FROM payments");
    $totalPayments = $stmt->fetch(PDO::FETCH_ASSOC)['total_payments'];
    echo "<p><strong>Total payments in database: {$totalPayments}</strong></p>";
    
    echo "<h2>2. Payments by Date</h2>";
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
        echo "<tr style='{$highlight}'><td>{$row['payment_date']}</td><td><strong>{$row['payment_count']}</strong></td></tr>";
    }
    echo "</table>";
    echo "<p><em>Yellow = May 26 (your transaction date)</em></p>";
    
    echo "<h2>3. Today vs May 26 Comparison</h2>";
    $today = date('Y-m-d');
    $may26 = '2025-05-26';
    
    // Count for today
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payments WHERE DATE(payment_time) = ?");
    $stmt->execute([$today]);
    $todayCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count for May 26
    $stmt->execute([$may26]);
    $may26Count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<p><strong>Today ({$today}): {$todayCount} payments</strong></p>";
    echo "<p><strong>May 26, 2025: {$may26Count} payments</strong></p>";
    
    echo "<h2>4. Dashboard Query Test (Current)</h2>";
    echo "<p>Testing the current dashboard query for today ({$today}):</p>";
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(p.payment_id) as today_transactions,
            COALESCE(SUM(o.total_amount), 0) as today_sales
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        WHERE DATE(p.payment_time) = ?
    ");
    $stmt->execute([$today]);
    $dashboardToday = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Dashboard result for today: <strong>{$dashboardToday['today_transactions']} transactions</strong>, Sales: \${$dashboardToday['today_sales']}</p>";
    
    echo "<h2>5. Dashboard Query Test (May 26)</h2>";
    echo "<p>Testing the dashboard query for May 26, 2025:</p>";
    
    $stmt->execute([$may26]);
    $dashboardMay26 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Dashboard result for May 26: <strong>{$dashboardMay26['today_transactions']} transactions</strong>, Sales: \${$dashboardMay26['today_sales']}</p>";
    
    echo "<h2>6. The Problem</h2>";
    if ($todayCount == 0 && $may26Count > 0) {
        echo "<div style='background-color: #ffcccc; padding: 10px; border: 1px solid red;'>";
        echo "<p><strong>PROBLEM IDENTIFIED:</strong></p>";
        echo "<p>• Your system is looking for transactions on <strong>TODAY ({$today})</strong></p>";
        echo "<p>• But your actual transactions are on <strong>MAY 26, 2025</strong></p>";
        echo "<p>• That's why the dashboard shows 0-1 transactions instead of {$may26Count}</p>";
        echo "</div>";
        
        echo "<h2>7. SOLUTION OPTIONS</h2>";
        echo "<div style='background-color: #ccffcc; padding: 10px; border: 1px solid green;'>";
        echo "<p><strong>Option 1:</strong> Change your computer's date to May 26, 2025</p>";
        echo "<p><strong>Option 2:</strong> Create new transactions today (May 27, 2025)</p>";
        echo "<p><strong>Option 3:</strong> Modify the dashboard to show May 26 data instead of today</p>";
        echo "</div>";
    }
    
    echo "<h2>8. Recent Payments Details</h2>";
    $stmt = $pdo->query("
        SELECT 
            p.payment_id,
            p.order_id,
            p.payment_time,
            o.total_amount,
            DATE(p.payment_time) as payment_date
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        ORDER BY p.payment_time DESC
        LIMIT 10
    ");
    $recentPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Payment ID</th><th>Order ID</th><th>Amount</th><th>Date</th><th>Time</th></tr>";
    foreach ($recentPayments as $payment) {
        $highlight = ($payment['payment_date'] === '2025-05-26') ? 'background-color: yellow;' : '';
        echo "<tr style='{$highlight}'>";
        echo "<td>{$payment['payment_id']}</td>";
        echo "<td>{$payment['order_id']}</td>";
        echo "<td>\${$payment['total_amount']}</td>";
        echo "<td>{$payment['payment_date']}</td>";
        echo "<td>" . date('H:i:s', strtotime($payment['payment_time'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
