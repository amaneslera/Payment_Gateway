<?php
// Debug script to test sales API step by step
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    echo "=== SALES API DEBUG ===\n\n";
    
    // Step 1: Test database connection
    echo "1. Testing database connection...\n";
    require_once __DIR__ . '/../../../config/db.php';
    
    if ($pdo) {
        echo "✅ Database connected successfully\n\n";
    } else {
        echo "❌ Database connection failed\n";
        exit;
    }
    
    // Step 2: Test auth middleware loading
    echo "2. Testing auth middleware...\n";
    try {
        require_once __DIR__ . '/../../middleware/auth_middleware.php';
        echo "✅ Auth middleware loaded successfully\n\n";
    } catch (Exception $e) {
        echo "❌ Auth middleware error: " . $e->getMessage() . "\n\n";
    }
    
    // Step 3: Check if tables exist and have data
    echo "3. Checking database tables...\n";
    $tables = ['orders', 'order_items', 'products', 'categories'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "✅ Table '$table': $count records\n";
        } catch (PDOException $e) {
            echo "❌ Table '$table' error: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    // Step 4: Test a simple sales query
    echo "4. Testing basic sales query...\n";
    try {
        $sql = "SELECT 
                    COUNT(DISTINCT o.order_id) as total_transactions,
                    COALESCE(SUM(oi.quantity), 0) as total_items_sold,
                    COALESCE(SUM(o.total_amount), 0) as total_sales
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.payment_status = 'Paid'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "✅ Basic query successful:\n";
        echo "   - Transactions: " . $result['total_transactions'] . "\n";
        echo "   - Items sold: " . $result['total_items_sold'] . "\n";
        echo "   - Total sales: $" . $result['total_sales'] . "\n\n";
        
    } catch (PDOException $e) {
        echo "❌ Basic query failed: " . $e->getMessage() . "\n\n";
    }
    
    // Step 5: Test date filtering
    echo "5. Testing date filtering...\n";
    try {
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        
        $sql = "SELECT COUNT(*) as count FROM orders 
                WHERE order_date BETWEEN ? AND ? AND payment_status = 'Paid'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo "✅ Date filtering works: $count orders in last 30 days\n\n";
        
    } catch (PDOException $e) {
        echo "❌ Date filtering failed: " . $e->getMessage() . "\n\n";
    }
    
    // Step 6: Check order dates specifically
    echo "6. Checking actual order dates...\n";
    try {
        $stmt = $pdo->prepare("SELECT order_id, order_date, total_amount, payment_status FROM orders LIMIT 5");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orders as $order) {
            echo "   Order {$order['order_id']}: {$order['order_date']} - ${$order['total_amount']} ({$order['payment_status']})\n";
        }
        echo "\n";
        
    } catch (PDOException $e) {
        echo "❌ Order dates check failed: " . $e->getMessage() . "\n\n";
    }
    
    // Step 7: Test with actual date range from orders
    echo "7. Testing with actual date range from existing orders...\n";
    try {
        // Get the actual date range of orders
        $stmt = $pdo->prepare("SELECT MIN(DATE(order_date)) as min_date, MAX(DATE(order_date)) as max_date FROM orders");
        $stmt->execute();
        $dateRange = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "   Actual order date range: {$dateRange['min_date']} to {$dateRange['max_date']}\n";
        
        // Test summary with actual date range
        $sql = "SELECT 
                    COUNT(DISTINCT o.order_id) as total_transactions,
                    COALESCE(SUM(oi.quantity), 0) as total_items_sold,
                    COALESCE(SUM(o.total_amount), 0) as total_sales
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.payment_status = 'Paid'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dateRange['min_date'] . ' 00:00:00', $dateRange['max_date'] . ' 23:59:59']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "✅ Summary with actual dates:\n";
        echo "   - Transactions: " . $result['total_transactions'] . "\n";
        echo "   - Items sold: " . $result['total_items_sold'] . "\n";
        echo "   - Total sales: $" . $result['total_sales'] . "\n\n";
        
    } catch (PDOException $e) {
        echo "❌ Actual date range test failed: " . $e->getMessage() . "\n\n";
    }
    
    // Step 8: Check what happens with getSalesSummary function
    echo "8. Testing getSalesSummary function simulation...\n";
    try {
        // Get actual date range again for proper testing
        $stmt = $pdo->prepare("SELECT MIN(DATE(order_date)) as min_date, MAX(DATE(order_date)) as max_date FROM orders");
        $stmt->execute();
        $dateRange = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $startDate = $dateRange['min_date'];
        $endDate = $dateRange['max_date'];
        
        echo "   Using dates: $startDate to $endDate\n";
        
        // Test the exact query from getSalesSummary
        $sql = "SELECT 
                    COUNT(DISTINCT o.order_id) as total_transactions,
                    COALESCE(SUM(oi.quantity), 0) as total_items_sold,
                    COALESCE(SUM(o.total_amount), 0) as total_sales,
                    COALESCE(SUM(oi.quantity * p.cost_price), 0) as total_cost,
                    COALESCE(AVG(o.total_amount), 0) as average_sale
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.payment_status = 'Paid'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $currentPeriod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "✅ getSalesSummary simulation successful:\n";
        echo "   " . json_encode($currentPeriod, JSON_PRETTY_PRINT) . "\n\n";
        
    } catch (Exception $e) {
        echo "❌ getSalesSummary simulation failed: " . $e->getMessage() . "\n\n";
    }
    
    echo "=== DEBUG COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "❌ Critical error: " . $e->getMessage() . "\n";
}
?>
