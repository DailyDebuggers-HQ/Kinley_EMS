<?php

session_start();

session_unset();


$_SESSION = [];

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

header("Location: index.php");
exit();
?>