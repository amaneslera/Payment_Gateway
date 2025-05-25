<?php
// Enable error reporting but capture it instead of displaying
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
});

// Buffer output to prevent any unwanted content
ob_start();

header('Content-Type: text/csv');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Include required files
    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../middleware/auth_middleware.php';

    // Check if user is authenticated
    $token = $_POST['token'] ?? '';
    
    if (!$token) {
        throw new Exception('Authentication token required');
    }
    
    // Validate token manually
    $jwt_parts = explode('.', $token);
    if (count($jwt_parts) !== 3) {
        throw new Exception('Invalid token format');
    }
    
    // For now, we'll do a basic validation. In production, properly validate the JWT
    $user = AuthMiddleware::validateTokenFromString($token);
    
    if (!$user) {
        throw new Exception('Invalid or expired token');
    }

    // Only Admin can export sales reports
    if ($user['role'] !== 'Admin') {
        throw new Exception('Insufficient permissions. Admin access required.');
    }

    // Process export request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        exportSalesReport();
    } else {
        throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    error_log("Sales Export Error: " . $e->getMessage());
    
    // Clear any CSV output and send JSON error instead
    if (ob_get_contents()) {
        ob_clean();
    }
    
    header('Content-Type: application/json');
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Clean the output buffer if not already done
    if (ob_get_contents()) {
        ob_end_flush();
    }
}

function exportSalesReport() {
    global $pdo;
    
    $startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_POST['end_date'] ?? date('Y-m-d');
    $categoryId = $_POST['category_id'] ?? null;
    
    // Validate dates
    if (!validateDate($startDate) || !validateDate($endDate)) {
        throw new Exception('Invalid date format');
    }
    
    try {
        // Set CSV headers
        $filename = "sales-report-{$startDate}-to-{$endDate}.csv";
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        
        // Clear any previous output
        if (ob_get_contents()) {
            ob_clean();
        }
        
        // Create file handle for output
        $output = fopen('php://output', 'w');
        
        // Write CSV headers
        fputcsv($output, [
            'Product ID',
            'Product Name',
            'Category',
            'Units Sold',
            'Unit Price',
            'Total Revenue',
            'Cost Price',
            'Total Cost',
            'Profit',
            'Profit Margin (%)',
            'Period'
        ]);
        
        // Build query with optional category filter
        $categoryFilter = '';
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        
        if ($categoryId) {
            $categoryFilter = ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        
        $sql = "SELECT 
                    p.product_id,
                    p.name as product_name,
                    c.category_name,
                    p.price as unit_price,
                    p.cost_price,
                    COALESCE(SUM(oi.quantity), 0) as units_sold,
                    COALESCE(SUM(oi.subtotal), 0) as total_revenue,
                    COALESCE(SUM(oi.quantity * p.cost_price), 0) as total_cost,
                    (COALESCE(SUM(oi.subtotal), 0) - COALESCE(SUM(oi.quantity * p.cost_price), 0)) as profit,
                    CASE 
                        WHEN COALESCE(SUM(oi.subtotal), 0) > 0 
                        THEN ROUND(((COALESCE(SUM(oi.subtotal), 0) - COALESCE(SUM(oi.quantity * p.cost_price), 0)) / COALESCE(SUM(oi.subtotal), 0)) * 100, 2)
                        ELSE 0 
                    END as profit_margin
                FROM products p
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.payment_status = 'Paid'
                $categoryFilter
                GROUP BY p.product_id, p.name, c.category_name, p.price, p.cost_price
                HAVING units_sold > 0
                ORDER BY total_revenue DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Write data rows
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['product_id'],
                $row['product_name'],
                $row['category_name'] ?: 'N/A',
                $row['units_sold'],
                number_format($row['unit_price'], 2),
                number_format($row['total_revenue'], 2),
                number_format($row['cost_price'] ?: 0, 2),
                number_format($row['total_cost'], 2),
                number_format($row['profit'], 2),
                $row['profit_margin'] . '%',
                "{$startDate} to {$endDate}"
            ]);
        }
        
        // Add summary row
        fputcsv($output, []); // Empty row
        fputcsv($output, ['=== SUMMARY ===']);
        
        // Get summary data
        $summarySQL = "SELECT 
                        COUNT(DISTINCT o.order_id) as total_transactions,
                        COALESCE(SUM(oi.quantity), 0) as total_items_sold,
                        COALESCE(SUM(o.total_amount), 0) as total_sales,
                        COALESCE(SUM(oi.quantity * p.cost_price), 0) as total_cost,
                        COALESCE(AVG(o.total_amount), 0) as average_sale
                    FROM orders o
                    LEFT JOIN order_items oi ON o.order_id = oi.order_id
                    LEFT JOIN products p ON oi.product_id = p.product_id
                    WHERE o.order_date BETWEEN ? AND ?
                    AND o.payment_status = 'Paid'
                    $categoryFilter";
        
        $stmt = $pdo->prepare($summarySQL);
        $stmt->execute($params);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $totalProfit = $summary['total_sales'] - $summary['total_cost'];
        $profitMargin = $summary['total_sales'] > 0 ? 
            (($totalProfit / $summary['total_sales']) * 100) : 0;
        
        fputcsv($output, ['Total Transactions', $summary['total_transactions']]);
        fputcsv($output, ['Total Items Sold', $summary['total_items_sold']]);
        fputcsv($output, ['Total Sales', number_format($summary['total_sales'], 2)]);
        fputcsv($output, ['Total Cost', number_format($summary['total_cost'], 2)]);
        fputcsv($output, ['Total Profit', number_format($totalProfit, 2)]);
        fputcsv($output, ['Profit Margin', number_format($profitMargin, 2) . '%']);
        fputcsv($output, ['Average Sale', number_format($summary['average_sale'], 2)]);
        fputcsv($output, ['Report Generated', date('Y-m-d H:i:s')]);
        
        fclose($output);
        
    } catch (PDOException $e) {
        error_log("Database error in exportSalesReport: " . $e->getMessage());
        throw new Exception('Database error occurred while generating report');
    }
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
?>
