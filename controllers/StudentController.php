<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Student.php";

class StudentController {
    public static function add($conn, $lastname, $firstname, $middlename, $age){
        if (Student::exists($conn,$lastname,$firstname, $middlename)){
            return [
                "status"=>"error",
                "message"=>"Student already exists."
            ];
        }

        if (Student::create($conn, $lastname, $firstname, $middlename, $age)){
            return [
                "status"=> "success",
                "message"=> "Student added successfully!"
            ];
        }

        return [
            "status"=> "Error",
            "message"=> "Error adding student."
        ];
    }
}