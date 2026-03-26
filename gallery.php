<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Nakupenda Tours & Safaris</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
            background-color: #f8f8f8;
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

        /* Gallery Page Styles */
        .page-header {
            margin-top: 80px;
            padding: 3rem 2rem;
            text-align: center;
            background: #fff;
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: #14532d;
            margin-bottom: 1rem;
        }

        .gallery-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .gallery-section { 
            margin-bottom: 3rem; 
        }
        
        .gallery-section h2 { 
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #ffb300;
            display: inline-block;
        }
        
        .images { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 20px; 
        }
        
        .images img { 
            width: 100%;
            max-width: 400px; 
            height: 300px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
            background: #fff; 
            padding: 5px; 
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .images img:hover {
            transform: scale(1.03);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
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

            .safari-footer .footer-content {
                flex-wrap: wrap;
                padding: 2rem 1rem 1rem 1rem;
            }
            
            .safari-footer .footer-col {
                margin: 0 0.7rem 1.2rem 0;
            }
            
            .images {
                justify-content: center;
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
                <li><a href="order.php">My Bookings</a></li>
                <li><a href="aboutzanzibar.php">Zanzibar</a></li>
                <li><a href="gallery.php" class="active">Gallery</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1>Gallery</h1>
    </div>

    <!-- Gallery Content -->
    <div class="gallery-container">
        <div class="gallery-section">
            <h2>Kendwa Beach</h2>
            <div class="images">
                <img src="photos/Kendwa%20Beach/kendwa01.jpg" alt="kendwa01.jpg">
                <img src="photos/Kendwa%20Beach/kendwa02.jpg" alt="kendwa02.jpg">
                <img src="photos/Kendwa%20Beach/kendwa03.jpg" alt="kendwa03.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>Mikumi</h2>
            <div class="images">
                <img src="photos/mikumi/mamba.jpg" alt="mamba.jpg">
                <img src="photos/mikumi/masai.jpg" alt="masai.jpg">
                <img src="photos/mikumi/mikumi01.jfif" alt="mikumi01.jfif">
                <img src="photos/mikumi/mikumi02.jfif" alt="mikumi02.jfif">
                <img src="photos/mikumi/mikumi03.jpg" alt="mikumi03.jpg">
                <img src="photos/mikumi/mikumi04.jpg" alt="mikumi04.jpg">
                <img src="photos/mikumi/mikumi05.jpg" alt="mikumi05.jpg">
                <img src="photos/mikumi/ndege.jpg" alt="ndege.jpg">
                <img src="photos/mikumi/zebra.jpg" alt="zebra.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>Nungwi</h2>
            <div class="images">
                <img src="photos/nungwi/nungwi01.jpg" alt="nungwi01.jpg">
                <img src="photos/nungwi/nungwi02.jpg" alt="nungwi02.jpg">
                <img src="photos/nungwi/nungwi03.jpg" alt="nungwi03.jpg">
                <img src="photos/nungwi/nungwi04.jpg" alt="nungwi04.jpg">
                <img src="photos/nungwi/nungwi5.jpg" alt="nungwi5.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>Ruaha</h2>
            <div class="images">
                <img src="photos/Ruaha/impara.jpg" alt="impara.jpg">
                <img src="photos/Ruaha/ruaha.jpg" alt="ruaha.jpg">
                <img src="photos/Ruaha/ruaha01.jpg" alt="ruaha01.jpg">
                <img src="photos/Ruaha/ruaha02.jpg" alt="ruaha02.jpg">
                <img src="photos/Ruaha/ruaha03.jpg" alt="ruaha03.jpg">
                <img src="photos/Ruaha/ruaha04.jpg" alt="ruaha04.jpg">
                <img src="photos/Ruaha/ruaha05.jpg" alt="ruaha05.jpg">
                <img src="photos/Ruaha/ruaha06.jpg" alt="ruaha06.jpg">
                <img src="photos/Ruaha/tembo.jpg" alt="tembo.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>Butterfly</h2>
            <div class="images">
                <img src="photos/butterfly/butter.jpg" alt="butter.jpg">
                <img src="photos/butterfly/butter01.jpg" alt="butter01.jpg">
                <img src="photos/butterfly/butter02.jpg" alt="butter02.jpg">
                <img src="photos/butterfly/butter03.jpg" alt="butter03.jpg">
                <img src="photos/butterfly/butter04.jpg" alt="butter04.jpg">
                <img src="photos/butterfly/butter05.jpg" alt="butter05.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>House of Wonders</h2>
            <div class="images">
                <img src="photos/house%20of%20wonders/house%2001.jpg" alt="house 01.jpg">
                <img src="photos/house%20of%20wonders/house02.jpg" alt="house02.jpg">
                <img src="photos/house%20of%20wonders/house03.jpg" alt="house03.jpg">
                <img src="photos/house%20of%20wonders/house04.jpg" alt="house04.jpg">
                <img src="photos/house%20of%20wonders/house05.jpg" alt="house05.jpg">
                <img src="photos/house%20of%20wonders/house9.jpg" alt="house9.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>Kilimanjaro</h2>
            <div class="images">
                <img src="photos/kilimanjaro/kilimanjaro.jpg" alt="kilimanjaro.jpg">
                <img src="photos/kilimanjaro/kilimanjaro01.jpg" alt="kilimanjaro01.jpg">
                <img src="photos/kilimanjaro/kilimanjaro02.jpg" alt="kilimanjaro02.jpg">
                <img src="photos/kilimanjaro/kilimanjaro03.jpg" alt="kilimanjaro03.jpg">
                <img src="photos/kilimanjaro/kilimanjaro04.jpg" alt="kilimanjaro04.jpg">
                <img src="photos/kilimanjaro/kilimanjaro05.jpg" alt="kilimanjaro05.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>Old Fort</h2>
            <div class="images">
                <img src="photos/old%20fort/fort.jpg" alt="fort.jpg">
                <img src="photos/old%20fort/fort01.jpg" alt="fort01.jpg">
                <img src="photos/old%20fort/fort03.jpg" alt="fort03.jpg">
                <img src="photos/old%20fort/fort04.jpg" alt="fort04.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>Prison Island</h2>
            <div class="images">
                <img src="photos/prison%20island/prison.jpg" alt="prison.jpg">
                <img src="photos/prison%20island/prison01.jpg" alt="prison01.jpg">
                <img src="photos/prison%20island/prison02.jpg" alt="prison02.jpg">
                <img src="photos/prison%20island/prison03.jpg" alt="prison03.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>Serengeti</h2>
            <div class="images">
                <img src="photos/serengeti/beast01.jpg" alt="beast01.jpg">
                <img src="photos/serengeti/est.jpg" alt="est.jpg">
                <img src="photos/serengeti/nyumbu.jpg" alt="nyumbu.jpg">
                <img src="photos/serengeti/tembo.jpg" alt="tembo.jpg">
                <img src="photos/serengeti/wildbeast.jpg" alt="wildbeast.jpg">
                <img src="photos/serengeti/zebra.jpg" alt="zebra.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>Spice Farm</h2>
            <div class="images">
                <img src="photos/spice%20farm/spice.jpg" alt="spice.jpg">
                <img src="photos/spice%20farm/spice01.jpg" alt="spice01.jpg">
            </div>
        </div>
        <div class="gallery-section">
            <h2>Stone Town</h2>
            <div class="images">
                <img src="photos/stone%20town/cannon.jpg" alt="cannon.jpg">
                <img src="photos/stone%20town/house.jpg" alt="house.jpg">
                <img src="photos/stone%20town/stone01.jpg" alt="stone01.jpg">
                <img src="photos/stone%20town/stone02.jpg" alt="stone02.jpg">
                <img src="photos/stone%20town/stone03.jpg" alt="stone03.jpg">
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
    </script>
</body>
</html>
