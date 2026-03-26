<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: admin_dashboard.php');
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            header('Location: admin_dashboard.php');
            exit();
        } else {
            $login_error = "Invalid username or password";
        }
    } catch(PDOException $e) {
        $login_error = "Login failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - <?php echo getSetting('company_name'); ?></title>
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
    }
    
    .login-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
      padding: 3rem;
      width: 100%;
      max-width: 400px;
    }
    
    .login-logo {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .login-logo img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      border: 3px solid #ffb300;
    }
    
    .login-title {
      color: #14532d;
      text-align: center;
      margin-bottom: 0.5rem;
      font-size: 1.8rem;
    }
    
    .login-subtitle {
      color: #666;
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
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
    
    .login-btn {
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
    
    .login-btn:hover {
      background: #ff8800;
    }
    
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 0.8rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      text-align: center;
    }
    
    .links-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid #e2e8f0;
    }
    
    .links-container a {
      color: #14532d;
      text-decoration: none;
      font-weight: 600;
    }
    
    .links-container a:hover {
      text-decoration: underline;
    }
    
    .signup-link {
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-logo">
      <img src="photos/download.jpg" alt="Company Logo">
    </div>
    
    <h1 class="login-title">Admin Login</h1>
    <p class="login-subtitle"><?php echo getSetting('company_name'); ?></p>
    
    <?php if (isset($login_error)): ?>
      <div class="error-message">
        <?php echo $login_error; ?>
      </div>
    <?php endif; ?>
    
    <form method="POST">
      <input type="hidden" name="login" value="1">
      
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-input" required 
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
      </div>
      
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" required>
      </div>
      
      <button type="submit" class="login-btn">Login</button>
    </form>
    
    <div class="links-container">
      <a href="home.php">← Back to Main Site</a>
      <a href="signup.php">Create Account</a>
    </div>
  </div>
</body>
</html>