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

$curriculumData = [];
if ($courseFilter){
    $curriculumData = Curriculum::fetchCurByCourse($conn, $courseFilter, $order);
}
?>

<!Doctype html>
<html>
    <head>
        <title>Curriculum List</title>
        <style>
            .year-block {
                margin-bottom: 40px;
                border: solid 3px black;
                padding: 10px;
            }
            .yeartitle {
                display: flex;
                justify-content: center;
                text-align: center;
                align-items: center;
                font-size: larger;
            }

            .semester-row {
                display: flex;
                justify-content: space-between;
                gap: 20px;
            }

            .semester-table {
                width: 48%;
            }
            .semester-table h4 {
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
            }

            .semester-table table {
                width: 100%;
                border-collapse: collapse;
            }

            .semester-table th,
            .semester-table td {
                border: 1px solid #ccc;
                padding: 6px;
                text-align: left;
            }
        </style>
    </head>
    <body>
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

            <?php if ($courseFilter): ?>
                <?php
                    $yearlabels = [
                        1 => 'First Year',
                        2 => 'Second Year',
                        3 => 'Third Year',
                        4 => 'Fourth Year'
                    ];
                ?>
                <?php foreach ($curriculumData as $year => $semesters): ?>

                    <div class="year-block">
                        <div class="yeartitle">
                            <h4>
                                <?= $yearlabels[$year] ?>
                            </h4>
                        </div>
                        <div class="semester-row">
                            <?php if (isset($semesters[1])): ?>
                                <div class="semester-table">
                                    <h4>First Semester</h4>
                                    <table>
                                        <tr>
                                            <th>Subject Code</th>
                                            <th>Subject Description</th>
                                            <th>Units</th>
                                        </tr>
                                        <?php foreach ($semesters[1] as $sub): ?>
                                            <tr>
                                                <td><?= $sub['subjectCode'] ?></td>
                                                <td><?= $sub['subdescription'] ?></td>
                                                <td><?= $sub['units'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($semesters[2])): ?>
                                <div class="semester-table">
                                    <h4>Second Semester</h4>
                                    <table>
                                        <tr>
                                            <th>Subject Code</th>
                                            <th>Subject Description</th>
                                            <th>Units</th>
                                        </tr>
                                        <?php foreach ($semesters[2] as $sub): ?>
                                            <tr>
                                                <td><?= $sub['subjectCode'] ?></td>
                                                <td><?= $sub['subdescription'] ?></td>
                                                <td><?= $sub['units'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($semesters[0])): ?>
                            <div class="semester-table">
                                <h4>Summer</h4>
                                <table>
                                    <tr>
                                        <th>Subject Code</th>
                                        <th>Subject Description</th>
                                        <th>Units</th>
                                    </tr>
                                    <?php foreach ($semesters[0] as $sub): ?>
                                        <tr>
                                            <td><?= $sub['subjectCode'] ?></td>
                                            <td><?= $sub['subdescription'] ?></td>
                                            <td><?= $sub['units'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </body>
</html>