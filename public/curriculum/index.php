<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Curriculum.php";
require_once __DIR__ . "/../../includes/header.php";

$order = (isset($_GET['sort']) && $_GET['sort'] === 'desc') ? 'DESC' : 'ASC';

// Fetch courses
$courseFilter = isset($_GET['courseID']) && $_GET['courseID'] != '' ? intval($_GET['courseID']) : null;
$coursesResult = $conn->query("SELECT courseID, courseDesc FROM course");
$courses = [];
while ($row = $coursesResult->fetch_assoc()) {
    $courses[] = $row;
}

// Determine selected course description
$selectedCourseDesc = '';
if ($courseFilter) {
    foreach ($courses as $course) {
        if ($course['courseID'] == $courseFilter) {
            $selectedCourseDesc = $course['courseDesc'];
            break;
        }
    }
}

// Fetch academic years (revisions) for selected course
$acadYears = [];
$latestAcadYearID = null;
if ($courseFilter) {
    $stmt = $conn->prepare("
        SELECT DISTINCT ay.acadYearID, ay.academicYear
        FROM course_curriculum cc
        JOIN academic_years ay ON cc.acadYearID = ay.acadYearID
        WHERE cc.courseID = ?
        ORDER BY ay.academicYear DESC
    ");
    $stmt->bind_param("i", $courseFilter);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $acadYears[] = $row;
        if ($latestAcadYearID === null) {
            $latestAcadYearID = $row['acadYearID']; // first row = latest year
        }
    }
}

// Selected revision / academic year
$acadYearFilter = isset($_GET['acadYearID']) && $_GET['acadYearID'] != '' 
    ? intval($_GET['acadYearID']) 
    : $latestAcadYearID; // default to latest if not manually selected

// Fetch curriculum only if course is selected
$curriculumData = [];
$curriculumRevision = '';
if ($courseFilter && $acadYearFilter) {
    // Get latest revision number for that course + academic year
    $stmt = $conn->prepare("
        SELECT MAX(revision) AS maxRev
        FROM course_curriculum
        WHERE courseID = ? AND acadYearID = ?
    ");
    $stmt->bind_param("ii", $courseFilter, $acadYearFilter);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $curriculumRevision = $row['maxRev'] ?? '';

    // Fetch curriculum data from model
    $curriculumData = Curriculum::fetchCurByCourse($conn, $courseFilter, $acadYearFilter, $order);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Curriculum List</title>
    <style>
        .year-block { margin-bottom: 40px; border: 3px solid black; padding: 10px; }
        .yeartitle { display: flex; justify-content: center; align-items: center; font-size: larger; }
        .semester-row { display: flex; justify-content: space-between; gap: 20px; }
        .semester-table { width: 48%; }
        .semester-table h4 { text-align: center; }
        .semester-table table { width: 100%; border-collapse: collapse; }
        .semester-table th, .semester-table td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .filter-form { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        .revision { font-weight: bold; margin-top: 10px; text-align: center; }
    </style>
</head>
<body>
<div class="table-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Curriculum List</h3>
        <form method="get" class="filter-form">
            <!-- Course dropdown -->
            <select name="courseID" onchange="this.form.submit()">
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['courseID'] ?>" <?= ($courseFilter == $course['courseID']) ? 'selected' : '' ?>>
                        <?= $course['courseDesc'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Revision dropdown, only shown after selecting course -->
            <?php if (!empty($acadYears)): ?>
                <select name="acadYearID" onchange="this.form.submit()">
                    <?php foreach ($acadYears as $year): ?>
                        <option value="<?= $year['acadYearID'] ?>" <?= ($acadYearFilter == $year['acadYearID']) ? 'selected' : '' ?>>
                            <?= $year['academicYear'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </form>
        <a href='/enrollment_system/public/index.php'>Return to Dashboard</a>
    </div>

    <?php if ($courseFilter && $selectedCourseDesc && $acadYearFilter): ?>
        <div style="margin-top: 20px; text-align: center;">
            <h2><?= $selectedCourseDesc ?></h2>
        </div>

        <?php
            $yearlabels = [1=>'First Year',2=>'Second Year',3=>'Third Year',4=>'Fourth Year'];
        ?>

        <?php foreach ($curriculumData as $year => $semesters): ?>
            <div class="year-block">
                <div class="yeartitle"><h4><?= $yearlabels[$year] ?? "Year $year" ?></h4></div>
                <div class="semester-row">
                    <?php if (isset($semesters[1])): ?>
                        <div class="semester-table">
                            <h4>First Semester</h4>
                            <table>
                                <tr><th>Subject Code</th><th>Subject Description</th><th>Units</th></tr>
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
                                <tr><th>Subject Code</th><th>Subject Description</th><th>Units</th></tr>
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
                            <tr><th>Subject Code</th><th>Subject Description</th><th>Units</th></tr>
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