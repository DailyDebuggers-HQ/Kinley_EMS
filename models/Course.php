<?php

require_once __DIR__ . "/../config/database.php";


class Course {

    public static function all($conn, $order = 'ASC') {
        $order = strtoupper($order);
        if ($order !== 'ASC' && $order !== 'DESC'){
            $order = 'ASC';
        }
        return $conn->query("SELECT * FROM course order by courseName $order");
    }

    public static function exists($conn, $courseName, $courseDesc) {
        $stmt = $conn->prepare("SELECT * FROM course where courseName = ? and courseDesc = ?");
        $stmt->bind_param("ss", $courseName, $courseDesc);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public static function create($conn, $courseName, $courseDesc) {
        $stmt = $conn->prepare("INSERT INTO course (courseName, courseDesc) VALUES (?, ?)");
        $stmt->bind_param("ss", $courseName, $courseDesc);
        return $stmt->execute();
    }
}