<?php

require __DIR__ . '/includes/bootstrap.php';

unset($_SESSION['admin_user']);

header('Location: login_admin.php');
exit;

