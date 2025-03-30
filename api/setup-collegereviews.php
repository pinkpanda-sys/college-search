<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Setting up College Reviews Table in CampusCompass</h1>";

// Database connection
require_once '../config/database.php';

// Check for connection error from the improved database.php
if (isset($GLOBALS['db_connection_error'])) {
    echo "<div style='color:red; padding:10px; border:1px solid red; margin:10px;'>";
    echo "<h2>Database Connection Error</h2>";
    echo "<p>" . $GLOBALS['db_connection_error'] . "</p>";
    echo "<p>Please check your database configuration in config/database.php</p>";
    echo "<p><a href='db-test.php'>Run a database connection test</a></p>";
    echo "</div>";
    exit;
}

try {
    // Additional check to ensure connection is established
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Get current database name
    $result = $conn->query("SELECT DATABASE() as db_name");
    $row = $result->fetch_assoc();
    $dbName = $row['db_name'];
    
    echo "<p>Current database: " . $dbName . "</p>";
    
    // Ensure we're using the campuscompass database
    if ($dbName != 'campuscompass') {
        // Try to switch to campuscompass
        if (!$conn->select_db('campuscompass')) {
            // Create the database if it doesn't exist
            if ($conn->query("CREATE DATABASE IF NOT EXISTS campuscompass")) {
                $conn->select_db('campuscompass');
                echo "<p>✅ Created and switched to campuscompass database</p>";
            } else {
                throw new Exception("Failed to create campuscompass database: " . $conn->error);
            }
        } else {
            echo "<p>✅ Switched to campuscompass database</p>";
        }
    }
    
    // Check if collegereviews table exists
    $collegeReviewsTableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'collegereviews'");
    $collegeReviewsTableExists = ($result && $result->num_rows > 0);
    
    if ($collegeReviewsTableExists) {
        echo "<p>✅ College Reviews table already exists</p>";
        
        // Check row count
        $countResult = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
        if ($countResult) {
            $row = $countResult->fetch_assoc();
            $reviewCount = $row['count'];
            echo "<p>The table currently has " . $reviewCount . " reviews</p>";
            
            if ($reviewCount == 0) {
                echo "<p>College Reviews table is empty. Adding sample data...</p>";
                // Will add sample data below
            } else {
                echo "<p>College Reviews table already has data</p>";
            }
        }
    } else {
        echo "<p>Creating College Reviews table...</p>";
        
        $sql = "CREATE TABLE collegereviews (
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
            echo "<p>✅ College Reviews table created successfully</p>";
            $collegeReviewsTableExists = true;
            $reviewCount = 0; // Set to 0 for the next check
        } else {
            throw new Exception("Error creating College Reviews table: " . $conn->error);
        }
    }
    
    // Add sample data if table is empty or newly created
    if ($collegeReviewsTableExists && (isset($reviewCount) && $reviewCount == 0)) {
        // Sample college reviews
        $sampleReviews = [
            [
                'user_id' => 101,
                'college_ranking' => 1,
                'rating' => 4.8,
                'review_text' => 'Our institution prides itself on providing a world-class education with state-of-the-art facilities and renowned faculty members.',
                'status' => 'approved'
            ],
            [
                'user_id' => 102,
                'college_ranking' => 2,
                'rating' => 4.2,
                'review_text' => 'We offer exceptional value for education with strong industry connections and impressive post-graduation placement rates.',
                'status' => 'pending'
            ],
            [
                'user_id' => 103,
                'college_ranking' => 3,
                'rating' => 4.5,
                'review_text' => 'Our college has been recognized for excellence in research and innovation, with numerous grants and partnerships with leading companies.',
                'status' => 'approved'
            ],
            [
                'user_id' => 104,
                'college_ranking' => 4,
                'rating' => 3.9,
                'review_text' => 'The academic environment at our institution challenges students while providing the support they need to succeed in their careers.',
                'status' => 'pending'
            ],
            [
                'user_id' => 105,
                'college_ranking' => 5,
                'rating' => 4.7,
                'review_text' => 'Our small class sizes and personalized approach to education ensure that every student receives the attention they deserve.',
                'status' => 'rejected'
            ]
        ];
        
        // Insert sample data
        $insertCount = 0;
        foreach ($sampleReviews as $review) {
            $stmt = $conn->prepare("INSERT INTO collegereviews (user_id, college_ranking, rating, review_text, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iidss", $review['user_id'], $review['college_ranking'], $review['rating'], $review['review_text'], $review['status']);
            
            if ($stmt->execute()) {
                $insertCount++;
            } else {
                echo "<p>❌ Error inserting review: " . $stmt->error . "</p>";
            }
            
            $stmt->close();
        }
        
        echo "<p>✅ Added $insertCount sample reviews to the collegereviews table</p>";
    }
    
    // Create colleges table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS colleges (
        ranking INT(11) NOT NULL,
        name VARCHAR(200) NOT NULL,
        location TEXT NOT NULL,
        contact VARCHAR(100) DEFAULT NULL,
        fees DECIMAL(10,2) DEFAULT NULL,
        maplink VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (ranking)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql)) {
        echo "Colleges table verified/created.\n";
    } else {
        throw new Exception("Error creating colleges table: " . $conn->error);
    }
    
    // Check if we already have colleges
    $result = $conn->query("SELECT COUNT(*) as count FROM colleges");
    $row = $result->fetch_assoc();
    
    if ($row['count'] < 5) {
        echo "Inserting sample colleges...\n";
        
        // Prepare sample colleges
        $colleges = [
            ['ranking' => 1, 'name' => 'Harvard University', 'location' => 'Cambridge, MA'],
            ['ranking' => 2, 'name' => 'Stanford University', 'location' => 'Stanford, CA'],
            ['ranking' => 3, 'name' => 'MIT', 'location' => 'Cambridge, MA'],
            ['ranking' => 4, 'name' => 'Princeton University', 'location' => 'Princeton, NJ'],
            ['ranking' => 5, 'name' => 'Yale University', 'location' => 'New Haven, CT']
        ];
        
        // Insert sample colleges
        $stmt = $conn->prepare("INSERT INTO colleges (ranking, name, location) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $college['ranking'], $college['name'], $college['location']);
        
        foreach ($colleges as $college) {
            try {
                $stmt->execute();
                echo "Added college: " . $college['name'] . "\n";
            } catch (Exception $e) {
                echo "Note: " . $college['name'] . " may already exist. Skipping.\n";
            }
        }
    } else {
        echo "Colleges table already has data. Skipping sample data insertion.\n";
    }
    
    echo "<h2>Setup Complete</h2>";
    echo "<p>Your collegereviews table is now ready to use.</p>";
    echo "<p><a href='../admin/review-management.html'>Go to Review Management</a></p>";
    echo "<p><a href='review-check.php'>Run a system check</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='db-test.php'>Run a database connection test</a></li>";
    echo "<li>Check if MySQL server is running</li>";
    echo "<li>Verify database credentials in config/database.php</li>";
    echo "<li>Make sure the web server has permissions to connect to MySQL</li>";
    echo "</ol>";
}
?> 