<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Student.php";

class StudentController {
    public static function add($conn, $lastname, $firstname, $middlename, $age, $courseID){
        if (Student::exists($conn,$lastname,$firstname, $middlename)){
            return [
                "status"=>"error",
                "message"=>"Student already exists."
            ];
        }

        if (Student::create($conn, $lastname, $firstname, $middlename, $age, $courseID)){
            return [
                "status"=> "success",
                "message"=> "Student added successfully!"
            ];
        }

        return [
            "status"=> "error",
            "message"=> "Error adding student."
        ];
    }

    public static function studentInfo ($conn, $studentID) {
        return Student::getStudentInfo($conn, $studentID);
    }

    public static function studentHist($conn, $studentID) {
        return Student::studentHistory($conn, $studentID);
    }
}