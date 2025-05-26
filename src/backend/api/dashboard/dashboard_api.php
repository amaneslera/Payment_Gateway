<?php
/**
 * Dashboard API Endpoint
 * Provides comprehensive dashboard data for the overview interface
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Set Philippines timezone
    date_default_timezone_set('Asia/Manila');
    
    // Include required files
    require_once __DIR__ . '/../../../config/db.php';
    require_once __DIR__ . '/../../middleware/auth_middleware.php';

    // Verify authentication
    $user = AuthMiddleware::validateToken();
    if (!$user) {
        exit; // Middleware handles the error response
    }

    // Check if user has Admin role
    if (!AuthMiddleware::checkRole($user, ['Admin', 'admin'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions']);
        exit;
    }

    // Get the request action
    $action = $_GET['action'] ?? 'overview';

    switch ($action) {
        case 'overview':
            getDashboardOverview($pdo);
            break;
        case 'metrics':
            getDashboardMetrics($pdo);
            break;
        case 'recent_transactions':
            getRecentTransactions($pdo);
            break;
        case 'system_status':
            getSystemStatus($pdo);
            break;
        default:
            getDashboardOverview($pdo);
    }

} catch (Exception $e) {
    error_log("Dashboard API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

/**
 * Get comprehensive dashboard overview data
 */
function getDashboardOverview($pdo) {
    try {
        $today = date('Y-m-d');
        $startOfMonth = date('Y-m-01');
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));

        // Key Performance Metrics
        $metrics = getDashboardMetrics($pdo, false);

        // Recent Transactions (last 10)
        $recentTransactions = getRecentTransactions($pdo, false, 10);

        // Cash Registry Summary
        $cashRegistry = getCashRegistrySummary($pdo);

        // Inventory Status
        $inventoryStatus = getInventoryStatus($pdo);

        // System Status
        $systemStatus = getSystemStatus($pdo, false);

        // Payment Method Breakdown for today
        $paymentBreakdown = getPaymentMethodBreakdown($pdo, $today);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'metrics' => $metrics,
                'recent_transactions' => $recentTransactions,
                'cash_registry' => $cashRegistry,
                'inventory_status' => $inventoryStatus,
                'system_status' => $systemStatus,
                'payment_breakdown' => $paymentBreakdown,
                'last_updated' => date('Y-m-d H:i:s')
            ]
        ]);

    } catch (Exception $e) {
        error_log("Dashboard overview error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch dashboard data: ' . $e->getMessage()
        ]);
    }
}

/**
 * Get key performance metrics
 */
function getDashboardMetrics($pdo, $output = true) {
    try {
        // Set Philippines timezone for accurate date calculations
        date_default_timezone_set('Asia/Manila');
        
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $startOfMonth = date('Y-m-01');        // Today's transactions - Count ONLY today's payments
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(p.payment_id) as today_transactions,
                COALESCE(SUM(o.total_amount), 0) as today_sales,
                COALESCE(AVG(o.total_amount), 0) as avg_transaction
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.order_id
            WHERE DATE(p.payment_time) = ?
        ");
        $stmt->execute([$today]);
        $todayData = $stmt->fetch(PDO::FETCH_ASSOC);        // Yesterday's transactions - Count all payments
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(p.payment_id) as yesterday_transactions,
                COALESCE(SUM(o.total_amount), 0) as yesterday_sales
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.order_id
            WHERE DATE(p.payment_time) = ?
        ");
        $stmt->execute([$yesterday]);
        $yesterdayData = $stmt->fetch(PDO::FETCH_ASSOC);        // Monthly transactions - Count all payments
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(p.payment_id) as monthly_transactions,
                COALESCE(SUM(o.total_amount), 0) as monthly_sales
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.order_id
            WHERE DATE(p.payment_time) >= ?
        ");
        $stmt->execute([$startOfMonth]);
        $monthlyData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Total users count
        $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM user WHERE role IN ('Admin', 'Cashier', 'Manager')");
        $usersData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Low stock items count
        $stmt = $pdo->query("
            SELECT COUNT(*) as low_stock_count 
            FROM products 
            WHERE stock_quantity <= min_stock_level AND stock_quantity > 0
        ");
        $lowStockData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Out of stock items count
        $stmt = $pdo->query("SELECT COUNT(*) as out_of_stock_count FROM products WHERE stock_quantity = 0");
        $outOfStockData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate percentage changes
        $salesChange = $yesterdayData['yesterday_sales'] > 0 
            ? (($todayData['today_sales'] - $yesterdayData['yesterday_sales']) / $yesterdayData['yesterday_sales']) * 100 
            : 0;

        $transactionChange = $yesterdayData['yesterday_transactions'] > 0 
            ? (($todayData['today_transactions'] - $yesterdayData['yesterday_transactions']) / $yesterdayData['yesterday_transactions']) * 100 
            : 0;

        $metrics = [
            'today_sales' => [
                'value' => floatval($todayData['today_sales']),
                'change' => round($salesChange, 1),
                'trend' => $salesChange >= 0 ? 'up' : 'down'
            ],
            'today_transactions' => [
                'value' => intval($todayData['today_transactions']),
                'change' => round($transactionChange, 1),
                'trend' => $transactionChange >= 0 ? 'up' : 'down'
            ],
            'monthly_sales' => [
                'value' => floatval($monthlyData['monthly_sales']),
                'transactions' => intval($monthlyData['monthly_transactions'])
            ],
            'avg_transaction' => [
                'value' => floatval($todayData['avg_transaction'])
            ],
            'total_users' => [
                'value' => intval($usersData['total_users'])
            ],
            'inventory_alerts' => [
                'low_stock' => intval($lowStockData['low_stock_count']),
                'out_of_stock' => intval($outOfStockData['out_of_stock_count'])
            ]
        ];

        if ($output) {
            echo json_encode([
                'status' => 'success',
                'data' => $metrics
            ]);
        } else {
            return $metrics;
        }

    } catch (Exception $e) {
        error_log("Dashboard metrics error: " . $e->getMessage());
        if ($output) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch metrics: ' . $e->getMessage()
            ]);
        }
        return null;
    }
}

