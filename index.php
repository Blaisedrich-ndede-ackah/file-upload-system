<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>unknown site</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide-icons"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>unknown site</h1>
            <a href="admin-login.php" class="admin-link">Admin Login</a>
        </header>
        
        <main>
            <div class="upload-container">
                <div id="drop-area" class="drop-area">
                    <div class="drop-message">
                        <i data-lucide="upload-cloud" class="upload-icon"></i>
                        <h2>Drag & Drop Files Here</h2>
                        <p>or</p>
                        <label for="fileInput" class="file-input-label">Browse Files</label>
                        <input type="file" id="fileInput" multiple class="file-input">
                    </div>
                </div>
                
                <div id="upload-progress" class="upload-progress hidden">
                    <div class="progress-bar">
                        <div id="progress-fill" class="progress-fill"></div>
                    </div>
                    <p id="progress-text">Uploading: 0%</p>
                </div>
                
                <!-- <div id="upload-list" class="upload-list">
                    <h3>Uploaded Files</h3>
                    <div id="file-list" class="file-list">
                        <!-- Files will be listed here -->
                    </div>
                </div> 
            </div>
            
            <!-- <div class="search-container">
                <h3>Search Files</h3>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search by filename...">
                    <button id="searchButton">
                        <i data-lucide="search" class="search-icon"></i>
                    </button>
                </div>
                <div id="search-results" class="search-results">
                    <!-- Search results will appear here -->
                </div>
            </div> 
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> unknown site</p>
        </footer>
    </div>
    
    <script src="assets/js/script.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
