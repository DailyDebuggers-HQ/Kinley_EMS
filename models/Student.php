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

    public static function create($conn, $lastname, $firstname, $middlename, $age, $courseID) {
        $conn->begin_transaction();

        try{
            $stmt = $conn->prepare("INSERT INTO students (lastname, firstname, middlename, age) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $lastname, $firstname, $middlename, $age);
            $stmt->execute();
            $studentID = $stmt->insert_id;

            $stmtProg = $conn->prepare("INSERT INTO student_programs (student_id, courseID) VALUES (?, ?)");
            $stmtProg->bind_param("ii", $studentID, $courseID);
            $stmtProg->execute();

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
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

    public static function getEnrollmentPeriods($conn, $studentID) {

        $years = [];
        $res = $conn->query("SELECT academicYear FROM academic_years ORDER BY academicYear asc");
        while ($row = $res->fetch_assoc()) {
            $years[] = $row['academicYear'];
        }

        $enrollments=[];

        $sql = "SELECT se.enrollmentID, ay.academicYear, se.semester
                FROM student_enrollments se
                JOIN student_programs sp ON se.studProgID = sp.studProgID
                JOIN academic_years ay ON se.acadYearID = ay.acadYearID
                WHERE sp.student_id = ?
                ORDER BY ay.academicYear asc, se.semester asc";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $enrollments[$row['academicYear']][$row['semester']] = $row['enrollmentID'];
        }

        $periods = [];
        foreach ($years as $year) {
            foreach ([1, 2, 0] as $semester) {
                $periods[] = [
                    'academicYear' => $year,
                    'semester' => $semester,
                    'enrollmentID' => $enrollments[$year][$semester] ?? null
                ];
            }
        }

        return $periods;
    }

    public static function studentHistory($conn, $enrollmentID) {
        $sql = "SELECT cc.courCurID, c.subjectCode, c.subdescription, c.units, g.midterm, g.final
                FROM grades g
                JOIN course_curriculum cc ON g.curID = cc.courCurID
                JOIN curriculum c ON cc.curID = c.curID
                WHERE g.enrollmentID = ?
                ORDER BY c.yearlevel asc, c.semester asc";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $enrollmentID);
        $stmt->execute();
        return $stmt->get_result();
    }
}