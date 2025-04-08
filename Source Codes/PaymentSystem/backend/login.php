<?php
// filepath: c:\xampp\htdocs\PaymentSystem\backend\login.php

// Add these lines at the top of login.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// For preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Enable error reporting but don't output it directly
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Set content type to JSON for all responses
header('Content-Type: application/json');

try {
    require 'db.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get raw input data
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Check if we have JSON data, otherwise fallback to POST
        if ($data && is_array($data)) {
            $username = isset($data['username']) ? $data['username'] : '';
            $password = isset($data['password']) ? $data['password'] : '';
        } else {
            // Fallback to traditional POST data
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
        }

        // Check if required data is received
        if (!$username || !$password) {
            echo json_encode(['status' => 'error', 'message' => 'Username or password not provided']);
            exit;
        }

        // Query the database
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            exit;
        }

        // Use SHA-256 for password verification
        $hashed_input = hash('sha256', $password);
        // Add these lines for debugging
        error_log("Input password: $password");
        error_log("Generated hash: $hashed_input");
        error_log("Stored hash: " . $user['password_hash']);

        // Try both lowercase and uppercase hex format comparisons
        if (strtolower($hashed_input) !== strtolower($user['password_hash'])) {
            echo json_encode(['status' => 'error', 'message' => 'Password mismatch']);
            exit;
        }

        // Success response
        echo json_encode([
            'status' => 'success', 
            'message' => 'Login successful',
            'user' => [
                'username' => $user['username'],
                'role' => $user['role'],
                'email' => $user['email']
            ]
        ]);
    } else {
        // Handle non-POST requests
        echo json_encode([
            'status' => 'error',
            'message' => 'This endpoint requires a POST request'
        ]);
    }
} catch (Exception $e) {
    // Handle any errors
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>