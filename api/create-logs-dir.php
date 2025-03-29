<?php
header('Content-Type: text/plain');

// Path to the logs directory
$logDir = __DIR__ . '/../logs';

// Create the directory if it doesn't exist
if (!file_exists($logDir)) {
    if (mkdir($logDir, 0777, true)) {
        echo "Logs directory created successfully.\n";
    } else {
        echo "Failed to create logs directory!\n";
    }
} else {
    echo "Logs directory already exists.\n";
}

// Ensure proper permissions
chmod($logDir, 0777);
echo "Permissions updated to 0777.\n";

// Test writing to a log file
$testFile = $logDir . '/test.log';
if (file_put_contents($testFile, date('[Y-m-d H:i:s]') . " Test log entry\n")) {
    echo "Successfully wrote to test log file.\n";
} else {
    echo "Failed to write to test log file!\n";
}

echo "\nLog directory setup complete: $logDir";
?> 