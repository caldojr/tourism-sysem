<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
ensurePostImagesTable($db);

// Get post data for editing
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;

if ($post_id > 0) {
    $query = "SELECT * FROM admin_posts WHERE id = ? AND deleted = 0";
    $stmt = $db->prepare($query);
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$post) {
    header("Location: manageposts.php");
    exit();
}

$current_images = fetchPostImages($db, [$post_id]);
$current_images = $current_images[$post_id] ?? [];
if (empty($current_images) && !empty($post['image_path'])) {
    $current_images = [$post['image_path']];
}

// Get deleted posts count for recycle bin
$recycle_count_query = "SELECT COUNT(*) as count FROM admin_posts WHERE deleted = 1";
$recycle_stmt = $db->prepare($recycle_count_query);
$recycle_stmt->execute();
$recycle_count = $recycle_stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Handle form submission for updating post
if ($_POST && isset($_POST['title'])) {
    $actor_admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $actor_admin_name = $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin');
    $category = $_POST['category'];
    $region = $_POST['region'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    
    // Handle multiple image upload (replace existing set when new files are provided)
    $image_path = $post['image_path']; // Keep existing image by default
    $uploaded_images = [];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $upload_dir = '../uploads/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $file_count = count($_FILES['images']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            if (!isset($_FILES['images']['error'][$i]) || $_FILES['images']['error'][$i] !== 0) {
                continue;
            }

            $file_extension = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_extensions, true)) {
                continue;
            }

            $file_name = uniqid('', true) . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $file_path)) {
                $uploaded_images[] = 'uploads/' . $file_name;
            }
        }
    }

    $should_replace_images = false;
    if (!empty($uploaded_images)) {
        $image_path = $uploaded_images[0];
        $should_replace_images = true;
    }
    
    $query = "UPDATE admin_posts SET category = ?, region = ?, title = ?, description = ?, image_path = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$category, $region, $title, $description, $image_path, $post_id])) {
        if ($should_replace_images) {
            $existing_images = fetchPostImages($db, [$post_id]);
            $existing_images = $existing_images[$post_id] ?? [];
            if (empty($existing_images) && !empty($post['image_path'])) {
                $existing_images = [$post['image_path']];
            }

            foreach ($existing_images as $existing_image) {
                if ($existing_image && file_exists('../' . $existing_image)) {
                    unlink('../' . $existing_image);
                }
            }

            $delete_stmt = $db->prepare("DELETE FROM admin_post_images WHERE post_id = ?");
            $delete_stmt->execute([$post_id]);

            $insert_stmt = $db->prepare(
                "INSERT INTO admin_post_images (post_id, image_path, sort_order) VALUES (?, ?, ?)"
            );
            foreach ($uploaded_images as $index => $path) {
                $insert_stmt->execute([$post_id, $path, $index]);
            }
        }

        $success = "Post updated successfully!";
        logSystemActivity(
            $db,
            'content',
            'post_update',
            'Post updated: ' . $title,
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'post',
            (string)$post_id,
            ['category' => $category, 'region' => $region]
        );
        // Refresh post data
        $post = array_merge($post, [
            'category' => $category,
            'region' => $region,
            'title' => $title,
            'description' => $description,
            'image_path' => $image_path
        ]);
        $current_images = fetchPostImages($db, [$post_id]);
        $current_images = $current_images[$post_id] ?? [];
        if (empty($current_images) && !empty($post['image_path'])) {
            $current_images = [$post['image_path']];
        }
    } else {
        $error = "Failed to update post!";
        if (!empty($uploaded_images)) {
            foreach ($uploaded_images as $uploaded_image) {
                if ($uploaded_image && file_exists('../' . $uploaded_image)) {
                    unlink('../' . $uploaded_image);
                }
            }
        }
        logSystemActivity(
            $db,
            'content',
            'post_update_failed',
            'Failed to update post: ' . $title,
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'post',
            (string)$post_id,
            ['category' => $category, 'region' => $region]
        );
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Nakupenda Tours</title>
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
        
        /* Form Styles */
        .admin-form {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ffb300;
            box-shadow: 0 0 0 2px rgba(255, 179, 0, 0.2);
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .admin-btn {
            background: #ffb300;
            color: #222;
            font-weight: bold;
            border: none;
            border-radius: 20px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s;
            margin-right: 1rem;
        }
        
        .admin-btn:hover {
            background: #ff8800;
            color: #fff;
        }
        
        .btn-cancel {
            background: #95a5a6;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
        }
        
        .current-image {
            margin-top: 1rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }
        
        .current-image img {
            width: 100%;
            max-width: 220px;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .current-image p {
            margin-top: 0.5rem;
            color: #666;
            font-size: 0.9rem;
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
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle - Positioned on RIGHT side with inline CSS -->
    <button class="menu-toggle" id="menuToggle" style="display: none; background: none; border: none; font-size: 1.5rem; color: #1a2a2a; cursor: pointer; position: fixed; top: 1rem; right: 1rem; z-index: 1000;">☰</button>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1>Nakupenda Tours</h1>
                <p>Admin Dashboard</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><span class="menu-icon">📊</span> Dashboard</a></li>
                <li><a href="post.php"><span class="menu-icon">📝</span> Create Post</a></li>
                <li><a href="manageposts.php"><span class="menu-icon">📋</span> Manage Posts</a></li>
                <li><a href="bookings.php"><span class="menu-icon">📅</span> Bookings</a></li>
                <li><a href="manage_admins.php"><span class="menu-icon">👥</span> Manage Admins</a></li>
                <li><a href="system_logs.php"><span class="menu-icon">🧾</span> System Logs</a></li>
                <li><a href="recyclebin.php"><span class="menu-icon">🗑️</span> Recycle Bin <?php if($recycle_count > 0): ?><span class="badge"><?php echo $recycle_count; ?></span><?php endif; ?></a></li>
                <li><a href="Profile.php"><span class="menu-icon">👤</span> Profile</a></li>
                <li><a href="logout.php"><span class="menu-icon">🚪</span> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="welcome">
                    <h2>Edit Post</h2>
                    <p>Update your post content</p>
                </div>
                <a href="manageposts.php" class="admin-btn btn-cancel">Back to Posts</a>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form class="admin-form" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="hotel" <?php echo $post['category'] === 'hotel' ? 'selected' : ''; ?>>Hotels</option>
                            <option value="transport" <?php echo $post['category'] === 'transport' ? 'selected' : ''; ?>>Transport</option>
                            <option value="safari" <?php echo $post['category'] === 'safari' ? 'selected' : ''; ?>>Safari Tours</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="region">Region</label>
                        <select id="region" name="region" required>
                            <option value="">Select Region</option>
                            <option value="tanzania" <?php echo $post['region'] === 'tanzania' ? 'selected' : ''; ?>>Tanzania Mainland</option>
                            <option value="zanzibar" <?php echo $post['region'] === 'zanzibar' ? 'selected' : ''; ?>>Zanzibar</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required placeholder="Enter post title" value="<?php echo htmlspecialchars($post['title']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required placeholder="Enter post description"><?php echo htmlspecialchars($post['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="images">Images</label>
                    <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    <small style="color: #666;">Select new images to replace the current set. Leave empty to keep existing images.</small>
                    
                    <div class="current-image">
                        <p>Current Images:</p>
                        <?php foreach ($current_images as $current_image): ?>
                            <img src="../<?php echo htmlspecialchars($current_image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="admin-btn">Update Post</button>
                    <a href="manageposts.php" class="admin-btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            
            // Show menu toggle on mobile
            function checkScreenSize() {
                if (window.innerWidth <= 768) {
                    menuToggle.style.display = 'block';
                } else {
                    menuToggle.style.display = 'none';
                    sidebar.classList.remove('active');
                }
            }
            
            // Initial check
            checkScreenSize();
            
            // Check on resize
            window.addEventListener('resize', checkScreenSize);
            
            // Toggle sidebar
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                    if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });
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
