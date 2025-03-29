<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Verification</h1>";

try {
    require_once '../config/database.php';
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Check current database
    $result = $conn->query("SELECT DATABASE() as current_db");
    $row = $result->fetch_assoc();
    $currentDb = $row['current_db'];
    echo "<p>Current database: $currentDb</p>";
    
    // Switch to campascompass if needed
    if ($currentDb !== 'campascompass') {
        if ($conn->select_db('campascompass')) {
            echo "<p>✅ Successfully switched to campascompass database</p>";
        } else {
            throw new Exception("Failed to switch to campascompass database");
        }
    }
    
    // Check collegereviews table
    $result = $conn->query("SHOW TABLES LIKE 'collegereviews'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ collegereviews table exists</p>";
        
        // Get row count
        $result = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
        $row = $result->fetch_assoc();
        echo "<p>Number of reviews: " . $row['count'] . "</p>";
    } else {
        echo "<p>❌ collegereviews table not found</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?> 