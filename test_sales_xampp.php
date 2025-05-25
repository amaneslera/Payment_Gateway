<?php
// Test sales API through XAMPP
echo "<h2>Sales API Test through XAMPP</h2>";

// Test different endpoints
$baseUrl = "http://localhost/Payment_Gateway/src/backend/api/sales/sales_api.php";

$endpoints = [
    "Categories" => "?action=categories",
    "Sales Summary" => "?action=summary",
    "Sales Trends" => "?action=sales_trends&days=7"
];

foreach ($endpoints as $name => $endpoint) {
    echo "<h3>Testing: $name</h3>";
    
    $url = $baseUrl . $endpoint;
    echo "URL: <code>$url</code><br>";
    
    // Use file_get_contents to test the endpoint
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer test-token-for-testing'
            ],
            'timeout' => 10
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "<div style='color: red;'>❌ Failed to get response</div>";
        $error = error_get_last();
        if ($error) {
            echo "<div style='color: red;'>Error: " . $error['message'] . "</div>";
        }
    } else {
        echo "<div style='color: green;'>✅ Response received</div>";
        echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px 0;'>";
        echo "<strong>Response:</strong><br>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        echo "</div>";
        
        // Try to decode JSON
        $json = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<div style='color: green;'>✅ Valid JSON response</div>";
        } else {
            echo "<div style='color: red;'>❌ Invalid JSON: " . json_last_error_msg() . "</div>";
        }
    }
    
    echo "<hr>";
}
?>
