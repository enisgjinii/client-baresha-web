<?php
session_start();
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>betaCRM</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <style>
        * {

            font-family: "Lato", sans-serif;
            font-weight: 400;
            font-style: normal;
        }

        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            background-color: white;
            min-height: 100vh;
            border-right: 1px solid #e9ecef;
        }

        .sidebar .nav-link {
            color: #6c757d;
            padding: 0.8rem 1rem;
            border-radius: 0.25rem;
            margin: 0.2rem 0;
        }

        .sidebar .nav-link.active {
            background-color: #e6f0ff;
            color: #0d6efd;
        }

        .main-content {
            padding: 20px;
        }

        .card {
            border-radius: 10px;
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }

        .metric-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }



        .product-img {
            width: 40px;
            height: 40px;
            background-color: #f8f9fa;
        }

        .badge-shipped {
            background-color: #e6f0ff;
            color: #0d6efd;
        }

        .badge-processing {
            background-color: #fff8e6;
            color: #fd7e14;
        }

        .badge-cancelled {
            background-color: #ffe6e6;
            color: #dc3545;
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .pagination .page-link {
            color: #6c757d;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.2.2/af-2.7.0/b-3.2.2/b-colvis-3.2.2/b-html5-3.2.2/b-print-3.2.2/cr-2.0.4/date-1.5.5/fc-5.0.4/fh-4.0.1/kt-2.12.1/r-3.0.4/rg-1.5.1/rr-1.5.0/sc-2.4.3/sb-1.8.2/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.css" rel="stylesheet" integrity="sha384-u4lA6l+7hbl4AEP7E3BuFQZ8+SF5YlUkETNubbdv5971U72pecvgyyCjNLPeO+lL" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="row">