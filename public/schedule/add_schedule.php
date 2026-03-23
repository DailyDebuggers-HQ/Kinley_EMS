<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/Schedule.php';

$message = "";

// Reset session only if user clicked "Start Fresh / Back to Dashboard"
if (isset($_POST['resetPage'])) {
    unset(
        $_SESSION['scheds'],
        $_SESSION['termCode'],
        $_SESSION['acadYearID'],
        $_SESSION['semester'],
        $_SESSION['studentID'],
        $_SESSION['yearLevel']
    );
    $_SESSION['scheds'] = [];
    header("Location: /enrollment_system/public/students/index.php");
    exit;
}

// Initialize schedules array if not yet set
if (!isset($_SESSION['scheds'])) {
    $_SESSION['scheds'] = [];
}

$termCode  = $_POST['termCode'] ?? $_SESSION['termCode'] ?? '';
$studentID = $_POST['studentID'] ?? $_SESSION['studentID'] ?? '';
$yearLevel = $_POST['yearLevel'] ?? $_SESSION['yearLevel'] ?? '';

// ----------------------
// Load Academic Term
// ----------------------
if (isset($_POST['loadTerm'])) {
    if (!preg_match('/^\d{3}$/', $termCode)) {
        $message = "Term code must be exactly 3 digits.";
    } else {
        $yearCode  = (int) substr($termCode, 0, 2);
        $semester  = (int) substr($termCode, 2, 1);

        if ($semester !== 1 && $semester !== 2) {
            $message = "Semester must be 1 or 2.";
        } else {
            $startYear    = 2000 + $yearCode - 1;
            $endYear      = 2000 + $yearCode;
            $academicYear = "$startYear-$endYear";

            $stmt   = $conn->prepare("SELECT acadYearID FROM academic_years WHERE academicYear = ?");
            $stmt->bind_param("s", $academicYear);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result) {
                $_SESSION['termCode']  = $termCode;
                $_SESSION['acadYearID'] = $result['acadYearID'];
                $_SESSION['semester']   = $semester;
                $message = "Academic term loaded successfully.";
            } else {
                $message = "Academic term does not exist.";
            }
        }
    }
}

// ----------------------
// Unlock Term Code Input
// ----------------------
if (isset($_POST['changeTerm'])) {
    unset($_SESSION['termCode'], $_SESSION['acadYearID'], $_SESSION['semester']);
}

// ----------------------
// Load Student
// ----------------------
if (isset($_POST['loadStudent'])) {
    if (!is_numeric($studentID)) {
        $message = "Invalid Student ID.";
    } else {
        $_SESSION['studentID'] = (int)$studentID;
        $_SESSION['scheds']    = [];
    }
}

// ----------------------
// Unlock Student ID Input
// ----------------------
if (isset($_POST['changeStudent'])) {
    unset($_SESSION['studentID'], $_SESSION['yearLevel']);
}

// ----------------------
// Set Year Level
// ----------------------
if (isset($_POST['selectYearLevel'])) {
    $_SESSION['yearLevel'] = (int)$yearLevel;
}

// ----------------------
// Fetch Student Data
// ----------------------
$student   = null;
$studentID = $_SESSION['studentID'] ?? null;

