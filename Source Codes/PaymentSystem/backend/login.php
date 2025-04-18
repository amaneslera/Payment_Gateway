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
        // Decode JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $username = htmlspecialchars($input['username'] ?? '');
        $password = htmlspecialchars($input['password'] ?? '');

        // Check if required data is received
        if (!$username || !$password) {
            echo json_encode(['status' => 'error', 'message' => 'Username or password not provided']);
            exit;
        }

        // Query the database
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify the password
            if (password_verify($password, $user['password_hash'])) { // Use password_verify for hashed passwords
                unset($user['password_hash']); // Remove sensitive data
                echo json_encode(['status' => 'success', 'message' => 'Login successful', 'user' => $user]);
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