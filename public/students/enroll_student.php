<?php 
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../controllers/EnrollmentController.php";

$studentID = $_GET['studentID'] ?? null;
if (!$studentID || !is_numeric($studentID)) {
    die("Invalid student ID.");
}

// Get student info and active program
$stmt = $conn->prepare("
    SELECT sp.studProgID, sp.courseID, s.firstname, s.lastname, s.middlename, c.courseName
    FROM student_programs sp
    JOIN students s ON sp.student_id = s.studentID
    JOIN course c ON sp.courseID = c.courseID
    WHERE s.studentID = ? AND sp.status='ACTIVE'
    LIMIT 1
");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) die("Active program not found for the student.");

// Academic years
$acadYears = $conn->query("SELECT acadYearID, academicYear FROM academic_years ORDER BY academicYear DESC");

// Subjects
$subjects = [];
$message = $status = "";
$selectedAcadYear = $_POST['acadYearID'] ?? '';
$selectedSemester = $_POST['semester'] ?? '';
$selectedYearlevel = $_POST['yearlevel'] ?? '';
$selectedSubjects = $_POST['subjects'] ?? [];

if (isset($_POST['load_subjects'])) {
    $semester  = (int)$selectedSemester;
    $yearlevel = (int)$selectedYearlevel;

    $subStmt = $conn->prepare("
        SELECT cc.courCurID, cur.subdescription
        FROM course_curriculum cc
        JOIN curriculum cur ON cur.curID = cc.curID
        WHERE cc.courseID = ? AND cur.semester = ? AND cur.yearLevel = ?
    ");
    $subStmt->bind_param("iii", $student['courseID'], $semester, $yearlevel);
    $subStmt->execute();
    $subjects = $subStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $check = $conn->prepare("
        SELECT 1 FROM student_enrollments 
        WHERE studEnrollID=? AND acadYearID=? AND semester=? LIMIT 1
    ");
    $check->bind_param("iii", $student['studProgID'], $selectedAcadYear, $selectedSemester);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $status  = "error";
        $message = "Student is already enrolled in this academic year and semester.";
    }
}

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['confirm_enroll'])) {
    try {
        $acadYearID = (int)$selectedAcadYear;
        $semester   = (int)$selectedSemester;
        $yearlevel  = (int)$selectedYearlevel;
        $subjects   = $_POST['subjects'] ?? null;

        $result = EnrollmentController::enroll(
            $conn,
            $student['studProgID'],
            $acadYearID,
            $semester,
            $yearlevel,
            $subjects
        );

        $status  = $result['status'];
        $message = $result['message'] . ($result['enrollmentID'] ?? '');

    } catch (Exception $e) {
        $status  = "error";
        $message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Enroll Student</title>
        <style>
            body { 
                font-family: Arial; 
                background: #f9f9f9; 
                padding: 20px; 
            }
            .container { 
                max-width: 600px;
                margin: auto; 
                background: #fff; 
                padding: 20px; 
                border-radius: 8px; 
            }
            h2 { 
                text-align: center; 
                margin-bottom: 5px; 
            }
            p { 
                text-align: center; 
                margin-bottom: 20px; 
                font-weight: bold; 
            }
            form { 
                margin-bottom: 20px; 
            }
            select, input[type=checkbox], button { 
                width: 100%; 
                margin-bottom: 12px; 
                padding: 8px; 
            }
            button { 
                cursor: pointer; 
            }
            .message { 
                padding: 10px; 
                margin-bottom: 15px; 
                border-radius: 5px; 
                font-weight: bold; 
            }
            .success { 
                background: #d4edda; 
                color: #155724; 
            }
            .error { 
                background: #f8d7da; 
                color: #721c24; 
            }
            .checkbox-group {
                margin-bottom: 16px;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .checkbox-group label {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 15px;
                cursor: pointer;
            }

            .checkbox-group input[type="checkbox"] {
                width: 18px;
                height: 18px;
                accent-color: #4CAF50;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></h2>
            <p><?= htmlspecialchars($student['courseName']) ?></p>

            <?php if ($message): ?>
                <div class="message <?= $status ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <button type="button" onclick="window.location.href='/enrollment_system/public/index.php'">Back to Dashboard</button>

            <!-- Load Subjects -->
            <form method="POST">
                <label>Academic Year</label>
                <select name="acadYearID" required>
                    <option value="">Select</option>
                    <?php while ($ay = $acadYears->fetch_assoc()): ?>
                        <option value="<?= $ay['acadYearID'] ?>" <?= ($selectedAcadYear == $ay['acadYearID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ay['academicYear']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Semester</label>
                <select name="semester" required>
                    <option value="">Select</option>
                    <option value="1" <?= ($selectedSemester == 1) ? 'selected' : '' ?>>First Semester</option>
                    <option value="2" <?= ($selectedSemester == 2) ? 'selected' : '' ?>>Second Semester</option>
                </select>

                <label>Year Level</label>
                <select name="yearlevel" required>
                    <option value="">Select</option>
                    <?php for ($i=1; $i<=4; $i++): ?>
                        <option value="<?= $i ?>" <?= ($selectedYearlevel == $i) ? 'selected' : '' ?>><?= $i ?><?= $i==1?'st':($i==2?'nd':($i==3?'rd':'th')) ?> Year</option>
                    <?php endfor; ?>
                </select>

                <button type="submit" name="load_subjects">Load Subjects</button>
            </form>

            <!-- Enroll -->
            <?php if (!empty($subjects)): ?>
            <form method="POST">
                <input type="hidden" name="acadYearID" value="<?= $selectedAcadYear ?>">
                <input type="hidden" name="semester" value="<?= $selectedSemester ?>">
                <input type="hidden" name="yearlevel" value="<?= $selectedYearlevel ?>">
                <input type="hidden" name="confirm_enroll" value="1">

                <h3>Subjects</h3>
                <div class="checkbox-group">
                    <?php foreach ($subjects as $sub): ?>
                        <label>
                            <input type="checkbox" name="subjects[]" value="<?= $sub['courCurID'] ?>"
                                <?= in_array($sub['courCurID'], $selectedSubjects) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($sub['subdescription']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit">Enroll Student</button>
            </form>
            <?php endif; ?>
        </div>
    </body>
</html>
