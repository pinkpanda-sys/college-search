<?php
header('Content-Type: text/plain');
echo "Database Connection Test\n\n";

require_once '../config/database.php';

if (!isset($conn)) {
    echo "ERROR: Database connection variable not set!\n";
    exit;
}

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error . "\n";
    exit;
}

echo "Connected to database successfully!\n";

// Test creating the colleges table
echo "\nTesting colleges table:\n";

$tableExists = $conn->query("SHOW TABLES LIKE 'colleges'")->num_rows > 0;
if (!$tableExists) {
    echo "Colleges table does not exist. Creating it...\n";
    
    $createTable = "CREATE TABLE colleges (
        ranking INT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        contact VARCHAR(100) NULL,
        fees DECIMAL(10,2) NULL,
        location TEXT NOT NULL,
        maplink VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTable)) {
        echo "Created colleges table successfully!\n";
    } else {
        echo "Error creating colleges table: " . $conn->error . "\n";
        exit;
    }
} else {
    echo "Colleges table exists. Showing structure:\n";
    $result = $conn->query("DESCRIBE colleges");
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ") " . 
             ($row['Null'] === "YES" ? "NULL" : "NOT NULL") . 
             ($row['Key'] === "PRI" ? " [PRIMARY KEY]" : "") . "\n";
    }
}

// Test inserting a sample college
echo "\nTesting insert operation:\n";

try {
    // Add a sample college (if it doesn't exist)
    $checkQuery = "SELECT COUNT(*) as count FROM colleges WHERE ranking = 999";
    $result = $conn->query($checkQuery);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $stmt = $conn->prepare("INSERT INTO colleges (ranking, name, location) VALUES (?, ?, ?)");
        $ranking = 999;
        $name = "Test College";
        $location = "Test Location";
        $stmt->bind_param("iss", $ranking, $name, $location);
        
        if ($stmt->execute()) {
            echo "Successfully inserted test college!\n";
        } else {
            echo "Failed to insert test college: " . $stmt->error . "\n";
        }
    } else {
        echo "Test college already exists.\n";
    }
    
    // Retrieve the test college
    $stmt = $conn->prepare("SELECT * FROM colleges WHERE ranking = ?");
    $ranking = 999;
    $stmt->bind_param("i", $ranking);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $college = $result->fetch_assoc();
        echo "\nRetrieved test college:\n";
        foreach ($college as $key => $value) {
            echo "- $key: " . ($value === null ? "NULL" : $value) . "\n";
        }
    } else {
        echo "Could not retrieve test college.\n";
    }
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
}

echo "\nTest completed.";
?> 