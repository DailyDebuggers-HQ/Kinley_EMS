<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Enrollment.php";

if (!isset($_GET['studentID'])) {
    header("Location: /enrollment_system/public/students/index.php");
    exit();
}

$studentID = $_GET['studentID'];
$historyresult = Enrollment::getByStudent($conn, $studentID);