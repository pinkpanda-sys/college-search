<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Quick Database Check</h1>";

try {
    require_once '../config/database.php';
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    // Get current database
    $result = $conn->query("SELECT DATABASE() as current_db");
    $row = $result->fetch_assoc();
    $currentDb = $row['current_db'];
    echo "<p>Current database: $currentDb</p>";
    
    // Try to switch to campascompass if not already using it
    if ($currentDb !== 'campascompass') {
        if (!$conn->select_db('campascompass')) {
            throw new Exception("Could not switch to campascompass database");
        }
        echo "<p>Switched to campascompass database</p>";
    }
    
    // Check table existence and data
    $result = $conn->query("SHOW TABLES LIKE 'collegereviews'");
    if (!$result || $result->num_rows === 0) {
        echo "<p>❌ collegereviews table does not exist!</p>";
        exit;
    }
    
    // Count rows
    $result = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
    $row = $result->fetch_assoc();
    echo "<p>Number of reviews in table: " . $row['count'] . "</p>";
    
    // Show sample data
    echo "<h2>Sample Data:</h2>";
    $result = $conn->query("SELECT * FROM collegereviews LIMIT 3");
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>User ID</th><th>College Ranking</th><th>Rating</th><th>Status</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['review_id'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['college_ranking'] . "</td>";
            echo "<td>" . $row['rating'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No data in the table!</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?> 