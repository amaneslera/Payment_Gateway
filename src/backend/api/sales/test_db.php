<?php
// Test database connection and tables for sales API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Include database config
    require_once __DIR__ . '/../../../config/db.php';
    
    $result = [
        'success' => true,
        'database_connection' => false,
        'tables' => [],
        'sample_data' => []
    ];
    
    // Test database connection
    if ($pdo) {
        $result['database_connection'] = true;
        
        // Check if required tables exist
        $requiredTables = ['orders', 'order_items', 'products', 'categories', 'users'];
        
        foreach ($requiredTables as $table) {
            try {
                $stmt = $pdo->prepare("DESCRIBE $table");
                $stmt->execute();
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $result['tables'][$table] = [
                    'exists' => true,
                    'columns' => array_column($columns, 'Field')
                ];
                
                // Get sample count
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
                $stmt->execute();
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                $result['tables'][$table]['count'] = $count;
                
            } catch (PDOException $e) {
                $result['tables'][$table] = [
                    'exists' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // Test a simple query like the sales API would do
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as order_count FROM orders WHERE payment_status = 'Paid'");
            $stmt->execute();
            $orderCount = $stmt->fetch(PDO::FETCH_ASSOC)['order_count'];
            $result['sample_data']['paid_orders'] = $orderCount;
        } catch (PDOException $e) {
            $result['sample_data']['error'] = $e->getMessage();
        }
        
    } else {
        $result['database_connection'] = false;
        $result['error'] = 'No PDO connection available';
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
