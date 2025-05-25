<?php
// Test script to verify SQL fixes
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../../../config/db.php';
    
    echo "Testing SQL fixes...\n\n";
    
    // Test 1: Top Products query
    echo "=== Testing Top Products Query ===\n";
    $limit = 10;
    $startDate = '2024-01-01 00:00:00';
    $endDate = '2024-12-31 23:59:59';
    
    $sql = "SELECT 
                p.product_id,
                p.name as product_name,
                c.category_name,
                SUM(oi.quantity) as units_sold,
                SUM(oi.subtotal) as total_revenue,
                SUM(oi.quantity * p.cost_price) as total_cost,
                (SUM(oi.subtotal) - SUM(oi.quantity * p.cost_price)) as profit,
                CASE 
                    WHEN SUM(oi.subtotal) > 0 
                    THEN ROUND(((SUM(oi.subtotal) - SUM(oi.quantity * p.cost_price)) / SUM(oi.subtotal)) * 100, 2)
                    ELSE 0 
                END as profit_margin
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            JOIN products p ON oi.product_id = p.product_id
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE o.order_date BETWEEN ? AND ?
            AND o.payment_status = 'Paid'
            GROUP BY p.product_id, p.name, c.category_name
            ORDER BY total_revenue DESC
            LIMIT " . $limit;
    
    echo "SQL Query:\n$sql\n\n";
    
    $stmt = $pdo->prepare($sql);
    $params = [$startDate, $endDate];
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query executed successfully!\n";
    echo "Number of results: " . count($result) . "\n\n";
    
    // Test 2: Product Sales Report query
    echo "=== Testing Product Sales Report Query ===\n";
    $limit = 20;
    $offset = 0;
    
    $sql = "SELECT 
                p.product_id,
                p.name as product_name,
                c.category_name,
                p.price as unit_price,
                p.cost_price,
                COALESCE(SUM(oi.quantity), 0) as units_sold,
                COALESCE(SUM(oi.subtotal), 0) as total_revenue,
                COALESCE(SUM(oi.quantity * p.cost_price), 0) as total_cost,
                (COALESCE(SUM(oi.subtotal), 0) - COALESCE(SUM(oi.quantity * p.cost_price), 0)) as profit,
                CASE 
                    WHEN COALESCE(SUM(oi.subtotal), 0) > 0 
                    THEN ROUND(((COALESCE(SUM(oi.subtotal), 0) - COALESCE(SUM(oi.quantity * p.cost_price), 0)) / COALESCE(SUM(oi.subtotal), 0)) * 100, 2)
                    ELSE 0 
                END as profit_margin
            FROM products p
            LEFT JOIN order_items oi ON p.product_id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.order_id
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE o.order_date BETWEEN ? AND ? AND o.payment_status = ?
            GROUP BY p.product_id, p.name, c.category_name, p.price, p.cost_price
            ORDER BY total_revenue DESC
            LIMIT " . $limit . " OFFSET " . $offset;
    
    echo "SQL Query:\n$sql\n\n";
    
    $stmt = $pdo->prepare($sql);
    $params = [$startDate, $endDate, 'Paid'];
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Query executed successfully!\n";
    echo "Number of results: " . count($result) . "\n\n";
    
    echo "All SQL tests passed! ✓\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
