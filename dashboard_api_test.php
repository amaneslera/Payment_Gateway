<?php
/**
 * Dashboard API Response Test
 * Test what the actual dashboard API returns vs what our query shows
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=pos_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Dashboard API Response Test</h1>";
    echo "<p><strong>Current Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
    
    // Test 1: Direct query (what we tested)
    echo "<h2>1. Direct Query Test (What we know works)</h2>";
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(p.payment_id) as today_transactions,
            COALESCE(SUM(o.total_amount), 0) as today_sales,
            COALESCE(AVG(o.total_amount), 0) as avg_transaction
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        WHERE DATE(p.payment_time) >= DATE_SUB(?, INTERVAL 7 DAY)
    ");
    $stmt->execute([$today]);
    $directResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Direct Query Result:</strong></p>";
    echo "<ul>";
    echo "<li>Transactions: {$directResult['today_transactions']}</li>";
    echo "<li>Sales: \${$directResult['today_sales']}</li>";
    echo "<li>Average: \${$directResult['avg_transaction']}</li>";
    echo "</ul>";
    
    // Test 2: Simulate the actual dashboard API call
    echo "<h2>2. Dashboard API Simulation</h2>";
    echo "<p>Calling the actual dashboard metrics function...</p>";
    
    // Include the dashboard API file and simulate the call
    $dashboard_api_url = "http://localhost/Payment_Gateway/src/backend/api/dashboard/dashboard_api.php?action=metrics";
    
    echo "<p><strong>API URL:</strong> <a href='$dashboard_api_url' target='_blank'>$dashboard_api_url</a></p>";
    
    // Try to call the API
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'Authorization: Bearer dummy_token_for_test',
                'Content-Type: application/json'
            ],
            'ignore_errors' => true
        ]
    ]);
    
    $api_response = @file_get_contents($dashboard_api_url, false, $context);
    
    if ($api_response) {
        echo "<p><strong>API Response:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars($api_response);
        echo "</pre>";
        
        $api_data = json_decode($api_response, true);
        if ($api_data && isset($api_data['data']['today_transactions'])) {
            echo "<p><strong>Parsed API Data:</strong></p>";
            echo "<ul>";
            echo "<li>Status: {$api_data['status']}</li>";
            echo "<li>Transactions: {$api_data['data']['today_transactions']['value']}</li>";
            echo "<li>Sales: {$api_data['data']['today_sales']['value']}</li>";
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>❌ Could not call API (authentication required)</p>";
        echo "<p>Let's test the dashboard function directly instead...</p>";
        
        // Test 3: Include and test the function directly
        echo "<h2>3. Direct Function Test</h2>";
        
        // Create a minimal version of the dashboard function
        function testGetDashboardMetrics($pdo) {
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $startOfMonth = date('Y-m-01');
            
            // Today's sales - using the updated query from our fix
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(p.payment_id) as today_transactions,
                    COALESCE(SUM(o.total_amount), 0) as today_sales,
                    COALESCE(AVG(o.total_amount), 0) as avg_transaction
                FROM payments p
                JOIN orders o ON p.order_id = o.order_id
                WHERE DATE(p.payment_time) >= DATE_SUB(?, INTERVAL 7 DAY)
            ");
            $stmt->execute([$today]);
            $todayData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'today_transactions' => [
                    'value' => intval($todayData['today_transactions'])
                ],
                'today_sales' => [
                    'value' => floatval($todayData['today_sales'])
                ],
                'avg_transaction' => [
                    'value' => floatval($todayData['avg_transaction'])
                ]
            ];
        }
        
        $functionResult = testGetDashboardMetrics($pdo);
        
        echo "<p><strong>Direct Function Result:</strong></p>";
        echo "<ul>";
        echo "<li>Transactions: {$functionResult['today_transactions']['value']}</li>";
        echo "<li>Sales: {$functionResult['today_sales']['value']}</li>";
        echo "<li>Average: {$functionResult['avg_transaction']['value']}</li>";
        echo "</ul>";
    }
    
    // Test 4: Check what the frontend should display
    echo "<h2>4. Frontend Display Check</h2>";
    echo "<div style='background-color: #e6f3ff; padding: 10px; border: 1px solid #0066cc;'>";
    echo "<p><strong>What your frontend SHOULD show:</strong></p>";
    echo "<p>• <strong>Transactions:</strong> {$directResult['today_transactions']}</p>";
    echo "<p>• <strong>Sales:</strong> \${$directResult['today_sales']}</p>";
    echo "</div>";
    
    echo "<h2>5. Troubleshooting Steps</h2>";
    echo "<div style='background-color: #fff3cd; padding: 10px; border: 1px solid #ffc107;'>";
    echo "<p><strong>If your dashboard still shows 1 transaction:</strong></p>";
    echo "<ol>";
    echo "<li><strong>Clear browser cache:</strong> Ctrl+F5 or hard refresh</li>";
    echo "<li><strong>Check browser console:</strong> F12 → Console for JavaScript errors</li>";
    echo "<li><strong>Check network tab:</strong> F12 → Network to see API calls</li>";
    echo "<li><strong>Check API endpoint:</strong> Make sure it's calling the right URL</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { white-space: pre-wrap; word-wrap: break-word; }
ul, ol { margin: 10px 0; }
a { color: #0066cc; }
</style>
