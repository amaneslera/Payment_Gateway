<?php
// Test script to verify invoice number search functionality
// Place this file in the root of your project and access it via browser

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once __DIR__ . '/config/db.php';

// Get test search term from URL parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// If no search term provided, use a default one for testing
if (empty($search)) {
    // Get the first payment ID from the database to create a valid test
    $conn = getConnection();
    $result = $conn->query("SELECT payment_id, payment_time FROM payments ORDER BY payment_id LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        $paymentId = $row['payment_id'];
        $paymentTime = $row['payment_time'];
        $formattedDateTime = new DateTime($paymentTime);
        $invoiceNo = date('Ymd', $formattedDateTime->getTimestamp()) . '-' . str_pad($paymentId, 3, '0', STR_PAD_LEFT);
        $search = $invoiceNo;
    } else {
        $search = 'no-valid-payments-found';
    }
}

echo "<h1>Transaction Search Test</h1>";
echo "<p>Testing search with term: <strong>{$search}</strong></p>";

// Connect to database
$conn = getConnection();

// Get search parameters
$page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query conditions
$conditions = [];
$params = [];
$types = '';

// Invoice number search test
if (preg_match('/^(\d{8})-(\d{3})$/i', $search, $matches)) {
    $datePrefix = $matches[1];
    $paymentId = ltrim($matches[2], '0'); // Remove leading zeros
    
    // Get date from prefix
    $year = substr($datePrefix, 0, 4);
    $month = substr($datePrefix, 4, 2);
    $day = substr($datePrefix, 6, 2);
    
    echo "<p>Detected invoice number format: Date part = {$year}-{$month}-{$day}, Payment ID part = {$paymentId}</p>";
    
    // Find transactions on that date with that payment ID
    $conditions[] = "(DATE(p.payment_time) = ? AND p.payment_id = ?)";
    $params[] = "$year-$month-$day";
    $params[] = $paymentId;
    $types .= 'si';
} else if (preg_match('/^CASH-(\d+)$/i', $search, $matches)) {
    $cashPaymentId = $matches[1];
    echo "<p>Detected CASH transaction ID format: Payment ID = {$cashPaymentId}</p>";
    $conditions[] = "(p.payment_id = ? AND p.payment_method = 'Cash')";
    $params[] = $cashPaymentId;
    $types .= 'i';
} else {
    echo "<p>Using general search format</p>";
    $conditions[] = "(p.payment_id LIKE ? OR p.order_id LIKE ? OR u.username LIKE ? OR p.payment_method LIKE ? OR p.paypal_transaction_id LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sssss';
}

$whereClause = '';
if (!empty($conditions)) {
    $whereClause = "WHERE " . implode(' AND ', $conditions);
}

// Get paginated results
$query = "
    SELECT 
        p.payment_id,
        p.order_id,
        p.payment_method,
        p.payment_time,
        p.transaction_status,
        p.paypal_transaction_id,
        u.username as cashier_name,
        o.total_amount
    FROM 
        payments p
    LEFT JOIN 
        user u ON p.cashier_id = u.user_id
    LEFT JOIN 
        orders o ON p.order_id = o.order_id
    $whereClause
    ORDER BY p.payment_time DESC
    LIMIT ? OFFSET ?
";

// Add limit and offset to params
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

echo "<p>SQL Query: " . htmlspecialchars($query) . "</p>";
echo "<p>Where Clause: " . htmlspecialchars($whereClause) . "</p>";
echo "<p>Parameters: " . htmlspecialchars(json_encode($params)) . "</p>";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Results</h2>";
echo "<p>Found " . $result->num_rows . " transactions</p>";

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>
            <tr>
                <th>Payment ID</th>
                <th>Order ID</th>
                <th>Payment Method</th>
                <th>Payment Time</th>
                <th>Invoice No.</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        $formattedDateTime = new DateTime($row['payment_time']);
        $invoiceNo = date('Ymd', $formattedDateTime->getTimestamp()) . '-' . str_pad($row['payment_id'], 3, '0', STR_PAD_LEFT);
        
        echo "<tr>
                <td>" . $row['payment_id'] . "</td>
                <td>" . $row['order_id'] . "</td>
                <td>" . $row['payment_method'] . "</td>
                <td>" . $row['payment_time'] . "</td>
                <td>" . $invoiceNo . "</td>
              </tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No transactions found</p>";
}
