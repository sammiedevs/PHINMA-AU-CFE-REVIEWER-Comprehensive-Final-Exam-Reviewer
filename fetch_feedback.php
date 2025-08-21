<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests

// Database configuration
$host = "fdb1030.awardspace.net"; 
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Fetch feedback from the database, sorted by created_at in descending order
$query = 'SELECT * FROM feedback ORDER BY created_at DESC';
$result = $conn->query($query);

if ($result) {
    $feedback = [];
    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
    echo json_encode($feedback);
} else {
    echo json_encode(['error' => 'Failed to fetch feedback']);
}

$conn->close();
?>