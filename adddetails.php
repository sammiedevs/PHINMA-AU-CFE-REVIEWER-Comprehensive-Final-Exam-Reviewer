<?php
$word = $_POST['word'];
$definition = $_POST['definition'];
$category = $_POST['category'];

$response = array();

//Check if all fieds are given
if (empty($word) || empty($definition) || empty($category)) {
    $response['success'] = "0";
    $response['message'] = "Some fields are empty. Please try again!";
    echo json_encode($response);
    die;
}

$worddetails = array(
    'word' => $word,
    'definition' => $definition,
    'category' => $category
);

//Insert the user into the database
if (saveWord($worddetails)) {
    $response['success'] = "1";
    $response['message'] = "Word added successfully!";
    echo json_encode($response);
} else {
    $response['success'] = "0";
    $response['message'] = "Word adding failed. Please try again!";
    echo json_encode($response);
}

function saveWord($worddetails) {
    require './connect.php';
    $query = "INSERT INTO details (word, definition, category) VALUES "
            . "(:word, :definition, :category)";
    $stmt = $pdo->prepare($query);
    return $stmt->execute($worddetails);
}