<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/header.php";

// Fetch only academic years that have schedules
$years = $conn->query("
    SELECT DISTINCT y.acadYearID, y.academicYear
    FROM academic_years y
    JOIN schedule s ON y.acadYearID = s.acadYearID
    ORDER BY y.academicYear DESC
");

// Get selected academic year
$selectedYear = $_GET['acadYearID'] ?? null;

// Fetch schedules only if a year is selected
$schedules = null;
if ($selectedYear) {

    $stmt = $conn->prepare("
        SELECT DISTINCT
            sc.schedID, 
            cur.subjectCode, 
            cur.subdescription, 
            sc.section, 
            sc.day, 
            sc.start_time, 
            sc.end_time, 
            sc.room
        FROM schedule sc
        LEFT JOIN course_curriculum cc ON sc.courCurID = cc.courCurID
        LEFT JOIN curriculum cur ON cc.curID = cur.curID
        WHERE sc.acadYearID = ?
        ORDER BY cur.subjectCode
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

    <div style="display:flex; justify-content:center; text-align:center;">
        <form method="GET" style="margin:15px 0;">
            <label>Select Academic Year:</label>
            <select name="acadYearID" onchange="this.form.submit()">
                <option value="">-- Select Academic Year --</option>
                <?php while ($row = $years->fetch_assoc()): ?>
                    <option value="<?= $row['acadYearID'] ?>" <?= ($selectedYear == $row['acadYearID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['academicYear']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>

    <?php if ($selectedYear && $schedules && $schedules->num_rows > 0): ?>
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

            <?php $no = 1; ?>
            <?php while ($row = $schedules->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['subjectCode'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['subdescription'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['section']) ?></td>
                    <td><?= htmlspecialchars($row['day']) ?></td>
                    <td><?= htmlspecialchars($row['start_time'] . ' - ' . $row['end_time']) ?></td>
                    <td><?= htmlspecialchars($row['room']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php elseif ($selectedYear): ?>
        <p style="text-align:center; margin-top:20px;">No schedules found for this academic year.</p>
    <?php else: ?>
        <p style="text-align:center; margin-top:20px;">Please select an academic year to view schedules.</p>
    <?php endif; ?>

</div>