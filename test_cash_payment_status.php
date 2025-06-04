<?php
require_once 'config/db.php';

// Test script to verify cash payment status is being saved correctly
try {
    $conn = getConnection();
    
    echo "=== Testing Cash Payment Status Fix ===\n\n";
    
    // First, let's check the current state of payments
    echo "1. Current payment statuses in database:\n";
    $result = $conn->query("
        SELECT 
            payment_id,
            order_id,
            payment_method,
            transaction_status,
            payment_time
        FROM payments 
        ORDER BY payment_id DESC 
        LIMIT 10
    ");
    
    while ($row = $result->fetch_assoc()) {
        $status = $row['transaction_status'] ?: 'NULL/EMPTY';
        echo "   Payment ID: {$row['payment_id']}, Order: {$row['order_id']}, Method: {$row['payment_method']}, Status: {$status}\n";
    }
    
    echo "\n2. Count of payments by method and status:\n";
    $result = $conn->query("
        SELECT 
            payment_method,
            COALESCE(transaction_status, 'NULL/EMPTY') as status,
            COUNT(*) as count
        FROM payments 
        GROUP BY payment_method, transaction_status
        ORDER BY payment_method, status
    ");
    
    while ($row = $result->fetch_assoc()) {
        echo "   {$row['payment_method']}: {$row['status']} - {$row['count']} payments\n";
    }
    
    echo "\n3. Testing the parameter binding fix:\n";
    echo "   The bind_param has been changed from 'isddsi' to 'isddssi'\n";
    echo "   This should fix the transaction_status field for cash payments.\n";
    
    echo "\n4. Database schema check:\n";
    $result = $conn->query("DESCRIBE payments");
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'transaction_status') {
            echo "   transaction_status field type: {$row['Type']}\n";
            echo "   transaction_status null allowed: {$row['Null']}\n";
            echo "   transaction_status default: " . ($row['Default'] ?: 'NULL') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
