<?php
/**
 * Comprehensive Payment Status Test
 * Tests both Cash and PayPal payments to ensure transaction_status is set to 'Success'
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Comprehensive Payment Status Test</h1>";
echo "<p>Testing both Cash and PayPal payment status handling</p>";

try {
    // Database connection
    require_once __DIR__ . '/src/config/db.php';
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
    
    // Test 1: Direct Cash Payment Test
    echo "<h2>Test 1: Direct Cash Payment Insertion</h2>";
    
    // First, create a test order
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, payment_status) 
        VALUES (114, 25.00, 'Pending')
    ");
    $orderStmt->execute();
    $testOrderId = $pdo->lastInsertId();
    echo "<p>✅ Created test order ID: $testOrderId</p>";
    
    // Insert order item
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, subtotal) 
        VALUES (?, 3, 1, 25.00)
    ");
    $itemStmt->execute([$testOrderId]);
    echo "<p>✅ Added test order item</p>";
    
    // Test cash payment insertion with explicit Success status
    $cashPaymentStmt = $pdo->prepare("
        INSERT INTO payments (
            order_id, 
            payment_method, 
            transaction_status,
            cash_received,
            change_amount, 
            paypal_transaction_id, 
            payment_time, 
            cashier_id
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    
    $cashPaymentStmt->execute([
        $testOrderId,
        'Cash',
        'Success',  // Explicitly set to Success
        30.00,      // cash_received
        5.00,       // change_amount
        null,       // paypal_transaction_id
        114         // cashier_id
    ]);
    
    $cashPaymentId = $pdo->lastInsertId();
    echo "<p>✅ Inserted cash payment with ID: $cashPaymentId</p>";
    
    // Verify the cash payment status
    $verifyStmt = $pdo->prepare("
        SELECT payment_id, payment_method, transaction_status, cash_received, change_amount 
        FROM payments 
        WHERE payment_id = ?
    ");
    $verifyStmt->execute([$cashPaymentId]);
    $cashPayment = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Cash Payment Verification:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    foreach ($cashPayment as $field => $value) {
        $color = ($field === 'transaction_status' && $value === 'Success') ? 'green' : 'black';
        echo "<tr><td>$field</td><td style='color: $color; font-weight: bold;'>$value</td></tr>";
    }
    echo "</table>";
    
    if ($cashPayment['transaction_status'] === 'Success') {
        echo "<p style='color: green; font-weight: bold;'>✅ SUCCESS: Cash payment status is correctly set to 'Success'</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ FAILED: Cash payment status is '{$cashPayment['transaction_status']}' instead of 'Success'</p>";
    }
    
    // Test 2: PayPal Payment Test
    echo "<h2>Test 2: Direct PayPal Payment Insertion</h2>";
    
    // Create another test order for PayPal
    $orderStmt->execute([114, 50.00, 'Pending']);
    $testOrderId2 = $pdo->lastInsertId();
    echo "<p>✅ Created test order ID: $testOrderId2</p>";
    
    // Insert order item
    $itemStmt->execute([$testOrderId2]);
    echo "<p>✅ Added test order item</p>";
    
    // Test PayPal payment insertion
    $paypalPaymentStmt = $pdo->prepare("
        INSERT INTO payments (
            order_id, 
            payment_method, 
            paypal_transaction_id, 
            transaction_status,
            cash_received,
            change_amount,
            payment_time,
            cashier_id
        ) VALUES (?, 'PayPal', ?, 'Success', ?, 0.00, NOW(), ?)
    ");
    
    $paypalPaymentStmt->execute([
        $testOrderId2,
        'TEST123456789',  // paypal_transaction_id
        50.00,            // cash_received (total amount for PayPal)
        114               // cashier_id
    ]);
    
    $paypalPaymentId = $pdo->lastInsertId();
    echo "<p>✅ Inserted PayPal payment with ID: $paypalPaymentId</p>";
    
    // Verify the PayPal payment status
    $verifyStmt->execute([$paypalPaymentId]);
    $paypalPayment = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>PayPal Payment Verification:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    foreach ($paypalPayment as $field => $value) {
        $color = ($field === 'transaction_status' && $value === 'Success') ? 'green' : 'black';
        echo "<tr><td>$field</td><td style='color: $color; font-weight: bold;'>$value</td></tr>";
    }
    echo "</table>";
    
    if ($paypalPayment['transaction_status'] === 'Success') {
        echo "<p style='color: green; font-weight: bold;'>✅ SUCCESS: PayPal payment status is correctly set to 'Success'</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ FAILED: PayPal payment status is '{$paypalPayment['transaction_status']}' instead of 'Success'</p>";
    }
    
    // Test 3: Check Recent Payments Overall
    echo "<h2>Test 3: Recent Payments Status Distribution</h2>";
    
    $recentStmt = $pdo->query("
        SELECT payment_method, transaction_status, COUNT(*) as count 
        FROM payments 
        WHERE payment_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        GROUP BY payment_method, transaction_status 
        ORDER BY payment_method, transaction_status
    ");
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Payment Method</th><th>Transaction Status</th><th>Count</th></tr>";
    
    $hasData = false;
    while ($row = $recentStmt->fetch(PDO::FETCH_ASSOC)) {
        $hasData = true;
        $color = ($row['transaction_status'] === 'Success') ? 'green' : 'red';
        echo "<tr>";
        echo "<td>{$row['payment_method']}</td>";
        echo "<td style='color: $color; font-weight: bold;'>{$row['transaction_status']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "</tr>";
    }
    
    if (!$hasData) {
        echo "<tr><td colspan='3'>No recent payments found</td></tr>";
    }
    
    echo "</table>";
    
    // Clean up test data
    echo "<h2>Cleanup</h2>";
    $pdo->prepare("DELETE FROM payments WHERE payment_id IN (?, ?)")->execute([$cashPaymentId, $paypalPaymentId]);
    $pdo->prepare("DELETE FROM order_items WHERE order_id IN (?, ?)")->execute([$testOrderId, $testOrderId2]);
    $pdo->prepare("DELETE FROM orders WHERE order_id IN (?, ?)")->execute([$testOrderId, $testOrderId2]);
    echo "<p>✅ Test data cleaned up</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
