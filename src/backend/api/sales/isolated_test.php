<?php
// Simple test to see what's causing extra output
header('Content-Type: application/json');

// Test 1: Direct JSON output
echo json_encode(['test' => 'simple', 'status' => 'working']);

// Don't include any other files to see if the issue is isolated
