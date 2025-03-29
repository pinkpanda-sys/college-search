<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
require_once '../config/database.php';

try {
    echo "<h1>Setting up Users Table</h1>";
    
    // Check if the users table exists
    $tableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        $tableExists = true;
        echo "<p>✅ Users table already exists.</p>";
    }
    
    // Create the users table if it doesn't exist
    if (!$tableExists) {
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
            echo "<p>✅ Users table created successfully.</p>";
            
            // Add a default admin user
            $adminUsername = "admin";
            $adminEmail = "admin@example.com";
            $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
            $adminFullName = "Admin User";
            $adminType = "admin";
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $adminUsername, $adminEmail, $adminPassword, $adminFullName, $adminType);
            
            if ($stmt->execute()) {
                echo "<p>✅ Default admin user created successfully.</p>";
                echo "<p>Username: admin</p>";
                echo "<p>Email: admin@example.com</p>";
                echo "<p>Password: admin123</p>";
            } else {
                echo "<p>❌ Error creating default admin user: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p>❌ Error creating users table: " . $conn->error . "</p>";
        }
    }
    
    // Display user count
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result && $row = $result->fetch_assoc()) {
        echo "<p>Total users in database: " . $row['count'] . "</p>";
    }
    
    // Show table structure
    echo "<h2>Users Table Structure</h2>";
    $result = $conn->query("DESCRIBE users");
    if ($result) {
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show existing users
    $result = $conn->query("SELECT user_id, username, email, user_type, created_at FROM users");
    if ($result && $result->num_rows > 0) {
        echo "<h2>Existing Users</h2>";
        echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Email</th><th>Type</th><th>Created At</th></tr>";
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
    
    echo "<p><a href='../login/register.html'>Go to Registration Page</a></p>";
    echo "<p><a href='../login/login.html'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 