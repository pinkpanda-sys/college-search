<?php
header('Content-Type: text/plain');

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/../logs';
if (!file_exists($logDir)) {
    if (mkdir($logDir, 0777, true)) {
        echo "Created logs directory.\n";
        chmod($logDir, 0777);
    } else {
        echo "Failed to create logs directory!\n";
    }
} else {
    echo "Logs directory exists.\n";
    chmod($logDir, 0777);
}

// Create a test error log file
$errorLog = $logDir . '/test-error.log';
if (file_put_contents($errorLog, date('[Y-m-d H:i:s]') . " Test error log entry\n", FILE_APPEND)) {
    echo "Successfully wrote to error log.\n";
} else {
    echo "Failed to write to error log!\n";
}

// Check permissions
echo "\nPermissions:\n";
echo "- Logs directory: " . substr(sprintf('%o', fileperms($logDir)), -4) . "\n";
if (file_exists($errorLog)) {
    echo "- Error log file: " . substr(sprintf('%o', fileperms($errorLog)), -4) . "\n";
}

echo "\nPaths:\n";
echo "- Current script: " . __FILE__ . "\n";
echo "- Logs directory: " . $logDir . "\n";
echo "- Error log: " . $errorLog . "\n";

echo "\nDone.";
?> 