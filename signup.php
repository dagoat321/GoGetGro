<?php

require __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$values = [
    'full_name' => '',
    'username' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['full_name'] = trim($_POST['full_name'] ?? '');
    $values['username'] = trim($_POST['username'] ?? '');
    $values['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($values['full_name'] === '' || $values['username'] === '' || $values['email'] === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'Complete all signup fields.';
    }

    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($errors === []) {
        try {
            create_user($db, $values['full_name'], $values['username'], $values['email'], $password);
            $_SESSION['signup_success'] = 'Account created. You can log in now.';
            header('Location: login.php');
            exit;
        } catch (mysqli_sql_exception $exception) {
            $errors[] = 'Username or email already exists.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | GoGetGro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="stylesheet_signup.css">
    <style>
        .error-box {
            color: #b42318;
            margin-bottom: 18px;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <a href="login.php" class="back-btn">&lt;</a>

    <div class="signup-container">
        <h1>Sign Up</h1>

        <?php if ($errors !== []): ?>
            <div class="error-box">
                <?= htmlspecialchars(implode(' ', $errors)) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="signup.php">
            <div class="input-group">
                <label>Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($values['full_name']) ?>" placeholder="Enter your full name" required>
            </div>

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($values['username']) ?>" placeholder="Choose a username" required>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($values['email']) ?>" placeholder="Enter your email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password" required>
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm your password" required>
            </div>

            <button class="signup-btn" type="submit">SIGN UP</button>
        </form>

        <div class="login-redirect">
            Already have an account? <a href="login.php"><b>Log In</b></a>
        </div>
    </div>
</body>
</html>

