<?php

require_once __DIR__ . "/../config/database.php";


class Curriculum {

    public static function fetchCurByCourse($conn, $courseID, $order = 'ASC') {
        $stmt = $conn->prepare(
            "SELECT cu.subjectCode, cu.subdescription, cu.yearlevel, cu.semester, cu.units, cc.courseID
            from course_curriculum cc
            inner join curriculum cu on cc.curID = cu.curID
            where cc.courseID = ?
            order by cu.yearlevel $order, cu.semester $order"
        );
        $stmt->bind_param("i", $courseID);
        $stmt->execute();
        $result = $stmt->get_result();

        $grouped = [];

        while($row = $result->fetch_assoc()) {
            $year = $row['yearlevel'];
            $semester = $row['semester'];

            $grouped[$year][$semester][] = $row;
        }

        return $grouped;
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