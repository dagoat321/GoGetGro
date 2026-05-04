<?php

require __DIR__ . '/includes/bootstrap.php';

if (is_admin_logged_in()) {
    header('Location: ' . admin_dashboard_path());
    exit;
}

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '') {
    header('Location: login_admin.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Enter your admin username and password.';
    } else {
        $admin = find_admin_by_username($db, $username);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            unset($admin['password_hash']);
            $_SESSION['admin_user'] = $admin;

            header('Location: ' . admin_dashboard_path($admin));
            exit;
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoGetGro | Admin Portal</title>
    <link rel="stylesheet" href="login_admin.css">
</head>
<body>
    <div class="loading-screen">
        <img src="images/Group 46.png" class="loading-logo" alt="GoGetGro Logo">
        <p class="loading-text">Go get groceries, the fast and easy way.</p>
    </div>

    <div class="login-container">
        <div class="admin-box">
            <div class="brand-container">
                <img src="images/Group 46.png" alt="GoGetGro Logo" class="admin-brand-logo">
            </div>

            <div class="logo-section">
                <span>Welcome to</span>
                <b>GoGetGro</b>
                <div class="admin-label">Admin Portal</div>
            </div>

            <form method="post" action="login_admin.php" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" placeholder="Admin Username" autocomplete="off" spellcheck="false" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Admin Password" required>
                </div>

                <?php if ($error !== ''): ?>
                    <div id="error-msg" style="color: red; font-size: 0.85rem; margin-top: -8px; margin-bottom: 8px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <button class="login-btn" type="submit">ACCESS PORTAL</button>
            </form>
        </div>
    </div>
</body>
</html>

