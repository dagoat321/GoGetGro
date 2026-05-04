<?php
require __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/contact_mailer.php';

$pageTitle = 'Help & Contact';
$extraCss = ['stylesheet2.css'];
require_once __DIR__ . '/includes/header.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $subject   = trim($_POST['subject'] ?? '');
    $message   = trim($_POST['message'] ?? '');

    if ($firstName && $lastName && $email && $subject && $message) {
        $body = "
        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
            <div style='background-color: #007a5e; padding: 20px; text-align: center; color: white;'>
                <h2 style='margin: 0;'>GoGetGro Support Request Received</h2>
            </div>
            <div style='padding: 20px;'>
                <p>Hi " . htmlspecialchars($firstName) . ",</p>
                <p>We have received your request for help regarding <strong>" . htmlspecialchars($subject) . "</strong>. Our support team will review your message and get back to you as soon as possible.</p>
                <p>For your records, here is a copy of your message:</p>
                <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;'>
                <p><strong>Name:</strong> " . htmlspecialchars($firstName . ' ' . $lastName) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
                <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;'>
                <p>Best Regards,<br>GoGetGro Support Team</p>
            </div>
            <div style='background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; color: #777;'>
                &copy; " . date('Y') . " G3 Quad, Inc. All rights reserved.
            </div>
        </div>
        ";

        if (send_contact_form_email($email, $firstName . ' ' . $lastName, "Support Request: $subject", $body)) {
            $success = true;
        } else {
            $error = "Failed to send email. Please try again later.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

$preFirstName = '';
$preLastName  = '';
$preEmail     = '';
if (is_logged_in()) {
    $user = current_user();
    $names = explode(' ', $user['full_name'] ?? '');
    $preFirstName = $names[0] ?? '';
    $preLastName  = isset($names[1]) ? implode(' ', array_slice($names, 1)) : '';
    $preEmail     = $user['email'] ?? '';
}
?>

<main class="content-wrapper">
    <div class="container my-5 py-5">
        <div class="row">
            <div class="col-lg-6 mb-5">
                <p class="text-uppercase fw-bold" style="color: var(--primary-green);">Let's Connect!</p>
                <h1 class="display-3 fw-bold mb-4">Get In Touch with <span style="color: var(--primary-green);">GoGetGro.</span></h1>

                <p class="text-muted mb-4" style="line-height: 1.8;">
                    Have questions, concerns, or feedback? The GoGetGro team is here to help.
                    Whether you need assistance with your orders, have product inquiries,
                    or want to know more about our services, our customer support team is ready to assist you.
                </p>

                <div class="contact-info-list">
                    <div class="d-flex align-items-start mb-4">
                        <div class="contact-icon me-3"><i class="bi bi-envelope"></i></div>
                        <div>
                            <p class="fw-bold mb-0">EMAIL</p>
                            <a href="mailto:gogetgrosupport@gmail.com" class="text-decoration-none text-dark">gogetgrosupport@gmail.com</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <div class="contact-icon me-3"><i class="bi bi-telephone"></i></div>
                        <div>
                            <p class="fw-bold mb-0">PHONE</p>
                            <span>+63 912 345 6789</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <div class="contact-icon me-3"><i class="bi bi-geo-alt"></i></div>
                        <div>
                            <p class="fw-bold mb-0">LOCATION</p>
                            <span>Angeles City, Pampanga</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="contact-form-card">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form id="contactForm" method="POST" action="help.php" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="first_name" class="form-control contact-input" placeholder="First Name" value="<?= htmlspecialchars($preFirstName) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="text" name="last_name" class="form-control contact-input" placeholder="Last Name" value="<?= htmlspecialchars($preLastName) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control contact-input" placeholder="Email Address" value="<?= htmlspecialchars($preEmail) ?>" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="subject" class="form-control contact-input" placeholder="Subject" required>
                        </div>
                        <div class="mb-4">
                            <textarea name="message" class="form-control contact-input" rows="9" placeholder="Your message..." required></textarea>
                        </div>
                        <button type="submit" class="btn send-message-btn w-100 py-3 fw-bold">
                            Send Message <i class="bi bi-send ms-2"></i>
                        </button>
                    </form>

                    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content text-center p-4">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <i class="bi bi-exclamation-circle-fill text-danger" style="font-size: 4rem;"></i>
                                    </div>
                                    <h2 class="fw-bold text-danger">Message could not be sent.</h2>
                                    <p class="text-muted">Please ensure all required fields are completed.</p>
                                    <button type="button" class="btn btn-dark w-50 py-2 mt-3" data-bs-dismiss="modal" style="border-radius: 25px;">OK</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content text-center p-4" style="border-radius: 20px; border: none;">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <i class="bi bi-envelope-check-fill" style="font-size: 5rem; color: #007a5e;"></i>
                                    </div>
                                    <h2 class="fw-bold" style="color: #007a5e;">Your message has been sent successfully.</h2>
                                    <p class="text-muted">Please expect a reply from our team shortly.</p>
                                    <button type="button" class="btn w-50 py-2 mt-3 text-white fw-bold" data-bs-dismiss="modal" style="border-radius: 25px; background-color: #007a5e;">OK</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<script>
    <?php if ($success): ?>
        document.addEventListener("DOMContentLoaded", function() {
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        });
    <?php endif; ?>

    document.getElementById('contactForm').addEventListener('submit', function(event) {
        let isValid = true;
        const inputs = this.querySelectorAll('[required]');
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));

        inputs.forEach(input => {
            if (input.value.trim() === '') {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            event.preventDefault();
            errorModal.show();
        }
    });

    document.querySelectorAll('.contact-input').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.remove('is-invalid');
            }
        });
    });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>