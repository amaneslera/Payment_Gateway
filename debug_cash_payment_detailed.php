<?php
/**
 * Detailed Cash Payment Debug
 * Test the exact same flow as the frontend to identify the issue
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Detailed Cash Payment Debug</h1>";
echo "<p>Current Date: " . date('Y-m-d H:i:s') . "</p>";

try {
    require_once 'config/db.php';
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
    
    echo "<h2>1. Frontend Cash Payment Data Simulation</h2>";
    
    // Simulate the exact data the frontend sends
    $frontendData = [
        'order_id' => 999, // We'll use a test order
        'payment_method' => 'Cash',
        'cash_received' => 100.00,
        'transaction_status' => 'Success'  // This is what frontend sends
    ];
    
    echo "<h3>Frontend Data:</h3>";
    echo "<pre>" . json_encode($frontendData, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<h2>2. Backend Processing Logic Test</h2>";
    
    // Test the backend logic from payments.php line 216
    $status_from_backend_logic = $frontendData['payment_method'] === 'Cash' ? 'Success' : 
                                ($frontendData['payment_method'] === 'PayPal' && isset($frontendData['status']) ? $frontendData['status'] : 'Pending');
    
    echo "<p><strong>Status calculated by backend logic:</strong> <span style='color: blue;'>'$status_from_backend_logic'</span></p>";
    
    // Check if frontend transaction_status is being used
    $frontend_status = isset($frontendData['transaction_status']) ? $frontendData['transaction_status'] : 'NOT FOUND';
    echo "<p><strong>Frontend transaction_status field:</strong> <span style='color: red;'>'$frontend_status'</span></p>";
    
    echo "<h2>3. Parameter Binding Test</h2>";
    
    // Test the parameter binding with the exact same data
    $stmt = $pdo->prepare("
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
    
    // Simulate the exact values from the backend
    $order_id = $frontendData['order_id'];
    $payment_method = $frontendData['payment_method'];
    $cash_received = $frontendData['cash_received'];
    $change_amount = 0.00; // Calculated as difference
    $paypal_transaction_id = null;
    $transaction_status = $status_from_backend_logic; // This is what backend calculates
    $cashier_id = 101; // Test user
    
    echo "<h3>Binding Parameters:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Parameter</th><th>Value</th><th>Type</th></tr>";
    echo "<tr><td>order_id</td><td>$order_id</td><td>integer (i)</td></tr>";
    echo "<tr><td>payment_method</td><td>'$payment_method'</td><td>string (s)</td></tr>";
    echo "<tr><td>cash_received</td><td>$cash_received</td><td>double (d)</td></tr>";
    echo "<tr><td>change_amount</td><td>$change_amount</td><td>double (d)</td></tr>";
    echo "<tr><td>paypal_transaction_id</td><td>" . ($paypal_transaction_id ?? 'NULL') . "</td><td>string (s)</td></tr>";
    echo "<tr><td style='background-color: yellow;'>transaction_status</td><td style='background-color: yellow;'>'$transaction_status'</td><td style='background-color: yellow;'>string (s)</td></tr>";
    echo "<tr><td>cashier_id</td><td>$cashier_id</td><td>integer (i)</td></tr>";
    echo "</table>";
    
    echo "<p><strong>bind_param type string:</strong> <code>isddssi</code></p>";
    
    $bind_result = $stmt->bind_param(
        "isddssi",
        $order_id,
        $payment_method,
        $cash_received,
        $change_amount,
        $paypal_transaction_id,
        $transaction_status,
        $cashier_id
    );
    
    if (!$bind_result) {
        echo "<p style='color: red;'>❌ bind_param FAILED: " . $stmt->error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ bind_param SUCCESS</p>";
        
        if ($stmt->execute()) {
            $payment_id = $pdo->lastInsertId();
            echo "<p style='color: green;'>✅ INSERT SUCCESS - Payment ID: $payment_id</p>";
            
            // Check what was actually inserted
            $check_stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = ?");
            $check_stmt->execute([$payment_id]);
            $inserted_row = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h3>Inserted Data:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            foreach ($inserted_row as $key => $value) {
                $bg = ($key === 'transaction_status') ? "background-color: yellow;" : "";
                $value_display = ($value === '' && $key === 'transaction_status') ? '<span style="color: red; font-weight: bold;">EMPTY STRING</span>' : $value;
                echo "<tr style='$bg'><td><strong>$key</strong></td><td>$value_display</td></tr>";
            }
            echo "</table>";
            
            // Clean up test data
            $pdo->prepare("DELETE FROM payments WHERE payment_id = ?")->execute([$payment_id]);
            echo "<p style='color: gray;'>🧹 Test data cleaned up</p>";
            
        } else {
            echo "<p style='color: red;'>❌ INSERT FAILED: " . $stmt->error . "</p>";
        }
    }
    
    echo "<h2>4. Comparison with PayPal Success Pattern</h2>";
    
    // Show how PayPal payments work
    $paypal_stmt = $pdo->prepare("SELECT payment_id, payment_method, transaction_status FROM payments WHERE payment_method = 'PayPal' AND transaction_status = 'Success' LIMIT 1");
    $paypal_stmt->execute();
    $paypal_example = $paypal_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($paypal_example) {
        echo "<p>✅ PayPal success example found:</p>";
        echo "<pre>" . json_encode($paypal_example, JSON_PRETTY_PRINT) . "</pre>";
        echo "<p><em>PayPal payments use direct SQL: INSERT ... VALUES (..., 'Success', ...)</em></p>";
    } else {
        echo "<p>❌ No successful PayPal payments found for comparison</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
table { margin: 10px 0; background-color: white; }
th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; font-weight: bold; }
tr:nth-child(even) { background-color: #f9f9f9; }
h1, h2, h3 { color: #333; }
pre { background-color: #f8f8f8; padding: 10px; border-radius: 4px; }
</style>";
?>
