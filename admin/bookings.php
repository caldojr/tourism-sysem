<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle status updates
if ($_POST && isset($_POST['update_booking'])) {
    $actor_admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $actor_admin_name = $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin');
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    $total_price = $_POST['total_price'] ?? null;
    $hotel_room_number = $_POST['hotel_room_number'] ?? null;
    $admin_notes = $_POST['admin_notes'] ?? null;
    
    $query = "UPDATE bookings SET status = ?, total_price = ?, hotel_room_number = ?, admin_notes = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$status, $total_price, $hotel_room_number, $admin_notes, $booking_id])) {
        $success = "Booking updated successfully!";
        logSystemActivity(
            $db,
            'booking',
            'booking_update',
            'Booking updated.',
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'booking',
            (string)$booking_id,
            ['status' => $status, 'total_price' => $total_price]
        );
    } else {
        $error = "Failed to update booking!";
        logSystemActivity(
            $db,
            'booking',
            'booking_update_failed',
            'Failed to update booking.',
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'booking',
            (string)$booking_id
        );
    }
}

// Handle booking deletion
if ($_POST && isset($_POST['delete_booking'])) {
    $actor_admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $actor_admin_name = $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin');
    $booking_id = $_POST['booking_id'];
    
    $query = "DELETE FROM bookings WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$booking_id])) {
        $success = "Booking deleted successfully!";
        logSystemActivity(
            $db,
            'booking',
            'booking_delete',
            'Booking deleted.',
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'booking',
            (string)$booking_id
        );
    } else {
        $error = "Failed to delete booking!";
        logSystemActivity(
            $db,
            'booking',
            'booking_delete_failed',
            'Failed to delete booking.',
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'booking',
            (string)$booking_id
        );
    }
}

// Handle notification creation
if ($_POST && isset($_POST['create_notification'])) {
    $actor_admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $actor_admin_name = $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin');
    $message = $_POST['notification_message'];
    
    $query = "INSERT INTO notifications (message) VALUES (?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$message])) {
        $success = "Notification created successfully!";
        $notification_id = (int)$db->lastInsertId();
        logSystemActivity(
            $db,
            'notification',
            'notification_create',
            'Notification created.',
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'notification',
            (string)$notification_id,
            ['message' => $message]
        );
    } else {
        $error = "Failed to create notification!";
        logSystemActivity(
            $db,
            'notification',
            'notification_create_failed',
            'Failed to create notification.',
            'admin',
            $actor_admin_id,
            $actor_admin_name,
            'notification',
            null
        );
    }
}

