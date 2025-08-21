<?php
$useremail = $_POST['useremail'];
$response = array();

//Check if all fieds are given
if (empty($useremail)) {
    $response['success'] = "0";
    $response['message'] = "Email field is empty. Please try again!";
    echo json_encode($response);
    die;
}

$userdetails = array(
    'useremail' => $useremail
);

//Search the user from the database
$success = searchUserDetails($userdetails);

if (!empty($success)) {
    $response['success'] = "1";
    $response['message'] = "User details retrieved successfully!";
    $response['details'] = $success;
    echo json_encode($response);
} else {
    $response['success'] = "0";
    $response['message'] = "Retrieving user details failed. Please try again!";
    echo json_encode($response);
}

function searchUserDetails($userdetails) {
    require './connect.php';
    $array = array();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE useremail = :useremail");
    $stmt->execute($userdetails);
    $array = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = null;
    return $array;
}