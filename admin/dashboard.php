<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$total_posts_query = "SELECT COUNT(*) as count FROM admin_posts WHERE deleted = 0";
$total_posts_stmt = $db->prepare($total_posts_query);
$total_posts_stmt->execute();
$total_posts = $total_posts_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$deleted_posts_query = "SELECT COUNT(*) as count FROM admin_posts WHERE deleted = 1";
$deleted_posts_stmt = $db->prepare($deleted_posts_query);
$deleted_posts_stmt->execute();
$deleted_posts = $deleted_posts_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$hotel_posts_query = "SELECT COUNT(*) as count FROM admin_posts WHERE category = 'hotel' AND deleted = 0";
$hotel_posts_stmt = $db->prepare($hotel_posts_query);
$hotel_posts_stmt->execute();
$hotel_posts = $hotel_posts_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$transport_posts_query = "SELECT COUNT(*) as count FROM admin_posts WHERE category = 'transport' AND deleted = 0";
$transport_posts_stmt = $db->prepare($transport_posts_query);
$transport_posts_stmt->execute();
$transport_posts = $transport_posts_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$safari_posts_query = "SELECT COUNT(*) as count FROM admin_posts WHERE category = 'safari' AND deleted = 0";
$safari_posts_stmt = $db->prepare($safari_posts_query);
$safari_posts_stmt->execute();
$safari_posts = $safari_posts_stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent posts
$recent_posts_query = "SELECT * FROM admin_posts WHERE deleted = 0 ORDER BY created_at DESC LIMIT 4";
$recent_posts_stmt = $db->prepare($recent_posts_query);
$recent_posts_stmt->execute();
$recent_posts = $recent_posts_stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nakupenda Tours</title>
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
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1a2a2a;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-weight: 600;
        }
        
        /* Recent Posts Section */
        .recent-posts {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a2a2a;
        }
        
        .view-all-btn {
            background: #ffb300;
            color: #222;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .view-all-btn:hover {
            background: #ff8800;
            color: #fff;
        }
        
        .posts-list {
            list-style: none;
        }
        
        .post-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f5f5f5;
            transition: background 0.3s;
        }
        
        .post-item:hover {
            background: #f9f9f9;
        }
        
        .post-item:last-child {
            border-bottom: none;
        }
        
        .post-image {
            width: 60px;
            height: 45px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }
        
        .post-info {
            flex: 1;
        }
        
        .post-title {
            font-weight: 600;
            color: #1a2a2a;
            margin-bottom: 0.3rem;
        }
        
        .post-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.8rem;
            color: #666;
        }
        
        .post-category {
            background: #ffb300;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .post-region {
            background: #14532d;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .no-posts {
            text-align: center;
            padding: 2rem;
            color: #666;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
  <button class="menu-toggle" id="menuToggle" style="display: ; background: none; border: none; font-size: 1.5rem; color: #1a2a2a; cursor: pointer; position: fixed; top: 1rem; right: -80%; z-index:1000 ;">☰</button>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1>Nakupenda Tours</h1>
                <p>Admin Dashboard</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><span class="menu-icon">📊</span> Dashboard</a></li>
                <li><a href="post.php"><span class="menu-icon">📝</span> Create Post</a></li>
                <li><a href="manageposts.php"><span class="menu-icon">📋</span> Manage Posts</a></li>
                <li><a href="bookings.php"><span class="menu-icon">📅</span> Bookings</a></li>
                <li><a href="manage_admins.php"><span class="menu-icon">👥</span> Manage Admins</a></li>
                <li><a href="system_logs.php"><span class="menu-icon">🧾</span> System Logs</a></li>
                <li><a href="recyclebin.php"><span class="menu-icon">🗑️</span> Recycle Bin <?php if($deleted_posts > 0): ?><span class="badge"><?php echo $deleted_posts; ?></span><?php endif; ?></a></li>
                <li><a href="Profile.php"><span class="menu-icon">👤</span> Profile</a></li>
                <li><a href="logout.php"><span class="menu-icon">🚪</span> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="welcome">
                    <h2>Welcome to Dashboard</h2>
                    <p>Manage your content and view statistics</p>
                </div>
            </div>
            
            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-number"><?php echo $total_posts; ?></div>
                    <div class="stat-label">Total Posts</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🗑️</div>
                    <div class="stat-number"><?php echo $deleted_posts; ?></div>
                    <div class="stat-label">Posts in Recycle Bin</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🏨</div>
                    <div class="stat-number"><?php echo $hotel_posts; ?></div>
                    <div class="stat-label">Hotel Posts</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🚗</div>
                    <div class="stat-number"><?php echo $transport_posts; ?></div>
                    <div class="stat-label">Transport Posts</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🦁</div>
                    <div class="stat-number"><?php echo $safari_posts; ?></div>
                    <div class="stat-label">Safari Posts</div>
                </div>
            </div>
            
            <!-- Recent Posts Section -->
            <div class="recent-posts">
                <div class="section-header">
                    <h3 class="section-title">Recent Posts</h3>
                    <a href="manageposts.php" class="view-all-btn">View All Posts</a>
                </div>
                
                <?php if (count($recent_posts) > 0): ?>
                    <ul class="posts-list">
                        <?php foreach ($recent_posts as $post): ?>
                            <li class="post-item">
                                <img src="../<?php echo $post['image_path']; ?>" alt="<?php echo $post['title']; ?>" class="post-image">
                                <div class="post-info">
                                    <div class="post-title"><?php echo $post['title']; ?></div>
                                    <div class="post-meta">
                                        <span class="post-category"><?php echo ucfirst($post['category']); ?></span>
                                        <span class="post-region"><?php echo ucfirst($post['region']); ?></span>
                                        <span><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-posts">
                        <p>No posts found. <a href="post.php">Create your first post</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
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
</body>
</html>
