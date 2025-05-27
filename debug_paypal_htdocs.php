<?php
/**
 * Debug PayPal Payment API - htdocs version
 * Place this in your htdocs/Payment_Gateway/debug_paypal.php to test directly
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

echo "<h2>PayPal API Debug - HTDOCS Version</h2>";

// Test 1: Check file existence and paths
echo "<h3>1. File Existence Check</h3>";
$requiredFiles = [
    'src/config/config.php',
    'config/db.php',
    'src/backend/middleware/auth_middleware.php',
    'src/backend/api/paypal-payment.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS<br>";
    } else {
        echo "❌ $file - MISSING<br>";
    }
}

// Test 2: Include configuration and check constants
echo "<h3>2. Configuration Test</h3>";
try {
    require_once 'src/config/config.php';
    echo "✅ Config file included successfully<br>";
    
    $paypalConstants = ['PAYPAL_CLIENT_ID', 'PAYPAL_CLIENT_SECRET', 'PAYPAL_ENVIRONMENT'];
    foreach ($paypalConstants as $constant) {
        if (defined($constant)) {
            $value = constant($constant);
            $display = $constant === 'PAYPAL_CLIENT_SECRET' ? '[HIDDEN]' : $value;
            echo "✅ $constant: $display<br>";
        } else {
            echo "❌ $constant: NOT DEFINED<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
}

// Test 3: Database connection
echo "<h3>3. Database Connection Test</h3>";
try {
    require_once 'config/db.php';
    echo "✅ Database connected successfully<br>";
    
    // Test payments table
    $stmt = $pdo->query("DESCRIBE payments");
    echo "✅ Payments table structure:<br>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "&nbsp;&nbsp;- {$row['Field']} ({$row['Type']})<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 4: Simulate PayPal API call
echo "<h3>4. PayPal API Simulation</h3>";

// Simulate the exact request from the frontend
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test_token';

$mockPayload = [
    'payment_method' => 'paypal',
    'paypal_transaction_id' => '8R2194297M199633P',
    'paypal_order_details' => [
        'id' => '8R2194297M199633P',
        'status' => 'COMPLETED',
        'purchase_units' => [
            [
                'amount' => [
                    'value' => '385.00'
                ]
            ]
        ],
        'payer' => [
            'payer_id' => 'CPV3FGL6ADH2N',
            'email_address' => 'andreieslera@gmail.com'
        ]
    ],
    'cart_items' => [
        [
            'id' => 1,
            'product_id' => 1,
            'name' => 'Sample Coffee',
            'price' => 150,
            'quantity' => 2
        ],
        [
            'id' => 2,
            'product_id' => 2,
            'name' => 'Sample Pastry',
            'price' => 85,
            'quantity' => 1
        ]
    ],
    'total_amount' => 385
];

echo "Test payload prepared...<br>";

// Capture any output from the PayPal API
ob_start();

try {
    // Mock authentication for testing
    class MockAuthMiddleware {
        public static function validateToken() {
            return ['user_id' => 114, 'username' => 'testuser'];
        }
    }
    
    // Temporarily mock file_get_contents for testing
    function mock_file_get_contents($filename) {
        global $mockPayload;
        if ($filename === 'php://input') {
            return json_encode($mockPayload);
        }
        return file_get_contents($filename);
    }
    
    echo "Attempting to process PayPal payment...<br>";
    
    // We'll include the PayPal API file in a way that we can catch errors
    
} catch (Exception $e) {
    echo "❌ Error during simulation: " . $e->getMessage() . "<br>";
}

$output = ob_get_clean();
echo "API Output captured: " . strlen($output) . " bytes<br>";
if ($output) {
    echo "<pre>$output</pre>";
}

// Test 5: Check error logs
echo "<h3>5. Error Logs Check</h3>";
$errorLogPath = ini_get('error_log');
echo "Error log path: $errorLogPath<br>";

if ($errorLogPath && file_exists($errorLogPath)) {
    $logs = file_get_contents($errorLogPath);
    $recentLogs = array_slice(explode("\n", $logs), -20); // Last 20 lines
    echo "<strong>Recent error logs:</strong><br>";
    echo "<pre>" . implode("\n", $recentLogs) . "</pre>";
} else {
    echo "No error log file found or accessible.<br>";
}

echo "<hr>";
echo "<h3>Next Steps</h3>";
echo "<p>1. Check if all required files exist in htdocs</p>";
echo "<p>2. Verify database connection</p>";
echo "<p>3. Test PayPal API directly with curl or Postman</p>";
echo "<p>4. Check Apache/PHP error logs</p>";

?>
