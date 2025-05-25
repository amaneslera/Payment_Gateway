<?php
require_once __DIR__ . '/src/config/config.php';

echo "Testing database connection...\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection successful!\n";
    
    // Test basic queries
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $orders = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Orders count: " . $orders['count'] . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $products = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Products count: " . $products['count'] . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $categories = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Categories count: " . $categories['count'] . "\n";
    
    // Test recent orders
    $stmt = $pdo->query("SELECT DATE(order_date) as date, COUNT(*) as count FROM orders GROUP BY DATE(order_date) ORDER BY date DESC LIMIT 5");
    echo "\nRecent order activity:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  " . $row['date'] . ": " . $row['count'] . " orders\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}
?>
