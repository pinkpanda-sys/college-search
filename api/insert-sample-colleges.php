<?php
header('Content-Type: text/plain');
echo "Adding sample colleges to database...\n\n";

// Database connection
require_once '../config/database.php';

// Check if there are already colleges in the database
$result = $conn->query("SELECT COUNT(*) as count FROM colleges");
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo "You already have {$row['count']} colleges in your database.\n";
    echo "If you want to add more, please add them through the admin interface.\n";
    exit;
}

// Sample colleges data
$colleges = [
    [
        'ranking' => 1,
        'name' => 'Indian Institute of Technology, Bombay',
        'contact' => '+91 22 2576 7901',
        'fees' => 250000,
        'location' => 'Mumbai, Maharashtra',
        'maplink' => 'https://maps.google.com/?q=IIT+Bombay'
    ],
    [
        'ranking' => 2,
        'name' => 'Indian Institute of Technology, Delhi',
        'contact' => '+91 11 2659 1999',
        'fees' => 245000,
        'location' => 'New Delhi, Delhi',
        'maplink' => 'https://maps.google.com/?q=IIT+Delhi'
    ],
    [
        'ranking' => 3,
        'name' => 'Indian Institute of Technology, Madras',
        'contact' => '+91 44 2257 8101',
        'fees' => 240000,
        'location' => 'Chennai, Tamil Nadu',
        'maplink' => 'https://maps.google.com/?q=IIT+Madras'
    ],
    [
        'ranking' => 4,
        'name' => 'Indian Institute of Science',
        'contact' => '+91 80 2293 2228',
        'fees' => 230000,
        'location' => 'Bangalore, Karnataka',
        'maplink' => 'https://maps.google.com/?q=IISc+Bangalore'
    ],
    [
        'ranking' => 5,
        'name' => 'Indian Institute of Technology, Kanpur',
        'contact' => '+91 512 259 7578',
        'fees' => 235000,
        'location' => 'Kanpur, Uttar Pradesh',
        'maplink' => 'https://maps.google.com/?q=IIT+Kanpur'
    ]
];

// Check if colleges table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'colleges'");
if ($tableCheck->num_rows == 0) {
    echo "Colleges table does not exist. Creating table...\n";
    
    // Create colleges table
    $createTable = "CREATE TABLE colleges (
        ranking INT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        contact VARCHAR(100),
        fees DECIMAL(10,2),
        location TEXT NOT NULL,
        maplink VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTable)) {
        echo "Error creating colleges table: " . $conn->error . "\n";
        exit;
    }
    
    echo "Colleges table created successfully!\n";
}

// Insert sample colleges
$successCount = 0;
foreach ($colleges as $college) {
    $stmt = $conn->prepare("INSERT INTO colleges (ranking, name, contact, fees, location, maplink) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdss", 
        $college['ranking'], 
        $college['name'], 
        $college['contact'], 
        $college['fees'], 
        $college['location'], 
        $college['maplink']
    );
    
    if ($stmt->execute()) {
        $successCount++;
        echo "Added: {$college['name']}\n";
    } else {
        echo "Error adding {$college['name']}: " . $stmt->error . "\n";
    }
}

echo "\nAdded $successCount of " . count($colleges) . " colleges to the database.";
echo "\nYou can now go back to your admin panel to manage these colleges.";
?> 