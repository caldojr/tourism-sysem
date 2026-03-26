<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
ensureSystemLogsTable($db);

$deleted_posts_query = "SELECT COUNT(*) as count FROM admin_posts WHERE deleted = 1";
$deleted_posts_stmt = $db->prepare($deleted_posts_query);
$deleted_posts_stmt->execute();
$deleted_posts = $deleted_posts_stmt->fetch(PDO::FETCH_ASSOC)['count'];

$logs_per_page = 30;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $logs_per_page;

$event_type_filter = trim($_GET['event_type'] ?? '');
$actor_type_filter = trim($_GET['actor_type'] ?? '');
$search_filter = trim($_GET['search'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

$where_clauses = [];
$params = [];

if ($event_type_filter !== '') {
    $where_clauses[] = "event_type = ?";
    $params[] = $event_type_filter;
}

if ($actor_type_filter !== '') {
    $where_clauses[] = "actor_type = ?";
    $params[] = $actor_type_filter;
}

if ($search_filter !== '') {
    $where_clauses[] = "(action LIKE ? OR description LIKE ? OR actor_name LIKE ? OR target_id LIKE ?)";
    $search_like = '%' . $search_filter . '%';
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
}

if ($date_from !== '') {
    $where_clauses[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to !== '') {
    $where_clauses[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

$count_query = "SELECT COUNT(*) FROM system_logs" . $where_sql;
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_logs = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_logs / $logs_per_page));

if ($current_page > $total_pages) {
    $current_page = $total_pages;
    $offset = ($current_page - 1) * $logs_per_page;
}

$logs_query = "SELECT * FROM system_logs" . $where_sql . " ORDER BY created_at DESC LIMIT $logs_per_page OFFSET $offset";
$logs_stmt = $db->prepare($logs_query);
$logs_stmt->execute($params);
$logs = $logs_stmt->fetchAll(PDO::FETCH_ASSOC);

$event_types_stmt = $db->query("SELECT DISTINCT event_type FROM system_logs ORDER BY event_type ASC");
$event_types = $event_types_stmt ? $event_types_stmt->fetchAll(PDO::FETCH_COLUMN) : [];

$actor_types_stmt = $db->query("SELECT DISTINCT actor_type FROM system_logs ORDER BY actor_type ASC");
$actor_types = $actor_types_stmt ? $actor_types_stmt->fetchAll(PDO::FETCH_COLUMN) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - Nakupenda Tours</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, html { min-height: 100%; font-family: 'Lato', Arial, sans-serif; background: #f7f7f7; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: linear-gradient(135deg, #1a2a2a 0%, #2d4a4a 100%); color: white; padding: 2rem 0; }
        .sidebar-header { padding: 0 1.5rem 2rem 1.5rem; border-bottom: 1px solid #3a5a5a; margin-bottom: 1rem; }
        .sidebar-header h1 { font-size: 1.5rem; font-weight: 900; color: #ffb300; margin-bottom: 0.5rem; }
        .sidebar-menu { list-style: none; }
        .sidebar-menu li { margin-bottom: 0.5rem; }
        .sidebar-menu a { display: flex; align-items: center; padding: 1rem 1.5rem; color: #ccc; text-decoration: none; transition: background 0.3s, color 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 179, 0, 0.1); color: #ffb300; border-right: 3px solid #ffb300; }
        .menu-icon { margin-right: 10px; width: 20px; text-align: center; }
        .badge { background: #ffb300; color: #222; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem; margin-left: auto; }
        .main-content { flex: 1; padding: 1.5rem; }
        .page-header { margin-bottom: 1.2rem; border-bottom: 1px solid #ddd; padding-bottom: 1rem; }
        .page-header h2 { color: #1a2a2a; margin-bottom: 0.2rem; }
        .page-header p { color: #666; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); padding: 1rem; margin-bottom: 1rem; }
        .filters-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.7rem; align-items: end; }
        .filters-form label { display: block; font-size: 0.85rem; color: #333; margin-bottom: 0.35rem; font-weight: 700; }
        .filters-form input, .filters-form select { width: 100%; border: 1px solid #d7d7d7; border-radius: 8px; padding: 0.55rem 0.65rem; }
        .btn { border: none; border-radius: 8px; padding: 0.6rem 0.9rem; cursor: pointer; font-weight: 700; }
        .btn-primary { background: #ffb300; color: #1a2a2a; }
        .btn-secondary { background: #e7e7e7; color: #333; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1200px; }
        th, td { text-align: left; padding: 0.6rem; border-bottom: 1px solid #ececec; font-size: 0.85rem; vertical-align: top; }
        th { background: #fafafa; color: #1a2a2a; }
        .metadata { max-width: 300px; white-space: pre-wrap; word-break: break-word; font-family: monospace; font-size: 0.78rem; color: #333; }
        .pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; gap: 0.8rem; }
        .pagination-links { display: flex; gap: 0.45rem; flex-wrap: wrap; }
        .page-link { padding: 0.45rem 0.7rem; border: 1px solid #d0d0d0; border-radius: 7px; text-decoration: none; color: #333; background: #fff; }
        .page-link.active { background: #ffb300; border-color: #ffb300; color: #1a2a2a; font-weight: 700; }
        @media (max-width: 960px) { .sidebar { display: none; } .main-content { padding: 1rem; } }
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
            <li><a href="manage_admins.php"><span class="menu-icon">👥</span> Manage Admins</a></li>
            <li><a href="system_logs.php" class="active"><span class="menu-icon">🧾</span> System Logs</a></li>
            <li><a href="recyclebin.php"><span class="menu-icon">🗑️</span> Recycle Bin <?php if($deleted_posts > 0): ?><span class="badge"><?php echo $deleted_posts; ?></span><?php endif; ?></a></li>
            <li><a href="profile.php"><span class="menu-icon">👤</span> Profile</a></li>
            <li><a href="logout.php"><span class="menu-icon">🚪</span> Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="page-header">
            <h2>System Logs</h2>
            <p>Audit trail of admin actions, login attempts, and visitor interactions.</p>
        </div>

        <div class="card">
            <form class="filters-form" method="GET">
                <div>
                    <label for="event_type">Event Type</label>
                    <select id="event_type" name="event_type">
                        <option value="">All</option>
                        <?php foreach ($event_types as $event_type_option): ?>
                            <option value="<?php echo htmlspecialchars($event_type_option); ?>" <?php echo $event_type_filter === $event_type_option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($event_type_option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="actor_type">Actor Type</label>
                    <select id="actor_type" name="actor_type">
                        <option value="">All</option>
                        <?php foreach ($actor_types as $actor_type_option): ?>
                            <option value="<?php echo htmlspecialchars($actor_type_option); ?>" <?php echo $actor_type_filter === $actor_type_option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($actor_type_option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_filter); ?>" placeholder="action, actor, target">
                </div>
                <div>
                    <label for="date_from">From</label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div>
                    <label for="date_to">To</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Apply</button>
                </div>
                <div>
                    <a href="system_logs.php" class="btn btn-secondary" style="width:100%;">Reset</a>
                </div>
            </form>
        </div>

        <div class="card">
            <div style="margin-bottom:0.7rem;color:#555;">Showing <?php echo count($logs); ?> of <?php echo $total_logs; ?> logs</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Event Type</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Actor</th>
                            <th>Target</th>
                            <th>IP</th>
                            <th>Page</th>
                            <th>Metadata</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                    <td><?php echo htmlspecialchars($log['event_type']); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($log['actor_type']); ?><br>
                                        <?php echo htmlspecialchars((string)($log['actor_name'] ?? '')); ?>
                                        <?php if (!empty($log['actor_id'])): ?>
                                            (ID: <?php echo (int)$log['actor_id']; ?>)
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars((string)($log['target_type'] ?? '')); ?><br>
                                        <?php echo htmlspecialchars((string)($log['target_id'] ?? '')); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars((string)($log['ip_address'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars((string)($log['page_url'] ?? '')); ?></td>
                                    <td class="metadata"><?php echo htmlspecialchars((string)($log['metadata_json'] ?? '')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9">No logs found for the selected filters.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <?php
                    $base_params = $_GET;
                    unset($base_params['page']);
                    $base_query = http_build_query($base_params);
                    $base_prefix = $base_query !== '' ? $base_query . '&' : '';
                ?>
                <div class="pagination">
                    <div>Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></div>
                    <div class="pagination-links">
                        <?php if ($current_page > 1): ?>
                            <a class="page-link" href="?<?php echo $base_prefix; ?>page=<?php echo $current_page - 1; ?>">Previous</a>
                        <?php endif; ?>
                        <?php for ($page_num = 1; $page_num <= $total_pages; $page_num++): ?>
                            <?php if ($page_num === $current_page): ?>
                                <span class="page-link active"><?php echo $page_num; ?></span>
                            <?php else: ?>
                                <a class="page-link" href="?<?php echo $base_prefix; ?>page=<?php echo $page_num; ?>"><?php echo $page_num; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($current_page < $total_pages): ?>
                            <a class="page-link" href="?<?php echo $base_prefix; ?>page=<?php echo $current_page + 1; ?>">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
