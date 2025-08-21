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

// Check if the event_id is provided
if (!isset($_POST['event_id'])) {
    $data = array(
        'status' => false,
        'msg' => 'Event ID is required.'
    );
    echo json_encode($data);
    exit();
}

// Sanitize the event_id
$event_id = mysqli_real_escape_string($con, $_POST['event_id']);

// Delete the event from the database only if it belongs to the user
$delete_query = "DELETE FROM `calendar_event_master` 
                 WHERE `event_id` = '$event_id' AND `user_id` = '$user_id'";

if (mysqli_query($con, $delete_query)) {
    if (mysqli_affected_rows($con) > 0) {
        $data = array(
            'status' => true,
            'msg' => 'Event deleted successfully!'
        );
    } else {
        $data = array(
            'status' => false,
            'msg' => 'Event not found or you don\'t have permission to delete it'
        );
    }
} else {
    $data = array(
        'status' => false,
        'msg' => 'Sorry, Event not deleted. Error: ' . mysqli_error($con)
    );
}

echo json_encode($data);
?>