<?php
// filepath: C:\xampp\htdocs\PaymentSystem\backend\test_db.php
header('Content-Type: application/json');

try {
    // Try connection with same credentials
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=pos_system", "pos", "pos");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo json_encode(['status' => 'success', 'message' => 'Connected successfully']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}