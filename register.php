<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'file_upload_system';
$db_user = 'root';       // Replace with your database username
$db_pass = ''; 


// Connect to database
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header("Location: register.html?status=error&message=Database connection failed");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate input
    if (empty($username) || empty($password)) {
        header("Location: register.html?status=error&message=Please fill in all fields");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert into database
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->execute();

        header("Location: register.html?status=success&message=User registered successfully");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate username
            header("Location: register.html?status=error&message=Username already exists");
        } else {
            header("Location: register.html?status=error&message=Registration failed");
        }
        exit();
    }
}

// If not a POST request, redirect to form
header("Location: register.html");
exit();
?>