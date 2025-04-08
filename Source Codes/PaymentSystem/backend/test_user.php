<?php
require 'db.php';

header('Content-Type: application/json');

// Just test if we can query a user
$stmt = $pdo->prepare("SELECT * FROM user WHERE username = 'admin'");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($user);
?>