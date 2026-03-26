<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$current_admin_id = (int)$_SESSION['admin_id'];
$current_admin_email = $_SESSION['admin_email'] ?? '';
$is_current_super_admin = isSuperAdminEmail($current_admin_email);

// Ensure status column exists for enabling/disabling admin accounts.
$status_column_stmt = $db->prepare("SHOW COLUMNS FROM admin_users LIKE 'is_active'");
$status_column_stmt->execute();
if ($status_column_stmt->rowCount() === 0) {
    $db->exec("ALTER TABLE admin_users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER password");
}

$deleted_posts_query = "SELECT COUNT(*) as count FROM admin_posts WHERE deleted = 1";
$deleted_posts_stmt = $db->prepare($deleted_posts_query);
$deleted_posts_stmt->execute();
$deleted_posts = $deleted_posts_stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    $actor_admin_name = $_SESSION['admin_email'] ?? ($_SESSION['admin_name'] ?? 'admin');

    if ($action === 'add_admin') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!$is_current_super_admin) {
            $error = "Only the super admin can add admin users.";
        } elseif ($full_name === '' || $email === '' || $country === '' || $phone_number === '' || $gender === '' || $password === '') {
            $error = "Please fill all required fields to add an admin user.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif ($password !== $confirm_password) {
            $error = "Password and confirm password do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $email_check_query = "SELECT id FROM admin_users WHERE email = ?";
            $email_check_stmt = $db->prepare($email_check_query);
            $email_check_stmt->execute([$email]);

            if ($email_check_stmt->rowCount() > 0) {
                $error = "This email is already registered for another admin.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_query = "INSERT INTO admin_users (full_name, email, country, phone_number, gender, password, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)";
                $insert_stmt = $db->prepare($insert_query);

                if ($insert_stmt->execute([$full_name, $email, $country, $phone_number, $gender, $hashed_password])) {
                    $success = "New admin user added successfully.";
                    $new_admin_id = (int)$db->lastInsertId();
                    logSystemActivity(
                        $db,
                        'admin_management',
                        'admin_add',
                        'New admin user added: ' . $email,
                        'admin',
                        $current_admin_id,
                        $actor_admin_name,
                        'admin_account',
                        (string)$new_admin_id,
                        ['new_admin_email' => $email, 'new_admin_name' => $full_name]
                    );
                    $_POST = [];
                } else {
                    $error = "Failed to add admin user. Please try again.";
                    logSystemActivity(
                        $db,
                        'admin_management',
                        'admin_add_failed',
                        'Failed to add admin user: ' . $email,
                        'admin',
                        $current_admin_id,
                        $actor_admin_name,
                        'admin_account',
                        null,
                        ['new_admin_email' => $email, 'new_admin_name' => $full_name]
                    );
                }
            }
        }
    }

    if ($action === 'toggle_status') {
        $target_admin_id = (int)($_POST['admin_id'] ?? 0);
        $target_status = (int)($_POST['target_status'] ?? 0);

        if (!$is_current_super_admin) {
            $error = "Only the super admin can change admin account status.";
        } elseif ($target_admin_id <= 0) {
            $error = "Invalid admin account selected.";
        } elseif ($target_admin_id === $current_admin_id && $target_status === 0) {
            $error = "You cannot disable your own account.";
        } else {
            $target_admin_email_query = "SELECT email FROM admin_users WHERE id = ?";
            $target_admin_email_stmt = $db->prepare($target_admin_email_query);
            $target_admin_email_stmt->execute([$target_admin_id]);
            $target_admin_email = (string)$target_admin_email_stmt->fetchColumn();

            if ($target_admin_email !== '' && isSuperAdminEmail($target_admin_email)) {
                $error = "The super admin account cannot be disabled.";
            } else {
            $update_status_query = "UPDATE admin_users SET is_active = ? WHERE id = ?";
            $update_status_stmt = $db->prepare($update_status_query);

            if ($update_status_stmt->execute([$target_status, $target_admin_id])) {
                $success = $target_status === 1 ? "Admin user enabled successfully." : "Admin user disabled successfully.";
                logSystemActivity(
                    $db,
                    'admin_management',
                    $target_status === 1 ? 'admin_enable' : 'admin_disable',
                    $target_status === 1 ? 'Admin account enabled.' : 'Admin account disabled.',
                    'admin',
                    $current_admin_id,
                    $actor_admin_name,
                    'admin_account',
                    (string)$target_admin_id
                );
            } else {
                $error = "Failed to update admin status.";
                logSystemActivity(
                    $db,
                    'admin_management',
                    'admin_toggle_status_failed',
                    'Failed updating admin account status.',
                    'admin',
                    $current_admin_id,
                    $actor_admin_name,
                    'admin_account',
                    (string)$target_admin_id,
                    ['target_status' => $target_status]
                );
            }
            }
        }
    }

    if ($action === 'delete_admin') {
        $target_admin_id = (int)($_POST['admin_id'] ?? 0);

        if (!$is_current_super_admin) {
            $error = "Only the super admin can delete admin users.";
        } elseif ($target_admin_id <= 0) {
            $error = "Invalid admin account selected.";
        } elseif ($target_admin_id === $current_admin_id) {
            $error = "You cannot delete your own account.";
        } else {
            $target_admin_email_query = "SELECT email FROM admin_users WHERE id = ?";
            $target_admin_email_stmt = $db->prepare($target_admin_email_query);
            $target_admin_email_stmt->execute([$target_admin_id]);
            $target_admin_email = (string)$target_admin_email_stmt->fetchColumn();

            if ($target_admin_email !== '' && isSuperAdminEmail($target_admin_email)) {
                $error = "The super admin account cannot be deleted.";
            } else {
            $delete_query = "DELETE FROM admin_users WHERE id = ?";
            $delete_stmt = $db->prepare($delete_query);

            if ($delete_stmt->execute([$target_admin_id])) {
                $success = "Admin user deleted successfully.";
                logSystemActivity(
                    $db,
                    'admin_management',
                    'admin_delete',
                    'Admin user deleted.',
                    'admin',
                    $current_admin_id,
                    $actor_admin_name,
                    'admin_account',
                    (string)$target_admin_id
                );
            } else {
                $error = "Failed to delete admin user.";
                logSystemActivity(
                    $db,
                    'admin_management',
                    'admin_delete_failed',
                    'Failed deleting admin user.',
                    'admin',
                    $current_admin_id,
                    $actor_admin_name,
                    'admin_account',
                    (string)$target_admin_id
                );
            }
            }
        }
    }

    if ($action === 'change_password') {
        $target_admin_id = (int)($_POST['admin_id'] ?? 0);
        $new_password = $_POST['new_password'] ?? '';
        $confirm_new_password = $_POST['confirm_new_password'] ?? '';

        if ($target_admin_id <= 0) {
            $error = "Invalid admin account selected.";
        } elseif (!$is_current_super_admin && $target_admin_id !== $current_admin_id) {
            $error = "You can only change your own password.";
        } elseif ($new_password === '' || $confirm_new_password === '') {
            $error = "Please enter and confirm the new password.";
        } elseif ($new_password !== $confirm_new_password) {
            $error = "New password and confirmation do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_query = "UPDATE admin_users SET password = ? WHERE id = ?";
            $update_password_stmt = $db->prepare($update_password_query);

            if ($update_password_stmt->execute([$password_hash, $target_admin_id])) {
                $success = "Password updated successfully.";
                logSystemActivity(
                    $db,
                    'security',
                    'admin_password_change',
                    'Admin password changed.',
                    'admin',
                    $current_admin_id,
                    $actor_admin_name,
                    'admin_account',
                    (string)$target_admin_id
                );
            } else {
                $error = "Failed to update password.";
                logSystemActivity(
                    $db,
                    'security',
                    'admin_password_change_failed',
                    'Failed to change admin password.',
                    'admin',
                    $current_admin_id,
                    $actor_admin_name,
                    'admin_account',
                    (string)$target_admin_id
                );
            }
        }
    }
}

