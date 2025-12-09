<?php

require_once __DIR__ . "/../config/database.php";


class Student {

    public static function all($conn) {
        return $conn->query("SELECT * FROM student order by lastname asc");
    }


    public static function find($conn, $studentID) {
        $stmt = $conn->prepare("SELECT * from student where studentID = ?");
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        
        $result = $stmt->get_result();

        if (!$result) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public static function exists($conn, $lastname, $firstname) {
        $stmt = $conn->prepare("SELECT * FROM student where lastname=? and firstname = ?");
        $stmt->bind_param("ss", $lastname, $firstname);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public static function create($conn, $lastname, $firstname) {
        $stmt = $conn->prepare("INSERT INTO student (lastname, firstname) VALUES (?, ?)");
        $stmt->bind_param("ss", $lastname, $firstname);
        return $stmt->execute();
    }
}