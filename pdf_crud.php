<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

$teacher_id = $_SESSION['user_id'];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['pdf_id'])) {
            $stmt = $conn->prepare("SELECT pdf_id, pdf_name, pdf_path, pdf_dl_path FROM pdf_resources WHERE pdf_id = ? AND teacher_id = ?");
            $stmt->bind_param("ii", $_GET['pdf_id'], $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_assoc());
        } else {
            $stmt = $conn->prepare("SELECT pdf_id, pdf_name, pdf_dl_path FROM pdf_resources WHERE teacher_id = ?");
            $stmt->bind_param("i", $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (empty($data['pdf_name']) || empty($data['pdf_link'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Name and link are required']));
        }
        
        // Store both original and converted links
        $stmt = $conn->prepare("INSERT INTO pdf_resources (pdf_name, pdf_path, pdf_dl_path, teacher_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", 
            $data['pdf_name'],
            $data['pdf_link'], // Original user input
            $data['pdf_link'], // Will be converted in JS before saving
            $teacher_id
        );
        
        if (!$stmt->execute()) {
            http_response_code(500);
            die(json_encode(['error' => 'Database error: ' . $conn->error]));
        }
        
        echo json_encode([
            'success' => true,
            'pdf_id' => $conn->insert_id,
            'pdf_name' => $data['pdf_name'],
            'pdf_path' => $data['pdf_link'], // Original
            'pdf_dl_path' => $data['pdf_link'] // Converted
        ]);
        break;

    case 'PUT':
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate input
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        die(json_encode(['error' => 'Invalid JSON data']));
    }
    
    if (empty($data['pdf_id']) || empty($data['pdf_name']) || empty($data['pdf_link'])) {
        http_response_code(400);
        die(json_encode(['error' => 'All fields are required']));
    }
    
    // Convert link if needed (same as create)
    $convertedLink = $data['pdf_link'];
    if (strpos($data['pdf_link'], 'docs.google.com') !== false && strpos($data['pdf_link'], '/edit') !== false) {
        $docId = preg_match('/\/d\/([^\/]+)/', $data['pdf_link'], $matches) ? $matches[1] : '';
        $convertedLink = "https://docs.google.com/document/d/$docId/export?format=pdf";
    }
    
    // Update database
    $stmt = $conn->prepare("UPDATE pdf_resources SET pdf_name = ?, pdf_path = ?, pdf_dl_path = ? WHERE pdf_id = ? AND teacher_id = ?");
    $stmt->bind_param("sssii", 
        $data['pdf_name'],
        $data['pdf_link'],    // Original link
        $convertedLink,       // Converted link
        $data['pdf_id'],
        $teacher_id
    );
    
    if (!$stmt->execute()) {
        http_response_code(500);
        die(json_encode(['error' => 'Database error: ' . $conn->error]));
    }
    
    // Return updated record
    $stmt = $conn->prepare("SELECT pdf_id, pdf_name, pdf_path, pdf_dl_path FROM pdf_resources WHERE pdf_id = ?");
    $stmt->bind_param("i", $data['pdf_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode($result->fetch_assoc());
    break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['pdf_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'PDF ID is required']));
        }
        
        $stmt = $conn->prepare("DELETE FROM pdf_resources WHERE pdf_id = ? AND teacher_id = ?");
        $stmt->bind_param("ii", $data['pdf_id'], $teacher_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        die(json_encode(['error' => 'Method not allowed']));
}

$conn->close();
?>