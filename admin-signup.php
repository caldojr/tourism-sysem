<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: admin_dashboard.php');
    exit();
}

// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    
    // Validation
    $errors = [];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    // Check if username or email already exists
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username or email already exists";
        }
    } catch(PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
    
    // If no errors, create user
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin'; // Default role for signup
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash, $full_name, $phone, $role]);
            
            // Auto-login after signup
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['full_name'] = $full_name;
            
            $signup_success = "Account created successfully! Redirecting to dashboard...";
            header("Refresh: 2; URL=admin_dashboard.php");
            
        } catch(PDOException $e) {
            $errors[] = "Failed to create account: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sign Up - <?php echo getSetting('company_name'); ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Lato', Arial, sans-serif;
      background: linear-gradient(135deg, #14532d 0%, #ffb300 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    
    .signup-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
      padding: 3rem;
      width: 100%;
      max-width: 500px;
    }
    
    .signup-logo {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .signup-logo img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      border: 3px solid #ffb300;
    }
    
    .signup-title {
      color: #14532d;
      text-align: center;
      margin-bottom: 0.5rem;
      font-size: 1.8rem;
    }
    
    .signup-subtitle {
      color: #666;
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      color: #14532d;
      font-weight: 600;
    }
    
    .form-input {
      width: 100%;
      padding: 0.8rem 1rem;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }
    
    .form-input:focus {
      outline: none;
      border-color: #ffb300;
    }
    
    .signup-btn {
      width: 100%;
      background: #ffb300;
      color: white;
      border: none;
      padding: 1rem;
      border-radius: 10px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
      margin-bottom: 1.5rem;
    }
    
    .signup-btn:hover {
      background: #ff8800;
    }
    
    .success-message {
      background: #d4edda;
      color: #155724;
      padding: 0.8rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      text-align: center;
    }
    
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 0.8rem;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
    
    .error-list {
      list-style: none;
      padding: 0;
    }
    
    .error-list li {
      margin-bottom: 0.5rem;
      padding-left: 1rem;
      position: relative;
    }
    
    .error-list li:before {
      content: '⚠';
      position: absolute;
      left: 0;
    }
    
    .login-link {
      text-align: center;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid #e2e8f0;
    }
    
    .login-link a {
      color: #14532d;
      text-decoration: none;
      font-weight: 600;
    }
    
    .login-link a:hover {
      text-decoration: underline;
    }
    
    .password-requirements {
      font-size: 0.85rem;
      color: #666;
      margin-top: 0.25rem;
    }
    
    @media (max-width: 600px) {
      .form-row {
        grid-template-columns: 1fr;
      }
      
      .signup-container {
        padding: 2rem 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="signup-container">
    <div class="signup-logo">
      <img src="photos/download.jpg" alt="Company Logo">
    </div>
    
    <h1 class="signup-title">Admin Sign Up</h1>
    <p class="signup-subtitle">Create your admin account for <?php echo getSetting('company_name'); ?></p>
    
    <?php if (isset($signup_success)): ?>
      <div class="success-message">
        <?php echo $signup_success; ?>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
      <div class="error-message">
        <ul class="error-list">
          <?php foreach ($errors as $error): ?>
            <li><?php echo $error; ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    
    <form method="POST">
      <input type="hidden" name="signup" value="1">
      
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Username *</label>
          <input type="text" name="username" class="form-input" required 
                 value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>
        
        <div class="form-group">
          <label class="form-label">Email *</label>
          <input type="email" name="email" class="form-input" required
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-input"
                 value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
        </div>
        
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="tel" name="phone" class="form-input"
                 value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
        </div>
      </div>
      
      <div class="form-group">
        <label class="form-label">Password *</label>
        <input type="password" name="password" class="form-input" required minlength="6">
        <div class="password-requirements">Must be at least 6 characters long</div>
      </div>
      
      <div class="form-group">
        <label class="form-label">Confirm Password *</label>
        <input type="password" name="confirm_password" class="form-input" required minlength="6">
      </div>
      
      <button type="submit" class="signup-btn">Create Admin Account</button>
    </form>
    
    <div class="login-link">
      Already have an account? <a href="login.php">Login here</a>
    </div>
  </div>

  <script>
    // Password confirmation validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const password = document.querySelector('input[name="password"]');
      const confirmPassword = document.querySelector('input[name="confirm_password"]');
      
      if (password.value !== confirmPassword.value) {
        e.preventDefault();
        alert('Passwords do not match!');
        confirmPassword.focus();
      }
    });

    // Real-time password match indicator
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
      input.addEventListener('input', checkPasswordMatch);
    });

    function checkPasswordMatch() {
      const password = document.querySelector('input[name="password"]');
      const confirmPassword = document.querySelector('input[name="confirm_password"]');
      
      if (password.value && confirmPassword.value) {
        if (password.value === confirmPassword.value) {
          confirmPassword.style.borderColor = '#28a745';
        } else {
          confirmPassword.style.borderColor = '#dc3545';
        }
      }
    }
  </script>
</body>
</html>