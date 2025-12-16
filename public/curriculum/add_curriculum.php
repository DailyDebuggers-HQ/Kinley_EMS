<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../controllers/CurriculumController.php";

$message = "";
$status = "";

if(isset($_SESSION['message'])){
    $message = $_SESSION['message'];
    $status = $_SESSION['status'];
    unset($_SESSION['message'], $_SESSION['status']);
}

$subjectCode = "";
$semester = "";
$yearlevel = "";
$subdescription = "";
$units = "";

if ($_SERVER['REQUEST_METHOD'] === "POST"){
    $subjectCode = trim($_POST["subjectCode"]);
    $semester = $_POST["semester"];
    $yearlevel = $_POST["yearlevel"];
    $subdescription = $_POST['subdescription'];
    $units = $_POST["units"];

    $result = CurriculumController::add($conn, $subjectCode, $semester, $yearlevel,$subdescription, $units);

    if ($result['status'] === 'success'){
        $_SESSION['message'] = $result['message'];
        $_SESSION['status'] = $result['status'];
        header("Location: /enrollment_system/public/curriculum/add_curriculum.php");
        exit();
    }

    $message = $result['message'];
    $status = $result['status'];
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Add Curriculum</title>
        <link rel='stylesheet' href = "/enrollment_system/public/assets/css/add.css">
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
            <h1>Add a curriculum</h1>

            <?php if ($message): ?>
                <p style="color: <?= $status === "error" ? 'red': 'green' ?>"><?=  $message ?></p>
            <?php endif ?>
            <form method="POST">
                <label>Subject Code:</label><br>
                <input type="text" name="subjectCode" required><br>
                <label>Semester:</label><br>
                <input type="text" name="semester" required><br>
                <label>Year Level</label>
                <input type="text" name="yearlevel" required><br>
                <label>Subject Description:</label><br>
                <input type="text" name="subdescription" required><br>
                <label>Units:</label><br>
                <input type="number" name="units" required><br>

                <div class="buttons">
                    <button type="submit">Add Curriculum</button>
                    <button type="button" onclick="window.location.href='/enrollment_system/public/index.php'">Return to Dashboard</button>
                </div>
            </form>
        </div>
    </body>
</html>
