<?php
header('Content-Type: application/json');

// Database connection details
$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Fetch distinct subjects from the exams table
$result = $conn->query("SELECT DISTINCT subject FROM exams");

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row['subject'];
}

echo json_encode($subjects);

$conn->close();
?>