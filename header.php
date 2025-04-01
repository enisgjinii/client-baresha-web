<?php
// Start session with secure parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
session_set_cookie_params(3600, '/', null, true, true);
session_start();

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Session validation
if (isset($_SESSION['last_ip']) && $_SESSION['last_ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=security");
    exit;
}

$_SESSION['last_ip'] = $_SERVER['REMOTE_ADDR'];
$user_id = $_SESSION['user_id'];

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://code.jquery.com https://cdn.datatables.net https://cdnjs.cloudflare.com https://cdn.jsdelivr.net 'unsafe-inline'; style-src 'self' https://stackpath.bootstrapcdn.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com; img-src 'self' data:;");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Baresha Network CRM">
    <meta name="color-scheme" content="light dark">
    <title>bareshaNetwork</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="icon" href="img/brand-icon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f8f9fa;
            --text-color: #212529;
            --border-color: #dee2e6;
            --sidebar-bg: #f8f9fa;
            --card-bg: #ffffff;
            --card-border: #e9ecef;
            --link-color: #495057;
            --link-hover-bg: #e9ecef;
            --link-active-bg: #e0f7fa;
            --link-active-color: #0b7285;
            --input-bg: #ffffff;
            --input-border: #ced4da;
            --shadow-color: rgba(0, 0, 0, 0.05);
        }
        
        [data-theme="dark"] {
            --bg-color: #121212;
            --text-color: #e0e0e0;
            --border-color: #2a2a2a;
            --sidebar-bg: #1a1a1a;
            --card-bg: #1e1e1e;
            --card-border: #2a2a2a;
            --link-color: #adb5bd;
            --link-hover-bg: #2a2a2a;
            --link-active-bg: #2a2a2a;
            --link-active-color: #6ea8fe;
            --input-bg: #2c2c2c;
            --input-border: #444444;
            --shadow-color: rgba(0, 0, 0, 0.2);
        }

        * {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 6px;
            box-shadow: 0 2px 4px var(--shadow-color);
        }

        .main-content {
            padding: 15px;
        }

        input, select, textarea, .form-control {
            background-color: var(--input-bg);
            color: var(--text-color);
            border-color: var(--input-border);
            font-size: 0.9rem;
            border-radius: 4px;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
        }
        
        .btn {
            font-size: 0.9rem;
            border-radius: 4px;
            padding: 0.375rem 0.75rem;
        }

        table {
            font-size: 0.85rem;
            color: var(--text-color);
        }
        
        .table th {
            border-top: none;
            font-weight: 500;
        }
    </style>
    <script>
    // Apply theme on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme-mode') || 'system';
        
        if (savedTheme === 'system') {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        } else {
            document.documentElement.setAttribute('data-theme', savedTheme);
        }
    });
    </script>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">