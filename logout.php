<?php

session_start();

// Clear session cart before destroying
$_SESSION['cart'] = [];

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;

