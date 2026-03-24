<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../controllers/StudentController.php";
require_once __DIR__ . "/../../models/Student.php";

$studentID = $_GET['studentID'] ?? null;
if (!$studentID || !is_numeric($studentID)){
    die("Valid Student ID is required.");
}

$message = '';

// Handle Payment Submission
if (isset($_POST['submitPayment'])) {
    $enrollmentID = (int)($_POST['enrollmentID'] ?? 0);
    $amountPaid   = (float)($_POST['amountPaid'] ?? 0);

    if ($enrollmentID > 0 && $amountPaid > 0) {
        $assessment = StudentController::getStudentAssessment($conn, $enrollmentID);
        $currentBalance = $assessment['balance'] ?? 0;

        if ($amountPaid > $currentBalance) {
            $message = "Payment amount exceeds current balance.";
        } else{
            $stmt = $conn->prepare("INSERT INTO payments (enrollmentID, amountPaid, paymentDate) VALUES (?, ?, CURRENT_DATE)");
            $stmt->bind_param("id", $enrollmentID, $amountPaid);
            $stmt->execute();

            header("Location: ".$_SERVER['REQUEST_URI']);
            exit;
        }
    } else{
        $message = "Please enter a valid payment amount.";
    }
}

$student = StudentController::getStudentInfo($conn, $studentID);
$periods = StudentController::getEnrollmentPeriods($conn, $studentID);

$selectedEnrollmentID = $_GET['period'] ?? null;
$action = $_GET['action'] ?? null;

$history = [];
$schedule = [];
$assessment = ['total' => 0, 'paid' => 0, 'balance' => 0, 'payments' => []];
$totalUnits = 0;
$totalAmount = 0;

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
            if (!in_array($year, $uniqueYears)) $uniqueYears[] = $year;
        }
        $yearnumber = array_search($selectedPeriod['academicYear'], $uniqueYears) + 1;
        $suffix = ($yearnumber == 1 ? 'st' : ($yearnumber == 2 ? 'nd' : ($yearnumber == 3 ? 'rd' : 'th')));
        $yearlevel = $yearnumber . $suffix . " Year";
    }

    if ($action === 'grades') {
        $history = StudentController::getStudentHistory($conn, $selectedEnrollmentID);
        if (!empty($history)) {
            foreach ($history as $row) $totalUnits += $row['units'];
        }
    } elseif ($action === 'schedule') {
        $schedule = Student::getStudentSched($conn, $selectedEnrollmentID);
        if (!empty($schedule)) {
            foreach ($schedule as $row) {
                $totalUnits += $row['units'];
                $totalAmount += $row['price'];
            }
            $assessment = StudentController::getStudentAssessment($conn, $selectedEnrollmentID);
        }
    }
}

