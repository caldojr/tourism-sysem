<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Pagination settings
$posts_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $posts_per_page;

// Get deleted posts count for recycle bin
$recycle_count_query = "SELECT COUNT(*) as count FROM admin_posts WHERE deleted = 1";
$recycle_stmt = $db->prepare($recycle_count_query);
$recycle_stmt->execute();
$recycle_count = $recycle_stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get total deleted posts count for pagination
$count_query = "SELECT COUNT(*) as total FROM admin_posts WHERE deleted = 1";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute();
$total_posts = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Handle search and filter
$search = '';
$category_filter = '';
$region_filter = '';

$query = "SELECT * FROM admin_posts WHERE deleted = 1";
$params = [];

if ($_GET) {
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $_GET['search'];
        $query .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category_filter = $_GET['category'];
        $query .= " AND category = ?";
        $params[] = $category_filter;
    }
    
    if (isset($_GET['region']) && !empty($_GET['region'])) {
        $region_filter = $_GET['region'];
        $query .= " AND region = ?";
        $params[] = $region_filter;
    }
}

$query .= " ORDER BY created_at DESC LIMIT $posts_per_page OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$deleted_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle post restoration
if ($_POST && isset($_POST['restore_post'])) {
    $actor_admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $actor_admin_name = $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin');
    $post_id = $_POST['post_id'];
    $restore_query = "UPDATE admin_posts SET deleted = 0 WHERE id = ?";
    $restore_stmt = $db->prepare($restore_query);
    if ($restore_stmt->execute([$post_id])) {
        logSystemActivity(
            $db,
            'content',
            'post_restore',
            'Post restored from recycle bin.',
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'post',
            (string)$post_id
        );
        $success = "Post restored successfully!";
        header("Location: recyclebin.php?page=" . $current_page);
        exit();
    } else {
        $error = "Failed to restore post!";
        logSystemActivity(
            $db,
            'content',
            'post_restore_failed',
            'Failed restoring post from recycle bin.',
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'post',
            (string)$post_id
        );
    }
}

// Handle permanent deletion
if ($_POST && isset($_POST['delete_permanent'])) {
    $actor_admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $actor_admin_name = $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin');
    $post_id = $_POST['post_id'];
    $delete_query = "DELETE FROM admin_posts WHERE id = ?";
    $delete_stmt = $db->prepare($delete_query);
    if ($delete_stmt->execute([$post_id])) {
        logSystemActivity(
            $db,
            'content',
            'post_permanent_delete',
            'Post permanently deleted from recycle bin.',
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'post',
            (string)$post_id
        );
        $success = "Post permanently deleted!";
        header("Location: recyclebin.php?page=" . $current_page);
        exit();
    } else {
        $error = "Failed to delete post permanently!";
        logSystemActivity(
            $db,
            'content',
            'post_permanent_delete_failed',
            'Failed permanent deletion from recycle bin.',
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'post',
            (string)$post_id
        );
    }
}

