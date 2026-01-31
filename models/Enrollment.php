<?php 
require_once __DIR__ . "/../config/database.php";

class Enrollment {

    public static function exists($conn, $enrollmentID, $courCurID){
        $stmt = $conn->prepare("SELECT 1 FROM grades WHERE enrollmentID = ? AND courCurID = ? LIMIT 1");
        if (!$stmt) return false;

        $stmt->bind_param("ii", $enrollmentID, $courCurID);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public static function enroll($conn, $studProgID, $acadYearID, $semester, $manualSubjects = null) {
        $conn->begin_transaction();
        try {

            $stmt = $conn->prepare("SELECT courseID, startDate FROM student_programs WHERE studProgID=? AND status='ACTIVE' LIMIT 1");
            $stmt->bind_param("i", $studProgID);
            $stmt->execute();
            $program = $stmt->get_result()->fetch_assoc();
            
            if (!$program) throw new Exception("Active program not found for the student.");

            $courseID = $program['courseID'];
            $startYear = (int)date('Y', strtotime($program['startDate']));
            $currentYear = (int)date('Y');
            $yearlevel = $currentYear - $startYear + 1;


            $check = $conn->prepare("SELECT 1 FROM student_enrollments WHERE studEnrollID=? AND acadYearID=? AND semester=?");
            $check->bind_param("iii", $studProgID, $acadYearID, $semester);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Student is already enrolled for this academic year and semester.");
            }


            $stmt = $conn->prepare("INSERT INTO student_enrollments (studEnrollID, acadYearID, semester) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $studProgID, $acadYearID, $semester);
            $stmt->execute();
            $enrollmentID = $conn->insert_id;


            if ($manualSubjects && is_array($manualSubjects) && count($manualSubjects) > 0) {
                $subjectsToEnroll = $manualSubjects; // manual selection
            } else {

                $subStmt = $conn->prepare("SELECT cc.courCurID
                    FROM course_curriculum cc
                    JOIN curriculum c ON c.curID = cc.curID
                    WHERE cc.courseID=? AND c.semester=? AND c.yearLevel=?
                ");
                $subStmt->bind_param("iii", $courseID, $semester, $yearlevel);
                $subStmt->execute();
                $res = $subStmt->get_result();
                $subjectsToEnroll = [];
                while ($row = $res->fetch_assoc()) {
                    $subjectsToEnroll[] = $row['courCurID'];
                }
            }


            foreach ($subjectsToEnroll as $courCurID) {

                $gradeStmt = $conn->prepare("INSERT INTO grades (enrollmentID, courCurID) VALUES (?, ?)");
                $gradeStmt->bind_param("ii", $enrollmentID, $courCurID);
                $gradeStmt->execute();

                $schedStmt = $conn->prepare("SELECT schedID FROM schedule WHERE courCurID=? AND acadYearID=? AND semester=?");
                $schedStmt->bind_param("iii", $courCurID, $acadYearID, $semester);
                $schedStmt->execute();
                $schedRes = $schedStmt->get_result();
                while ($schedRow = $schedRes->fetch_assoc()) {
                    $schedID = $schedRow['schedID'];
                    $insertSched = $conn->prepare("INSERT INTO student_schedule (enrollmentID, schedID) VALUES (?, ?)");
                    $insertSched->bind_param("ii", $enrollmentID, $schedID);
                    $insertSched->execute();
                }
            }

            $feesStmt = $conn->prepare("SELECT SUM(sf.price) AS total
                FROM student_schedule ss
                JOIN schedule s ON ss.schedID = s.schedID
                JOIN subject_fees sf ON sf.courCurID = s.courCurID
                WHERE ss.enrollmentID=? AND sf.acadYearID=? AND sf.semester=?
            ");
            $feesStmt->bind_param("iii", $enrollmentID, $acadYearID, $semester);
            $feesStmt->execute();
            $total = $feesStmt->get_result()->fetch_assoc()['total'] ?? 0;

            // Insert assessment record
            $assessStmt = $conn->prepare("INSERT INTO student_assessment (enrollmentID, totalAmount) VALUES (?, ?)");
            $assessStmt->bind_param("id", $enrollmentID, $total);
            $assessStmt->execute();

            $conn->commit();
            return $enrollmentID;

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
}
