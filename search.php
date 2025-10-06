<?php
require_once 'db.php';

// Check if the request is a search query
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
    
    // Search files in the database
    $files = searchFiles($keyword);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'files' => $files
    ]);
    exit;
}

// If not a valid request, return error
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
