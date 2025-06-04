<?php
/**
 * Test script to verify cash payments now correctly save 'Success' status
 */

// Include database configuration
require_once 'config/db.php';
require_once 'vendor/autoload.php';

// For JWT token generation (simulate login)
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateTestToken($userId = 1) {
    $key = "your-secret-key-here-make-it-long-and-secure-2024";
    $payload = [
        'user_id' => $userId,
        'username' => 'admin',
        'role' => 'admin',
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60) // 24 hours
    ];
    return JWT::encode($payload, $key, 'HS256');
}

echo "<h2>Testing Cash Payment Fix</h2>\n";

try {
    $conn = getConnection();
    
    // 1. Create a test order first
    echo "<h3>Step 1: Creating test order...</h3>\n";
    
    $conn->begin_transaction();
    
    // Insert test order
    $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_status) VALUES (?, ?, 'Pending')");
    $userId = 1;
    $totalAmount = 25.50;
    $orderStmt->bind_param("id", $userId, $totalAmount);
    $orderStmt->execute();
    $orderId = $conn->insert_id;
    
    // Insert order items (need at least one for the payment validation)
    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
    $productId = 1; // Assuming product 1 exists
    $quantity = 1;
    $subtotal = 25.50;
    $itemStmt->bind_param("iiid", $orderId, $productId, $quantity, $subtotal);
    $itemStmt->execute();
    
    $conn->commit();
    
    echo "✓ Test order created with ID: $orderId<br>\n";
    
    // 2. Test cash payment through API
    echo "<h3>Step 2: Making cash payment through API...</h3>\n";
    
    $token = generateTestToken();
    $paymentData = [
        'order_id' => $orderId,
        'payment_method' => 'Cash',
        'cash_received' => 30.00,
        'transaction_status' => 'Success'
    ];
    
    // Make API call
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/Payment_Gateway/src/backend/api/payments.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode<br>\n";
    echo "API Response: " . htmlspecialchars($response) . "<br>\n";
    
    $responseData = json_decode($response, true);
    
    if ($responseData && $responseData['success']) {
        echo "✓ Payment API call successful<br>\n";
        $paymentId = $responseData['data']['payment_id'];
        
        // 3. Verify in database
        echo "<h3>Step 3: Verifying payment in database...</h3>\n";
        
        $checkStmt = $conn->prepare("
            SELECT payment_id, order_id, payment_method, transaction_status, cash_received, change_amount, payment_time 
            FROM payments 
            WHERE payment_id = ?
        ");
        $checkStmt->bind_param("i", $paymentId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $payment = $result->fetch_assoc();
            echo "✓ Payment found in database:<br>\n";
            echo "<pre>" . print_r($payment, true) . "</pre>\n";
            
            if ($payment['transaction_status'] === 'Success') {
                echo "<strong style='color: green;'>✓ SUCCESS: transaction_status is correctly set to 'Success'!</strong><br>\n";
            } else {
                echo "<strong style='color: red;'>✗ FAILED: transaction_status is '" . ($payment['transaction_status'] ?: 'NULL/EMPTY') . "' instead of 'Success'</strong><br>\n";
            }
        } else {
            echo "<strong style='color: red;'>✗ Payment not found in database</strong><br>\n";
        }
        
    } else {
        echo "<strong style='color: red;'>✗ Payment API call failed</strong><br>\n";
        if ($responseData) {
            echo "Error: " . $responseData['message'] . "<br>\n";
        }
    }
    
    // 4. Check all recent cash payments
    echo "<h3>Step 4: Checking all recent cash payments...</h3>\n";
    
    $recentStmt = $conn->prepare("
        SELECT payment_id, order_id, payment_method, transaction_status, payment_time 
        FROM payments 
        WHERE payment_method = 'Cash' 
        ORDER BY payment_time DESC 
        LIMIT 5
    ");
    $recentStmt->execute();
    $recentResult = $recentStmt->get_result();
    
    echo "<table border='1' cellpadding='5'>\n";
    echo "<tr><th>Payment ID</th><th>Order ID</th><th>Method</th><th>Status</th><th>Time</th></tr>\n";
    
    while ($row = $recentResult->fetch_assoc()) {
        $statusColor = $row['transaction_status'] === 'Success' ? 'green' : 'red';
        $statusText = $row['transaction_status'] ?: 'NULL/EMPTY';
        echo "<tr>";
        echo "<td>{$row['payment_id']}</td>";
        echo "<td>{$row['order_id']}</td>";
        echo "<td>{$row['payment_method']}</td>";
        echo "<td style='color: $statusColor; font-weight: bold;'>$statusText</td>";
        echo "<td>{$row['payment_time']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo "<strong style='color: red;'>Error: " . $e->getMessage() . "</strong><br>\n";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>\n";
}
?>
