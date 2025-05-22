<?php
echo "Starting DB connection test...\n";

// Define constants for testing (if different from config.php)
define("DB_HOST", "localhost"); 
define("DB_USER", "pos"); 
define("DB_PASS", "pos");
define("DB_NAME", "pos_system");

// Test direct connection
try {
    echo "Testing mysqli connection...\n";
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        echo "FAILED: " . $conn->connect_error . "\n";
    } else {
        echo "SUCCESS: Connected successfully using mysqli\n";
        $conn->close();
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

// Test PDO connection
try {
    echo "\nTesting PDO connection...\n";
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "SUCCESS: Connected successfully using PDO\n";
    $pdo = null;
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}

echo "\nTest complete.";
