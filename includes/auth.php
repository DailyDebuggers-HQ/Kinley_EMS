<?php

session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: /enrollment_system/public/login.php");
    exit();
}

?>