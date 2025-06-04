<?php
/**
 * Test Cash Payment Status Fix
 * This script tests if cash payments now correctly show 'Success' status
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🧪 Cash Payment Status Fix Test</h2>";
echo "<p>Testing if the payment status bug has been fixed...</p>";

try {
    // Direct database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=pos_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
    
    echo "<h3>📊 Recent Cash Payments Analysis</h3>";
    
    // Check recent cash payments and their status
    $stmt = $pdo->prepare("
        SELECT 
            payment_id,
            order_id,
            payment_method,
            transaction_status,
            cash_received,
            change_amount,
            payment_time,
            CASE 
                WHEN transaction_status = '' THEN 'EMPTY'
                WHEN transaction_status IS NULL THEN 'NULL'
                ELSE transaction_status
            END as status_display
        FROM payments 
        WHERE payment_method = 'Cash'
        ORDER BY payment_time DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recentCashPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($recentCashPayments) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th>Payment ID</th><th>Order ID</th><th>Cash Received</th><th>Change</th>";
        echo "<th>Transaction Status</th><th>Status Display</th><th>Payment Time</th>";
        echo "</tr>";
        
        foreach ($recentCashPayments as $payment) {
            $statusColor = ($payment['transaction_status'] === 'Success') ? 'green' : 
                          (($payment['transaction_status'] === '' || $payment['transaction_status'] === null) ? 'red' : 'orange');
            
            echo "<tr>";
            echo "<td>{$payment['payment_id']}</td>";
            echo "<td>{$payment['order_id']}</td>";
            echo "<td>₱" . number_format($payment['cash_received'], 2) . "</td>";
            echo "<td>₱" . number_format($payment['change_amount'], 2) . "</td>";
            echo "<td style='color: {$statusColor}; font-weight: bold;'>{$payment['transaction_status']}</td>";
            echo "<td style='color: {$statusColor}; font-weight: bold;'>{$payment['status_display']}</td>";
            echo "<td>" . date('M d, Y H:i:s', strtotime($payment['payment_time'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count status types
        echo "<h3>📈 Status Summary</h3>";
        $statusCounts = [];
        foreach ($recentCashPayments as $payment) {
            $status = $payment['status_display'];
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
        }
        
        foreach ($statusCounts as $status => $count) {
            $color = ($status === 'Success') ? 'green' : 'red';
            echo "<p style='color: {$color}; font-weight: bold;'>{$status}: {$count} payments</p>";
        }
        
        // Compare with PayPal payments
        echo "<h3>🔄 PayPal vs Cash Status Comparison</h3>";
        $stmt = $pdo->prepare("
            SELECT 
                payment_method,
                COUNT(*) as total_payments,
                SUM(CASE WHEN transaction_status = 'Success' THEN 1 ELSE 0 END) as success_payments,
                SUM(CASE WHEN transaction_status = '' OR transaction_status IS NULL THEN 1 ELSE 0 END) as empty_payments
            FROM payments 
            WHERE payment_method IN ('Cash', 'PayPal')
            GROUP BY payment_method
        ");
        $stmt->execute();
        $comparison = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th>Payment Method</th><th>Total Payments</th><th>Success Status</th><th>Empty/NULL Status</th><th>Success Rate</th>";
        echo "</tr>";
        
        foreach ($comparison as $row) {
            $successRate = ($row['total_payments'] > 0) ? 
                          round(($row['success_payments'] / $row['total_payments']) * 100, 1) : 0;
            $rateColor = ($successRate > 50) ? 'green' : 'red';
            
            echo "<tr>";
            echo "<td style='font-weight: bold;'>{$row['payment_method']}</td>";
            echo "<td>{$row['total_payments']}</td>";
            echo "<td style='color: green;'>{$row['success_payments']}</td>";
            echo "<td style='color: red;'>{$row['empty_payments']}</td>";
            echo "<td style='color: {$rateColor}; font-weight: bold;'>{$successRate}%</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='margin-top: 20px; padding: 15px; background-color: #e7f3ff; border-left: 5px solid #2196F3;'>";
        echo "<h4>💡 Expected Result After Fix:</h4>";
        echo "<p>• <strong>Cash payments should have 'Success' status</strong> (not empty/NULL)</p>";
        echo "<p>• <strong>PayPal payments should continue to have 'Success' status</strong></p>";
        echo "<p>• <strong>Both payment methods should show similar success rates</strong></p>";
        echo "</div>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ No cash payments found in database</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
table { margin: 10px 0; background-color: white; }
th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; font-weight: bold; }
tr:nth-child(even) { background-color: #f9f9f9; }
h2, h3 { color: #333; }
</style>
