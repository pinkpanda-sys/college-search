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
    
    // Set up the colleges table if it doesn't exist (for proper joining)
    $result = $conn->query("SHOW TABLES LIKE 'colleges'");
    $collegesTableExists = ($result && $result->num_rows > 0);
    
    if (!$collegesTableExists) {
        echo "<p>Creating Colleges table for proper joining...</p>";
        
        $sql = "CREATE TABLE colleges (
            id INT(11) NOT NULL AUTO_INCREMENT,
            rankings INT(11) NOT NULL,
            name VARCHAR(255) NOT NULL,
            location VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p>✅ Colleges table created successfully</p>";
            
            // Add sample colleges
            $sampleColleges = [
                ['rankings' => 1, 'name' => 'Harvard University', 'location' => 'Cambridge, MA'],
                ['rankings' => 2, 'name' => 'Stanford University', 'location' => 'Stanford, CA'],
                ['rankings' => 3, 'name' => 'MIT', 'location' => 'Cambridge, MA'],
                ['rankings' => 4, 'name' => 'Princeton University', 'location' => 'Princeton, NJ'],
                ['rankings' => 5, 'name' => 'Yale University', 'location' => 'New Haven, CT']
            ];
            
            $insertCount = 0;
            foreach ($sampleColleges as $college) {
                $stmt = $conn->prepare("INSERT INTO colleges (rankings, name, location) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $college['rankings'], $college['name'], $college['location']);
                
                if ($stmt->execute()) {
                    $insertCount++;
                } else {
                    echo "<p>❌ Error inserting college: " . $stmt->error . "</p>";
                }
                
                $stmt->close();
            }
            
            echo "<p>✅ Added $insertCount sample colleges to the colleges table</p>";
        } else {
            echo "<p>❌ Error creating Colleges table: " . $conn->error . "</p>";
        }
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