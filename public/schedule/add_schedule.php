<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/Schedule.php';

$termCode = $_POST['termCode'] ?? null;
$studentID = $_POST['studentID'] ?? null;
$schedID = $_POST['schedID'] ?? null;

$academicYear = null;
$semester = null;
$student = null;

if (!isset($_SESSION['scheds'])) {
    $_SESSION['scheds'] = [];
}

if (isset($_POST['loadTerm'])){
    $yearCode = (int) substr($termCode, 0, 2);
    $semester = (int) substr($termCode, 2, 1);

    $startYear = 2000 + $yearCode - 1;
    $endYear = 2000 + $yearCode;

    $academicYear = "$startYear-$endYear";
}

if(isset($_POST['loadStudent'])){
    $stmt = $conn->prepare("
        SELECT sp.studProgID,
        s.firstname,
        s.middlename,
        s.lastname,
        c.courseName
        FROM student_programs sp
        JOIN students s on sp.student_id = s.studentID
        JOIN course c on sp.courseID = c.courseID
        WHERE s.studentID = ? AND sp.status='ACTIVE' LIMIT 1
    ");

    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}

if(isset($_POST['addSchedule'])){
    $schedID = (int) ($_POST['schedID'] ?? null);

    $stmt = $conn->prepare("
        SELECT sc.schedID, cur.subdescription as subjectCode, sc.day, sc.start_time as startTime, sc.end_time as endTime, sc.room, sc.section
        FROM schedule sc
        JOIN course_curriculum cc ON sc.courCurID = cc.courCurID
        JOIN curriculum cur ON cc.curID = cur.curID
        WHERE sc.schedID = ?");
    $stmt->bind_param("i", $schedID);
    $stmt->execute();
    $sched = $stmt->get_result()->fetch_assoc();

    if ($sched) {
        $_SESSION['scheds'][] = $sched;
        $message = "Schedule added to the list.";
    } else {
        $message = "Schedule ID not found.";
    }
}

if(isset($_POST['saveSchedules'])){
    $enrollmentID = (int) ($_POST['enrollmentID'] ?? null);

    foreach ($_SESSION['scheds'] as $sched) {
        $stmt = $conn->prepare("INSERT INTO student_schedule (enrollmentID, schedID) VALUES (?, ?)");
        $stmt->bind_param("ii", $enrollmentID, $sched['schedID']);
        $stmt->execute();
    }
    $_SESSION['scheds'] = [];
    $message = "Schedules saved for the student.";
}
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Schedule Assignment</title>
        <style>
            body{
            font-family: Arial;
            background:#f4f6f8;
            }
            h2{
            text-align:center;
            }

            .container{
            width:700px;
            margin:auto;
            background:white;
            padding:20px;
            border-radius:8px;
            }

            input{
            padding:8px;
            width:200px;
            margin-bottom:10px;
            }

            table{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
            }

            th,td{
            border:1px solid #ccc;
            padding:8px;
            text-align:center;
            }

            .schedInput{
            width:80px;
            }
        </style>
    </head>
    <body>
        <button type="button" onclick="window.location.href='/enrollment_system/public/index.php'">Back to Dashboard</button>
        <div class="container">

        <h2>Schedule Assignment</h2>

        <?php if(!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

        <form method="POST">
            <label>Academic Term Code</label>
            <input type="text" name="termCode" id="termCode" value="<?= htmlspecialchars($termCode) ?>" required>
            <button name="loadTerm">Enter</button>
        </form>

        <?php if ($termCode): ?>
            <form method = "POST">
                <input type="hidden" name="termCode" value="<?= htmlspecialchars($termCode) ?>">
                <label>Student ID</label>
                <input type="text" name="studentID" value="<?= htmlspecialchars($studentID) ?>" required>
                <button name="loadStudent">Enter</button>
            </form>
        <?php endif; ?>

        <?php if($student): ?>
            <p>Student: <?= htmlspecialchars($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']) ?></p><br>
            <p>Course: <?= htmlspecialchars($student['courseName']) ?></p>

            <h3>Schedules</h3>
            <table>
                <thead>
                    <tr>
                        <th>SchedID</th>
                        <th>Subject</th>
                        <th>Day</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Room</th>
                        <th>Section</th>
                    </tr>
                </thead>
                <?php foreach($_SESSION['scheds'] as $sched): ?>
                    <tr>
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
            <form method="POST">
                <input type="hidden" name="termCode" value="<?= htmlspecialchars($termCode) ?>">
                <input type="hidden" name="studentID" value="<?= htmlspecialchars($studentID) ?>">
                
                <label>Add Sched ID</label><br>
                <input type="text" name="schedID" required>
                <button name="addSchedule">Enter</button>
            </form>

            <form method="POST">
                <input type="hidden" name="studentID" value="<?= htmlspecialchars($studentID) ?>">
                <input type="hidden" name="enrollmentID" value="1">
                <button name="saveSchedules">Save Schedule</button>
            </form>
        <?php endif; ?>
        </div>
    </body>
</html>