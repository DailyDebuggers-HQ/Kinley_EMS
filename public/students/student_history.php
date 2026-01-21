<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../controllers/StudentController.php";
require_once __DIR__ . "/../../models/Student.php";

$studentID = $_GET['studentID'] ?? null;
if (!$studentID || !is_numeric($studentID)){
    die("Valid Student ID is required.");
}


$student = StudentController::getStudentInfo($conn, $studentID);
$periods = StudentController::getEnrollmentPeriods($conn, $studentID);

$selectedEnrollmentID = $_GET['period'] ?? null;
$action = $_GET['action'] ?? null;


$history = [];
$schedule = [];
$assessment = ['total' => 0, 'paid' => 0, 'balance' => 0, 'payments' => []];


$yearlevel = '';
if ($selectedEnrollmentID && is_numeric($selectedEnrollmentID) && $selectedEnrollmentID > 0){
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

    if ($action === 'grades') {
        $history = StudentController::getStudentHistory($conn, $selectedEnrollmentID);

        if (!empty($history)) {
            $totalUnits = 0;
            foreach ($history as $row) {
                $totalUnits += $row['units'];
            }
        } else {
            $totalUnits = 0;
        }
    } else if ($action === 'schedule') {
        $schedule = Student::getStudentSched($conn, $selectedEnrollmentID);
        if (!empty($schedule)) {
            $totalUnits = 0;
            $totalAmount = 0;
            foreach ($schedule as $row) {
                $totalUnits += $row['units'];
                $totalAmount += $row['price'];
            }

            $assessment = StudentController::getStudentAssessment($conn, $selectedEnrollmentID);

        }
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

            button {
                padding: 5px 10px;
                margin: 5px;
                cursor: pointer;
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
                    <th>Birth Date</th>
                    <th>Course</th>
                </tr>
                <?php if ($student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['firstname']) ?></td>
                    <td><?= htmlspecialchars($student['middlename']) ?></td>
                    <td><?= htmlspecialchars($student['lastname']) ?></td>
                    <td><?= htmlspecialchars($student['birthdate'] ) ?></td>
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
                <input type="hidden" name="action" value="">

                <label for="period">Select Year:</label>
                <select name="period" id="period" style="padding:5px; margin:5px;">
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

                <button type="submit" onclick = "this.form.action.value='grades'">Grade Inquiry</button>
                <button type="submit" onclick = "this.form.action.value='schedule'">Account/Schedule</button>
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

                <?php if (!empty($history)): ?>
                    <table>
                        <tr>
                            <th>Subject Code</th>
                            <th>Description</th>
                            <th>MidTerm</th>
                            <th>Final</th>
                            <th>Units</th>
                        </tr>
                        <?php foreach ($history as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['subjectCode']) ?></td>
                            <td><?= htmlspecialchars($row['subdescription']) ?></td>
                            <td><?= htmlspecialchars($row['midterm']) ?></td>
                            <td><?= htmlspecialchars($row['final']) ?></td>
                            <td><?= htmlspecialchars($row['units']) ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td colspan="4" style="text-align:right; font-weight:bold;">Total Units:</td>
                            <td style="font-weight:bold;"><?= $totalUnits ?></td>
                        </tr>
                    </table>
                <?php endif; ?>

                <?php if (!empty($schedule)): ?>
                    <table>
                        <tr>
                            <th>Subject Code</th>
                            <th>Description</th>
                            <th>Day</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Amount</th>
                            <th>Room</th>
                            <th>Section</th>
                            <th>Units</th>
                        </tr>
                        <?php if (!empty($schedule)): ?>
                            <?php foreach ($schedule as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['subjectCode']) ?></td>
                                <td><?= htmlspecialchars($row['subdescription']) ?></td>
                                <td><?= htmlspecialchars($row['days']) ?></td>
                                <td><?= htmlspecialchars($row['start_time']) ?></td>
                                <td><?= htmlspecialchars($row['end_time']) ?></td>
                                <td><?= htmlspecialchars($row['price']) ?></td>
                                <td><?= htmlspecialchars($row['room']) ?></td>
                                <td><?= htmlspecialchars($row['section']) ?></td>
                                <td><?= htmlspecialchars($row['units']) ?></td>
                            </tr>
                            <?php endforeach; ?>

                            <tr>
                                <td colspan="5" style="text-align:right; font-weight:bold;">Total Amount:</td>
                                <td style="font-weight:bold;"><?= $totalAmount ?></td>
                                <td colspan="2"></td>
                                <td style="font-weight:bold;">Total Units: <?= $totalUnits ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>

                    <div style="display: flex; text-align: center; justify-content: center; padding-bottom: 20px;"><h2>ASSESSMENT</h2></div>

                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Amount Paid</th>
                            <th>Balance</th>
                        </tr>
                        <?php
                        $runningBalance = $assessment['total']; 
                        foreach ($assessment['payments'] as $payment):
                            $currentTotal = $runningBalance;
                            $actualPaid = $payment['amountPaid'];
                            if ($actualPaid > $runningBalance) {
                                $actualPaid = $runningBalance; 
                            }

                            $runningBalance -= $payment['amountPaid'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['paymentDate']) ?></td>
                            <td><?= number_format($currentTotal, 2) ?></td>
                            <td><?= number_format($actualPaid, 2) ?></td>
                            <td><?= number_format(max($runningBalance, 0), 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </body>
                        <!--Now, we just have to continue populating the followings:
                        1. schedule of curriculum
                        2. student_schedule
                        3. subject fees
                        4. student assessments
                        5. payments -->
</html>
