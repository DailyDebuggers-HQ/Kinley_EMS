<?php

require_once __DIR__ . "/../config/database.php";

class Student {

    public static function all($conn, $order = 'ASC') {
        $order = strtoupper($order);
        if ($order !== 'ASC' && $order !== 'DESC'){
            $order = 'ASC';
        }

        $sql = "SELECT st.studentID, st.firstname, st.lastname, st.middlename, st.age, sp.courseID, c.courseName
                FROM student_programs sp
                JOIN students st ON sp.student_id = st.studentID
                JOIN course c ON sp.courseID = c.courseID
                ORDER BY st.studentID $order";
        return $conn->query($sql);
    }

    public static function exists($conn, $lastname, $firstname, $middlename) {
        if ($middlename === null) {
            $stmt = $conn->prepare("SELECT 1 FROM students WHERE lastname=? AND firstname=? AND middlename IS NULL");
            $stmt->bind_param("ss", $lastname, $firstname);
        } else {
            $stmt = $conn->prepare("SELECT 1 FROM students WHERE lastname=? AND firstname=? AND middlename=?");
            $stmt->bind_param("sss", $lastname, $firstname, $middlename);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res && $res->num_rows > 0;
    }

    public static function create($conn, $lastname, $firstname, $middlename, $age, $courseID) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO students (lastname, firstname, middlename, age) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $lastname, $firstname, $middlename, $age);
            $stmt->execute();
            $studentID = $stmt->insert_id;

            $stmtProg = $conn->prepare("INSERT INTO student_programs (student_id, courseID) VALUES (?, ?)");
            $stmtProg->bind_param("ii", $studentID, $courseID);
            $stmtProg->execute();

            $conn->commit();
            return $studentID;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    public static function getStudentInfo($conn, $studentID) {
        $sql = "SELECT st.firstname, st.lastname, st.middlename, co.courseDesc, st.age
                FROM student_programs sp
                JOIN students st ON sp.student_id = st.studentID
                JOIN course co ON sp.courseID = co.courseID
                WHERE sp.student_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res && $res->num_rows > 0 ? $res->fetch_assoc() : null;
    }

    public static function getEnrollmentPeriods($conn, $studentID) {
        $sql = "SELECT se.enrollmentID, se.semester, ay.academicYear
            FROM student_enrollments se
            JOIN academic_years ay ON se.acadYearID = ay.acadYearID
            WHERE se.studEnrollID = (SELECT sp.studEnrollID FROM student_programs sp WHERE sp.student_id = ? LIMIT 1)
            ORDER BY ay.academicYear ASC, se.semester ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $res = $stmt->get_result();

        $periods = [];
        while ($row = $res->fetch_assoc()) {
            $periods[] = $row;
        }
        return $periods;
    }

    public static function getStudentHistory($conn, $enrollmentID) {
        $sql = "SELECT cc.courCurID, c.subjectCode, c.subdescription, c.units, g.midterm, g.final
                FROM grades g
                JOIN course_curriculum cc ON g.courCurID = cc.courCurID
                JOIN curriculum c ON cc.curID = c.curID
                WHERE g.enrollmentID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $enrollmentID);
        $stmt->execute();
        $res = $stmt->get_result();
        $history = [];
        while ($row = $res->fetch_assoc()) {
            $history[] = $row;
        }
        return $history;
    }

    public static function statusVerifier($conn, $enrollmentID) {
        $sql = "SELECT status from student_enrollments where enrollmentID = ?";
        $stmt = $conn->prepare($sql);
        $stmt -> bind_param("i", $enrollmentID);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result && $result->num_rows > 0 ? $result->fetch_assoc()['status']: null;
    }
}
