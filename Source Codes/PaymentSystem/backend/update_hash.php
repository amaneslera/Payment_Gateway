<?php
// Update password hash
require 'db.php';

$username = 'admin';
$password = 'password123'; 
$hash = hash('sha256', $password);  

$stmt = $pdo->prepare("UPDATE user SET password_hash = :hash WHERE username = :username");
$stmt->execute(['hash' => $hash, 'username' => $username]);

echo "Password hash updated successfully: " . $hash;
?>