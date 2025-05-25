<?php
// Direct test of sales API
require_once __DIR__ . '/src/backend/api/sales/sales_api.php';

// Simulate a GET request for summary
$_GET['action'] = 'summary';

// Capture output
ob_start();

// This would normally be called by the web server
// But we can test the logic directly

echo "Testing sales API...\n";

// Test database connection first
require_once __DIR__ . '/src/config/db.php';

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Database connection: SUCCESS\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as order_count FROM orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total orders in database: " . $result['order_count'] . "\n";
    
    // Test products count
    $stmt = $pdo->query("SELECT COUNT(*) as product_count FROM products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total products in database: " . $result['product_count'] . "\n";
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();
echo $output;
?>
