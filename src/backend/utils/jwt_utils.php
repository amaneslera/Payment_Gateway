<?php
/**
 * JSON Web Token (JWT) utility functions
 */

/**
 * Generate a JWT token
 * 
 * @param array $payload The data to include in the token
 * @param string $secret The secret key used for signature
 * @param int $expiration Token expiration time in seconds
 * @return string The JWT token
 */
function generateJWT($payload, $secret, $expiration = 3600) {
    // Create header
    $header = json_encode([
        'typ' => 'JWT',
        'alg' => 'HS256'
    ]);
    
    // Encode header
    $header_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    
    // Add standard claims to payload
    $payload['iat'] = time(); // Issued at
    $payload['exp'] = time() + $expiration; // Expiration time
    
    // Encode payload
    $payload_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
    
    // Generate signature
    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret, true);
    $signature_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    // Create JWT token
    return "$header_encoded.$payload_encoded.$signature_encoded";
}

/**
 * Validate a JWT token
 * 
 * @param string $token The JWT token to validate
 * @param string $secret The secret key used for signature
 * @return array|bool The decoded payload if valid, false otherwise
 */
function validateJWT($token, $secret) {
    // Split the token
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    
    list($header_encoded, $payload_encoded, $signature_encoded) = $parts;
    
    // Verify signature
    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret, true);
    $signature_check = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    if ($signature_encoded !== $signature_check) {
        return false;
    }
    
    // Decode payload
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload_encoded)), true);
    
    // Check if token is expired
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}