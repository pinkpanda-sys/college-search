<?php
// Basic configuration
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Ensure logs directory exists
$logDir = __DIR__ . '/../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

// Setup logging
ini_set('log_errors', 1);
ini_set('error_log', $logDir . '/php-errors.log');

// Log all API requests
error_log("API Request: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

// Set content type
header('Content-Type: application/json');

try {
    // Load database connection
    require_once '../config/database.php';
    
    // Validate connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    // Handle different HTTP methods
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // GET request - Read operation
            if (isset($_GET['id'])) {
                // Get specific college
                $id = intval($_GET['id']);
                $stmt = $conn->prepare("SELECT * FROM colleges WHERE rankings = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $college = $result->fetch_assoc();
                
                if ($college) {
                    echo json_encode($college);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'College not found']);
                }
            } else {
                // Get all colleges
                $result = $conn->query("SELECT * FROM colleges ORDER BY rankings");
                $colleges = [];
                while ($row = $result->fetch_assoc()) {
                    $colleges[] = $row;
                }
                echo json_encode($colleges);
            }
            break;
            
        case 'POST':
            // POST request - Create operation
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            // Validate required fields
            if (!isset($data['rankings']) || !isset($data['name']) || !isset($data['location'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
            
            // Check for duplicate rankings
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM colleges WHERE rankings = ?");
            $rankings = intval($data['rankings']);
            $stmt->bind_param('i', $rankings);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'A college with this rankings already exists']);
                exit;
            }
            
            // Prepare data for insertion
            $name = $data['name'];
            $location = $data['location'];
            $contact = isset($data['contact']) ? $data['contact'] : null;
            $fees = isset($data['fees']) && is_numeric($data['fees']) ? $data['fees'] : null;
            $maplink = isset($data['maplink']) ? $data['maplink'] : null;
            
            // Insert the college
            $stmt = $conn->prepare("INSERT INTO colleges (rankings, name, contact, fees, location, maplink) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('issdss', $rankings, $name, $contact, $fees, $location, $maplink);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'College added successfully']);
            } else {
                throw new Exception("Insert failed: " . $stmt->error);
            }
            break;
            
        case 'PUT':
            // PUT request - Update operation
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            // Validate required fields
            if (!isset($data['rankings']) || !isset($data['name']) || !isset($data['location'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
            
            // Prepare data for update
            $rankings = intval($data['rankings']);
            $name = $data['name'];
            $location = $data['location'];
            $contact = isset($data['contact']) ? $data['contact'] : null;
            $fees = isset($data['fees']) && is_numeric($data['fees']) ? $data['fees'] : null;
            $maplink = isset($data['maplink']) ? $data['maplink'] : null;
            
            // Update the college
            $stmt = $conn->prepare("UPDATE colleges SET name = ?, contact = ?, fees = ?, location = ?, maplink = ? WHERE rankings = ?");
            $stmt->bind_param('ssdssi', $name, $contact, $fees, $location, $maplink, $rankings);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'College updated successfully']);
            } else {
                throw new Exception("Update failed: " . $stmt->error);
            }
            break;
            
        case 'DELETE':
            // DELETE request - Delete operation
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing college ID']);
                exit;
            }
            
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("DELETE FROM colleges WHERE rankings = ?");
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'College deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'College not found or could not be deleted']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    // Log the error
    error_log("API Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 