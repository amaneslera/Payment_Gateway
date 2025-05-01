<?php
// Simple test file to verify Apache configuration

// Set content type
header('Content-Type: application/json');

// Return a success message
echo json_encode([
    'status' => 'success',
    'message' => 'Apache can access this file',
    'server_info' => [
        'document_root' => $_SERVER['DOCUMENT_ROOT'],
        'script_filename' => $_SERVER['SCRIPT_FILENAME'],
        'php_version' => phpversion()
    ]
]);
