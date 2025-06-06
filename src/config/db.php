<?php
// filepath: c:\xampp\htdocs\PaymentSystem\backend\db.php

// Set Philippines timezone globally for all backend operations
date_default_timezone_set('Asia/Manila');

// Load configuration
require_once __DIR__ . '/config.php'; // Make sure this path is correct

// Create the global PDO connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Function to get mysqli connection for code that uses mysqli syntax
function getConnection() {
    // Create and return a mysqli connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }
    
    // Set charset
    $conn->set_charset('utf8mb4');
    
    return $conn;
}
// No closing PHP tag