<?php
// Set error handling to log errors to a file
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("Starting test script");

// Set a custom error log file
ini_set('error_log', __DIR__ . '/debug.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Include database connection
    require_once __DIR__ . '/../../../config/db.php';
    error_log("Database connection included");
    
    // Test the SQL queries directly
    $startDate = date('Y-m-d', strtotime('-30 days'));
    $endDate = date('Y-m-d');
    $limit = 10;
    
    error_log("Testing getTopProducts query");
    
    // Test the top products query
    $sql = "SELECT 
                p.product_id,
                p.name as product_name,
                c.category_name,
                COALESCE(SUM(oi.quantity), 0) as units_sold,
                COALESCE(SUM(oi.subtotal), 0) as total_revenue,
                COALESCE(SUM(oi.quantity * p.cost_price), 0) as total_cost
            FROM products p
            LEFT JOIN order_items oi ON p.product_id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.order_id
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE (oi.order_id IS NULL OR (o.order_date BETWEEN ? AND ? AND o.payment_status = 'Paid'))
            GROUP BY p.product_id, p.name, c.category_name
            ORDER BY total_revenue DESC
            LIMIT ?";
    
    $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59', (int)$limit];
    error_log("Parameters: " . json_encode($params));
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Query executed successfully");
    
    echo json_encode([
        'success' => true,
        'message' => 'Test query executed successfully',
        'data' => [
            'products' => $products
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Test Script Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
