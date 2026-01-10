<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../controllers/StudentController.php";

$studentID = $_GET['studentID'];
if (!$studentID){
    die("Student ID is required.");
}

$info = StudentController::studentInfo($conn, $studentID);

$periods = StudentController::getEnrollmentPeriods($conn, $studentID);

$selectedEnrollmentID = $_GET['period'] ?? null;
$result = null;

if ($selectedEnrollmentID && is_numeric($selectedEnrollmentID) && $selectedEnrollmentID > 0){
    $result = StudentController::studentHist($conn, $selectedEnrollmentID);
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Student History</title>
        <link rel="stylesheet" href="/enrollment_system/public/assets/css/style.css">
    </head>
    <body>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 5px;">
        <h1>Student History Page</h1>
        <a href="/enrollment_system/public/index.php">Return to Dashboard</a>
        </div>
        <div class="student-info table-container">
            <table>
                <tr>
                   <th>Student Name</th>
                   <th>Course</th>
                </tr>
                <?php 
                    while ($row = $info->fetch_assoc()):
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']) ?></td>
                    <td><?= htmlspecialchars($row['courseDesc']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>

            <form method="GET">
                <input type="hidden" name="studentID" value="<?= htmlspecialchars($studentID) ?>">

                <label for="period">Select Semester:</label>
                <select style="padding:5px; margin: 5px;" name="period" id="period" onchange="this.form.submit()">
                    <option value="" <?= !$selectedEnrollmentID ? 'selected' : '' ?>>--</option>
                    <?php foreach ($periods as $p):
                        if (!$p['enrollmentID']){
                            continue;
                        }
                        $label = ($p['semester'] == 1 ? 'First Semester' : ($p['semester'] == 2 ? 'Second Semester' : 'Summer')) 
                         . ', ' . $p['academicYear'];
                        $enrollmentID = $p['enrollmentID'];
                    ?>
                    <option value="<?= $enrollmentID ?>" <?= ($selectedEnrollmentID == $enrollmentID) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($selectedEnrollmentID): ?>
            <table>
                <tr>
                    <th>Subject Code</th>
                    <th>Description</th>
                    <th>MidTerm</th>
                    <th>Final</th>
                    <th>Units</th>
                </tr>
                <?php if ($result && $result->num_rows > 0):?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['subjectCode']) ?></td>
                        <td><?= htmlspecialchars($row['subdescription']) ?></td>
                        <td><?= htmlspecialchars($row['midterm']) ?></td>
                        <td><?= htmlspecialchars($row['final']) ?></td>
                        <td><?= htmlspecialchars($row['units']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No records found for the selected period.</td>
                    </tr>
                <?php endif; ?>
            </table>
            <?php endif; ?>
        </div>
    </body>
</html>