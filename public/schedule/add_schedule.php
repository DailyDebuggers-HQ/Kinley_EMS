<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/Schedule.php';

$message = "";

// Reset session only if user clicked "Start Fresh / Back to Dashboard"
if (isset($_POST['resetPage'])) {
    unset($_SESSION['scheds'], $_SESSION['termCode'], $_SESSION['acadYearID'], $_SESSION['semester'], $_SESSION['studentID']);
    $_SESSION['scheds'] = [];

    header("Location: /enrollment_system/public/index.php");
    exit;
}

// Initialize schedules array if not yet set
if (!isset($_SESSION['scheds'])) {
    $_SESSION['scheds'] = [];
}

$termCode = $_POST['termCode'] ?? $_SESSION['termCode'] ?? '';
$studentID = $_POST['studentID'] ?? $_SESSION['studentID'] ?? '';

// ----------------------
// Load Academic Term
// ----------------------
if (isset($_POST['loadTerm'])) {
    if (!preg_match('/^\d{3}$/', $termCode)) {
        $message = "Term code must be exactly 3 digits.";
    } else {
        $yearCode = (int) substr($termCode, 0, 2);
        $semester = (int) substr($termCode, 2, 1);

        if ($semester !== 1 && $semester !== 2) {
            $message = "Semester must be 1 or 2.";
        } else {
            $startYear = 2000 + $yearCode - 1;
            $endYear = 2000 + $yearCode;
            $academicYear = "$startYear-$endYear";

            $stmt = $conn->prepare("SELECT acadYearID FROM academic_years WHERE academicYear = ?");
            $stmt->bind_param("s", $academicYear);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result) {
                $_SESSION['termCode'] = $termCode;
                $_SESSION['acadYearID'] = $result['acadYearID'];
                $_SESSION['semester'] = $semester;
                $message = "Academic term loaded successfully.";
            } else {
                $message = "Academic term does not exist.";
            }
        }
    }
}

// ----------------------
// Load Student
// ----------------------
if (isset($_POST['loadStudent'])) {
    if (!is_numeric($studentID)) {
        $message = "Invalid Student ID.";
    } else {
        $_SESSION['studentID'] = (int)$studentID;
        $_SESSION['scheds'] = []; // reset schedules for new student
    }
}

// ----------------------
// Fetch Student Data
// ----------------------
$student = null;
$studentID = $_SESSION['studentID'] ?? null;

