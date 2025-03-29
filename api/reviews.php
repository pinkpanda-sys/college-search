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
ini_set('display_errors', 1); // Set to 1 temporarily for debugging
error_reporting(E_ALL);

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Add detailed error logging
function logError($message) {
    global $logDir;
    if (is_dir($logDir) && is_writable($logDir)) {
        file_put_contents(
            $logDir . '/reviews-api-error.log',
            date('[Y-m-d H:i:s]') . ' ' . $message . "\n",
            FILE_APPEND
        );
    }
}

// Try-catch for better error handling
try {
    // Load database connection
    require_once '../config/database.php';
    
    // Check connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    // Check if GET request for test
    if (isset($_GET['test']) && $_GET['test'] === 'db') {
        echo json_encode(['success' => true, 'message' => 'Database connection successful']);
        exit;
    }
    
    // Display current database
    if (isset($_GET['debug']) && $_GET['debug'] === 'db') {
        $result = $conn->query("SELECT DATABASE() as current_db");
        if ($result && $row = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'current_database' => $row['current_db']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not determine current database']);
        }
        exit;
    }
    
    // Check if we're in the correct database
    $result = $conn->query("SELECT DATABASE() as current_db");
    if ($result && $row = $result->fetch_assoc()) {
        $currentDb = $row['current_db'];
        if ($currentDb !== 'campascompass') {
            // Try to switch to campascompass
            if (!$conn->select_db('campascompass')) {
                throw new Exception("Failed to switch to campascompass database");
            }
        }
    }
    
    // Check ONLY for collegereviews table
    $collegeReviewsTableExists = false;
    
    try {
        $result = $conn->query("SHOW TABLES LIKE 'collegereviews'");
        $collegeReviewsTableExists = ($result && $result->num_rows > 0);
    } catch (Exception $e) {
        error_log("Error checking collegereviews table: " . $e->getMessage());
    }
    
    // If the collegereviews table doesn't exist, return an error
    if (!$collegeReviewsTableExists) {
        echo json_encode([
            'error' => true, 
            'message' => 'The collegereviews table does not exist in campascompass database. Please set up the table first.',
            'setup_needed' => true
        ]);
        exit;
    }
    
    // Handle different HTTP methods
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            getReviews($conn);
            break;
        case 'POST':
            updateReviewStatus($conn);
            break;
        case 'DELETE':
            deleteReview($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    // Log error
    logError("Main API Error: " . $e->getMessage());
    
    // Return error
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

// Function to get reviews - ONLY from collegereviews table
function getReviews($conn) {
    try {
        $query = "SELECT * FROM collegereviews";
        $conditions = [];
        $params = [];
        $types = "";

        // Add status filter if provided
        if (isset($_GET['status']) && $_GET['status'] !== 'all') {
            $conditions[] = "status = ?";
            $params[] = $_GET['status'];
            $types .= "s";
        }

        // Add search filter if provided
        if (isset($_GET['search']) && trim($_GET['search']) !== '') {
            $searchTerm = '%' . trim($_GET['search']) . '%';
            $conditions[] = "review_text LIKE ?";
            $params[] = $searchTerm;
            $types .= "s";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY created_at DESC LIMIT 50";

        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            // Map the database column names to what the frontend expects
            $reviews[] = [
                'review_id' => $row['r_id'] ?? null,
                'user_id' => $row['u_id'] ?? null,
                'college_ranking' => $row['college_ranking'] ?? null,
                'rating' => $row['rating'] ?? null,
                'review_text' => $row['review_text'] ?? null,
                'status' => $row['status'] ?? 'pending', // Default to pending if not set
                'created_at' => $row['created_at'] ?? null
            ];
        }

        echo json_encode($reviews);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Error fetching reviews: ' . $e->getMessage()]);
    }
}

// Helper function to get a single review by ID
function getReviewById($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM collegereviews WHERE r_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            // Map the database column names to what the frontend expects
            $review = [
                'review_id' => $row['r_id'] ?? null,
                'user_id' => $row['u_id'] ?? null,
                'college_ranking' => $row['college_ranking'] ?? null,
                'rating' => $row['rating'] ?? null,
                'review_text' => $row['review_text'] ?? null,
                'status' => $row['status'] ?? 'pending', // Default to pending if not set
                'created_at' => $row['created_at'] ?? null
            ];
            echo json_encode($review);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Review not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching review: ' . $e->getMessage()]);
    }
}

// Function to get reviews from collegereviews table
function getReviewsFromTable($conn) {
    try {
        // Check if colleges table exists for joining
        $result = $conn->query("SHOW TABLES LIKE 'colleges'");
        $collegesTableExists = ($result && $result->num_rows > 0);
        
        // Check if users table exists for joining
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        $usersTableExists = ($result && $result->num_rows > 0);
        
        // Build the base query depending on available tables
        if ($collegesTableExists && $usersTableExists) {
            $query = "
                SELECT r.*, c.name as college_name, c.location as college_location, 
                       u.username as user_name, u.email as user_email
                FROM collegereviews r
                LEFT JOIN colleges c ON r.college_ranking = c.rankings
                LEFT JOIN users u ON r.user_id = u.user_id
            ";
        } else if ($collegesTableExists) {
            $query = "
                SELECT r.*, c.name as college_name, c.location as college_location
                FROM collegereviews r
                LEFT JOIN colleges c ON r.college_ranking = c.rankings
            ";
        } else if ($usersTableExists) {
            $query = "
                SELECT r.*, u.username as user_name, u.email as user_email
                FROM collegereviews r
                LEFT JOIN users u ON r.user_id = u.user_id
            ";
        } else {
            $query = "SELECT * FROM collegereviews";
        }
        
        // Add filters if provided
        $conditions = [];
        $params = [];
        $types = "";
        
        // Filter by status if provided
        if (isset($_GET['status']) && $_GET['status'] !== 'all') {
            $conditions[] = "r.status = ?";
            $params[] = $_GET['status'];
            $types .= "s";
        }
        
        // Add search if provided
        if (isset($_GET['search']) && trim($_GET['search']) !== '') {
            $searchTerm = '%' . trim($_GET['search']) . '%';
            if ($collegesTableExists) {
                $conditions[] = "(c.name LIKE ? OR r.review_text LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "ss";
            } else {
                $conditions[] = "r.review_text LIKE ?";
                $params[] = $searchTerm;
                $types .= "s";
            }
        }
        
        // Add conditions to query if any
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Order by
        $query .= " ORDER BY r.created_at DESC LIMIT 50";
        
        // Prepare and execute the query
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        
        return $reviews;
    } catch (Exception $e) {
        logError("Error in getReviewsFromTable: " . $e->getMessage());
        return [];
    }
}

// Function to update review status
function updateReviewStatus($conn) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['review_id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields (review_id, status)']);
            return;
        }
        
        // Check if status is valid
        $validStatuses = ['pending', 'approved', 'rejected'];
        if (!in_array($data['status'], $validStatuses)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status value']);
            return;
        }
        
        // Update the review status
        $stmt = $conn->prepare("UPDATE collegereviews SET status = ? WHERE r_id = ?");
        $stmt->bind_param('si', $data['status'], $data['review_id']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Review status updated successfully',
                'new_status' => $data['status']
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update review status: ' . $conn->error]);
        }
    } catch (Exception $e) {
        logError("Error in updateReviewStatus: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating review: ' . $e->getMessage()]);
    }
}

// Function to delete a review
function deleteReview($conn) {
    try {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Review ID is required']);
            return;
        }
        
        $reviewId = intval($_GET['id']);
        
        // Delete the review
        $stmt = $conn->prepare("DELETE FROM collegereviews WHERE r_id = ?");
        $stmt->bind_param('i', $reviewId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete review: ' . $conn->error]);
        }
    } catch (Exception $e) {
        logError("Error in deleteReview: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting review: ' . $e->getMessage()]);
    }
}
?> 