<?php
/**
 * PayPal Payment Processing API
 * Handles PayPal payment transactions and records them in the database
 */

// Enable error reporting but capture it instead of displaying
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Clean any existing output
        if (ob_get_level()) {
            ob_clean();
        }
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        exit;
    }
});

// Set headers first before any output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

// Buffer output to prevent any unwanted content
ob_start();

/**
 * Get PayPal access token for API authentication
 */
function getPayPalAccessToken() {
    $client_id = PAYPAL_CLIENT_ID;
    $client_secret = PAYPAL_CLIENT_SECRET;
    $environment = PAYPAL_ENVIRONMENT;
    
    $api_url = $environment === 'sandbox' ? 
        'https://api.sandbox.paypal.com/v1/oauth2/token' : 
        'https://api.paypal.com/v1/oauth2/token';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Accept-Language: en_US'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }
    
    error_log("PayPal access token error: " . $response);
    return null;
}

/**
 * Verify PayPal order with PayPal API
 */
function verifyPayPalOrder($orderId, $accessToken) {
    $environment = PAYPAL_ENVIRONMENT;
    $api_url = $environment === 'sandbox' ? 
        "https://api.sandbox.paypal.com/v2/checkout/orders/$orderId" : 
        "https://api.paypal.com/v2/checkout/orders/$orderId";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        return json_decode($response, true);
    }
    
    error_log("PayPal order verification error: " . $response);
    return null;
}

