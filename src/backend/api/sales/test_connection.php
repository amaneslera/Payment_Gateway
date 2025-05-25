<?php
// Simple test endpoint to verify database connection and authentication
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Include required files
    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../middleware/auth_middleware.php';

    // Test database connection
    $testQuery = $pdo->query("SELECT 1 as test");
    $dbTest = $testQuery->fetch();
    
    // Check if user is authenticated
    $user = AuthMiddleware::validateToken();
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Authentication failed',
            'database_connection' => $dbTest ? 'OK' : 'FAILED'
        ]);
        exit;
    }

    // Test some basic queries
    $categoryTest = $pdo->query("SELECT COUNT(*) as count FROM categories")->fetch();
    $productTest = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch();
    $orderTest = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Connection successful',
        'user' => [
            'id' => $user->user_id ?? 'unknown',
            'role' => $user->role ?? 'unknown'
        ],
        'database_status' => [
            'connection' => 'OK',
            'categories_count' => $categoryTest['count'],
            'products_count' => $productTest['count'],
            'orders_count' => $orderTest['count']
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
