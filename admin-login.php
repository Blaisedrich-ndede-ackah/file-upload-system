<?php
require_once 'db.php';
session_start();

// Check if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin-dashboard.php');
    exit;
}

// Check if login form was submitted
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Verify admin credentials
    $admin = verifyAdminLogin($username, $password);
    
    if ($admin) {
        // Set admin session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        
        // Redirect to admin dashboard
        header('Location: admin-dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - unknown site</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Admin Login</h1>
            <a href="index.php" class="back-link">Back to Home</a>
        </header>
        
        <main>
            <div class="login-container">
                <form method="POST" action="" class="login-form">
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="login-button">Login</button>
                </form>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> unknown site</p>
        </footer>
    </div>
</body>
</html>
