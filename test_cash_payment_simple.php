<?php
// Simple test to check if cash payments are working
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Cash Payment Test ===\n";

// Test database connection
try {
    require_once 'config/db.php';
    $conn = getConnection();
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Check current payments count
$result = $conn->query("SELECT COUNT(*) as count FROM payments");
$row = $result->fetch_assoc();
$initial_count = $row['count'];
echo "Current payments in database: $initial_count\n";

// Test inserting a cash payment directly
$order_id = 999; // Test order ID
$payment_method = 'Cash';
$cash_received = 100.00;
$change_amount = 5.00;
$cashier_id = 1;

$sql = "INSERT INTO payments (order_id, payment_method, cash_received, change_amount, cashier_id) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "✗ Prepare failed: " . $conn->error . "\n";
    exit;
}

$stmt->bind_param("isddi", $order_id, $payment_method, $cash_received, $change_amount, $cashier_id);

if ($stmt->execute()) {
    echo "✓ Payment inserted successfully\n";
    $payment_id = $conn->insert_id;
    echo "New payment ID: $payment_id\n";
    
    // Check the status of the inserted payment
    $check_sql = "SELECT payment_id, transaction_status, payment_method, cash_received FROM payments WHERE payment_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $payment_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($payment_data = $result->fetch_assoc()) {
        echo "Payment details:\n";
        echo "- ID: " . $payment_data['payment_id'] . "\n";
        echo "- Status: " . $payment_data['transaction_status'] . "\n";
        echo "- Method: " . $payment_data['payment_method'] . "\n";
        echo "- Amount: $" . $payment_data['cash_received'] . "\n";
        
        if ($payment_data['transaction_status'] === 'Success') {
            echo "✓ Status is correctly set to 'Success'\n";
        } else {
            echo "✗ Status is '" . $payment_data['transaction_status'] . "' instead of 'Success'\n";
        }
    }
    
} else {
    echo "✗ Payment insertion failed: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();

echo "=== Test Complete ===\n";
?>
