<?php

// JWT Configuration

// These constants should match what's used in your JWT utils
if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', $_ENV['JWT_SECRET'] ?? 'emQzx7$pGrT!9Bvf@KwL#2Hs5*yUn8aC');
}

if (!defined('JWT_EXPIRATION')) {
    define('JWT_EXPIRATION', $_ENV['JWT_EXPIRATION'] ?? 3600); // 1 hour
}

if (!defined('JWT_REFRESH_EXPIRATION')) {
    define('JWT_REFRESH_EXPIRATION', $_ENV['JWT_REFRESH_EXPIRATION'] ?? 604800); // 1 week
}