/**
 * Get recent transactions
 */
function getRecentTransactions($pdo, $output = true, $limit = 10) {
    try {
        // Set Philippines timezone for accurate time formatting
        date_default_timezone_set('Asia/Manila');
          $stmt = $pdo->prepare("
            SELECT 
                p.payment_id,
                o.order_id,
                p.payment_method,
                o.total_amount,
                p.payment_time,
                u.username as cashier_name,
                CONCAT(DATE_FORMAT(p.payment_time, '%Y%m%d'), '-', LPAD(p.payment_id, 3, '0')) as invoice_no
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.order_id
            LEFT JOIN user u ON p.cashier_id = u.user_id
            ORDER BY p.payment_time DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);        // Format the data
        $formattedTransactions = array_map(function($transaction) {
            return [
                'payment_id' => $transaction['payment_id'],
                'order_id' => $transaction['order_id'],
                'invoice_no' => $transaction['invoice_no'],
                'amount' => floatval($transaction['total_amount']),
                'payment_method' => $transaction['payment_method'],
                'cashier' => $transaction['cashier_name'] ?? 'Unknown',
                'status' => 'Success', // Always show Success since payment exists
                'time' => date('H:i', strtotime($transaction['payment_time'])),
                'date' => date('M d, Y', strtotime($transaction['payment_time']))
            ];
        }, $transactions);

        if ($output) {
            echo json_encode([
                'status' => 'success',
                'data' => $formattedTransactions
            ]);
        } else {
            return $formattedTransactions;
        }

    } catch (Exception $e) {
        error_log("Recent transactions error: " . $e->getMessage());
        if ($output) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch recent transactions: ' . $e->getMessage()
            ]);
        }
        return [];
    }
}

/**
 * Get cash registry summary
 */
function getCashRegistrySummary($pdo) {
    try {
        // Set Philippines timezone for accurate date calculations
        date_default_timezone_set('Asia/Manila');
        $today = date('Y-m-d');        // Today's cash transactions
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(p.payment_id) as cash_transactions,
                COALESCE(SUM(p.cash_received), 0) as total_cash_received,
                COALESCE(SUM(p.change_amount), 0) as total_change_given,
                COALESCE(SUM(o.total_amount), 0) as net_cash_sales
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.order_id
            WHERE DATE(p.payment_time) = ? 
            AND p.payment_method = 'Cash'
        ");
        $stmt->execute([$today]);
        $cashData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cash on hand calculation
        $cashOnHand = floatval($cashData['total_cash_received']) - floatval($cashData['total_change_given']);

        return [
            'date' => $today,
            'cash_transactions' => intval($cashData['cash_transactions']),
            'total_received' => floatval($cashData['total_cash_received']),
            'total_change' => floatval($cashData['total_change_given']),
            'cash_on_hand' => $cashOnHand,
            'net_sales' => floatval($cashData['net_cash_sales'])
        ];

    } catch (Exception $e) {
        error_log("Cash registry error: " . $e->getMessage());
        return [
            'date' => date('Y-m-d'),
            'cash_transactions' => 0,
            'total_received' => 0,
            'total_change' => 0,
            'cash_on_hand' => 0,
            'net_sales' => 0
        ];
    }
}

/**
 * Get inventory status summary
 */
