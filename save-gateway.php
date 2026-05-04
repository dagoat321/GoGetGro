<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

$user     = current_user();
$selected = $_POST['gateways'] ?? [];
$allKeys  = array_keys(payment_gateways());

// Delete existing gateways
$stmt = $db->prepare('DELETE FROM user_payment_gateways WHERE user_id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();

// Insert selected ones
foreach ($selected as $key) {
    if (in_array($key, $allKeys, true)) {
        $stmt = $db->prepare('INSERT IGNORE INTO user_payment_gateways (user_id, gateway_key) VALUES (?, ?)');
        $stmt->bind_param('is', $user['id'], $key);
        $stmt->execute();
    }
}

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Payment methods saved successfully.'];
header('Location: profile.php');
exit;

