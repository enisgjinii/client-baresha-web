<?php
// Include database connection
include 'connection.php';
include 'csv-income.php';

// Simulate a request to test the response format of get_csv_data.php
session_start();
$_SESSION['user_id'] = 1; // Replace with a valid user ID for testing

// Simulate POST data
$_POST['action'] = 'get_csv_data';
$_POST['start'] = 0;
$_POST['length'] = 10;
$_POST['draw'] = 1;

// Include the server-side script
include 'get_csv_data.php';
?>
