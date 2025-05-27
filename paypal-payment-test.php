<?php
/**
 * Simplified PayPal Payment API for Testing
 * This version removes complex dependencies and focuses on basic functionality
 */

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'message' => 'CORS preflight handled']);
    exit;
}

// Log the request for debugging
error_log("PayPal API called with method: " . $_SERVER['REQUEST_METHOD']);

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }

    // Get raw input
    $input = file_get_contents('php://input');
    error_log("Raw input received: " . $input);

    if (empty($input)) {
        throw new Exception('No input data received');
    }

    // Parse JSON
    $data = json_decode($input, true);
    $jsonError = json_last_error();
    
    if ($jsonError !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    error_log("Parsed data: " . print_r($data, true));

    // Basic validation
    $required_fields = ['payment_method', 'paypal_transaction_id', 'cart_items', 'total_amount'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate payment method
    if ($data['payment_method'] !== 'paypal') {
        throw new Exception('Invalid payment method: ' . $data['payment_method']);
    }

    // Validate total amount
    $totalAmount = floatval($data['total_amount']);
    if ($totalAmount <= 0) {
        throw new Exception('Invalid total amount: ' . $totalAmount);
    }

    // Validate cart items
    if (empty($data['cart_items']) || !is_array($data['cart_items'])) {
        throw new Exception('Cart items are required and must be an array');
    }

    $paypalTransactionId = $data['paypal_transaction_id'];
    if (empty($paypalTransactionId)) {
        throw new Exception('PayPal transaction ID is required');
    }

    // At this point, basic validation passed
    error_log("Basic validation passed for transaction: " . $paypalTransactionId);

    // Skip PayPal API verification for now to test database insertion
    error_log("Skipping PayPal API verification for testing");    // Try to include config and database
    try {
        // Load configuration - fix path for htdocs linked workspace
        $configPath = __DIR__ . '/src/config/config.php';
        if (!file_exists($configPath)) {
            throw new Exception('Config file not found at: ' . $configPath);
        }
        require_once $configPath;
        error_log("Config loaded successfully from: " . $configPath);

        // Load database - fix path for htdocs linked workspace  
        $dbPath = __DIR__ . '/config/db.php';
        if (!file_exists($dbPath)) {
            throw new Exception('Database config file not found at: ' . $dbPath);
        }
        require_once $dbPath;
        error_log("Database connected successfully from: " . $dbPath);

    } catch (Exception $e) {
        error_log("Config/DB error: " . $e->getMessage());
        throw new Exception("Configuration error: " . $e->getMessage());
    }

    // Mock user for testing (skip authentication for now)
    $user = ['user_id' => 114, 'username' => 'testuser'];
    error_log("Using mock user for testing");

    // Start database transaction
    $pdo->beginTransaction();
    error_log("Database transaction started");

    try {
        // Calculate totals from cart items
        $subtotal = 0;
        foreach ($data['cart_items'] as $item) {
            $subtotal += floatval($item['price']) * intval($item['quantity']);
        }
        
        $tax = $subtotal * 0.12; // 12% VAT
        $calculatedTotal = $subtotal + $tax;

        // Allow small rounding differences
        if (abs($calculatedTotal - $totalAmount) > 0.01) {
            error_log("Total mismatch: calculated=$calculatedTotal, provided=$totalAmount");
            // Don't throw error for small differences, log and continue
        }

        error_log("Totals calculated: subtotal=$subtotal, tax=$tax, total=$calculatedTotal");        // Create order record using existing schema
        $orderStmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, payment_status) 
            VALUES (?, ?, 'Paid')
        ");
        $orderStmt->execute([$user['user_id'], $totalAmount]);
        $orderId = $pdo->lastInsertId();
        error_log("Order created with ID: " . $orderId);

        // Insert order items
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
            error_log("Order item added: product_id=" . $item['product_id']);
        }

        // Record PayPal payment
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
        ]);

        $paymentId = $pdo->lastInsertId();
        error_log("Payment recorded with ID: " . $paymentId);

        // Try to store PayPal details if table exists
        try {
            $paypalOrderDetails = $data['paypal_order_details'] ?? null;
            if ($paypalOrderDetails) {
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
                error_log("PayPal details stored");
            }
        } catch (Exception $e) {
            error_log("PayPal details storage failed: " . $e->getMessage());
            // Don't fail the whole transaction for this
        }

        // Commit transaction
        $pdo->commit();
        error_log("Transaction committed successfully");

        // Prepare response
        $responseData = [
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'paypal_transaction_id' => $paypalTransactionId,
            'total_amount' => $totalAmount,
            'payment_status' => 'Success',
            'order_status' => 'Completed',
            'transaction_time' => date('Y-m-d H:i:s')
        ];

        // Success response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'PayPal payment processed successfully',
            'data' => $responseData
        ]);

    } catch (Exception $e) {
        // Rollback transaction
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        error_log("Transaction error: " . $e->getMessage());
        throw $e;
    }

} catch (Exception $e) {
    error_log("PayPal payment error: " . $e->getMessage());
    
    // Clean any existing output
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'input_length' => strlen($input ?? ''),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
}

// Clean output buffer
if (ob_get_level()) {
    ob_end_flush();
}
?>
