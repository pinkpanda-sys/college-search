<?php
// Basic headers and error settings
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Try-catch for better error handling
try {
    // Load database connection
    require_once '../config/database.php';
    
    // Check connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }
    
    // Check if table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'colleges'")->num_rows > 0;
    
    if (!$tableExists) {
        // Return empty array if table doesn't exist
        echo json_encode([]);
        exit;
    }
    
    // Perform a simple query
    $result = $conn->query("SELECT * FROM colleges ORDER BY ranking");
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    // Fetch all colleges
    $colleges = [];
    while ($row = $result->fetch_assoc()) {
        $colleges[] = $row;
    }
    
    // Return the data
    echo json_encode($colleges);
    
} catch (Exception $e) {
    // Log error to file if possible
    $logDir = __DIR__ . '/../logs';
    if (is_dir($logDir) && is_writable($logDir)) {
        file_put_contents(
            $logDir . '/api-error.log',
            date('[Y-m-d H:i:s]') . ' ' . $e->getMessage() . "\n",
            FILE_APPEND
        );
    }
    
    // Return error
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 