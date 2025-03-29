<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

try {
    // Database connection
    require_once '../config/database.php';
    
    if (isset($GLOBALS['db_connection_error'])) {
        throw new Exception($GLOBALS['db_connection_error']);
    }
    
    if (!isset($conn)) {
        throw new Exception("Database connection variable not set");
    }
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color:green'>✅ Database connection successful</p>";
    
    // Get database name
    $result = $conn->query("SELECT DATABASE() as current_db");
    if ($result && $row = $result->fetch_assoc()) {
        $currentDb = $row['current_db'];
        echo "<p>Current database: " . $currentDb . "</p>";
        
        // Check if this is the correct database
        if ($currentDb != 'campuscompass') {
            echo "<p style='color:orange'>⚠️ Currently using database '$currentDb', not 'campuscompass'</p>";
            
            // Check if campuscompass exists
            $result = $conn->query("SHOW DATABASES LIKE 'campuscompass'");
            if ($result && $result->num_rows > 0) {
                echo "<p>Database 'campuscompass' exists but is not selected</p>";
                
                if ($conn->select_db('campuscompass')) {
                    echo "<p style='color:green'>✅ Successfully switched to 'campuscompass' database</p>";
                    $currentDb = 'campuscompass';
                } else {
                    echo "<p style='color:red'>❌ Failed to select 'campuscompass' database</p>";
                }
            } else {
                echo "<p>Database 'campuscompass' does not exist yet</p>";
                echo "<p><a href='setup-collegereviews.php'>Create campuscompass database and tables</a></p>";
            }
        }
    }
    
    // Check for tables in the current database
    echo "<h2>Tables in Current Database</h2>";
    $result = $conn->query("SHOW TABLES");
    
    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_row()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No tables found in the current database</p>";
    }
    
    // Check specifically for collegereviews
    $result = $conn->query("SHOW TABLES LIKE 'collegereviews'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color:green'>✅ Table 'collegereviews' exists</p>";
        
        // Check count
        $result = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
        if ($result && $row = $result->fetch_assoc()) {
            echo "<p>The collegereviews table has " . $row['count'] . " records</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Table 'collegereviews' does not exist</p>";
        echo "<p><a href='setup-collegereviews.php'>Set up collegereviews table</a></p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Database Error</h2>";
    echo "<p style='color:red'>" . $e->getMessage() . "</p>";
    
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Check if MySQL server is running</li>";
    echo "<li>Verify database credentials in config/database.php</li>";
    echo "<li>Try using 127.0.0.1 instead of localhost</li>";
    echo "<li>Ensure proper permissions for the database user</li>";
    echo "</ol>";
}
?>

<p><a href="setup-collegereviews.php">Set up collegereviews table</a></p>
<p><a href="../admin/review-management.html">Go to Review Management</a></p> 