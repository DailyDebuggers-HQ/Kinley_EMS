<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/header.php";

/* GET ACADEMIC YEARS FOR DROPDOWN */
$years = $conn->query("SELECT DISTINCT y.acadYearID, y.academicYear FROM academic_years y
JOIN schedule s ON y.acadYearID = s.acadYearID
ORDER BY y.acadYearID DESC");

/* SELECTED YEAR */
$selectedYear = $_GET['acadYearID'] ?? null;

/* FETCH SCHEDULES ONLY IF YEAR IS SELECTED */
$schedules = null;

if ($selectedYear) {

    $stmt = $conn->prepare("
        SELECT sc.schedID, c.subjectCode, c.subdescription, section, day, start_time, end_time, room
        FROM schedule sc
        JOIN course_curriculum cc ON sc.courCurID = cc.courCurID
        JOIN curriculum c ON cc.curID = c.curID
        WHERE sc.acadYearID = ?
        ORDER BY c.subjectCode
    ");

    $stmt->bind_param("i", $selectedYear);
    $stmt->execute();

    $schedules = $stmt->get_result();
}
?>

<div class="table-container">

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3>Schedule List</h3>
        <a href="/enrollment_system/public/index.php">Return to Dashboard</a>
    </div>

    <div style="display: flex; justify-content: center; text-align: center;">
        <form method="GET" style="margin:15px 0;">
            
            <label>Select Academic Year:</label>

            <select name="acadYearID" onchange="this.form.submit()">
                <option value="">-- Select Academic Year --</option>

                <?php while($row = $years->fetch_assoc()): ?>

                    <option value="<?= $row['acadYearID'] ?>"
                        <?= ($selectedYear == $row['acadYearID']) ? 'selected' : '' ?>>

                        <?= $row['academicYear'] ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </form>
    </div>

    <!-- SHOW TABLE ONLY IF YEAR SELECTED -->
    <?php if ($selectedYear): ?>

    <table>
        <tr>
            <th>No.</th>
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Section</th>
            <th>Day</th>
            <th>Time</th>
            <th>Room</th>
        </tr>

        <?php
        $no = 1;
        while($row = $schedules->fetch_assoc()):
        ?>

        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['subjectCode'] ?></td>
            <td><?= $row['subdescription'] ?></td>
            <td><?= $row['section'] ?></td>
            <td><?= $row['day'] ?></td>
            <td><?= $row['start_time'] ?> - <?= $row['end_time'] ?></td>
            <td><?= $row['room'] ?></td>
        </tr>

        <?php endwhile; ?>

    </table>

    <?php else: ?>
        <p style="text-align: center; margin-top: 20px;">Please select an academic year to view schedules.</p>

    <?php endif; ?>

</div>