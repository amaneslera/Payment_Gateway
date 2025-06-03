<?php
// Authentication service for Payment Gateway

// Load autoloader first - try multiple possible paths
$autoload_paths = [
    __DIR__ . '/../../../vendor/autoload.php',  // Standard path from src/backend/services
    __DIR__ . '/../../../../vendor/autoload.php',  // Alternative path
    dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php',  // Explicit path to root
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

// Add namespace imports here
use Firebase\JWT\JWT;

// Then set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Update CORS headers to handle both localhost and IP
header('Access-Control-Allow-Origin: *'); // For development only
// OR for more security:
$allowed_origins = [
    'http://localhost:5500',
    'http://127.0.0.1:5500'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}

// Handle preflight OPTIONS request - CRITICAL for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// NOW enable error reporting (after CORS headers are sent)
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Include database connection from correct location
    require_once __DIR__ . '/../../config/db.php';
    
    // Add JWT config before any JWT usage
    require_once __DIR__ . '/../../config/jwt_config.php';
    
    // Include JWT utilities
    require_once __DIR__ . '/../utils/jwt_utils.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Decode JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
            exit;
        }
        
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        // Check if required data is received
        if (!$username || !$password) {
            echo json_encode(['status' => 'error', 'message' => 'Username or password not provided']);
            exit;
        }

        // Query the database
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check password - adjust based on how your passwords are stored
            if (hash('sha256', $password) === $user['password_hash']) {
                // Generate JWT token using our utility function
                $payload = [
                    'data' => [
                        'user_id' => $user['user_id'],
                        'username' => $user['username'],
                        'role' => $user['role']
                    ],
                    'iat' => time(),
                    'exp' => time() + JWT_EXPIRATION
                ];
                
                $token = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
                $refresh_token = bin2hex(random_bytes(32));
                $refresh_expiry = date('Y-m-d H:i:s', time() + JWT_REFRESH_EXPIRATION);
                
                // Store refresh token in database
                $storeTokenStmt = $pdo->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) 
                                               VALUES (?, ?, ?)");
                $storeTokenStmt->execute([
                    $user['user_id'],
                    $refresh_token,
                    $refresh_expiry
                ]);
                
                // Remove sensitive data
                unset($user['password_hash']);
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Login successful', 
                    'token' => $token,
                    'refresh_token' => $refresh_token,
                    'expires_in' => JWT_EXPIRATION,
                    'user' => $user
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);

}
?>