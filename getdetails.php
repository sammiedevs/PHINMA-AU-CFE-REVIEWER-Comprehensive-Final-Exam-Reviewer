<?php
$response = array();

$success = getDetails();

if (!empty($success)) {
   $response['success'] = "1";
   $response['message'] = "Details fetched successfully!";
   $response['details'] = $success;
   echo json_encode($response);
} else {
   $response['success'] = "0";
   $response['message'] = "Failed to fetch details!";
   echo json_encode($response);
}

function getDetails() {
   require './connect.php';
   $array = array();
   $stmt = $pdo->prepare("SELECT * FROM details");
   $stmt->execute();
   $array = $stmt->fetchAll(PDO::FETCH_ASSOC);
   $stmt = null;
   return $array;
}
?>