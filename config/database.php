<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbHost = 'localhost';
$dbName = 'gogetgro';
$dbUser = 'root';
$dbPass = '';

try {
    $db = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $db->set_charset('utf8mb4');
} catch (Exception $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
