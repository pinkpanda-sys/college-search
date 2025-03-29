<?php
header('Content-Type: text/plain');
echo "Starting database setup...\n\n";

// Database connection
require_once '../config/database.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully!\n";

// Check if colleges table exists
$result = $conn->query("SHOW TABLES LIKE 'colleges'");
if ($result->num_rows > 0) {
    echo "Colleges table already exists. Dropping it to recreate...\n";
    $conn->query("DROP TABLE colleges");
}

// Create colleges table with correct structure
$createColleges = "CREATE TABLE colleges (
    ranking INT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    contact VARCHAR(100) NULL,
    fees DECIMAL(10,2) NULL,
    location TEXT NOT NULL,
    maplink VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($createColleges)) {
    echo "Colleges table created successfully!\n";
} else {
    echo "Error creating colleges table: " . $conn->error . "\n";
}

// Insert a sample college
$insertSample = "INSERT INTO colleges (ranking, name, location) 
                 VALUES (1, 'Sample University', 'Sample Location')";

if ($conn->query($insertSample)) {
    echo "Sample college added successfully!\n";
} else {
    echo "Error adding sample college: " . $conn->error . "\n";
}

echo "\nDatabase setup completed.";
?> 