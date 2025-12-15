<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Enrollment.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['action']) && $_POST['action'] === 'enroll') {
        $studentID = $_POST['studentID'];
        $courseCode = $_POST['courseCode'];
        $grades = $_POST['grades'] ?? NULL;

        if (Enrollment::isEnrolled($conn, $studentID, $courseCode)) {
            // Student is already enrolled in the course
            header("Location: /enrollment_system/public/enrollment/add_enrollment.php?error=already_enrolled");
            exit();
        }

        Enrollment::create($conn, $studentID, $courseCode, $grades);
        header("Location: /enrollment_system/public/enrollment/add_enrollment.php?success=enrolled");
        exit();
    }
}