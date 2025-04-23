<?php
// filepath: C:\xampp\htdocs\PaymentSystem\backend\refresh_token.php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'jwt_config.php';
require_once 'db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $refreshToken = $data['refresh_token'] ?? '';
    
    if (empty($refreshToken)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Refresh token is required']);
        exit;
    }
    
    $pdo->beginTransaction();
    
    try {
        $decoded = JWT::decode($refreshToken, new Key(JWT_SECRET_KEY, 'HS256'));
        $user_id = $decoded->data->user_id ?? null;
        
        if (!$user_id || $decoded->data->type !== 'refresh') {
            throw new Exception('Invalid refresh token');
        }
        
        // Verify token exists in database and isn't revoked
        $stmt = $pdo->prepare("SELECT * FROM refresh_tokens WHERE user_id = :user_id AND token = :token AND expires_at > NOW() AND revoked = 0");
        $stmt->execute([
            'user_id' => $user_id,
            'token' => $refreshToken
        ]);
        
        $tokenRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tokenRecord) {
            throw new Exception('Refresh token not found or expired');
        }
        
        // Get user details
        $userStmt = $pdo->prepare("SELECT user_id, username, role FROM user WHERE user_id = :user_id");
        $userStmt->execute(['user_id' => $user_id]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Generate new tokens
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
        
        // Generate new refresh token
        $refreshPayload = [
            'iat' => $issuedAt,
            'exp' => $issuedAt + JWT_REFRESH_EXPIRATION,
            'data' => [
                'user_id' => $user['user_id'],
                'type' => 'refresh'
            ]
        ];
        
        $jwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
        $newRefreshToken = JWT::encode($refreshPayload, JWT_SECRET_KEY, 'HS256');
        
        // Revoke old refresh token
        $updateStmt = $pdo->prepare("UPDATE refresh_tokens SET revoked = 1 WHERE id = :id");
        $updateStmt->execute(['id' => $tokenRecord['id']]);
        
        // Store new refresh token
        $storeTokenStmt = $pdo->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
        $storeTokenStmt->execute([
            'user_id' => $user['user_id'],
            'token' => $newRefreshToken,
            'expires_at' => date('Y-m-d H:i:s', $issuedAt + JWT_REFRESH_EXPIRATION)
        ]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Token refreshed successfully',
            'token' => $jwt,
            'refresh_token' => $newRefreshToken,
            'expires_in' => JWT_EXPIRATION
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Failed to refresh token: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}