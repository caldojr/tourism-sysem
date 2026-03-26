<?php
require_once 'config.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $message_type = $_POST['message_type'] ?? 'general';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, message_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message, $message_type]);
        
        $message_success = "Thank you for your message! We'll get back to you soon.";
    } catch(PDOException $e) {
        $message_error = "Message sending failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo getSetting('company_name'); ?></title>
    <!-- Include your styles -->
</head>
<body>
    <!-- Navigation -->
    
    <div class="dashboard-content" style="margin-top:70px;">
        <h1>Contact Us</h1>
        
        <?php if (isset($message_success)): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                <?php echo $message_success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" style="max-width: 600px; margin: 0 auto;">
            <input type="hidden" name="send_message" value="1">
            
            <input type="text" name="name" placeholder="Your Name" required style="width:100%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;">
            
            <input type="email" name="email" placeholder="Your Email" required style="width:100%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;">
            
            <input type="tel" name="phone" placeholder="Your Phone (Optional)" style="width:100%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;">
            
            <input type="text" name="subject" placeholder="Subject" required style="width:100%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;">
            
            <select name="message_type" style="width:100%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;">
                <option value="general">General Inquiry</option>
                <option value="booking_inquiry">Booking Inquiry</option>
                <option value="suggestion">Suggestion</option>
                <option value="complaint">Complaint</option>
            </select>
            
            <textarea name="message" placeholder="Your Message" required style="width:100%;padding:0.7rem;margin-bottom:1rem;border-radius:8px;border:1px solid #ccc;min-height:150px;"></textarea>
            
            <button type="submit" class="view-more-btn">Send Message</button>
        </form>
    </div>
    
    <!-- Footer -->
</body>
</html>