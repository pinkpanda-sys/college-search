<?php
header('Content-Type: text/plain');

// Display PHP configuration
echo "PHP Version: " . phpversion() . "\n\n";

// Test database connection
echo "Testing Database Connection:\n";
require_once '../config/database.php';

if (isset($conn) && !$conn->connect_error) {
    echo "✓ Connected to database successfully\n";
    echo "  Host info: " . $conn->host_info . "\n";
    echo "  Server version: " . $conn->server_info . "\n\n";
    
    // Check if database exists
    echo "Database '" . DB_NAME . "' exists\n\n";
    
    // Test colleges table
    echo "Testing Colleges Table:\n";
    $result = $conn->query("SHOW TABLES LIKE 'colleges'");
    if ($result->num_rows > 0) {
        echo "✓ Colleges table exists\n";
        
        // Check table structure
        echo "\nTable Structure:\n";
        $result = $conn->query("DESCRIBE colleges");
        while ($row = $result->fetch_assoc()) {
            echo "  " . $row['Field'] . " - " . $row['Type'] . " - " . ($row['Null'] === "YES" ? "NULL" : "NOT NULL") . 
                 ($row['Key'] === "PRI" ? " (PRIMARY KEY)" : "") . "\n";
        }
        
        // Count colleges
        echo "\nCounting records:\n";
        $result = $conn->query("SELECT COUNT(*) as count FROM colleges");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "✓ Found {$row['count']} colleges in database\n";
            
            // Show sample data
            if ($row['count'] > 0) {
                $result = $conn->query("SELECT * FROM colleges LIMIT 1");
                if ($result && $row = $result->fetch_assoc()) {
                    echo "\nSample college data:\n";
                    foreach ($row as $key => $value) {
                        echo "  $key: " . ($value === null ? "NULL" : $value) . "\n";
                    }
                }
            }
        } else {
            echo "✗ Error counting colleges: " . $conn->error . "\n";
        }
    } else {
        echo "✗ Colleges table does not exist\n";
        echo "  Will be created when the first college is added\n";
    }
} else {
    echo "✗ Database connection failed\n";
    if (isset($conn)) {
        echo "Error: " . $conn->connect_error . "\n";
    }
}

echo "\nDone.";
?> 