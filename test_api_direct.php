<?php
header('Content-Type: application/json');

echo "Testing API access...\n";

// Test database connection
try {
    require_once 'src/config/db.php';
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit;
}

// Test if we can access the sales API file
$salesApiPath = 'src/backend/api/sales/sales_api.php';
if (file_exists($salesApiPath)) {
    echo "✓ Sales API file exists\n";
} else {
    echo "✗ Sales API file not found\n";
    exit;
}

// Test basic API call to categories (no auth required for testing)
echo "\nTesting categories API...\n";

// Simulate a simple GET request to categories
$_GET['action'] = 'categories';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Temporarily bypass auth for testing
$originalAuthFile = 'src/backend/middleware/auth_middleware.php';
$tempContent = file_get_contents($originalAuthFile);

// Create a temporary auth bypass
file_put_contents('temp_auth.php', '<?php
class AuthMiddleware {
    public static function validateToken() {
        return (object)["role" => "Admin", "user_id" => 1];
    }
}
?>');

// Replace the auth include temporarily
$salesContent = file_get_contents($salesApiPath);
$modifiedContent = str_replace(
    "require_once __DIR__ . '/../../middleware/auth_middleware.php';",
    "require_once 'temp_auth.php';",
    $salesContent
);

file_put_contents('temp_sales_api.php', $modifiedContent);

// Execute the modified API
ob_start();
include 'temp_sales_api.php';
$output = ob_get_clean();

echo "API Output:\n";
echo $output;

// Cleanup
unlink('temp_auth.php');
unlink('temp_sales_api.php');
?>
