<?php
echo "PHP is working\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";

// Test basic database connection
try {
    require_once __DIR__ . '/../../config/config.php';
    echo "Config loaded successfully\n";
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection: SUCCESS\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Orders count: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
