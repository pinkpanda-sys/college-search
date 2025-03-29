<?php
// Simple test endpoint
header('Content-Type: application/json');

// Database connection
require_once '../config/database.php';

if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Sample data for testing
$colleges = [
    [
        'ranking' => 1,
        'name' => 'Test University',
        'location' => 'Test Location',
        'contact' => '+1234567890',
        'fees' => 250000,
        'maplink' => 'https://maps.google.com/?q=test'
    ],
    [
        'ranking' => 2,
        'name' => 'Another University',
        'location' => 'Another Location',
        'contact' => '+9876543210',
        'fees' => 300000,
        'maplink' => null
    ]
];

// Return sample data
echo json_encode($colleges);
?> 