if ($studentID) {
    $stmt = $conn->prepare("
    SELECT sp.studProgID,
           e.enrollmentID,
           s.firstname,
           s.middlename,
           s.lastname,
           c.courseName,
           sp.courseID
    FROM student_programs sp
    JOIN students s ON sp.student_id = s.studentID
    JOIN student_enrollments e 
        ON e.studEnrollID = sp.studProgID
    JOIN course c ON sp.courseID = c.courseID
    WHERE sp.student_id = ?
      AND sp.status = 'ACTIVE'
      AND e.acadYearID = ?
      AND e.semester = ?
    LIMIT 1
");
    $stmt->bind_param("iii", $studentID, $_SESSION['acadYearID'], $_SESSION['semester']);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if (!$student) {
        $message = "Student not found!";
        unset($_SESSION['studentID'], $_SESSION['yearLevel']);
    }
}

// ----------------------
// Auto-load Schedules for the Student's Program/Year/Semester
// ----------------------
if ($student && isset($_SESSION['acadYearID'], $_SESSION['semester'], $_SESSION['yearLevel'])) {
    $stmt = $conn->prepare("
        SELECT 
            sc.schedID,
            cur.subdescription AS subjectCode,
            sc.day,
            sc.start_time AS startTime,
            sc.end_time AS endTime,
            sc.room,
            sc.section
        FROM schedule sc
        JOIN course_curriculum cc ON sc.courCurID = cc.courCurID
        JOIN curriculum cur ON cur.curID = cc.curID
        WHERE cc.courseID = ? AND sc.acadYearID = ? AND sc.semester = ? AND cur.yearlevel = ?
        ORDER BY cur.subjectCode, sc.section, sc.day, sc.start_time
    ");

    $stmt->bind_param(
        "iiii",
        $student['courseID'],
        $_SESSION['acadYearID'],
        $_SESSION['semester'],
        $_SESSION['yearLevel']
    );

    $stmt->execute();
    $result = $stmt->get_result();

    $_SESSION['scheds'] = [];
    while ($row = $result->fetch_assoc()) {
        $_SESSION['scheds'][$row['schedID']] = $row;
    }
}

// ----------------------
// Add Schedule manually by SchedID
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
// Save Schedules
// ----------------------
if (isset($_POST['saveSchedules'])) {
    $selected     = $_POST['selectedScheds'] ?? [];
    $enrollmentID = (int)($_POST['enrollmentID'] ?? 0);

    if (empty($selected)) {
        $message = "No schedules selected.";
    } else {
        $alreadyExists = 0;
        $savedCount    = 0;

        foreach ($selected as $schedID) {
            $checkStmt   = $conn->prepare("SELECT * FROM student_schedule WHERE enrollmentID = ? AND schedID = ?");
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

        require_once __DIR__ . '/../../models/Student.php';
        Student::updateStudentAssessment($conn, $enrollmentID);

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
        /* Keep your existing CSS */
        body { font-family: Arial; background: #f4f6f8; margin: 0; padding: 20px 0; }
        .container { width: 850px; margin: auto; background: white; padding: 25px 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h2 { text-align:center; color:#1f2d3d; margin-bottom:25px; }
        .form-inline { display:flex; align-items:center; gap:10px; margin-bottom:15px; flex-wrap:wrap; }
        input[type="text"], select { padding:10px; border-radius:6px; border:1px solid #ccc; font-size:14px; width:150px; }
        button { padding:10px 18px; border-radius:6px; border:none; cursor:pointer; font-weight:bold; font-size:14px; transition:0.3s; }
        button[name="loadTerm"], button[name="loadStudent"], button[name="addSchedule"] { background-color:#4CAF50; color:white; }
        button[name="changeTerm"], button[name="changeStudent"], button[name="selectYearLevel"] { background-color:#f39c12; color:white; }
        button[name="saveSchedules"] { background-color:#3498db; color:white; margin-top:10px; }
        table { width:100%; border-collapse:collapse; margin-top:15px; border-radius:8px; overflow:hidden; box-shadow:0 2px 5px rgba(0,0,0,0.05); }
        th, td { padding:12px; text-align:center; }
        th { background-color:#1f2d3d; color:white; }
        tr:nth-child(even) { background-color:#f9f9f9; }
        tr:hover { background-color:#f1f1f1; }
        .topRow { display:flex; justify-content:space-between; margin-bottom:15px; font-weight:bold; color:#1f2d3d; }
        .message { font-weight:bold; margin-bottom:15px; }
        #searchInput { margin-bottom:15px; padding:8px; width:300px; border-radius:6px; border:1px solid #ccc; }
    </style>
    <script>
        // Simple table search/filter
        function searchTable() {
            const input = document.getElementById('searchInput').value.trim();
            const table = document.getElementById('schedTable');
            const rows = table.getElementsByTagName('tr');
            for (let i=1; i<rows.length; i++) {
                const schedCell = rows[i].getElementsByTagName('td')[1];
                if (!input) {
                    // If input is empty, show all rows
                    rows[i].style.display = '';
                } else if (schedCell.innerText === input) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
    </script>
</head>
<body>

<form method="POST" style="display:inline;">
    <button type="submit" name="resetPage">Back to Student List</button>
</form>

<div class="container">
    <h2>Schedule Assignment</h2>

    <?php if (!empty($message)):
        $color = str_contains(strtolower($message), 'not') || str_contains(strtolower($message), 'no') ? 'red' : 'green'; ?>
        <p class="message" style="color:<?= $color ?>"><?= $message ?></p>
    <?php endif; ?>

    <!-- Academic Term -->
    <form method="POST" class="form-inline">
        <label>Academic Term Code</label>
        <input type="text" name="termCode" value="<?= htmlspecialchars($_SESSION['termCode'] ?? '') ?>" <?= isset($_SESSION['termCode']) ? 'readonly' : '' ?> required>
        <?php if (isset($_SESSION['termCode'])): ?>
            <button type="submit" name="changeTerm">Change</button>
        <?php else: ?>
            <button type="submit" name="loadTerm">Enter</button>
        <?php endif; ?>
    </form>

    <!-- Student -->
    <?php if (isset($_SESSION['acadYearID'])): ?>
        <form method="POST" class="form-inline">
            <label>Student ID</label>
            <input type="text" name="studentID" value="<?= htmlspecialchars($studentID ?? '') ?>" <?= isset($_SESSION['studentID']) ? 'readonly' : '' ?> required>
            <?php if (isset($_SESSION['studentID'])): ?>
                <button type="submit" name="changeStudent">Change</button>
            <?php else: ?>
                <button type="submit" name="loadStudent">Enter</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>

    <!-- Year Level -->
    <?php if ($student && isset($_SESSION['studentID'])):
        $stmt = $conn->prepare("
            SELECT DISTINCT cur.yearlevel
            FROM curriculum cur
            JOIN course_curriculum cc ON cur.curID = cc.curID
            WHERE cc.courseID = ?
            ORDER BY cur.yearlevel ASC
        ");
        $stmt->bind_param("i", $student['courseID']);
        $stmt->execute();
        $yearLevels = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>
        <form method="POST" class="form-inline">
            <label>Year Level</label>
            <select name="yearLevel" required>
                <option value="">-- Select --</option>
                <?php foreach ($yearLevels as $yl): ?>
                    <option value="<?= $yl['yearlevel'] ?>" <?= (isset($_SESSION['yearLevel']) && $_SESSION['yearLevel'] == $yl['yearlevel']) ? 'selected' : '' ?>>
                        <?= $yl['yearlevel'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="selectYearLevel">Select</button>
        </form>
    <?php endif; ?>

    <!-- Student Info & Schedule Table -->
    <?php if ($student && isset($_SESSION['yearLevel'])): ?>
        <div class="topRow">
            <p>Student: <?= htmlspecialchars($student['firstname'].' '.$student['middlename'].' '.$student['lastname']) ?></p>
            <p>Course: <?= htmlspecialchars($student['courseName']) ?></p>
            <p>Year Level: <?= htmlspecialchars($_SESSION['yearLevel']) ?></p>
        </div>

        <h3>Search Schedules</h3>
        <form id="searchForm" onsubmit="searchTable(); return false;" class="form-inline">
            <input type="text" id="searchInput" placeholder="Enter schedID..." style="width:250px;">
            <button type="button" onclick="searchTable()">Search</button>
        </form>

        <form method="POST">
            <input type="hidden" name="studentID" value="<?= htmlspecialchars($studentID) ?>">
            <input type="hidden" name="enrollmentID" value="<?= htmlspecialchars($student['enrollmentID']) ?>">

            <table id="schedTable">
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