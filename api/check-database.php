<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Check</h1>";

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
    echo "<p>Server info: " . $conn->server_info . "</p>";
    
    // Get database name
    $result = $conn->query("SELECT DATABASE() as db_name");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Current database: " . ($row['db_name'] ?? 'Unknown') . "</p>";
    }
    
    // Check tables
    $tables = [
        'reviews' => 'Setup Review Tables',
        'collegereviews' => 'Setup College Reviews Table',
        'colleges' => 'Setup Colleges Table',
        'users' => 'Setup Users Table'
    ];
    
    echo "<h2>Table Check</h2>";
    $anyTableExists = false;
    
    foreach ($tables as $table => $setupLink) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        $tableExists = ($result && $result->num_rows > 0);
        
        if ($tableExists) {
            $anyTableExists = true;
            echo "<p>✅ Table '$table' exists</p>";
            
            // Check row count
            $countResult = $conn->query("SELECT COUNT(*) as count FROM $table");
            if ($countResult) {
                $row = $countResult->fetch_assoc();
                echo "<p>&nbsp;&nbsp;&nbsp; - Contains " . $row['count'] . " rows</p>";
                
                if ($row['count'] == 0) {
                    echo "<p>&nbsp;&nbsp;&nbsp; - ⚠️ Table is empty. <a href='setup-test-reviews.php'>Add test data</a></p>";
                }
            }
        } else {
            echo "<p>❌ Table '$table' does not exist</p>";
        }
    }
    
    if (!$anyTableExists) {
        echo "<p>No required tables exist. <a href='setup-test-reviews.php'>Click here to set up test data</a></p>";
    }
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li><a href='setup-test-reviews.php'>Set up test review data</a></li>";
    echo "<li><a href='../admin/review-management.html'>Go to Review Management</a></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Make sure the database connection settings in config/database.php are correct.</p>";
    
    echo "<h3>Try using test data instead</h3>";
    echo "<p>If you're having trouble with the database, you can use test data:</p>";
    echo "<ol>";
    echo "<li><a href='../admin/review-management.html'>Go to Review Management</a> (it's set to use test data by default)</li>";
    echo "<li>If you don't see any reviews, click the \"Debug Mode\" button at bottom right</li>";
    echo "<li>Then click \"Show Sample Reviews\" to display fake review data</li>";
    echo "</ol>";
}
?> 