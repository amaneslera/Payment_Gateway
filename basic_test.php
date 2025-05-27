<?php
echo "Testing basic PHP functionality...\n";

// Test 1: Check if we can include required files
try {
    require_once 'vendor/autoload.php';
    echo "✓ Autoloader included successfully\n";
} catch (Exception $e) {
    echo "✗ Autoloader error: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    require_once 'src/config/config.php';
    echo "✓ Config included successfully\n";
} catch (Exception $e) {
    echo "✗ Config error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check JWT functionality
try {
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    
    $payload = ['test' => 'data', 'exp' => time() + 3600];
    $token = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
    echo "✓ JWT token generated: " . substr($token, 0, 20) . "...\n";
} catch (Exception $e) {
    echo "✗ JWT error: " . $e->getMessage() . "\n";
}

// Test 3: Check if curl is available
if (function_exists('curl_init')) {
    echo "✓ cURL is available\n";
} else {
    echo "✗ cURL is not available\n";
}

// Test 4: Check actual PayPal API file
if (file_exists('src/backend/api/paypal-payment.php')) {
    echo "✓ PayPal API file exists\n";
    
    // Try to check syntax
    $syntaxCheck = `php -l src/backend/api/paypal-payment.php 2>&1`;
    if (strpos($syntaxCheck, 'No syntax errors') !== false) {
        echo "✓ PayPal API syntax is valid\n";
    } else {
        echo "✗ PayPal API syntax error: " . $syntaxCheck . "\n";
    }
} else {
    echo "✗ PayPal API file missing\n";
}

echo "Basic test complete.\n";
?>
