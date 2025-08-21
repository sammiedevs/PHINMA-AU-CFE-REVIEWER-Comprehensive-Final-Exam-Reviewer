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

// Get user_id from session (you'll need to set this when user logs in)
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

// Fetch events from the database for specific user
$display_query = "SELECT event_id, event_name, event_start_date, event_end_date 
                 FROM calendar_event_master 
                 WHERE user_id = '$user_id'";
$results = mysqli_query($con, $display_query);

// Check if the query was successful
if (!$results) {
    $data = array(
        'status' => false,
        'msg' => 'Error executing query: ' . mysqli_error($con)
    );
    echo json_encode($data);
    exit();
}

$count = mysqli_num_rows($results);

if ($count > 0) {
    $data_arr = array();
    while ($data_row = mysqli_fetch_assoc($results)) {
        $data_arr[] = array(
            'id' => $data_row['event_id'],
            'title' => $data_row['event_name'],
            'start' => $data_row['event_start_date'],
            'end' => $data_row['event_end_date'],
            'color' => '#' . substr(uniqid(), -6) // Random color
        );
    }

    $data = array(
        'status' => true,
        'msg' => 'successfully!',
        'data' => $data_arr
    );
} else {
    $data = array(
        'status' => false,
        'msg' => 'No events found for this user!'
    );
}

echo json_encode($data);
?>