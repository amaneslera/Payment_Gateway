<?php
// filepath: c:\xampp\htdocs\PaymentSystem\backend\user_api.php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';
require_once 'auth_middleware.php';

$method = $_SERVER['REQUEST_METHOD'];

// Public endpoints (no auth required)
if ($method === 'OPTIONS') {
    exit;
}

// Protected endpoints (require authentication)
// All operations except certain read operations require authentication
if ($method !== 'GET' || isset($_GET['user_id'])) {
    $userData = AuthMiddleware::validateToken();
    if (!$userData) {
        exit; // Middleware already sent error response
    }
    
    // Admin-only operations
    if (($method === 'POST' || $method === 'PUT' || $method === 'DELETE') && 
        !AuthMiddleware::checkRole($userData, ['Admin'])) {
        exit; // Middleware already sent error response
    }
}

// Continue with your existing switch case for CRUD operations...
switch ($method) {
    case 'GET':
        // Check if a specific user ID is requested
        if (isset($_GET['user_id'])) {
            // Fetch specific user by ID
            $stmt = $pdo->prepare("SELECT user_id, username, role, email, created_at, updated_at FROM user WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $_GET['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo json_encode($user);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
            }
        } else {
            // Fetch all users (original behavior)
            $stmt = $pdo->query("SELECT user_id, username, role, email, created_at, updated_at FROM user");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        }
        break;

    case 'POST':
        // Create a new user
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Validate required fields
            if (!isset($data['username']) || !isset($data['password']) || !isset($data['role']) || !isset($data['email'])) {
                throw new Exception('Missing required fields');
            }
            
            // Check if username already exists
            $check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = :username");
            $check->execute(['username' => $data['username']]);
            if ($check->fetchColumn() > 0) {
                throw new Exception('Username already exists');
            }
            
            $stmt = $pdo->prepare("INSERT INTO user (username, password_hash, role, email) VALUES (:username, :password_hash, :role, :email)");
            $result = $stmt->execute([
                'username' => $data['username'],
                'password_hash' => hash('sha256', $data['password']),
                'role' => $data['role'],
                'email' => $data['email']
            ]);
            
            // If we get here without exceptions, commit the transaction
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'User created successfully']);
            
        } catch (Exception $e) {
            // Something went wrong, rollback changes
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Initialize arrays for update fields and parameters
            $updateFields = [];
            $params = [];
            
            // Most important - set the user_id parameter first
            if (!isset($data['user_id'])) {
                throw new Exception('User ID is required');
            }
            
            // Add user_id to params right away
            $params['user_id'] = $data['user_id'];
            
            // Check if user exists before attempting update
            $checkUser = $pdo->prepare("SELECT COUNT(*) FROM user WHERE user_id = :user_id");
            $checkUser->execute(['user_id' => $data['user_id']]);
            if ($checkUser->fetchColumn() == 0) {
                throw new Exception('User not found');
            }
            
            if (isset($data['username'])) {
                // Check if new username already exists (but not for this user)
                $checkUsername = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = :username AND user_id != :user_id");
                $checkUsername->execute(['username' => $data['username'], 'user_id' => $data['user_id']]);
                if ($checkUsername->fetchColumn() > 0) {
                    throw new Exception('Username already taken');
                }
                $updateFields[] = "username = :username";
                $params['username'] = $data['username'];
            }
            
            if (isset($data['role'])) {
                $updateFields[] = "role = :role";
                $params['role'] = $data['role'];
            }
            
            if (isset($data['email'])) {
                $updateFields[] = "email = :email";
                $params['email'] = $data['email'];
            }
            
            if (isset($data['password']) && !empty($data['password'])) {
                $updateFields[] = "password_hash = :password_hash";
                $params['password_hash'] = hash('sha256', $data['password']);
            }
            
            // Add updated_at timestamp
            $updateFields[] = "updated_at = NOW()";
            
            if (empty($updateFields)) {
                throw new Exception('No fields to update');
            }
            
            $sql = "UPDATE user SET " . implode(", ", $updateFields) . " WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                throw new Exception('Failed to update user');
            }
            
            // If we get here without exceptions, commit the transaction
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
            
        } catch (Exception $e) {
            // Something went wrong, rollback changes
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            if (!isset($data['user_id'])) {
                throw new Exception('User ID is required');
            }
            
            // You might want to also delete related records in other tables
            // For example:
            // $stmt1 = $pdo->prepare("DELETE FROM user_profiles WHERE user_id = :user_id");
            // $stmt1->execute(['user_id' => $data['user_id']]);
            
            $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = :user_id");
            $result = $stmt->execute(['user_id' => $data['user_id']]);
            
            if (!$result) {
                throw new Exception('Failed to delete user');
            }
            
            // If we get here without exceptions, commit the transaction
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
            
        } catch (Exception $e) {
            // Something went wrong, rollback changes
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        break;
}
?>