<?php
header('Content-Type: application/json');

// Database connection
require_once '../config/database.php';

// Initialize stats array
$stats = [
    'colleges' => 0,
    'users' => 0,
    'reviews' => 0,
    'courses' => 0
];

// Get college count
$query = "SELECT COUNT(*) as count FROM colleges";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['colleges'] = $row['count'];
}

// Get user count
$query = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['users'] = $row['count'];
}

// Get review count
$query = "SELECT COUNT(*) as count FROM reviews";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['reviews'] = $row['count'];
}

// Get course count
$query = "SELECT COUNT(*) as count FROM courses";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['courses'] = $row['count'];
}

// Format numbers with commas for thousands
$stats['users'] = number_format($stats['users']);
$stats['reviews'] = number_format($stats['reviews']);
$stats['courses'] = number_format($stats['courses']);

// Return the stats as JSON
echo json_encode($stats);
?> 