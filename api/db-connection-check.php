<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Verification</h1>";

try {
    // Database connection
    require_once '../config/database.php';
    
    if (!isset($conn)) {
        throw new Exception("Database connection variable not set. Check your database.php file.");
    }
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Base database connection is working</p>";
    
    // Get current database
    $result = $conn->query("SELECT DATABASE() as db");
    $row = $result->fetch_assoc();
    $currentDb = $row['db'];
    
    echo "<p>Current database: <strong>" . ($currentDb ?: 'None selected') . "</strong></p>";
    
    if ($currentDb != 'campuscompass') {
        echo "<p>⚠️ Current database is not 'campuscompass'. Attempting to switch...</p>";
        
        // Try to switch to campuscompass database
        if ($conn->select_db('campuscompass')) {
            echo "<p>✅ Successfully switched to 'campuscompass' database</p>";
            $currentDb = 'campuscompass';
        } else {
            echo "<p>❌ Failed to switch to 'campuscompass' database. Does it exist?</p>";
            
            // Get list of available databases
            $result = $conn->query("SHOW DATABASES");
            echo "<p>Available databases:</p><ul>";
            while ($row = $result->fetch_row()) {
                echo "<li>" . $row[0] . ($row[0] == 'campuscompass' ? ' ✓' : '') . "</li>";
            }
            echo "</ul>";
            
            // Ask if we should create the database
            echo "<p>Would you like to create the campuscompass database? <a href='?create=true'>Yes, create it</a></p>";
            
            // Create database if requested
            if (isset($_GET['create']) && $_GET['create'] === 'true') {
                if ($conn->query("CREATE DATABASE IF NOT EXISTS campuscompass")) {
                    echo "<p>✅ Created 'campuscompass' database</p>";
                    $conn->select_db('campuscompass');
                    $currentDb = 'campuscompass';
                } else {
                    echo "<p>❌ Failed to create 'campuscompass' database: " . $conn->error . "</p>";
                }
            }
        }
    }
    
    // From this point forward, check and work with the tables in the selected database
    // Always check if we actually have a selected database before proceeding
    if (!empty($currentDb)) {
        echo "<h2>Table Status in '$currentDb' Database</h2>";
        
        // Check for reviews and collegereviews tables
        $tables = [
            'collegereviews' => [
                'fields' => ['review_id', 'user_id', 'college_ranking', 'rating', 'review_text', 'status', 'created_at'],
                'setup_file' => 'setup-collegereviews-data.php'
            ],
            'reviews' => [
                'fields' => ['review_id', 'user_id', 'college_ranking', 'rating', 'review_text', 'status', 'created_at'],
                'setup_file' => 'setup-reviews.php'
            ],
            'colleges' => [
                'fields' => ['id', 'rankings', 'name', 'location'],
                'setup_file' => 'setup-colleges.php'
            ],
            'users' => [
                'fields' => ['user_id', 'username', 'email'],
                'setup_file' => 'setup-users.php'
            ]
        ];
        
        foreach ($tables as $table => $info) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "<h3>✅ Table '$table' exists</h3>";
                
                // Check record count
                $countResult = $conn->query("SELECT COUNT(*) as count FROM $table");
                if ($countResult && $row = $countResult->fetch_assoc()) {
                    $recordCount = $row['count'];
                    if ($recordCount > 0) {
                        echo "<p>Contains $recordCount records</p>";
                        
                        // Show sample data for the specific table
                        if ($table === 'collegereviews') {
                            $sample = $conn->query("SELECT * FROM $table LIMIT 3");
                            echo "<p>Sample data:</p>";
                            echo "<table border='1' cellpadding='5'>";
                            echo "<tr>";
                            while ($field = $sample->fetch_field()) {
                                echo "<th>" . $field->name . "</th>";
                            }
                            echo "</tr>";
                            
                            while ($row = $sample->fetch_assoc()) {
                                echo "<tr>";
                                foreach ($row as $value) {
                                    echo "<td>" . htmlspecialchars($value) . "</td>";
                                }
                                echo "</tr>";
                            }
                            echo "</table>";
                        }
                    } else {
                        echo "<p>⚠️ Table is empty. <a href='{$info['setup_file']}'>Run setup script</a> to add sample data.</p>";
                    }
                }
                
                // Check structure
                $fieldsResult = $conn->query("DESCRIBE $table");
                $existing_fields = [];
                if ($fieldsResult) {
                    while ($row = $fieldsResult->fetch_assoc()) {
                        $existing_fields[] = $row['Field'];
                    }
                }
                
                // Check if all required fields exist
                $missing_fields = array_diff($info['fields'], $existing_fields);
                if (empty($missing_fields)) {
                    echo "<p>✅ Table structure looks good</p>";
                } else {
                    echo "<p>⚠️ Missing fields: " . implode(", ", $missing_fields) . "</p>";
                }
            } else {
                echo "<h3>❌ Table '$table' does not exist</h3>";
                echo "<p>You should <a href='{$info['setup_file']}'>run the setup script</a> to create this table.</p>";
            }
        }
    }
    
    echo "<h2>Next Steps</h2>";
    echo "<ol>";
    echo "<li><a href='setup-collegereviews-data.php'>Run College Reviews Setup Script</a> to ensure the collegereviews table exists with data</li>";
    echo "<li><a href='../admin/review-management.html'>Go to Review Management</a> to see if reviews are displayed correctly</li>";
    echo "<li><a href='reviews.php?debug=db'>Check API Database Connection</a> to verify what database the API sees</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    
    echo "<h3>Database Configuration Check</h3>";
    echo "<p>Check your database.php file to ensure it has the correct connection settings:</p>";
    echo "<pre>";
    echo "// Expected contents of config/database.php:
    \$servername = \"localhost\"; // or your database server
    \$username = \"your_database_username\";
    \$password = \"your_database_password\";
    \$dbname = \"campuscompass\"; // Make sure this is correct
    
    // Create connection
    \$conn = new mysqli(\$servername, \$username, \$password, \$dbname);
    </pre>";
}
?> 