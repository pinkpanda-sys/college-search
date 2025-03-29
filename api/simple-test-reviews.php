<?php
// Add CORS headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Ensure content type for regular requests
header('Content-Type: application/json');

// Sample test data
$testReviews = [
    [
        'review_id' => 1,
        'user_id' => 101,
        'college_ranking' => 1,
        'rating' => 4.5,
        'review_text' => 'This is a sample review from the simple test API. If you see this, your JavaScript is working correctly.',
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'source' => 'reviews',
        'college_name' => 'Simple Test University',
        'college_location' => 'Test City, Test State',
        'user_name' => 'Test User'
    ],
    [
        'review_id' => 2,
        'user_id' => 102,
        'college_ranking' => 2,
        'rating' => 3.5,
        'review_text' => 'This is a sample college review from the simple test API. No database connection required to see this.',
        'status' => 'approved',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'source' => 'collegereviews',
        'college_name' => 'Another Test University',
        'college_location' => 'Another City, Another State',
        'user_name' => 'College Reviewer'
    ],
    [
        'review_id' => 3,
        'user_id' => 103,
        'college_ranking' => 3,
        'rating' => 2.0,
        'review_text' => 'This is a rejected review sample from the simple test API. This confirms that your frontend is working.',
        'status' => 'rejected',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'source' => 'reviews',
        'college_name' => 'Third Test University',
        'college_location' => 'Third City, Third State',
        'user_name' => 'Rejected User'
    ]
];

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if specific ID is requested
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        
        foreach ($testReviews as $review) {
            if ($review['review_id'] === $id) {
                echo json_encode($review);
                exit;
            }
        }
        
        // Review not found
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Review not found']);
        exit;
    }
    
    // Return all reviews (with optional filtering)
    $filteredReviews = $testReviews;
    
    // Filter by status if provided
    if (isset($_GET['status']) && $_GET['status'] !== 'all') {
        $status = $_GET['status'];
        $filteredReviews = array_filter($filteredReviews, function($review) use ($status) {
            return $review['status'] === $status;
        });
    }
    
    // Filter by source if provided
    if (isset($_GET['source']) && $_GET['source'] !== 'all') {
        $source = $_GET['source'];
        $filteredReviews = array_filter($filteredReviews, function($review) use ($source) {
            return $review['source'] === $source;
        });
    }
    
    // Search if provided
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $search = strtolower(trim($_GET['search']));
        $filteredReviews = array_filter($filteredReviews, function($review) use ($search) {
            return strpos(strtolower($review['college_name']), $search) !== false || 
                   strpos(strtolower($review['review_text']), $search) !== false;
        });
    }
    
    // Reset array keys and return
    echo json_encode(array_values($filteredReviews));
    exit;
}

// Handle POST request (update status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse JSON body
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['review_id']) || !isset($data['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    // In a real implementation, this would update the review status
    // For this test version, we just return success
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully (test mode)'
    ]);
    exit;
}

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Review ID is required']);
        exit;
    }
    
    // In a real implementation, this would delete the review
    // For this test version, we just return success
    echo json_encode([
        'success' => true,
        'message' => 'Review deleted successfully (test mode)'
    ]);
    exit;
}

// If we get here, it's an unsupported method
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?> 