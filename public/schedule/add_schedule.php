<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/Schedule.php';

$message = "";
$student = NULL;
$enrollmentID = NULL;
$yearCode = NULL;
$semester = NULL;
$academicYear = "";


$termCode = $_POST['termCode'] ?? null;
$studentID = $_POST['studentID'] ?? null;

if (!isset($_SESSION['scheds'])) {
    $_SESSION['scheds'] = [];
}

if(isset($_POST['loadTerm'])) {
    if (!$termCode || strlen($termCode) < 3) {
        $message = "Invalid term code.";
    }else{
        $yearCode = (int) substr($termCode, 0, 2);
        $semester = (int) substr($termCode, 2, 1);

        $startYear = 2000 + $yearCode-1;
        $endYear = 2000 + $yearCode;

        $academicYear = "$startYear-$endYear";

        $stmt = $conn->prepare("SELECT acadYearID FROM academic_years WHERE academicYear = ?");
        $stmt->bind_param("s", $academicYear);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if($result){
            $_SESSION['termCode'] = $termCode;
            $_SESSION['acadYearID'] = $result['acadYearID'];
            $_SESSION['semester'] = $semester;
        }
        else{
            $message = "Academic term does not exist.";
        }
    }
}

if(isset($_POST['loadStudent'])) {

    $_SESSION['studentID'] = $studentID;

    $_SESSION['scheds'] = [];
}

if ($studentID){
        $stmt = $conn->prepare("SELECT sp.studProgID, e.enrollmentID, s.firstname, s.middlename, s.lastname, c.courseName 
                                FROM student_programs sp
                                JOIN students s ON sp.student_id = s.studentID
                                JOIN student_enrollments e on e.studEnrollID = sp.studProgID 
                                JOIN course c ON sp.courseID = c.courseID
                                WHERE sp.student_id = ? AND sp.status = 'ACTIVE'
                                LIMIT 1");
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();

        if(!$student){
            $message="Student Not Found!";
        }
}

if (isset($_POST['addSchedule'])) {
    $schedID = (int)$_POST['schedID'];

    $stmt = $conn->prepare("SELECT sc.schedID, cur.subdescription as subjectCode, sc.day, sc.start_time as startTime, sc.end_time as endTime, sc.room, sc.section
                            FROM schedule sc
                            JOIN course_curriculum cc ON sc.courCurID = cc.courCurID
                            JOIN curriculum cur on cur.curID = cc.curID
                            WHERE sc.schedID = ?");
    $stmt->bind_param("i", $schedID);
    $stmt->execute();
    $sched = $stmt->get_result()->fetch_assoc();

    if ($sched){
        if (!isset($_SESSION['scheds'][$schedID])) {
            $_SESSION['scheds'][$schedID] = $sched;
            $message = "Schedule Loaded!";
        } else {
            $message = "Schedule already loaded.";
        }
    }
    else {
        $message = "Schedule not found.";
    }
}

if (isset($_POST['saveSchedules'])) {
    $selected = $_POST['selectedScheds'] ?? [];

    if(empty($selected)) {
        $message = "No schedules selected.";
    }
    else{
        $enrollmentID = (int)$_POST['enrollmentID'];

        foreach ($selected as $schedID) {
            $stmt = $conn->prepare("INSERT INTO student_schedule (enrollmentID, schedID)
                                    VALUES (?,?)");
            $stmt->bind_param("ii", $enrollmentID, $schedID);
            $stmt->execute();
        }

        $_SESSION['scheds'] = [];
        $message = "Schedules saved.";
    }
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

            .container{
            width:750px;
            margin:auto;
            background:white;
            padding:20px;
            border-radius:8px;
            }

            h2{
            text-align:center;
            }

            input{
            padding:8px;
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

            .topRow{
            display:flex;
            justify-content:space-between;
            }
        </style>
    </head>
    <body>
        <button onclick="window.location.href='/enrollment_system/public/index.php'">Back to Dashboard</button>
        
        <div class="container">

            <h2>Schedule Assignment</h2>

            <?php if(!empty($message)) {
                $color = (str_contains(strtolower($message), 'not') || str_contains(strtolower($message), 'no')) ? "red" : "green";


                echo "<p style='color:$color;'>$message</p>";} ?>

            <form method="POST">
                <label>Academic Term Code</label>
                <input type="text" name="termCode" value="<?= htmlspecialchars($termCode ?? '') ?>" required>
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

                <form method="POST">
                    <input type="hidden" name="studentID" value="<?= htmlspecialchars($studentID) ?>">
                    <input type="hidden" name="termCode" value="<?= htmlspecialchars($termCode) ?>">
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
                        <?php foreach($_SESSION['scheds'] as $sched): ?>
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