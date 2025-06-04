<?php
// Enable error reporting but capture it instead of displaying
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
});

// Buffer output to prevent any unwanted content
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {    // Fix the path to point to the correct location of db.php
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../middleware/auth_middleware.php';

    // Log debugging info
    error_log("payments.php script started");
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    
    if (function_exists('getallheaders')) {
        error_log("Authorization header: " . (getallheaders()['Authorization'] ?? 'NOT FOUND'));
    }
    
    // Check if user is authenticated
    $user = AuthMiddleware::validateToken();
    
    // Log the user data for debugging
    error_log("User authentication result: " . ($user ? json_encode($user) : "Authentication failed"));

    if (!$user) {
        throw new Exception('Unauthorized access');
    }

    // Process request based on method
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'POST':
            processPayment();
            break;
        case 'GET':
            if (isset($_GET['order_id'])) {
                getPaymentByOrderId($_GET['order_id']);
            } else {
                getPayments();
            }
            break;
        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    // Clear any output that might have been generated
    ob_clean();
    
    // Return proper JSON error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
    ]);
}

/**
 * Process a new payment
 */
function processPayment() {
    global $user;
    
    // Get request data
    $rawData = file_get_contents('php://input');
    error_log('Raw payment request data: ' . $rawData);
    
    $data = json_decode($rawData, true);
    
    // Check for JSON decode errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg() . ' in data: ' . $rawData);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data: ' . json_last_error_msg()
        ]);
        exit;
    }
    
    // Validate required fields
    if (!isset($data['order_id']) || !isset($data['payment_method'])) {
        $missingFields = [];
        if (!isset($data['order_id'])) $missingFields[] = 'order_id';
        if (!isset($data['payment_method'])) $missingFields[] = 'payment_method';
        
        error_log('Missing required fields: ' . implode(', ', $missingFields));
        error_log('Received data: ' . json_encode($data));
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missingFields)
        ]);
        exit;
    }
    
    // Validate order_id is a valid integer
    if (!is_numeric($data['order_id']) || $data['order_id'] <= 0) {
        error_log('Invalid order_id value: ' . $data['order_id']);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid order_id value. Must be a positive number.'
        ]);
        exit;
    }
      // Validate payment method-specific fields
    if ($data['payment_method'] === 'Cash') {
        // Check if cash_received is provided
        if (!isset($data['cash_received'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Cash payment requires cash_received amount'
            ]);
            return;
        }
        
        // Validate cash_received is a valid number
        $cashReceived = $data['cash_received'];
        if (!is_numeric($cashReceived)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid amount: Cash received must be a valid number'
            ]);
            return;
        }
        
        // Convert to float and check if it's positive
        $cashReceived = floatval($cashReceived);
        if ($cashReceived <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid amount: Cash received must be greater than zero'
            ]);
            return;
        }
        
        // Check for reasonable upper limit (optional)
        if ($cashReceived > 1000000) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid amount: Cash received amount is too large'
            ]);
            return;
        }
    }
    
    if ($data['payment_method'] === 'PayPal' && !isset($data['paypal_transaction_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'PayPal payment requires transaction_id'
        ]);
        return;
    }

    try {
        // Use global function instead of Database class
        $conn = getConnection();
        
        if (!$conn) {
            throw new Exception("Failed to connect to database");
        }

        // Disable autocommit and start transaction
        $conn->autocommit(false);
        
        error_log("Starting cash payment transaction for order_id: " . $data['order_id']);
        
        // First validate the order exists and get the total
        $orderStmt = $conn->prepare("
            SELECT SUM(oi.quantity * p.price) as total_amount, o.payment_status, o.user_id
            FROM orders o 
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.order_id = ?
            GROUP BY o.order_id, o.payment_status, o.user_id
        ");
        
        if (!$orderStmt) {
            throw new Exception("Failed to prepare order query: " . $conn->error);
        }
        
        $orderStmt->bind_param("i", $data['order_id']);
        
        if (!$orderStmt->execute()) {
            throw new Exception("Failed to execute order query: " . $orderStmt->error);
        }
        
        $orderResult = $orderStmt->get_result();
        
        if ($orderResult->num_rows === 0) {
            throw new Exception("Order not found with ID: " . $data['order_id']);
        }
        
        $order = $orderResult->fetch_assoc();
        $totalAmount = floatval($order['total_amount']);
        
        error_log("Order found. Total amount: $totalAmount, Current payment status: " . $order['payment_status']);
        
        // For cash payments, validate the amount is sufficient
        $cashReceived = null;
        $changeAmount = null;
          if ($data['payment_method'] === 'Cash') {
            $cashReceived = floatval($data['cash_received']);
            
            if ($cashReceived < $totalAmount) {
                $shortage = $totalAmount - $cashReceived;
                throw new Exception("Insufficient cash payment. Total required: ₱{$totalAmount}, Cash received: ₱{$cashReceived}, Short by: ₱{$shortage}");
            }
            
            $changeAmount = $cashReceived - $totalAmount;
            error_log("Cash payment - Received: $cashReceived, Change: $changeAmount");
        }
        
        // Get user ID properly
        $userId = null;
        if (is_array($user)) {
            $userId = $user['user_id'];
        } elseif (is_object($user)) {
            $userId = $user->user_id;
        } else {
            throw new Exception("User authentication data is in an unexpected format");
        }
          error_log("Processing payment for user ID: $userId");
        error_log("Payment data received: " . json_encode($data));
        error_log("Cash received: $cashReceived, Change amount: $changeAmount");
        
        // Create the payment record - let database handle the default transaction_status
        $paypalTransactionId = isset($data['paypal_transaction_id']) ? $data['paypal_transaction_id'] : null;
          error_log("About to insert payment with status: Success");
        
        // Force transaction_status to 'Success' directly in SQL for all payments
        $stmt = $conn->prepare("
            INSERT INTO payments (
                order_id, 
                payment_method, 
                transaction_status,
                cash_received,
                change_amount, 
                paypal_transaction_id, 
                payment_time, 
                cashier_id
            ) VALUES (?, ?, 'Success', ?, ?, ?, NOW(), ?)
        ");
        
        if (!$stmt) {
            throw new Exception("Failed to prepare payment insert query: " . $conn->error);
        }
        
        // Bind parameters - transaction_status is hardcoded as 'Success' in SQL
        $stmt->bind_param(
            "isddsi",
            $data['order_id'],
            $data['payment_method'],
            $cashReceived,
            $changeAmount,
            $paypalTransactionId,
            $userId
        );
          if (!$stmt->execute()) {
            throw new Exception("Failed to insert payment: " . $stmt->error);
        }
        
        $paymentId = $conn->insert_id;
        error_log("Payment record created with ID: $paymentId - Should have status: Success");
        
        // Verify the payment was inserted with correct status
        $verifyStmt = $conn->prepare("
            SELECT payment_id, transaction_status, payment_method, cash_received, change_amount
            FROM payments 
            WHERE payment_id = ?
        ");
        $verifyStmt->bind_param("i", $paymentId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        $savedPayment = $verifyResult->fetch_assoc();
          error_log("Payment verification after insert: " . json_encode($savedPayment));
        
        // Update order status to Paid
        $updateOrderStmt = $conn->prepare("
            UPDATE orders 
            SET payment_status = 'Paid'
            WHERE order_id = ?
        ");
        
        if (!$updateOrderStmt) {
            throw new Exception("Failed to prepare order update query: " . $conn->error);
        }
        
        $updateOrderStmt->bind_param("i", $data['order_id']);
        
        if (!$updateOrderStmt->execute()) {
            throw new Exception("Failed to update order status: " . $updateOrderStmt->error);
        }
        
        if ($updateOrderStmt->affected_rows === 0) {
            error_log("Warning: Order update affected 0 rows for order_id: " . $data['order_id']);
        } else {
            error_log("Order status updated successfully for order_id: " . $data['order_id']);
        }
        
        // Commit transaction
        if (!$conn->commit()) {
            throw new Exception("Failed to commit transaction: " . $conn->error);
        }
        
        error_log("Transaction committed successfully");
        
        // Re-enable autocommit
        $conn->autocommit(true);
        
        // Verify the payment was actually saved
        $verifyStmt = $conn->prepare("
            SELECT payment_id, transaction_status, payment_method 
            FROM payments 
            WHERE payment_id = ?
        ");
        $verifyStmt->bind_param("i", $paymentId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        $savedPayment = $verifyResult->fetch_assoc();
        
        error_log("Payment verification: " . json_encode($savedPayment));
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => [
                'payment_id' => $paymentId,
                'order_id' => $data['order_id'],
                'payment_method' => $data['payment_method'],
                'total_amount' => $totalAmount,
                'cash_received' => $cashReceived,
                'change_amount' => $changeAmount,
                'transaction_status' => 'Success',
                'payment_time' => date('Y-m-d H:i:s')
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if (isset($conn)) {
            $conn->rollback();
            $conn->autocommit(true);
        }
        
        // Add more detailed error logging
        error_log("Payment processing error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        error_log("Request data: " . json_encode($data));
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Get payment details for a specific order
 */
function getPaymentByOrderId($orderId) {
    try {
        // Use global function instead of Database class
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            SELECT p.*, u.username as cashier_name
            FROM payments p
            LEFT JOIN users u ON p.cashier_id = u.user_id
            WHERE p.order_id = ?
        ");
        
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'No payment found for this order'
            ]);
            return;
        }
        
        $payment = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'data' => $payment
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Get list of payments with pagination
 */
function getPayments() {
    try {
        // Use global function instead of Database class
        $conn = getConnection();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        // Get total count for pagination
        $countStmt = $conn->query("SELECT COUNT(*) as total FROM payments");
        $totalResult = $countStmt->fetch_assoc();
        $totalRecords = $totalResult['total'];
        
        // Get paginated results
        $stmt = $conn->prepare("
            SELECT p.*, u.username as cashier_name
            FROM payments p
            LEFT JOIN users u ON p.cashier_id = u.user_id
            ORDER BY p.payment_time DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $payments,
            'pagination' => [
                'total' => $totalRecords,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalRecords / $limit)
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>