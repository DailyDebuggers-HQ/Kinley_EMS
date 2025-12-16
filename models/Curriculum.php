<?php

require_once __DIR__ . "/../config/database.php";


class Curriculum {

    public static function all($conn, $order = 'ASC') {
        $order = strtoupper($order);

        if ($order !== 'ASC' && $order !== 'DESC'){
            $order = 'ASC';
        }

        return $conn->query("SELECT * FROM curriculum order by subjectCode $order");
    }

    public static function exists($conn, $subjectCode, $semester, $yearlevel, $subdescription){
        $stmt= $conn->prepare("SELECT * from curriculum where subjectCode = ? and semester = ? and yearlevel = ? and subdescription = ?");
        $stmt->bind_param("ssss", $subjectCode, $semester, $yearlevel, $subdescription);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public static function create($conn, $subjectCode, $semester, $yearlevel, $subdescription, $units) {
        $stmt = $conn->prepare("INSERT INTO curriculum (subjectCode, semester, yearlevel, subdescription, units) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $subjectCode, $semester, $yearlevel, $subdescription, $units);
        return $stmt->execute();
    }
}