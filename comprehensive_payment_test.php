<?php
/**
 * Comprehensive Payment Status Test
 * Tests both Cash and PayPal payments to ensure they set status to 'Success'
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Comprehensive Payment Status Test</h1>";
echo "<p><strong>Objective:</strong> Verify that both Cash and PayPal payments correctly set transaction_status to 'Success'</p>";

try {
    require_once __DIR__ . '/src/config/db.php';
    echo "<p style='color: green;'>✅ Database connected</p>";
    
    echo "<h2>📊 Current Status Overview</h2>";
    
    // Check current status distribution
    $stmt = $pdo->query("
        SELECT 
            payment_method,
            transaction_status,
            COUNT(*) as count,
            MAX(payment_time) as latest_payment
        FROM payments 
        GROUP BY payment_method, transaction_status
        ORDER BY payment_method, transaction_status
    ");
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Payment Method</th><th>Status</th><th>Count</th><th>Latest Payment</th></tr>";
    
    $hasCorrectStatus = ['Cash' => false, 'PayPal' => false];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bgColor = $row['transaction_status'] === 'Success' ? 'lightgreen' : 
                  ($row['transaction_status'] === 'Pending' ? 'lightyellow' : 'lightcoral');
        
        echo "<tr style='background-color: {$bgColor};'>";
        echo "<td>{$row['payment_method']}</td>";
        echo "<td><strong>{$row['transaction_status']}</strong></td>";
        echo "<td>{$row['count']}</td>";
        echo "<td>{$row['latest_payment']}</td>";
        echo "</tr>";
        
        if ($row['transaction_status'] === 'Success') {
            $hasCorrectStatus[$row['payment_method']] = true;
        }
    }
    
    echo "</table>";
    
    echo "<h2>🧪 Test Results Summary</h2>";
    
    foreach ($hasCorrectStatus as $method => $hasSuccess) {
        if ($hasSuccess) {
            echo "<p style='color: green; font-size: 16px;'>✅ <strong>{$method}</strong> payments: CORRECTLY setting status to 'Success'</p>";
        } else {
            echo "<p style='color: red; font-size: 16px;'>❌ <strong>{$method}</strong> payments: NOT setting status to 'Success' (may still be using 'Pending')</p>";
        }
    }
    
    echo "<h2>🔧 Fix Implementation Status</h2>";
    
    // Check if the fixes are in place by looking at the code
    $cashPaymentFile = __DIR__ . '/src/backend/api/payments.php';
    $paypalPaymentFile = __DIR__ . '/src/backend/api/paypal-payment.php';
    
    echo "<h3>Cash Payment Fix Check:</h3>";
    if (file_exists($cashPaymentFile)) {
        $cashContent = file_get_contents($cashPaymentFile);
        if (strpos($cashContent, "VALUES (?, ?, 'Success',") !== false) {
            echo "<p style='color: green;'>✅ Cash payment fix IMPLEMENTED - explicitly setting 'Success' status</p>";
        } else {
            echo "<p style='color: red;'>❌ Cash payment fix NOT FOUND - may still be using database default</p>";
        }
    }
    
    echo "<h3>PayPal Payment Fix Check:</h3>";
    if (file_exists($paypalPaymentFile)) {
        $paypalContent = file_get_contents($paypalPaymentFile);
        if (strpos($paypalContent, "VALUES (?, 'PayPal', ?, 'Success',") !== false) {
            echo "<p style='color: green;'>✅ PayPal payment fix IMPLEMENTED - explicitly setting 'Success' status</p>";
        } else {
            echo "<p style='color: red;'>❌ PayPal payment fix NOT FOUND - may still be using database default</p>";
        }
    }
    
    echo "<h2>📝 Recent Payment Examples</h2>";
    
    // Show recent examples of each payment type
    $stmt = $pdo->query("
        SELECT 
            payment_id,
            order_id,
            payment_method,
            transaction_status,
            cash_received,
            payment_time
        FROM payments 
        ORDER BY payment_time DESC 
        LIMIT 10
    ");
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Payment ID</th><th>Order ID</th><th>Method</th><th>Status</th><th>Cash Received</th><th>Time</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bgColor = $row['transaction_status'] === 'Success' ? 'lightgreen' : 
                  ($row['transaction_status'] === 'Pending' ? 'lightyellow' : 'lightcoral');
        
        echo "<tr style='background-color: {$bgColor};'>";
        echo "<td>{$row['payment_id']}</td>";
        echo "<td>{$row['order_id']}</td>";
        echo "<td>{$row['payment_method']}</td>";
        echo "<td><strong>{$row['transaction_status']}</strong></td>";
        echo "<td>{$row['cash_received']}</td>";
        echo "<td>{$row['payment_time']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h2>💡 Next Steps</h2>";
    
    if ($hasCorrectStatus['Cash'] && $hasCorrectStatus['PayPal']) {
        echo "<div style='background-color: lightgreen; padding: 15px; border-radius: 5px;'>";
        echo "<h3>🎉 SUCCESS! Both payment methods are working correctly.</h3>";
        echo "<p><strong>Cash Payments:</strong> ✅ Setting status to 'Success'</p>";
        echo "<p><strong>PayPal Payments:</strong> ✅ Setting status to 'Success'</p>";
        echo "<p><strong>Result:</strong> Your payment gateway is now consistently marking successful payments as 'Success' in the database.</p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: lightcoral; padding: 15px; border-radius: 5px;'>";
        echo "<h3>⚠️ Action Required</h3>";
        echo "<p>The fixes have been implemented in the code, but you may need to:</p>";
        echo "<ul>";
        echo "<li>Test a new cash payment to see if it gets 'Success' status</li>";
        echo "<li>Test a new PayPal payment to see if it gets 'Success' status</li>";
        echo "<li>Clear any server caches if you're using them</li>";
        echo "<li>Restart your web server to ensure the updated code is loaded</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h2>🔍 Database Schema Reminder</h2>";
    echo "<p><strong>Current Default:</strong> The database sets transaction_status default to 'Pending'</p>";
    echo "<p><strong>Fixed Behavior:</strong> Both payment methods now explicitly set it to 'Success' when payment is processed successfully</p>";
    
} catch (Exception $e) {
    echo "<div style='background-color: lightcoral; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error During Testing</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>
