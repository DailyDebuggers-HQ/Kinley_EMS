<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enrollment System</title>
    <link rel="stylesheet" href="/enrollment_system/public/assets/css/style.css">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
            background: #f4f6f9;
        }

        /* SIDEBAR */

        .sidebar {
            width: 220px;
            background: #1f2d3d;
            color: white;
            padding-top: 20px;
            position: fixed;
            height: 100vh;
        }

        .sidebar h4 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            width: 100%;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        .sidebar a:hover {
            background: #2c3e50;
        }

        /* HOVER DROPDOWN */

        .hover-dropdown {
            position: relative;
        }

        .hover-dropdown > a {
            display: block;
            width: 100%;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
        }

        .hover-dropdown > a:hover {
            background: #2c3e50;
        }

        .dropdown-container {
            display: none;
            background: #273746;
        }

        .dropdown-container a {
            display: block;
            padding: 12px 40px;
            font-size: 14px;
            color: white;
            text-decoration: none;
        }

        .dropdown-container a:hover {
            background: #34495e;
        }

        /* Hover trigger */
        .hover-dropdown:hover .dropdown-container {
            display: block;
        }

        /* MAIN CONTENT */

        .main-content {
            margin-left: 220px;
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content-wrapper {
            flex: 1;
        }

        footer {
            background: #ffffff;
            border-top: 1px solid #ddd;
            text-align: center;
            padding: 12px;
        }

    </style>

</head>

<body>

<?php require_once __DIR__ . "/sidebar.php"; ?>

<div class="main-content">
<div class="content-wrapper">