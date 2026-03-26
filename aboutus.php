<?php
require_once 'config.php';

// Get company information from settings
$company_name = getSetting('company_name');
$contact_email = getSetting('contact_email');
$contact_phone = getSetting('contact_phone');
$company_address = getSetting('company_address');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - <?php echo $company_name; ?></title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    /* Include all your existing styles from transport.php */
    .responsive-navbar { /* Your navbar styles */ }
    .safari-footer { /* Your footer styles */ }
    
    .about-hero {
      background: linear-gradient(rgba(20, 83, 45, 0.8), rgba(20, 83, 45, 0.6)), url('photos/about-bg.jpg');
      background-size: cover;
      background-position: center;
      color: white;
      padding: 100px 20px;
      text-align: center;
      margin-top: 70px;
    }
    
    .about-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 3rem 2rem;
    }
    
    .about-section {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 18px rgba(0,0,0,0.12);
      padding: 2.5rem;
      margin-bottom: 2rem;
    }
    
    .team-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }
    
    .team-member {
      text-align: center;
      background: #f8f9fa;
      padding: 1.5rem;
      border-radius: 12px;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin: 2rem 0;
    }
    
    .stat-card {
      background: #14532d;
      color: white;
      padding: 1.5rem;
      border-radius: 12px;
      text-align: center;
    }
  </style>
