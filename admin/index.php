<?php
session_start();
require_once '../config.php';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    $invalid_credentials_message = "Incorrect credentials.";
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM admin_users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $admin['password'])) {
            $admin_is_disabled = array_key_exists('is_active', $admin) && (int)$admin['is_active'] !== 1;
            if ($admin_is_disabled) {
                $error = $invalid_credentials_message;
                logSystemActivity(
                    $db,
                    'auth',
                    'login_attempt',
                    'Failed admin login attempt (disabled account).',
                    'admin',
                    (int)$admin['id'],
                    $admin['email'],
                    'admin_account',
                    (string)$admin['id'],
                    ['result' => 'failed_disabled', 'email' => $email]
                );
            } else {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['is_super_admin'] = isSuperAdminEmail($admin['email']) ? 1 : 0;
                logSystemActivity(
                    $db,
                    'auth',
                    'login_attempt',
                    'Successful admin login.',
                    'admin',
                    (int)$admin['id'],
                    $admin['email'],
                    'admin_account',
                    (string)$admin['id'],
                    ['result' => 'success', 'email' => $email]
                );
                
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = $invalid_credentials_message;
            logSystemActivity(
                $db,
                'auth',
                'login_attempt',
                'Failed admin login attempt (wrong password).',
                'admin',
                (int)$admin['id'],
                $admin['email'],
                'admin_account',
                (string)$admin['id'],
                ['result' => 'failed_wrong_password', 'email' => $email]
            );
        }
    } else {
        $error = $invalid_credentials_message;
        logSystemActivity(
            $db,
            'auth',
            'login_attempt',
            'Failed admin login attempt (unknown email).',
            'admin',
            null,
            $email,
            'admin_account',
            null,
            ['result' => 'failed_unknown_email', 'email' => $email]
        );
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Zanzibar Tours</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { height: 100%; font-family: 'Lato', Arial, sans-serif; background: #f7f7f7; }
        
        .admin-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #ffb300 0%, #ff8800 100%);
        }
        
        .admin-form-wrapper {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 24px rgba(0,0,0,0.18);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
        }
        
        .admin-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .admin-logo h1 {
            color: #1a2a2a;
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
        }
        
        .admin-logo p {
            color: #666;
            font-size: 1rem;
        }
        
        .admin-form {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-size: 1rem;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-group input {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ffb300;
            box-shadow: 0 0 0 2px rgba(255, 179, 0, 0.2);
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
            transition: background 0.3s, color 0.3s;
            margin-top: 0.5rem;
        }
        
        .admin-btn:hover {
            background: #ff8800;
            color: #fff;
        }
        
        .form-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .form-links a {
            color: #ffb300;
            text-decoration: none;
            font-weight: 600;
            margin: 0 0.5rem;
        }
        
        .form-links a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .alert-error {
            background: #ffe6e6;
            color: #d63031;
            border: 1px solid #ff7675;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-form-wrapper">
            <div class="admin-logo">
                <h1>Welcome Tours</h1>
                <p>Admin Login</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form class="admin-form" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="admin-btn">Login</button>
            </form>
            
        </div>
    </div>
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
