<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Curriculum.php";

class CurriculumController {
    public static function add($conn, $subjectCode, $semester, $yearlevel, $subdescription, $units){
        if (Curriculum::exists($conn, $subjectCode)){
            return [
                "status"=> "error",
                "message"=> "Subject Code already exists!"
            ];
        }

        else{
            if (Curriculum::create($conn, $subjectCode, $semester, $yearlevel, $subdescription, $units, null, null)){
                return [
                    "status" => "success",
                    "message" => "Curriculum added successfully!"
                ];
            }

            return [
                "status" => "error",
                "message" => "Error adding curriculum"
            ];
        }
    }
}