<?php
require_once 'db.php';

// Check if the request is a file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create uploads directory if it doesn't exist
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $response = ['success' => false, 'message' => '', 'files' => []];
    
    // Check if files were uploaded
    if (isset($_FILES['files'])) {
        $files = $_FILES['files'];
        
        // Loop through each uploaded file
        for ($i = 0; $i < count($files['name']); $i++) {
            $fileName = $files['name'][$i];
            $fileTmpName = $files['tmp_name'][$i];
            $fileSize = $files['size'][$i];
            $fileError = $files['error'][$i];
            $fileType = $files['type'][$i];
            
            // Check for upload errors
            if ($fileError === 0) {
                // Validate file size (max 50MB)
                $maxSize = 50 * 1024 * 1024; // 50MB in bytes
                if ($fileSize > $maxSize) {
                    $response['message'] .= "File $fileName exceeds the maximum size limit (50MB). ";
                    continue;
                }
                
                // Validate file type (optional - can be adjusted based on requirements)
                $allowedTypes = [
                    'image/', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument',
                    'text/', 'application/zip', 'application/x-rar-compressed', 'audio/', 'video/'
                ];
                
                $isValidType = false;
                foreach ($allowedTypes as $type) {
                    if (strpos($fileType, $type) === 0) {
                        $isValidType = true;
                        break;
                    }
                }
                
                if (!$isValidType && $fileType !== '') {
                    $response['message'] .= "File $fileName has an invalid file type. ";
                    continue;
                }
                
                // Generate a unique filename
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = uniqid() . '.' . $fileExtension;
                $destination = $uploadDir . $newFileName;
                
                // Move the uploaded file to the destination
                if (move_uploaded_file($fileTmpName, $destination)) {
                    // Save file information to database
                    $fileId = saveFileToDatabase($fileName, $newFileName, $destination, $fileSize, $fileType);
                    
                    // Add file information to response
                    $response['files'][] = [
                        'id' => $fileId,
                        'name' => $fileName,
                        'size' => $fileSize,
                        'type' => $fileType,
                        'path' => $destination
                    ];
                } else {
                    $response['message'] .= "Failed to move uploaded file: $fileName. ";
                }
            } else {
                $response['message'] .= "Error uploading file: $fileName. Error code: $fileError. ";
            }
        }
        
        if (count($response['files']) > 0) {
            $response['success'] = true;
            $response['message'] = 'Files uploaded successfully.';
        } else if (empty($response['message'])) {
            $response['message'] = 'No valid files were uploaded.';
        }
    } else {
        $response['message'] = 'No files were uploaded.';
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
