<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin

$host = "fdb1030.awardspace.net"; 
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch PDFs from teachers
$sql = "SELECT id, pdf_name, pdf_path, pdf_dl_path FROM pdf_resources WHERE stored_pdf = TRUE";
$result = $conn->query($sql);

if (!$result) {
    die(json_encode(["error" => "Failed to fetch teacher PDFs: " . $conn->error]));
}

$pdfs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdfs[] = $row;
    }
}

echo json_encode($pdfs);

$conn->close();
?>