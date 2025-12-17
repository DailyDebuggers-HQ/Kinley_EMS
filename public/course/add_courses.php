<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/Course.php';
require_once __DIR__ . '/../../controllers/CourseController.php';


$message="";
$status="";

if(isset($_SESSION['message'])){
    $message = $_SESSION['message'];
    $status = $_SESSION['status'];
    unset($_SESSION['message'], $_SESSION['status']);
}

$courseName="";
$courseDesc="";

if ($_SERVER['REQUEST_METHOD'] === "POST"){
    $courseName = $_POST['courseName'];
    $courseDesc = $_POST['courseDesc'];

    $result = CourseController::add($conn, $courseName, $courseDesc);

    if ($result['status'] === "success"){
        $_SESSION['message']=$result['message'];
        $_SESSION['status'] = $result['status'];
        header("Location: /enrollment_system/public/course/add_courses.php");
        exit();
    }

    $message = $result['message'];
    $status = $result['status'];
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Add Courses</title>
        <link rel="stylesheet" href="/enrollment_system/public/assets/css/add.css">
        <style>
            .buttons {
                display: flex;
                justify-content: center;
                align-items: center; 
                gap:10px;
            }

            .buttons button {
                font-size: large;
                cursor: pointer;
            }
        </style>
    </head>

    <body>
        <div class="add-container">
            <h1>Add Course</h1>

            <?php if ($message): ?>
                <p style="color: <?= $status === "error" ? 'red' : 'green' ?>;"><?= $message ?></p>
            <?php endif; ?>

            <form method="POST">
                <label>Course Name:</label><br>
                <input type="text" name="courseName" required><br>
                <label>Course Description:</label><br>
                <input type="text" name="courseDesc" required><br>

                <div class="buttons">
                    <button type="submit">Add Course</button>
                    <button type="button" onclick="window.location.href='/enrollment_system/public/index.php'">Back to Dashboard</button>
                </div>
            </form>
        </div>
    </body>
</html>