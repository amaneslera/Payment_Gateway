<?php
// Test sales API with proper authentication
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Simulate the exact request that the frontend makes
    $jwt_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDg0NTg5NjMsImV4cCI6MTc0ODQ2MjU2MywidXNlcl9pZCI6MTAxLCJ1c2VybmFtZSI6IkRyYWluZSIsInJvbGUiOiJBZG1pbiIsImVtYWlsIjoiZHJhaW5lQGdtYWlsLmNvbSJ9.J-sQ85y8Hg-TFvShQqbNAHpIhAIzwsqUWH1K9fNYyII";
    
    // Test the summary endpoint
    $url = "http://localhost/Payment_Gateway/src/backend/api/sales/sales_api.php?action=summary";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $jwt_token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo json_encode([
        'success' => true,
        'http_code' => $httpCode,
        'response' => $response,
        'curl_error' => $error,
        'response_decoded' => json_decode($response, true)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
