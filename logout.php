<?php
session_start();

// Unset all session variables
session_unset();

// Destroy the session.
session_destroy();

// Redirect to login page (or any other appropriate page)
header("Location: login.php"); // Replace login.php with your login page URL
exit();
