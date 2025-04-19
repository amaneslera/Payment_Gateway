<?php
// filepath: c:\xampp\htdocs\PaymentSystem\backend\db.php

$host = '127.0.0.1';
$dbname = 'pos_system';
$username = 'pos';
$password = 'pos';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // REMOVE THIS LINE:
    // echo "Database connection successful!";
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
// No closing PHP tag