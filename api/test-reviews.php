<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
require_once '../config/database.php';

echo "<h1>Review API Test</h1>";

try {
    // Check if the reviews table exists
    $result = $conn->query("SHOW TABLES LIKE 'reviews'");
    if (!$result || $result->num_rows === 0) {
        echo "<p>Reviews table does not exist. <a href='setup-reviews.php'>Create it now</a>.</p>";
        exit;
    }
    
    // Count reviews
    $result = $conn->query("SELECT COUNT(*) as count FROM reviews");
    if ($result && $row = $result->fetch_assoc()) {
        echo "<p>Found " . $row['count'] . " reviews in the database.</p>";
        
        if ($row['count'] === 0) {
            echo "<p>You need to add some reviews to test the functionality. <a href='setup-reviews.php'>Add sample reviews</a>.</p>";
            exit;
        }
    }
    
    // Test the API directly
    echo "<h2>Testing GET Request</h2>";
    
    // Use cURL to make a request to the API
    $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reviews.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p>API Response Code: " . $httpCode . "</p>";
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "<p>API request successful!</p>";
        
        // Try to decode JSON
        $data = json_decode($response, true);
        if ($data === null) {
            echo "<p>Warning: Could not parse JSON response:</p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
        } else {
            echo "<p>Successfully parsed JSON response with " . count($data) . " reviews.</p>";
            
            // Display sample review
            if (!empty($data)) {
                echo "<h3>Sample Review Data:</h3>";
                echo "<pre>";
                print_r($data[0]);
                echo "</pre>";
            }
        }
    } else {
        echo "<p>API request failed with code " . $httpCode . "</p>";
        echo "<p>Response:</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
    
    echo "<p><a href='../admin/review-management.html'>Go to Review Management</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 