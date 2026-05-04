<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: profile.php'); exit; }

$user    = current_user();
$current = trim($_POST['current_password'] ?? '');
$new     = trim($_POST['new_password'] ?? '');
$confirm = trim($_POST['confirm_password'] ?? '');

$fetch = $db->prepare('SELECT password_hash FROM users WHERE id = ?');
$fetch->bind_param('i', $user['id']);
$fetch->execute();
$row = $fetch->get_result()->fetch_assoc();

if (!password_verify($current, $row['password_hash'])) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Current password is incorrect.'];
    header('Location: profile.php'); exit;
}
if (strlen($new) < 8) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'New password must be at least 8 characters.'];
    header('Location: profile.php'); exit;
}
if ($new !== $confirm) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Passwords do not match.'];
    header('Location: profile.php'); exit;
}

$hash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
$stmt->bind_param('si', $hash, $user['id']);
$stmt->execute();

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Password changed successfully.'];
header('Location: profile.php');
exit;

