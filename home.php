<?php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

// Get all posts for home page
$query = "SELECT * FROM admin_posts ORDER BY created_at DESC LIMIT 9";
$stmt = $db->prepare($query);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$post_ids = array_column($posts, 'id');
$post_images = fetchPostImages($db, $post_ids);

// Get counts for statistics
$safari_count = $db->query("SELECT COUNT(*) FROM admin_posts WHERE category = 'safari'")->fetchColumn();
$hotel_count = $db->query("SELECT COUNT(*) FROM admin_posts WHERE category = 'hotel'")->fetchColumn();
$transport_count = $db->query("SELECT COUNT(*) FROM admin_posts WHERE category = 'transport'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Nakupenda Tours & Safaris</title>
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        body, html { 
            height: 100%; 
            font-family: 'Lato', Arial, sans-serif;
            scroll-behavior: smooth;
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
        
      /* Hero Section */
.hero-section {
    margin-top: 80px;
    min-height: 100vh;
    background: linear-gradient(135deg, rgba(20, 83, 45, 0.7), rgba(255, 179, 0, 0.6)), url('photos/download.jpg') center/cover fixed;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    padding: 4rem 2rem;
    position: relative;
    overflow: hidden;
}

.hero-content {
    max-width: 900px;
    z-index: 2;
}

.hero-content h1 {
    font-size: 4.5rem;
    font-weight: 900;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
    line-height: 1.1;
}

.hero-content p {
    font-size: 1.4rem;
    margin-bottom: 3rem;
    opacity: 0.95;
    line-height: 1.6;
}

.cta-buttons {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-button {
    background: #ffb300;
    color: white;
    border: none;
    padding: 1.2rem 2.5rem;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
    box-shadow: 0 8px 25px rgba(255, 179, 0, 0.3);
}

.cta-button.secondary {
    background: transparent;
    border: 3px solid white;
}

.cta-button:hover {
    background: #ff8800;
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(255, 179, 0, 0.4);
}

.cta-button.secondary:hover {
    background: white;
    color: #ff8800;
}
        
        /* Statistics Section */
        .stats-section {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #14532d, #ffb300);
            color: white;
        }
        
        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            text-align: center;
        }
        
        .stat-item {
            padding: 2rem;
        }
        
        .stat-number {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.3);
        }
        
        .stat-label {
            font-size: 1.3rem;
            font-weight: 600;
            opacity: 0.9;
        }
        
        /* Featured Section */
        .featured-section {
            padding: 6rem 2rem;
            background: #f8f9fa;
        }
        
        .section-title {
            text-align: center;
            font-size: 3.5rem;
            font-weight: 800;
            color: #14532d;
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 4rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 3rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .featured-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.4s ease;
            position: relative;
        }
        
        .featured-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        }
        
        .featured-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ffb300, #ff8800, #14532d);
            z-index: 2;
        }
        
        .card-image-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 1rem;
            background: #fff;
        }

        .card-image-stack img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transition: transform 0.35s;
        }

        .featured-card:hover .card-image-stack img {
            transform: scale(1.02);
        }
        
        .featured-content {
            padding: 2.5rem;
            position: relative;
        }
        
        .featured-badge {
            position: absolute;
            top: -20px;
            right: 2rem;
            background: #ffb300;
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 25px;
            font-weight: 800;
            font-size: 0.9rem;
            box-shadow: 0 6px 15px rgba(255, 179, 0, 0.4);
        }
        
        .featured-category {
            display: inline-block;
            background: #14532d;
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .featured-region {
            display: inline-block;
            background: #ffb300;
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 1rem;
            margin-left: 0.5rem;
        }
        
        .featured-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1a2a2a;
            margin-bottom: 1.2rem;
            line-height: 1.3;
        }
        
        .featured-description {
            color: #666;
            line-height: 1.7;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .view-details-btn {
            width: 100%;
            background: #ffb300;
            color: #fff;
            border: none;
            padding: 1.2rem 1.5rem;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 1.1rem;
        }
        
        .view-details-btn:hover {
            background: #ff8800;
        }
        
        /* Services Section */
        .services-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .service-card {
            background: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .service-icon {
            font-size: 3.5rem;
            color: #ffb300;
            margin-bottom: 1.5rem;
        }
        
        .service-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #14532d;
            margin-bottom: 1rem;
        }
        
        .service-description {
            color: #666;
            line-height: 1.6;
            font-size: 1.1rem;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.95);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .modal-content {
            background: white;
            border-radius: 25px;
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
        }
        
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            background: rgba(0,0,0,0.7);
            border: none;
            font-size: 2rem;
            color: #fff;
            cursor: pointer;
            z-index: 1;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        
        .modal-close:hover {
            background: rgba(0,0,0,0.9);
        }

        .modal-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 25px 25px 0 0;
        }
        
        .modal-body {
            padding: 2.5rem;
        }
        
        .modal-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: #14532d;
            margin-bottom: 1rem;
        }
        
        .modal-category {
            display: inline-block;
            background: #ffb300;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .modal-description {
            color: #444;
            line-height: 1.8;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            white-space: pre-line;
        }

        /* Footer Styles - Matching safari.php */
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

        /* Responsive Design */
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
                top: 80px;
                left: -100%;
                width: 280px;
                height: calc(100vh - 80px);
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
            
            .hero-content h1 {
                font-size: 2.8rem;
            }
            
            .hero-content p {
                font-size: 1.2rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .featured-grid {
                grid-template-columns: 1fr;
            }
            
            .section-title {
                font-size: 2.5rem;
            }
            
            .modal-content {
                max-width: 95%;
            }
            
            .modal-image {
                height: 250px;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
            
            .modal-title {
                font-size: 1.8rem;
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
                <li><a href="home.php" class="active">Home</a></li>
                <li><a href="safari.php">Safari Tours</a></li>
                <li><a href="transport.php">Transport</a></li>
                <li><a href="hotel.php">Hotel</a></li>
                <li><a href="book.php">Book</a></li>
                <li><a href="order.php">My Bookings</a></li>
                <li><a href="aboutzanzibar.php">Zanzibar</a></li>
                <li><a href="gallery.php">Gallery</a></li>
            </ul>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Discover Tanzania's Wild Beauty</h1>
            <p>Experience unforgettable adventures with Nakupenda Tours & Safaris. From breathtaking safaris to luxury accommodations, we craft journeys that create lifelong memories.</p>
            <div class="cta-buttons">
                <a href="#safari-packages" class="cta-button">Explore All Nakupenda Tour</a>
                <a href="book.php" class="cta-button secondary">Book Your Trip</a>
            </div>
        </div>
    </section>

    <!-- Featured Destinations Section -->
    <section class="featured-section"  id="safari-packages">
        <h2 class="section-title">Featured Destinations</h2>
        <p class="section-subtitle">Discover our most popular destinations and experiences across Tanzania and Zanzibar</p>
        
        <div class="featured-grid" id="posts-container">
            <?php foreach ($posts as $post): ?>
                <?php
                    $images = $post_images[$post['id']] ?? [];
                    if (empty($images) && !empty($post['image_path'])) {
                        $images = [$post['image_path']];
                    }
                    $primary_image = $images[0] ?? '';
                ?>
                <div class="featured-card" data-region="<?php echo $post['region']; ?>" data-category="<?php echo $post['category']; ?>">
                    <div class="card-image-stack">
                        <?php foreach ($images as $image): ?>
                            <img src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                        <?php endforeach; ?>
                    </div>
                    <div class="featured-content">
                        <div class="featured-badge">Featured</div>
                        <div>
                            <span class="featured-category"><?php echo ucfirst($post['category']); ?></span>
                            <span class="featured-region"><?php echo ucfirst($post['region']); ?></span>
                        </div>
                        <h3 class="featured-title"><?php echo $post['title']; ?></h3>
                        <div class="featured-price" style="font-weight:bold;color:#ffb300;margin-bottom:0.5rem;">
                            <?php if(isset($post['price']) && $post['price'] !== null): ?>
                                $<?php echo number_format($post['price'], 2); ?>
                            <?php endif; ?>
                        </div>
                        <p class="featured-description"><?php echo substr($post['description'], 0, 120) . '...'; ?></p>
                       
                        <button class="view-details-btn"
                                data-post-id="<?php echo (int)$post['id']; ?>"
                                data-title="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-category="<?php echo htmlspecialchars($post['category'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-region="<?php echo htmlspecialchars($post['region'], ENT_QUOTES, 'UTF-8'); ?>"
                                onclick="trackViewDetailsClick(this); openModal('<?php echo addslashes($post['title']); ?>', '<?php echo addslashes($post['description']); ?>', '<?php echo addslashes($primary_image); ?>', '<?php echo $post['category']; ?>')">
                            View Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section">
        <h2 class="section-title">Our Services</h2>
        <p class="section-subtitle">Comprehensive travel solutions for the perfect Tanzanian adventure</p>
        
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-lion"></i>
                </div>
                <h3 class="service-title">Safari Tours</h3>
                <p class="service-description">Experience the ultimate wildlife adventure with our expertly guided safari tours through Tanzania's most famous national parks.</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-hotel"></i>
                </div>
                <h3 class="service-title">Luxury Accommodation</h3>
                <p class="service-description">Stay in handpicked luxury hotels and lodges that offer comfort, style, and authentic Tanzanian hospitality.</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-car"></i>
                </div>
                <h3 class="service-title">Transport Services</h3>
                <p class="service-description">Reliable and comfortable transportation options for all your travel needs across Tanzania and Zanzibar.</p>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div id="postModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <img id="modalImage" src="" alt="" class="modal-image">
            <div class="modal-body">
                <h2 id="modalTitle" class="modal-title"></h2>
                <span id="modalCategory" class="modal-category"></span>
                <p id="modalDescription" class="modal-description"></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
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
        
        // Close menu when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768) {
                if (!navbarMenu.contains(e.target) && !navbarToggle.contains(e.target)) {
                    navbarMenu.classList.remove('open');
                }
            }
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.responsive-navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(255, 179, 0, 0.98)';
                navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
            } else {
                navbar.style.background = 'rgba(255, 179, 0, 0.95)';
                navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
            }
        });
        
        function trackViewDetailsClick(buttonEl) {
            const payload = new URLSearchParams({
                event_type: 'visitor',
                action: 'view_details_click',
                post_id: buttonEl.getAttribute('data-post-id') || '',
                title: buttonEl.getAttribute('data-title') || '',
                category: buttonEl.getAttribute('data-category') || '',
                region: buttonEl.getAttribute('data-region') || '',
                source_page: 'home.php'
            });

            if (navigator.sendBeacon) {
                navigator.sendBeacon('track_event.php', payload);
                return;
            }

            fetch('track_event.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body: payload.toString(),
                keepalive: true
            }).catch(function () {});
        }

        // Modal functionality
        function openModal(title, description, image, category) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalDescription').textContent = description;
            document.getElementById('modalImage').src = image;
            document.getElementById('modalImage').alt = title;
            document.getElementById('modalCategory').textContent = category.charAt(0).toUpperCase() + category.slice(1);
            document.getElementById('postModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.getElementById('postModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        document.getElementById('postModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</body>
</html>
