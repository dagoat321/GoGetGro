<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: profile.php'); exit; }

$user     = current_user();
$fullName = trim($_POST['full_name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');

if ($fullName === '' || $email === '') {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Name and email are required.'];
    header('Location: profile.php'); exit;
}

$stmt = $db->prepare('UPDATE users SET full_name = ?, email = ? WHERE id = ?');
$stmt->bind_param('ssi', $fullName, $email, $user['id']);
$stmt->execute();

// Refresh session user data
$fetch = $db->prepare('SELECT id, full_name, username, email, password_hash FROM users WHERE id = ?');
$fetch->bind_param('i', $user['id']);
$fetch->execute();
$_SESSION['user'] = $fetch->get_result()->fetch_assoc();

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profile updated successfully.'];
header('Location: profile.php');
exit;

