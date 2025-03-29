<?php
session_start();
ini_set('display_errors', 0); // Set to 1 for debugging
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
            $logDir . '/login-error.log',
            date('[Y-m-d H:i:s]') . ' ' . $message . "\n",
            FILE_APPEND
        );
    }
}

try {
    require_once '../config/database.php';
    
    // Check connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Check if users table exists
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        $tableExists = ($result && $result->num_rows > 0);
        
        if (!$tableExists) {
            $_SESSION['error'] = "User database not set up. Please contact administrator.";
            logError("Users table does not exist");
            header("Location: ../login/login.html");
            exit();
        }
        
        // Validate input
        if (!isset($_POST['email']) || empty($_POST['email']) || !isset($_POST['password']) || empty($_POST['password'])) {
            $_SESSION['error'] = "Email and password are required!";
            logError("Missing email or password");
            header("Location: ../login/login.html");
            exit();
        }
        
        // Use prepared statements for security
        $email = $_POST['email'];
        $password = $_POST['password'];
        logError("Login attempt for email: " . $email);
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Success - set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                
                logError("Login successful for user: " . $user['username']);
                
                // Redirect based on user type
                if ($user['user_type'] == 'admin') {
                    header("Location: ../admin/admin-panel.html");
                } else {
                    header("Location: ../users/user-panel.html");
                }
                exit();
            } else {
                // Password doesn't match
                logError("Invalid password for email: " . $email);
                $_SESSION['error'] = "Invalid email or password!";
            }
        } else {
            // Email not found
            logError("Email not found: " . $email);
            $_SESSION['error'] = "Invalid email or password!";
        }
        
        header("Location: ../login/login.html");
        exit();
    } else {
        // Not a POST request
        $_SESSION['error'] = "Invalid request method!";
        header("Location: ../login/login.html");
        exit();
    }
} catch (Exception $e) {
    logError("Login exception: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred during login. Please try again later.";
    header("Location: ../login/login.html");
    exit();
}
?>
