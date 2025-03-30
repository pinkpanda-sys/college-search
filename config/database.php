<?php
// Database connection configuration
$servername = "localhost";  // Your MySQL server
$username = "root";         // Your MySQL username
$password = "";            // Your MySQL password
$dbname = "campuscompass";  // Fixed database name typo (was campascompass)

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    $GLOBALS['db_connection_error'] = $conn->connect_error;
    return;
}

// Check if campuscompass database exists
$result = $conn->query("SHOW DATABASES LIKE '$dbname'");
$dbExists = ($result && $result->num_rows > 0);

// Create database if it doesn't exist
if (!$dbExists) {
    if (!$conn->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
        $GLOBALS['db_connection_error'] = "Failed to create/select database $dbname";
        return;
    }
}

// Select the campuscompass database
if (!$conn->select_db($dbname)) {
    $GLOBALS['db_connection_error'] = "Failed to select database $dbname";
    return;
}

// Log the current database (for debugging)
$result = $conn->query("SELECT DATABASE() as current_db");
if ($result && $row = $result->fetch_assoc()) {
    error_log("Connected to database: " . $row['current_db']);
}
?>
