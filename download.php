<?php
// Force HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Set proper headers
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=" . basename($_GET['file']));

// Security validations
$file = $_GET['file'] ?? '';
if (empty($file) || preg_match('/\.\.\//', $file)) {
    die("Invalid file request");
}

// Define your PDF directory
$pdfDir = __DIR__ . '/uploads/pdfs/';
$filePath = realpath($pdfDir . $file);

// Verify file exists and is within the allowed directory
if (!$filePath || !is_file($filePath) || strpos($filePath, realpath($pdfDir)) !== 0) {
    die("File not found");
}

// Serve the file
readfile($filePath);
exit();