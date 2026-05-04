<?php

require __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$loginValue = '';
$successMessage = $_SESSION['signup_success'] ?? '';
unset($_SESSION['signup_success']);

$hasError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginValue = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($loginValue === '' || $password === '') {
        $error = 'Enter your username or email and password.';
        $hasError = true;
    } else {
        $user = find_user_by_login($db, $loginValue);

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        }

        $error = 'Invalid login credentials.';
        $hasError = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoGetGro | Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="stylesheet.css">
    <style>
        .form-error {
            color: #b42318;
            margin-bottom: 16px;
            font-size: 0.95rem;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        /* When there's an error, skip the loading animation and show content immediately */
        .no-loading .loading-screen {
            display: none !important;
        }
        .no-loading .login-container {
            opacity: 1 !important;
            animation: none !important;
        }
    </style>
</head>
<body class="<?= $hasError ? 'no-loading' : '' ?>">
    <?php if (!$hasError): ?>
    <div class="loading-screen" id="loading">
        <img src="images/Group 13.png" alt="GoGetGro" class="loading-logo">
        <p class="loading-text">Go get groceries, the fast and easy way.</p>
    </div>
    <?php endif; ?>

    <div class="login-container">
        <div class="left">
            <div class="form-wrapper">
                <div class="logo">
                    Welcome to<br>
                    <b>GoGetGro.</b>
                </div>

                <?php if ($successMessage !== ''): ?>
                    <div style="color: #d4f9ee; margin-bottom: 16px; font-size: 0.95rem; background: rgba(255,255,255,0.15); border-radius: 8px; padding: 10px 14px;">
                        <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="form-error">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="login.php">
                    <div class="input-group">
                        <label>Username or Email</label>
                        <input type="text" name="login" id="loginInput" value="<?= htmlspecialchars($loginValue) ?>" placeholder="Enter username or email" required>
                    </div>

                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" id="passwordInput" placeholder="Enter password" required>
                    </div>

                    <a href="forgotpass.php" class="link-wrapper">
                        <span class="link"><b>Forgot Password?</b></span>
                    </a>

                    <button class="login-btn" type="submit">LOGIN</button>
                </form>

                <div class="signup-text">
                    Do not have an account?
                    <a href="signup.php" class="signup-link-wrapper">
                        <span class="signup-link"><b>Sign Up</b></span>
                    </a>
                </div>

                <div class="social-footer">
                    <p>Stay connected with GoGetGro!</p>
                    <div class="social-icons">
                        <a href="https://www.facebook.com" target="_blank" rel="noreferrer"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.instagram.com" target="_blank" rel="noreferrer"><i class="bi bi-instagram"></i></a>
                        <a href="https://www.tiktok.com" target="_blank" rel="noreferrer"><i class="bi bi-tiktok"></i></a>
                        <a href="https://www.messenger.com" target="_blank" rel="noreferrer"><i class="bi bi-chat-dots-fill"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="right">
            <div class="carousel">
                <div class="slide"><img src="images/log 1.png" alt="Promo slide 1"></div>
                <div class="slide"><img src="images/log 2.png" alt="Promo slide 2"></div>
                <div class="slide"><img src="images/log 3.png" alt="Promo slide 3"></div>
                <div class="slide"><img src="images/log 1.png" alt="Promo slide 4"></div>
            </div>
            <a href="index.php" class="continue">Continue Without an Account</a>
        </div>
    </div>
</body>
</html>

