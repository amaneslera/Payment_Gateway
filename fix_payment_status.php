<?php
/**
 * Fix Payment Status Script
 * Updates all 'Pending' and empty transaction_status entries to 'Success'
 * since these represent completed cash payments that should be successful
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Payment Status Fix Script</h1>";
echo "<p>This script will update all 'Pending' and empty payment statuses to 'Success'</p>";

try {
    // Load database connection
    require_once __DIR__ . '/src/config/db.php';
    echo "<p style='color: green;'>✅ Database connected successfully</p>";

    // Check current state
    echo "<h2>Current Payment Status Distribution:</h2>";
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN transaction_status = '' THEN 'EMPTY'
                WHEN transaction_status IS NULL THEN 'NULL'
                ELSE transaction_status 
            END as status_label,
            transaction_status,
            COUNT(*) as count
        FROM payments
        GROUP BY transaction_status
        ORDER BY count DESC
    ");
    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>Status</th><th>Raw Value</th><th>Count</th></tr>";
    foreach ($statusCounts as $status) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($status['status_label']) . "</td>";
        echo "<td>'" . htmlspecialchars($status['transaction_status']) . "'</td>";
        echo "<td>" . $status['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Find payments that need to be updated
    echo "<h2>Payments to be Updated:</h2>";
    $stmt = $pdo->query("
        SELECT payment_id, order_id, payment_method, transaction_status, payment_time
        FROM payments 
        WHERE transaction_status = 'Pending' OR transaction_status = '' OR transaction_status IS NULL
        ORDER BY payment_id
    ");
    $paymentsToUpdate = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($paymentsToUpdate)) {
        echo "<p style='color: green;'>✅ No payments need to be updated. All payments already have 'Success' status.</p>";
    } else {
        echo "<p>Found <strong>" . count($paymentsToUpdate) . "</strong> payments that need to be updated:</p>";
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>Payment ID</th><th>Order ID</th><th>Method</th><th>Current Status</th><th>Date</th></tr>";
        foreach ($paymentsToUpdate as $payment) {
            $currentStatus = $payment['transaction_status'] === '' ? '(EMPTY)' : 
                           ($payment['transaction_status'] === null ? '(NULL)' : $payment['transaction_status']);
            echo "<tr>";
            echo "<td>" . $payment['payment_id'] . "</td>";
            echo "<td>" . $payment['order_id'] . "</td>";
            echo "<td>" . $payment['payment_method'] . "</td>";
            echo "<td style='color: red;'>" . htmlspecialchars($currentStatus) . "</td>";
            echo "<td>" . $payment['payment_time'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Ask for confirmation
        echo "<div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0;'>";
        echo "<h3>⚠️ Confirmation Required</h3>";
        echo "<p>This will update <strong>" . count($paymentsToUpdate) . "</strong> payment records to 'Success' status.</p>";
        echo "<p>These payments represent completed transactions that should have been marked as successful.</p>";
        echo "<form method='POST' style='margin: 10px 0;'>";
        echo "<input type='hidden' name='confirm_update' value='1'>";
        echo "<button type='submit' style='background-color: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer;'>Update Payment Statuses</button>";
        echo "</form>";
        echo "</div>";
    }

    // Process the update if confirmed
    if (isset($_POST['confirm_update']) && $_POST['confirm_update'] == '1') {
        echo "<h2>Processing Updates...</h2>";
        
        try {
            $pdo->beginTransaction();
            
            // Update all pending/empty statuses to Success
            $updateStmt = $pdo->prepare("
                UPDATE payments 
                SET transaction_status = 'Success' 
                WHERE transaction_status = 'Pending' OR transaction_status = '' OR transaction_status IS NULL
            ");
            
            $updateStmt->execute();
            $affectedRows = $updateStmt->rowCount();
            
            $pdo->commit();
            
            echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0;'>";
            echo "<h3>✅ Update Successful!</h3>";
            echo "<p><strong>Updated {$affectedRows} payment records</strong> to 'Success' status.</p>";
            echo "</div>";
            
            // Show updated status distribution
            echo "<h2>Updated Payment Status Distribution:</h2>";
            $stmt = $pdo->query("
                SELECT 
                    CASE 
                        WHEN transaction_status = '' THEN 'EMPTY'
                        WHEN transaction_status IS NULL THEN 'NULL'
                        ELSE transaction_status 
                    END as status_label,
                    transaction_status,
                    COUNT(*) as count
                FROM payments
                GROUP BY transaction_status
                ORDER BY count DESC
            ");
            $newStatusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr style='background-color: #f2f2f2;'><th>Status</th><th>Raw Value</th><th>Count</th></tr>";
            foreach ($newStatusCounts as $status) {
                $highlight = ($status['status_label'] === 'Success') ? 'background-color: #d4edda;' : '';
                echo "<tr style='{$highlight}'>";
                echo "<td>" . htmlspecialchars($status['status_label']) . "</td>";
                echo "<td>'" . htmlspecialchars($status['transaction_status']) . "'</td>";
                echo "<td>" . $status['count'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } catch (Exception $e) {
            $pdo->rollback();
            echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 20px 0;'>";
            echo "<h3>❌ Update Failed</h3>";
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    max-width: 1200px;
}
table { 
    margin: 10px 0; 
    width: 100%;
}
th, td { 
    padding: 8px 12px; 
    text-align: left; 
    border: 1px solid #ddd;
}
th { 
    background-color: #f2f2f2; 
    font-weight: bold;
}
button {
    font-size: 16px;
    border-radius: 4px;
}
button:hover {
    opacity: 0.9;
}
</style>
