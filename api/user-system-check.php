<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>User System Check</h1>";

try {
    // Database connection
    require_once '../config/database.php';
    
    if (!isset($conn)) {
        throw new Exception("Database connection variable not set");
    }
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    $tableExists = ($result && $result->num_rows > 0);
    
    if ($tableExists) {
        echo "<p>✅ Users table exists</p>";
        
        // Count records
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        if ($result && $row = $result->fetch_assoc()) {
            echo "<p>User count: " . $row['count'] . "</p>";
            
            // If no users, suggest adding a default admin
            if ($row['count'] == 0) {
                echo "<p>⚠️ No users found in the database. You might want to add a default admin.</p>";
                echo "<form method='post'>";
                echo "<input type='hidden' name='create_admin' value='1'>";
                echo "<button type='submit'>Create Default Admin</button>";
                echo "</form>";
            }
        }
        
        // Show table structure
        $result = $conn->query("DESCRIBE users");
        if ($result) {
            echo "<h3>Users Table Structure</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // List first 10 users
            $result = $conn->query("SELECT user_id, username, email, user_type, created_at FROM users LIMIT 10");
            if ($result && $result->num_rows > 0) {
                echo "<h3>User List (first 10)</h3>";
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Type</th><th>Created</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['user_id'] . "</td>";
                    echo "<td>" . $row['username'] . "</td>";
                    echo "<td>" . $row['email'] . "</td>";
                    echo "<td>" . $row['user_type'] . "</td>";
                    echo "<td>" . $row['created_at'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    } else {
        echo "<p>❌ Users table does not exist</p>";
        echo "<p>Would you like to create it now? <a href='setup-users.php'>Create Users Table</a></p>";
    }
    
    // Check if the registration form exists
    $registerFilePath = __DIR__ . '/../login/register.html';
    if (file_exists($registerFilePath)) {
        echo "<p>✅ Registration page exists</p>";
    } else {
        echo "<p>❌ Registration page does not exist at expected location</p>";
    }
    
    // Check if the registration handler exists
    $handlerFilePath = __DIR__ . '/../handlers/register_handler.php';
    if (file_exists($handlerFilePath)) {
        echo "<p>✅ Registration handler exists</p>";
    } else {
        echo "<p>❌ Registration handler does not exist at expected location</p>";
    }
    
    // Create default admin if requested
    if (isset($_POST['create_admin']) && $tableExists) {
        $adminUsername = "admin";
        $adminEmail = "admin@example.com";
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $adminFullName = "Admin User";
        $adminType = "admin";
        
        // Check if admin already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $adminUsername, $adminEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<p>⚠️ Admin user already exists!</p>";
        } else {
            // Create admin user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $adminUsername, $adminEmail, $adminPassword, $adminFullName, $adminType);
            
            if ($stmt->execute()) {
                echo "<p>✅ Default admin user created successfully!</p>";
                echo "<p>Username: admin</p>";
                echo "<p>Password: admin123</p>";
            } else {
                echo "<p>❌ Error creating admin user: " . $stmt->error . "</p>";
            }
        }
    }
    
    echo "<h2>Links</h2>";
    echo "<ul>";
    echo "<li><a href='../login/register.html'>Go to Registration Page</a></li>";
    echo "<li><a href='../login/login.html'>Go to Login Page</a></li>";
    echo "<li><a href='setup-users.php'>Setup Users Table</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

// Handle the create admin form
?> 