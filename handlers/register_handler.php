<?php
session_start();
ini_set('display_errors', 0); // Disable error display in production
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
            $logDir . '/user-registration-error.log',
            date('[Y-m-d H:i:s]') . ' ' . $message . "\n",
            FILE_APPEND
        );
    }
}

try {
    // Log that we're starting the registration process
    logError("Registration process started");
    
    // Log the POST data for debugging (remove sensitive data)
    $safePostData = $_POST;
    if (isset($safePostData['password'])) {
        $safePostData['password'] = '****HIDDEN****';
    }
    if (isset($safePostData['confirmPassword'])) {
        $safePostData['confirmPassword'] = '****HIDDEN****';
    }
    logError("Form submitted with data: " . print_r($safePostData, true));
    
    // Database connection
    require_once '../config/database.php';
    
    // Check connection
    if (!isset($conn) || $conn->connect_error) {
        logError("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
        throw new Exception("Database connection failed. Please try again later.");
    }
    
    logError("Database connection successful");
    
    // Check if users table exists (silently fail if it doesn't)
    $tableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    $tableExists = ($result && $result->num_rows > 0);
    
    if (!$tableExists) {
        // Create users table if it doesn't exist
        $sql = "CREATE TABLE users (
            user_id INT(11) NOT NULL AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            user_type ENUM('student', 'admin') DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id)
        )";
        
        if ($conn->query($sql) === TRUE) {
            logError("Users table created automatically");
            $tableExists = true;
        } else {
            logError("Failed to create users table: " . $conn->error);
            throw new Exception("Database setup issue. Please contact administrator.");
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate input fields
        $required_fields = ['username', 'email', 'password', 'full_name'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                logError("Missing required field: " . $field);
                $_SESSION['error'] = "All fields are required!";
                header("Location: ../login/register.html");
                exit();
            }
        }
        
        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            logError("Invalid email format: " . $_POST['email']);
            $_SESSION['error'] = "Invalid email format!";
            header("Location: ../login/register.html");
            exit();
        }
        
        // Validate password length
        if (strlen($_POST['password']) < 6) {
            logError("Password too short");
            $_SESSION['error'] = "Password must be at least 6 characters long!";
            header("Location: ../login/register.html");
            exit();
        }
        
        // Check password confirmation
        if (isset($_POST['confirmPassword']) && $_POST['password'] !== $_POST['confirmPassword']) {
            logError("Passwords don't match");
            $_SESSION['error'] = "Passwords don't match!";
            header("Location: ../login/register.html");
            exit();
        }
        
        // Use prepared statements to prevent SQL injection
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = $_POST['full_name'];
        $admin_code = isset($_POST['admin_code']) ? $_POST['admin_code'] : '';
        
        logError("Processing registration for user: " . $username);
        
        // Check if email or username already exists using prepared statement
        $check_stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        if (!$check_stmt) {
            logError("Prepare statement failed: " . $conn->error);
            $_SESSION['error'] = "Database error. Please try again later.";
            header("Location: ../login/register.html");
            exit();
        }
        
        $check_stmt->bind_param("ss", $email, $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            logError("Email or username already exists");
            $_SESSION['error'] = "Email or username already exists!";
            header("Location: ../login/register.html");
            exit();
        }
        
        // Determine user type based on admin code
        $user_type = 'student';
        if ($admin_code === 'ADMIN123') { // You can change this code
            $user_type = 'admin';
            logError("Admin code provided - setting user as admin");
        }
        
        // Insert new user using prepared statement
        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, ?)");
        if (!$insert_stmt) {
            logError("Prepare statement for insert failed: " . $conn->error);
            $_SESSION['error'] = "Database error. Please try again later.";
            header("Location: ../login/register.html");
            exit();
        }
        
        $insert_stmt->bind_param("sssss", $username, $email, $password, $full_name, $user_type);
        
        if ($insert_stmt->execute()) {
            logError("User registered successfully: " . $username);
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: ../login/login.html");
            exit();
        } else {
            logError("Registration failed: " . $insert_stmt->error);
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("Location: ../login/register.html");
            exit();
        }
    } else {
        // If not a POST request
        logError("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        $_SESSION['error'] = "Invalid request method!";
        header("Location: ../login/register.html");
        exit();
    }
} catch (Exception $e) {
    logError("Registration exception: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred during registration. Please try again later.";
    header("Location: ../login/register.html");
    exit();
}
?>
