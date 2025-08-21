<?php
session_start(); // Start session to access user_id

$host = "fdb1030.awardspace.net"; 
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Create connection
$con = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get user_id from session
session_start();
if (!isset($_SESSION['useremail'])) {
    die(json_encode(["error" => "User not logged in"]));
}

$useremail = $_SESSION['useremail'];

// Fetch user_id from the database
$stmt = $con->prepare("SELECT id FROM users WHERE useremail = ?");
$stmt->bind_param("s", $useremail); // Bind the parameter
$stmt->execute();
$result = $stmt->get_result(); // Get the result set
$user = $result->fetch_assoc(); // Fetch the associative array

if (!$user) {
    die(json_encode(["error" => "User not found"]));
}

$user_id = $user['id'];

// Check if the required fields are present in the $_POST array
if (!isset($_POST['event_name']) || !isset($_POST['event_start_date']) || !isset($_POST['event_end_date'])) {
    $data = array(
        'status' => false,
        'msg' => 'All fields are required.'
    );
    echo json_encode($data);
    exit();
}

// Sanitize and validate input data
$event_name = mysqli_real_escape_string($con, $_POST['event_name']);
$event_start_date = mysqli_real_escape_string($con, $_POST['event_start_date']);
$event_end_date = mysqli_real_escape_string($con, $_POST['event_end_date']);

// Validate date format
if (!strtotime($event_start_date) || !strtotime($event_end_date)) {
    $data = array(
        'status' => false,
        'msg' => 'Invalid date format.'
    );
    echo json_encode($data);
    exit();
}

// Format dates for database
$event_start_date = date("Y-m-d", strtotime($event_start_date));
$event_end_date = date("Y-m-d", strtotime($event_end_date));

// Insert event into the database with user_id
$insert_query = "INSERT INTO `calendar_event_master` (`event_name`, `event_start_date`, `event_end_date`, `user_id`) 
                 VALUES ('$event_name', '$event_start_date', '$event_end_date', '$user_id')";

if (mysqli_query($con, $insert_query)) {
    $data = array(
        'status' => true,
        'msg' => 'Event added successfully!'
    );
} else {
    $data = array(
        'status' => false,
        'msg' => 'Sorry, Event not added. Error: ' . mysqli_error($con)
    );
}

echo json_encode($data);
?>