</head>
<body>
  <!-- Responsive Navigation -->
  <nav class="responsive-navbar">
    <div class="navbar-container">
      <div class="navbar-logo">
        <img src="photos/download.jpg" alt="Company Logo" class="navbar-logo-img" />
      </div>
      <button class="navbar-toggle" aria-label="Toggle menu">
        <span class="bar"></span>
        <span class="bar"></span>
      </button>
      <ul class="navbar-menu">
        <li><a href="home.php">Home</a></li>
        <li><a href="aboutus.php">About Us</a></li>
        <li><a href="aboutzanzibar.php">About Zanzibar</a></li>
        <li class="dropdown">
          <a href="#">Our Tours &#9662;</a>
          <ul class="dropdown-content">
            <li><a href="packages.php">Packages</a></li>
          </ul>
        </li>
        <li><a href="safari.php">Safari</a></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="transport.php">Transport</a></li>
        <li><a href="hotel.php">Hotel</a></li>
        <li><a href="book.php">Book</a></li>
        <li><a href="contact.php">Contact Us</a></li>
        <li class="navbar-logout"><a href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="about-hero">
    <h1 style="font-size: 3rem; margin-bottom: 1rem;">About <?php echo $company_name; ?></h1>
    <p style="font-size: 1.3rem;">Crafting Unforgettable Journeys Across Tanzania</p>
  </section>

  <div class="about-content">
    <!-- Company Story -->
    <section class="about-section">
      <h2 style="color: #14532d; margin-bottom: 1.5rem;">Our Story</h2>
      <p style="font-size: 1.1rem; line-height: 1.7; color: #444;">
        Founded with a passion for showcasing the breathtaking beauty of Tanzania, <?php echo $company_name; ?> 
        has been creating exceptional travel experiences for over a decade. We believe that every journey 
        should be transformative, every safari should be awe-inspiring, and every beach getaway should 
        be rejuvenating.
      </p>
      
      <div class="stats-grid">
        <div class="stat-card">
          <h3 style="font-size: 2.5rem; margin: 0;">10+</h3>
          <p>Years Experience</p>
        </div>
        <div class="stat-card">
          <h3 style="font-size: 2.5rem; margin: 0;">5000+</h3>
          <p>Happy Travelers</p>
        </div>
        <div class="stat-card">
          <h3 style="font-size: 2.5rem; margin: 0;">50+</h3>
          <p>Tour Destinations</p>
        </div>
        <div class="stat-card">
          <h3 style="font-size: 2.5rem; margin: 0;">98%</h3>
          <p>Customer Satisfaction</p>
        </div>
      </div>
    </section>

    <!-- Mission & Vision -->
    <section class="about-section">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
          <h3 style="color: #14532d; margin-bottom: 1rem;">Our Mission</h3>
          <p>To provide authentic, sustainable, and unforgettable travel experiences that connect visitors with the natural wonders and rich cultures of Tanzania while supporting local communities and conservation efforts.</p>
        </div>
        <div>
          <h3 style="color: #14532d; margin-bottom: 1rem;">Our Vision</h3>
          <p>To be the leading tour operator in East Africa, recognized for excellence in service, commitment to sustainable tourism, and creating life-changing travel experiences.</p>
        </div>
      </div>
    </section>

    <!-- Values -->
    <section class="about-section">
      <h2 style="color: #14532d; margin-bottom: 1.5rem;">Our Values</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
        <div style="text-align: center; padding: 1.5rem;">
          <i class="fas fa-handshake" style="font-size: 3rem; color: #ffb300; margin-bottom: 1rem;"></i>
          <h4 style="color: #14532d;">Trust & Reliability</h4>
          <p>We build lasting relationships based on trust and deliver on our promises.</p>
        </div>
        <div style="text-align: center; padding: 1.5rem;">
          <i class="fas fa-leaf" style="font-size: 3rem; color: #ffb300; margin-bottom: 1rem;"></i>
          <h4 style="color: #14532d;">Sustainability</h4>
          <p>We're committed to eco-friendly practices and supporting local communities.</p>
        </div>
        <div style="text-align: center; padding: 1.5rem;">
          <i class="fas fa-heart" style="font-size: 3rem; color: #ffb300; margin-bottom: 1rem;"></i>
          <h4 style="color: #14532d;">Passion</h4>
          <p>We're passionate about sharing the beauty of Tanzania with the world.</p>
        </div>
        <div style="text-align: center; padding: 1.5rem;">
          <i class="fas fa-star" style="font-size: 3rem; color: #ffb300; margin-bottom: 1rem;"></i>
          <h4 style="color: #14532d;">Excellence</h4>
          <p>We strive for excellence in every aspect of our service.</p>
        </div>
      </div>
    </section>

    <!-- Team Section -->
    <section class="about-section">
      <h2 style="color: #14532d; margin-bottom: 1.5rem;">Meet Our Team</h2>
      <div class="team-grid">
        <div class="team-member">
          <img src="photos/team1.jpg" alt="Team Member" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem;">
          <h4>John Mwamba</h4>
          <p style="color: #ffb300; font-weight: bold;">Founder & CEO</p>
          <p>15+ years in tourism industry</p>
        </div>
        <div class="team-member">
          <img src="photos/team2.jpg" alt="Team Member" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem;">
          <h4>Sarah Juma</h4>
          <p style="color: #ffb300; font-weight: bold;">Operations Manager</p>
          <p>Expert in safari logistics</p>
        </div>
        <div class="team-member">
          <img src="photos/team3.jpg" alt="Team Member" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem;">
          <h4>David Kimambo</h4>
          <p style="color: #ffb300; font-weight: bold;">Head Guide</p>
          <p>Wildlife expert with 12 years experience</p>
        </div>
        <div class="team-member">
          <img src="photos/team4.jpg" alt="Team Member" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem;">
          <h4>Grace Mrosso</h4>
          <p style="color: #ffb300; font-weight: bold;">Customer Relations</p>
          <p>Ensuring your journey is perfect</p>
        </div>
      </div>
    </section>
  </div>

  <!-- Footer -->
  <footer class="safari-footer">
    <div class="footer-bg"></div>
    <div class="footer-content">
      <div class="footer-col company-info">
        <h3><?php echo $company_name; ?></h3>
        <p class="tagline">Crafting Unforgettable Journeys Across Tanzania.</p>
        <p class="naac">Part of the NAAC Group.</p>
      </div>
      <div class="footer-col quick-links">
        <h4>Explore</h4>
        <ul>
          <li><a href="home.php">Home</a></li>
          <li><a href="aboutus.php">About Us</a></li>
          <li><a href="packages.php">Safari Packages</a></li>
          <li><a href="contact.php">Contact</a></li>
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
          <li><i class="fas fa-phone"></i> <?php echo $contact_phone; ?></li>
          <li><i class="fas fa-envelope"></i> <?php echo $contact_email; ?></li>
          <li><i class="fas fa-map-marker-alt"></i> <?php echo $company_address; ?></li>
        </ul>
      </div>
    </div>
    <div class="footer-bar">
      © 2024 <?php echo $company_name; ?>. All rights reserved. A proud member of NAAC.
    </div>
  </footer>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
  <script>
    // Responsive Navbar JS
    const navbarToggle = document.querySelector('.navbar-toggle');
    const navbarMenu = document.querySelector('.navbar-menu');
    if (navbarToggle && navbarMenu) {
      navbarToggle.addEventListener('click', function() {
        navbarMenu.classList.toggle('open');
      });
    }
  </script>
</body>
</html>