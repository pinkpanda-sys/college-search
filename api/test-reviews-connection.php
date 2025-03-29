<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Reviews API Connection Test</h1>";

try {
    // Database connection
    require_once '../config/database.php';
    
    if (!isset($conn)) {
        throw new Exception("Database connection variable not set");
    }
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Check for reviews and collegereviews tables
    $reviewsTableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'reviews'");
    $reviewsTableExists = ($result && $result->num_rows > 0);
    
    $collegeReviewsTableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'collegereviews'");
    $collegeReviewsTableExists = ($result && $result->num_rows > 0);
    
    echo "<p>Reviews table exists: " . ($reviewsTableExists ? "Yes" : "No") . "</p>";
    echo "<p>College Reviews table exists: " . ($collegeReviewsTableExists ? "Yes" : "No") . "</p>";
    
    // Try a simple query
    if ($reviewsTableExists) {
        $result = $conn->query("SELECT COUNT(*) as count FROM reviews");
        if ($result && $row = $result->fetch_assoc()) {
            echo "<p>Reviews count: " . $row['count'] . "</p>";
        }
    }
    
    if ($collegeReviewsTableExists) {
        $result = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
        if ($result && $row = $result->fetch_assoc()) {
            echo "<p>College Reviews count: " . $row['count'] . "</p>";
        }
    }
    
    echo "<p>Test completed successfully</p>";
    
} catch (Exception $e) {
    echo "<h2>Test Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 