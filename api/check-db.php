<?php
header('Content-Type: text/plain');
echo "Database Connection Test\n\n";

try {
    // Load database connection
    require_once '../config/database.php';
    
    // Check the connection
    if (!isset($conn)) {
        echo "ERROR: Database connection not established.\n";
        exit;
    }
    
    if ($conn->connect_error) {
        echo "Connection failed: " . $conn->connect_error . "\n";
        exit;
    }
    
    echo "Database connection successful!\n";
    
    // Try a simple query
    $result = $conn->query("SELECT DATABASE()");
    $row = $result->fetch_row();
    echo "Connected to database: " . $row[0] . "\n";
    
    // Check if the colleges table exists
    $result = $conn->query("SHOW TABLES LIKE 'colleges'");
    if ($result->num_rows > 0) {
        echo "\nThe colleges table exists.\n";
        
        // Count records
        $result = $conn->query("SELECT COUNT(*) as count FROM colleges");
        $row = $result->fetch_assoc();
        echo "Number of colleges: " . $row['count'] . "\n";
    } else {
        echo "\nThe colleges table does not exist!\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?> 