// Get all bookings with related post information
$query = "SELECT b.* FROM bookings b ORDER BY b.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get related post information for each booking
foreach ($bookings as &$booking) {
    // Get safari information
    $safari_info = [];
    if (!empty($booking['selected_safaris']) && $booking['selected_safaris'] != '[]') {
        $safari_ids = json_decode($booking['selected_safaris'], true);
        if (is_array($safari_ids) && !empty($safari_ids)) {
            $placeholders = str_repeat('?,', count($safari_ids) - 1) . '?';
            $safari_query = "SELECT id, title, image_path, region FROM admin_posts WHERE id IN ($placeholders)";
            $safari_stmt = $db->prepare($safari_query);
            $safari_stmt->execute($safari_ids);
            $safari_info = $safari_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    $booking['safari_info'] = $safari_info;
    
    // Get hotel information
    $hotel_info = [];
    if (!empty($booking['selected_hotels']) && $booking['selected_hotels'] != '[]') {
        $hotel_ids = json_decode($booking['selected_hotels'], true);
        if (is_array($hotel_ids) && !empty($hotel_ids)) {
            $placeholders = str_repeat('?,', count($hotel_ids) - 1) . '?';
            $hotel_query = "SELECT id, title, image_path, region FROM admin_posts WHERE id IN ($placeholders)";
            $hotel_stmt = $db->prepare($hotel_query);
            $hotel_stmt->execute($hotel_ids);
            $hotel_info = $hotel_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    $booking['hotel_info'] = $hotel_info;
    
    // Get transport information
    $transport_info = [];
    if (!empty($booking['selected_transports']) && $booking['selected_transports'] != '[]') {
        $transport_ids = json_decode($booking['selected_transports'], true);
        if (is_array($transport_ids) && !empty($transport_ids)) {
            $placeholders = str_repeat('?,', count($transport_ids) - 1) . '?';
            $transport_query = "SELECT id, title, image_path, region FROM admin_posts WHERE id IN ($placeholders)";
            $transport_stmt = $db->prepare($transport_query);
            $transport_stmt->execute($transport_ids);
            $transport_info = $transport_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    $booking['transport_info'] = $transport_info;
}
unset($booking); // break the reference

// Get statistics for dashboard
$total_bookings_query = "SELECT COUNT(*) as count FROM bookings";
$total_bookings_stmt = $db->prepare($total_bookings_query);
$total_bookings_stmt->execute();
$total_bookings = $total_bookings_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$pending_bookings_query = "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'";
$pending_bookings_stmt = $db->prepare($pending_bookings_query);
$pending_bookings_stmt->execute();
$pending_bookings = $pending_bookings_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$approved_bookings_query = "SELECT COUNT(*) as count FROM bookings WHERE status = 'approved'";
$approved_bookings_stmt = $db->prepare($approved_bookings_query);
$approved_bookings_stmt->execute();
$approved_bookings = $approved_bookings_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$cancelled_bookings_query = "SELECT COUNT(*) as count FROM bookings WHERE status = 'cancelled'";
$cancelled_bookings_stmt = $db->prepare($cancelled_bookings_query);
$cancelled_bookings_stmt->execute();
$cancelled_bookings = $cancelled_bookings_stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Nakupenda Tours</title>
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
        
        /* Notification Section */
        .notification-section {
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
        
        .notification-form {
            display: flex;
            gap: 1rem;
        }
        
        .notification-input {
            flex: 1;
            padding: 0.8rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .notification-btn {
            background: #ffb300;
            color: #222;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .notification-btn:hover {
            background: #ff8800;
            color: #fff;
        }
        
        /* Bookings Section */
        .bookings-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 1.5rem;
        }
        
        .booking-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #ffb300;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }
        
        .booking-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a2a2a;
        }
        
        .booking-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d1edff;
            color: #0c5460;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .detail-group h4 {
            color: #14532d;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        
        .detail-group p {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .selected-items {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 3px solid #ffb300;
        }
        
        .selected-items h5 {
            color: #14532d;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }
        
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .item-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        
        .item-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }
        
        .item-title {
            font-weight: 600;
            color: #1a2a2a;
            margin-bottom: 0.3rem;
        }
        
        .item-region {
            background: #14532d;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .booking-form {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            flex: 1 1 calc(50% - 1rem);
            min-width: 200px;
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
            height: 80px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .btn-update {
            background: #ffb300;
            color: #222;
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn-update:hover {
            background: #ff8800;
            color: #fff;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn-delete:hover {
            background: #c0392b;
        }
        
        .no-bookings {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .alert {
            padding: 1rem 1.5rem;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .notification-form {
                flex-direction: column;
            }
            
            .booking-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .form-group {
                flex: 1 1 100%;
            }
            
            .form-actions {
                flex-direction: column;
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
                <li><a href="dashboard.php"><span class="menu-icon">📊</span> Dashboard</a></li>
                <li><a href="post.php"><span class="menu-icon">📝</span> Create Post</a></li>
                <li><a href="manageposts.php"><span class="menu-icon">📋</span> Manage Posts</a></li>
                <li><a href="bookings.php" class="active"><span class="menu-icon">📅</span> Bookings</a></li>
                <li><a href="manage_admins.php"><span class="menu-icon">👥</span> Manage Admins</a></li>
                <li><a href="system_logs.php"><span class="menu-icon">🧾</span> System Logs</a></li>
                <li><a href="recyclebin.php"><span class="menu-icon">🗑️</span> Recycle Bin</a></li>
                <li><a href="profile.php"><span class="menu-icon">👤</span> Profile</a></li>
                <li><a href="logout.php"><span class="menu-icon">🚪</span> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="welcome">
                    <h2>Manage Bookings</h2>
                    <p>View and manage customer bookings</p>
                </div>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number"><?php echo $total_bookings; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-number"><?php echo $pending_bookings; ?></div>
                    <div class="stat-label">Pending Bookings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?php echo $approved_bookings; ?></div>
                    <div class="stat-label">Approved Bookings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">❌</div>
                    <div class="stat-number"><?php echo $cancelled_bookings; ?></div>
                    <div class="stat-label">Cancelled Bookings</div>
                </div>
            </div>
            
            <!-- Notification Section -->
            <div class="notification-section">
                <div class="section-header">
                    <h3 class="section-title">Send Notification</h3>
                </div>
                <form method="POST" class="notification-form">
                    <input type="text" name="notification_message" class="notification-input" placeholder="Enter notification message for all users..." required>
                    <button type="submit" name="create_notification" class="notification-btn">Send Notification</button>
                </form>
            </div>
            
            <!-- Bookings Section -->
            <div class="bookings-section">
                <div class="section-header">
                    <h3 class="section-title">All Bookings</h3>
                </div>
                
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <h3 class="booking-title">Booking #<?php echo $booking['id']; ?> - <?php echo $booking['full_name']; ?></h3>
                                <span class="booking-status status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            
                            <div class="booking-details">
                                <div class="detail-group">
                                    <h4>Personal Information</h4>
                                    <p><strong>Name:</strong> <?php echo $booking['full_name']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                                    <p><strong>Phone:</strong> <?php echo $booking['phone_number']; ?></p>
                                </div>
                                
                                <div class="detail-group">
                                    <h4>Travel Details</h4>
                                    <p><strong>Region:</strong> <?php echo ucfirst($booking['region']); ?></p>
                                    <p><strong>Dates:</strong> <?php echo date('M j, Y', strtotime($booking['start_date'])); ?> - <?php echo date('M j, Y', strtotime($booking['end_date'])); ?></p>
                                    <p><strong>Travelers:</strong> <?php echo $booking['total_travelers']; ?></p>
                                </div>
                                
                                <div class="detail-group">
                                    <h4>Booking Information</h4>
                                    <p><strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($booking['created_at'])); ?></p>
                                    <p><strong>Last Updated:</strong> <?php echo date('M j, Y g:i A', strtotime($booking['updated_at'])); ?></p>
                                    <?php if ($booking['total_price']): ?>
                                        <p><strong>Total Price:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                                    <?php endif; ?>
                                    <?php if ($booking['hotel_room_number']): ?>
                                        <p><strong>Hotel Room:</strong> <?php echo $booking['hotel_room_number']; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Selected Safaris -->
                            <?php if (!empty($booking['safari_info'])): ?>
                                <div class="selected-items">
                                    <h5>Selected Safari Tours</h5>
                                    <div class="items-grid">
                                        <?php foreach ($booking['safari_info'] as $safari): ?>
                                            <div class="item-card">
                                                <img src="../<?php echo $safari['image_path']; ?>" alt="<?php echo $safari['title']; ?>" class="item-image">
                                                <div class="item-title"><?php echo $safari['title']; ?></div>
                                                <span class="item-region"><?php echo ucfirst($safari['region']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Selected Hotels -->
                            <?php if (!empty($booking['hotel_info'])): ?>
                                <div class="selected-items">
                                    <h5>Selected Hotels</h5>
                                    <div class="items-grid">
                                        <?php foreach ($booking['hotel_info'] as $hotel): ?>
                                            <div class="item-card">
                                                <img src="../<?php echo $hotel['image_path']; ?>" alt="<?php echo $hotel['title']; ?>" class="item-image">
                                                <div class="item-title"><?php echo $hotel['title']; ?></div>
                                                <span class="item-region"><?php echo ucfirst($hotel['region']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Selected Transport -->
                            <?php if (!empty($booking['transport_info'])): ?>
                                <div class="selected-items">
                                    <h5>Selected Transport</h5>
                                    <div class="items-grid">
                                        <?php foreach ($booking['transport_info'] as $transport): ?>
                                            <div class="item-card">
                                                <img src="../<?php echo $transport['image_path']; ?>" alt="<?php echo $transport['title']; ?>" class="item-image">
                                                <div class="item-title"><?php echo $transport['title']; ?></div>
                                                <span class="item-region"><?php echo ucfirst($transport['region']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($booking['special_requests'])): ?>
                                <div class="selected-items">
                                    <h5>Special Requests</h5>
                                    <p><?php echo $booking['special_requests']; ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Update Booking Form -->
                            <form method="POST" class="booking-form">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="status_<?php echo $booking['id']; ?>">Status</label>
                                        <select id="status_<?php echo $booking['id']; ?>" name="status" required>
                                            <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="approved" <?php echo $booking['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                            <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="total_price_<?php echo $booking['id']; ?>">Total Price (TZS)</label>
                                        <input type="number" id="total_price_<?php echo $booking['id']; ?>" name="total_price" step="0.01" min="0" value="<?php echo $booking['total_price'] ?? ''; ?>" placeholder="Enter total price">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="hotel_room_number_<?php echo $booking['id']; ?>">Hotel Room Number</label>
                                        <input type="text" id="hotel_room_number_<?php echo $booking['id']; ?>" name="hotel_room_number" value="<?php echo $booking['hotel_room_number'] ?? ''; ?>" placeholder="Enter hotel room number">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="admin_notes_<?php echo $booking['id']; ?>">Admin Notes</label>
                                        <textarea id="admin_notes_<?php echo $booking['id']; ?>" name="admin_notes" placeholder="Enter notes for the customer"><?php echo $booking['admin_notes'] ?? ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" name="update_booking" class="btn-update">Update Booking</button>
                                    <button type="submit" name="delete_booking" class="btn-delete" onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.')">Delete Booking</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-bookings">
                        <p>No bookings found.</p>
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