function getInventoryStatus($pdo) {
    try {
        // Get critical inventory status
        $stmt = $pdo->query("
            SELECT 
                p.product_id,
                p.name,
                p.stock_quantity,
                p.min_stock_level,
                CASE 
                    WHEN p.stock_quantity = 0 THEN 'Out of Stock'
                    WHEN p.stock_quantity <= p.min_stock_level THEN 'Low Stock'
                    ELSE 'In Stock'
                END as status,
                CASE 
                    WHEN p.is_food = 1 AND p.expiry_date IS NOT NULL THEN 
                        DATEDIFF(p.expiry_date, CURDATE())
                    ELSE NULL
                END as days_to_expiry
            FROM products p
            WHERE p.stock_quantity <= p.min_stock_level OR 
                  (p.is_food = 1 AND p.expiry_date IS NOT NULL AND DATEDIFF(p.expiry_date, CURDATE()) <= 7)
            ORDER BY 
                CASE 
                    WHEN p.stock_quantity = 0 THEN 1
                    WHEN p.stock_quantity <= p.min_stock_level THEN 2
                    ELSE 3
                END,
                p.stock_quantity ASC
            LIMIT 10
        ");
        $criticalItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Overall inventory summary
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_products,
                SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN stock_quantity <= min_stock_level AND stock_quantity > 0 THEN 1 ELSE 0 END) as low_stock,
                SUM(stock_quantity * price) as total_inventory_value
            FROM products
        ");
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'summary' => [
                'total_products' => intval($summary['total_products']),
                'out_of_stock' => intval($summary['out_of_stock']),
                'low_stock' => intval($summary['low_stock']),
                'inventory_value' => floatval($summary['total_inventory_value'])
            ],
            'critical_items' => array_map(function($item) {
                return [
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'stock_quantity' => intval($item['stock_quantity']),
                    'min_stock_level' => intval($item['min_stock_level']),
                    'status' => $item['status'],
                    'days_to_expiry' => $item['days_to_expiry'] ? intval($item['days_to_expiry']) : null
                ];
            }, $criticalItems)
        ];

    } catch (Exception $e) {
        error_log("Inventory status error: " . $e->getMessage());
        return [
            'summary' => [
                'total_products' => 0,
                'out_of_stock' => 0,
                'low_stock' => 0,
                'inventory_value' => 0
            ],
            'critical_items' => []
        ];
    }
}

/**
 * Get system status information
 */
function getSystemStatus($pdo, $output = true) {
    try {
        // Database health check
        $dbHealth = 'healthy';
        try {
            $pdo->query("SELECT 1")->fetch();
        } catch (Exception $e) {
            $dbHealth = 'error';
        }

        // Recent activity check
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as recent_orders,
                MAX(order_date) as last_order
            FROM orders 
            WHERE order_date >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);

        // Active users (logged in within last 24 hours)
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT user_id) as active_users
            FROM refresh_tokens 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND revoked = 0
        ");
        $activeUsers = $stmt->fetch(PDO::FETCH_ASSOC);

        $status = [
            'database' => $dbHealth,
            'last_activity' => $activity['last_order'] ? date('H:i', strtotime($activity['last_order'])) : 'No recent activity',
            'recent_orders' => intval($activity['recent_orders']),
            'active_users' => intval($activeUsers['active_users']),
            'server_time' => date('Y-m-d H:i:s'),
            'uptime_status' => 'online'
        ];

        if ($output) {
            echo json_encode([
                'status' => 'success',
                'data' => $status
            ]);
        } else {
            return $status;
        }

    } catch (Exception $e) {
        error_log("System status error: " . $e->getMessage());
        $errorStatus = [
            'database' => 'error',
            'last_activity' => 'Unknown',
            'recent_orders' => 0,
            'active_users' => 0,
            'server_time' => date('Y-m-d H:i:s'),
            'uptime_status' => 'error'
        ];

        if ($output) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'data' => $errorStatus
            ]);
        } else {
            return $errorStatus;
        }
    }
}

/**
 * Get payment method breakdown
 */
function getPaymentMethodBreakdown($pdo, $date) {
    try {        $stmt = $pdo->prepare("
            SELECT 
                p.payment_method,
                COUNT(*) as transaction_count,
                SUM(o.total_amount) as total_amount
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.order_id
            WHERE DATE(p.payment_time) = ? AND o.payment_status = 'Paid'
            GROUP BY p.payment_method
        ");
        $stmt->execute([$date]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $breakdown = [];
        foreach ($results as $result) {
            $breakdown[$result['payment_method']] = [
                'count' => intval($result['transaction_count']),
                'amount' => floatval($result['total_amount'])
            ];
        }

        return $breakdown;

    } catch (Exception $e) {
        error_log("Payment breakdown error: " . $e->getMessage());
        return [];
    }
}
?>
