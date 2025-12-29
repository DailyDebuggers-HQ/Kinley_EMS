<?php

require_once __DIR__ . "/../config/database.php";


class Student {

    public static function all($conn, $order = 'ASC') {
        $order = strtoupper($order);
        if ($order !== 'ASC' && $order !== 'DESC'){
            $order = 'ASC';
        }
        return $conn->query("SELECT * FROM students order by lastname $order");
    }

    public static function exists($conn, $lastname, $firstname, $middlename) {
        if ($middlename === null) {
            $stmt = $conn->prepare("SELECT 1 from students where lastname = ? and firstname = ? and middlename is null");
            $stmt->bind_param("ss", $lastname, $firstname);
        }
        else{

            $stmt = $conn->prepare("SELECT * FROM students where lastname=? and firstname = ? and middlename = ?");
            $stmt->bind_param("sss", $lastname, $firstname, $middlename);
        }
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public static function create($conn, $lastname, $firstname, $middlename, $age) {
        $stmt = $conn->prepare("INSERT INTO students (lastname, firstname, middlename, age) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $lastname, $firstname, $middlename, $age);
        return $stmt->execute();
    }

    public static function getStudentInfo($conn, $studentID) {
        $sql = "SELECT st.firstname, st.lastname, st.middlename, co.courseDesc
                FROM student_programs sp
                JOIN students st ON sp.student_id = st.id
                JOIN course co ON sp.courseID = co.courseID
                WHERE sp.student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        return $stmt->get_result();
    }

    public static function studentHistory($conn, $studentID) {
        $sql = "SELECT se.subEnID, cu.subjectCode, cu.subdescription, se.midterm, se.final, cu.units
                FROM sub_enrolled se
                JOIN student_programs sp ON se.studProgID = sp.studProgID
                JOIN curriculum cu ON se.curID = cu.curID
                WHERE sp.student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        return $stmt->get_result();
    }
}