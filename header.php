<?php
// Start session with secure parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
session_set_cookie_params(86400, '/', null, true, true); // Changed from 3600 to 86400 (24 hours)
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
// Update Content-Security-Policy to allow external flag images
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://code.jquery.com https://cdn.datatables.net https://cdnjs.cloudflare.com https://cdn.jsdelivr.net 'unsafe-inline'; style-src 'self' https://stackpath.bootstrapcdn.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com; img-src 'self' https://flagcdn.com data:;");
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
        }        table {
            font-size: 0.85rem;
            color: var(--text-color);
        }
        
        .table th {
            border-top: none;
            font-weight: 500;
        }
        
        /* Common dark mode styles for all pages */
        :root {
            --primary-color: #6B46C1; /* Purple */
            --secondary-color: #3B82F6; /* Blue */
            --success-color: #10B981; /* Green */
            --warning-color: #F59E0B; /* Amber */
            --danger-color: #EF4444; /* Red */
            --info-color: #3B82F6; /* Blue */
            
            --bg-body: #F7FAFC; /* Very Light Gray */
            --bg-card: #FFFFFF; /* White */
            --border-color: #E2E8F0; /* Light Gray */
            
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --card-border-radius: 12px;
        }
        
        /* Dark mode variables to be applied when dark mode is active */
        .dark-mode {
            --text-primary: #EDF2F7;
            --text-secondary: #A0AEC0;
            --text-muted: #718096;
            --bg-body: #1A202C;
            --bg-card: #2D3748;
            --border-color: #4A5568;
        }
        
        /* Make dark mode styles apply to the body element */
        .dark-mode .card,
        .dark-mode .dropdown-menu,
        .dark-mode .modal-content {
            background-color: var(--bg-card);
            border-color: var(--border-color);
        }
        
        .dark-mode .table,
        .dark-mode .table-striped>tbody>tr:nth-of-type(odd)>* {
            color: var(--text-secondary);
        }
        
        .dark-mode .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .dark-mode .nav-link,
        .dark-mode .navbar-brand,
        .dark-mode .dropdown-item {
            color: var(--text-secondary);
        }
        
        .dark-mode .dropdown-item:hover {
            background-color: var(--bg-body);
            color: var(--text-primary);
        }
        
        .dark-mode .navbar {
            background-color: var(--bg-card) !important;
            border-bottom: 1px solid var(--border-color);
        }
          .dark-mode .border {
            border-color: var(--border-color) !important;
        }
        
        /* Mobile Responsiveness Improvements */
        @media (max-width: 767.98px) {
            .main-content {
                padding: 10px;
            }
            
            .card {
                margin-bottom: 15px;
            }
            
            .dashboard-card {
                margin-bottom: 15px;
            }
            
            .dashboard-card-header {
                padding: 0.75rem 1rem;
            }
            
            .dashboard-card-body {
                padding: 1rem;
            }
            
            .dashboard-card-title {
                font-size: 1rem;
            }
            
            .dashboard-summary-value {
                font-size: 1.1rem;
            }
            
            /* Improved touch targets for mobile */
            .nav-link, 
            .dropdown-item,
            .btn {
                padding: 0.5rem 0.75rem;
                min-height: 44px; /* Minimum touch target size */
                display: flex;
                align-items: center;
            }
            
            /* Better table display on small screens */
            .table-responsive {
                border: 0;
            }
            
            /* Improve form elements on mobile */
            .form-control,
            .form-select {
                font-size: 16px; /* Prevents zoom on iOS */
                height: 44px;
            }
            
            /* Fix for dropdown menus on mobile */
            .dropdown-menu {
                width: 100%;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            /* Optimize navbar for mobile */
            .navbar-brand {
                font-size: 1.1rem;
            }
            
            /* Dark mode toggle more accessible on mobile */
            #theme-toggle {
                padding: 0.5rem;
                margin-left: 0.5rem;
                min-height: 44px;
                min-width: 44px;
                justify-content: center;
            }
            
            /* Optimized sidebar for mobile */
            .sidebar {
                width: 100%;
                position: fixed;
                z-index: 1030;
                top: 0;
                left: 0;
                height: auto;
                max-height: 100vh;
                overflow-y: auto;
            }
        }
    </style><script>
    // Apply theme on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme-mode') || 'light';
        
        // Apply theme
        applyTheme(savedTheme);
        
        // Set the right icon for the theme toggle
        updateThemeToggleIcon(savedTheme);
        
        // Add event listener for theme toggle
        document.getElementById('theme-toggle')?.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            // Save to localStorage
            localStorage.setItem('theme-mode', newTheme);
            
            // Apply the new theme
            applyTheme(newTheme);
            
            // Update the toggle icon
            updateThemeToggleIcon(newTheme);
        });
    });
    
    // Function to apply theme to the document
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.body.classList.remove('dark-mode');
        
        if (theme === 'dark') {
            document.body.classList.add('dark-mode');
        }
    }
    
    // Function to update theme toggle icon
    function updateThemeToggleIcon(theme) {
        const themeIcon = document.getElementById('theme-icon');
        if (!themeIcon) return;
        
        if (theme === 'dark') {
            themeIcon.classList.remove('bi-moon');
            themeIcon.classList.add('bi-sun');
        } else {
            themeIcon.classList.remove('bi-sun');
            themeIcon.classList.add('bi-moon');
        }
    }
    </script>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">