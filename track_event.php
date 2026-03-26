<?php
require_once 'config.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

$payload = $_POST;
if (empty($payload)) {
    $raw_input = file_get_contents('php://input');
    $decoded = json_decode($raw_input, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

$action = trim((string)($payload['action'] ?? ''));
$event_type = trim((string)($payload['event_type'] ?? 'visitor_activity'));
$title = trim((string)($payload['title'] ?? ''));
$category = trim((string)($payload['category'] ?? ''));
$region = trim((string)($payload['region'] ?? ''));
$post_id = trim((string)($payload['post_id'] ?? ''));
$source_page = trim((string)($payload['source_page'] ?? ''));

if ($action === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing action']);
    exit();
}

$description = "Visitor action: " . $action;
if ($title !== '') {
    $description .= " (" . $title . ")";
}

logSystemActivity(
    $db,
    $event_type,
    $action,
    $description,
    'visitor',
    null,
    'Guest Visitor',
    'post',
    $post_id !== '' ? $post_id : null,
    [
        'title' => $title,
        'category' => $category,
        'region' => $region,
        'source_page' => $source_page
    ]
);

echo json_encode(['status' => 'ok']);
?>
