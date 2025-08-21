<?php
$word = $_POST['word'];
$definition = $_POST['definition'];
$category = $_POST['category'];

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

//Update the user into the database
if (updateWord($worddetails)) {
    $response['success'] = "1";
    $response['message'] = "Word updated successfully!";
    echo json_encode($response);
} else {
    $response['success'] = "0";
    $response['message'] = "Word updating failed!";
    echo json_encode($response);
}

function updateWord($worddetails) {
    require './connect.php';
    $query = "UPDATE details SET definition=:definition, category=:category WHERE word=:word";
    $stmt = $pdo->prepare($query);
    return $stmt->execute($worddetails);
}

?>