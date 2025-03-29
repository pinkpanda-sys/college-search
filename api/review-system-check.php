<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
require_once '../config/database.php';

echo "<h1>Review System Check</h1>";

try {
    // Check database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Check if logs directory exists and is writable
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
        echo "<p>✅ Created logs directory</p>";
    } elseif (!is_writable($logDir)) {
        echo "<p>⚠️ Logs directory exists but is not writable</p>";
    } else {
        echo "<p>✅ Logs directory exists and is writable</p>";
    }
    
    // Check if reviews table exists
    $result = $conn->query("SHOW TABLES LIKE 'reviews'");
    $tableExists = ($result && $result->num_rows > 0);
    
    if ($tableExists) {
        echo "<p>✅ Reviews table exists</p>";
        
        // Check review count
        $result = $conn->query("SELECT COUNT(*) as count FROM reviews");
        if ($result && $row = $result->fetch_assoc()) {
            echo "<p>There are currently " . $row['count'] . " reviews in the database</p>";
        }
    } else {
        echo "<p>❌ Reviews table does not exist</p>";
        echo "<p>Would you like to create it now? <a href='setup-reviews.php'>Create Reviews Table</a></p>";
    }
    
    // Check if college table exists
    $result = $conn->query("SHOW TABLES LIKE 'colleges'");
    $collegeTableExists = ($result && $result->num_rows > 0);
    
    if ($collegeTableExists) {
        echo "<p>✅ Colleges table exists</p>";
        
        // Check college count
        $result = $conn->query("SELECT COUNT(*) as count FROM colleges");
        if ($result && $row = $result->fetch_assoc()) {
            echo "<p>There are currently " . $row['count'] . " colleges in the database</p>";
        }
    } else {
        echo "<p>❌ Colleges table does not exist - reviews will not work without colleges</p>";
    }
    
    // Test the API endpoint
    echo "<h2>API Test</h2>";
    echo "<p>Testing GET request to reviews.php...</p>";
    
    $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reviews.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "<p>✅ API is responding (HTTP $httpCode)</p>";
        
        // Try to decode the JSON
        $data = json_decode($response, true);
        if ($data !== null) {
            echo "<p>✅ API returns valid JSON</p>";
            echo "<p>API returned " . count($data) . " reviews</p>";
        } else {
            echo "<p>❌ API response is not valid JSON</p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
        }
    } else {
        echo "<p>❌ API request failed with HTTP code $httpCode</p>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
    }
    
    echo "<h2>Links</h2>";
    echo "<ul>";
    echo "<li><a href='setup-reviews.php'>Run Setup/Reset Reviews Table</a></li>";
    echo "<li><a href='../admin/review-management.html'>Go to Review Management</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?> 