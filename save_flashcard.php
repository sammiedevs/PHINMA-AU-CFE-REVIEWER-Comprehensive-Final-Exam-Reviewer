<?php
session_start();

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => false, "msg" => "Database connection failed: " . $conn->connect_error]));
}

// Check if the user is logged in
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

// Get the term and definition from the POST request
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['term']) || !isset($data['definition'])) {
    die(json_encode(["status" => false, "msg" => "Term and definition are required"]));
}

$term = $data['term'];
$definition = $data['definition'];

// Insert the flashcard into the database
$query = "INSERT INTO flashcards (term, definition, user_id) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $term, $definition, $user_id);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "msg" => "Flashcard saved successfully!"]);
} else {
    echo json_encode(["status" => false, "msg" => "Error saving flashcard: " . $stmt->error]);
}
?>