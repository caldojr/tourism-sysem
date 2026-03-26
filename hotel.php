<?php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

// Get hotel posts
$query = "SELECT * FROM admin_posts WHERE category = 'hotel' ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hotel_ids = array_column($hotels, 'id');
$hotel_images = fetchPostImages($db, $hotel_ids);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Hotels & Accommodation - Nakupenda Tours & Safaris</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: 'Lato', Arial, sans-serif; }
        
        /* Navigation Styles */
        .responsive-navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #ffb300;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .navbar-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.8rem 1.5rem;
        }
        
        .navbar-logo-img {
            height: 50px;
            width: 50px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #ff8800;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            object-fit: cover;
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
            margin: 0 0.3rem;
        }
        
        .navbar-menu a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 0.7rem 1rem;
            border-radius: 6px;
            transition: background 0.3s;
            display: block;
        }
        
        .navbar-menu a:hover,
        .navbar-menu a.active {
            background: #ff8800;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #ffb300;
            min-width: 180px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 0 0 8px 8px;
            z-index: 10;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        .dropdown-content a {
            padding: 0.7rem 1rem;
            border-radius: 0;
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
            
            .dropdown-content {
                position: static;
                box-shadow: none;
                background: #ff8800;
                display: none;
            }
            
            .dropdown.open .dropdown-content {
                display: block;
            }
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
        
        /* Hotels Section */
        .hotels-section {
            padding: 4rem 1.5rem;
            background: #f8f9fa;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            color: #14532d;
            margin-bottom: 3rem;
        }
        
        .search-filter-container {
            max-width: 1200px;
            margin: 0 auto 4rem auto;
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            max-width: 500px;
        }
        
        .search-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid #ffb300;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #ff8800;
            box-shadow: 0 0 0 3px rgba(255, 179, 0, 0.2);
        }
        
        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        
        .filter-btn {
            background: #ffb300;
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .filter-btn.active {
            background: #ff8800;
            transform: scale(1.05);
        }
        
        .filter-btn:hover {
            background: #ff8800;
        }
        
        .hotels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .hotel-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .hotel-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .hotel-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffb300, #ff8800);
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

        .hotel-card:hover .card-image-stack img {
            transform: scale(1.02);
        }
        
        .hotel-content {
            padding: 2rem;
            position: relative;
        }
        
        .hotel-badge {
            position: absolute;
            top: -20px;
            right: 2rem;
            background: #ffb300;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(255, 179, 0, 0.3);
        }
        
        .hotel-region {
            display: inline-block;
            background: #14532d;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .hotel-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1a2a2a;
            margin-bottom: 1rem;
            line-height: 1.3;
        }
        
        .hotel-description {
            color: #666;
            line-height: 1.7;
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }
        
        .view-details-btn {
            width: 100%;
            background: #ffb300;
            color: #fff;
            border: none;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 1rem;
        }
        
        .view-details-btn:hover {
            background: #ff8800;
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
        
        .modal-region {
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
            
            .search-filter-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            .hotels-grid {
                grid-template-columns: 1fr;
            }
            
            .section-title {
                font-size: 2rem;
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
                <li><a href="hotel.php" class="active">Hotel</a></li>
                <li><a href="book.php">Book</a></li>
                <li><a href="order.php">My Bookings</a></li>
                <li><a href="aboutzanzibar.php">Zanzibar</a></li>
                <li><a href="gallery.html">Gallery</a></li>

            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Luxury Hotels & Accommodation</h1>
            <p>Experience unparalleled comfort in the finest hotels across Tanzania and Zanzibar</p>
            <a href="#hotel-packages" class="cta-button">Explore Luxury Hotels</a>
        </div>
    </section>

    <!-- Hotels Section -->
    <section class="hotels-section" id="hotel-packages">
        <h2 class="section-title">Our Premium Hotels</h2>
        
        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="Search hotels by title or description...">
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Hotels</button>
                <button class="filter-btn" data-filter="tanzania">Tanzania Mainland</button>
                <button class="filter-btn" data-filter="zanzibar">Zanzibar</button>
            </div>
        </div>
        
        <div class="hotels-grid" id="hotels-container">
            <?php foreach ($hotels as $hotel): ?>
                <?php
                    $images = $hotel_images[$hotel['id']] ?? [];
                    if (empty($images) && !empty($hotel['image_path'])) {
                        $images = [$hotel['image_path']];
                    }
                    $primary_image = $images[0] ?? '';
                ?>
                <div class="hotel-card" data-region="<?php echo $hotel['region']; ?>" data-title="<?php echo strtolower($hotel['title']); ?>" data-description="<?php echo strtolower($hotel['description']); ?>">
                    <div class="card-image-stack">
                        <?php foreach ($images as $image): ?>
                            <img src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($hotel['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                        <?php endforeach; ?>
                    </div>
                    <div class="hotel-content">
                        <div class="hotel-badge">Luxury</div>
                        <span class="hotel-region"><?php echo ucfirst($hotel['region']); ?></span>
                        <h3 class="hotel-title"><?php echo $hotel['title']; ?></h3>
                        <div class="hotel-price" style="font-weight:bold;color:#ffb300;margin-bottom:0.5rem;">
                            <?php if(isset($hotel['price']) && $hotel['price'] !== null): ?>
                                $<?php echo number_format($hotel['price'], 2); ?>
                            <?php endif; ?>
                        </div>
                        <p class="hotel-description"><?php echo substr($hotel['description'], 0, 120) . '...'; ?></p>
                       
                        <button class="view-details-btn"
                                data-post-id="<?php echo (int)$hotel['id']; ?>"
                                data-title="<?php echo htmlspecialchars($hotel['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-category="hotel"
                                data-region="<?php echo htmlspecialchars($hotel['region'], ENT_QUOTES, 'UTF-8'); ?>"
                                onclick="trackViewDetailsClick(this); openModal('<?php echo addslashes($hotel['title']); ?>', '<?php echo addslashes($hotel['description']); ?>', '<?php echo addslashes($primary_image); ?>', '<?php echo $hotel['region']; ?>')">
                            View Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Modal -->
    <div id="hotelModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <img id="modalImage" src="" alt="" class="modal-image">
            <div class="modal-body">
                <h2 id="modalTitle" class="modal-title"></h2>
                <span id="modalRegion" class="modal-region"></span>
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
        const dropdowns = document.querySelectorAll('.dropdown');
        
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('open');
        });
        
        // Close menu when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768) {
                if (!navbarMenu.contains(e.target) && !navbarToggle.contains(e.target)) {
                    navbarMenu.classList.remove('open');
                    dropdowns.forEach(dropdown => dropdown.classList.remove('open'));
                }
            }
        });
        
        // Mobile dropdown functionality
        dropdowns.forEach(dropdown => {
            const dropBtn = dropdown.querySelector('a');
            dropBtn.addEventListener('click', function(e) {
                if (window.innerWidth < 768) {
                    e.preventDefault();
                    dropdown.classList.toggle('open');
                }
            });
        });
        
        // Filter functionality
        const filterBtns = document.querySelectorAll('.filter-btn');
        const hotelCards = document.querySelectorAll('.hotel-card');
        
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                hotelCards.forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-region') === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
        
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            hotelCards.forEach(card => {
                const title = card.getAttribute('data-title');
                const description = card.getAttribute('data-description');
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        function trackViewDetailsClick(buttonEl) {
            const payload = new URLSearchParams({
                event_type: 'visitor',
                action: 'view_details_click',
                post_id: buttonEl.getAttribute('data-post-id') || '',
                title: buttonEl.getAttribute('data-title') || '',
                category: buttonEl.getAttribute('data-category') || '',
                region: buttonEl.getAttribute('data-region') || '',
                source_page: 'hotel.php'
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
        function openModal(title, description, image, region) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalDescription').textContent = description;
            document.getElementById('modalImage').src = image;
            document.getElementById('modalImage').alt = title;
            document.getElementById('modalRegion').textContent = region.charAt(0).toUpperCase() + region.slice(1);
            document.getElementById('hotelModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.getElementById('hotelModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        document.getElementById('hotelModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Add smooth scrolling
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
