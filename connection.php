<?php
// Try to get DB_ENV from actual environment variables first
$env = getenv('DB_ENV');

// If DB_ENV is not set (false or empty string), try to infer it from the HTTP host
if ($env === false || $env === '') {
    if (isset($_SERVER['HTTP_HOST'])) {
        $http_host = strtolower($_SERVER['HTTP_HOST']);
        // Check if the host is localhost or an IP address commonly used for local development
        if (in_array($http_host, ['localhost', '127.0.0.1'])) {
            $env = 'local';
        } else {
            // Assume production for any other host.
            // You might want to add more specific checks if you have staging environments, e.g.:
            // } else if (strpos($http_host, 'staging.yourdomain.com') !== false) {
            //     $env = 'staging';
            $env = 'production';
        }
    } else {
        // If HTTP_HOST is not set (e.g., CLI script), default to 'local'.
        // This maintains a safe default for command-line operations.
        $env = 'local';
    }
}

// Default to 'local' if $env is somehow still not set (e.g. empty string from getenv initially)
if ($env === '') { // Ensure $env has a fallback if it was an empty string from getenv and HTTP_HOST logic didn't set it.
    $env = 'local';
}

if ($env == "local") {
    $db_host = getenv('DB_HOST') ?: 'localhost';
    $db_user = getenv('DB_USER') ?: 'root';
    $db_pass = getenv('DB_PASS') ?: '';
    $db_name = getenv('DB_NAME') ?: 'bareshao_f';
    $db_port = getenv('DB_PORT') ?: 3306; // Usually 3306 or 3307 on some local setups
} else if ($env == "production") {
    $db_host = getenv('DB_HOST') ?: '192.250.231.19';
    $db_user = getenv('DB_USER') ?: 'bareshao_f';
    $db_pass = getenv('DB_PASS') ?: 'pg07#cN40';
    $db_name = getenv('DB_NAME') ?: 'bareshao_f';
    $db_port = getenv('DB_PORT') ?: 3306; // Default MySQL port
} else {
    // Fallback or error handling if DB_ENV is neither local nor production
    error_log("Invalid DB_ENV specified: " . $env);
    exit("Invalid environment configuration. Please contact support.");
}

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

// Check connection
if ($conn->connect_errno) {
    // Log the error instead of displaying it
    error_log("Failed to connect to MySQL: " . $conn->connect_error);
    exit("Database connection error. Please try again later.");
}

// Set the character set to utf8mb4
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
    exit("Character set error. Please contact support.");
}
