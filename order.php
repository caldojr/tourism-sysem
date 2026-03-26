<?php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

// Get active notification
$notification_query = "SELECT * FROM notifications WHERE is_active = true ORDER BY created_at DESC LIMIT 1";
$notification_stmt = $db->prepare($notification_query);
$notification_stmt->execute();
$notification = $notification_stmt->fetch(PDO::FETCH_ASSOC);

// Handle search
$bookings = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $normalized_phone = preg_replace('/\D+/', '', $phone_number);

    if ($email !== '' || $normalized_phone !== '') {
        // Match by exact email OR normalized phone digits (country code stays part of the match).
        $query = "SELECT * FROM bookings
                  WHERE (? <> '' AND email = ?)
                     OR (? <> '' AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone_number, '+', ''), '-', ''), ' ', ''), '(', ''), ')', '') = ?)
                  ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$email, $email, $normalized_phone, $normalized_phone]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Now get the related post titles for each booking
    foreach ($bookings as &$booking) {
        // Get safari titles
        $safari_titles = [];
        if (!empty($booking['selected_safaris']) && $booking['selected_safaris'] != '[]') {
            $safari_ids = json_decode($booking['selected_safaris'], true);
            if (is_array($safari_ids) && !empty($safari_ids)) {
                $placeholders = str_repeat('?,', count($safari_ids) - 1) . '?';
                $safari_query = "SELECT title FROM admin_posts WHERE id IN ($placeholders)";
                $safari_stmt = $db->prepare($safari_query);
                $safari_stmt->execute($safari_ids);
                $safari_titles = $safari_stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        }
        $booking['safari_titles'] = implode(', ', $safari_titles);
        
        // Get hotel titles
        $hotel_titles = [];
        if (!empty($booking['selected_hotels']) && $booking['selected_hotels'] != '[]') {
            $hotel_ids = json_decode($booking['selected_hotels'], true);
            if (is_array($hotel_ids) && !empty($hotel_ids)) {
                $placeholders = str_repeat('?,', count($hotel_ids) - 1) . '?';
                $hotel_query = "SELECT title FROM admin_posts WHERE id IN ($placeholders)";
                $hotel_stmt = $db->prepare($hotel_query);
                $hotel_stmt->execute($hotel_ids);
                $hotel_titles = $hotel_stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        }
        $booking['hotel_titles'] = implode(', ', $hotel_titles);
        
        // Get transport titles
        $transport_titles = [];
        if (!empty($booking['selected_transports']) && $booking['selected_transports'] != '[]') {
            $transport_ids = json_decode($booking['selected_transports'], true);
            if (is_array($transport_ids) && !empty($transport_ids)) {
                $placeholders = str_repeat('?,', count($transport_ids) - 1) . '?';
                $transport_query = "SELECT title FROM admin_posts WHERE id IN ($placeholders)";
                $transport_stmt = $db->prepare($transport_query);
                $transport_stmt->execute($transport_ids);
                $transport_titles = $transport_stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        }
        $booking['transport_titles'] = implode(', ', $transport_titles);
    }
    unset($booking); // break the reference
}

// Handle booking deletion
if ($_POST && isset($_POST['delete_booking'])) {
    $booking_id = $_POST['booking_id'];
    $delete_query = "DELETE FROM bookings WHERE id = ? AND status = 'pending'";
    $delete_stmt = $db->prepare($delete_query);
    
    if ($delete_stmt->execute([$booking_id])) {
        $success = "Booking deleted successfully!";
        // Refresh the page
        header("Location: order.php");
        exit();
    } else {
        $error = "Failed to delete booking. Only pending bookings can be deleted.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Nakupenda Tours & Safaris</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { 
            height: 100%; 
            font-family: 'Lato', Arial, sans-serif; 
            overflow-y: scroll;
        }
        
        /* Navigation Styles */
        .responsive-navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(255, 179, 0, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .navbar-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
        }
        
        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .navbar-logo-img {
            height: 60px;
            width: 60px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #ff8800;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            object-fit: cover;
        }
        
        .logo-text {
            color: white;
            font-weight: 800;
            font-size: 1.4rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        
        .navbar-toggle {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 44px;
            height: 44px;
            background: none;
            border: none;
            cursor: pointer;
        }
        
        .navbar-toggle .bar {
            display: block;
            width: 28px;
            height: 3px;
            margin: 3px 0;
            background: #fff;
            border-radius: 2px;
            transition: 0.3s;
        }
        
        .navbar-menu {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .navbar-menu li {
            position: relative;
            margin: 0 0.5rem;
        }
        
        .navbar-menu a {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            transition: all 0.3s;
            display: block;
            font-size: 1rem;
        }
        
        .navbar-menu a:hover,
        .navbar-menu a.active {
            background: #ff8800;
            transform: translateY(-2px);
        }
        
        /* Mobile Styles */
        @media (max-width: 768px) {
            .navbar-container {
                padding: 0.8rem 1rem;
            }
            
            .navbar-toggle {
                display: flex;
                position: absolute;
                right: 1rem;
                top: 50%;
                transform: translateY(-50%);
            }
            
            .navbar-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 280px;
                height: calc(100vh - 70px);
                flex-direction: column;
                background: #ffb300;
                transition: left 0.3s ease;
                padding: 1rem 0;
                box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            }
            
            .navbar-menu.open {
                left: 0;
            }
            
            .navbar-menu li {
                margin: 0;
                width: 100%;
            }
            
            .navbar-menu a {
                padding: 1rem 1.5rem;
                border-radius: 0;
            }
        }
        
        .hero-section {
            margin-top: 70px;
            min-height: 40vh;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('photos/download.jpg') center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 2rem;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        .search-section {
            padding: 3rem 1.5rem;
            background: #f8f9fa;
        }
        
        .search-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            padding: 3rem;
        }
        
        .search-form .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .search-form .form-group {
            flex: 1 1 calc(50% - 1.5rem);
            min-width: 300px;
        }
        
        .search-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: #333;
        }
        
        .search-form input {
            width: 70%;
            padding: 1rem 1.2rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .search-form input:focus {
            outline: none;
            border-color: #ffb300;
            box-shadow: 0 0 0 3px rgba(255, 179, 0, 0.2);
        }
        
        .search-btn {
            width: 100%;
            background: #ffb300;
            color: white;
            border: none;
            padding: 1.2rem 2rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .search-btn:hover {
            background: #ff8800;
        }
        
        .bookings-section {
            padding: 3rem 1.5rem;
            background: white;
        }
        
        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
            border-left: 5px solid #ffb300;
        }
        
        .booking-header {
            background: #f8f9fa;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .booking-body {
            padding: 2rem;
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
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1rem 0;
            border-left: 4px solid #ffb300;
        }
        
        .selected-items h5 {
            color: #14532d;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }
        
        .selected-items p {
            color: #666;
            line-height: 1.6;
        }
        
        .notification-box {
            background: #e6f7e6;
            border: 1px solid #58d68d;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin: 1rem 0;
            color: #27ae60;
        }
        
        .admin-message {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin: 1rem 0;
            color: #856404;
        }
        
        .pending-message {
            background: #d1edff;
            border: 1px solid #bee5eb;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin: 1rem 0;
            color: #0c5460;
        }
        
        .booking-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .delete-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: 600;
        }
        
        .delete-btn:hover {
            background: #c0392b;
        }
        
        .no-bookings {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .no-bookings h3 {
            margin-bottom: 1rem;
            color: #14532d;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
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

        /* Footer Styles */
        .safari-footer {
            position: relative;
            color: #fff;
            font-family: 'Lato', Arial, sans-serif;
            background: #ffb300;
            overflow: hidden;
            margin-top: 3rem;
        }

        .safari-footer .footer-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('photos/nakupenda.jpg') center center/cover no-repeat;
            opacity: 0.18;
            z-index: 1;
        }

        .safari-footer .footer-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 1.2rem 1.5rem;
            background: linear-gradient(90deg, #ffb300 80%, #ff8800 100%);
            border-radius: 16px 16px 0 0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }

        .safari-footer .footer-col {
            flex: 1 1 220px;
            margin: 0 1.2rem 1.5rem 0;
            min-width: 180px;
        }

        .safari-footer .footer-col h3, .safari-footer .footer-col h4 {
            margin-bottom: 1rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .safari-footer .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .safari-footer .footer-col ul li {
            margin-bottom: 0.7rem;
            font-size: 1rem;
        }

        .safari-footer .footer-col.quick-links ul li a {
            color: #fff;
            text-decoration: none;
            transition: color 0.18s, background 0.18s;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }

        .safari-footer .footer-col.quick-links ul li a:hover {
            background: #fff;
            color: #ff8800;
        }

        .safari-footer .footer-col.contact-info ul li i {
            margin-right: 0.7rem;
            color: #fff;
        }

        .safari-footer .tagline {
            font-size: 1.05rem;
            margin-bottom: 0.7rem;
            color: #fff;
            font-style: italic;
        }

        .safari-footer .naac {
            font-size: 0.98rem;
            color: #fff;
            opacity: 0.85;
        }

        .safari-footer .footer-bar {
            position: relative;
            z-index: 2;
            background: #ff8800;
            text-align: center;
            padding: 0.9rem 1rem;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.2px;
            border-radius: 0 0 16px 16px;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.04);
            margin-top: -0.5rem;
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .search-form .form-group {
                flex: 1 1 100%;
            }
            
            .search-container {
                padding: 2rem;
            }
            
            .booking-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .safari-footer .footer-content {
                flex-wrap: wrap;
                padding: 2rem 1rem 1rem 1rem;
            }
            
            .safari-footer .footer-col {
                margin: 0 0.7rem 1.2rem 0;
            }
        }

        @media (max-width: 600px) {
            .safari-footer .footer-content {
                flex-direction: column;
                padding: 1.5rem 0.7rem 0.7rem 0.7rem;
            }
            
            .safari-footer .footer-col {
                margin: 0 0 1.2rem 0;
                min-width: 0;
            }
            
            .safari-footer .footer-bar {
                font-size: 0.95rem;
                padding: 0.7rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="responsive-navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <img src="photos/download.jpg" alt="Nakupenda Tours" class="navbar-logo-img">
                <div class="logo-text">Nakupenda Tours</div>
            </div>
            
            <button class="navbar-toggle" aria-label="Toggle menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            
            <ul class="navbar-menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="safari.php">Safari Tours</a></li>
                <li><a href="transport.php">Transport</a></li>
                <li><a href="hotel.php">Hotel</a></li>
                <li><a href="book.php">Book</a></li>
                <li><a href="order.php" class="active">My Bookings</a></li>
                <li><a href="aboutzanzibar.php">Zanzibar</a></li>
          <li><a href="gallery.php">Gallery</a></li>

            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>My Bookings</h1>
            <p>View and manage your adventure bookings</p>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <div class="search-container">
            <h2 style="text-align: center; color: #14532d; margin-bottom: 2rem;">Find Your Bookings</h2>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form class="search-form" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" placeholder="Enter phone with country code (e.g. +255620144829)" value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
                    </div>
                </div>
                
                <button type="submit" class="search-btn">Search Bookings</button>
            </form>
        </div>
    </section>

    <!-- Bookings Section -->
    <section class="bookings-section">
        <div style="max-width: 1000px; margin: 0 auto;">
            <?php if ($notification): ?>
                <div class="notification-box">
                    <strong>Notification:</strong> <?php echo $notification['message']; ?>
                </div>
            <?php endif; ?>
            
            <?php if (count($bookings) > 0): ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <h3>Booking #<?php echo $booking['id']; ?></h3>
                            <span class="booking-status status-<?php echo $booking['status']; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                        
                        <div class="booking-body">
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
                                
                                <?php if ($booking['total_price']): ?>
                                    <div class="detail-group">
                                        <h4>Pricing & Details</h4>
                                        <p><strong>Total Price:</strong> USD:<?php echo number_format($booking['total_price']); ?>/=</p>
                                        <?php if ($booking['hotel_room_number']): ?>
                                            <p><strong>Hotel Room:</strong> <?php echo $booking['hotel_room_number']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Selected Items -->
                            <?php if (!empty($booking['safari_titles'])): ?>
                                <div class="selected-items">
                                    <h5>Selected Safari Tours:</h5>
                                    <p><?php echo $booking['safari_titles']; ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($booking['hotel_titles'])): ?>
                                <div class="selected-items">
                                    <h5>Selected Hotels:</h5>
                                    <p><?php echo $booking['hotel_titles']; ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($booking['transport_titles'])): ?>
                                <div class="selected-items">
                                    <h5>Selected Transport:</h5>
                                    <p><?php echo $booking['transport_titles']; ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($booking['special_requests'])): ?>
                                <div class="selected-items">
                                    <h5>Special Requests:</h5>
                                    <p><?php echo $booking['special_requests']; ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Admin Notes -->
                            <?php if (!empty($booking['admin_notes'])): ?>
                                <div class="admin-message">
                                    <strong>Admin Message:</strong> <?php echo $booking['admin_notes']; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Pending Message -->
                            <?php if ($booking['status'] === 'pending' && !$booking['total_price']): ?>
                                <div class="pending-message">
                                    <strong>Status:</strong> Waiting to get total price and hotel room number. We'll contact you soon.
                                </div>
                            <?php endif; ?>
                            
                            <!-- Actions -->
                            <?php if ($booking['status'] === 'pending'): ?>
                                <div class="booking-actions">
                                    <form method="POST">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" name="delete_booking" class="delete-btn" onclick="return confirm('Are you sure you want to delete this booking?')">
                                            Delete Booking
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif ($_POST): ?>
                <div class="no-bookings">
                    <h3>No bookings found</h3>
                    <p>Please check your email and phone number, or make a new booking.</p>
                </div>
            <?php else: ?>
                <div class="no-bookings">
                    <h3>Search for your bookings</h3>
                    <p>Enter your email or phone number above to view your bookings.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="safari-footer">
        <div class="footer-bg"></div>
        <div class="footer-content">
            <div class="footer-col company-info">
                <h3>Nakupenda Tours & Safaris</h3>
                <p class="tagline">Crafting Unforgettable Journeys Across Tanzania.</p>
                <p class="naac">Part of the NAAC Group.</p>
            </div>
            <div class="footer-col quick-links">
                <h4>Explore</h4>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="aboutus.php">About Us</a></li>
                    <li><a href="safari.php">Safari Packages</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                </ul>
            </div>
            <div class="footer-col destinations">
                <h4>Popular Destinations</h4>
                <ul>
                    <li>Serengeti</li>
                    <li>Ngorongoro</li>
                    <li>Zanzibar</li>
                    <li>Kilimanjaro</li>
                    <li>Tarangire</li>
                </ul>
            </div>
            <div class="footer-col contact-info">
                <h4>Get In Touch</h4>
                <ul>
                    <li><i class="fas fa-phone"></i> +255 620144829</li>
                    <li><i class="fas fa-envelope"></i> info@nakupendatours.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> Dar es Salaam, Tanzania</li>
                </ul>
            </div>
        </div>
        <div class="footer-bar">
            © <?php echo date('Y'); ?> Nakupenda Tours & Safaris. All rights reserved. A proud member of NAAC.
        </div>
    </footer>

    <script>
        // Mobile Navigation
        const navbarToggle = document.querySelector('.navbar-toggle');
        const navbarMenu = document.querySelector('.navbar-menu');
        
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('open');
        });
        
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768) {
                if (!navbarMenu.contains(e.target) && !navbarToggle.contains(e.target)) {
                    navbarMenu.classList.remove('open');
                }
            }
        });
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="assets/sweetalert2.all.min.js"></script>
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
