<?php
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

$admin = current_admin();
if (!$admin || ($admin['role'] ?? '') !== 'owner') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$orderId   = (int) ($_POST['order_id'] ?? 0);
$newStatus = trim($_POST['status'] ?? '');

$allowed = ['To Pay', 'To Ship', 'Completed', 'Cancelled'];

if ($orderId <= 0 || !in_array($newStatus, $allowed, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
$stmt->bind_param('si', $newStatus, $orderId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error']);
}