if ($studentID) {
    $stmt = $conn->prepare("
        SELECT sp.studProgID, e.enrollmentID, s.firstname, s.middlename, s.lastname, c.courseName 
        FROM student_programs sp
        JOIN students s ON sp.student_id = s.studentID
        JOIN student_enrollments e ON e.studEnrollID = sp.studProgID
        JOIN course c ON sp.courseID = c.courseID
        WHERE sp.student_id = ? AND sp.status = 'ACTIVE'
        LIMIT 1
    ");
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if (!$student) {
        $message = "Student not found!";
        unset($_SESSION['studentID']);
    }
}

// ----------------------
// Add Schedule
// ----------------------
if (isset($_POST['addSchedule'])) {
    $schedID = (int)($_POST['schedID'] ?? 0);

    $stmt = $conn->prepare("
        SELECT sc.schedID, cur.subdescription AS subjectCode, sc.day, sc.start_time AS startTime,
               sc.end_time AS endTime, sc.room, sc.section
        FROM schedule sc
        JOIN course_curriculum cc ON sc.courCurID = cc.courCurID
        JOIN curriculum cur ON cur.curID = cc.curID
        WHERE sc.schedID = ?
    ");
    $stmt->bind_param("i", $schedID);
    $stmt->execute();
    $sched = $stmt->get_result()->fetch_assoc();

    if ($sched) {
        if (!isset($_SESSION['scheds'][$schedID])) {
            $_SESSION['scheds'][$schedID] = $sched;
            $message = "Schedule loaded successfully!";
        } else {
            $message = "Schedule already loaded.";
        }
    } else {
        $message = "Schedule not found.";
    }
}

// ----------------------
// Save Schedules with Duplicate Check
// ----------------------
if (isset($_POST['saveSchedules'])) {
    $selected = $_POST['selectedScheds'] ?? [];
    $enrollmentID = (int)($_POST['enrollmentID'] ?? 0);

    if (empty($selected)) {
        $message = "No schedules selected.";
    } else {
        $alreadyExists = 0;
        $savedCount = 0;

        foreach ($selected as $schedID) {
            $checkStmt = $conn->prepare("SELECT * FROM student_schedule WHERE enrollmentID = ? AND schedID = ?");
            $checkStmt->bind_param("ii", $enrollmentID, $schedID);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result()->fetch_assoc();

            if ($checkResult) {
                $alreadyExists++;
            } else {
                $insertStmt = $conn->prepare("INSERT INTO student_schedule (enrollmentID, schedID) VALUES (?, ?)");
                $insertStmt->bind_param("ii", $enrollmentID, $schedID);
                $insertStmt->execute();
                $savedCount++;
            }
        }

        $_SESSION['scheds'] = [];
        $message = "$savedCount schedule(s) saved successfully.";
        if ($alreadyExists > 0) {
            $message .= " ($alreadyExists schedule(s) already exist and were skipped.)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Assignment</title>
    <style>
        body { font-family: Arial; background:#f4f6f8; }
        .container { width:750px; margin:auto; background:white; padding:20px; border-radius:8px; }
        h2 { text-align:center; }
        input { padding:8px; margin-bottom:10px; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { border:1px solid #ccc; padding:8px; text-align:center; }
        .topRow { display:flex; justify-content:space-between; margin-bottom:15px; }
    </style>
</head>
<body>
    <!-- Reset / Back Button -->
    <form method="POST" style="display:inline;">
        <button type="submit" name="resetPage">Back to Dashboard</button>
    </form>

    <div class="container">
        <h2>Schedule Assignment</h2>

        <?php if (!empty($message)):
            $color = str_contains(strtolower($message), 'not') || str_contains(strtolower($message), 'no') ? 'red' : 'green';
        ?>
            <p style="color:<?= $color ?>"><?= $message ?></p>
        <?php endif; ?>

        <!-- Academic Term Form -->
        <form method="POST">
            <label>Academic Term Code</label>
            <input type="text" name="termCode" value="<?= htmlspecialchars($_SESSION['termCode'] ?? '') ?>" required>
            <button name="loadTerm">Enter</button>
        </form>

        <!-- Student Form -->
        <?php if (isset($_SESSION['acadYearID'])): ?>
            <form method="POST">
                <label>Student ID</label>
                <input type="text" name="studentID" value="<?= htmlspecialchars($studentID ?? '') ?>" required>
                <button name="loadStudent">Enter</button>
            </form>
        <?php endif; ?>

        <!-- Add Schedule Section -->
        <?php if ($student): ?>
            <div class="topRow">
                <p>Student: <?= htmlspecialchars($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']) ?></p>
                <p>Course: <?= htmlspecialchars($student['courseName']) ?></p>
            </div>

            <h3>Add Schedule</h3>
            <form method="POST">
                <input type="hidden" name="studentID" value="<?= htmlspecialchars($studentID) ?>">
                <label>Sched ID</label><br>
                <input type="text" name="schedID" required>
                <button name="addSchedule">Enter</button>
            </form>

            <!-- Loaded Schedules Table -->
            <form method="POST">
                <input type="hidden" name="studentID" value="<?= htmlspecialchars($studentID) ?>">
                <input type="hidden" name="enrollmentID" value="<?= htmlspecialchars($student['enrollmentID']) ?>">

                <table>
                    <tr>
                        <th>Select</th>
                        <th>SchedID</th>
                        <th>Subject</th>
                        <th>Day</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Room</th>
                        <th>Section</th>
                    </tr>
                    <?php foreach ($_SESSION['scheds'] as $sched): ?>
                        <tr>
                            <td><input type="checkbox" name="selectedScheds[]" value="<?= $sched['schedID'] ?>"></td>
                            <td><?= $sched['schedID'] ?></td>
                            <td><?= htmlspecialchars($sched['subjectCode']) ?></td>
                            <td><?= htmlspecialchars($sched['day']) ?></td>
                            <td><?= htmlspecialchars($sched['startTime']) ?></td>
                            <td><?= htmlspecialchars($sched['endTime']) ?></td>
                            <td><?= htmlspecialchars($sched['room']) ?></td>
                            <td><?= htmlspecialchars($sched['section']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <button name="saveSchedules">Save Schedules</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>