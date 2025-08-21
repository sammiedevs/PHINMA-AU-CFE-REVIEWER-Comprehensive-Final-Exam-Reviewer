<?php
session_start(); // Start the session

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Create connection using mysqli (since you're using mysqli for the user check)
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => false, "msg" => "Database connection failed: " . $conn->connect_error]));
}

// Check if the user is logged in
if (!isset($_SESSION['useremail'])) {
    die(json_encode(["status" => false, "msg" => "User not logged in"]));
}

$useremail = $_SESSION['useremail'];

// Fetch user_id from the database
$stmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die(json_encode(["status" => false, "msg" => "User not found"]));
}

$user_id = $user['id'];

$json = file_get_contents('php://input');

// Debug: Log the raw input
file_put_contents('debug_input.log', $json . PHP_EOL, FILE_APPEND);

if (empty($json)) {
    die(json_encode([
        'status' => false,
        'msg' => 'No input data received',
        'debug' => [
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'Not set',
            'input_data' => $json
        ]
    ]));
}

// Decode JSON
$input = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode([
        'status' => false,
        'msg' => 'Invalid JSON format',
        'error' => json_last_error_msg(),
        'raw_data' => $json
    ]));
}

// Validate required fields
$required = ['id', 'term', 'definition'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        die(json_encode([
            'status' => false,
            'msg' => "Missing or empty field: $field",
            'received_data' => $input
        ]));
    }
}

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
$stmt->bind_param("s", $_SESSION['useremail']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die(json_encode([
        'status' => false,
        'msg' => 'User not found'
    ]));
}

$user_id = $user['id'];

// Verify flashcard belongs to user
$check = $conn->prepare("SELECT id FROM flashcards WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $input['id'], $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    die(json_encode([
        'status' => false,
        'msg' => 'Flashcard not found or access denied',
        'debug' => [
            'input_id' => $input['id'],
            'user_id' => $user_id
        ]
    ]));
}

// Update flashcard
$stmt = $conn->prepare("UPDATE flashcards SET term = ?, definition = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("ssii", $input['term'], $input['definition'], $input['id'], $user_id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => true,
        'msg' => 'Flashcard updated successfully',
        'updated_id' => $input['id']
    ]);
} else {
    die(json_encode([
        'status' => false,
        'msg' => 'Update failed',
        'error' => $stmt->error
    ]));
}

$conn->close();
?>