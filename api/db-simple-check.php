<?php
header('Content-Type: text/plain');
error_reporting(0); // Disable error reporting for security

try {
    // Database connection
    require_once '../config/database.php';
    
    // Check connection only - no table info
    if (!isset($conn) || $conn->connect_error) {
        echo "ERROR: Database connection failed\n";
        exit;
    }
    
    echo "SUCCESS: Database connection working\n";
    
    // Check if users table exists without showing details
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        echo "SUCCESS: Users table exists\n";
    } else {
        echo "WARNING: Users table does not exist\n";
        echo "Please create the users table using the admin interface\n";
    }
} catch (Exception $e) {
    echo "ERROR: An exception occurred\n";
}
?> 