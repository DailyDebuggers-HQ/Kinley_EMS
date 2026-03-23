<?php

require_once __DIR__ . "/../config/database.php";


class Curriculum {

    public static function fetchCurByCourse($conn, $courseID, $acadYearID = null, $order = 'ASC') {
        // If no academic year provided, fetch the latest revision per course
        $sql = "SELECT cu.subjectCode, cu.subdescription, cu.yearlevel, cu.semester, cu.units, cc.courseID, cc.acadYearID, cc.revision
                FROM course_curriculum cc
                INNER JOIN curriculum cu ON cc.curID = cu.curID
                WHERE cc.courseID = ? ";

        if ($acadYearID) {
            $sql .= " AND cc.acadYearID = ? ";
        } else {
            // latest revision per course (acadYearID)
            $sql .= " AND (cc.courseID, cc.acadYearID, cc.revision) IN (
                        SELECT courseID, acadYearID, MAX(revision)
                        FROM course_curriculum
                        WHERE courseID = ?
                        GROUP BY acadYearID
                    ) ";
        }

        $sql .= " ORDER BY cu.yearlevel $order, cu.subjectCode $order";

        $stmt = $conn->prepare($sql);

        if ($acadYearID) {
            $stmt->bind_param("ii", $courseID, $acadYearID);
        } else {
            $stmt->bind_param("ii", $courseID, $courseID);
        }

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

    public static function create($conn, $subjectCode, $semester, $yearlevel, $subdescription, $units, $courseID, $acadYearID, $revision = 1) {
        $stmt = $conn->prepare(
            "INSERT INTO course_curriculum (curID, courseID, acadYearID, revision)
            SELECT curID, ?, ?, ? FROM curriculum WHERE subjectCode = ?"
        );
        $stmt->bind_param("iiis", $courseID, $acadYearID, $revision, $subjectCode);
        return $stmt->execute();
    }

}