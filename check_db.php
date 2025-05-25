<?php
// Check database tables and users
require_once __DIR__ . '/src/config/db.php';

echo "<h2>Database Structure Check</h2>";

try {
    // Check if users table exists and has data
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Available Tables:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check users table
    if (in_array('users', $tables)) {
        echo "<h3>Users Table:</h3>";
        $stmt = $pdo->query("SELECT user_id, username, email, role FROM users LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($users) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['user_id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No users found in the database.</p>";
        }
    }
    
    // Check categories table
    if (in_array('categories', $tables)) {
        echo "<h3>Categories Table:</h3>";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Categories count: " . $count['count'] . "</p>";
    }
    
    // Check orders table
    if (in_array('orders', $tables)) {
        echo "<h3>Orders Table:</h3>";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Orders count: " . $count['count'] . "</p>";
    }

} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
?>
