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

body{
    margin:0;
    font-family:Arial;
}

/* SIDEBAR */

.sidebar{
    position:fixed;
    width:220px;
    height:100vh;
    background:#1f2d3d;
    color:white;
}

.sidebar h4{
    text-align:center;
    padding:20px;
}

.sidebar a{
    display:block;
    padding:12px 20px;
    color:white;
    text-decoration:none;
}

.sidebar a:hover{
    background:#2c3e50;
}

/* MAIN CONTENT */

.main-content{
    margin-left:220px;
    padding:25px;
}

</style>

</head>

<body>

<?php require_once __DIR__ . "/sidebar.php"; ?>

<div class="main-content">