<?php
require __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/email_helper.php';

if (!is_logged_in()) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: profile.php'); exit; }

$user = current_user();

// Ensure the newsletter_subscribed column exists (add it if not)
$db->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS newsletter_subscribed TINYINT(1) NOT NULL DEFAULT 1");

// Get current subscription status
$stmt = $db->prepare('SELECT newsletter_subscribed FROM users WHERE id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$currentStatus = (int)($row['newsletter_subscribed'] ?? 1);

// Toggle it
$newStatus = $currentStatus ? 0 : 1;
$upd = $db->prepare('UPDATE users SET newsletter_subscribed = ? WHERE id = ?');
$upd->bind_param('ii', $newStatus, $user['id']);
$upd->execute();

// Refresh session
$fetch = $db->prepare('SELECT id, full_name, username, email, password_hash FROM users WHERE id = ?');
$fetch->bind_param('i', $user['id']);
$fetch->execute();
$_SESSION['user'] = $fetch->get_result()->fetch_assoc();

if ($newStatus) {
    $subject = "Welcome to GoGetGro Deals";
    $message = "
    <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
        <div style='background-color: #007a5e; padding: 20px; text-align: center; color: white;'>
            <h2 style='margin: 0;'>Welcome to GoGetGro Deals</h2>
        </div>
        <div style='padding: 20px;'>
            <p>Dear " . htmlspecialchars($user['full_name'] ?? 'Customer') . ",</p>
            <p>Thank you for subscribing to the GoGetGro newsletter!</p>
            <p>You will now receive updates on our exclusive deals, latest products, and special offers right here in your inbox.</p>
            <br>
            <p>Best Regards,<br>GoGetGro Support Team</p>
        </div>
        <div style='background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; color: #777;'>
            &copy; " . date('Y') . " G3 Quad, Inc. All rights reserved.
        </div>
    </div>
    ";
    send_gogetgro_email($user['email'], $subject, $message);

    $_SESSION['flash'] = ['type' => 'success', 'msg' => ' You are now subscribed to our newsletter! Exclusive deals will be sent to ' . htmlspecialchars($user['email']) . '.'];
} else {
    $subject = "GoGetGro Newsletter Unsubscription";
    $message = "
    <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
        <div style='background-color: #007a5e; padding: 20px; text-align: center; color: white;'>
            <h2 style='margin: 0;'>GoGetGro Newsletter Unsubscription</h2>
        </div>
        <div style='padding: 20px;'>
            <p>Dear " . htmlspecialchars($user['full_name'] ?? 'Customer') . ",</p>
            <p>You have successfully unsubscribed from the GoGetGro newsletter.</p>
            <p>You will no longer receive updates on our exclusive deals and offers.</p>
            <p>If you change your mind, we'd love to have you back! You can re-subscribe at any time from your profile settings.</p>
            <p>Please consider subscribing again in the future so you don't miss out on our best offers and discounts.</p>
            <br>
            <p>Best Regards,<br>GoGetGro Support Team</p>
        </div>
        <div style='background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; color: #777;'>
            &copy; " . date('Y') . " G3 Quad, Inc. All rights reserved.
        </div>
    </div>
    ";
    send_gogetgro_email($user['email'], $subject, $message);

    $_SESSION['flash'] = ['type' => 'info', 'msg' => 'You have unsubscribed from our newsletter. You will no longer receive promotional emails.'];
}

header('Location: profile.php');
exit;

