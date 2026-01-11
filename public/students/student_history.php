<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../controllers/StudentController.php";

$studentID = $_GET['studentID'] ?? null;
if (!$studentID || !is_numeric($studentID)){
    die("Valid Student ID is required.");
}


$student = StudentController::getStudentInfo($conn, $studentID);


$periods = StudentController::getEnrollmentPeriods($conn, $studentID);

$selectedEnrollmentID = $_GET['period'] ?? null;


$history = [];

$yearlevel = '';
if ($selectedEnrollmentID && is_numeric($selectedEnrollmentID) && $selectedEnrollmentID > 0){
    $history = StudentController::getStudentHistory($conn, $selectedEnrollmentID);

    $selectedPeriod = null;
    foreach ($periods as $p) {
        if ($p['enrollmentID'] == $selectedEnrollmentID) {
            $selectedPeriod = $p;
            break;
        }
    }

    if ($selectedPeriod) {
        $uniqueYears = [];
        foreach ($periods as $p) {
            $year = $p['academicYear'];
            if (!in_array($year, $uniqueYears)) {
                $uniqueYears[] = $year;
            }
        }
        $yearnumber = array_search($selectedPeriod['academicYear'], $uniqueYears) + 1;

        if ($yearnumber == 1) $suffix = 'st';
        elseif ($yearnumber == 2) $suffix = 'nd';
        elseif ($yearnumber == 3) $suffix = 'rd';
        else $suffix = 'th';

        $yearlevel = $yearnumber . $suffix . " Year";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enrollment History</title>
    <link rel="stylesheet" href="/enrollment_system/public/assets/css/style.css">
    <style>
        .student-title {
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 5px;">
        <h1>Enrollment History</h1>
        <a href="/enrollment_system/public/index.php">Return to Dashboard</a>
    </div>
    
    <div class = "student-title">
        <h2><?= htmlspecialchars($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']) ?></h2>
    </div>

    <div class="student-info table-container">
        <table>
            <tr>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Last Name</th>
                <th>Age</th>
                <th>Course</th>
            </tr>
            <?php if ($student): ?>
            <tr>
                <td><?= htmlspecialchars($student['firstname']) ?></td>
                <td><?= htmlspecialchars($student['middlename']) ?></td>
                <td><?= htmlspecialchars($student['lastname']) ?></td>
                <td><?= htmlspecialchars($student['age'] ) ?></td>
                <td><?= htmlspecialchars($student['courseDesc']) ?></td>
            </tr>
            <?php else: ?>
            <tr>
                <td colspan="2">Student not found</td>
            </tr>
            <?php endif; ?>
        </table>

        <form method="GET" action="">
            <input type="hidden" name="studentID" value="<?= htmlspecialchars($studentID) ?>">
            <label for="period">Select Year:</label>
            <select name="period" id="period" style="padding:5px; margin:5px;" onchange="this.form.submit()">
                <option value="" <?= !$selectedEnrollmentID ? 'selected' : '' ?>>--</option>
                <?php foreach ($periods as $p):
                    $label = ($p['semester'] == 1 ? 'First Semester' : ($p['semester'] == 2 ? 'Second Semester' : 'Summer')) 
                             . ', ' . $p['academicYear'];
                ?>
                <option value="<?= $p['enrollmentID'] ?>" <?= ($selectedEnrollmentID == $p['enrollmentID']) ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($selectedEnrollmentID): 
            $status = StudentController::statusVerifier($conn, $selectedEnrollmentID);
        ?>
            <div style="display: flex; justify-content: center; align-items: center; gap: 20px;">
                <h3><?= htmlspecialchars($yearlevel) ?></h3>
                <?php if($status): ?>
                    <span style="padding: 5px 10px; border-radius: 5px; 
                            background-color: <?= $status === 'COMPLETED' ? '#4CAF50' : '#FF9800' ?>;
                            color: white; font-weight: bold;">
                        <?= htmlspecialchars($status) ?>
                    </span>
                <?php endif; ?>
            </div>
        <table>
            <tr>
                <th>Subject Code</th>
                <th>Description</th>
                <th>MidTerm</th>
                <th>Final</th>
                <th>Units</th>
            </tr>
            <?php if (!empty($history)): ?>
                <?php foreach ($history as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['subjectCode']) ?></td>
                    <td><?= htmlspecialchars($row['subdescription']) ?></td>
                    <td><?= htmlspecialchars($row['midterm']) ?></td>
                    <td><?= htmlspecialchars($row['final']) ?></td>
                    <td><?= htmlspecialchars($row['units']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No records found for the selected period.</td>
                </tr>
            <?php endif; ?>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>
