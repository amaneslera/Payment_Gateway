<?php
// filepath: C:\xampp\htdocs\PaymentSystem\backend\auth_middleware.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AuthMiddleware {
    /**
     * Validate JWT token and get user data
     * @return object|false User data if token is valid, false otherwise
     */
    public static function validateToken() {
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
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