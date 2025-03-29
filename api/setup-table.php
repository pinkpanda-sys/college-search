<?php
header('Content-Type: text/plain');
echo "Setting up colleges table\n\n";

try {
    // Load database connection
    require_once '../config/database.php';
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Connection not established"));
    }
    
    // Check if table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'colleges'")->num_rows > 0;
    
    // Drop table if it exists (optional - comment out if you want to keep data)
    if ($tableExists) {
        echo "Colleges table already exists. Recreating...\n";
        $conn->query("DROP TABLE colleges");
    }
    
    // Create colleges table
    $sql = "CREATE TABLE colleges (
        ranking INT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        contact VARCHAR(100) NULL,
        fees DECIMAL(10,2) NULL,
        location TEXT NOT NULL,
        maplink VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "Colleges table created successfully!\n";
    } else {
        throw new Exception("Table creation failed: " . $conn->error);
    }
    
    // Insert sample data
    echo "\nInserting sample colleges...\n";
    
    $sampleColleges = [
        [1, "Indian Institute of Technology, Bombay", "+91 22 2576 7901", 250000, "Mumbai, Maharashtra", "https://maps.google.com/?q=IIT+Bombay"],
        [2, "Indian Institute of Technology, Delhi", "+91 11 2659 1999", 245000, "New Delhi, Delhi", "https://maps.google.com/?q=IIT+Delhi"],
        [3, "Indian Institute of Science", "+91 80 2293 2228", 230000, "Bangalore, Karnataka", "https://maps.google.com/?q=IISc+Bangalore"],
        [4, "Indian Institute of Technology, Madras", "+91 44 2257 8101", 240000, "Chennai, Tamil Nadu", "https://maps.google.com/?q=IIT+Madras"],
        [5, "Indian Institute of Technology, Kanpur", "+91 512 259 7578", 235000, "Kanpur, Uttar Pradesh", "https://maps.google.com/?q=IIT+Kanpur"]
    ];
    
    $stmt = $conn->prepare("INSERT INTO colleges (ranking, name, contact, fees, location, maplink) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($sampleColleges as $college) {
        $stmt->bind_param("issdss", $college[0], $college[1], $college[2], $college[3], $college[4], $college[5]);
        if ($stmt->execute()) {
            echo "Added: " . $college[1] . "\n";
        } else {
            echo "Failed to add " . $college[1] . ": " . $stmt->error . "\n";
        }
    }
    
    echo "\nSetup completed successfully!";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?> 