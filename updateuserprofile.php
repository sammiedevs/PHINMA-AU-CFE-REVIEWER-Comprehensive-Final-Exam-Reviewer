<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Include the database connection
require './connect.php';

// Initialize response
$response = array();

// Read POST data (handle JSON or form-data)
$inputData = json_decode(file_get_contents("php://input"), true);
if ($inputData) {
    $_POST = $inputData;
}

// Debugging: Log received POST data
error_log("Received POST Data: " . print_r($_POST, true));

// Validate required fields
$requiredFields = ['useremail', 'username', 'userfullname', 'userphone'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field])) {
        $response['success'] = "0";
        $response['message'] = "Some fields are missing.";
        echo json_encode($response);
        die();
    }
}

// Retrieve and validate data
$useremail = trim($_POST['useremail']);
$username = trim($_POST['username']);
$userfullname = trim($_POST['userfullname']);
$userphone = trim($_POST['userphone']);

// Check for empty fields
if (empty($useremail) || empty($username) || empty($userfullname) || empty($userphone)) {
    $response['success'] = "0";
    $response['message'] = "Some fields are empty.";
    echo json_encode($response);
    die();
}

// Validate email format
if (!filter_var($useremail, FILTER_VALIDATE_EMAIL)) {
    $response['success'] = "0";
    $response['message'] = "Invalid email format.";
    echo json_encode($response);
    die();
}

// Validate phone number (basic validation)
if (!preg_match("/^[0-9]{10,15}$/", $userphone)) {
    $response['success'] = "0";
    $response['message'] = "Invalid phone number.";
    echo json_encode($response);
    die();
}

// Ensure $pdo is correctly initialized
if (!isset($pdo)) {
    $response['success'] = "0";
    $response['message'] = "Database connection is missing!";
    echo json_encode($response);
    die();
}

// Update the user profile in the database
try {
    $query = "UPDATE users SET 
              username = :username, 
              userfullname = :userfullname, 
              userphone = :userphone 
              WHERE useremail = :useremail";
    $stmt = $pdo->prepare($query);

    // Debugging: Log the query and parameters
    error_log("Executing query: " . $query);
    error_log("With parameters: " . print_r([
        ':username' => $username,
        ':userfullname' => $userfullname,
        ':userphone' => $userphone,
        ':useremail' => $useremail
    ], true));

    $success = $stmt->execute([
        ':username' => $username,
        ':userfullname' => $userfullname,
        ':userphone' => $userphone,
        ':useremail' => $useremail
    ]);

    if ($success && $stmt->rowCount() > 0) {
        $response['success'] = "1";
        $response['message'] = "User profile updated successfully!";
    } else {
        $response['success'] = "0";
        $response['message'] = "User profile update failed!";
    }
} catch (PDOException $e) {
    $response['success'] = "0";
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
?>