<?php
// Simple sales API without output buffering
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
    $user = AuthMiddleware::validateToken();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    // Only Admin can access sales reports
    if ($user->role !== 'Admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions. Admin access required.']);
        exit;
    }

    // Process request based on method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'summary':
            getSalesSummary($pdo);
            break;
        case 'top_products':
            getTopProducts($pdo);
            break;
        case 'product_sales':
            getProductSalesReport($pdo);
            break;
        case 'categories':
            getCategories($pdo);
            break;
        case 'sales_trends':
            getSalesTrends($pdo);
            break;
        case 'category_sales':
            getCategorySales($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action specified']);
            exit;
    }

} catch (Exception $e) {
    error_log("Sales API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getSalesSummary($pdo) {
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $categoryId = $_GET['category_id'] ?? null;
    
    // Validate dates
    if (!validateDate($startDate) || !validateDate($endDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format']);
        return;
    }
    
    try {
        // Build base query with optional category filter
        $categoryFilter = '';
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        
        if ($categoryId && $categoryId !== '') {
            $categoryFilter = ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        
        // Get current period summary
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
                AND o.payment_status = 'Paid'
                $categoryFilter";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $currentPeriod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate previous period for comparison
        $periodDays = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1;
        $prevStartDate = date('Y-m-d', strtotime($startDate . " -$periodDays days"));
        $prevEndDate = date('Y-m-d', strtotime($endDate . " -$periodDays days"));
        
        $prevParams = [$prevStartDate . ' 00:00:00', $prevEndDate . ' 23:59:59'];
        if ($categoryId && $categoryId !== '') {
            $prevParams[] = $categoryId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($prevParams);
        $previousPeriod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate changes
        $changes = calculateChanges($currentPeriod, $previousPeriod);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'current' => $currentPeriod,
                'changes' => $changes,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'category_id' => $categoryId
                ]
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in getSalesSummary: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

function getTopProducts($pdo) {
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $categoryId = $_GET['category_id'] ?? null;
    $limit = (int)($_GET['limit'] ?? 10);
    
    // Validate inputs
    if (!validateDate($startDate) || !validateDate($endDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format']);
        return;
    }
    
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    
    try {
        $categoryFilter = '';
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        
        if ($categoryId && $categoryId !== '') {
            $categoryFilter = ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        
        $sql = "SELECT 
                    p.product_id,
                    p.name as product_name,
                    c.category_name,
                    SUM(oi.quantity) as units_sold,
                    SUM(oi.subtotal) as total_revenue,
                    SUM(oi.quantity * p.cost_price) as total_cost,
                    (SUM(oi.subtotal) - SUM(oi.quantity * p.cost_price)) as profit,
                    CASE 
                        WHEN SUM(oi.subtotal) > 0 
                        THEN ROUND(((SUM(oi.subtotal) - SUM(oi.quantity * p.cost_price)) / SUM(oi.subtotal)) * 100, 2)
                        ELSE 0 
                    END as profit_margin
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                JOIN products p ON oi.product_id = p.product_id
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.payment_status = 'Paid'
                $categoryFilter
                GROUP BY p.product_id, p.name, c.category_name
                ORDER BY total_revenue DESC
                LIMIT $limit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add ranking
        foreach ($products as $index => &$product) {
            $product['rank'] = $index + 1;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $products
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in getTopProducts: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

function getProductSalesReport($pdo) {
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $categoryId = $_GET['category_id'] ?? null;
    $search = $_GET['search'] ?? '';
    $sortBy = $_GET['sort_by'] ?? 'revenue';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    // Validate inputs
    if (!validateDate($startDate) || !validateDate($endDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format']);
        return;
    }
    
    $allowedSortFields = ['revenue', 'quantity', 'profit', 'name'];
    if (!in_array($sortBy, $allowedSortFields)) {
        $sortBy = 'revenue';
    }
    
    try {
        $whereConditions = ['o.order_date BETWEEN ? AND ?', 'o.payment_status = ?'];
        $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59', 'Paid'];
        
        if ($categoryId && $categoryId !== '') {
            $whereConditions[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }
        
        if ($search && $search !== '') {
            $whereConditions[] = '(p.name LIKE ? OR COALESCE(p.sku, "") LIKE ? OR COALESCE(p.barcode, "") LIKE ?)';
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Order by clause
        $orderByMap = [
            'revenue' => 'total_revenue DESC',
            'quantity' => 'units_sold DESC',
            'profit' => 'profit DESC',
            'name' => 'p.name ASC'
        ];
        $orderBy = $orderByMap[$sortBy];
        
        // Count total records
        $countSql = "SELECT COUNT(DISTINCT p.product_id) as total
                     FROM products p
                     LEFT JOIN order_items oi ON p.product_id = oi.product_id
                     LEFT JOIN orders o ON oi.order_id = o.order_id
                     LEFT JOIN categories c ON p.category_id = c.category_id
                     $whereClause";
        
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get paginated results
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
                $whereClause
                GROUP BY p.product_id, p.name, c.category_name, p.price, p.cost_price
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'products' => $products,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalRecords,
                    'pages' => ceil($totalRecords / $limit)
                ]
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in getProductSalesReport: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

function getCategories($pdo) {
    try {
        $sql = "SELECT category_id, category_name, description FROM categories ORDER BY category_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in getCategories: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

function getSalesTrends($pdo) {
    $days = min(365, max(7, (int)($_GET['days'] ?? 7)));
    $categoryId = $_GET['category_id'] ?? null;
    
    try {
        $categoryFilter = '';
        $params = [$days];
        
        if ($categoryId && $categoryId !== '') {
            $categoryFilter = ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        
        $sql = "SELECT 
                    DATE(o.order_date) as sale_date,
                    COALESCE(SUM(o.total_amount), 0) as daily_sales,
                    COUNT(DISTINCT o.order_id) as daily_transactions,
                    COALESCE(SUM(oi.quantity), 0) as daily_items
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                AND o.payment_status = 'Paid'
                $categoryFilter
                GROUP BY DATE(o.order_date)
                ORDER BY sale_date ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $trends = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $trends
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in getSalesTrends: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

function getCategorySales($pdo) {
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    
    if (!validateDate($startDate) || !validateDate($endDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date format']);
        return;
    }
    
    try {
        $sql = "SELECT 
                    c.category_name,
                    COALESCE(SUM(oi.subtotal), 0) as total_sales,
                    COALESCE(SUM(oi.quantity), 0) as total_quantity,
                    COUNT(DISTINCT p.product_id) as product_count
                FROM categories c
                LEFT JOIN products p ON c.category_id = p.category_id
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.payment_status = 'Paid'
                GROUP BY c.category_id, c.category_name
                HAVING total_sales > 0
                ORDER BY total_sales DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $categorySales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $categorySales
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error in getCategorySales: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

function calculateChanges($current, $previous) {
    $changes = [];
    
    foreach (['total_sales', 'total_items_sold', 'total_transactions', 'average_sale'] as $metric) {
        $currentValue = (float)($current[$metric] ?? 0);
        $previousValue = (float)($previous[$metric] ?? 0);
        
        if ($previousValue > 0) {
            $changePercent = (($currentValue - $previousValue) / $previousValue) * 100;
        } else {
            $changePercent = $currentValue > 0 ? 100 : 0;
        }
        
        $changes[$metric] = [
            'value' => $currentValue,
            'previous_value' => $previousValue,
            'change_percent' => round($changePercent, 2),
            'change_direction' => $changePercent > 0 ? 'up' : ($changePercent < 0 ? 'down' : 'same')
        ];
    }
    
    return $changes;
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
?>