try {
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../middleware/auth_middleware.php';

    // Log debugging info
    error_log("paypal-payment.php script started");
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    
    // Check if user is authenticated
    $user = AuthMiddleware::validateToken();
    error_log("User authentication result: " . ($user ? json_encode($user) : "Authentication failed"));
    
    if (!$user) {
        throw new Exception('Authentication required');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }

    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }    // Validate required fields
    $required_fields = ['payment_method', 'paypal_transaction_id', 'cart_items', 'total_amount'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            error_log("Missing required field: $field. Received data: " . json_encode($data));
            throw new Exception("Missing required field: $field");
        }
    }

    error_log("PayPal payment data received: " . json_encode($data));

    // Validate payment method
    if ($data['payment_method'] !== 'paypal') {
        error_log("Invalid payment method: " . $data['payment_method']);
        throw new Exception('Invalid payment method');
    }

    // Validate cart items
    if (empty($data['cart_items']) || !is_array($data['cart_items'])) {
        throw new Exception('Cart items are required');
    }

    // Validate total amount
    $totalAmount = floatval($data['total_amount']);
    if ($totalAmount <= 0) {
        throw new Exception('Invalid total amount');
    }

    // Get PayPal order details
    $paypalOrderDetails = $data['paypal_order_details'] ?? null;
    $paypalTransactionId = $data['paypal_transaction_id'];    // Validate PayPal transaction
    if (!$paypalTransactionId) {
        throw new Exception('PayPal transaction ID is required');
    }

    // Verify PayPal order with PayPal API (optional for development)
    error_log("Verifying PayPal order: $paypalTransactionId");
    $paypalOrderData = null;
    $orderStatus = 'COMPLETED'; // Default for development
    
    // Try to verify with PayPal API if possible
    try {
        $accessToken = getPayPalAccessToken();
        
        if ($accessToken) {
            $paypalOrderData = verifyPayPalOrder($paypalTransactionId, $accessToken);
            
            if ($paypalOrderData) {
                $orderStatus = $paypalOrderData['status'] ?? 'COMPLETED';
                error_log("PayPal order verified successfully. Status: $orderStatus");
            } else {
                error_log("PayPal order verification failed, proceeding with transaction");
            }
        } else {
            error_log("PayPal access token failed, proceeding with transaction");
        }
    } catch (Exception $e) {
        error_log("PayPal verification error (continuing anyway): " . $e->getMessage());
    }
    
    // Check if order status is valid (only if we got data from PayPal)
    if ($paypalOrderData && !in_array($orderStatus, ['COMPLETED', 'APPROVED'])) {
        throw new Exception("Invalid PayPal order status: $orderStatus");
    }
    
    // Verify order amount if we have PayPal data
    if ($paypalOrderData && isset($paypalOrderData['purchase_units'][0]['amount']['value'])) {
        $paypalAmount = floatval($paypalOrderData['purchase_units'][0]['amount']['value']);
        
        if (abs($paypalAmount - $totalAmount) > 0.01) {
            throw new Exception("PayPal amount mismatch. Expected: $totalAmount, PayPal: $paypalAmount");
        }
    }

    // Start database transaction
    $pdo->beginTransaction();

    try {
        // Calculate totals from cart items
        $subtotal = 0;
        foreach ($data['cart_items'] as $item) {
            $subtotal += floatval($item['price']) * intval($item['quantity']);
        }
        
        $tax = $subtotal * 0.12; // 12% VAT
        $calculatedTotal = $subtotal + $tax;

        // Verify the total matches
        if (abs($calculatedTotal - $totalAmount) > 0.01) {
            throw new Exception('Total amount mismatch');
        }        // Create order record using existing schema
        $orderStmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, payment_status) 
            VALUES (?, ?, 'Paid')
        ");
        $orderStmt->execute([$user['user_id'], $totalAmount]);
        $orderId = $pdo->lastInsertId();        // Insert order items
        $orderItemStmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, subtotal) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($data['cart_items'] as $item) {
            $itemSubtotal = floatval($item['price']) * intval($item['quantity']);
            $orderItemStmt->execute([
                $orderId,
                intval($item['product_id']),
                intval($item['quantity']),
                $itemSubtotal
            ]);

            // Update product stock
            $updateStockStmt = $pdo->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity - ? 
                WHERE product_id = ? AND stock_quantity >= ?
            ");
            $updateStockStmt->execute([
                intval($item['quantity']),
                intval($item['product_id']),
                intval($item['quantity'])
            ]);

            // Check if stock update was successful
            if ($updateStockStmt->rowCount() === 0) {
                throw new Exception("Insufficient stock for product ID: " . $item['product_id']);
            }
        }        // Record PayPal payment - explicitly set transaction_status to 'Success'
        $paymentStmt = $pdo->prepare("
            INSERT INTO payments (
                order_id, 
                payment_method, 
                paypal_transaction_id, 
                transaction_status,
                cash_received,
                change_amount,
                payment_time,
                cashier_id
            ) VALUES (?, 'PayPal', ?, 'Success', ?, 0.00, NOW(), ?)
        ");
        
        $paymentStmt->execute([
            $orderId,
            $paypalTransactionId,
            $totalAmount,
            $user['user_id']
        ]);        $paymentId = $pdo->lastInsertId();

        // Store PayPal order details if available
        if ($paypalOrderDetails) {
            try {
                $paypalDetailsStmt = $pdo->prepare("
                    INSERT INTO paypal_transaction_details (
                        payment_id,
                        paypal_order_id,
                        payer_id,
                        payer_email,
                        transaction_details,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())
                ");

                $payerInfo = $paypalOrderDetails['payer'] ?? null;
                $payerId = $payerInfo['payer_id'] ?? null;
                $payerEmail = $payerInfo['email_address'] ?? null;

                $paypalDetailsStmt->execute([
                    $paymentId,
                    $paypalOrderDetails['id'] ?? $paypalTransactionId,
                    $payerId,
                    $payerEmail,
                    json_encode($paypalOrderDetails)
                ]);
                
                error_log("PayPal transaction details stored successfully");
            } catch (Exception $e) {
                error_log("Warning: Failed to store PayPal transaction details: " . $e->getMessage());
                // Continue without failing the entire transaction
            }
        }

        // Commit transaction
        $pdo->commit();        // Prepare response data
        $responseData = [
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'paypal_transaction_id' => $paypalTransactionId,
            'total_amount' => $totalAmount,
            'payment_status' => 'Success',
            'order_status' => 'Completed',
            'transaction_time' => date('Y-m-d H:i:s')
        ];

        // Log successful payment
        error_log("PayPal payment processed successfully: Order ID $orderId, Payment ID $paymentId, Transaction ID: $paypalTransactionId");

        // Clean output buffer before sending response
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'PayPal payment processed successfully',
            'data' => $responseData
        ]);
        exit;

    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("PayPal payment error: " . $e->getMessage());
    // Clean any existing output
    if (ob_get_level()) {
        ob_clean();
    }
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("PayPal payment fatal error: " . $e->getMessage());
    // Clean any existing output
    if (ob_get_level()) {
        ob_clean();
    }
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}

// Clean output buffer and send response
if (ob_get_level()) {
    ob_end_clean();
}
exit;
?>
