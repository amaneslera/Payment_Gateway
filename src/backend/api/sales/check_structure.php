<?php
// Check actual database content and structure
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once __DIR__ . '/../../../config/db.php';
    
    $result = [
        'success' => true,
        'database_info' => []
    ];
    
    // Check orders table structure and sample data
    $stmt = $pdo->prepare("DESCRIBE orders");
    $stmt->execute();
    $ordersStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM orders LIMIT 5");
    $stmt->execute();
    $ordersSample = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result['database_info']['orders'] = [
        'structure' => $ordersStructure,
        'sample_data' => $ordersSample
    ];
    
    // Check order_items table
    $stmt = $pdo->prepare("DESCRIBE order_items");
    $stmt->execute();
    $orderItemsStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM order_items LIMIT 5");
    $stmt->execute();
    $orderItemsSample = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result['database_info']['order_items'] = [
        'structure' => $orderItemsStructure,
        'sample_data' => $orderItemsSample
    ];
    
    // Check products table
    $stmt = $pdo->prepare("DESCRIBE products");
    $stmt->execute();
    $productsStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM products LIMIT 5");
    $stmt->execute();
    $productsSample = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result['database_info']['products'] = [
        'structure' => $productsStructure,
        'sample_data' => $productsSample
    ];
    
    // Check categories table
    $stmt = $pdo->prepare("DESCRIBE categories");
    $stmt->execute();
    $categoriesStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM categories LIMIT 5");
    $stmt->execute();
    $categoriesSample = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result['database_info']['categories'] = [
        'structure' => $categoriesStructure,
        'sample_data' => $categoriesSample
    ];
    
    // Try to run a test query similar to what the sales API does
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT o.order_id) as total_transactions,
                COALESCE(SUM(oi.quantity), 0) as total_items_sold,
                COALESCE(SUM(o.total_amount), 0) as total_sales
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.product_id
            WHERE o.payment_status = 'Paid'
        ");
        $stmt->execute();
        $testQuery = $stmt->fetch(PDO::FETCH_ASSOC);
        $result['test_query_result'] = $testQuery;
    } catch (PDOException $e) {
        $result['test_query_error'] = $e->getMessage();
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
