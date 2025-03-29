<?php
header('Content-Type: text/html');
ini_set('display_errors', 0);

echo "<h1>Setup Helper</h1>";
echo "<p>This helper is designed to set up your system without triggering security alarms.</p>";

try {
    // Check if database.php exists and has required content
    $configFile = '../config/database.php';
    if (!file_exists($configFile)) {
        echo "<p style='color: red;'>Error: database.php file is missing!</p>";
        echo "<p>Please make sure you have a config/database.php file with your database credentials.</p>";
        echo "<code>
        &lt;?php
        // Database configuration
        define('DB_SERVER', 'your_server');
        define('DB_USERNAME', 'your_username');
        define('DB_PASSWORD', 'your_password');
        define('DB_NAME', 'your_database');
        
        // Create connection
        \$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        // Check connection
        if (\$conn->connect_error) {
            die('Connection failed: ' . \$conn->connect_error);
        }
        ?&gt;
        </code>";
        exit();
    }
    
    // Check if register.html exists
    $registerFile = '../login/register.html';
    $registerFilePHP = '../login/register.php';
    
    if (file_exists($registerFile) && !file_exists($registerFilePHP)) {
        echo "<p style='color: orange;'>Warning: register.html exists but register.php doesn't.</p>";
        echo "<p>Consider renaming register.html to register.php to enable PHP processing.</p>";
        echo "<p><a href='javascript:void(0)' onclick=\"alert('To rename the file, use your FTP client or hosting control panel file manager to rename login/register.html to login/register.php')\">Learn how to rename</a></p>";
    }
    
    // Check if login.html exists
    $loginFile = '../login/login.html';
    $loginFilePHP = '../login/login.php';
    
    if (file_exists($loginFile) && !file_exists($loginFilePHP)) {
        echo "<p style='color: orange;'>Warning: login.html exists but login.php doesn't.</p>";
        echo "<p>Consider renaming login.html to login.php to enable PHP processing.</p>";
        echo "<p><a href='javascript:void(0)' onclick=\"alert('To rename the file, use your FTP client or hosting control panel file manager to rename login/login.html to login/login.php')\">Learn how to rename</a></p>";
    }
    
    // Check if .htaccess exists and has required content for running PHP in HTML files
    $htaccessFile = '../.htaccess';
    $htaccessNeeded = false;
    
    if (file_exists($htaccessFile)) {
        $htaccessContent = file_get_contents($htaccessFile);
        if (strpos($htaccessContent, 'AddType application/x-httpd-php .html') === false) {
            $htaccessNeeded = true;
        }
    } else {
        $htaccessNeeded = true;
    }
    
    if ($htaccessNeeded) {
        echo "<p style='color: orange;'>Warning: .htaccess file doesn't have PHP processing for HTML files.</p>";
        echo "<p>You should either:</p>";
        echo "<ul>";
        echo "<li>Rename your HTML files to .php (recommended), or</li>";
        echo "<li>Add this line to your .htaccess file: <code>AddType application/x-httpd-php .html</code></li>";
        echo "</ul>";
    }
    
    // Test database connection without details
    require_once '../config/database.php';
    
    if (!isset($conn) || $conn->connect_error) {
        echo "<p style='color: red;'>Error: Database connection failed!</p>";
        echo "<p>Please check your database connection details in config/database.php.</p>";
    } else {
        echo "<p style='color: green;'>Database connection successful!</p>";
        
        // Check if tables exist without showing details
        $tables = ['users'];
        $missingTables = [];
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if (!$result || $result->num_rows == 0) {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            echo "<p style='color: green;'>All required tables exist!</p>";
        } else {
            echo "<p style='color: orange;'>Missing tables: " . implode(', ', $missingTables) . "</p>";
            echo "<p>Don't worry - these will be created automatically when needed.</p>";
        }
    }
    
    echo "<h2>Links</h2>";
    echo "<ul>";
    echo "<li><a href='../login/register.html'>Go to Registration Page (HTML)</a></li>";
    if (file_exists($registerFilePHP)) {
        echo "<li><a href='../login/register.php'>Go to Registration Page (PHP)</a></li>";
    }
    echo "<li><a href='../login/login.html'>Go to Login Page (HTML)</a></li>";
    if (file_exists($loginFilePHP)) {
        echo "<li><a href='../login/login.php'>Go to Login Page (PHP)</a></li>";
    }
    echo "</ul>";
    
    echo "<h2>What to do next</h2>";
    echo "<ol>";
    echo "<li>Rename your .html files to .php for forms that need PHP processing.</li>";
    echo "<li>Try registering a new user to test the system.</li>";
    echo "<li>Check the logs directory for any error logs if issues occur.</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 