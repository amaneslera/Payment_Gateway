<?php
// filepath: c:\xampp\htdocs\PaymentSystem\backend\login.php

// Allow requests from any origin
header('Access-Control-Allow-Origin: *');

// Allow specific HTTP methods
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Allow specific headers
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include JWT library
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/jwt_config.php';

use Firebase\JWT\JWT;

// Enable error reporting but don't output it directly
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Set content type to JSON for all responses
header('Content-Type: application/json');

try {
    // Include database connection
    require 'db.php';
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Start transaction
        $pdo->beginTransaction();
        
        // Decode JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $username = htmlspecialchars($input['username'] ?? '');
        $password = htmlspecialchars($input['password'] ?? '');

        // Check if required data is received
        if (!$username || !$password) {
            echo json_encode(['status' => 'error', 'message' => 'Username or password not provided']);
            if ($pdo->inTransaction()) $pdo->rollBack();
            exit;
        }

        // Query the database
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Notice: Using either password_verify or hash check based on your system
            // Check if your system uses hash('sha256', $password) instead
            if (hash('sha256', $password) === $user['password_hash']) {
                // Update last login time
                $updateStmt = $pdo->prepare("UPDATE user SET updated_at = NOW() WHERE user_id = :user_id");
                $updateStmt->execute(['user_id' => $user['user_id']]);
                
                // Generate JWT token
                $issuedAt = time();
                $expirationTime = $issuedAt + JWT_EXPIRATION;
                
                $payload = [
                    'iat' => $issuedAt,
                    'exp' => $expirationTime,
                    'data' => [
                        'user_id' => $user['user_id'],
                        'username' => $user['username'],
                        'role' => $user['role']
                    ]
                ];
                
                // Generate refresh token
                $refreshPayload = [
                    'iat' => $issuedAt,
                    'exp' => $issuedAt + JWT_REFRESH_EXPIRATION,
                    'data' => [
                        'user_id' => $user['user_id'],
                        'type' => 'refresh'
                    ]
                ];
                
                $jwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
                $refreshToken = JWT::encode($refreshPayload, JWT_SECRET_KEY, 'HS256');
                
                // Store refresh token in database
                $storeTokenStmt = $pdo->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) 
                                               VALUES (:user_id, :token, :expires_at)");
                $storeTokenStmt->execute([
                    'user_id' => $user['user_id'],
                    'token' => $refreshToken,
                    'expires_at' => date('Y-m-d H:i:s', $issuedAt + JWT_REFRESH_EXPIRATION)
                ]);
                
                // Remove sensitive data
                unset($user['password_hash']);
                
                // Commit transaction
                $pdo->commit();
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Login successful', 
                    'token' => $jwt,
                    'refresh_token' => $refreshToken,
                    'expires_in' => JWT_EXPIRATION,
                    'user' => $user
                ]);
            } else {
                if ($pdo->inTransaction()) $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
            }
        } else {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>