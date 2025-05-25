<?php
// Test script to validate sales_api.php functionality
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/db.php';

function getTopProducts($pdo) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
    $endDate = date('Y-m-d');
    $limit = 10;
    
    try {
        $sql = "SELECT 
                    p.product_id,
                    p.name as product_name,
                    c.category_name,
                    SUM(oi.quantity) as units_sold,
                    SUM(oi.subtotal) as total_revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                JOIN products p ON oi.product_id = p.product_id
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.payment_status = 'Paid'
                GROUP BY p.product_id, p.name, c.category_name
                ORDER BY total_revenue DESC
                LIMIT ?";
        
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59', $limit];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $products
        ];
        
    } catch (PDOException $e) {
        error_log("Database error in getTopProducts: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Database error occurred: ' . $e->getMessage()
        ];
    }
}

// Run test
$result = getTopProducts($pdo);
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
