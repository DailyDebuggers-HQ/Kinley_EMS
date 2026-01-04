<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../controllers/StudentController.php";

$studentID = $_GET['studentID'];
$yearlevel = $_GET['yearlevel'] ?? null;
$result = StudentController::studentHist($conn, $studentID, $yearlevel);
$info = StudentController::studentInfo($conn, $studentID);

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

                <label for="yearlevel">Select Year Level:</label>
                <select style="padding:5px; margin: 5px;" name="yearlevel" id="yearlevel" onchange="this.form.submit()">
                    <option value="">All Years</option>
                    <option value="1" <?= ($_GET['yearlevel'] ?? '') == 1 ? 'selected' : '' ?>>Year 1</option>
                    <option value="2" <?= ($_GET['yearlevel'] ?? '') == 2 ? 'selected' : '' ?>>Year 2</option>
                    <option value="3" <?= ($_GET['yearlevel'] ?? '') == 3 ? 'selected' : '' ?>>Year 3</option>
                    <option value="4" <?= ($_GET['yearlevel'] ?? '') == 4 ? 'selected' : '' ?>>Year 4</option>
                </select>
            </form>


            <table>
                <tr>
                    <th>Subjects</th>
                    <th>Description</th>
                    <th>Semester</th>
                    <th>MidTerm</th>
                    <th>Final</th>
                    <th>Units</th>
                </tr>
                <?php 
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['subjectCode']) ?></td>
                    <td><?= htmlspecialchars($row['subdescription']) ?></td>
                    <td><?= htmlspecialchars($row['semester']) ?></td>
                    <td><?= htmlspecialchars($row['midterm']) ?></td>
                    <td><?= htmlspecialchars($row['final']) ?></td>
                    <td><?= htmlspecialchars($row['units']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </body>
</html>