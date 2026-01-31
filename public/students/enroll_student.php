<?php 
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../controllers/EnrollmentController.php";

// Fetch students
$students = $conn->query("
    SELECT sp.studProgID, s.firstname, s.lastname, s.middlename, c.courseName
    FROM student_programs sp
    JOIN students s ON sp.student_id = s.studentID
    JOIN course c ON sp.courseID = c.courseID
    WHERE sp.status='ACTIVE'
");

// Fetch academic years
$acadYears = $conn->query("SELECT acadYearID, academicYear FROM academic_years ORDER BY academicYear DESC");

// Fetch subjects if a course is selected
$subjects = [];
if (isset($_POST['courseID'])) {
    $courseID = (int)$_POST['courseID'];
    $semester = (int)$_POST['semester'];
    $yearlevel = (int)$_POST['yearlevel'];

    $subRes = $conn->prepare("
        SELECT cc.courCurID, c.subdescription
        FROM course_curriculum cc
        JOIN curriculum c ON c.curID = cc.curID
        WHERE cc.courseID=? AND c.semester=? AND c.yearLevel=?
    ");
    $subRes->bind_param("iii", $courseID, $semester, $yearlevel);
    $subRes->execute();
    $res = $subRes->get_result();
    while ($row = $res->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// Handle form submission
$message = $status = "";
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['studProgID'])) {
    $studProgID = trim($_POST['studProgID']);
    $acadYearID = trim($_POST['acadYearID']);
    $semester = trim($_POST['semester']);
    $manualSubjects = $_POST['subjects'] ?? null;

    $result = EnrollmentController::enroll($conn, $studProgID, $acadYearID, $semester, $manualSubjects);

    $status = $result['status'];
    $message = $result['message'] . ($result['enrollmentID'] ?? '');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Enroll Student</title>
<style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
    h1 { font-weight: 400; }
    form { background: #fff; padding: 20px; border-radius: 8px; max-width: 500px; }
    select, button { display: block; width: 100%; margin-bottom: 15px; padding: 8px; }
    button { background: #4CAF50; color: #fff; border: none; cursor: pointer; }
    button:hover { background: #45a049; }
    .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
</style>
</head>
<body>

<h1>Enroll Student</h1>

<?php if($message): ?>
    <div class="message <?= $status === "error" ? 'error' : 'success' ?>"><?= $message ?></div>
<?php endif; ?>

<form method="POST">
    <label for="studProgID">Select Student:</label>
    <select name="studProgID" id="studProgID" required>
        <option value="">Select Student</option>
        <?php while($row = $students->fetch_assoc()): ?>
            <option value="<?= $row['studProgID'] ?>"
                <?= (isset($_POST['studProgID']) && $_POST['studProgID'] == $row['studProgID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname'] . ' ' . $row['middlename']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label for="acadYearID">Select Academic Year:</label>
    <select name="acadYearID" id="acadYearID" required>
        <option value="">Select Academic Year</option>
        <?php while($row = $acadYears->fetch_assoc()): ?>
            <option value="<?= $row['acadYearID'] ?>" <?= (isset($_POST['acadYearID']) && $_POST['acadYearID'] == $row['acadYearID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['academicYear']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label for="semester">Select Semester:</label>
    <select name="semester" id="semester" required>
        <option value="">Select Semester</option>
        <option value="1" <?= (isset($_POST['semester']) && $_POST['semester']==1) ? 'selected':'' ?>>First Semester</option>
        <option value="2" <?= (isset($_POST['semester']) && $_POST['semester']==2) ? 'selected':'' ?>>Second Semester</option>
    </select>

    <?php if(!empty($subjects)): ?>
        <label>Subjects to Enroll (Optional, you can deselect some):</label>
        <?php foreach($subjects as $sub): ?>
            <div>
                <input type="checkbox" name="subjects[]" value="<?= $sub['courCurID'] ?>" checked>
                <?= htmlspecialchars($sub['subdescription']) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <button type="submit">Enroll Student</button>
    <button type="button" onclick="window.location.href='/enrollment_system/public/index.php'">Back to Dashboard</button>
</form>
<!--We need to fix the manual adding of subs -->
</body>
</html>
