<?php
require_once 'vendor/autoload.php';

session_start();

// Log the logout activity
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Unknown';
$username = isset($_SESSION['user_perdoruesi']) ? $_SESSION['user_perdoruesi'] : 'Unknown';
$log_entry = date('Y-m-d H:i:s') . " - Logout: User {$username} (ID: {$user_id}) from {$ip_address}\n";
file_put_contents('login_activity.log', $log_entry, FILE_APPEND);

// Clear authentication cookies if they exist
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Set security headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT');

// Redirect to login page with security headers
header('Clear-Site-Data: "cache", "cookies", "storage"');
header('Location: login.php');
exit();
