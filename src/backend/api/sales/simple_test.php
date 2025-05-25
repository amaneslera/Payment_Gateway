<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
    
    // Check if user is authenticated
    $user = AuthMiddleware::validateToken();
    
    if (!$user) {
        throw new Exception('Unauthorized access');
    }

    // Only Admin can access sales reports
    if ($user->role !== 'Admin') {
        throw new Exception('Insufficient permissions. Admin access required.');
    }

    // Simple test - just return categories
    $sql = "SELECT category_id, category_name, description FROM categories ORDER BY category_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Test successful',
        'data' => $categories,
        'count' => count($categories)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
