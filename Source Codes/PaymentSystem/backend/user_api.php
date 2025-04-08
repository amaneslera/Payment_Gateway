<?php
// filepath: c:\xampp\htdocs\PaymentSystem\backend\user_api.php
require 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Fetch all users
        $stmt = $pdo->query("SELECT user_id, username, role, email, created_at, updated_at FROM user");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
        break;

    case 'POST':
        // Create a new user
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['username']) || !isset($data['password']) || !isset($data['role']) || !isset($data['email'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            break;
        }
        
        // Check if username already exists
        $check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = :username");
        $check->execute(['username' => $data['username']]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
            break;
        }
        
        $stmt = $pdo->prepare("INSERT INTO user (username, password_hash, role, email) VALUES (:username, :password_hash, :role, :email)");
        $result = $stmt->execute([
            'username' => $data['username'],
            'password_hash' => hash('sha256', $data['password']), // Use SHA-256 to match login system
            'role' => $data['role'],
            'email' => $data['email']
        ]);
        
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'User created successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create user']);
        }
        break;

    case 'PUT':
        // Update a user
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
            break;
        }
        
        // Check if user exists
        $check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE user_id = :user_id");
        $check->execute(['user_id' => $data['user_id']]);
        if ($check->fetchColumn() == 0) {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            break;
        }
        
        // Build update query based on provided fields
        $updateFields = [];
        $params = ['user_id' => $data['user_id']];
        
        if (isset($data['username'])) {
            // Check if new username already exists (but not for this user)
            $checkUsername = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = :username AND user_id != :user_id");
            $checkUsername->execute(['username' => $data['username'], 'user_id' => $data['user_id']]);
            if ($checkUsername->fetchColumn() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Username already taken']);
                break;
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
        
        if (isset($data['password'])) {
            $updateFields[] = "password_hash = :password_hash";
            $params['password_hash'] = hash('sha256', $data['password']);
        }
        
        // Add updated_at timestamp
        $updateFields[] = "updated_at = NOW()";
        
        if (empty($updateFields)) {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
            break;
        }
        
        $sql = "UPDATE user SET " . implode(", ", $updateFields) . " WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update user']);
        }
        break;

    case 'DELETE':
        // Delete a user
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
            break;
        }
        
        $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = :user_id");
        $result = $stmt->execute(['user_id' => $data['user_id']]);
        
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        break;
}
?>