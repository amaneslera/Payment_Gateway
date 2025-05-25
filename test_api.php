<?php
// Test script to check API endpoints
header('Content-Type: text/html; charset=utf-8');

echo "<h2>API Testing Results</h2>";

// Test 1: Database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    require_once __DIR__ . '/src/config/db.php';
    echo "✅ Database connection successful<br>";
    echo "PDO object created: " . ($pdo ? "Yes" : "No") . "<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 2: JWT library
echo "<h3>2. JWT Library Test</h3>";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Composer autoload successful<br>";
    
    if (class_exists('Firebase\JWT\JWT')) {
        echo "✅ Firebase JWT library loaded<br>";
    } else {
        echo "❌ Firebase JWT library not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Autoload failed: " . $e->getMessage() . "<br>";
}

// Test 3: Auth middleware
echo "<h3>3. Auth Middleware Test</h3>";
try {
    require_once __DIR__ . '/src/backend/middleware/auth_middleware.php';
    echo "✅ Auth middleware loaded successfully<br>";
    
    if (class_exists('AuthMiddleware')) {
        echo "✅ AuthMiddleware class exists<br>";
    } else {
        echo "❌ AuthMiddleware class not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Auth middleware failed: " . $e->getMessage() . "<br>";
}

// Test 4: Test API endpoint
echo "<h3>4. Sales API Test</h3>";
echo "<p>Testing without authentication (should fail with 401):</p>";

$url = 'http://localhost:8080/src/backend/api/sales/sales_api.php?action=categories';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json'
    ]
]);

$result = @file_get_contents($url, false, $context);
if ($result === false) {
    echo "❌ API request failed<br>";
} else {
    echo "✅ API responded<br>";
    echo "Response: " . htmlspecialchars($result) . "<br>";
}

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>If database connection failed, check your .env file and database credentials</li>";
echo "<li>If JWT library failed, run 'composer install' again</li>";
echo "<li>To test authenticated endpoints, you'll need a valid JWT token</li>";
echo "</ul>";
?>
