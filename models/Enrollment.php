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

    public static function enroll($conn, $studProgID, $acadYearID, $semester, $yearlevel, $manualSubjects = null) {
        $conn->begin_transaction();
        try {
            // Get student program
            $stmt = $conn->prepare("SELECT courseID, startDate FROM student_programs WHERE studProgID=? AND status='ACTIVE' LIMIT 1");
            $stmt->bind_param("i", $studProgID);
            $stmt->execute();
            $program = $stmt->get_result()->fetch_assoc();
            
            if (!$program) throw new Exception("Active program not found for the student.");

            $courseID = $program['courseID'];

            // Calculate year level if not provided
            if (!$yearlevel) {
                $startYear = (int)date('Y', strtotime($program['startDate']));
                $currentYear = (int)date('Y');
                $yearlevel = $currentYear - $startYear + 1;
            }

            // Check if already enrolled
            $check = $conn->prepare("SELECT 1 FROM student_enrollments WHERE studEnrollID=? AND acadYearID=? AND semester=?");
            $check->bind_param("iii", $studProgID, $acadYearID, $semester);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                throw new Exception("Student is already enrolled for this academic year and semester.");
            }

            // Insert enrollment
            $stmt = $conn->prepare("INSERT INTO student_enrollments (studEnrollID, acadYearID, semester) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $studProgID, $acadYearID, $semester);
            $stmt->execute();
            $enrollmentID = $conn->insert_id;

            // Determine subjects to enroll
            if (is_array($manualSubjects) && count($manualSubjects) > 0) {
                $subjectsToEnroll = $manualSubjects;
            } else {
                $subStmt = $conn->prepare("
                    SELECT cc.courCurID
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

            // Enroll subjects
            foreach ($subjectsToEnroll as $courCurID) {
                if (!self::exists($conn, $enrollmentID, $courCurID)) {
                    $gradeStmt = $conn->prepare("INSERT INTO grades (enrollmentID, courCurID) VALUES (?, ?)");
                    $gradeStmt->bind_param("ii", $enrollmentID, $courCurID);
                    $gradeStmt->execute();
                }
            }

            $conn->commit();
            return $enrollmentID;

        } catch (Exception $e) {
            $conn->rollback();
            throw $e; // Let the exception bubble up to the controller/page
        }
    }
}