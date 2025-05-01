<?php
// filepath: c:\GitHub\Payment_Gateway\Source Codes\PaymentSystem\backend\inventory_api.php

// Turn off error display, errors will be handled properly through JSON responses
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set proper headers for JSON API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';
require_once 'auth_middleware.php';

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Validate user authentication (except for GET operations)
if ($method !== 'GET') {
    $userData = AuthMiddleware::validateToken();
    if (!$userData) {
        exit; // Middleware handles the error response
    }
    
    // Only Admin or Manager can modify inventory
    if (!AuthMiddleware::checkRole($userData, ['Admin', 'Manager'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions']);
        exit;
    }
}

try {
    $pdo = getConnection();
    
    // Route based on the endpoint
    $endpoint = isset($_GET['action']) ? $_GET['action'] : 'products';
    
    switch ($endpoint) {
        case 'products':
            handleProductRequests($pdo, $method);
            break;
            
        case 'categories':
            handleCategoryRequests($pdo, $method);
            break;
            
        case 'suppliers':
            handleSupplierRequests($pdo, $method);
            break;
            
        case 'transactions':
            handleTransactionRequests($pdo, $method);
            break;
            
        case 'purchase-orders':
            handlePurchaseOrderRequests($pdo, $method);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

function handleProductRequests($pdo, $method) {
    switch ($method) {
        case 'GET':
            if (isset($_GET['product_id'])) {
                // Get specific product
                $stmt = $pdo->prepare("
                    SELECT p.*, c.category_name, s.name as supplier_name,
                    (p.price - COALESCE(p.cost_price, 0)) as profit_margin
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                    WHERE p.product_id = :product_id
                ");
                $stmt->execute(['product_id' => $_GET['product_id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    // Add stock status
                    if ($product['stock_quantity'] <= 0) {
                        $product['stock_status'] = 'Out of Stock';
                    } elseif ($product['stock_quantity'] <= $product['min_stock_level']) {
                        $product['stock_status'] = 'Low Stock';
                    } else {
                        $product['stock_status'] = 'In Stock';
                    }
                    
                    // Check expiry if it's a food item
                    if ($product['is_food'] && $product['expiry_date']) {
                        $today = new DateTime();
                        $expiry = new DateTime($product['expiry_date']);
                        $daysUntilExpiry = $today->diff($expiry)->days;
                        $product['days_until_expiry'] = $expiry > $today ? $daysUntilExpiry : -$daysUntilExpiry;
                    }
                    
                    echo json_encode($product);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
                }
            } else {
                // Get all products with optional filters
                $params = [];
                $where = [];
                
                // Apply filters if provided
                if (isset($_GET['category_id'])) {
                    $where[] = "p.category_id = :category_id";
                    $params['category_id'] = $_GET['category_id'];
                }
                
                if (isset($_GET['is_food'])) {
                    $where[] = "p.is_food = :is_food";
                    $params['is_food'] = $_GET['is_food'];
                }
                
                if (isset($_GET['low_stock'])) {
                    $where[] = "p.stock_quantity <= p.min_stock_level AND p.stock_quantity > 0";
                }
                
                if (isset($_GET['out_of_stock'])) {
                    $where[] = "p.stock_quantity <= 0";
                }
                
                if (isset($_GET['search'])) {
                    $where[] = "(p.name LIKE :search OR p.barcode LIKE :search OR p.sku LIKE :search)";
                    $params['search'] = '%' . $_GET['search'] . '%';
                }
                
                $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
                
                $sql = "
                    SELECT p.*, c.category_name, s.name as supplier_name,
                    (p.price - COALESCE(p.cost_price, 0)) as profit_margin
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                    $whereClause
                    ORDER BY p.name
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Add stock status for each product
                foreach ($products as &$product) {
                    if ($product['stock_quantity'] <= 0) {
                        $product['stock_status'] = 'Out of Stock';
                    } elseif ($product['stock_quantity'] <= $product['min_stock_level']) {
                        $product['stock_status'] = 'Low Stock';
                    } else {
                        $product['stock_status'] = 'In Stock';
                    }
                    
                    // Inventory value calculation
                    $product['inventory_value'] = $product['stock_quantity'] * $product['price'];
                }
                
                echo json_encode($products);
            }
            break;
            
        case 'POST':
            // Create new product
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['name']) || !isset($data['price'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Name and price are required']);
                exit;
            }
            
            $sql = "
                INSERT INTO products (
                    name, category_id, price, description, stock_quantity, 
                    cost_price, barcode, sku, product_image, is_food,
                    expiry_date, min_stock_level, supplier_id
                ) VALUES (
                    :name, :category_id, :price, :description, :stock_quantity,
                    :cost_price, :barcode, :sku, :product_image, :is_food,
                    :expiry_date, :min_stock_level, :supplier_id
                )
            ";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'name' => $data['name'],
                'category_id' => $data['category_id'] ?? null,
                'price' => $data['price'],
                'description' => $data['description'] ?? null,
                'stock_quantity' => $data['stock_quantity'] ?? 0,
                'cost_price' => $data['cost_price'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'sku' => $data['sku'] ?? null,
                'product_image' => $data['product_image'] ?? null,
                'is_food' => $data['is_food'] ?? 0,
                'expiry_date' => $data['expiry_date'] ?? null,
                'min_stock_level' => $data['min_stock_level'] ?? 5,
                'supplier_id' => $data['supplier_id'] ?? null
            ]);
            
            if ($result) {
                $productId = $pdo->lastInsertId();
                
                // Record the initial stock if provided
                if (isset($data['stock_quantity']) && $data['stock_quantity'] > 0) {
                    $transactionSql = "
                        INSERT INTO inventory_transactions (
                            product_id, quantity_change, transaction_type, 
                            notes, user_id
                        ) VALUES (
                            :product_id, :quantity_change, 'adjustment',
                            'Initial inventory setup', :user_id
                        )
                    ";
                    
                    $userData = AuthMiddleware::validateToken();
                    $pdo->prepare($transactionSql)->execute([
                        'product_id' => $productId,
                        'quantity_change' => $data['stock_quantity'],
                        'user_id' => $userData['user_id']
                    ]);
                }
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Product added successfully',
                    'product_id' => $productId
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add product']);
            }
            break;
            
        case 'PUT':
            // Update product
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['product_id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
                exit;
            }
            
            // Check if product exists
            $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $data['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Product not found']);
                exit;
            }
            
            // Capture the old quantity for inventory transaction
            $oldQuantity = $product['stock_quantity'];
            
            // Build update query dynamically
            $fields = [
                'name', 'category_id', 'price', 'description', 'stock_quantity',
                'cost_price', 'barcode', 'sku', 'product_image', 'is_food',
                'expiry_date', 'min_stock_level', 'supplier_id'
            ];
            
            $updates = [];
            $params = ['product_id' => $data['product_id']];
            
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = :$field";
                    $params[$field] = $data[$field];
                }
            }
            
            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
                exit;
            }
            
            $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE product_id = :product_id";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                // Record inventory adjustment if quantity changed
                if (isset($data['stock_quantity']) && $data['stock_quantity'] != $oldQuantity) {
                    $quantityChange = $data['stock_quantity'] - $oldQuantity;
                    $userData = AuthMiddleware::validateToken();
                    
                    $transactionSql = "
                        INSERT INTO inventory_transactions (
                            product_id, quantity_change, transaction_type, 
                            notes, user_id
                        ) VALUES (
                            :product_id, :quantity_change, 'adjustment',
                            :notes, :user_id
                        )
                    ";
                    
                    $pdo->prepare($transactionSql)->execute([
                        'product_id' => $data['product_id'],
                        'quantity_change' => $quantityChange,
                        'notes' => 'Manual inventory adjustment',
                        'user_id' => $userData['user_id']
                    ]);
                }
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Product updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update product']);
            }
            break;
            
        case 'DELETE':
            // Delete product
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['product_id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
                exit;
            }
            
            // Check if the product is already used in orders
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $data['product_id']]);
            $usedInOrders = (int)$stmt->fetchColumn();
            
            if ($usedInOrders > 0) {
                http_response_code(409); // Conflict
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Cannot delete product as it is used in orders'
                ]);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :product_id");
            $result = $stmt->execute(['product_id' => $data['product_id']]);
            
            if ($result && $stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
}

