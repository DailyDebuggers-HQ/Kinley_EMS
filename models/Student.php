<?php

require_once __DIR__ . "/../config/database.php";

class Student {

    public static function all($conn, $order = 'ASC', $keyword = null) {
        $order = strtoupper($order);
        if ($order !== 'ASC' && $order !== 'DESC'){
            $order = 'ASC';
        }

        $sql = "SELECT st.studentID, st.firstname, st.lastname, st.middlename, st.birthdate, sp.courseID, c.courseName
                FROM student_programs sp
                JOIN students st ON sp.student_id = st.studentID
                JOIN course c ON sp.courseID = c.courseID";
        
        if ($keyword) {
            $sql .= " WHERE st.lastname LIKE ? 
                    OR st.firstname LIKE ? 
                    OR st.middlename LIKE ? ";
        }

        $sql .= " ORDER BY st.studentID $order";

        $stmt = $conn->prepare($sql);

        if ($keyword){
            $search = "%$keyword%";
            $stmt->bind_param("sss", $search, $search, $search);
        }
        $stmt->execute();
        return $stmt->get_result();
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

    public static function create($conn, $lastname, $firstname, $middlename, $birthdate, $courseID) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO students (lastname, firstname, middlename, birthdate) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $lastname, $firstname, $middlename, $birthdate);
            $stmt->execute();
            $studentID = $stmt->insert_id;
            $stmt->close();

            $stmtProg = $conn->prepare("INSERT INTO student_programs (student_id, courseID) VALUES (?, ?)");
            $stmtProg->bind_param("ii", $studentID, $courseID);
            $stmtProg->execute();
            $stmtProg->close();

            $conn->commit();
            return $studentID;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    public static function getStudentInfo($conn, $studentID) {
        $sql = "SELECT st.firstname, st.lastname, st.middlename, co.courseDesc, st.birthdate
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
            WHERE se.studEnrollID = (SELECT sp.student_id FROM student_programs sp WHERE sp.student_id = ? LIMIT 1)
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

    public static function getStudentSched ($conn, $enrollmentID){
        $sql = "SELECT cc.courCurID, c.subjectCode, c.subdescription, s.day, s.start_time, s.end_time, s.room, s.section, c.units, sf.price
            FROM student_schedule ss
            JOIN schedule s ON ss.schedID = s.schedID
            JOIN course_curriculum cc ON s.courCurID = cc.courCurID
            JOIN curriculum c ON cc.curID = c.curID
            JOIN subject_fees sf ON cc.courCurID = sf.courCurID
            WHERE ss.enrollmentID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $enrollmentID);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }

        // Map full day names to abbreviations
        $dayMap = ['MON'=>'M','TUE'=>'T','WED'=>'W','THU'=>'Th','FRI'=>'F','SAT'=>'S'];

        // Aggregate rows by subject (courCurID)
        $grouped = [];
        foreach ($rows as $row) {
            $id = $row['courCurID'];

            // Format day abbreviation
            $abbrDay = $dayMap[$row['day']] ?? $row['day'];

            if (!isset($grouped[$id])) {
                $grouped[$id] = $row;
                $grouped[$id]['days'] = $abbrDay;
            } else {
                $grouped[$id]['days'] .= $abbrDay;
            }
        }

        // Return as a simple array
        return array_values($grouped);
    }

    public static function getStudentAssessment($conn, $enrollmentID){
        $stmt = $conn->prepare("SELECT totalAmount FROM student_assessment WHERE enrollmentID = ?");
        $stmt->bind_param("i", $enrollmentID);
        $stmt->execute();
        $res = $stmt->get_result();
        $total = $res->fetch_assoc()['totalAmount'] ?? 0;
        $stmt->close();

        $stmt = $conn->prepare("SELECT SUM(amountPaid) as totalPaid FROM payments WHERE enrollmentID = ?");
        $stmt->bind_param("i", $enrollmentID);
        $stmt->execute();
        $res = $stmt->get_result();
        $paid = $res->fetch_assoc()['totalPaid'] ?? 0;
        $stmt->close();

        $stmt = $conn->prepare("SELECT paymentDate, amountPaid from payments where enrollmentID = ? order by paymentDate ASC");
        $stmt->bind_param("i", $enrollmentID);
        $stmt->execute();
        $res = $stmt->get_result();
        $payment = [];
        while ($row = $res->fetch_assoc()) {
            $payment[] = $row;
        }
        $stmt->close();

        return [
            'total' => $total,
            'paid' => $paid,
            'balance' => $total - $paid,
            'payments' => $payment
        ];
    }
}