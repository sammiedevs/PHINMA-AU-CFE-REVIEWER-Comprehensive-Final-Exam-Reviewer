<?php

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action == "view") {
    $sql = "SELECT * FROM reviewers WHERE user_id = '$user_id'";
    $result = $conn->query($sql);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
} elseif ($action == "update") {
    $id = $conn->real_escape_string($_POST['id']);
    $subject = $conn->real_escape_string($_POST['subject']);

    $sql = "UPDATE reviewers SET subject='$subject' WHERE id='$id' AND user_id='$user_id'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => "Subject updated successfully!"]);
    } else {
        echo json_encode(["error" => "Failed to update subject"]);
    }
} elseif ($action == "details") {
    $id = $conn->real_escape_string($_GET['id']);
    $sql = "SELECT * FROM reviewers WHERE id = '$id' AND user_id='$user_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Subject not found"]);
    }
}

$conn->close();
?>