// Handle multiple actions
if ($_POST && isset($_POST['bulk_action'])) {
    $actor_admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $actor_admin_name = $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin');
    if (isset($_POST['selected_posts']) && !empty($_POST['selected_posts'])) {
        $selected_posts = $_POST['selected_posts'];
        $action = $_POST['bulk_action'];
        $placeholders = str_repeat('?,', count($selected_posts) - 1) . '?';
        
        if ($action === 'restore') {
            $bulk_query = "UPDATE admin_posts SET deleted = 0 WHERE id IN ($placeholders)";
            $success_msg = count($selected_posts) . " posts restored successfully!";
        } else if ($action === 'delete') {
            $bulk_query = "DELETE FROM admin_posts WHERE id IN ($placeholders)";
            $success_msg = count($selected_posts) . " posts permanently deleted!";
        }
        
        $bulk_stmt = $db->prepare($bulk_query);
        if ($bulk_stmt->execute($selected_posts)) {
            logSystemActivity(
                $db,
                'content',
                $action === 'restore' ? 'post_restore_bulk' : 'post_permanent_delete_bulk',
                $action === 'restore' ? 'Multiple posts restored from recycle bin.' : 'Multiple posts permanently deleted from recycle bin.',
                'admin',
                $actor_admin_id,
                $actor_admin_name,
                'post',
                'bulk',
                ['post_ids' => array_values($selected_posts), 'count' => count($selected_posts), 'bulk_action' => $action]
            );
            $success = $success_msg;
            header("Location: recyclebin.php?page=" . $current_page);
            exit();
        } else {
            $error = "Failed to perform bulk action!";
            logSystemActivity(
                $db,
                'content',
                'post_bulk_action_failed',
                'Failed recycle bin bulk action.',
                'admin',
                $actor_admin_id,
                $actor_admin_name,
                'post',
                'bulk',
                ['post_ids' => array_values($selected_posts), 'count' => count($selected_posts), 'bulk_action' => $action]
            );
        }
    } else {
        $error = "No posts selected!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycle Bin - Nakupenda Tours</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: 'Lato', Arial, sans-serif; background: #f7f7f7; }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1a2a2a 0%, #2d4a4a 100%);
            color: white;
            padding: 2rem 0;
            transition: transform 0.3s ease;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 2rem 1.5rem;
            border-bottom: 1px solid #3a5a5a;
            margin-bottom: 1rem;
        }
        
        .sidebar-header h1 {
            font-size: 1.5rem;
            font-weight: 900;
            color: #ffb300;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: #ccc;
            text-decoration: none;
            transition: background 0.3s, color 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 179, 0, 0.1);
            color: #ffb300;
            border-right: 3px solid #ffb300;
        }
        
        .menu-icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .badge {
            background: #ffb300;
            color: #222;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            margin-left: auto;
        }
        
        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #1a2a2a;
            cursor: pointer;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1000;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }
        
        .welcome h2 {
            color: #1a2a2a;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        /* Search and Filter Section */
        .filter-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .filter-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #ffb300;
            box-shadow: 0 0 0 2px rgba(255, 179, 0, 0.2);
        }
        
        .filter-btn {
            background: #ffb300;
            color: #222;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            transition: background 0.3s;
            height: fit-content;
        }
        
        .filter-btn:hover {
            background: #ff8800;
            color: #fff;
        }
        
        /* Bulk Actions */
        .bulk-actions {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .bulk-select {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .bulk-select input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        
        .bulk-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-bulk-restore {
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-bulk-delete {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-bulk-restore:hover {
            background: #219653;
        }
        
        .btn-bulk-delete:hover {
            background: #c0392b;
        }
        
        /* Posts Table */
        .posts-table-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 1.5rem;
            overflow-x: auto;
        }
        
        .posts-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .posts-table th {
            background: #f5f5f5;
            padding: 1rem;
            text-align: left;
            font-weight: 700;
            color: #333;
            border-bottom: 1px solid #ddd;
        }
        
        .posts-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .post-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .post-title {
            font-weight: 600;
            color: #1a2a2a;
        }
        
        .post-category {
            display: inline-block;
            background: #ffb300;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .post-region {
            display: inline-block;
            background: #14532d;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .deleted-badge {
            display: inline-block;
            background: #e74c3c;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-restore, .btn-delete {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            transition: color 0.3s;
            padding: 0.3rem;
        }
        
        .btn-restore {
            color: #27ae60;
        }
        
        .btn-restore:hover {
            color: #219653;
        }
        
        .btn-delete {
            color: #e74c3c;
        }
        
        .btn-delete:hover {
            color: #c0392b;
        }
        
        .no-posts {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .pagination-info {
            color: #666;
            font-size: 0.9rem;
        }
        
        .pagination-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .pagination-btn {
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
        }
        
        .pagination-btn:hover {
            background: #f5f5f5;
        }
        
        .pagination-btn.active {
            background: #ffb300;
            color: white;
            border-color: #ffb300;
        }
        
        .pagination-btn:disabled {
            background: #f5f5f5;
            color: #999;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .alert-success {
            background: #e6f7e6;
            color: #27ae60;
            border: 1px solid #58d68d;
        }
        
        .alert-error {
            background: #ffe6e6;
            color: #d63031;
            border: 1px solid #ff7675;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                transform: translateX(-100%);
                z-index: 999;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                padding: 1rem;
                margin-left: 0;
            }
            
            .filter-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .bulk-actions {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .posts-table {
                font-size: 0.9rem;
            }
            
            .posts-table th, 
            .posts-table td {
                padding: 0.5rem;
            }
            
            .pagination {
                flex-direction: column;
                gap: 1rem;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle" style="display: ; background: none; border: none; font-size: 1.5rem; color: #1a2a2a; cursor: pointer; position: fixed; top: 1rem; right: -80%; z-index:1000 ;">☰</button>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1>Nakupenda Tours</h1>
                <p>Admin Dashboard</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="Dashboard.php"><span class="menu-icon">📊</span> Dashboard</a></li>
                <li><a href="post.php"><span class="menu-icon">📝</span> Create Post</a></li>
                <li><a href="manageposts.php"><span class="menu-icon">📋</span> Manage Posts</a></li>
                <li><a href="bookings.php"><span class="menu-icon">📅</span> Bookings</a></li>
                <li><a href="manage_admins.php"><span class="menu-icon">👥</span> Manage Admins</a></li>
                <li><a href="system_logs.php"><span class="menu-icon">🧾</span> System Logs</a></li>
                <li><a href="recyclebin.php" class="active"><span class="menu-icon">🗑️</span> Recycle Bin <?php if($recycle_count > 0): ?><span class="badge"><?php echo $recycle_count; ?></span><?php endif; ?></a></li>
                <li><a href="Profile.php"><span class="menu-icon">👤</span> Profile</a></li>
                <li><a href="logout.php"><span class="menu-icon">🚪</span> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="welcome">
                    <h2>Recycle Bin</h2>
                    <p>Restore or permanently delete posts</p>
                </div>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Search and Filter Section -->
            <div class="filter-section">
                <form method="GET">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="search">Search Posts</label>
                            <input type="text" id="search" name="search" placeholder="Search by title or description" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="filter-group">
                            <label for="category">Filter by Category</label>
                            <select id="category" name="category">
                                <option value="">All Categories</option>
                                <option value="hotel" <?php echo $category_filter === 'hotel' ? 'selected' : ''; ?>>Hotels</option>
                                <option value="transport" <?php echo $category_filter === 'transport' ? 'selected' : ''; ?>>Transport</option>
                                <option value="safari" <?php echo $category_filter === 'safari' ? 'selected' : ''; ?>>Safari Tours</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="region">Filter by Region</label>
                            <select id="region" name="region">
                                <option value="">All Regions</option>
                                <option value="tanzania" <?php echo $region_filter === 'tanzania' ? 'selected' : ''; ?>>Tanzania Mainland</option>
                                <option value="zanzibar" <?php echo $region_filter === 'zanzibar' ? 'selected' : ''; ?>>Zanzibar</option>
                            </select>
                        </div>
                        <button type="submit" class="filter-btn">Apply Filters</button>
                    </div>
                </form>
            </div>
            
            <!-- Bulk Actions -->
            <form method="POST" id="bulkForm">
                <input type="hidden" name="bulk_action" id="bulkAction" value="">
                <div class="bulk-actions">
                    <div class="bulk-select">
                        <input type="checkbox" id="selectAll">
                        <label for="selectAll">Select All</label>
                        <span class="pagination-info">Showing <?php echo count($deleted_posts); ?> of <?php echo $total_posts; ?> deleted posts</span>
                    </div>
                    <div class="bulk-buttons">
                        <button type="button" id="bulkRestoreBtn" class="btn-bulk-restore">Restore Selected</button>
                        <button type="button" id="bulkDeleteBtn" class="btn-bulk-delete">Delete Selected Permanently</button>
                    </div>
                </div>
            
                <!-- Posts Table -->
                <div class="posts-table-container">
                    <?php if (count($deleted_posts) > 0): ?>
                        <table class="posts-table">
                            <thead>
                                <tr>
                                    <th width="50">Select</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Region</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deleted_posts as $post): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_posts[]" value="<?php echo $post['id']; ?>" class="post-checkbox">
                                        </td>
                                        <td>
                                            <img src="../<?php echo $post['image_path']; ?>" alt="<?php echo $post['title']; ?>" class="post-image">
                                        </td>
                                        <td>
                                            <div class="post-title"><?php echo $post['title']; ?></div>
                                        </td>
                                        <td>
                                            <span class="post-category"><?php echo ucfirst($post['category']); ?></span>
                                        </td>
                                        <td>
                                            <span class="post-region"><?php echo ucfirst($post['region']); ?></span>
                                        </td>
                                        <td>
                                            <?php echo substr($post['description'], 0, 100) . '...'; ?>
                                        </td>
                                        <td>
                                            <span class="deleted-badge">Deleted</span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                    <button type="submit" name="restore_post" class="btn-restore" title="Restore Post" onclick="return confirm('Are you sure you want to restore this post?')">↶</button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                    <button type="submit" name="delete_permanent" class="btn-delete" title="Delete Permanently" onclick="return confirm('Are you sure you want to permanently delete this post? This action cannot be undone!')">🗑️</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-posts">
                            <p>No deleted posts found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <div class="pagination-info">
                        Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                    </div>
                    <div class="pagination-buttons">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $region_filter ? '&region=' . $region_filter : ''; ?>" class="pagination-btn">Previous</a>
                        <?php else: ?>
                            <span class="pagination-btn" disabled>Previous</span>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $region_filter ? '&region=' . $region_filter : ''; ?>" class="pagination-btn <?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $region_filter ? '&region=' . $region_filter : ''; ?>" class="pagination-btn">Next</a>
                        <?php else: ?>
                            <span class="pagination-btn" disabled>Next</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.post-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });

        // Auto-submit form when category or region changes
        document.getElementById('category').addEventListener('change', function() {
            this.form.submit();
        });

        document.getElementById('region').addEventListener('change', function() {
            this.form.submit();
        });

        // Bulk action confirmations with SweetAlert2
        async function handleBulkAction(action) {
            const checkedPosts = document.querySelectorAll('.post-checkbox:checked');
            if (!checkedPosts.length) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'No posts selected',
                    text: 'Please select at least one post first.',
                    timer: 2500,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
                return;
            }

            const isDelete = action === 'delete';
            const result = await Swal.fire({
                icon: isDelete ? 'warning' : 'question',
                title: isDelete ? 'Confirm Permanent Delete' : 'Confirm Restore',
                text: isDelete
                    ? 'Are you sure you want to permanently delete selected posts? This action cannot be undone!'
                    : 'Are you sure you want to restore selected posts?',
                showCancelButton: true,
                confirmButtonText: isDelete ? 'Yes, delete' : 'Yes, restore',
                cancelButtonText: 'Cancel',
                confirmButtonColor: isDelete ? '#d33' : '#ffb300'
            });

            if (!result.isConfirmed) {
                return;
            }

            document.getElementById('bulkAction').value = action;
            document.getElementById('bulkForm').submit();
        }

        document.getElementById('bulkDeleteBtn').addEventListener('click', function () {
            handleBulkAction('delete');
        });

        document.getElementById('bulkRestoreBtn').addEventListener('click', function () {
            handleBulkAction('restore');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (window.innerWidth <= 768 && sidebar.classList.contains('active') && 
                !sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
    <script src="../assets/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = Array.from(document.querySelectorAll('.alert'));
            if (!alerts.length || typeof Swal === 'undefined') {
                return;
            }

            const queue = alerts
                .map(function (alertEl) {
                    alertEl.style.display = 'none';
                    const message = (alertEl.textContent || '').trim();
                    if (!message) {
                        return null;
                    }
                    const isError = alertEl.classList.contains('alert-error');
                    return {
                        icon: isError ? 'error' : 'success',
                        title: isError ? 'Error' : 'Success',
                        text: message
                    };
                })
                .filter(Boolean);

            (async function showAlertsSequentially() {
                for (const item of queue) {
                    await Swal.fire({
                        icon: item.icon,
                        title: item.title,
                        text: item.text,
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                }
            })();
        });
    </script>
</body>
</html>
