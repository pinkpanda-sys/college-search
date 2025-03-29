<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Review System Diagnostics</h1>";

try {
    // Check PHP version
    echo "<h2>System Information</h2>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
    
    // Check logs directory
    $logDir = __DIR__ . '/../logs';
    echo "<h2>Logs Directory</h2>";
    if (is_dir($logDir)) {
        echo "<p>✅ Logs directory exists</p>";
        if (is_writable($logDir)) {
            echo "<p>✅ Logs directory is writable</p>";
        } else {
            echo "<p>❌ Logs directory is not writable. Fix permissions.</p>";
        }
    } else {
        echo "<p>❌ Logs directory does not exist. Creating...</p>";
        if (mkdir($logDir, 0755, true)) {
            echo "<p>✅ Logs directory created successfully</p>";
        } else {
            echo "<p>❌ Failed to create logs directory</p>";
        }
    }
    
    // Check for error logs
    echo "<h2>Error Logs</h2>";
    $logFiles = glob($logDir . '/*.log');
    if (empty($logFiles)) {
        echo "<p>No log files found</p>";
    } else {
        echo "<p>Found " . count($logFiles) . " log files:</p>";
        echo "<ul>";
        foreach ($logFiles as $file) {
            $fileName = basename($file);
            echo "<li>$fileName - " . filesize($file) . " bytes";
            
            // Show last few lines of the file
            if (filesize($file) > 0) {
                $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $lastLines = array_slice($lines, -10); // Show last 10 lines
                echo "<pre>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";
            }
            
            echo "</li>";
        }
        echo "</ul>";
    }
    
    // Database connection
    echo "<h2>Database Connection</h2>";
    require_once '../config/database.php';
    
    if (!isset($conn)) {
        echo "<p>❌ Database connection variable not set!</p>";
        exit;
    }
    
    if ($conn->connect_error) {
        echo "<p>❌ Connection failed: " . $conn->connect_error . "</p>";
        exit;
    }
    
    echo "<p>✅ Connected to database successfully!</p>";
    echo "<p>Database: " . (defined('DB_NAME') ? DB_NAME : 'Unknown') . "</p>";
    
    // Check for reviews and collegereviews tables
    echo "<h2>Database Tables</h2>";
    
    $tables = ['reviews', 'collegereviews', 'colleges', 'users'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p>✅ Table '$table' exists</p>";
            
            // Count records
            $countResult = $conn->query("SELECT COUNT(*) as count FROM $table");
            if ($countResult && $row = $countResult->fetch_assoc()) {
                echo "<p>&nbsp;&nbsp;&nbsp;Contains " . $row['count'] . " records</p>";
            }
            
            // Show table structure
            $structureResult = $conn->query("DESCRIBE $table");
            if ($structureResult) {
                echo "<details><summary>Table structure</summary><table border='1'>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                while ($row = $structureResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['Field'] . "</td>";
                    echo "<td>" . $row['Type'] . "</td>";
                    echo "<td>" . $row['Null'] . "</td>";
                    echo "<td>" . $row['Key'] . "</td>";
                    echo "<td>" . $row['Default'] . "</td>";
                    echo "</tr>";
                }
                echo "</table></details>";
            }
        } else {
            echo "<p>❌ Table '$table' does not exist</p>";
            
            // For reviews and collegereviews, offer setup links
            if ($table == 'reviews') {
                echo "<p>&nbsp;&nbsp;&nbsp;<a href='setup-reviews.php'>Setup Reviews Table</a></p>";
            } else if ($table == 'collegereviews') {
                echo "<p>&nbsp;&nbsp;&nbsp;<a href='setup-collegereviews.php'>Setup College Reviews Table</a></p>";
            }
        }
    }
    
    // Test API
    echo "<h2>API Test</h2>";
    $apiUrl = "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . 
              "://" . $_SERVER['HTTP_HOST'] . 
              dirname($_SERVER['PHP_SELF']) . "/reviews.php";
    
    echo "<p>Testing API URL: <a href='$apiUrl' target='_blank'>$apiUrl</a></p>";
    
    // Make a test request to the API
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);
    
    echo "<p>API Response Code: " . $httpCode . "</p>";
    echo "<details><summary>Response Headers</summary><pre>" . htmlspecialchars($header) . "</pre></details>";
    echo "<details><summary>Response Body</summary><pre>" . htmlspecialchars($body) . "</pre></details>";
    
    // Check for specific syntax errors in the API file
    echo "<h2>PHP Lint Check</h2>";
    $apiFile = __DIR__ . '/reviews.php';
    $output = [];
    $returnCode = 0;
    exec("php -l " . escapeshellarg($apiFile) . " 2>&1", $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "<p>✅ No syntax errors detected in reviews.php</p>";
    } else {
        echo "<p>❌ Syntax errors found in reviews.php:</p>";
        echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
    }
    
    echo "<h2>Manual API Checks</h2>";
    echo "<p>You can test specific API endpoints:</p>";
    
    echo "<ul>";
    echo "<li><a href='$apiUrl' target='_blank'>Get all reviews</a></li>";
    echo "<li><a href='{$apiUrl}?status=pending' target='_blank'>Get pending reviews</a></li>";
    echo "<li><a href='{$apiUrl}?status=approved' target='_blank'>Get approved reviews</a></li>";
    echo "<li><a href='{$apiUrl}?source=reviews' target='_blank'>Get reviews from reviews table</a></li>";
    echo "<li><a href='{$apiUrl}?source=collegereviews' target='_blank'>Get reviews from collegereviews table</a></li>";
    echo "</ul>";
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li>Check the error logs above for specific error messages</li>";
    echo "<li>Ensure all required tables exist (run setup scripts if needed)</li>";
    echo "<li>Test the API with different parameters to isolate the issue</li>";
    echo "<li>Visit <a href='../admin/review-management.html'>Review Management Page</a> after fixing issues</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2>Diagnostics Error</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?> 