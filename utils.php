<?php
function createPdfCopy($originalPath) {
    $tempDir = 'temp_pdfs/'; // Directory to store temporary copies
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true); // Create the directory if it doesn't exist
    }

    $copyPath = $tempDir . uniqid('pdf_') . '.pdf'; // Generate a unique name for the copy
    if (copy($originalPath, $copyPath)) {
        return $copyPath; // Return the path to the copy
    } else {
        return null; // Return null if copying fails
    }
}
?>