<?php
// CORS headers must come first before any output or errors
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://127.0.0.1:5500');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// NOW enable error reporting (after CORS headers are sent)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/jwt_config.php';
require_once __DIR__ . '/../utils/jwt_utils.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;

// Get the refresh token from the request
$data = json_decode(file_get_contents('php://input'), true);
$refresh_token = $data['refresh_token'] ?? '';

if (empty($refresh_token)) {
    echo json_encode(['status' => 'error', 'message' => 'Refresh token is required']);
    exit;
}

try {
    // Verify the refresh token in the database
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM refresh_tokens WHERE token = ? AND revoked = 0");
    $stmt->execute([$refresh_token]);
    $token_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$token_data) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid refresh token']);
        exit;
    }

    // Check if the token is expired
    if (strtotime($token_data['expires_at']) < time()) {
        echo json_encode(['status' => 'error', 'message' => 'Refresh token expired']);
        exit;
    }

    // Get the user data to generate new tokens
    $user_id = $token_data['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit;
    }

    // Generate new tokens
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
    $new_refresh_token = bin2hex(random_bytes(32));
    $refresh_expiry = date('Y-m-d H:i:s', time() + JWT_REFRESH_EXPIRATION);

    // Revoke the old refresh token
    $stmt = $pdo->prepare("UPDATE refresh_tokens SET revoked = 1 WHERE token = ?");
    $stmt->execute([$refresh_token]);

    // Save the new refresh token
    $stmt = $pdo->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['user_id'], $new_refresh_token, $refresh_expiry]);

    // Remove sensitive data
    unset($user['password_hash']);

    echo json_encode([
        'status' => 'success',
        'token' => $token,
        'refresh_token' => $new_refresh_token,
        'expires_in' => JWT_EXPIRATION,
        'user' => $user
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}