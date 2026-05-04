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

$user   = current_user();
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $label   = trim($_POST['label'] ?? 'Home');
    $address = trim($_POST['address_line'] ?? '');
    if ($label !== '' && $address !== '') {
        $stmt = $db->prepare('INSERT INTO user_addresses (user_id, label, address_line) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $user['id'], $label, $address);
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Address added successfully.'];
    }
} elseif ($action === 'edit') {
    $addressId = (int)($_POST['address_id'] ?? 0);
    $label     = trim($_POST['label'] ?? '');
    $address   = trim($_POST['address_line'] ?? '');
    if ($addressId > 0 && $label !== '' && $address !== '') {
        $stmt = $db->prepare('UPDATE user_addresses SET label = ?, address_line = ? WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ssii', $label, $address, $addressId, $user['id']);
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Address updated.'];
    }
} elseif ($action === 'delete') {
    $addressId = (int)($_POST['address_id'] ?? 0);
    if ($addressId > 0) {
        $stmt = $db->prepare('DELETE FROM user_addresses WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $addressId, $user['id']);
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Address removed.'];
    }
}

header('Location: profile.php');
exit;

