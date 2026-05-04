<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/email_helper.php';

// Ensure the subscribers table exists (for development/setup convenience)
$db->query("CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

// Check if email already exists
$stmt = $db->prepare('SELECT id FROM subscribers WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'You are already subscribed.']);
    exit;
}

// Insert new subscriber using prepared statements
$insertStmt = $db->prepare('INSERT INTO subscribers (email) VALUES (?)');
$insertStmt->bind_param('s', $email);

if ($insertStmt->execute()) {
    $subject = "Welcome to GoGetGro Deals";
    
    $message = "
    <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
        <div style='background-color: #007a5e; padding: 20px; text-align: center; color: white;'>
            <h2 style='margin: 0;'>Welcome to GoGetGro Deals</h2>
        </div>
        <div style='padding: 20px;'>
            <p>Dear Subscriber,</p>
            <p>Thank you for subscribing to GoGetGro!</p>
            <p>You will now receive updates on our exclusive deals and offers right here in your inbox.</p>
            <br>
            <p>Best Regards,<br>GoGetGro Support Team</p>
        </div>
        <div style='background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; color: #777;'>
            &copy; " . date('Y') . " G3 Quad, Inc. All rights reserved.
        </div>
    </div>
    ";

    if (send_gogetgro_email($email, $subject, $message)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Subscribed, but confirmation email failed to send.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save subscription.']);
}

