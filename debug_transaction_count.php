<?php
/**
 * Debug Database Inconsistency
 * Check the mismatch between orders.payment_status and payments.transaction_status
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Database Transaction Count Debug</h2>";

try {
    require_once __DIR__ . '/config/db.php';
    echo "<p>✓ Database connection successful</p>";
    
    $today = '2025-05-26';
    
    echo "<h3>Today's Transactions Analysis ($today):</h3>";
    
    // 1. Check payments table for today
    echo "<p><strong>1. Payments table for today:</strong></p>";
    $stmt = $pdo->prepare("
        SELECT payment_id, order_id, payment_method, transaction_status, payment_time 
        FROM payments 
        WHERE DATE(payment_time) = ?
        ORDER BY payment_time
    ");
    $stmt->execute([$today]);
    $todayPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Payment ID</th><th>Order ID</th><th>Method</th><th>Transaction Status</th><th>Time</th></tr>";
    foreach ($todayPayments as $payment) {
        $status = $payment['transaction_status'] === '' ? '(EMPTY)' : $payment['transaction_status'];
        echo "<tr>";
        echo "<td>" . $payment['payment_id'] . "</td>";
        echo "<td>" . $payment['order_id'] . "</td>";
        echo "<td>" . $payment['payment_method'] . "</td>";
        echo "<td style='color: " . ($payment['transaction_status'] === '' ? 'red' : 'green') . ";'>" . $status . "</td>";
        echo "<td>" . $payment['payment_time'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Total payments today: " . count($todayPayments) . "</strong></p>";
    
    // 2. Check orders table for today's payments
    echo "<p><strong>2. Orders table for today's payments:</strong></p>";
    if (count($todayPayments) > 0) {
        $orderIds = array_column($todayPayments, 'order_id');
        $placeholders = str_repeat('?,', count($orderIds) - 1) . '?';
        
        $stmt = $pdo->prepare("
            SELECT order_id, payment_status, total_amount, order_date 
            FROM orders 
            WHERE order_id IN ($placeholders)
            ORDER BY order_id
        ");
        $stmt->execute($orderIds);
        $todayOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Order ID</th><th>Payment Status</th><th>Total Amount</th><th>Order Date</th></tr>";
        foreach ($todayOrders as $order) {
            echo "<tr>";
            echo "<td>" . $order['order_id'] . "</td>";
            echo "<td style='color: " . ($order['payment_status'] === 'Paid' ? 'green' : 'red') . ";'>" . $order['payment_status'] . "</td>";
            echo "<td>₱" . number_format($order['total_amount'], 2) . "</td>";
            echo "<td>" . $order['order_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Test the current dashboard query
    echo "<p><strong>3. Current dashboard query result:</strong></p>";
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT p.payment_id) as today_transactions,
            COALESCE(SUM(o.total_amount), 0) as today_sales
        FROM orders o 
        JOIN payments p ON o.order_id = p.order_id
        WHERE DATE(p.payment_time) = ? AND o.payment_status = 'Paid'
    ");
    $stmt->execute([$today]);
    $dashboardResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Dashboard shows: <strong>" . $dashboardResult['today_transactions'] . " transactions</strong> with total ₱" . number_format($dashboardResult['today_sales'], 2) . "</p>";
    
    // 4. Test alternative query without payment_status filter
    echo "<p><strong>4. Alternative query (count all payments for today):</strong></p>";
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT p.payment_id) as today_transactions,
            COALESCE(SUM(o.total_amount), 0) as today_sales
        FROM orders o 
        JOIN payments p ON o.order_id = p.order_id
        WHERE DATE(p.payment_time) = ?
    ");
    $stmt->execute([$today]);
    $altResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Alternative count: <strong>" . $altResult['today_transactions'] . " transactions</strong> with total ₱" . number_format($altResult['today_sales'], 2) . "</p>";
    
    // 5. Check for empty transaction_status
    echo "<p><strong>5. Count by transaction status:</strong></p>";
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN p.transaction_status = '' THEN 'EMPTY'
                WHEN p.transaction_status IS NULL THEN 'NULL'
                ELSE p.transaction_status 
            END as status_label,
            p.transaction_status,
            COUNT(*) as count
        FROM payments p
        WHERE DATE(p.payment_time) = ?
        GROUP BY p.transaction_status
    ");
    $stmt->execute([$today]);
    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Status Label</th><th>Raw Status</th><th>Count</th></tr>";
    foreach ($statusCounts as $status) {
        echo "<tr>";
        echo "<td>" . $status['status_label'] . "</td>";
        echo "<td>'" . $status['transaction_status'] . "'</td>";
        echo "<td>" . $status['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
