<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../controllers/CourseController.php";

$message = "";
$status = "";

if(isset($_SESSION['message'])){
    $message = $_SESSION['message'];
    $status = $_SESSION['status'];
    unset($_SESSION['message'], $_SESSION['status']);
}

$courseName = "";
$units = "";

if ($_SERVER['REQUEST_METHOD'] === "POST"){
    $courseName = trim($_POST["courseName"]);
    $units = $_POST["units"];

    $result = CourseController::add($conn, $courseName, $units);

    if ($result['status'] === 'success'){
        $_SESSION['message'] = $result['message'];
        $_SESSION['status'] = $result['status'];
        header("Location: /enrollment_system/public/courses/add_courses.php");
        exit();
    }

    $message = $result['message'];
    $status = $result['status'];
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Add Course</title>
        <link rel='stylesheet' href = "/enrollment_system/public/assets/css/add.css">
    </head>
    <body>
        <div class="add-container">
            <h1>Add a course</h1>

            <?php if ($message): ?>
                <p style="color: <?= $status === "error" ? 'red': 'green' ?>"><?=  $message ?></p>
            <?php endif ?>
            <form method="POST">
                <label>Course Name:</label><br>
                <input type="text" name="courseName" required><br>
                <label>Units:</label><br>
                <input type="number" name="units" required><br>

                <div class="buttons">
                    <button type="submit">Add Course</button>
                    <button type="button" onclick="window.location.href='/enrollment_system/public/index.php'">Return to Dashboard</button>
                </div>
            </form>
        </div>
    </body>
</html>

<?php require_once __DIR__ . "/../../includes/footer.php" ?>