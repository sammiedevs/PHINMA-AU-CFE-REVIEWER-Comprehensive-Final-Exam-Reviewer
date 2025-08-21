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
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get PDF ID from the request
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    die(json_encode(["error" => "Invalid request data"]));
}

$pdfId = $data['id'];

if (!$pdfId) {
    echo json_encode(["error" => "PDF ID is required"]);
    exit;
}

// Fetch the original PDF path from the database
$sql = "SELECT pdf_path FROM pdf_resources WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $pdfId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["error" => "PDF not found"]);
    exit;
}

$originalPath = $row['pdf_path'];

// Return the original path (URL or local file path)
echo json_encode(["copyPath" => $originalPath]);

$stmt->close();
$conn->close();
?>