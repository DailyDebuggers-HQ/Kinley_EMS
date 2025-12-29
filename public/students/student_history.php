<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../controllers/StudentController.php";

$studentID = $_GET['studentID'];
$result = StudentController::studentHist($conn, $studentID);
$info = StudentController::studentInfo($conn, $studentID);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Student History</title>
        <link rel="stylesheet" href="/enrollment_system/public/assets/css/style.css">
    </head>
    <body>
        <h1>Student History Page</h1>
        <a href="/enrollment_system/public/index.php">Return to Dashboard</a>
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

            <table>
                <tr>
                    <th>Subjects</th>
                    <th>Description</th>
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
                    <td><?= htmlspecialchars($row['midterm']) ?></td>
                    <td><?= htmlspecialchars($row['final']) ?></td>
                    <td><?= htmlspecialchars($row['units']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </body>
</html>