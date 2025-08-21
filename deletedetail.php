<?php
$word = $_POST['word'];

$response = array();

//Check if all fieds are given
if (empty($word)) {
    $response['success'] = "0";
    $response['message'] = "Some fields are empty. Please try again!";
    echo json_encode($response);
    die;
}

$worddetails = array(
    'word' => $word,
);

//Update the user into the database
if (deleteWord($worddetails)) {
    $response['success'] = "1";
    $response['message'] = "Word deleted successfully!";
    echo json_encode($response);
} else {
    $response['success'] = "0";
    $response['message'] = "Word deletion failed!";
    echo json_encode($response);
}

function deleteWord($worddetails) {
    require './connect.php';
    $query = "DELETE FROM details WHERE word=:word";
    $stmt = $pdo->prepare($query);
    return $stmt->execute($worddetails);
}

?>