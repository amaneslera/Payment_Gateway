<?php
/**
 * Environment Check Script
 * 
 * This script checks if the required environment configurations are set up correctly.
 */

echo "Checking environment setup...\n\n";

// Check PHP version
echo "PHP Version: " . phpversion() . "\n";
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "WARNING: PHP version 7.4.0 or higher is recommended.\n";
}

// Check required extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'json'];
echo "\nChecking required PHP extensions:\n";
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ $ext extension is loaded.\n";
    } else {
        echo "✗ $ext extension is NOT loaded. This extension is required.\n";
    }
}

// Check if .env file exists
echo "\nChecking configuration files:\n";
if (file_exists(__DIR__ . '/.env')) {
    echo "✓ .env file exists.\n";
    
    // Check if required variables are set
    $envContents = file_get_contents(__DIR__ . '/.env');
    $requiredVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'JWT_SECRET'];
    foreach ($requiredVars as $var) {
        if (strpos($envContents, $var . '=') !== false) {
            echo "✓ $var is defined in .env file.\n";
        } else {
            echo "✗ $var is NOT defined in .env file. This variable is required.\n";
        }
    }
} else {
    echo "✗ .env file does NOT exist. Please create this file with required configuration.\n";
}

// Check composer dependencies
echo "\nChecking Composer dependencies:\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✓ Composer dependencies are installed.\n";
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('\Firebase\JWT\JWT')) {
        echo "✓ Firebase JWT library is properly installed.\n";
    } else {
        echo "✗ Firebase JWT library is NOT properly installed.\n";
    }
} else {
    echo "✗ Composer dependencies are NOT installed. Run 'composer install' to install them.\n";
}

// Test database connection
echo "\nTesting database connection:\n";
try {
    require_once __DIR__ . '/src/config/config.php';
    
    if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✓ Database connection successful.\n";
        
        // Check if tables exist
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "✓ Found " . count($tables) . " tables in the database.\n";
    } else {
        echo "✗ Database configuration constants are not defined.\n";
    }
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\nEnvironment check completed.\n";