<?php
session_start();

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'event_management';

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}
?>