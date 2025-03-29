<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Review System Check</h1>";

try {
    // Database connection
    require_once '../config/database.php';
    
    // Check connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Check tables
    $tables = ['reviews', 'collegereviews', 'colleges', 'users'];
    $existingTables = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            $existingTables[] = $table;
            echo "<p>✅ Table '$table' exists</p>";
            
            // Count records
            $countResult = $conn->query("SELECT COUNT(*) as count FROM $table");
            if ($countResult && $row = $countResult->fetch_assoc()) {
                echo "<p>&nbsp;&nbsp;&nbsp;Contains " . $row['count'] . " records</p>";
            }
        } else {
            echo "<p>❌ Table '$table' does not exist</p>";
            
            // Provide setup links
            if ($table === 'reviews') {
                echo "<p>&nbsp;&nbsp;&nbsp;<a href='setup-reviews.php'>Setup Reviews Table</a></p>";
            } else if ($table === 'collegereviews') {
                echo "<p>&nbsp;&nbsp;&nbsp;<a href='setup-collegereviews.php'>Setup College Reviews Table</a></p>";
            } else if ($table === 'colleges') {
                echo "<p>&nbsp;&nbsp;&nbsp;<a href='setup-table.php'>Setup Colleges Table</a></p>";
            } else if ($table === 'users') {
                echo "<p>&nbsp;&nbsp;&nbsp;<a href='setup-users.php'>Setup Users Table</a></p>";
            }
        }
    }
    
    // Test API if tables exist
    if (in_array('reviews', $existingTables) || in_array('collegereviews', $existingTables)) {
        echo "<h2>API Test</h2>";
        
        // Make a test request to the API
        $apiUrl = "http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "s" : "") . 
                  "://" . $_SERVER['HTTP_HOST'] . 
                  dirname($_SERVER['PHP_SELF']) . "/reviews.php";
        
        echo "<p>Testing API URL: " . $apiUrl . "</p>";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p>API Response Code: " . $httpCode . "</p>";
        
        if ($httpCode === 200) {
            echo "<p>✅ API is functioning correctly</p>";
            
            // Check response data
            $data = json_decode($response, true);
            if (is_array($data)) {
                echo "<p>Found " . count($data) . " reviews in the response</p>";
            } else {
                echo "<p>⚠️ API response does not contain review data</p>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 300)) . (strlen($response) > 300 ? '...' : '') . "</pre>";
            }
        } else {
            echo "<p>❌ API returned error: " . $httpCode . "</p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 300)) . (strlen($response) > 300 ? '...' : '') . "</pre>";
        }
    }
    
    echo "<h2>Navigation</h2>";
    echo "<ul>";
    echo "<li><a href='../admin/review-management.html'>Go to Review Management</a></li>";
    if (in_array('collegereviews', $existingTables)) {
        echo "<li><a href='setup-collegereviews.php'>Manage College Reviews Table</a></li>";
    } else {
        echo "<li><a href='setup-collegereviews.php'>Create College Reviews Table</a></li>";
    }
    if (in_array('reviews', $existingTables)) {
        echo "<li><a href='setup-reviews.php'>Manage Reviews Table</a></li>";
    } else {
        echo "<li><a href='setup-reviews.php'>Create Reviews Table</a></li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 