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

// Get deleted posts count for recycle bin
$recycle_count_query = "SELECT COUNT(*) as count FROM admin_posts WHERE deleted = 1";
$recycle_stmt = $db->prepare($recycle_count_query);
$recycle_stmt->execute();
$recycle_count = $recycle_stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Handle form submission
if ($_POST && isset($_POST['title'])) {
    $actor_admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $actor_admin_name = $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin');
    $category = $_POST['category'];
    $region = $_POST['region'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    
    // Handle multiple image upload
    $image_path = '';
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

    if (!empty($uploaded_images)) {
        $image_path = $uploaded_images[0];
    } else {
        $error = "Please upload at least one valid image.";
    }
    
    if (!isset($error)) {
        $query = "INSERT INTO admin_posts (category, region, title, description, price, image_path) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);

        if ($stmt->execute([$category, $region, $title, $description, $price, $image_path])) {
            $success = "Post created successfully!";
            $new_post_id = (int)$db->lastInsertId();
            if (!empty($uploaded_images)) {
                $image_insert = $db->prepare(
                    "INSERT INTO admin_post_images (post_id, image_path, sort_order) VALUES (?, ?, ?)"
                );
                foreach ($uploaded_images as $index => $path) {
                    $image_insert->execute([$new_post_id, $path, $index]);
                }
            }
        logSystemActivity(
            $db,
            'content',
            'post_create',
            'Post created: ' . $title,
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'post',
            (string)$new_post_id,
            ['category' => $category, 'region' => $region]
        );
            // Clear form fields
            $_POST = array();
        } else {
            $error = "Failed to create post!";
            logSystemActivity(
                $db,
                'content',
                'post_create_failed',
                'Failed to create post: ' . $title,
                'admin',
                $actor_admin_id,
                $actor_admin_name,
                'post',
                null,
                ['category' => $category, 'region' => $region]
            );
        }
    }
}

// Get 4 most recent posts
$query = "SELECT * FROM admin_posts WHERE deleted = 0 ORDER BY created_at DESC LIMIT 2";
$stmt = $db->prepare($query);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - Nakupenda Tours</title>
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
        }
        
        .admin-btn:hover {
            background: #ff8800;
            color: #fff;
        }
        
        /* Recent Posts Section */
        .recent-posts-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 2rem;
        }
        
        .section-title {
            color: #1a2a2a;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ffb300;
        }
        
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .post-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .post-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .post-content {
            padding: 1.2rem;
        }
        
        .post-category {
            display: inline-block;
            background: #ffb300;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .post-region {
            display: inline-block;
            background: #14532d;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            margin-left: 0.5rem;
        }
        
        .post-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a2a2a;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        
        .post-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .no-posts {
            text-align: center;
            padding: 2rem;
            color: #666;
            font-style: italic;
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
            
            .posts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle - Positioned on RIGHT side -->
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
                <li><a href="post.php" class="active"><span class="menu-icon">📝</span> Create Post</a></li>
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
                    <h2>Create New Post</h2>
                    <p>Add new content for hotels, transport, or safari tours</p>
                </div>
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
                            <option value="hotel" <?php echo isset($_POST['category']) && $_POST['category'] === 'hotel' ? 'selected' : ''; ?>>Hotels</option>
                            <option value="transport" <?php echo isset($_POST['category']) && $_POST['category'] === 'transport' ? 'selected' : ''; ?>>Transport</option>
                            <option value="safari" <?php echo isset($_POST['category']) && $_POST['category'] === 'safari' ? 'selected' : ''; ?>>Safari Tours</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="region">Region</label>
                        <select id="region" name="region" required>
                            <option value="">Select Region</option>
                            <option value="tanzania" <?php echo isset($_POST['region']) && $_POST['region'] === 'tanzania' ? 'selected' : ''; ?>>Tanzania Mainland</option>
                            <option value="zanzibar" <?php echo isset($_POST['region']) && $_POST['region'] === 'zanzibar' ? 'selected' : ''; ?>>Zanzibar</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required placeholder="Enter post title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required placeholder="Enter post description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="images">Images</label>
                    <input type="file" id="images" name="images[]" accept="image/*" multiple required>
                    <small style="color: #666;">Select one or more images (JPG, PNG, GIF, WEBP)</small>
                </div>

                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" step="0.01" id="price" name="price" required placeholder="Enter price" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                </div>
                
                <button type="submit" class="admin-btn">Create Post</button>
            </form>

            <!-- Recent Posts Section - Only 4 Posts -->
            <div class="recent-posts-section">
                <h3 class="section-title">Recent Posts</h3>
                <?php if (count($posts) > 0): ?>
                    <div class="posts-grid">
                        <?php foreach ($posts as $post): ?>
                            <div class="post-card">
                                <img src="../<?php echo $post['image_path']; ?>" alt="<?php echo $post['title']; ?>">
                                <div class="post-content">
                                    <div>
                                        <span class="post-category"><?php echo ucfirst($post['category']); ?></span>
                                        <span class="post-region"><?php echo ucfirst($post['region']); ?></span>
                                    </div>
                                    <h4 class="post-title"><?php echo $post['title']; ?></h4>
                                    <p class="post-description"><?php echo substr($post['description'], 0, 100) . '...'; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-posts">
                        <p>No posts yet. Create your first post above!</p>
                    </div>
                <?php endif; ?>
            </div>
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
