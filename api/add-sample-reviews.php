<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Add Sample Reviews</h1>";

try {
    require_once '../config/database.php';
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    // Ensure we're using campascompass database
    if (!$conn->select_db('campascompass')) {
        throw new Exception("Failed to switch to campascompass database");
    }
    
    // Check if table exists and is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM collegereviews");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo "<p>Table already has " . $row['count'] . " reviews. No need to add sample data.</p>";
        exit;
    }
    
    // Add sample reviews
    $sampleReviews = [
        [
            'user_id' => 1,
            'college_ranking' => 1,
            'rating' => 4.5,
            'review_text' => 'Excellent faculty and great campus environment.',
            'status' => 'approved'
        ],
        [
            'user_id' => 2,
            'college_ranking' => 2,
            'rating' => 4.0,
            'review_text' => 'Good facilities but needs improvement in some areas.',
            'status' => 'pending'
        ],
        [
            'user_id' => 3,
            'college_ranking' => 3,
            'rating' => 3.5,
            'review_text' => 'Average experience, could be better.',
            'status' => 'approved'
        ]
    ];
    
    $insertCount = 0;
    foreach ($sampleReviews as $review) {
        $stmt = $conn->prepare("INSERT INTO collegereviews (user_id, college_ranking, rating, review_text, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iidss", 
            $review['user_id'],
            $review['college_ranking'],
            $review['rating'],
            $review['review_text'],
            $review['status']
        );
        
        if ($stmt->execute()) {
            $insertCount++;
        }
        $stmt->close();
    }
    
    echo "<p>Successfully added $insertCount sample reviews.</p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?> 