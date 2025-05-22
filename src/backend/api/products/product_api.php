<?php
// Debug section - temporarily enable errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/product_api_error.log');

// Prevent any unwanted output that might break JSON
ob_start();

// Set proper headers for JSON API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Clear any previous output
ob_clean();

// Function to return JSON error response
function returnError($message, $statusCode = 500, $details = null) {
    http_response_code($statusCode);
    $response = [
        'status' => 'error',
        'message' => $message
    ];
    
    if ($details) {
        $response['details'] = $details;
    }
    
    echo json_encode($response);
    exit;
}

try {
    // Simply include the database configuration file
    require_once __DIR__ . '/../../../config/db.php';
    
    // Use the global PDO connection that's already created in db.php
    // $pdo = getConnection(); // This was incorrect as getConnection() now returns mysqli
    
    if (!isset($pdo)) {
        returnError('Database connection failed', 500);
    }
    
    // Handle GET requests
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Check if a code parameter is provided
        if (isset($_GET['code'])) {
            $code = $_GET['code'];
            
            // Query the database for the product by code
            $stmt = $pdo->prepare("
                SELECT * FROM products 
                WHERE barcode = :code OR sku = :code OR product_id = :code
            ");
            $stmt->execute(['code' => $code]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                // Return product data as JSON
                echo json_encode([
                    'status' => 'success',
                    'product' => $product
                ]);
            } else {
                // Product not found
                returnError('Product not found', 404);
            }
        } else {
            // If no code is provided, return all products
            $stmt = $pdo->query("SELECT * FROM products ORDER BY name");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 'success',
                'products' => $products
            ]);
        }
    } else {
        // Handle other request methods
        returnError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    // Log the error for debugging
    error_log("PDO Error: " . $e->getMessage());
    returnError('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    // Log the error for debugging
    error_log("General Error: " . $e->getMessage());
    returnError('Server error: ' . $e->getMessage(), 500);
}
?>
