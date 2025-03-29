<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Setting Up Test Review Data</h1>";

// Database connection
require_once '../config/database.php';

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Check if reviews table exists
    $reviewsTableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'reviews'");
    $reviewsTableExists = ($result && $result->num_rows > 0);
    
    // Check if collegereviews table exists
    $collegeReviewsTableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'collegereviews'");
    $collegeReviewsTableExists = ($result && $result->num_rows > 0);
    
    // If neither exists, create the reviews table
    if (!$reviewsTableExists && !$collegeReviewsTableExists) {
        echo "<p>Creating reviews table...</p>";
        
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
            echo "<p>✅ Reviews table created successfully</p>";
            $reviewsTableExists = true;
        } else {
            echo "<p>❌ Error creating reviews table: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ " . ($reviewsTableExists ? "Reviews" : "College reviews") . " table already exists</p>";
    }
    
    // Add sample data to the reviews table
    if ($reviewsTableExists) {
        // Check if the table is empty
        $result = $conn->query("SELECT COUNT(*) as count FROM reviews");
        $row = $result->fetch_assoc();
        $count = $row['count'];
        
        if ($count === "0") {
            echo "<p>Adding sample reviews to reviews table...</p>";
            
            // Sample review data
            $sampleReviews = [
                [
                    'user_id' => 1,
                    'college_ranking' => 1,
                    'rating' => 4.5,
                    'review_text' => 'Excellent university with top-notch faculty and amazing campus. The educational experience was transformative and prepared me well for my career.',
                    'status' => 'approved'
                ],
                [
                    'user_id' => 2,
                    'college_ranking' => 2,
                    'rating' => 3.5,
                    'review_text' => 'Good college overall. The facilities are nice but some programs could use improvement. Most professors are knowledgeable and helpful.',
                    'status' => 'pending'
                ],
                [
                    'user_id' => 3,
                    'college_ranking' => 3,
                    'rating' => 2.0,
                    'review_text' => 'Disappointing experience. The classes were overcrowded and the administration was not helpful with resolving issues.',
                    'status' => 'rejected'
                ],
                [
                    'user_id' => 4,
                    'college_ranking' => 4,
                    'rating' => 5.0,
                    'review_text' => 'Best decision I ever made! The academic environment is challenging but supportive, and the networking opportunities are outstanding.',
                    'status' => 'approved'
                ],
                [
                    'user_id' => 5,
                    'college_ranking' => 5,
                    'rating' => 3.0,
                    'review_text' => 'Average experience. Some departments are excellent while others need significant improvement. Student life is good though.',
                    'status' => 'pending'
                ]
            ];
            
            // Insert sample reviews
            $insertCount = 0;
            foreach ($sampleReviews as $review) {
                $stmt = $conn->prepare("INSERT INTO reviews (user_id, college_ranking, rating, review_text, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('iidss', $review['user_id'], $review['college_ranking'], $review['rating'], $review['review_text'], $review['status']);
                
                if ($stmt->execute()) {
                    $insertCount++;
                } else {
                    echo "<p>❌ Error inserting review: " . $stmt->error . "</p>";
                }
                $stmt->close();
            }
            
            echo "<p>✅ Added $insertCount sample reviews to the reviews table</p>";
        } else {
            echo "<p>✅ Reviews table already has $count reviews</p>";
        }
    }
    
    // Add sample data to the collegereviews table if it exists
    if ($collegeReviewsTableExists) {
        // Check if the table is empty
        $result = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
        $row = $result->fetch_assoc();
        $count = $row['count'];
        
        if ($count === "0") {
            echo "<p>Adding sample reviews to collegereviews table...</p>";
            
            // Sample college review data
            $sampleCollegeReviews = [
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
                ]
            ];
            
            // Check if the college reviews table has the same structure
            try {
                $result = $conn->query("DESCRIBE collegereviews");
                if ($result) {
                    // Insert sample college reviews
                    $insertCount = 0;
                    foreach ($sampleCollegeReviews as $review) {
                        $stmt = $conn->prepare("INSERT INTO collegereviews (user_id, college_ranking, rating, review_text, status) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param('iidss', $review['user_id'], $review['college_ranking'], $review['rating'], $review['review_text'], $review['status']);
                        
                        if ($stmt->execute()) {
                            $insertCount++;
                        } else {
                            echo "<p>❌ Error inserting college review: " . $stmt->error . "</p>";
                        }
                        $stmt->close();
                    }
                    
                    echo "<p>✅ Added $insertCount sample reviews to the collegereviews table</p>";
                } else {
                    echo "<p>❌ Could not verify collegereviews table structure</p>";
                }
            } catch (Exception $e) {
                echo "<p>❌ Error checking collegereviews table structure: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>✅ College reviews table already has $count reviews</p>";
        }
    }
    
    // Create collegereviews table if it doesn't exist but reviews exists
    if ($reviewsTableExists && !$collegeReviewsTableExists) {
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
            echo "<p>✅ College reviews table created successfully</p>";
            
            // Add sample data to the newly created collegereviews table
            echo "<p>Adding sample reviews to collegereviews table...</p>";
            
            // Sample college review data
            $sampleCollegeReviews = [
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
                ]
            ];
            
            // Insert sample college reviews
            $insertCount = 0;
            foreach ($sampleCollegeReviews as $review) {
                $stmt = $conn->prepare("INSERT INTO collegereviews (user_id, college_ranking, rating, review_text, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('iidss', $review['user_id'], $review['college_ranking'], $review['rating'], $review['review_text'], $review['status']);
                
                if ($stmt->execute()) {
                    $insertCount++;
                } else {
                    echo "<p>❌ Error inserting college review: " . $stmt->error . "</p>";
                }
                $stmt->close();
            }
            
            echo "<p>✅ Added $insertCount sample reviews to the collegereviews table</p>";
        } else {
            echo "<p>❌ Error creating collegereviews table: " . $conn->error . "</p>";
        }
    }
    
    echo "<h2>Setup Completed</h2>";
    echo "<p>The review system should now have sample data to display.</p>";
    echo "<p><a href='../admin/review-management.html'>Go to Review Management</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 