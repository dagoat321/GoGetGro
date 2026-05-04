<?php

session_start();

require __DIR__ . '/../config/database.php';
require __DIR__ . '/store.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