$admins_query = "SELECT id, full_name, email, phone_number, country, gender, created_at, is_active FROM admin_users ORDER BY created_at DESC";
$admins_stmt = $db->prepare($admins_query);
$admins_stmt->execute();
$admins = $admins_stmt->fetchAll(PDO::FETCH_ASSOC);

$active_count = 0;
$disabled_count = 0;
foreach ($admins as $admin_row) {
    if ((int)$admin_row['is_active'] === 1) {
        $active_count++;
    } else {
        $disabled_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin Users - Nakupenda Tours</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { min-height: 100%; font-family: 'Lato', Arial, sans-serif; background: #f7f7f7; }

        .dashboard-container { display: flex; min-height: 100vh; }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1a2a2a 0%, #2d4a4a 100%);
            color: white;
            padding: 2rem 0;
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

        .sidebar-menu { list-style: none; }
        .sidebar-menu li { margin-bottom: 0.5rem; }

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

        .menu-icon { margin-right: 10px; width: 20px; text-align: center; }
        .badge {
            background: #ffb300;
            color: #222;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            margin-left: auto;
        }

        .main-content { flex: 1; padding: 2rem; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #ddd;
            padding-bottom: 1rem;
        }

        .page-header h2 { color: #1a2a2a; }
        .page-header p { color: #666; margin-top: 0.25rem; }

        .stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .stat-box {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.2rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            min-width: 170px;
        }

        .stat-box strong {
            display: block;
            font-size: 1.4rem;
            color: #1a2a2a;
        }

        .stat-box span { color: #666; font-size: 0.95rem; }

        .grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: 360px 1fr;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 1.5rem;
        }

        .card h3 { color: #1a2a2a; margin-bottom: 1rem; }

        .form-group { margin-bottom: 0.9rem; }
        .form-group label {
            display: block;
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            border: 1px solid #d7d7d7;
            border-radius: 8px;
            padding: 0.7rem 0.75rem;
            font-size: 0.95rem;
        }

        .show-password-toggle {
            margin-top: 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.88rem;
            color: #444;
        }

        .show-password-toggle input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
            cursor: pointer;
            font-weight: 700;
        }

        .btn-primary { background: #ffb300; color: #1a2a2a; }
        .btn-primary:hover { background: #ff9800; }

        .btn-success { background: #2e7d32; color: #fff; }
        .btn-warning { background: #c62828; color: #fff; }
        .btn-danger { background: #6d4c41; color: #fff; }

        .alert {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .alert-success {
            background: #e7f7ea;
            color: #1b7f38;
            border: 1px solid #bfe6c9;
        }

        .alert-error {
            background: #feecec;
            color: #b92020;
            border: 1px solid #f5bcbc;
        }

        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 780px;
        }

        th, td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid #efefef;
            font-size: 0.93rem;
            vertical-align: top;
        }

        th { color: #1a2a2a; background: #fafafa; }
        .status {
            display: inline-block;
            padding: 0.25rem 0.55rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .status-active {
            background: #e7f7ea;
            color: #1b7f38;
        }

        .status-disabled {
            background: #feecec;
            color: #b92020;
        }

        .actions { display: flex; gap: 0.4rem; flex-wrap: wrap; }
        .inline-form { display: inline; }

        @media (max-width: 960px) {
            .grid { grid-template-columns: 1fr; }
            .main-content { padding: 1rem; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
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
            <li><a href="manage_admins.php" class="active"><span class="menu-icon">👥</span> Manage Admins</a></li>
            <li><a href="system_logs.php"><span class="menu-icon">🧾</span> System Logs</a></li>
            <li><a href="recyclebin.php"><span class="menu-icon">🗑️</span> Recycle Bin <?php if($deleted_posts > 0): ?><span class="badge"><?php echo $deleted_posts; ?></span><?php endif; ?></a></li>
            <li><a href="profile.php"><span class="menu-icon">👤</span> Profile</a></li>
            <li><a href="logout.php"><span class="menu-icon">🚪</span> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h2>Manage Admin Users</h2>
                <p>Add new admins, disable/enable accounts, and review all system admins.</p>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-box">
                <strong><?php echo count($admins); ?></strong>
                <span>Total Admins</span>
            </div>
            <div class="stat-box">
                <strong><?php echo $active_count; ?></strong>
                <span>Active Admins</span>
            </div>
            <div class="stat-box">
                <strong><?php echo $disabled_count; ?></strong>
                <span>Disabled Admins</span>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h3>Add New Admin</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_admin">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" required value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" required value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (($_POST['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                    </div>
                    <label class="show-password-toggle" for="show_password_toggle">
                        <input type="checkbox" id="show_password_toggle">
                        Show password
                    </label>
                    <button type="submit" class="btn btn-primary">Add Admin</button>
                </form>
            </div>

            <div class="card">
                <h3>All System Admins</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Country</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin_row): ?>
                                <?php $is_active = (int)$admin_row['is_active'] === 1; ?>
                                <?php $row_is_super_admin = isSuperAdminEmail($admin_row['email']); ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($admin_row['full_name']); ?>
                                        <?php echo ((int)$admin_row['id'] === $current_admin_id) ? ' (You)' : ''; ?>
                                        <?php echo $row_is_super_admin ? ' (Super Admin)' : ''; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($admin_row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($admin_row['phone_number']); ?></td>
                                    <td><?php echo htmlspecialchars($admin_row['country']); ?></td>
                                    <td>
                                        <span class="status <?php echo $is_active ? 'status-active' : 'status-disabled'; ?>">
                                            <?php echo $is_active ? 'Active' : 'Disabled'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($admin_row['created_at'])); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                onclick="openChangePasswordModal(<?php echo (int)$admin_row['id']; ?>, '<?php echo htmlspecialchars($admin_row['full_name'], ENT_QUOTES, 'UTF-8'); ?>')"
                                                <?php echo (!$is_current_super_admin && (int)$admin_row['id'] !== $current_admin_id) ? 'disabled' : ''; ?>
                                            >
                                                Change Password
                                            </button>

                                            <form method="POST" class="inline-form">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="admin_id" value="<?php echo (int)$admin_row['id']; ?>">
                                                <input type="hidden" name="target_status" value="<?php echo $is_active ? 0 : 1; ?>">
                                                <button type="submit" class="btn <?php echo $is_active ? 'btn-warning' : 'btn-success'; ?>" <?php echo (!$is_current_super_admin || $row_is_super_admin) ? 'disabled' : ''; ?>>
                                                    <?php echo $is_active ? 'Disable' : 'Enable'; ?>
                                                </button>
                                            </form>

                                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this admin account? This action cannot be undone.');">
                                                <input type="hidden" name="action" value="delete_admin">
                                                <input type="hidden" name="admin_id" value="<?php echo (int)$admin_row['id']; ?>">
                                                <button type="submit" class="btn btn-danger" <?php echo (!$is_current_super_admin || $row_is_super_admin) ? 'disabled' : ''; ?>>Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="passwordChangeForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="change_password">
    <input type="hidden" name="admin_id" id="passwordChangeAdminId" value="">
    <input type="hidden" name="new_password" id="passwordChangeNewPassword" value="">
    <input type="hidden" name="confirm_new_password" id="passwordChangeConfirmPassword" value="">
</form>
<script>
    const showPasswordToggle = document.getElementById('show_password_toggle');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    if (showPasswordToggle && passwordInput && confirmPasswordInput) {
        showPasswordToggle.addEventListener('change', function () {
            const fieldType = this.checked ? 'text' : 'password';
            passwordInput.type = fieldType;
            confirmPasswordInput.type = fieldType;
        });
    }

    async function openChangePasswordModal(adminId, adminName) {
        if (typeof Swal === 'undefined') {
            return;
        }

        const result = await Swal.fire({
            title: 'Change Password',
            html:
                '<p style="margin:0 0 12px 0;color:#555;">' + adminName + '</p>' +
                '<input id="swal-new-password" type="password" class="swal2-input" placeholder="New password">' +
                '<input id="swal-confirm-password" type="password" class="swal2-input" placeholder="Confirm new password">',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Update Password',
            preConfirm: function () {
                const newPassword = document.getElementById('swal-new-password').value;
                const confirmPassword = document.getElementById('swal-confirm-password').value;

                if (!newPassword || !confirmPassword) {
                    Swal.showValidationMessage('Please enter and confirm the new password.');
                    return false;
                }

                if (newPassword.length < 6) {
                    Swal.showValidationMessage('Password must be at least 6 characters.');
                    return false;
                }

                if (newPassword !== confirmPassword) {
                    Swal.showValidationMessage('Passwords do not match.');
                    return false;
                }

                return { newPassword: newPassword, confirmPassword: confirmPassword };
            }
        });

        if (!result.isConfirmed || !result.value) {
            return;
        }

        document.getElementById('passwordChangeAdminId').value = String(adminId);
        document.getElementById('passwordChangeNewPassword').value = result.value.newPassword;
        document.getElementById('passwordChangeConfirmPassword').value = result.value.confirmPassword;
        document.getElementById('passwordChangeForm').submit();
    }
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
