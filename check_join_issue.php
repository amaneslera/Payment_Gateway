<?php
/**
 * Check for payments without matching orders (JOIN issue)
 */

require_once 'config/db.php';

try {
    echo "<h2>Payment-Order JOIN Issue Analysis</h2>\n";
    echo "Current Date: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Total payments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM payments");
    $totalPayments = $stmt->fetch()['total'];
    echo "✅ Total payments in database: $totalPayments\n\n";
    
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    echo "✅ Total orders in database: $totalOrders\n\n";
    
    // Payments with matching orders (INNER JOIN)
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM payments p 
        INNER JOIN orders o ON p.order_id = o.order_id
    ");
    $joinedCount = $stmt->fetch()['total'];
    echo "🔍 Payments with matching orders (INNER JOIN): $joinedCount\n\n";
    
    // The difference
    $missing = $totalPayments - $joinedCount;
    echo "❌ Payments WITHOUT matching orders: $missing\n\n";
    
    // Show which payments don't have orders
    if ($missing > 0) {
        echo "📋 Payments without matching orders:\n";
        $stmt = $pdo->query("
            SELECT p.payment_id, p.order_id, p.payment_method, p.payment_time, p.cash_received
            FROM payments p 
            LEFT JOIN orders o ON p.order_id = o.order_id
            WHERE o.order_id IS NULL
            ORDER BY p.payment_time DESC
        ");
        $orphanPayments = $stmt->fetchAll();
        
        foreach ($orphanPayments as $payment) {
            echo sprintf(
                "Payment ID: %d, Order ID: %s, Method: %s, Time: %s, Amount: $%.2f\n",
                $payment['payment_id'],
                $payment['order_id'] ?? 'NULL',
                $payment['payment_method'],
                $payment['payment_time'],
                $payment['cash_received'] ?? 0
            );
        }
    }
    
    echo "\n🔧 SOLUTION: Use LEFT JOIN or count payments directly without requiring orders\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
