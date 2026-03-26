<?php
class Database {
    private $host = "localhost";
    private $db_name = "zanzibar_admin";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

function getSuperAdminEmails() {
    return [
        'njogoloaldo@gmail.com'
    ];
}

function isSuperAdminEmail($email) {
    $normalized_email = strtolower(trim((string)$email));
    if ($normalized_email === '') {
        return false;
    }

    $super_admin_emails = array_map('strtolower', getSuperAdminEmails());
    return in_array($normalized_email, $super_admin_emails, true);
}

function ensureSystemLogsTable(PDO $db) {
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $db->exec(
        "CREATE TABLE IF NOT EXISTS system_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(60) NOT NULL,
            action VARCHAR(120) NOT NULL,
            description TEXT NOT NULL,
            actor_type VARCHAR(40) NOT NULL DEFAULT 'system',
            actor_id INT NULL,
            actor_name VARCHAR(255) NULL,
            target_type VARCHAR(60) NULL,
            target_id VARCHAR(120) NULL,
            metadata_json LONGTEXT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            page_url VARCHAR(255) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_system_logs_created_at (created_at),
            INDEX idx_system_logs_event_type (event_type),
            INDEX idx_system_logs_actor_id (actor_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $ensured = true;
}

function ensurePostImagesTable(PDO $db) {
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $db->exec(
        "CREATE TABLE IF NOT EXISTS admin_post_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            image_path VARCHAR(500) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_post_images_post_id (post_id),
            INDEX idx_post_images_sort (post_id, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $ensured = true;
}

function fetchPostImages(PDO $db, array $postIds) {
    if (empty($postIds)) {
        return [];
    }

    ensurePostImagesTable($db);

    $placeholders = implode(',', array_fill(0, count($postIds), '?'));
    $stmt = $db->prepare(
        "SELECT post_id, image_path
         FROM admin_post_images
         WHERE post_id IN ($placeholders)
         ORDER BY sort_order ASC, id ASC"
    );
    $stmt->execute(array_values($postIds));

    $imagesByPost = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $postId = (int)$row['post_id'];
        if (!isset($imagesByPost[$postId])) {
            $imagesByPost[$postId] = [];
        }
        $imagesByPost[$postId][] = $row['image_path'];
    }

    return $imagesByPost;
}

function logSystemActivity(
    PDO $db,
    $event_type,
    $action,
    $description,
    $actor_type = 'system',
    $actor_id = null,
    $actor_name = null,
    $target_type = null,
    $target_id = null,
    $metadata = []
) {
    try {
        ensureSystemLogsTable($db);

        $metadata_json = null;
        if (is_array($metadata) && !empty($metadata)) {
            $metadata_json = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $page_url = $_SERVER['REQUEST_URI'] ?? null;

        $insert_query = "INSERT INTO system_logs
            (event_type, action, description, actor_type, actor_id, actor_name, target_type, target_id, metadata_json, ip_address, user_agent, page_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->execute([
            (string)$event_type,
            (string)$action,
            (string)$description,
            (string)$actor_type,
            $actor_id,
            $actor_name,
            $target_type,
            $target_id,
            $metadata_json,
            $ip_address,
            $user_agent,
            $page_url
        ]);
    } catch (Throwable $exception) {
        // Never break user flows because of logging errors.
    }
}
?>
