<?php
header('Content-Type: text/plain');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campascompass');  // Make sure this matches your config

echo "Attempting to create database...\n";

// Connect without database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully!\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

$conn->close();

echo "\nNow you can visit setup-database.php to create tables";
?> 