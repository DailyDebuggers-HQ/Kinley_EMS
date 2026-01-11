<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Student.php";

class StudentController {

    public static function add($conn, $lastname, $firstname, $middlename, $age, $courseID){
        if (Student::exists($conn, $lastname, $firstname, $middlename)) {
            return ["status"=>"error", "message"=>"Student already exists."];
        }

        $studentID = Student::create($conn, $lastname, $firstname, $middlename, $age, $courseID);
        if ($studentID) {
            return ["status"=>"success", "message"=>"Student added successfully!", "studentID"=>$studentID];
        }

        return ["status"=>"error", "message"=>"Error adding student."];
    }

    public static function getStudentInfo($conn, $studentID) {
        return Student::getStudentInfo($conn, $studentID);
    }

    public static function getEnrollmentPeriods($conn, $studentID) {
        return Student::getEnrollmentPeriods($conn, $studentID);
    }

    public static function getStudentHistory($conn, $enrollmentID) {
        return Student::getStudentHistory($conn, $enrollmentID);
    }

    public static function statusVerifier($conn, $enrollmentID) {
        return Student::statusVerifier($conn, $enrollmentID);
    }
}
