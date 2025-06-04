<?php
/**
 * Comprehensive test for cash payment validation improvements
 * Tests various invalid amount scenarios
 */

header('Content-Type: text/html; charset=UTF-8');

// Include database connection
require_once 'config/db.php';

echo "<h1>Cash Payment Validation Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>";

// Test cases for invalid amounts
$testCases = [
    [
        'name' => 'Empty cash_received',
        'data' => [
            'order_id' => 1,
            'payment_method' => 'Cash'
            // Missing cash_received
        ]
    ],
    [
        'name' => 'Non-numeric cash_received',
        'data' => [
            'order_id' => 1,
            'payment_method' => 'Cash',
            'cash_received' => 'abc123'
        ]
    ],
    [
        'name' => 'Zero cash_received',
        'data' => [
            'order_id' => 1,
            'payment_method' => 'Cash',
            'cash_received' => 0
        ]
    ],
    [
        'name' => 'Negative cash_received',
        'data' => [
            'order_id' => 1,
            'payment_method' => 'Cash',
            'cash_received' => -50
        ]
    ],
    [
        'name' => 'Too large cash_received',
        'data' => [
            'order_id' => 1,
            'payment_method' => 'Cash',
            'cash_received' => 2000000
        ]
    ],
    [
        'name' => 'Insufficient cash_received',
        'data' => [
            'order_id' => 1,
            'payment_method' => 'Cash',
            'cash_received' => 10 // Assuming order total is higher
        ]
    ],
    [
        'name' => 'Valid cash_received',
        'data' => [
            'order_id' => 1,
            'payment_method' => 'Cash',
            'cash_received' => 1000
        ]
    ]
];

// First, let's create a test order
echo "<div class='test-section'>";
echo "<h2>Setting up test data...</h2>";

try {
    $conn = getConnection();
    
    // Check if test products exist
    $productCheck = $conn->query("SELECT COUNT(*) as count FROM products LIMIT 1");
    $productCount = $productCheck->fetch_assoc()['count'];
    
    if ($productCount == 0) {
        echo "<div class='error'>No products found. Adding test product...</div>";
        $conn->query("INSERT INTO products (name, price, barcode, stock_quantity) VALUES ('Test Product', 100.00, 'TEST001', 50)");
    }
    
    // Create a test order
    $conn->query("DELETE FROM orders WHERE user_id = 999"); // Clean up previous test orders
    $conn->query("INSERT INTO orders (user_id, payment_status, order_date) VALUES (999, 'Pending', NOW())");
    $testOrderId = $conn->insert_id;
    
    // Add order items
    $productResult = $conn->query("SELECT product_id FROM products LIMIT 1");
    $product = $productResult->fetch_assoc();
    $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($testOrderId, {$product['product_id']}, 2, 100.00)");
    
    echo "<div class='success'>Test order created with ID: $testOrderId (Total: ₱200.00)</div>";
    
    // Update test cases to use the actual order ID
    foreach ($testCases as &$case) {
        $case['data']['order_id'] = $testOrderId;
    }
    
} catch (Exception $e) {
    echo "<div class='error'>Setup error: " . $e->getMessage() . "</div>";
    exit;
}

echo "</div>";

// Mock JWT token for testing
$mockToken = 'test_token_for_validation';

// Test each validation case
foreach ($testCases as $case) {
    echo "<div class='test-section'>";
    echo "<h3>Test: {$case['name']}</h3>";
    echo "<div class='info'>Data: " . json_encode($case['data'], JSON_PRETTY_PRINT) . "</div>";
    
    // Make request to payments API
    $url = 'http://localhost/Payment_Gateway/src/backend/api/payments.php';
    
    $postData = json_encode($case['data']);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $mockToken
            ],
            'content' => $postData,
            'ignore_errors' => true
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $httpCode = null;
    
    // Extract HTTP status code
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (strpos($header, 'HTTP/') === 0) {
                $httpCode = (int)substr($header, 9, 3);
                break;
            }
        }
    }
    
    echo "<div class='info'>HTTP Status: $httpCode</div>";
    
    if ($response) {
        $responseData = json_decode($response, true);
        if ($responseData) {
            echo "<div class='info'>Response:</div>";
            echo "<pre>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre>";
            
            // Validate response
            if ($case['name'] === 'Valid cash_received') {
                if ($responseData['success'] === true) {
                    echo "<div class='success'>✓ Valid payment processed correctly</div>";
                } else {
                    echo "<div class='error'>✗ Valid payment should have succeeded</div>";
                }
            } else {
                if ($responseData['success'] === false && isset($responseData['message'])) {
                    echo "<div class='success'>✓ Invalid payment rejected with message: {$responseData['message']}</div>";
                } else {
                    echo "<div class='error'>✗ Invalid payment should have been rejected</div>";
                }
            }
        } else {
            echo "<div class='error'>Invalid JSON response: $response</div>";
        }
    } else {
        echo "<div class='error'>No response received</div>";
    }
    
    echo "</div>";
}

// Clean up test data
echo "<div class='test-section'>";
echo "<h2>Cleaning up test data...</h2>";
try {
    $conn->query("DELETE FROM order_items WHERE order_id = $testOrderId");
    $conn->query("DELETE FROM orders WHERE order_id = $testOrderId");
    echo "<div class='success'>Test data cleaned up successfully</div>";
} catch (Exception $e) {
    echo "<div class='error'>Cleanup error: " . $e->getMessage() . "</div>";
}
echo "</div>";

echo "<h2>Frontend Validation Test</h2>";
echo "<div class='test-section'>";
echo "<p>To test frontend validation:</p>";
echo "<ol>";
echo "<li>Open the cashier page</li>";
echo "<li>Add some items to cart</li>";
echo "<li>Click the CASH button</li>";
echo "<li>Try entering these values in the cash received field:</li>";
echo "<ul>";
echo "<li>Empty field - should show 'Please enter the amount received'</li>";
echo "<li>'abc' - should show 'Invalid amount: Please enter a valid number'</li>";
echo "<li>'0' - should show 'Invalid amount: Amount must be greater than zero'</li>";
echo "<li>'-50' - should show 'Invalid amount: Amount must be greater than zero'</li>";
echo "<li>'2000000' - should show 'Invalid amount: Amount is too large'</li>";
echo "<li>Amount less than total - should show 'Insufficient payment: Need ₱X.XX more'</li>";
echo "<li>Valid amount - should calculate change correctly</li>";
echo "</ul>";
echo "<li>The change amount field should show error messages in red</li>";
echo "<li>The Complete Payment button should be disabled for invalid amounts</li>";
echo "</ol>";
echo "</div>";

?>
