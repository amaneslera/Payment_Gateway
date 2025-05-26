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
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Include required files
    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../middleware/auth_middleware.php';

    // Check if user is authenticated
    $user = AuthMiddleware::validateToken();
    
    // Log the user data for debugging
    error_log("User authentication result: " . ($user ? json_encode($user) : "Authentication failed"));

    if (!$user) {
        throw new Exception('Unauthorized access');
    }

    // Process request based on method
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method !== 'GET') {
        throw new Exception('Method not allowed');
    }

    // Check if requesting a specific transaction
    if (isset($_GET['transaction_id'])) {
        // Fetch single transaction details
        getTransactionById($_GET['transaction_id']);
    } else {
        // Get transactions with filtering options
        getTransactions();
    }

} catch (Exception $e) {
    // Clear any existing output
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
 * Get details of a specific transaction by ID
 */
function getTransactionById($transactionId) {
    try {
        // Use global function instead of Database class
        $conn = getConnection();
        
        // Validate transaction ID
        $transactionId = intval($transactionId);
        if ($transactionId <= 0) {
            throw new Exception('Invalid transaction ID');
        }
          // Prepare query to get transaction details
        $query = "
            SELECT 
                p.payment_id,
                p.order_id,
                p.payment_method,
                p.payment_time,
                p.transaction_status,
                p.cash_received,
                p.change_amount,
                p.paypal_transaction_id,
                u.username as cashier_name,
                o.total_amount            FROM 
                payments p
            LEFT JOIN 
                user u ON p.cashier_id = u.user_id
            LEFT JOIN 
                orders o ON p.order_id = o.order_id
            WHERE 
                p.payment_id = ?
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $transactionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Transaction not found');
        }
          $row = $result->fetch_assoc();
        
        // Format the data for frontend display
        $formattedDateTime = new DateTime($row['payment_time']);
          $transaction = [
            'transaction_id' => $row['payment_id'],
            'order_id' => $row['order_id'],
            'date' => $formattedDateTime->format('M d, Y'),
            'time' => $formattedDateTime->format('g:i a'),
            'transaction_type' => 'Sale', // Assuming all are sales, could vary based on your system
            'transaction_amount' => (float)$row['total_amount'],
            'payment_method' => $row['payment_method'],
            'payment_details' => $row['payment_method'] === 'Cash' 
                ? "Cash: ₱" . number_format((float)$row['cash_received'], 2) . ", Change: ₱" . number_format((float)$row['change_amount'], 2)
                : "PayPal ID: " . $row['paypal_transaction_id'],
            'invoice_no' => date('Ymd', $formattedDateTime->getTimestamp()) . '-' . str_pad($row['payment_id'], 3, '0', STR_PAD_LEFT),
            'cashier' => $row['cashier_name']
        ];
        
        // Get order items
        $orderItemsQuery = "
            SELECT 
                oi.product_id,
                p.name as product_name,
                oi.quantity,
                oi.subtotal
            FROM 
                order_items oi
            JOIN 
                products p ON oi.product_id = p.product_id
            WHERE 
                oi.order_id = ?
        ";
        
        $itemStmt = $conn->prepare($orderItemsQuery);
        $itemStmt->bind_param('i', $row['order_id']);
        $itemStmt->execute();
        $itemsResult = $itemStmt->get_result();
        
        $orderItems = [];
        while ($itemRow = $itemsResult->fetch_assoc()) {
            $orderItems[] = [
                'product_id' => $itemRow['product_id'],
                'product_name' => $itemRow['product_name'],
                'quantity' => (int)$itemRow['quantity'],
                'subtotal' => (float)$itemRow['subtotal'],
                'unit_price' => (float)$itemRow['subtotal'] / (int)$itemRow['quantity']
            ];
        }
        
        $transaction['items'] = $orderItems;
        
        // Get inventory changes
        $inventoryChangesQuery = "
            SELECT 
                it.transaction_id as inventory_transaction_id,
                it.quantity_change,
                it.transaction_date,
                p.name as product_name,
                p.stock_quantity as current_stock
            FROM 
                inventory_transactions it
            JOIN 
                products p ON it.product_id = p.product_id
            WHERE 
                it.reference_id = ? AND
                it.transaction_type = 'sale'
        ";
        
        $invStmt = $conn->prepare($inventoryChangesQuery);
        $invStmt->bind_param('i', $row['order_id']);
        $invStmt->execute();
        $invResult = $invStmt->get_result();
        
        $inventoryChanges = [];
        while ($invRow = $invResult->fetch_assoc()) {
            $inventoryChanges[] = [
                'inventory_transaction_id' => $invRow['inventory_transaction_id'],
                'product_name' => $invRow['product_name'],
                'quantity_change' => (int)$invRow['quantity_change'],
                'transaction_date' => $invRow['transaction_date'],
                'current_stock' => (int)$invRow['current_stock']
            ];
        }
        
        $transaction['inventory_changes'] = $inventoryChanges;
        
        echo json_encode([
            'success' => true,
            'data' => $transaction
        ]);
        
    } catch (Exception $e) {
        error_log("Transaction API error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Get list of transactions with pagination and optional filtering
 */
function getTransactions() {
    try {
        // Use global function instead of Database class
        $conn = getConnection();
        
        // Get filter parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
        
        $offset = ($page - 1) * $limit;
        
        // Build query conditions
        $conditions = [];
        $params = [];
        $types = '';
          if ($search) {
            $conditions[] = "(p.payment_id LIKE ? OR p.order_id LIKE ? OR u.username LIKE ? OR p.payment_method LIKE ? OR p.paypal_transaction_id LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'sssss';
        }
        
        if ($startDate) {
            $conditions[] = "DATE(p.payment_time) >= ?";
            $params[] = $startDate;
            $types .= 's';
        }
        
        if ($endDate) {
            $conditions[] = "DATE(p.payment_time) <= ?";
            $params[] = $endDate;
            $types .= 's';
        }
        
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(' AND ', $conditions);
        }
          // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM payments p LEFT JOIN user u ON p.cashier_id = u.user_id $whereClause";
        
        if (!empty($params)) {
            $countStmt = $conn->prepare($countQuery);
            $countStmt->bind_param($types, ...$params);
            $countStmt->execute();
            $totalResult = $countStmt->get_result()->fetch_assoc();
        } else {
            $countResult = $conn->query($countQuery);
            $totalResult = $countResult->fetch_assoc();
        }
        
        $totalRecords = $totalResult['total'];
        
        // Get paginated results
        $query = "
            SELECT 
                p.payment_id,
                p.order_id,
                p.payment_method,
                p.payment_time,
                p.transaction_status,
                p.cash_received,
                p.change_amount,
                p.paypal_transaction_id,
                u.username as cashier_name,
                o.total_amount            FROM 
                payments p
            LEFT JOIN 
                user u ON p.cashier_id = u.user_id
            LEFT JOIN 
                orders o ON p.order_id = o.order_id
            $whereClause
            ORDER BY p.payment_time DESC
            LIMIT ? OFFSET ?
        ";
        
        // Add limit and offset to params
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            // Format the data for frontend display
            $formattedDateTime = new DateTime($row['payment_time']);
              $transactions[] = [
                'transaction_id' => $row['payment_id'],
                'order_id' => $row['order_id'],
                'date' => $formattedDateTime->format('M d, Y'),
                'time' => $formattedDateTime->format('g:i a'),
                'transaction_type' => 'Sale', // Assuming all are sales, could vary based on your system
                'transaction_amount' => (float)$row['total_amount'],
                'payment_method' => $row['payment_method'],
                'invoice_no' => date('Ymd', $formattedDateTime->getTimestamp()) . '-' . str_pad($row['payment_id'], 3, '0', STR_PAD_LEFT),
                'cashier' => $row['cashier_name']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $transactions,
            'pagination' => [
                'total' => $totalRecords,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalRecords / $limit)
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Transaction API error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>
