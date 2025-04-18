<?php
// filepath: c:\xampp\htdocs\PaymentSystem\backend\db.php

$host = '127.0.0.1';
$dbname = 'pos_system';
$username = 'pos';
$password = 'pos';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>