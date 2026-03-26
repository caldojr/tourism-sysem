[file name]: profile.php
[file content begin]
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get admin data
$admin_id = $_SESSION['admin_id'];
$query = "SELECT * FROM admin_users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Get deleted posts count for recycle bin
$recycle_count_query = "SELECT COUNT(*) as count FROM admin_posts WHERE deleted = 1";
$recycle_stmt = $db->prepare($recycle_count_query);
$recycle_stmt->execute();
$recycle_count = $recycle_stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Handle form submission
if ($_POST && isset($_POST['full_name'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $country = $_POST['country'];
    $phone_number = $_POST['phone_number'];
    $gender = $_POST['gender'];
    
    // Handle password update
    $password_update = '';
    $params = [$full_name, $email, $country, $phone_number, $gender, $admin_id];
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_update = ", password = ?";
        $params = [$full_name, $email, $country, $phone_number, $gender, $password, $admin_id];
    }
    
    $query = "UPDATE admin_users SET full_name = ?, email = ?, country = ?, phone_number = ?, gender = ? $password_update WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute($params)) {
        $success = "Profile updated successfully!";
        logSystemActivity(
            $db,
            'admin_profile',
            !empty($_POST['password']) ? 'profile_update_with_password_change' : 'profile_update',
            !empty($_POST['password']) ? 'Admin profile and password updated.' : 'Admin profile updated.',
            'admin',
            (int)$admin_id,
            $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin'),
            'admin_account',
            (string)$admin_id
        );
        // Refresh admin data
        $query = "SELECT * FROM admin_users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Failed to update profile!";
        logSystemActivity(
            $db,
            'admin_profile',
            'profile_update_failed',
            'Failed to update admin profile.',
            'admin',
            (int)$admin_id,
            $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin'),
            'admin_account',
            (string)$admin_id
        );
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Nakupenda Tours</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: 'Lato', Arial, sans-serif; background: #f7f7f7; }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1a2a2a 0%, #2d4a4a 100%);
            color: white;
            padding: 2rem 0;
            transition: transform 0.3s ease;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 2rem 1.5rem;
            border-bottom: 1px solid #3a5a5a;
            margin-bottom: 1rem;
        }
        
        .sidebar-header h1 {
            font-size: 1.5rem;
            font-weight: 900;
            color: #ffb300;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: #ccc;
            text-decoration: none;
            transition: background 0.3s, color 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 179, 0, 0.1);
            color: #ffb300;
            border-right: 3px solid #ffb300;
        }
        
        .menu-icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .badge {
            background: #ffb300;
            color: #222;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            margin-left: auto;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }
        
        .welcome h2 {
            color: #1a2a2a;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        /* Profile Section */
        .profile-container {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .profile-sidebar {
            width: 300px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 2rem;
            text-align: center;
        }
        
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffb300, #ff8800);
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .profile-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a2a2a;
            margin-bottom: 0.5rem;
        }
        
        .profile-email {
            color: #666;
            margin-bottom: 1rem;
        }
        
        .profile-stats {
            margin-top: 1.5rem;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .stat-label {
            color: #666;
        }
        
        .stat-value {
            font-weight: 600;
            color: #1a2a2a;
        }
        
        /* Form Styles */
        .profile-form {
            flex: 1;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ffb300;
            box-shadow: 0 0 0 2px rgba(255, 179, 0, 0.2);
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .admin-btn {
            background: #ffb300;
            color: #222;
            font-weight: bold;
            border: none;
            border-radius: 20px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s;
            margin-right: 1rem;
        }
        
        .admin-btn:hover {
            background: #ff8800;
            color: #fff;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .password-note {
            background: #f8f9fa;
            border-left: 4px solid #ffb300;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }
        
        .password-note p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
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
        
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                transform: translateX(-100%);
                z-index: 999;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                padding: 1rem;
                margin-left: 0;
            }
            
            .profile-container {
                flex-direction: column;
            }
            
            .profile-sidebar {
                width: 100%;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle - Positioned on RIGHT side -->
    <button class="menu-toggle" id="menuToggle" style="display: none; background: none; border: none; font-size: 1.5rem; color: #1a2a2a; cursor: pointer; position: fixed; top: 1rem; right: 1rem; z-index: 1000;">☰</button>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1>Nakupenda Tours</h1>
                <p>Admin Dashboard</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><span class="menu-icon">📊</span> Dashboard</a></li>
                <li><a href="post.php"><span class="menu-icon">📝</span> Create Post</a></li>
                <li><a href="manageposts.php"><span class="menu-icon">📋</span> Manage Posts</a></li>
                <li><a href="bookings.php"><span class="menu-icon">📅</span> Bookings</a></li>
                <li><a href="manage_admins.php"><span class="menu-icon">👥</span> Manage Admins</a></li>
                <li><a href="system_logs.php"><span class="menu-icon">🧾</span> System Logs</a></li>
                <li><a href="recyclebin.php"><span class="menu-icon">🗑️</span> Recycle Bin <?php if($recycle_count > 0): ?><span class="badge"><?php echo $recycle_count; ?></span><?php endif; ?></a></li>
                <li><a href="profile.php" class="active"><span class="menu-icon">👤</span> Profile</a></li>
                <li><a href="logout.php"><span class="menu-icon">🚪</span> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="welcome">
                    <h2>Admin Profile</h2>
                    <p>Manage your personal information and account settings</p>
                </div>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="profile-container">
                <!-- Profile Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-image">
                        <?php 
                        // Display initials based on name
                        $initials = '';
                        if (!empty($admin['full_name'])) {
                            $names = explode(' ', $admin['full_name']);
                            foreach ($names as $name) {
                                $initials .= strtoupper(substr($name, 0, 1));
                                if (strlen($initials) >= 2) break;
                            }
                        }
                        echo $initials ?: 'A';
                        ?>
                    </div>
                    <h3 class="profile-name"><?php echo htmlspecialchars($admin['full_name']); ?></h3>
                    <p class="profile-email"><?php echo htmlspecialchars($admin['email']); ?></p>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-label">Member Since</span>
                            <span class="stat-value"><?php echo date('M Y', strtotime($admin['created_at'])); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Role</span>
                            <span class="stat-value">Administrator</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Status</span>
                            <span class="stat-value" style="color: #27ae60;">Active</span>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Form -->
                <form class="profile-form" method="POST">
                    <h3 style="color: #1a2a2a; margin-bottom: 1.5rem; font-size: 1.3rem;">Personal Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($admin['full_name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($admin['email']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" required value="<?php echo htmlspecialchars($admin['country']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="tel" id="phone_number" name="phone_number" required value="<?php echo htmlspecialchars($admin['phone_number']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="Male" <?php echo $admin['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $admin['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                            
                        </select>
                    </div>
                    
                    <div class="password-note">
                        <p><strong>Note:</strong> Leave password fields empty if you don't want to change your password.</p>
                    </div>
                    
                    <h3 style="color: #1a2a2a; margin-bottom: 1.5rem; font-size: 1.3rem;">Change Password</h3>
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter new password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="admin-btn">Update Profile</button>
                        <a href="Dashboard.php" class="admin-btn btn-secondary">Back to Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            
            // Show menu toggle on mobile
            function checkScreenSize() {
                if (window.innerWidth <= 768) {
                    menuToggle.style.display = 'block';
                } else {
                    menuToggle.style.display = 'none';
                    sidebar.classList.remove('active');
                }
            }
            
            // Initial check
            checkScreenSize();
            
            // Check on resize
            window.addEventListener('resize', checkScreenSize);
            
            // Toggle sidebar
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                    if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });

            // Password confirmation validation
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePassword() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords don't match");
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            password.addEventListener('change', validatePassword);
            confirmPassword.addEventListener('keyup', validatePassword);
        });
    </script>
    <script src="../assets/sweetalert2.all.min.js"></script>
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
[file content end]
