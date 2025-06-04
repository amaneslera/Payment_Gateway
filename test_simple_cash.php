<?php
// Simple cash payment test
require_once 'config/db.php';

echo "Testing cash payment insertion...\n\n";

try {
    // Test mysqli connection
    $conn = getConnection();
    echo "✓ MySQLi connection successful\n";
    
    // Test simple insert to payments table
    $testOrderId = 74; // Use existing order from database
    $testUserId = 114; // Use existing user ID
    
    echo "Attempting to insert cash payment...\n";
    
    $stmt = $conn->prepare("
        INSERT INTO payments (
            order_id, 
            payment_method, 
            transaction_status,
            cash_received,
            change_amount, 
            payment_time, 
            cashier_id
        ) VALUES (?, 'Cash', 'Success', ?, ?, NOW(), ?)
    ");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $cashReceived = 50.00;
    $changeAmount = 25.00;
    
    $stmt->bind_param("iddi", $testOrderId, $cashReceived, $changeAmount, $testUserId);
    
    if ($stmt->execute()) {
        $paymentId = $conn->insert_id;
        echo "✓ Payment inserted successfully with ID: $paymentId\n";
        
        // Verify the payment
        $verifyStmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $verifyStmt->bind_param("i", $paymentId);
        $verifyStmt->execute();
        $result = $verifyStmt->get_result();
        $payment = $result->fetch_assoc();
        
        echo "Payment verification:\n";
        echo "- Payment ID: " . $payment['payment_id'] . "\n";
        echo "- Order ID: " . $payment['order_id'] . "\n";
        echo "- Payment Method: " . $payment['payment_method'] . "\n";
        echo "- Transaction Status: " . $payment['transaction_status'] . "\n";
        echo "- Cash Received: " . $payment['cash_received'] . "\n";
        echo "- Change Amount: " . $payment['change_amount'] . "\n";
        echo "- Payment Time: " . $payment['payment_time'] . "\n";
        
    } else {
        throw new Exception("Failed to execute payment insert: " . $stmt->error);
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
