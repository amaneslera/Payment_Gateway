<?php
// filepath: C:\xampp\htdocs\PaymentSystem\config.php

// Load environment variables from .env file in project root
$env_file = __DIR__ . '/../.env';  // Adjust this path based on where your .env file is
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Define constants based on environment variables
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'pos_system');
define('DB_USER', $_ENV['DB_USER'] ?? 'pos');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'pos');

define('API_URL', $_ENV['API_URL'] ?? 'http://localhost/PaymentGateway/');
define('JWT_SECRET_KEY', $_ENV['JWT_SECRET'] ?? 'default_secret_key');
define('JWT_EXPIRATION', (int)($_ENV['JWT_EXPIRATION'] ?? 3600));
define('JWT_REFRESH_EXPIRATION', (int)($_ENV['JWT_REFRESH_EXPIRATION'] ?? 604800));

// Update this line to match your frontend origin
define('FRONTEND_URL', $_ENV['FRONTEND_URL'] ?? 'http://127.0.0.1:5500');