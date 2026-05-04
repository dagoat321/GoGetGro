<?php
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

$admin = current_admin();
if (!$admin) {
    echo json_encode([]);
    exit;
}

$userId = (int) ($_GET['user_id'] ?? 0);
if ($userId <= 0) {
    echo json_encode([]);
    exit;
}

$rows = get_user_order_history_admin($db, $userId);
echo json_encode($rows, JSON_UNESCAPED_UNICODE);

