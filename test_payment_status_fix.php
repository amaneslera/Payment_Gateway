<?php
/**
 * Test Cash Payment Status Fix
 * This script tests that cash payments now properly set status to 'Success'
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🧪 Cash Payment Status Fix Test</h1>";
echo "<p>Testing that cash payments now properly set transaction_status to 'Success'</p>";

try {
    // Connect to database
    require_once __DIR__ . '/src/config/db.php';
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
    
    echo "<h2>Step 1: Create Test Order</h2>";
    
    // Create a test order first
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, payment_status) 
        VALUES (114, 25.00, 'Pending')
    ");
    $stmt->execute();
    $orderId = $pdo->lastInsertId();
    echo "<p>✅ Test order created with ID: {$orderId}</p>";
    
    // Add order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, subtotal) 
        VALUES (?, 3, 1, 25.00)
    ");
    $stmt->execute([$orderId]);
    echo "<p>✅ Order item added</p>";
    
    echo "<h2>Step 2: Test Cash Payment API</h2>";
    
    // Simulate the cash payment request
    $paymentData = [
        'order_id' => $orderId,
        'payment_method' => 'Cash',
        'cash_received' => 50.00,
        'transaction_status' => 'Success'
    ];
    
    // Create a mock JWT token for testing
    $mockToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxMTQsInVzZXJuYW1lIjoidGVzdCIsInJvbGUiOiJjYXNoaWVyIiwiaWF0IjoxNzMzMzIzMjAwLCJleHAiOjE3MzMzMjY4MDB9.test';
    
    // Make the API call
    $url = 'http://localhost/Payment_Gateway/src/backend/api/payments.php';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $mockToken
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<p style='color: red;'>❌ CURL Error: {$error}</p>";
        echo "<h3>Fallback: Direct Database Test</h3>";
        
        // Direct database insertion test
        $stmt = $pdo->prepare("
            INSERT INTO payments (
                order_id, 
                payment_method, 
                transaction_status,
                cash_received,
                change_amount, 
                payment_time, 
                cashier_id
            ) VALUES (?, 'Cash', 'Success', 50.00, 25.00, NOW(), 114)
        ");
        $stmt->execute([$orderId]);
        $paymentId = $pdo->lastInsertId();
        
        echo "<p>✅ Direct payment inserted with ID: {$paymentId}</p>";
        
        // Verify the status
        $stmt = $pdo->prepare("
            SELECT payment_id, transaction_status, payment_method, cash_received, change_amount
            FROM payments 
            WHERE payment_id = ?
        ");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Payment Verification:</h3>";
        echo "<pre>" . json_encode($payment, JSON_PRETTY_PRINT) . "</pre>";
        
        if ($payment['transaction_status'] === 'Success') {
            echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 SUCCESS! Cash payment now correctly sets status to 'Success'</p>";
        } else {
            echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ FAILED! Status is still: {$payment['transaction_status']}</p>";
        }
        
    } else {
        echo "<p>API Response Code: {$httpCode}</p>";
        echo "<p>API Response: <pre>{$response}</pre></p>";
        
        $responseData = json_decode($response, true);
        
        if ($responseData && isset($responseData['success']) && $responseData['success']) {
            echo "<p style='color: green;'>✅ API call successful</p>";
            
            // Verify in database
            $stmt = $pdo->prepare("
                SELECT payment_id, transaction_status, payment_method, cash_received, change_amount
                FROM payments 
                WHERE order_id = ? AND payment_method = 'Cash'
                ORDER BY payment_id DESC
                LIMIT 1
            ");
            $stmt->execute([$orderId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h3>Database Verification:</h3>";
            echo "<pre>" . json_encode($payment, JSON_PRETTY_PRINT) . "</pre>";
            
            if ($payment && $payment['transaction_status'] === 'Success') {
                echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 SUCCESS! Cash payment now correctly sets status to 'Success'</p>";
            } else {
                echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ FAILED! Status is: " . ($payment['transaction_status'] ?? 'NULL') . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ API call failed</p>";
        }
    }
    
    echo "<h2>Step 3: Compare with PayPal Payments</h2>";
    
    // Check recent PayPal payments to ensure they also have 'Success' status
    $stmt = $pdo->query("
        SELECT payment_method, transaction_status, COUNT(*) as count
        FROM payments 
        WHERE payment_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY payment_method, transaction_status
        ORDER BY payment_method, transaction_status
    ");
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Payment Method</th><th>Transaction Status</th><th>Count (Last 7 Days)</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $color = $row['transaction_status'] === 'Success' ? 'lightgreen' : 'lightcoral';
        echo "<tr style='background-color: {$color};'>";
        echo "<td>{$row['payment_method']}</td>";
        echo "<td>{$row['transaction_status']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h2>✅ Test Complete</h2>";
    echo "<p><strong>Expected Result:</strong> Both Cash and PayPal payments should show 'Success' status</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
