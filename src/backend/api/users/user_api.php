<?php

// Temporarily enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Add CORS headers for browser access
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://127.0.0.1:5500');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Include required files with correct paths
// Load autoloader first - try multiple possible paths
$autoload_paths = [
    __DIR__ . '/../../../../vendor/autoload.php',  // Standard path
    __DIR__ . '/../../../../../vendor/autoload.php',  // Alternative path
    dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php',  // Explicit path to root
];

$autoload_loaded = false;
foreach ($autoload_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoload_loaded = true;
        break;
    }
}

if (!$autoload_loaded) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unable to load Composer autoloader']);
    exit;
}

    require_once __DIR__ . '/../../../config/db.php';
    
     
    require_once __DIR__ . '/../../middleware/auth_middleware.php';
    
    // Verify database connection
    if (!isset($pdo)) {
        echo json_encode(['error' => 'Database connection not established']);
        exit;
    }
    
    // Handle different HTTP methods for CRUD operations
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get list of users
            $stmt = $pdo->prepare("SELECT user_id, username, email, role, created_at, updated_at FROM user");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Return users as JSON
            echo json_encode($users);
            break;
            
        case 'POST':
            // Create a new user
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            if (!isset($input['username']) || !isset($input['password']) || !isset($input['email']) || !isset($input['role'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
                exit;
            }
            
            // Hash password
            $password_hash = hash('sha256', $input['password']);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO user (username, password_hash, email, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$input['username'], $password_hash, $input['email'], $input['role']]);
            
            // Get the new user ID
            $user_id = $pdo->lastInsertId();
            
            echo json_encode(['status' => 'success', 'message' => 'User added successfully', 'user_id' => $user_id]);
            break;
            
        case 'PUT':
            // Update an existing user
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate user_id
            if (!isset($input['user_id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
                exit;
            }
            
            // Build update query dynamically
            $updateFields = [];
            $params = [];
            
            if (isset($input['username'])) {
                $updateFields[] = "username = ?";
                $params[] = $input['username'];
            }
            
            if (isset($input['email'])) {
                $updateFields[] = "email = ?";
                $params[] = $input['email'];
            }
            
            if (isset($input['role'])) {
                $updateFields[] = "role = ?";
                $params[] = $input['role'];
            }
            
            if (isset($input['password']) && !empty($input['password'])) {
                $updateFields[] = "password_hash = ?";
                $params[] = hash('sha256', $input['password']);
            }
            
            // Add updated timestamp
            $updateFields[] = "updated_at = NOW()";
            
            // Add user_id to params
            $params[] = $input['user_id'];
            
            // Execute update query
            $query = "UPDATE user SET " . implode(", ", $updateFields) . " WHERE user_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            
            echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
            break;
            
        case 'DELETE':
            // Delete a user
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate user_id
            if (!isset($input['user_id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
                exit;
            }
            
            // Delete user
            $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ?");
            $stmt->execute([$input['user_id']]);
            
            echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>