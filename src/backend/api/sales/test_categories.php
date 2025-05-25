<?php
// Test script to check for duplicate categories
require_once __DIR__ . '/../../../config/db.php';

try {
    // Test 1: Check all categories
    echo "=== ALL CATEGORIES ===\n";
    $sql = "SELECT category_id, category_name, description FROM categories ORDER BY category_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($categories as $category) {
        echo "ID: {$category['category_id']}, Name: '{$category['category_name']}', Description: '{$category['description']}'\n";
    }
    
    echo "\nTotal categories found: " . count($categories) . "\n\n";
    
    // Test 2: Check for duplicate category names
    echo "=== DUPLICATE CATEGORY NAMES ===\n";
    $sql = "SELECT category_name, COUNT(*) as count FROM categories GROUP BY category_name HAVING COUNT(*) > 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "No duplicate category names found.\n";
    } else {
        foreach ($duplicates as $duplicate) {
            echo "Duplicate name: '{$duplicate['category_name']}' appears {$duplicate['count']} times\n";
        }
    }
    
    echo "\n=== TEST SALES API CATEGORIES ENDPOINT ===\n";
    
    // Include error handling like in sales_api.php
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
    
    // Test the exact same query as sales API
    $sql = "SELECT category_id, category_name, description FROM categories ORDER BY category_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "API Response would be:\n";
    $response = [
        'success' => true,
        'data' => $categories
    ];
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
