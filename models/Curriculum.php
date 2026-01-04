<?php

require_once __DIR__ . "/../config/database.php";


class Curriculum {

    public static function all($conn, $order = 'ASC', $courseID = null) {
        $order = strtoupper($order);
        if ($order !== 'ASC' && $order !== 'DESC'){
            $order = 'ASC';
        }

        if ($courseID) {
            $sql = "SELECT cu.* FROM curriculum cu 
                    JOIN course_curriculum cc ON cu.curID = cc.curID 
                    WHERE cc.courseID = ? 
                    ORDER BY cu.yearlevel $order, cu.semester $order";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $courseID);
            $stmt->execute();
            return $stmt->get_result();
        } else {
            return $conn->query("SELECT * FROM curriculum ORDER BY yearlevel $order");
        }
    }


    public static function exists($conn, $subjectCode){
        $stmt= $conn->prepare("SELECT * from curriculum where subjectCode = ?");
        $stmt->bind_param("s", $subjectCode);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public static function create($conn, $subjectCode, $semester, $yearlevel, $subdescription, $units) {
        $stmt = $conn->prepare("INSERT INTO curriculum (subjectCode, semester, yearlevel, subdescription, units) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $subjectCode, $semester, $yearlevel, $subdescription, $units);
        return $stmt->execute();
    }
}