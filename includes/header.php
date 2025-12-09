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
    </head>

    <body>
        <div class="headContainer">
            <nav class="headerLinks">
                <div class="left-links">
                    <a href="/enrollment_system/public/students/">Students</a>
                    <a href="/enrollment_system/public/courses/">Courses</a>
                    <a href="/enrollment_system/public/enrollment/">Enrollment</a>
                </div>

                <a class="logout" href="/enrollment_system/public/logout.php">Logout</a>
            </nav>
        </div>