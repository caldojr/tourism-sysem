<?php
require_once 'config.php';

// Get all active tours
$stmt = $pdo->prepare("SELECT * FROM safari_tours WHERE is_active = 1 ORDER BY price_per_person ASC");
$stmt->execute();
$tours = $stmt->fetchAll();

// Handle tour booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_tour'])) {
    $tour_id = $_POST['tour_id'];
    $booking_date = $_POST['booking_date'];
    $number_of_people = $_POST['number_of_people'];
    $contact_email = $_POST['contact_email'];
    $contact_phone = $_POST['contact_phone'];
    $guest_names = json_encode([$_POST['guest_name']]); // Simple array for single guest
    $special_requests = $_POST['special_requests'] ?? '';
    
    // Calculate total amount
    $tour_stmt = $pdo->prepare("SELECT price_per_person FROM safari_tours WHERE tour_id = ?");
    $tour_stmt->execute([$tour_id]);
    $tour = $tour_stmt->fetch();
    $total_amount = $tour['price_per_person'] * $number_of_people;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tour_bookings (tour_id, booking_date, number_of_people, total_amount, guest_names, contact_email, contact_phone, special_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tour_id, $booking_date, $number_of_people, $total_amount, $guest_names, $contact_email, $contact_phone, $special_requests]);
        
        $booking_success = "Thank you! Your tour booking has been received. Total amount: $" . number_format($total_amount, 2);
    } catch(PDOException $e) {
        $booking_error = "Booking failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tour Packages - <?php echo getSetting('company_name'); ?></title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .packages-hero {
      background: linear-gradient(rgba(20, 83, 45, 0.8), rgba(20, 83, 45, 0.6)), url('photos/packages-bg.jpg');
      background-size: cover;
      background-position: center;
      color: white;
      padding: 100px 20px;
      text-align: center;
      margin-top: 70px;
    }
    
    .packages-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 3rem 2rem;
    }
    
    .tours-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 2rem;
      margin: 2rem 0;
    }
    
    .tour-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 18px rgba(0,0,0,0.12);
      overflow: hidden;
      transition: transform 0.3s;
    }
    
    .tour-card:hover {
      transform: translateY(-5px);
    }
    
    .tour-header {
      background: #14532d;
      color: white;
      padding: 1.5rem;
      text-align: center;
    }
    
    .tour-price {
      font-size: 2rem;
      font-weight: bold;
      color: #ffb300;
    }
    
    .tour-details {
      padding: 1.5rem;
    }
    
    .included-list, .excluded-list {
      list-style: none;
      padding: 0;
    }
    
    .included-list li:before {
      content: '✓ ';
      color: green;
      font-weight: bold;
    }
    
    .excluded-list li:before {
      content: '✗ ';
      color: red;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="responsive-navbar">
    <!-- Include navigation code -->
  </nav>

  <!-- Hero Section -->
  <section class="packages-hero">
    <h1 style="font-size: 3rem; margin-bottom: 1rem;">Safari Tour Packages</h1>
    <p style="font-size: 1.3rem;">Experience the Best of Tanzania with Our Curated Tours</p>
  </section>

  <div class="packages-content">
    <!-- Success/Error Messages -->
    <?php if (isset($booking_success)): ?>
      <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
        <?php echo $booking_success; ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($booking_error)): ?>
      <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
        <?php echo $booking_error; ?>
      </div>
    <?php endif; ?>

    <!-- Tours Grid -->
    <div class="tours-grid">
      <?php foreach ($tours as $tour): ?>
        <div class="tour-card">
          <div class="tour-header">
            <h3><?php echo htmlspecialchars($tour['name']); ?></h3>
            <div class="tour-price">$<?php echo number_format($tour['price_per_person'], 2); ?></div>
            <p style="margin: 0.5rem 0;">per person</p>
          </div>
          
          <img src="<?php echo $tour['image_url']; ?>" alt="<?php echo htmlspecialchars($tour['name']); ?>" style="width:100%;height:200px;object-fit:cover;">
          
          <div class="tour-details">
            <p><strong>Duration:</strong> <?php echo $tour['duration_days']; ?> days</p>
            <p><strong>Difficulty:</strong> <?php echo ucfirst($tour['difficulty_level']); ?></p>
            
            <h4 style="color: #14532d; margin: 1rem 0 0.5rem 0;">Destinations:</h4>
            <p><?php 
              $destinations = json_decode($tour['destinations'], true);
              echo is_array($destinations) ? implode(', ', $destinations) : $tour['destinations'];
            ?></p>
            
            <h4 style="color: #14532d; margin: 1rem 0 0.5rem 0;">Included:</h4>
            <p><?php echo nl2br(htmlspecialchars($tour['included_services'])); ?></p>
            
            <button class="view-more-btn" onclick="showTourModal(<?php echo $tour['tour_id']; ?>)" style="width:100%;margin-top:1rem;">Book This Tour</button>
          </div>
        </div>
      <?php endforeach; ?>
      
      <?php if (empty($tours)): ?>
        <div style="text-align:center;color:#666;grid-column:1/-1;">
          <p>No tour packages available at the moment.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tour Booking Modal -->
  <div id="tour-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;max-width:500px;width:97vw;padding:2.2rem 1.3rem 1.3rem 1.3rem;position:relative;box-shadow:0 2px 24px rgba(0,0,0,0.18);max-height:90vh;overflow-y:auto;">
      <button onclick="closeTourModal()" style="position:absolute;top:1rem;right:1.2rem;background:none;border:none;font-size:2rem;color:#ffb300;cursor:pointer;">&times;</button>
      <div id="tour-modal-details"></div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="safari-footer">
    <!-- Include footer code -->
  </footer>

  <script>
    function showTourModal(tourId) {
      // In a real application, you would fetch tour details via AJAX
      const formHtml = `
        <form method="POST" style="text-align:center;">
          <input type="hidden" name="tour_id" value="${tourId}">
          <input type="hidden" name="book_tour" value="1">
          <h3 style="color:#14532d;margin-bottom:1.5rem;">Book Your Safari Tour</h3>
          
          <input type="date" name="booking_date" required style="width:95%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;">
          <input type="number" name="number_of_people" required min="1" placeholder="Number of People" style="width:95%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;">
          <input type="text" name="guest_name" required placeholder="Primary Guest Name" style="width:95%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;">
          <input type="email" name="contact_email" required placeholder="Email Address" style="width:95%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;">
          <input type="tel" name="contact_phone" required placeholder="Phone Number" style="width:95%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;">
          <textarea name="special_requests" placeholder="Special Requests or Requirements" style="width:95%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;min-height:100px;"></textarea>
          
          <button type="submit" class="view-more-btn" style="width:95%;">Complete Booking</button>
        </form>
      `;
      
      document.getElementById('tour-modal-details').innerHTML = formHtml;
      document.getElementById('tour-modal').style.display = 'flex';
    }
    
    function closeTourModal() {
      document.getElementById('tour-modal').style.display = 'none';
    }

    // Close modal when clicking outside
    document.getElementById('tour-modal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeTourModal();
      }
    });

    // Navbar functionality
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