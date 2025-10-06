<?php
require_once 'db.php';

// Check if file ID is provided
if (isset($_GET['id'])) {
    $fileId = $_GET['id'];
    
    // Get file information from database
    $file = getFileById($fileId);
    
    // Check if file exists and is approved
    if ($file && $file['status'] === 'approved' && file_exists($file['file_path'])) {
        // Increment download count
        incrementDownloadCount($fileId);
        
        // Set headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file['file_type']);
        header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $file['file_size']);
        
        // Output file content
        readfile($file['file_path']);
        exit;
    }
}

// If file not found or not approved, redirect to index page
header('Location: index.php');
exit;
