<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../config/mail_config.php';

function send_contact_form_email($userEmail, $userName, $subject, $htmlMessage) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = MAIL_PORT;

        // Recipients
        // We set the "From" name to indicate it's from the Contact Form
        $mail->setFrom(MAIL_USER, 'GoGetGro Contact Form');
        
        // Send directly to the user as a receipt
        $mail->addAddress($userEmail, $userName);
        
        // BCC to support so they get a copy
        $mail->addBCC(MAIL_USER);
        
        // Set Reply-To so if support replies, it goes to the user
        $mail->addReplyTo($userEmail, $userName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Contact form message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
