<?php
// filepath: C:\xampp\htdocs\PaymentSystem\backend\test_user.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Include database connection
require 'db.php';

// Hardcoded test - replace with real credentials from your database
$test_username = 'Draine';
$test_password = '0801'; 

try {
    // Query the database for the user
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
    $stmt->execute(['username' => $test_username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Check if password matches (using SHA-256)
        $password_matches = (hash('sha256', $test_password) === $user['password_hash']);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'User found',
            'user_exists' => true,
            'password_hash' => $user['password_hash'],
            'provided_hash' => hash('sha256', $test_password),
            'password_matches' => $password_matches
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}