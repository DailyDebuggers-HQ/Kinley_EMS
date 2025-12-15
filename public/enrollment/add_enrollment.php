<?php
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../../includes/auth.php";
    require_once __DIR__ . "/../../controllers/EnrollmentController.php";


    $studentsResult = $conn->query("SELECT studentID, firstname, lastname from student order by lastname");
    $students = $studentsResult ? $studentsResult->fetch_all(MYSQLI_ASSOC) : [];
    $coursesResult = $conn->query("SELECT courseCode, courseName from course order by courseName");
    $courses = $coursesResult ? $coursesResult->fetch_all(MYSQLI_ASSOC) :[];

    $gradesOptions = ['1.0','1.25','1.5','1.75','2.0','2.25','2.5','2.75','3.0','4.0','5.0','NC','W'];

    $error = $_GET['error'] ?? '';
    $success = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Manage Enrollment</title>
        <link rel="stylesheet" href="/enrollment_system/public/assets/css/manage.css">
    </head>
    <body>
        <div class='form-container'>
            <h1>Enroll student in available Courses</h1>

            <?php if ($error):?>
                <div class="message error">
                    <?php
                        if ($error === 'already_enrolled') echo "This student is already in this course!";
                        elseif ($error === "missing_fields") echo "Please fill up all fields";
                        else echo "Failed to enroll student in course.";
                    ?>
                </div>

            <?php elseif ($success === 'enrolled'): ?>
                <div class="message success">
                    <?php echo "Student enrolled successfully." ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="action" value="enroll">

                <label for="studentID">Select Student</label>
                <select name="studentID" id="studentID" required>
                    <option value="">Select Student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= htmlspecialchars($student['studentID']) ?>">
                            <?= htmlspecialchars($student['lastname'] . ', ' . $student['firstname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="courseCode">Select Course</label>
                <select name="courseCode" id = "courseCode" required>
                    <option value = "">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['courseCode']) ?>">
                            <?= htmlspecialchars($course['courseName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="grades">Grade</label>
                <select id = "grades" name="grades" required>
                    <option value="">Grade</option>
                    <?php foreach ($gradesOptions as $grade): ?>
                        <option value="<?= $grade ?>"><?= $grade ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Enroll student</button>
            </form>

            <button type="button" onclick="window.location.href='/enrollment_system/public/index.php'">Return to Dashboard</button>
        </div>
    </body>
</html>