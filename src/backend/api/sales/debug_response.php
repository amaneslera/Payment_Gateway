<?php
// Debug script to check what's being output by the sales API

// Enable error reporting to see any warnings/notices
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to capture everything
ob_start();

// Include the sales API
include 'sales_api.php';

// Get the captured output
$output = ob_get_contents();
ob_end_clean();

// Show the raw output with visible whitespace
echo "Raw output length: " . strlen($output) . "\n";
echo "Raw output (with special chars visible):\n";
echo json_encode($output, JSON_UNESCAPED_SLASHES) . "\n";

// Try to decode the JSON to see where it fails
$lines = explode("\n", $output);
foreach ($lines as $i => $line) {
    if (!empty(trim($line))) {
        echo "Line $i: " . json_encode($line) . "\n";
        $decoded = json_decode($line);
        if ($decoded !== null) {
            echo "  -> Valid JSON\n";
        } else {
            echo "  -> JSON Error: " . json_last_error_msg() . "\n";
        }
    }
}
?>
