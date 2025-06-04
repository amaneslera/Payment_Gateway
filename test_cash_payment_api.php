<?php
/**
 * Test Cash Payment API Endpoint
 * This simulates exactly what the frontend does when making a cash payment
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Cash Payment API Test</h1>";
echo "<p>Testing the actual cash payment API endpoint</p>";

try {
    // First, let's create a test order like the frontend does
    require_once __DIR__ . '/src/config/db.php';
    
    echo "<h2>Step 1: Create Test Order</h2>";
    
    // Create a test order
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, payment_status) 
        VALUES (114, 25.00, 'Pending')
    ");
    $orderStmt->execute();
    $testOrderId = $pdo->lastInsertId();
    
    // Add order item
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, subtotal) 
        VALUES (?, 3, 1, 25.00)
    ");
    $itemStmt->execute([$testOrderId]);
    
    echo "<p>✅ Created test order ID: $testOrderId</p>";
    
    echo "<h2>Step 2: Simulate Frontend Cash Payment Request</h2>";
    
    // Simulate the exact data that the frontend sends
    $paymentData = [
        'order_id' => $testOrderId,
        'payment_method' => 'Cash',
        'cash_received' => 30.00,
        'transaction_status' => 'Success'  // Frontend tries to send this
    ];
    
    echo "<p>Payment data being sent:</p>";
    echo "<pre>" . json_encode($paymentData, JSON_PRETTY_PRINT) . "</pre>";
    
    // Create a mock JWT token for testing (you'll need to replace this with a real one)
    $testToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImlhdCI6MTczMzMyOTk2MCwiZXhwIjoxNzMzMzMzNTYwLCJ1c2VyX2lkIjoxMTQsInVzZXJuYW1lIjoiY2FzaGllciIsInJvbGUiOiJjYXNoaWVyIn0.Q-kL2zn3LGKzGYaE-uVJoXOdp5uTsxRnOcV3gT3qFgE';
    
    // Make HTTP request to the payments API
    $apiUrl = 'http://localhost/Payment_Gateway/src/backend/api/payments.php';
    
    // If localhost doesn't work, try the file path
    if (!@file_get_contents('http://localhost')) {
        echo "<p>Note: localhost not available, will test the PHP file directly</p>";
        
        // Test by including the file directly
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $testToken;
        
        // Mock the input
        $GLOBALS['HTTP_RAW_POST_DATA'] = json_encode($paymentData);
        
        // Capture output
        ob_start();
        
        // Set up environment to simulate POST request
        $_POST = $paymentData;
        
        // Include the payments API file
        try {
            include __DIR__ . '/src/backend/api/payments.php';
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error including payments.php: " . $e->getMessage() . "</p>";
        }
        
        $output = ob_get_clean();
        echo "<h3>API Response:</h3>";
        echo "<pre>$output</pre>";
        
    } else {
        // Use cURL to make the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $testToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<h3>API Response (HTTP $httpCode):</h3>";
        echo "<pre>$response</pre>";
    }
    
    echo "<h2>Step 3: Verify Payment in Database</h2>";
    
    // Check if payment was created
    $checkStmt = $pdo->prepare("
        SELECT * FROM payments 
        WHERE order_id = ? 
        ORDER BY payment_id DESC 
        LIMIT 1
    ");
    $checkStmt->execute([$testOrderId]);
    $payment = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($payment) {
        echo "<h3>Payment Record Found:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($payment as $field => $value) {
            $color = ($field === 'transaction_status' && $value === 'Success') ? 'green' : 'black';
            echo "<tr><td>$field</td><td style='color: $color; font-weight: bold;'>$value</td></tr>";
        }
        echo "</table>";
        
        if ($payment['transaction_status'] === 'Success') {
            echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ SUCCESS: Cash payment status is correctly set to 'Success'!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold; font-size: 18px;'>❌ FAILED: Cash payment status is '{$payment['transaction_status']}' instead of 'Success'</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No payment record found for order ID: $testOrderId</p>";
    }
    
    // Clean up
    echo "<h2>Cleanup</h2>";
    if ($payment) {
        $pdo->prepare("DELETE FROM payments WHERE payment_id = ?")->execute([$payment['payment_id']]);
    }
    $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$testOrderId]);
    $pdo->prepare("DELETE FROM orders WHERE order_id = ?")->execute([$testOrderId]);
    echo "<p>✅ Test data cleaned up</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
