<?php
// Test version of sales API without authentication
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

try {
    $action = $_GET['action'] ?? 'summary';
    
    switch ($action) {
        case 'summary':
            // Get sales summary
            $stmt = $pdo->prepare("
                SELECT 
                    COALESCE(SUM(oi.price * oi.quantity), 0) as total_sales,
                    COUNT(DISTINCT o.id) as total_orders,
                    COALESCE(AVG(oi.price * oi.quantity), 0) as average_order,
                    COUNT(DISTINCT oi.product_id) as products_sold
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.order_date >= CURDATE() - INTERVAL 30 DAY
            ");
            $stmt->execute();
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Format the response
            $response = [
                'total_sales' => number_format($summary['total_sales'], 2),
                'total_orders' => intval($summary['total_orders']),
                'average_order' => number_format($summary['average_order'], 2),
                'products_sold' => intval($summary['products_sold'])
            ];
            break;
            
        case 'top_products':
            // Get top selling products
            $stmt = $pdo->prepare("
                SELECT 
                    p.name,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.price * oi.quantity) as total_revenue
                FROM products p
                JOIN order_items oi ON p.id = oi.product_id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.order_date >= CURDATE() - INTERVAL 30 DAY
                GROUP BY p.id, p.name
                ORDER BY total_sold DESC
                LIMIT 10
            ");
            $stmt->execute();
            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'sales_trends':
            // Get daily sales for the last 7 days
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(o.order_date) as date,
                    COALESCE(SUM(oi.price * oi.quantity), 0) as total_sales,
                    COUNT(DISTINCT o.id) as order_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.order_date >= CURDATE() - INTERVAL 7 DAY
                GROUP BY DATE(o.order_date)
                ORDER BY date ASC
            ");
            $stmt->execute();
            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        default:
            http_response_code(400);
            $response = ['error' => 'Invalid action'];
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
