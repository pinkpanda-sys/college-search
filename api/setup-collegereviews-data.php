<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Setting Up College Reviews Table</h1>";

// Database connection
require_once '../config/database.php';

try {
    // Check connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Get current database
    $result = $conn->query("SELECT DATABASE() as current_db");
    $row = $result->fetch_assoc();
    $currentDb = $row['current_db'];
    echo "<p>Current database: $currentDb</p>";
    
    // Check if colleges table exists (for relationship)
    $collegesTableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'colleges'");
    $collegesTableExists = ($result && $result->num_rows > 0);
    
    if (!$collegesTableExists) {
        echo "<p>Creating colleges table for proper relationships...</p>";
        
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
            
            echo "<p>✅ Added $insertCount sample colleges to the database</p>";
            
            $collegesTableExists = true;
        } else {
            echo "<p>❌ Error creating colleges table: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ Colleges table already exists</p>";
    }
    
    // Check if collegereviews table exists
    $collegeReviewsTableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'collegereviews'");
    $collegeReviewsTableExists = ($result && $result->num_rows > 0);
    
    if (!$collegeReviewsTableExists) {
        echo "<p>Creating collegereviews table...</p>";
        
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
        } else {
            echo "<p>❌ Error creating College Reviews table: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ College Reviews table already exists</p>";
        
        // Check if it has data
        $result = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
        $row = $result->fetch_assoc();
        $reviewCount = $row['count'];
        
        echo "<p>College Reviews table has $reviewCount records</p>";
        
        if ($reviewCount > 0) {
            echo "<p>The table already has data. Skipping sample data insertion.</p>";
            echo "<p><a href='../admin/review-management.html'>Go to Review Management</a></p>";
            exit; // Exit early if data exists
        }
    }
    
    // Add sample data to collegereviews table
    if ($collegeReviewsTableExists) {
        echo "<p>Adding sample data to collegereviews table...</p>";
        
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
        
        echo "<p>✅ Added $insertCount sample reviews to the database</p>";
    }
    
    echo "<p>Setup completed successfully!</p>";
    echo "<p><a href='../admin/review-management.html'>Go to Review Management</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?> 