// Implementation of other handling functions
function handleCategoryRequests($pdo, $method) {
    // Handle category CRUD operations
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($categories);
            break;
        
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['category_name'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Category name is required']);
                exit;
            }
            
            $stmt = $pdo->prepare("INSERT INTO categories (category_name, description) VALUES (:name, :description)");
            $result = $stmt->execute([
                'name' => $data['category_name'],
                'description' => $data['description'] ?? null
            ]);
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Category added successfully',
                    'category_id' => $pdo->lastInsertId()
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add category']);
            }
            break;
            
        // Implement PUT and DELETE as needed
    }
}

function handleSupplierRequests($pdo, $method) {
    // Handle supplier CRUD operations
    switch ($method) {
        case 'GET':
            if (isset($_GET['supplier_id'])) {
                $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = :id");
                $stmt->execute(['id' => $_GET['supplier_id']]);
                $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($supplier) {
                    echo json_encode($supplier);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Supplier not found']);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name");
                $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($suppliers);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['name'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Supplier name is required']);
                exit;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO suppliers (name, contact_person, phone, email, address) 
                VALUES (:name, :contact_person, :phone, :email, :address)
            ");
            
            $result = $stmt->execute([
                'name' => $data['name'],
                'contact_person' => $data['contact_person'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null
            ]);
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Supplier added successfully',
                    'supplier_id' => $pdo->lastInsertId()
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add supplier']);
            }
            break;
            
        // Implement PUT and DELETE as needed
    }
}

function handleTransactionRequests($pdo, $method) {
    switch ($method) {
        case 'GET':
            // Get inventory transactions with optional filters
            $params = [];
            $where = [];
            
            if (isset($_GET['product_id'])) {
                $where[] = "t.product_id = :product_id";
                $params['product_id'] = $_GET['product_id'];
            }
            
            if (isset($_GET['transaction_type'])) {
                $where[] = "t.transaction_type = :transaction_type";
                $params['transaction_type'] = $_GET['transaction_type'];
            }
            
            if (isset($_GET['start_date'])) {
                $where[] = "t.transaction_date >= :start_date";
                $params['start_date'] = $_GET['start_date'] . ' 00:00:00';
            }
            
            if (isset($_GET['end_date'])) {
                $where[] = "t.transaction_date <= :end_date";
                $params['end_date'] = $_GET['end_date'] . ' 23:59:59';
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $sql = "
                SELECT t.*, p.name as product_name, u.username
                FROM inventory_transactions t
                LEFT JOIN products p ON t.product_id = p.product_id
                LEFT JOIN users u ON t.user_id = u.user_id
                $whereClause
                ORDER BY t.transaction_date DESC
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($transactions);
            break;
            
        case 'POST':
            // Record a new inventory transaction
            $data = json_decode(file_get_contents('php://input'), true);
            $userData = AuthMiddleware::validateToken();
            
            // Validate required fields
            if (!isset($data['product_id']) || !isset($data['quantity_change']) || !isset($data['transaction_type'])) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Product ID, quantity change and transaction type are required'
                ]);
                exit;
            }
            
            // Begin transaction to ensure data consistency
            $pdo->beginTransaction();
            
            try {
                // Insert the transaction record
                $stmt = $pdo->prepare("
                    INSERT INTO inventory_transactions (
                        product_id, quantity_change, transaction_type,
                        reference_id, notes, user_id
                    ) VALUES (
                        :product_id, :quantity_change, :transaction_type,
                        :reference_id, :notes, :user_id
                    )
                ");
                
                $stmt->execute([
                    'product_id' => $data['product_id'],
                    'quantity_change' => $data['quantity_change'],
                    'transaction_type' => $data['transaction_type'],
                    'reference_id' => $data['reference_id'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'user_id' => $userData['user_id']
                ]);
                
                // Update product stock quantity
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity + :quantity_change
                    WHERE product_id = :product_id
                ");
                
                $stmt->execute([
                    'product_id' => $data['product_id'],
                    'quantity_change' => $data['quantity_change']
                ]);
                
                $pdo->commit();
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Inventory transaction recorded successfully'
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            break;
    }
}

function handlePurchaseOrderRequests($pdo, $method) {
    // Handle purchase order operations
    // Implement as needed for your POS system
}
?>