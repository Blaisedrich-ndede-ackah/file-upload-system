<?php
// Database configuration
$host = 'localhost';
$dbname = 'file_upload_system';
$username = 'root';
$password = '';

// Create database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
function createTables() {
    global $pdo;
    
    // Files table
    $pdo->exec("CREATE TABLE IF NOT EXISTS files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        original_filename VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_size INT NOT NULL,
        file_type VARCHAR(100) NOT NULL,
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
        download_count INT DEFAULT 0
    )");
    
    // Admin users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert default admin user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES ('admin', ?)");
        $stmt->execute([$hashedPassword]);
    }
}

// Call the function to create tables
createTables();

// Function to save file information to database
function saveFileToDatabase($originalFilename, $filename, $filePath, $fileSize, $fileType) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO files (original_filename, filename, file_path, file_size, file_type) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$originalFilename, $filename, $filePath, $fileSize, $fileType]);
    
    return $pdo->lastInsertId();
}

// Function to get all files
function getAllFiles($status = null) {
    global $pdo;
    
    $query = "SELECT * FROM files";
    if ($status) {
        $query .= " WHERE status = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to search files
function searchFiles($keyword) {
    global $pdo;
    
    $keyword = "%$keyword%";
    $stmt = $pdo->prepare("SELECT * FROM files WHERE original_filename LIKE ? AND status = 'approved'");
    $stmt->execute([$keyword]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get file by ID
function getFileById($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to update file status
function updateFileStatus($id, $status) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE files SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    return $stmt->rowCount() > 0;
}

// Function to increment download count
function incrementDownloadCount($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE files SET download_count = download_count + 1 WHERE id = ?");
    $stmt->execute([$id]);
}

// Function to verify admin login
function verifyAdminLogin($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}
