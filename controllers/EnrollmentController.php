<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Enrollment.php";

class EnrollmentController {
    public static function enroll($conn, $studentID, $acadYearID, $semester, $manualSubjects = null){
        try{
            $enrollmentID = Enrollment::enroll($conn, $studentID, $acadYearID, $semester, $manualSubjects);

            return [
                "status" => "success",
                "message" => "Student enrolled successfully with Enrollment ID: ",
                "enrollmentID" => $enrollmentID
            ];
        } catch (Exception $e){
            return [
                "status" => "error",
                "message" => $e->getMessage()
            ];
        }
    }
}