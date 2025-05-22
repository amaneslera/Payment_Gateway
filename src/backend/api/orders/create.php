<?php
// Enable error reporting but capture it instead of displaying
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Buffer output to prevent any unwanted content
ob_start();

// Set proper headers for API and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');  
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// For debugging - will be hidden from output but stored in logs
error_log("Orders create.php script started");

try {
    // Include required files
    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../middleware/auth_middleware.php';    // Check if user is authenticated
    $user = AuthMiddleware::validateToken();

    if (!$user) {
        throw new Exception('Unauthorized access');
    }    
    
    // Log user data without assuming structure
    if (is_array($user)) {
        error_log("User authenticated (array): " . $user['user_id']);
    } elseif (is_object($user)) {
        error_log("User authenticated (object): " . $user->user_id);
    } else {
        error_log("User authenticated (unknown format): " . print_r($user, true));
    }

    // Only accept POST requests for order creation
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Get request data
    $jsonData = file_get_contents('php://input');
    error_log("Received JSON: " . $jsonData);
    
    $data = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // Validate required fields
    if (!isset($data['items']) || empty($data['items'])) {
        throw new Exception('Missing required field: items');
    }
    
    // Use the database connection
    $conn = getConnection();  // This returns a mysqli connection
    
    // Start transaction
    $conn->begin_transaction();
      error_log("Starting transaction");
    
    // Create the order record - EXACTLY matching database schema
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id,
            total_amount,
            payment_status
        ) VALUES (?, ?, ?)
    ");
    
    // Determine user_id based on user data structure
    $userId = null;
    if (is_array($user)) {
        error_log("User data is an array: " . json_encode($user));
        $userId = $user['user_id'];
    } 
    elseif (is_object($user)) {
        error_log("User data is an object: " . json_encode($user));
        $userId = $user->user_id;
    }
    else {
        error_log("User data is unexpected type: " . gettype($user) . " - " . print_r($user, true));
        throw new Exception("User authentication data is in an unexpected format");
    }
    
    $totalAmount = $data['total_amount'] ?? 0;
    $paymentStatus = $data['payment_status'] ?? 'Pending';
    
    $stmt->bind_param("ids", $userId, $totalAmount, $paymentStatus);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create order: " . $stmt->error . " (Code: " . $stmt->errno . ")");
    }
    
    $orderId = $conn->insert_id;
    error_log("Created order ID: " . $orderId);
      // Insert order items - using fields that exist in the schema
    $itemStmt = $conn->prepare("
        INSERT INTO order_items (
            order_id,
            product_id,
            quantity,
            subtotal
        ) VALUES (?, ?, ?, ?)
    ");
      // Prepare statement for checking inventory availability
    $checkInventoryStmt = $conn->prepare("
        SELECT name, stock_quantity 
        FROM products 
        WHERE product_id = ?
    ");
    
    // Prepare statement for updating inventory quantity
    $updateInventoryStmt = $conn->prepare("
        UPDATE products 
        SET stock_quantity = stock_quantity - ? 
        WHERE product_id = ? AND stock_quantity >= ?
    ");
    
    foreach ($data['items'] as $item) {
        $subtotal = $item['quantity'] * $item['price'];
        
        error_log("Adding item: product_id=" . $item['product_id'] . ", quantity=" . $item['quantity'] . ", subtotal=" . $subtotal);
        
        $itemStmt->bind_param("iidd", 
            $orderId,
            $item['product_id'], 
            $item['quantity'],
            $subtotal
        );
        
        if (!$itemStmt->execute()) {
            throw new Exception("Failed to add order item: " . $itemStmt->error . " (Code: " . $itemStmt->errno . ")");
        }
          // Check if there's enough inventory available
        $checkInventoryStmt->bind_param("i", $item['product_id']);
        if (!$checkInventoryStmt->execute()) {
            throw new Exception("Failed to check inventory: " . $checkInventoryStmt->error . " (Code: " . $checkInventoryStmt->errno . ")");
        }
        
        $result = $checkInventoryStmt->get_result();
        $product = $result->fetch_assoc();
        
        if (!$product) {
            throw new Exception("Product ID " . $item['product_id'] . " not found in inventory");
        }
        
        if ($product['stock_quantity'] < $item['quantity']) {
            throw new Exception("Insufficient inventory for product: " . $product['name'] . ". Available: " . $product['stock_quantity'] . ", Requested: " . $item['quantity']);
        }
        
        // Update inventory quantity
        $updateInventoryStmt->bind_param("dii", 
            $item['quantity'],
            $item['product_id'],
            $item['quantity']
        );
        
        if (!$updateInventoryStmt->execute()) {
            throw new Exception("Failed to update inventory quantity: " . $updateInventoryStmt->error . " (Code: " . $updateInventoryStmt->errno . ")");
        }
        
        // Check if any rows were affected
        if ($updateInventoryStmt->affected_rows <= 0) {
            throw new Exception("Failed to update inventory for product ID " . $item['product_id'] . ". Stock might be insufficient.");
        }
        
        error_log("Updated inventory: reduced product_id=" . $item['product_id'] . " quantity by " . $item['quantity']);
    }    // Create inventory transaction logs
    $logStmt = $conn->prepare("
        INSERT INTO inventory_transactions (
            product_id,
            transaction_type,
            quantity_change,
            transaction_date,
            reference_id,
            user_id
        ) VALUES (?, 'sale', ?, NOW(), ?, ?)
    ");
    
    // Log each product inventory change
    foreach ($data['items'] as $item) {
        // For sales, quantity_change should be negative as it's reducing inventory
        $negativeQuantity = -1 * abs($item['quantity']);
        $logStmt->bind_param("iiii", 
            $item['product_id'],
            $negativeQuantity,
            $orderId,
            $userId
        );
          // We don't throw exceptions here as logging should not stop the transaction
        if (!$logStmt->execute()) {
            error_log("Warning: Failed to log inventory transaction: " . $logStmt->error);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $orderId
    ]);
    
} catch (Exception $e) {
    // Clear any existing output
    ob_clean();
    
    // Rollback transaction if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }
    
    // Log the complete error details
    error_log("Order create error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Add more detailed error logging
    error_log("Request data: " . print_r($_REQUEST, true));
    error_log("JSON Input: " . file_get_contents('php://input'));
    error_log("Server variables: " . print_r($_SERVER, true));
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
