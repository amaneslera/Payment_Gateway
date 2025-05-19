<?php
// filepath: c:\xampp\htdocs\PaymentSystem\backend\db.php

// Load configuration
require_once __DIR__ . '/config.php';

// Create the global PDO connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Add the getConnection function to maintain compatibility with both code patterns
function getConnection() {
    global $pdo;
    return $pdo;
}
// No closing PHP tag