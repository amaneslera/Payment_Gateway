<?php
// Direct test of sales API to debug the issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Sales API Direct Test</h2>";

// Test the sales API directly
echo "<h3>Testing Sales API Endpoint</h3>";

// Capture any output from the API
ob_start();
$_GET['action'] = 'categories';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Include the API file
try {
    include __DIR__ . '/src/backend/api/sales/sales_api.php';
} catch (Exception $e) {
    echo "Error including API: " . $e->getMessage();
}

$output = ob_get_clean();

echo "<strong>API Output:</strong><br>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Check if output is valid JSON
$json = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "<p>✅ Valid JSON response</p>";
    echo "<strong>Parsed JSON:</strong><br>";
    echo "<pre>" . print_r($json, true) . "</pre>";
} else {
    echo "<p>❌ Invalid JSON response</p>";
    echo "<strong>JSON Error:</strong> " . json_last_error_msg() . "<br>";
}
?>
