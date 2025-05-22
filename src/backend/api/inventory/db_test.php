<?php
// Display all errors for debugging purposes
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load configuration
require_once __DIR__ . '/../../../config/config.php';

echo "<h1>Database Connection Test</h1>";

echo "<h2>Database Configuration</h2>";
echo "<p>DB_HOST: " . DB_HOST . "</p>";
echo "<p>DB_NAME: " . DB_NAME . "</p>";
echo "<p>DB_USER: " . DB_USER . "</p>";
echo "<p>DB_PASS: " . str_repeat("*", strlen(DB_PASS)) . "</p>";

echo "<h2>Testing PDO Connection</h2>";
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>PDO Connection successful!</p>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Connected to database: " . $result['current_db'] . "</p>";
    
    // Test inventory table
    $stmt = $pdo->query("SHOW TABLES LIKE 'inventory'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>Inventory table exists!</p>";
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventory");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Inventory table has " . $result['count'] . " records.</p>";
    } else {
        echo "<p style='color: red;'>Inventory table does not exist!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>PDO Connection failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Testing mysqli Connection</h2>";
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>mysqli Connection failed: " . $mysqli->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>mysqli Connection successful!</p>";
        
        // Test a simple query
        $result = $mysqli->query("SELECT DATABASE() as current_db");
        $row = $result->fetch_assoc();
        echo "<p>Connected to database: " . $row['current_db'] . "</p>";
    }
    
    $mysqli->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>mysqli error: " . $e->getMessage() . "</p>";
}
