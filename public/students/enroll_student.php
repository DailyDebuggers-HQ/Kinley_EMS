<?php 
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../controllers/EnrollmentController.php";

$studentID = $_GET['studentID'] ?? null;
if (!$studentID || !is_numeric($studentID)) {
    die("Invalid student ID.");
}

/* ===============================
   GET ACTIVE STUDENT PROGRAM
================================= */
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

/* ===============================
   LOAD ACADEMIC YEARS
================================= */
$acadYears = $conn->query("
    SELECT acadYearID, academicYear 
    FROM academic_years 
    ORDER BY academicYear DESC
");

$subjects = [];
$message = $status = "";

$selectedAcadYear  = $_POST['acadYearID'] ?? '';
$selectedSemester  = $_POST['semester'] ?? '';
$selectedYearlevel = $_POST['yearlevel'] ?? '';
$selectedSubjects  = $_POST['subjects'] ?? [];

/* ===============================
   PROGRESSIVE SUBJECT LOADING
================================= */
if ($_SERVER['REQUEST_METHOD'] === "POST" && !isset($_POST['confirm_enroll'])) {

    if (empty($selectedAcadYear)) {
        $status  = "error";
        $message = "Please select an academic year first.";
    } else {
        // Get latest curriculum revision applicable for the selected academic year
        $query = "
            SELECT cc.courCurID, cur.subjectCode, cur.subdescription, cur.units
            FROM course_curriculum cc
            JOIN curriculum cur ON cur.curID = cc.curID
            WHERE cc.courseID = ?
              AND cc.acadYearID = (
                  SELECT MAX(cc2.acadYearID)
                  FROM course_curriculum cc2
                  WHERE cc2.courseID = ? 
                    AND cc2.acadYearID <= ?
              )
        ";

        $params = [$student['courseID'], $student['courseID'], (int)$selectedAcadYear];
        $types  = "iii";

        // Apply optional semester filter
        if (!empty($selectedSemester)) {
            $query .= " AND cur.semester = ?";
            $params[] = (int)$selectedSemester;
            $types .= "i";
        }

        // Apply optional year level filter
        if (!empty($selectedYearlevel)) {
            $query .= " AND cur.yearLevel = ?";
            $params[] = (int)$selectedYearlevel;
            $types .= "i";
        }

        // Prepare and execute query
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        /* Duplicate enrollment check ONLY if complete selection */
        if ($selectedAcadYear && $selectedSemester && $selectedYearlevel) {
            $check = $conn->prepare("
                SELECT 1 
                FROM student_enrollments 
                WHERE studEnrollID = ? 
                  AND acadYearID = ? 
                  AND semester = ?
                LIMIT 1
            ");
            $check->bind_param("iii", 
                $student['studProgID'], 
                $selectedAcadYear, 
                $selectedSemester
            );
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $status  = "error";
                $message = "Student is already enrolled in this academic year and semester.";
            }
        }
    }
}
/* ===============================
   CONFIRM ENROLLMENT
================================= */
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['confirm_enroll'])) {

    try {
        $result = EnrollmentController::enroll(
            $conn,
            $student['studProgID'],
            (int)$selectedAcadYear,
            (int)$selectedSemester,
            (int)$selectedYearlevel,
            $_POST['subjects'] ?? null
        );

        $status  = $result['status'];
        $message = $result['message'];

    } catch (Exception $e) {
        $status  = "error";
        $message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enroll Student</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        h2, p { text-align: center; }
        select, button { width: 100%; margin-bottom: 12px; padding: 8px; }
        .checkbox-group { margin-top: 15px; }
        .checkbox-group label { display: block; margin-bottom: 8px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        table {
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f8f9fa;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="container">

    <h2><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></h2>
    <p><?= htmlspecialchars($student['courseName']) ?></p>

    <?php if ($message): ?>
        <div class="message <?= $status ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <button onclick="window.location.href='/enrollment_system/public/students/index.php'">
        Back to Student List
    </button>

    <!-- FILTER FORM -->
    <form method="POST" id="filterForm">

        <label>Academic Year</label>
        <select name="acadYearID" onchange="this.form.submit()">
            <option value="">Select</option>
            <?php while ($ay = $acadYears->fetch_assoc()): ?>
                <option value="<?= $ay['acadYearID'] ?>"
                    <?= ($selectedAcadYear == $ay['acadYearID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ay['academicYear']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Semester</label>
        <select name="semester" onchange="this.form.submit()">
            <option value="">Select</option>
            <option value="1" <?= ($selectedSemester == 1) ? 'selected' : '' ?>>First Semester</option>
            <option value="2" <?= ($selectedSemester == 2) ? 'selected' : '' ?>>Second Semester</option>
        </select>

        <label>Year Level</label>
        <select name="yearlevel" onchange="this.form.submit()">
            <option value="">Select</option>
            <?php for ($i=1; $i<=4; $i++): ?>
                <option value="<?= $i ?>"
                    <?= ($selectedYearlevel == $i) ? 'selected' : '' ?>>
                    <?= $i ?><?= $i==1?'st':($i==2?'nd':($i==3?'rd':'th')) ?> Year
                </option>
            <?php endfor; ?>
        </select>
    </form>

    <!-- SUBJECT LIST -->
    <?php if (!empty($subjects)): ?>
    <form method="POST">

        <input type="hidden" name="acadYearID" value="<?= $selectedAcadYear ?>">
        <input type="hidden" name="semester" value="<?= $selectedSemester ?>">
        <input type="hidden" name="yearlevel" value="<?= $selectedYearlevel ?>">
        <input type="hidden" name="confirm_enroll" value="1">

        <h3>Subjects</h3>

        <table border="1" width="100%" cellpadding="8" cellspacing="0">
            <thead style="background:#f2f2f2;">
                <tr>
                    <th>Select</th>
                    <th>Subject Code</th>
                    <th>Description</th>
                    <th>Units</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $sub): ?>
                    <tr>
                        <td style="text-align:center;">
                            <input type="checkbox" 
                                name="subjects[]" 
                                value="<?= $sub['courCurID'] ?>"
                                <?= in_array($sub['courCurID'], $selectedSubjects) ? 'checked' : '' ?>>
                        </td>
                        <td><?= htmlspecialchars($sub['subjectCode']) ?></td>
                        <td><?= htmlspecialchars($sub['subdescription']) ?></td>
                        <td style="text-align:center;">
                            <?= htmlspecialchars($sub['units']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit">Enroll Student</button>
    </form>
    <?php endif; ?>

</div>

</body>
</html>