if (isset($_POST['submitGrades']) && !empty($_POST['grades'])) {
    $successCount = 0;

    $stmt = $conn->prepare("UPDATE grades SET midterm = ?, final = ? WHERE gradeID = ?");

    foreach ($_POST['grades'] as $gradeID => $g) {
        $midterm = $g['midterm'] ?? '';
        $final   = $g['final'] ?? '';

        // Convert empty strings to NULL
        $midtermVal = $midterm === '' ? null : $midterm;
        $finalVal   = $final === '' ? null : $final;

        // Bind as string for midterm/final, integer for gradeID
        $stmt->bind_param("ssi", $midtermVal, $finalVal, $gradeID);

        $stmt->execute();

        if ($stmt->affected_rows > 0) $successCount++;
    }

    $stmt->close();
    $message = $successCount > 0 ? "Updated $successCount grade(s) successfully!" : "No grades were updated.";

    // Refresh history after update
    $history = StudentController::getStudentHistory($conn, $selectedEnrollmentID);

    // --- AUTO-COMPLETE STATUS CHECK ---
    $allFilled = true;
    foreach ($history as $row) {
        if ($row['midterm'] === null || $row['midterm'] === '' || 
            $row['final'] === null || $row['final'] === '') {
            $allFilled = false;
            break;
        }
    }

    if ($allFilled) {
        $stmt = $conn->prepare("UPDATE student_enrollments SET status = 'COMPLETED' WHERE enrollmentID = ?");
        $stmt->bind_param("i", $selectedEnrollmentID);
        $stmt->execute();
        $stmt->close();
        $message .= " Enrollment marked as COMPLETED!";
    } else {
        // If not all grades are filled, we can optionally set status back to ENROLLED
        $stmt = $conn->prepare("UPDATE student_enrollments SET status = 'ONGOING' WHERE enrollmentID = ? AND status = 'COMPLETED'");
        $stmt->bind_param("i", $selectedEnrollmentID);
        $stmt->execute();
        $stmt->close();
        $message .= " Enrollment status updated to ONGOING if it was previously COMPLETED.";}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enrollment History</title>
    <link rel="stylesheet" href="/enrollment_system/public/assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; padding:0; }
        h1, h2, h3 { margin:0; }
        a { text-decoration:none; color:#1f2d3d; }
        a:hover { text-decoration:underline; }
        .student-title { display:flex; justify-content:center; align-items:center; margin:20px 0; }
        table { border-collapse:collapse; width:100%; background:white; margin-bottom:20px; }
        th, td { border:1px solid #ddd; padding:8px; text-align:center; }
        th { background:#1f2d3d; color:white; }
        tr:nth-child(even) { background:#f4f4f4; }
        button { padding:5px 10px; margin:5px; cursor:pointer; border:none; border-radius:4px; background:#1f2d3d; color:white; }
        button:hover { background:#4CAF50; }

        /* Modal */
        #payModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; }
        #payForm { background:white; padding:20px; border-radius:10px; width:300px; text-align:center; }
        #payForm input { width:90%; padding:8px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
        #payMessage { color:red; min-height:20px; margin-bottom:10px; }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px;">
        <h1>Enrollment History</h1>
        <a href="/enrollment_system/public/students/index.php">Return to Student List</a>
    </div>

    <div class="student-title">
        <h2><?= htmlspecialchars($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']) ?></h2>
    </div>

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
            <td><?= htmlspecialchars($student['birthdate']) ?></td>
            <td><?= htmlspecialchars($student['courseDesc']) ?></td>
        </tr>
        <?php else: ?>
        <tr><td colspan="5">Student not found</td></tr>
        <?php endif; ?>
    </table>

    <form method="GET">
        <input type="hidden" name="studentID" value="<?= htmlspecialchars($studentID) ?>">
        <input type="hidden" name="action" value="">
        <label for="period">Select Year:</label>
        <select name="period" id="period" style="padding:5px; margin:5px;">
            <option value="" <?= !$selectedEnrollmentID ? 'selected' : '' ?>>--</option>
            <?php foreach ($periods as $p): 
                $label = ($p['semester']==1?'First Semester':($p['semester']==2?'Second Semester':'Summer')).', '.$p['academicYear'];
            ?>
            <option value="<?= $p['enrollmentID'] ?>" <?= $selectedEnrollmentID==$p['enrollmentID']?'selected':'' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" onclick="this.form.action.value='grades'">Grade Inquiry</button>
        <button type="submit" onclick="this.form.action.value='schedule'">Account/Schedule</button>
    </form>

    <?php if ($selectedEnrollmentID):
        $status = StudentController::statusVerifier($conn, $selectedEnrollmentID);
    ?>
    <div style="display:flex; justify-content:center; align-items:center; gap:20px; margin:10px 0;">
        <h3><?= htmlspecialchars($yearlevel) ?></h3>
        <?php if($status): ?>
            <span style="padding:5px 10px; border-radius:5px; color:white; font-weight:bold; background:<?= $status==='COMPLETED'?'#4CAF50':'#FF9800' ?>"><?= htmlspecialchars($status) ?></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($history)): ?>
        <form method="POST">
            <table>
                <?php if (!empty($_GET['msg'])): ?>
                    <div style="color:green; text-align:center; margin:10px 0;">
                        <?= htmlspecialchars($_GET['msg']) ?>
                    </div>
                <?php endif; ?>
                <tr>
                    <th>Subject Code</th>
                    <th>Description</th>
                    <th>MidTerm</th>
                    <th>Final</th>
                    <th>Units</th>
                </tr>
                <?php 
                $gradeOptions = ['NC','5.0','3.75','3.50','3.25','3.0','2.75','2.5','2.25','2.0','1.75','1.5','1.25','1.0'];
                foreach ($history as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['subjectCode']) ?></td>
                    <td><?= htmlspecialchars($row['subdescription']) ?></td>
                    <td>
                        <select name="grades[<?= $row['gradeID'] ?>][midterm]">
                            <option value="">--</option>
                            <?php foreach ($gradeOptions as $g): ?>
                            <option value="<?= $g ?>" <?= ($row['midterm']==$g?'selected':'') ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="grades[<?= $row['gradeID'] ?>][final]">
                            <option value="">--</option>
                            <?php foreach ($gradeOptions as $g): ?>
                            <option value="<?= $g ?>" <?= ($row['final']==$g?'selected':'') ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><?= htmlspecialchars($row['units']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="5" style="text-align:center;">
                        <button type="submit" name="submitGrades">Save Grades</button>
                    </td>
                </tr>
            </table>
        </form>
    <?php endif; ?>

    <?php if (!empty($schedule)): ?>
        <table>
            <tr>
                <th>Subject Code</th><th>Description</th><th>Day</th><th>Start Time</th><th>End Time</th>
                <th>Amount</th><th>Room</th><th>Section</th><th>Units</th>
            </tr>
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
        </table>

        <h2 style="text-align:center;">ASSESSMENT</h2>
        <table>
            <tr><th>Date</th><th>Total Amount</th><th>Amount Paid</th><th>Balance</th><th>Action</th></tr>
            <?php $runningBalance = $assessment['total']; ?>
            <tr>
                <td><?= htmlspecialchars($assessment['assessedDate']) ?></td>
                <td><?= number_format($runningBalance,2) ?></td>
                <td><?= number_format(0,2) ?></td>
                <td><?= number_format($runningBalance,2) ?></td>
                <td><?php if($runningBalance>0): ?><button type="button" onclick="showPayForm(<?= $selectedEnrollmentID ?>, <?= $runningBalance ?>)">Pay</button><?php endif; ?></td>
            </tr>
            <?php foreach ($assessment['payments'] as $payment): 
                $currentTotal = $runningBalance;
                $actualPaid = min($payment['amountPaid'], $runningBalance);
                $runningBalance -= $payment['amountPaid'];
            ?>
            <tr>
                <td><?= htmlspecialchars($payment['paymentDate']) ?></td>
                <td><?= number_format($currentTotal,2) ?></td>
                <td><?= number_format($actualPaid,2) ?></td>
                <td><?= number_format(max($runningBalance,0),2) ?></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Payment Modal -->
    <div id="payModal">
        <form method="POST" id="payForm">
            <h3>Make a Payment</h3>
            <div id="payMessage"><?= htmlspecialchars($message) ?></div>
            <input type="hidden" name="enrollmentID" id="payEnrollmentID">
            <input type="number" name="amountPaid" id="payAmount" step="0.01" min="0" required>
            <br><br>
            <button type="submit" name="submitPayment">Submit</button>
            <button type="button" onclick="closePayForm()">Cancel</button>
        </form>
    </div>

    <script>
        // Attach listener once
        document.getElementById('payForm').addEventListener('submit', function(e){
            let max = parseFloat(document.getElementById('payAmount').max);
            let val = parseFloat(document.getElementById('payAmount').value);
            if(val > max){
                e.preventDefault();
                document.getElementById('payMessage').textContent = "Payment cannot exceed current balance.";
            }
        });

        function showPayForm(enrollmentID, balance){
            document.getElementById('payModal').style.display='flex';
            document.getElementById('payEnrollmentID').value = enrollmentID;
            document.getElementById('payAmount').max = balance;
            document.getElementById('payAmount').value = balance;
            document.getElementById('payMessage').textContent = '';
        }

        function closePayForm(){
            document.getElementById('payModal').style.display='none';
        }
    </script>
</body>
</html>