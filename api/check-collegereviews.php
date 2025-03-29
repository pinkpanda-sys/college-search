<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>College Reviews Table Check</h1>";

try {
    // Database connection
    require_once '../config/database.php';
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Get current database and switch if needed
    $result = $conn->query("SELECT DATABASE() as current_db");
    $row = $result->fetch_assoc();
    $currentDb = $row['current_db'];

    if ($currentDb !== 'campascompass') {
        if (!$conn->select_db('campascompass')) {
            throw new Exception("Failed to switch to campascompass database. Please ensure it exists.");
        }
        echo "<p>✅ Switched to campascompass database</p>";
    } else {
        echo "<p>✅ Already using campascompass database</p>";
    }
    
    // Check if collegereviews table exists
    $result = $conn->query("SHOW TABLES LIKE 'collegereviews'");
    $tableExists = ($result && $result->num_rows > 0);
    
    if (!$tableExists) {
        echo "<p>❌ The 'collegereviews' table does not exist in the campascompass database.</p>";
        echo "<p>You can create it by visiting: <a href='setup-collegereviews-data.php'>Setup College Reviews</a></p>";
        exit;
    }
    
    // Get row count
    $result = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
    $row = $result->fetch_assoc();
    $rowCount = $row['count'];
    
    echo "<h2>CollegeReviews Table Information:</h2>";
    echo "<p>Number of rows: $rowCount</p>";
    echo "<p>Number of columns: 7</p>";
    
    echo "<h2>Column Structure:</h2>";
    echo "<ul>";
    echo "<li>review_id (INT, Auto Increment, Primary Key)</li>";
    echo "<li>user_id (INT, Nullable)</li>";
    echo "<li>college_ranking (INT)</li>";
    echo "<li>rating (DECIMAL(3,1))</li>";
    echo "<li>review_text (TEXT)</li>";
    echo "<li>status (ENUM: 'pending', 'approved', 'rejected')</li>";
    echo "<li>created_at (TIMESTAMP, Default: CURRENT_TIMESTAMP)</li>";
    echo "</ul>";
    
    // Show sample data
    if ($rowCount > 0) {
        echo "<h2>First 5 Reviews:</h2>";
        $result = $conn->query("SELECT * FROM collegereviews LIMIT 5");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>User ID</th><th>College Ranking</th><th>Rating</th><th>Review Text</th><th>Status</th><th>Created At</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['review_id'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['college_ranking'] . "</td>";
            echo "<td>" . $row['rating'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($row['review_text'], 0, 100)) . "...</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?> 