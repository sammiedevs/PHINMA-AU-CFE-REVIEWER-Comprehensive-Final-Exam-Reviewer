<?php
session_start(); // Start the session to access session variables

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Check if the user is logged in
session_start();
if (!isset($_SESSION['useremail'])) {
    die(json_encode(["error" => "User not logged in"]));
}

$useremail = $_SESSION['useremail'];

// Fetch user_id from the database
$stmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
$stmt->bind_param("s", $useremail); // Bind the parameter
$stmt->execute();
$result = $stmt->get_result(); // Get the result set
$user = $result->fetch_assoc(); // Fetch the associative array

if (!$user) {
    die(json_encode(["error" => "User not found"]));
}

$user_id = $user['id'];

// Fetch flashcards for the logged-in user
$query = "SELECT term, definition FROM flashcards WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id); // Use prepared statements to prevent SQL injection
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die(json_encode(["error" => "Query failed: " . $conn->error]));
}

$flashcards = array();
while ($row = $result->fetch_assoc()) {
    $flashcards[] = $row;
}

echo json_encode($flashcards);
?>