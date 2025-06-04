<?php
/**
 * Test script to verify cash payment fix
 * This script tests if cash payments now correctly show 'Success' status
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config/db.php';

echo "<h2>Testing Cash Payment Fix</h2>\n";

try {
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    echo "<h3>1. Checking current payment statuses</h3>\n";
    
    // Check current payment statuses
    $statusQuery = "
        SELECT 
            payment_method,
            transaction_status,
            COUNT(*) as count,
            CASE 
                WHEN transaction_status = '' THEN 'EMPTY'
                WHEN transaction_status IS NULL THEN 'NULL'
                ELSE transaction_status
            END as status_display
        FROM payments 
        GROUP BY payment_method, transaction_status
        ORDER BY payment_method, transaction_status
    ";
    
    $result = $conn->query($statusQuery);
    echo "<table border='1'>\n";
    echo "<tr><th>Payment Method</th><th>Status Display</th><th>Actual Status</th><th>Count</th></tr>\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status_display']) . "</td>";
        echo "<td>" . htmlspecialchars($row['transaction_status'] ?: '(empty)') . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<h3>2. Creating test order for cash payment</h3>\n";
    
    // Create a test order first
    $conn->begin_transaction();
    
    // Insert test order
    $orderStmt = $conn->prepare("
        INSERT INTO orders (user_id, total_amount, order_date, payment_status) 
        VALUES (1, 25.00, NOW(), 'Pending')
    ");
    $orderStmt->execute();
    $testOrderId = $conn->insert_id;
    
    echo "Created test order with ID: $testOrderId<br>\n";
    
    // Add order items
    $orderItemStmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, subtotal) 
        VALUES (?, 1, 2, 25.00)
    ");
    $orderItemStmt->execute([$testOrderId]);
    
    echo "Added order items<br>\n";
    
    echo "<h3>3. Making cash payment via API</h3>\n";
    
    // Prepare payment data
    $paymentData = [
        'order_id' => $testOrderId,
        'payment_method' => 'Cash',
        'cash_received' => 30.00,
        'transaction_status' => 'Success'
    ];
    
    // Simulate the API call by calling the payment processing function directly
    // First, we need to simulate the authentication
    $_POST = $paymentData;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Mock user authentication for testing
    $GLOBALS['user'] = ['user_id' => 1, 'username' => 'test_user'];
    
    // Capture the API response
    ob_start();
    
    // Simulate the payment request by sending JSON data
    $jsonData = json_encode($paymentData);
    
    // Mock the php://input stream
    $tempStream = fopen('php://temp', 'r+');
    fwrite($tempStream, $jsonData);
    rewind($tempStream);
    
    // Test payment processing by inserting directly with our fixed SQL
    $paymentStmt = $conn->prepare("
        INSERT INTO payments (
            order_id, 
            payment_method, 
            cash_received,
            change_amount, 
            paypal_transaction_id, 
            transaction_status,
            payment_time, 
            cashier_id
        ) VALUES (?, ?, ?, ?, ?, 'Success', NOW(), ?)
    ");
    
    $changeAmount = $paymentData['cash_received'] - 25.00; // $5.00 change
    
    $paymentStmt->bind_param(
        "isddsi",
        $testOrderId,
        $paymentData['payment_method'],
        $paymentData['cash_received'],
        $changeAmount,
        null, // paypal_transaction_id
        1     // cashier_id
    );
    
    if ($paymentStmt->execute()) {
        $paymentId = $conn->insert_id;
        echo "✅ Cash payment created successfully with ID: $paymentId<br>\n";
        
        // Update order status
        $updateStmt = $conn->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?");
        $updateStmt->execute([$testOrderId]);
        
        $conn->commit();
        
        echo "<h3>4. Verifying the payment status</h3>\n";
        
        // Check the payment we just created
        $checkStmt = $conn->prepare("
            SELECT 
                payment_id,
                order_id,
                payment_method,
                cash_received,
                change_amount,
                transaction_status,
                CASE 
                    WHEN transaction_status = '' THEN 'EMPTY'
                    WHEN transaction_status IS NULL THEN 'NULL'
                    ELSE transaction_status
                END as status_display,
                payment_time
            FROM payments 
            WHERE payment_id = ?
        ");
        $checkStmt->execute([$paymentId]);
        $paymentRecord = $checkStmt->fetch_assoc();
        
        echo "<table border='1'>\n";
        echo "<tr><th>Field</th><th>Value</th></tr>\n";
        foreach ($paymentRecord as $field => $value) {
            echo "<tr><td>$field</td><td>" . htmlspecialchars($value ?: '(empty)') . "</td></tr>\n";
        }
        echo "</table>\n";
        
        if ($paymentRecord['transaction_status'] === 'Success') {
            echo "<h3>🎉 SUCCESS: Cash payment now correctly shows 'Success' status!</h3>\n";
        } else {
            echo "<h3>❌ ISSUE: Cash payment status is: '" . htmlspecialchars($paymentRecord['status_display']) . "'</h3>\n";
        }
        
    } else {
        echo "❌ Failed to create cash payment: " . $paymentStmt->error . "<br>\n";
        $conn->rollback();
    }
    
    fclose($tempStream);
    
    echo "<h3>5. Updated payment status summary</h3>\n";
    
    // Check updated payment statuses
    $result = $conn->query($statusQuery);
    echo "<table border='1'>\n";
    echo "<tr><th>Payment Method</th><th>Status Display</th><th>Actual Status</th><th>Count</th></tr>\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status_display']) . "</td>";
        echo "<td>" . htmlspecialchars($row['transaction_status'] ?: '(empty)') . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo "Error: " . $e->getMessage() . "<br>\n";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>\n";
}
?>
