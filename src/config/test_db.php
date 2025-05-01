<?php
// Simple database connection test

// Load configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// This page will only display results, not JSON
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Database Connection Test</h1>
    <?php
    try {
        // We already have $pdo from db.php, so let's test a simple query
        $stmt = $pdo->query("SELECT 1 AS connection_test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['connection_test'] == 1) {
            echo "<div class='success'>";
            echo "<h2>✓ Connection Successful!</h2>";
            echo "<p>Successfully connected to the <strong>" . DB_NAME . "</strong> database on <strong>" . DB_HOST . "</strong>.</p>";
            
            // Display tables in the database
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (count($tables) > 0) {
                echo "<h3>Tables found in the database:</h3>";
                echo "<ul>";
                foreach ($tables as $table) {
                    echo "<li>" . htmlspecialchars($table) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No tables found in the database.</p>";
            }
            echo "</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='error'>";
        echo "<h2>✗ Connection Failed</h2>";
        echo "<p>Failed to connect to database: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<h3>Check the following:</h3>";
        echo "<ul>";
        echo "<li>Is MySQL running?</li>";
        echo "<li>Do the database credentials in your .env file match your MySQL setup?</li>";
        echo "<li>Does the database '" . DB_NAME . "' exist?</li>";
        echo "</ul>";
        echo "</div>";
    }
    ?>

    <h3>Connection Settings Used:</h3>
    <ul>
        <li>Host: <?php echo htmlspecialchars(DB_HOST); ?></li>
        <li>Database: <?php echo htmlspecialchars(DB_NAME); ?></li>
        <li>Username: <?php echo htmlspecialchars(DB_USER); ?></li>
        <li>Password: [Hidden]</li>
    </ul>

    <p><strong>Important:</strong> Make sure to first fix the issue in your .env file by removing the PHP comment at the top!</p>
</body>
</html>