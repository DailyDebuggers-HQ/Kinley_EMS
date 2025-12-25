<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Curriculum.php";
require_once __DIR__ . "/../../includes/header.php";

$order = (isset($_GET['sort']) && $_GET['sort'] === 'desc') ? 'DESC' : 'ASC';
$toggleOrder = ($order === "ASC") ? 'desc' : 'asc';


$courseFilter = isset($_GET ['courseID']) && $_GET['courseID'] != '' ? intval($_GET ['courseID']) : null;

$sql = "SELECT courseID, courseDesc from course";
$resultsql = $conn->query($sql);

$courses = [];
if ($resultsql->num_rows > 0) {
    while ($row = $resultsql->fetch_assoc()) {
        $courses[] = $row;
    }
}

$result = Curriculum::all($conn, $order, $courseFilter);
?>

<div class="table-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Curriculum List</h3>

        <form method = "get" id = "courseForm">
            <select name = 'courseID' id='courseID' onchange="this.form.submit()">
                <option value=''>--</option>
                <?php
                foreach ($courses as $course) {
                    $selected = ($courseFilter == $course['courseID']) ? 'selected' : '';
                    echo '<option value="'. $course['courseID'] .'" '. $selected .'>'. $course['courseDesc'] .'</option>';
                }
                ?>
            </select>
        </form>
        <a href='/enrollment_system/public/index.php'>Return to Dashboard</a>
    </div>
    <table>
        <tr>
            <th>Subject Code
                <a href="?sort=<?= $toggleOrder ?><?= $courseFilter ? '&courseID=' . $courseFilter : '' ?>" 
                    style="text-decoration: none; font-size: 0.9em;">
                    <?= ($order === 'ASC') ? '▲' : '▼' ?>
                </a>
            </th>
            <th>Subject Description</th>
            <th>Year Level</th>
            <th>Semester</th>
            <th>Units</th>
        </tr>

        <?php 
            while ($row = $result->fetch_assoc()):
        ?>

        <tr>
            <td><?= $row['subjectCode'] ?></td>
            <td><?= $row['subdescription'] ?></td>
            <td><?= $row['yearlevel'] ?></td>
            <td><?= $row['semester'] ?></td>
            <td><?= $row['units'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>