<?php
require_once 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit;
}

// Handle file status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $fileId = $_POST['file_id'] ?? 0;
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        updateFileStatus($fileId, 'approved');
    } elseif ($action === 'reject') {
        updateFileStatus($fileId, 'rejected');
    } elseif ($action === 'delete') {
        $file = getFileById($fileId);
        if ($file && file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        // Delete file record from database
        $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$fileId]);
    }
    
    // Redirect to refresh the page
    header('Location: admin-dashboard.php');
    exit;
}

// Get filter parameter
$filter = $_GET['filter'] ?? 'all';

// Get files based on filter
$files = [];
if ($filter === 'pending') {
    $files = getAllFiles('pending');
} elseif ($filter === 'approved') {
    $files = getAllFiles('approved');
} elseif ($filter === 'rejected') {
    $files = getAllFiles('rejected');
} else {
    $files = getAllFiles();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - unknown site</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide-icons"></script>
</head>
<body>
    <div class="container">
        <header class="admin-header">
            <h1>Admin Dashboard</h1>
            <div class="admin-actions">
                <span class="admin-username">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="?logout=1" class="logout-button">Logout</a>
                <a href="index.php" class="back-link">View Site</a>
            </div>
        </header>
        
        <main>
            <div class="admin-controls">
                <h2>File Management</h2>
                
                <div class="filter-controls">
                    <a href="?filter=all" class="filter-button <?php echo $filter === 'all' ? 'active' : ''; ?>">All Files</a>
                    <a href="?filter=pending" class="filter-button <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="?filter=approved" class="filter-button <?php echo $filter === 'approved' ? 'active' : ''; ?>">Approved</a>
                    <a href="?filter=rejected" class="filter-button <?php echo $filter === 'rejected' ? 'active' : ''; ?>">Rejected</a>
                </div>
                
                <div class="search-box admin-search">
                    <input type="text" id="adminSearchInput" placeholder="Search files...">
                    <button id="adminSearchButton">
                        <i data-lucide="search" class="search-icon"></i>
                    </button>
                </div>
            </div>
            
            <div class="file-table-container">
                <table class="file-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Type</th>
                            <th>Upload Date</th>
                            <th>Status</th>
                            <th>Downloads</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminFileList">
                        <?php if (count($files) > 0): ?>
                            <?php foreach ($files as $file): ?>
                                <tr data-filename="<?php echo htmlspecialchars($file['original_filename']); ?>">
                                    <td><?php echo $file['id']; ?></td>
                                    <td data-filename="<?php echo htmlspecialchars($file['filename']); ?>"><?php echo htmlspecialchars($file['original_filename']); ?></td>
                                    <td><?php echo formatFileSize($file['file_size']); ?></td>
                                    <td><?php echo htmlspecialchars($file['file_type']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($file['upload_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $file['status']; ?>">
                                            <?php echo ucfirst($file['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $file['download_count']; ?></td>
                                    <td class="actions-cell">
                                        <div class="action-buttons">
                                            <a href="download.php?id=<?php echo $file['id']; ?>" class="action-button download-button" title="Download">
                                                <i data-lucide="download" class="action-icon"></i>
                                            </a>
                                            
                                            <?php if ($file['status'] !== 'approved'): ?>
                                                <form method="POST" action="" class="inline-form">
                                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="action-button approve-button" title="Approve">
                                                        <i data-lucide="check" class="action-icon"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($file['status'] !== 'rejected'): ?>
                                                <form method="POST" action="" class="inline-form">
                                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="action-button reject-button" title="Reject">
                                                        <i data-lucide="x" class="action-icon"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" action="" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="action-button delete-button" title="Delete">
                                                    <i data-lucide="trash-2" class="action-icon"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-files">No files found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> unknown site - Admin Panel</p>
        </footer>
    </div>
    
    <script src="assets/js/admin.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Format file size function
        <?php
        function formatFileSize($bytes) {
            if ($bytes >= 1073741824) {
                return number_format($bytes / 1073741824, 2) . ' GB';
            } elseif ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            } else {
                return $bytes . ' bytes';
            }
        }
        ?>
    </script>
</body>
</html>
