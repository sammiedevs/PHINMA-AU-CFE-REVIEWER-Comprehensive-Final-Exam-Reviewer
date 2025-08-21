<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Start the session
session_start();

// Debugging: Log session data
error_log('Session Data: ' . print_r($_SESSION, true));

// Check if the user is logged in
if (!isset($_SESSION['useremail'])) {
    echo json_encode(["success" => "0", "message" => "User not logged in"]);
    exit;
}

$useremail = $_SESSION['useremail'];

// Database connection
$servername = "fdb1030.awardspace.net";  
$username = "4584890_ccr";
$password = "Sta12bucks.";
$dbname = "4584890_ccr";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log('Database connection successful'); // Debugging statement
} catch (PDOException $e) {
    echo json_encode(["success" => "0", "message" => "Connection failed: " . $e->getMessage()]);
    exit;
}

// Fetch user data
$stmt = $conn->prepare("SELECT username, useremail, userfullname, userphone FROM users WHERE useremail = ?");
$stmt->execute([$useremail]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Debugging: Log the query result
error_log('Query Result: ' . print_r($user, true));

if ($user) {
    echo json_encode(["success" => "1", "data" => $user]);
} else {
    echo json_encode(["success" => "0", "message" => "User not found"]);
}
?>