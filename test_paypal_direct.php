<?php
/**
 * Direct PayPal API Test
 * Test the actual PayPal payment endpoint with a real HTTP request
 */

// Test data that matches what the frontend sends
$testData = [
    'payment_method' => 'paypal',
    'paypal_transaction_id' => 'TEST_TXN_123456',
    'paypal_order_details' => [
        'id' => 'TEST_ORDER_123456',
        'status' => 'COMPLETED',
        'purchase_units' => [
            [
                'amount' => [
                    'value' => '100.00',
                    'currency_code' => 'PHP'
                ]
            ]
        ],
        'payer' => [
            'payer_id' => 'TEST_PAYER_123',
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

// Test JWT token (we'll generate a valid one)
require_once 'vendor/autoload.php';
require_once 'src/config/config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Generate a test JWT token
$payload = [
    'user_id' => 114,
    'username' => 'testuser',
    'role' => 'cashier',
    'iat' => time(),
    'exp' => time() + 3600
];

$jwt_token = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');

echo "=== PayPal API Direct Test ===\n";
echo "Generated JWT token: " . substr($jwt_token, 0, 50) . "...\n";

// Make actual HTTP request to the API
$url = 'http://localhost/Payment_Gateway/src/backend/api/paypal-payment.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $jwt_token
]);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, fopen('php://temp', 'w+'));

echo "\nMaking request to: $url\n";
echo "Request method: POST\n";
echo "Content-Type: application/json\n";
echo "Authorization: Bearer [token]\n";
echo "Payload: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

echo "\n=== Response ===\n";
echo "HTTP Code: $httpCode\n";
echo "cURL Error: " . ($error ?: "None") . "\n";
echo "Response Length: " . strlen($response) . " bytes\n";
echo "Response Content:\n";
echo $response . "\n";

// Try to decode JSON response
if ($response) {
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\nJSON Decoded Successfully:\n";
        print_r($decoded);
    } else {
        echo "\nJSON Decode Error: " . json_last_error_msg() . "\n";
        echo "Raw response (first 500 chars):\n";
        echo substr($response, 0, 500) . "\n";
    }
}

curl_close($ch);

echo "\n=== Test Complete ===\n";
?>
