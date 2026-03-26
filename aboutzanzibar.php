<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Zanzibar - Nakupenda Tours & Safaris</title>
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

    /* Main Content Styles */
    .dashboard-content {
        margin-top: 70px;
        padding: 2rem 1rem;
    }

    .dash-section {
        font-family: 'Lato', Arial, sans-serif;
        background: rgba(255,255,255,0.97);
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        padding: 2.5rem 2rem;
        max-width: 1200px;
        margin: 0 auto 3rem auto;
    }

    .dash-section h1 {
        color: #ff8800;
        font-size: 2.8rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .dash-section h2 {
        color: #ffb300;
        font-size: 2rem;
        font-weight: 700;
        margin: 3rem 0 1.5rem 0;
        border-bottom: 3px solid #ffb300;
        padding-bottom: 0.5rem;
    }

    .dash-section h3 {
        color: #14532d;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 2rem 0 1rem 0;
    }

    .history-list {
        color: #14532d;
        font-size: 1.1rem;
        margin-bottom: 2.5rem;
        line-height: 1.7;
    }

    .history-list li {
        margin-bottom: 1rem;
        padding-left: 1rem;
    }

    .attractions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .attraction-category {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 12px;
        border-left: 5px solid #ffb300;
    }

    .attraction-category ul {
        color: #222;
        font-size: 1.05rem;
        list-style: none;
    }

    .attraction-category li {
        margin-bottom: 0.8rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
    }

    .attraction-category li:before {
        content: "✓";
        color: #ffb300;
        font-weight: bold;
        margin-right: 0.8rem;
        font-size: 1.2rem;
    }

    .attraction-category li:last-child {
        border-bottom: none;
    }

    /* Admin Posts Styles */
    #admin-posts-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        margin: 2rem 0;
    }

    .attraction-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.12);
        padding: 1.7rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 420px;
        transition: transform 0.3s ease;
    }

    .attraction-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .view-more-btn {
        background: #ffb300;
        color: #fff;
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.3s;
        margin-bottom: 0.7rem;
        font-size: 1.08rem;
    }

    .view-more-btn:hover {
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
        max-width: 600px;
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

    .modal-body {
        padding: 2.5rem;
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
            font-size: 2.8rem;
        }
        
        .dash-section {
            padding: 1.5rem 1rem;
        }
        
        .dash-section h1 {
            font-size: 2.2rem;
        }
        
        .dash-section h2 {
            font-size: 1.6rem;
        }
        
        .attractions-grid {
            grid-template-columns: 1fr;
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
        
        #admin-posts-list {
            grid-template-columns: 1fr;
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
                <li><a href="aboutzanzibar.php" class="active">Zanzibar</a></li>
                <li><a href="gallery.php">Gallery</a></li>
            </ul>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="hero-content">
      <h1>Discover Zanzibar</h1>
      <p>The Spice Island with rich history, pristine beaches, and vibrant culture</p>
      <a href="#zanzibar-content" class="cta-button">Explore Zanzibar</a>
    </div>
  </section>

  <!-- Main Content -->
  <div class="dashboard-content" id="zanzibar-content">
    <!-- Admin Posts Section -->
    <div id="admin-posts-list"></div>

    <!-- Zanzibar Information Section -->
    <div class="dash-section">
      <h1>🌴 About Zanzibar: A Brief History</h1>
      <ol class="history-list">
        <li><b>Early Inhabitants</b> – People from Bantu, Arab, and Persian origins settled on the islands over 2,000 years ago.</li>
        <li><b>Trade Hub</b> – By the 8th century, Zanzibar became a center for Indian Ocean trade, famous for spices (cloves, cinnamon) and ivory.</li>
        <li><b>Omani Arab Rule</b> – In the 17th century, the Omani Arabs took control; Stone Town flourished as a trading city.</li>
        <li><b>Slave Trade Era</b> – Zanzibar became a key hub in the East African slave trade during the 18th and 19th centuries.</li>
        <li><b>Sultanate of Zanzibar</b> – In 1856, Zanzibar became its own sultanate under Omani rule.</li>
        <li><b>British Protectorate</b> – Late 19th century, Britain established a protectorate; slavery was abolished in 1897.</li>
        <li><b>Independence & Revolution</b> – Zanzibar gained independence in 1963; a revolution in 1964 led to the Zanzibar Republic, which later united with Tanganyika to form Tanzania.</li>
        <li><b>Modern Zanzibar</b> – Today, Zanzibar is semi-autonomous, famous for tourism, spices, and cultural heritage.</li>
      </ol>

      <h2>🏝️ Complete List of Zanzibar Attractions</h2>
      
      <div class="attractions-grid">
        <div class="attraction-category">
          <h3>🏛️ Stone Town & History</h3>
          <ul>
            <li>Stone Town Tour – UNESCO World Heritage site</li>
            <li>House of Wonders (Beit al-Ajaib)</li>
            <li>Old Fort (Ngome Kongwe)</li>
            <li>Forodhani Night Market – Evening street food by the sea</li>
            <li>Darajani Market – Local food & spice market</li>
            <li>Palace Museum (Sultan's Palace)</li>
            <li>Freddie Mercury House – Birthplace of the music legend</li>
            <li>Slave Market & Anglican Cathedral – Learn about the slave trade history</li>
            <li>Maruhubi Palace Ruins</li>
            <li>Mtoni Palace Ruins</li>
            <li>Mbweni Ruins & Botanical Gardens</li>
            <li>Mangapwani Slave Caves & Chambers</li>
          </ul>
        </div>

        <div class="attraction-category">
          <h3>🏖️ Beaches & North Coast</h3>
          <ul>
            <li>Nungwi Beach – Beautiful sunsets & lively nightlife</li>
            <li>Kendwa Beach – Known for Full Moon parties</li>
            <li>Mnarani Aquarium (Nungwi Turtle Center) – Sea turtle conservation</li>
            <li>Kiwengwa Beach – Long, scenic beach with many resorts</li>
            <li>Salama Cave (Kiwengwa) – Hidden cave with natural freshwater pool</li>
            <li>Muyuni Beach – Secluded and quiet</li>
            <li>Matemwe Beach – Close to Mnemba Island, good for snorkeling</li>
          </ul>
        </div>

        <div class="attraction-category">
          <h3>🏝️ Islands & Water Tours</h3>
          <ul>
            <li>Prison Island (Changuu Island) – Giant tortoises & history</li>
            <li>Nakupenda Sandbank – White sandbank in the middle of the ocean</li>
            <li>Mnemba Atoll – The best snorkeling and diving spot</li>
            <li>Chumbe Island – Marine park with coral reef conservation</li>
            <li>Bawe Island – Relaxing and quiet getaway</li>
            <li>Chapwani Island (Grave Island) – Colonial history site</li>
          </ul>
        </div>

        <div class="attraction-category">
          <h3>🌿 Nature & Eco Tours</h3>
          <ul>
            <li>Spice Farms (Spice Tour) – Taste and learn about Zanzibar spices</li>
            <li>Jozani Forest – Home of the rare Red Colobus monkeys</li>
            <li>Pete Village Boardwalk – Mangrove forest walk</li>
            <li>Butterfly Centre (Pete) – Butterfly sanctuary</li>
            <li>Seaweed Center (Paje) – Learn about seaweed farming and women projects</li>
          </ul>
        </div>

        <div class="attraction-category">
          <h3>🐬 South Coast & Special Spots</h3>
          <ul>
            <li>Kizimkazi Dolphin Tour – Swim with dolphins</li>
            <li>The Rock Restaurant (Michamvi) – Iconic restaurant in the sea</li>
            <li>Bwejuu & Paje Beaches – Kitesurfing & water sports paradise</li>
            <li>Jambiani Village Tour – Experience local village life</li>
            <li>Makunduchi Village – Famous for Mwaka Kogwa cultural festival</li>
          </ul>
        </div>

        <div class="attraction-category">
          <h3>🌅 Unique Experiences & Activities</h3>
          <ul>
            <li>Sunset Dhow Cruise – Traditional sailing boat at sunset</li>
            <li>Sandbank Seafood BBQ – Fresh seafood lunch on a sandbank</li>
            <li>Deep Sea Fishing Trips</li>
            <li>Horse Riding (Nungwi / Paje)</li>
            <li>Scuba Diving & Snorkeling Trips</li>
            <li>Swahili Cooking Classes</li>
            <li>Birthday / Honeymoon Packages</li>
            <li>Traditional Dance Shows & Taarab Nights</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Admin Posts -->
  <div id="admin-post-modal" class="modal">
    <div class="modal-content">
      <button class="modal-close" onclick="closeAdminModal()">&times;</button>
      <div class="modal-body" id="admin-modal-body">
        <!-- Modal content will be inserted here -->
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

    // Admin Posts Functionality
    function renderAdminPosts() {
        var posts = JSON.parse(localStorage.getItem('postedContent') || '[]');
        var list = document.getElementById('admin-posts-list');
        if (!list) return;
        
        list.innerHTML = '';
        
        posts.forEach(function(post) {
            if (post.target === 'aboutzanzibar.html' || post.target === 'aboutzanzibar.php') {
                var card = document.createElement('div');
                card.className = 'attraction-card';
                
                let photoHtml = post.photo ? 
                    `<img src="${post.photo}" alt="${post.title}" style="width:100%;max-width:340px;height:220px;object-fit:cover;border-radius:12px;margin-bottom:1.2rem;box-shadow:0 4px 18px rgba(0,0,0,0.12);" />` : '';
                
                let titleHtml = `<h3 style="color:#14532d;font-size:1.35rem;font-weight:800;margin-bottom:0.7rem;text-align:center;word-break:break-word;">${post.title}</h3>`;
                let buttonHtml = `<button class="view-more-btn" onclick="showAdminPostModal('${post.title.replace(/'/g, "\\'")}', '${post.description.replace(/'/g, "\\'")}', '${post.photo || ''}')">View More</button>`;
                
                card.innerHTML = photoHtml + titleHtml + buttonHtml;
                list.appendChild(card);
            }
        });
    }

    function showAdminPostModal(title, description, photo) {
        var modalHtml = `<h2 style='color:#14532d;text-align:center;margin-bottom:1.5rem;font-size:1.8rem;font-weight:800;'>${title}</h2>`;
        
        if (photo) {
            modalHtml += `<img src='${photo}' alt='${title}' style='width:100%;max-width:400px;height:250px;object-fit:cover;border-radius:12px;margin-bottom:1.5rem;box-shadow:0 4px 18px rgba(0,0,0,0.12);display:block;margin-left:auto;margin-right:auto;' />`;
        }
        
        modalHtml += `<div style='font-size:1.12rem;color:#444;text-align:left;margin-bottom:1.5rem;white-space:pre-line;word-break:break-word;line-height:1.6;'>${description}</div>`;
        
        document.getElementById('admin-modal-body').innerHTML = modalHtml;
        document.getElementById('admin-post-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeAdminModal() {
        document.getElementById('admin-post-modal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('admin-post-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAdminModal();
        }
    });

    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        renderAdminPosts();
    });

    // Listen for storage changes (if other tabs update posts)
    window.addEventListener('storage', renderAdminPosts);
  </script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</body>
</html>
