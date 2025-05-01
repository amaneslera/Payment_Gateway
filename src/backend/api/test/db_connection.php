<?php
/**
 * Database Connection Test API Endpoint
 * 
 * This file provides an API endpoint to test the database connection.
 * It returns JSON data with connection status and database information.
 */

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

// Include the database configuration
require_once __DIR__ . '/../../../config/db.php';

// Check if we have a valid PDO connection
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        // Get server info
        $serverInfo = $pdo->getAttribute(PDO::ATTR_SERVER_INFO);
        $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        
        // Get database tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Prepare response
        $response = [
            'status' => 'success',
            'message' => 'Database connection successful',
            'connection' => [
                'host' => DB_HOST,
                'database' => DB_NAME,
                'user' => DB_USER,
                'server_version' => $serverVersion,
                'tables_count' => count($tables),
                'tables' => $tables
            ]
        ];
        
        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        $response = [
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ];
        
        echo json_encode($response);
        exit;
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Database connection failed: PDO object not available'
    ];
    
    echo json_encode($response);
    exit;
}