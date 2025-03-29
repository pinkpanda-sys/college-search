<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

// Define direct database credentials to test connection
$servername = "localhost";  // Try 127.0.0.1 if localhost doesn't work
$username = "root";         // Your database username
$password = "";             // Your database password

echo "<p>Attempting to connect to MySQL server at: $servername</p>";
echo "<p>Using username: $username</p>";

try {
    // Create connection without selecting a database
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color:green'>✅ Successfully connected to MySQL server!</p>";
    
    // Check if campuscompass database exists
    $result = $conn->query("SHOW DATABASES LIKE 'campuscompass'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color:green'>✅ 'campuscompass' database exists</p>";
        
        // Try to select it
        if ($conn->select_db('campuscompass')) {
            echo "<p style='color:green'>✅ Successfully selected 'campuscompass' database</p>";
            
            // List tables
            $tables = [];
            $result = $conn->query("SHOW TABLES");
            if ($result) {
                while ($row = $result->fetch_row()) {
                    $tables[] = $row[0];
                }
            }
            
            echo "<p>Tables in 'campuscompass' database: " . (empty($tables) ? "none" : implode(", ", $tables)) . "</p>";
        } else {
            echo "<p style='color:red'>❌ Failed to select 'campuscompass' database: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:orange'>⚠️ 'campuscompass' database does not exist yet.</p>";
    }
    
    // Show all available databases
    echo "<h2>Available Databases:</h2>";
    echo "<ul>";
    $result = $conn->query("SHOW DATABASES");
    while ($row = $result->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ " . $e->getMessage() . "</p>";
    
    // Additional debugging info
    echo "<h2>Troubleshooting Tips:</h2>";
    echo "<ol>";
    echo "<li>Check if MySQL server is running</li>";
    echo "<li>Verify username and password are correct</li>";
    echo "<li>Try using '127.0.0.1' instead of 'localhost'</li>";
    echo "<li>Make sure MySQL port is not blocked (default: 3306)</li>";
    echo "<li>Check MySQL error logs for additional information</li>";
    echo "</ol>";
}
?>

<p><a href="setup-collegereviews.php">Try setup again</a> after fixing connection issues</p> 