<?php
// Direct SQL test - bypass all auth issues
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Transaction Count Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { padding: 15px; margin: 10px 0; border-radius: 5px; font-size: 18px; }
        .success { background-color: #d4edda; border: 2px solid #28a745; }
        .error { background-color: #f8d7da; border: 2px solid #dc3545; }
        .info { background-color: #d1ecf1; border: 2px solid #17a2b8; }
    </style>
</head>
<body>
    <h1>🚀 SIMPLE TRANSACTION COUNT TEST</h1>
    
    <?php
    try {
        require_once __DIR__ . '/src/config/db.php';
        
        // Test 1: Direct payment count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM payments");
        $direct_count = $stmt->fetch()['total'];
        
        // Test 2: Sales API query (the exact one from sales_api.php)
        $sql = "SELECT 
                    COUNT(DISTINCT pay.payment_id) as total_transactions
                FROM payments pay
                LEFT JOIN orders o ON pay.order_id = o.order_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE pay.payment_time BETWEEN '2025-01-01 00:00:00' AND '2025-12-31 23:59:59'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $api_count = $stmt->fetch()['total_transactions'];
        
        echo '<div class="info">📊 <strong>Direct Payment Count:</strong> ' . $direct_count . '</div>';
        echo '<div class="info">🔍 <strong>Sales API Query Count:</strong> ' . $api_count . '</div>';
        
        if ($direct_count == 48 && $api_count == 48) {
            echo '<div class="result success">✅ <strong>SUCCESS!</strong> Both show 48 transactions - LEFT JOIN fix is working!</div>';
        } elseif ($direct_count == $api_count) {
            echo '<div class="result success">✅ <strong>CONSISTENT!</strong> Both show ' . $direct_count . ' transactions</div>';
        } else {
            echo '<div class="result error">❌ <strong>MISMATCH!</strong> Direct: ' . $direct_count . ', API Query: ' . $api_count . '</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="result error">💥 <strong>Error:</strong> ' . $e->getMessage() . '</div>';
    }
    ?>
    
    <hr>
    <p><strong>Next step:</strong> If this shows 48/48, then the issue is in the frontend. If not, the SQL needs more fixes.</p>
</body>
</html>
