<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
require_once '../config/database.php';

try {
    echo "<h1>Setting up Reviews Table</h1>";
    
    // Check if the reviews table exists
    $tableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'reviews'");
    if ($result && $result->num_rows > 0) {
        $tableExists = true;
        echo "<p>Reviews table already exists.</p>";
    }
    
    // Create the reviews table if it doesn't exist
    if (!$tableExists) {
        $sql = "CREATE TABLE reviews (
            review_id INT(11) NOT NULL AUTO_INCREMENT,
            user_id INT(11) NULL,
            college_ranking INT(11) NOT NULL,
            rating DECIMAL(3,1) NOT NULL,
            review_text TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (review_id)
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p>Reviews table created successfully.</p>";
            
            // Check if colleges table exists before adding sample data
            $result = $conn->query("SHOW TABLES LIKE 'colleges'");
            if ($result && $result->num_rows > 0) {
                echo "<p>Adding sample reviews...</p>";
                
                // Get existing college rankings from the colleges table
                $rankings = [];
                $result = $conn->query("SELECT rankings FROM colleges ORDER BY rankings LIMIT 10");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $rankings[] = $row['rankings'];
                    }
                }
                
                // If no colleges found, use default values
                if (empty($rankings)) {
                    $rankings = [1, 2, 3, 4, 5];
                    echo "<p>No colleges found in database. Using default rankings.</p>";
                }
                
                // Sample review texts
                $reviewTexts = [
                    "This college offers excellent faculty and infrastructure. The campus is beautiful and the course material is up-to-date with industry standards.",
                    "I had a great learning experience at this institution. The professors are knowledgeable and helpful.",
                    "Average college with decent facilities. Could improve on extracurricular activities and placement assistance.",
                    "Not satisfied with the teaching methods. The curriculum needs to be updated to meet current industry demands.",
                    "Outstanding college! Great environment for learning with state-of-the-art laboratories and libraries."
                ];
                
                // Insert sample reviews
                $insertCount = 0;
                foreach ($rankings as $ranking) {
                    // Generate 1-3 reviews for each college
                    $reviewCount = rand(1, 3);
                    for ($i = 0; $i < $reviewCount; $i++) {
                        $rating = (rand(30, 50) / 10); // Random rating between 3.0 and 5.0
                        $reviewText = $reviewTexts[array_rand($reviewTexts)];
                        $status = ['pending', 'approved', 'rejected'][rand(0, 2)]; // Random status
                        
                        $stmt = $conn->prepare("INSERT INTO reviews (college_ranking, rating, review_text, status) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("idss", $ranking, $rating, $reviewText, $status);
                        
                        if ($stmt->execute()) {
                            $insertCount++;
                        } else {
                            echo "<p>Error adding sample review: " . $stmt->error . "</p>";
                        }
                    }
                }
                
                echo "<p>Added $insertCount sample reviews successfully.</p>";
            } else {
                echo "<p>Warning: Colleges table does not exist. Please set up colleges first for reviews to work properly.</p>";
                echo "<p><a href='setup-table.php'>Set up colleges table</a></p>";
            }
        } else {
            echo "<p>Error creating reviews table: " . $conn->error . "</p>";
        }
    }
    
    // Display review count
    $result = $conn->query("SELECT COUNT(*) as count FROM reviews");
    if ($result && $row = $result->fetch_assoc()) {
        echo "<p>Total reviews in database: " . $row['count'] . "</p>";
    }
    
    // Show reviews table structure
    echo "<h2>Reviews Table Structure</h2>";
    $result = $conn->query("DESCRIBE reviews");
    if ($result) {
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<p><a href='../admin/review-management.html'>Go to Review Management</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 