<?php
// Test password hashes for debugging
$users = [
    'Draine' => '21a450ca63e673188f62d47608211457ed9f61dc8184b39c38d8fdf4b9cbaa71',
    'admin' => 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f'
];

$common_passwords = ['password', 'admin', 'secret', 'password123', '123456', 'admin123'];

echo "Testing common passwords for admin users:\n\n";

foreach ($users as $username => $stored_hash) {
    echo "User: $username\n";
    echo "Stored hash: $stored_hash\n";
    
    foreach ($common_passwords as $password) {
        $test_hash = hash('sha256', $password);
        if ($test_hash === $stored_hash) {
            echo "✓ MATCH FOUND: Password is '$password'\n";
            break;
        }
    }
    echo "\n";
}

// Also test specific hash values
echo "Testing specific password 'secret' for admin:\n";
echo "Hash of 'secret': " . hash('sha256', 'secret') . "\n";
echo "Stored hash: ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f\n";
echo "Match: " . (hash('sha256', 'secret') === 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f' ? 'YES' : 'NO') . "\n";
?>
