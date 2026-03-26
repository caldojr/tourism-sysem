<?php
require_once 'config.php';

// Check if user is admin
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_post'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $target_page = $_POST['target_page'];
        $post_type = $_POST['post_type'];
        $user_id = $_SESSION['user_id'];
        
        // Handle file upload
        $photo = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $upload_dir = 'photos/';
            $file_name = time() . '_' . basename($_FILES['photo']['name']);
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
                $photo = $file_path;
            }
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO admin_posts (user_id, title, description, photo, target_page, post_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $description, $photo, $target_page, $post_type]);
            $post_success = "Post added successfully!";
        } catch(PDOException $e) {
            $post_error = "Failed to add post: " . $e->getMessage();
        }
    }
    
    // Add other admin actions here (edit settings, manage bookings, etc.)
}

// Get stats for dashboard
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$tours_count = $pdo->query("SELECT COUNT(*) FROM safari_tours WHERE is_active = 1")->fetchColumn();
$bookings_count = $pdo->query("SELECT COUNT(*) FROM (
    SELECT booking_id FROM transport_bookings 
    UNION ALL SELECT booking_id FROM hotel_bookings 
    UNION ALL SELECT booking_id FROM tour_bookings
) as all_bookings")->fetchColumn();
$revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM (
    SELECT total_amount FROM tour_bookings WHERE booking_status != 'cancelled'
    UNION ALL SELECT total_amount FROM hotel_bookings WHERE booking_status != 'cancelled'
) as all_revenue")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - <?php echo getSetting('company_name'); ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Lato', Arial, sans-serif;
      background: #f8f9fa;
    }
    
    .admin-header {
      background: #14532d;
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .admin-nav {
      background: #ffb300;
      padding: 1rem 2rem;
    }
    
    .admin-nav ul {
      list-style: none;
      display: flex;
      gap: 2rem;
    }
    
    .admin-nav a {
      color: white;
      text-decoration: none;
      font-weight: 600;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      transition: background 0.3s;
    }
    
    .admin-nav a:hover {
      background: #ff8800;
    }
    
    .dashboard-content {
      padding: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      text-align: center;
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      color: #14532d;
      margin-bottom: 0.5rem;
    }
    
    .admin-section {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 2rem;
      margin-bottom: 2rem;
    }
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #14532d;
    }
    
    .form-input, .form-select, .form-textarea {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 1rem;
    }
    
    .form-textarea {
      min-height: 100px;
      resize: vertical;
    }
    
    .btn {
      background: #ffb300;
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
    }
    
    .btn:hover {
      background: #ff8800;
    }
    
    .success-message {
      background: #d4edda;
      color: #155724;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
    
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <header class="admin-header">
    <h1>Admin Dashboard</h1>
    <div>
      <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
      <a href="logout.php" style="color: white; margin-left: 1rem;">Logout</a>
    </div>
  </header>
  
  <nav class="admin-nav">
    <ul>
      <li><a href="#dashboard">Dashboard</a></li>
      <li><a href="#posts">Manage Posts</a></li>
      <li><a href="#bookings">Bookings</a></li>
      <li><a href="#tours">Tours</a></li>
      <li><a href="#settings">Settings</a></li>
    </ul>
  </nav>
  
  <div class="dashboard-content">
    <!-- Dashboard Stats -->
    <section id="dashboard" class="admin-section">
      <h2 style="color: #14532d; margin-bottom: 1.5rem;">Dashboard Overview</h2>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number"><?php echo $users_count; ?></div>
          <div>Total Users</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo $tours_count; ?></div>
          <div>Active Tours</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo $bookings_count; ?></div>
          <div>Total Bookings</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">$<?php echo number_format($revenue, 2); ?></div>
          <div>Total Revenue</div>
        </div>
      </div>
    </section>
    
    <!-- Add New Post -->
    <section id="posts" class="admin-section">
      <h2 style="color: #14532d; margin-bottom: 1.5rem;">Add New Post</h2>
      
      <?php if (isset($post_success)): ?>
        <div class="success-message"><?php echo $post_success; ?></div>
      <?php endif; ?>
      
      <?php if (isset($post_error)): ?>
        <div class="error-message"><?php echo $post_error; ?></div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_post" value="1">
        
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-input" required>
        </div>
        
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-textarea" required></textarea>
        </div>
        
        <div class="form-group">
          <label class="form-label">Target Page</label>
          <select name="target_page" class="form-select" required>
            <option value="">Select Page</option>
            <option value="home.html">Home Page</option>
            <option value="transport.html">Transport Page</option>
            <option value="hotel.html">Hotel Page</option>
            <option value="safari.html">Safari Page</option>
            <option value="packages.html">Packages Page</option>
            <option value="gallery.html">Gallery Page</option>
            <option value="aboutzanzibar.html">About Zanzibar</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Post Type</label>
          <select name="post_type" class="form-select" required>
            <option value="">Select Type</option>
            <option value="general">General</option>
            <option value="transport">Transport</option>
            <option value="hotel">Hotel</option>
            <option value="tour">Tour</option>
            <option value="safari">Safari</option>
            <option value="promotion">Promotion</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Photo (Optional)</label>
          <input type="file" name="photo" class="form-input" accept="image/*">
        </div>
        
        <button type="submit" class="btn">Add Post</button>
      </form>
    </section>
    
    <!-- Recent Bookings -->
    <section id="bookings" class="admin-section">
      <h2 style="color: #14532d; margin-bottom: 1.5rem;">Recent Bookings</h2>
      <p>Booking management functionality would be implemented here.</p>
    </section>
    
    <!-- Tour Management -->
    <section id="tours" class="admin-section">
      <h2 style="color: #14532d; margin-bottom: 1.5rem;">Tour Management</h2>
      <p>Tour creation and management functionality would be implemented here.</p>
    </section>
    
    <!-- Settings -->
    <section id="settings" class="admin-section">
      <h2 style="color: #14532d; margin-bottom: 1.5rem;">Website Settings</h2>
      <p>Website configuration and settings management would be implemented here.</p>
    </section>
  </div>
</body>
</html>