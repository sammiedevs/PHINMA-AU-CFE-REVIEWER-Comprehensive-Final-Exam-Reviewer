<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['useremail'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
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
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
    exit;
}

// Fetch user data
$stmt = $conn->prepare("SELECT id, username, useremail, userfullname, userphone FROM users WHERE useremail = ?");
$stmt->execute([$useremail]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(["success" => true, "userfullname" => $user['userfullname'], "userid" => $user['id']]);
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}
?>