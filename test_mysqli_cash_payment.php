<?php
echo "=== MySQLi Cash Payment Test ===\n";

try {
    // Use the exact same database connection as payments.php
    require_once 'src/config/config.php';
    require_once 'src/config/db.php';
    
    $conn = getConnection();
    echo "✅ MySQLi connection established\n";
    
    // Get a real order ID
    $result = $conn->query("SELECT order_id FROM orders ORDER BY order_id DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $real_order_id = $row['order_id'];
        echo "Using real order ID: $real_order_id\n";
    } else {
        echo "❌ No orders found\n";
        exit;
    }
    
    // Test the EXACT code from payments.php
    echo "\n=== Exact payments.php Logic Test ===\n";
    
    // Simulate exact frontend data
    $data = [
        'order_id' => $real_order_id,
        'payment_method' => 'Cash',
        'cash_received' => 100.00,
        'transaction_status' => 'Success'  // What frontend sends
    ];
    
    // Exact logic from payments.php line 216 (this ignores frontend transaction_status!)
    $status = $data['payment_method'] === 'Cash' ? 'Success' : 
             ($data['payment_method'] === 'PayPal' && isset($data['status']) ? $data['status'] : 'Pending');
    
    echo "Frontend sends: transaction_status = '" . $data['transaction_status'] . "'\n";
    echo "Backend calculates: status = '$status'\n";
    echo "Status type: " . gettype($status) . "\n";
    echo "Status length: " . strlen($status) . "\n";
    
    // Exact MySQLi preparation from payments.php
    $stmt = $conn->prepare("
        INSERT INTO payments (
            order_id, 
            payment_method, 
            cash_received,
            change_amount, 
            paypal_transaction_id, 
            transaction_status,
            payment_time, 
            cashier_id
        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    
    if (!$stmt) {
        echo "❌ Prepare failed: " . $conn->error . "\n";
        exit;
    }
    
    // Exact same variables as payments.php
    $cashReceived = 100.00;
    $changeAmount = 0.00;
    $paypalTransactionId = null;
    $userId = 101;
    
    echo "\nBefore bind_param:\n";
    echo "  order_id: {$data['order_id']} (type: " . gettype($data['order_id']) . ")\n";
    echo "  payment_method: '{$data['payment_method']}' (type: " . gettype($data['payment_method']) . ")\n";
    echo "  cashReceived: $cashReceived (type: " . gettype($cashReceived) . ")\n";
    echo "  changeAmount: $changeAmount (type: " . gettype($changeAmount) . ")\n";
    echo "  paypalTransactionId: " . ($paypalTransactionId ?? 'NULL') . " (type: " . gettype($paypalTransactionId) . ")\n";
    echo "  status: '$status' (type: " . gettype($status) . ", length: " . strlen($status) . ")\n";
    echo "  userId: $userId (type: " . gettype($userId) . ")\n";
    
    // EXACT bind_param call from payments.php line 227
    $bind_result = $stmt->bind_param(
        "isddssi",
        $data['order_id'],
        $data['payment_method'],
        $cashReceived,
        $changeAmount,
        $paypalTransactionId,
        $status,
        $userId
    );
    
    if (!$bind_result) {
        echo "❌ bind_param failed: " . $stmt->error . "\n";
        exit;
    }
    
    echo "✅ bind_param successful\n";
    
    // Execute
    if ($stmt->execute()) {
        $payment_id = $conn->insert_id;
        echo "✅ Execute successful - Payment ID: $payment_id\n";
        
        // Check what was actually inserted using MySQLi
        $check_stmt = $conn->prepare("SELECT payment_id, payment_method, transaction_status, CHAR_LENGTH(transaction_status) as status_length FROM payments WHERE payment_id = ?");
        $check_stmt->bind_param("i", $payment_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $inserted_row = $check_result->fetch_assoc();
        
        echo "\nActually inserted data:\n";
        echo "  payment_method: '" . $inserted_row['payment_method'] . "'\n";
        echo "  transaction_status: '" . $inserted_row['transaction_status'] . "'\n";
        echo "  status_length: " . $inserted_row['status_length'] . "\n";
        echo "  is_empty_string: " . ($inserted_row['transaction_status'] === '' ? 'YES' : 'NO') . "\n";
        echo "  is_null: " . ($inserted_row['transaction_status'] === null ? 'YES' : 'NO') . "\n";
        
        if ($inserted_row['transaction_status'] === '') {
            echo "🚨 PROBLEM CONFIRMED: transaction_status inserted as EMPTY STRING!\n";
        } else {
            echo "✅ transaction_status inserted correctly\n";
        }
        
        // Clean up
        $delete_stmt = $conn->prepare("DELETE FROM payments WHERE payment_id = ?");
        $delete_stmt->bind_param("i", $payment_id);
        $delete_stmt->execute();
        echo "\n🧹 Test data cleaned up\n";
        
    } else {
        echo "❌ Execute failed: " . $stmt->error . "\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "=== End MySQLi Test ===\n";
?>
