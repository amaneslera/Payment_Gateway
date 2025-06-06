<?php

// Load autoloader first - try multiple possible paths
$autoload_paths = [
    __DIR__ . '/../../../vendor/autoload.php',  // Standard path from src/backend/middleware
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
require_once __DIR__ . '/../../config/jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AuthMiddleware {
    /**
     * Validate JWT token and get user data
     * @return object|false User data if token is valid, false otherwise
     */
    public static function validateToken() {
        // Try multiple ways to get the Authorization header
        $authHeader = null;
        
        // Method 1: getallheaders()
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        if (!empty($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } 
        // Method 2: $_SERVER variables
        elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }
        elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        
        // Try case-insensitive check as fallback
        if ($authHeader === null) {
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'authorization') {
                    $authHeader = $value;
                    break;
                }
            }
        }
        
        // Log for debugging
        error_log("Final Auth header found: " . ($authHeader ?: "NONE"));
        
        // Check if Authorization header exists and has Bearer format
        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized: No token provided']);
            return false;
        }
        
        $token = $matches[1];
        
        try {
            // Decode token
            $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
            return $decoded->data;
            
        } catch (ExpiredException $e) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token expired', 'code' => 'token_expired']);
            return false;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid token: ' . $e->getMessage()]);
            return false;
        }
    }
      /**
     * Validate JWT token from string and get user data
     * @param string $token JWT token string
     * @return object|false User data if token is valid, false otherwise
     */
    public static function validateTokenFromString($token) {
        if (empty($token)) {
            return false;
        }
        
        try {
            // Decode token
            $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
            return (array)$decoded->data; // Convert to array for compatibility
            
        } catch (ExpiredException $e) {
            error_log("Token expired: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Invalid token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has required role
     * @param object $userData User data from token
     * @param array $allowedRoles Array of allowed roles
     * @return bool True if user has required role, false otherwise
     */
    public static function checkRole($userData, $allowedRoles) {
        if (!$userData || !isset($userData->role)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Forbidden: Role information missing']);
            return false;
        }
        
        if (!in_array($userData->role, $allowedRoles)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Forbidden: Insufficient permissions']);
            return false;
        }
        
        return true;
    }
}