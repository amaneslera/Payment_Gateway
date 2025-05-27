<?php
/**
 * Debug PayPal API Endpoint
 * Test file to debug the PayPal payment API issues
 */

echo "=== PayPal API Debug Test ===\n";

// Test 1: Check if required files exist
echo "1. Checking file existence:\n";
$files = [
    'src/backend/api/paypal-payment.php',
    'src/config/config.php',
    'config/db.php',
    'src/backend/middleware/auth_middleware.php'
];

foreach ($files as $file) {
    echo "   $file: " . (file_exists($file) ? "✓ EXISTS" : "✗ MISSING") . "\n";
}

// Test 2: Check if constants are defined
echo "\n2. Checking PayPal configuration:\n";
require_once 'src/config/config.php';

$constants = ['PAYPAL_CLIENT_ID', 'PAYPAL_CLIENT_SECRET', 'PAYPAL_ENVIRONMENT'];
foreach ($constants as $constant) {
    if (defined($constant)) {
        $value = constant($constant);
        $displayValue = $constant === 'PAYPAL_CLIENT_SECRET' ? '[HIDDEN]' : $value;
        echo "   $constant: ✓ $displayValue\n";
    } else {
        echo "   $constant: ✗ NOT DEFINED\n";
    }
}

// Test 3: Test direct API call simulation
echo "\n3. Testing API call simulation:\n";

// Simulate the API request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test_token';

// Capture output from the API
ob_start();

try {
    // Mock JWT token validation for testing
    class MockAuthMiddleware {
        public static function validateToken() {
            return ['user_id' => 114, 'username' => 'testuser'];
        }
    }
    
    // Mock the auth middleware temporarily
    if (!class_exists('AuthMiddleware')) {
        class AuthMiddleware extends MockAuthMiddleware {}
    }
    
    // Test JSON input
    $testPayload = [
        'payment_method' => 'paypal',
        'paypal_transaction_id' => 'TEST_TXN_123',
        'paypal_order_details' => [
            'id' => 'TEST_ORDER_123',
            'status' => 'COMPLETED',
            'purchase_units' => [
                [
                    'amount' => [
                        'value' => '100.00'
                    ]
                ]
            ],
            'payer' => [
                'payer_id' => 'TEST_PAYER',
                'email_address' => 'test@example.com'
            ]
        ],
        'cart_items' => [
            [
                'product_id' => 3,
                'quantity' => 1,
                'price' => 89.29
            ]
        ],
        'total_amount' => 100.00
    ];
    
    // Set up input stream for the API
    $temp = tmpfile();
    fwrite($temp, json_encode($testPayload));
    rewind($temp);
    
    echo "   Test payload prepared: ✓\n";
    echo "   Attempting to include PayPal API file...\n";
    
    // This would normally fail due to database connection, but we can see where it fails
    
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();
echo "   Output captured: " . strlen($output) . " bytes\n";

// Test 4: Check database connection
echo "\n4. Testing database connection:\n";
try {
    require_once 'config/db.php';
    echo "   Database connection: ✓ SUCCESS\n";
    
    // Check if payments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'payments'");
    if ($stmt->rowCount() > 0) {
        echo "   Payments table: ✓ EXISTS\n";
    } else {
        echo "   Payments table: ✗ MISSING\n";
    }
    
    // Check if paypal_transaction_details table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'paypal_transaction_details'");
    if ($stmt->rowCount() > 0) {
        echo "   PayPal details table: ✓ EXISTS\n";
    } else {
        echo "   PayPal details table: ✗ MISSING\n";
    }
    
} catch (Exception $e) {
    echo "   Database connection: ✗ ERROR - " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
