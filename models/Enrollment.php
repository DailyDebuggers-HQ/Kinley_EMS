<?php

require_once __DIR__ . "/../config/database.php";


class Enrollment {

    public static function all($conn) {
        $sql = ("SELECT e.*, s.firstname, s.lastname, c.courseName
        FROM enrollment e
        JOIN student s on e.studentID = s.studentID
        JOIN course c on e.courseCode = c.courseCode");
        
        return $conn->query($sql);
    }


    public static function getByStudent ($conn, $studentID){
        $stmt = $conn-> prepare ("
        SELECT c.courseName, e.grades
        FROM enrollment e
        JOIN course c on e.courseCode = c.courseCode
        where e.studentID = ?
        order by c.courseName");

        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        return $stmt->get_result();
    }


    public static function find($conn, $enroll_id) {
        $stmt = $conn->prepare("SELECT * from enrollment where enroll_id = ?");
        $stmt->bind_param("i", $enroll_id);
        $stmt->execute();
        
        $result = $stmt->get_result();

        if (!$result) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public static function create($conn, $studentID, $courseCode, $grades = NULL) {

        if (self::isEnrolled($conn, $studentID, $courseCode)) {
            return false;
        }
        $stmt = $conn->prepare("INSERT INTO enrollment (studentID, courseCode, grades) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $studentID, $courseCode, $grades);
        return $stmt->execute();
    }

    //this is for checking if the student is already enrolled in the course

    public static function isEnrolled($conn, $studentID, $courseCode) {
        $stmt = $conn->prepare("SELECT 1 from enrollment where studentID = ? and courseCode = ?");
        $stmt->bind_param("ii", $studentID, $courseCode);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->num_rows > 0;
    }
}