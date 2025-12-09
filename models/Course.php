<?php

require_once __DIR__ . "/../config/database.php";


class Course {

    public static function all($conn) {
        return $conn->query("SELECT * FROM course");
    }


    public static function find($conn, $courseCode) {
        $stmt = $conn->prepare("SELECT * from course where courseCode = ?");
        $stmt->bind_param("i", $courseCode);
        $stmt->execute();
        
        $result = $stmt->get_result();

        if (!$result) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public static function exists($conn, $courseName, $units){
        $stmt= $conn->prepare("SELECT * from course where courseName = ? and units = ?");
        $stmt->bind_param("si", $courseName, $units);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public static function create($conn, $courseName, $units) {
        $stmt = $conn->prepare("INSERT INTO course (courseName, units) VALUES (?, ?)");
        $stmt->bind_param("si", $courseName, $units);
        return $stmt->execute();
    }
}