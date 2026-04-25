<?php
require_once '../includes/config.php';

// Log activity
if (isset($_SESSION['user_id'])) {
    $pdo->prepare("INSERT INTO activity_logs (user_id, action, ip_address, user_agent) VALUES (?, 'logout', ?, ?)")
        ->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
}

// Clear all session data
$_SESSION = [];

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to home
redirect(APP_URL . '/index.php');
