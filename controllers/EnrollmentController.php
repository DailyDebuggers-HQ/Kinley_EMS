<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Enrollment.php";

class EnrollmentController {
    public static function enroll($conn, $studentID, $courseID){
        if (Enrollment::exists($conn, $studentID, $courseID)){
            return [
                "status"=> "error",
                "message"=> "Student is already enrolled in this subject!"
            ];
        }

        else{
            if (Enrollment::enroll($conn, $studentID, $courseID)){
                return [
                    "status" => "success",
                    "message" => "Student enrolled successfully!"
                ];
            }

            return [
                "status" => "error",
                "message" => "Error enrolling student"
            ];
        }
    }
}