<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Course.php";

class CourseController {
    public static function add($conn, $courseName, $courseDesc){
        if (Course::exists($conn, $courseName, $courseDesc)){
            return [
                "status"=> "error",
                "message"=> "Course already exists!"
            ];
        }

        else{
            if (Course::create($conn, $courseName, $courseDesc)){
                return [
                    "status" => "success",
                    "message" => "Course added successfully!"
                ];
            }

            return [
                "status" => "error",
                "message" => "Error adding course"
            ];
        }
    }
}