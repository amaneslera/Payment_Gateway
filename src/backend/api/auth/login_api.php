<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include database connection and JWT handling
require_once __DIR__ . '/../../../config/db.php'; // Fixed path to db.php instead of database.php
require_once '../../vendor/autoload.php'; // Assuming you're using composer for JWT
use \Firebase\JWT\JWT;

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Check if username and password are provided
if (!isset($data->username) || !isset($data->password)) {
    echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
    exit();
}

try {
    // Use the global $pdo from db.php instead of creating a new Database object
    
    // Prepare query to check user
    $query = "SELECT user_id, username, password, role, email FROM users WHERE username = ?";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $data->username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($data->password, $row['password'])) {
            // Password is correct, create JWT token
            $secret_key = "your_secret_key"; // Should be stored securely in config
            $issued_at = time();
            $expiration_time = $issued_at + (60 * 60); // Valid for 1 hour
            
            $payload = [
                'iat' => $issued_at,
                'exp' => $expiration_time,
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'role' => $row['role']
            ];
            
            // Generate token
            $jwt = JWT::encode($payload, $secret_key, 'HS256');
            
            // Update last login time
            $update_query = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(1, $row['user_id']);
            $update_stmt->execute();
            
            // Return success with token and user role
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'token' => $jwt,
                'username' => $row['username'],
                'role' => $row['role']
            ]);
        } else {
            // Password incorrect
            echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        }
    } else {
        // User not found
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
