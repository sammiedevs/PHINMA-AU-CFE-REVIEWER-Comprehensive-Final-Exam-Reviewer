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
    // Fetch all subjects for the user
    $sql = "SELECT id, subject FROM reviewers WHERE user_id = '$user_id'";
    $result = $conn->query($sql);

    if (!$result) {
        die(json_encode(["error" => "Failed to fetch subjects: " . $conn->error]));
    }

    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row; // Add each subject to the array
    }

    // Use an associative array to filter out duplicate subjects
    $uniqueSubjects = [];
    foreach ($subjects as $subject) {
        $subjectName = $subject['subject'];
        if (!isset($uniqueSubjects[$subjectName])) {
            $uniqueSubjects[$subjectName] = $subject; // Store the first occurrence of each subject
        }
    }

    // Convert the associative array back to a regular array
    $data = array_values($uniqueSubjects);

    echo json_encode($data);
} elseif ($action == "add") {
    $subject = $conn->real_escape_string($_POST['subject']);

    $sql = "INSERT INTO reviewers (subject, user_id) VALUES ('$subject', '$user_id')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => "Subject added successfully!"]);
    } else {
        echo json_encode(["error" => "Failed to add subject"]);
    }
}elseif ($action == "update") {
    if (!isset($_POST['subject_id']) || !isset($_POST['subject_name'])) {
        echo json_encode(["error" => "Invalid input: Missing subject_id or subject_name"]);
        exit;
    }

    $id = intval($_POST['subject_id']); // Ensure it's an integer
    $newSubjectName = $conn->real_escape_string($_POST['subject_name']);

    // Fetch the current subject name using the provided ID
    $fetchStmt = $conn->prepare("SELECT subject FROM reviewers WHERE id = ? AND user_id = ?");
    $fetchStmt->bind_param("ii", $id, $user_id);
    $fetchStmt->execute();
    $fetchResult = $fetchStmt->get_result();

    if ($fetchResult->num_rows === 0) {
        echo json_encode(["error" => "Subject not found or you don't have permission to update"]);
        exit;
    }

    $currentSubject = $fetchResult->fetch_assoc()['subject'];

    // Update all rows with the same subject name
    $updateStmt = $conn->prepare("UPDATE reviewers SET subject = ? WHERE subject = ? AND user_id = ?");
    $updateStmt->bind_param("ssi", $newSubjectName, $currentSubject, $user_id);

    if ($updateStmt->execute()) {
        echo json_encode(["success" => "All matching subjects updated successfully!"]);
    } else {
        echo json_encode(["error" => "Failed to update subjects: " . $conn->error]);
    }

    $updateStmt->close();
        
} elseif ($action == "delete") {
    $id = $conn->real_escape_string($_POST['id']);
    $sql = "DELETE FROM reviewers WHERE id = '$id' AND user_id='$user_id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => "Reviewer deleted successfully!"]);
    } else {
        echo json_encode(["error" => "Failed to delete reviewer"]);
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