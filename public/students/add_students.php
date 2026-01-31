<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/Student.php';
require_once __DIR__ . '/../../controllers/StudentController.php';
require_once __DIR__ . '/../../models/Course.php';


$message="";
$status="";

$programsResult = Course::all($conn);

if(isset($_SESSION['message'])){
    $message = $_SESSION['message'];
    $status = $_SESSION['status'];
    unset($_SESSION['message'], $_SESSION['status']);
}

$lastname="";
$firstname="";
$middlename="";
$birthdate="";
$courseID=null;


if ($_SERVER['REQUEST_METHOD'] === "POST"){
    $lastname = trim($_POST['lastname']);
    $firstname = trim($_POST['firstname']);
    $middlename = $_POST['middlename'];
    $birthdate = $_POST['birthdate'];
    $courseID = intval($_POST['courseID']);

    if (!$courseID) {
        $message = "Please select a valid course.";
        $status = "error";
    } 
    else {

        $result = StudentController::add($conn, $lastname, $firstname, $middlename, $birthdate, $courseID);

        if ($result['status'] === "success"){
            $_SESSION['message']=$result['message'];
            $_SESSION['status'] = $result['status'];
            header("Location: /enrollment_system/public/students/add_students.php");
            exit();
        }

        $message = $result['message'];
        $status = $result['status'];
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Add Student</title>
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

            select {
                width: 100%;
                padding: 5px;
                margin: 10px 0;
            }
        </style>
    </head>

    <body>
        <div class="add-container">
            <h1>Add Student</h1>

            <?php if ($message): ?>
                <p style="color: <?= $status === "error" ? 'red' : 'green' ?>;"><?= $message ?></p>
            <?php endif; ?>

            <form method="POST">
                <label>First Name:</label><br>
                <input type="text" name="firstname" required value="<?= htmlspecialchars($firstname) ?>"><br>
                <label>Last Name:</label><br>
                <input type="text" name="lastname" required value="<?= htmlspecialchars($lastname) ?>"><br>
                <label>Middle Name:</label><br>
                <input type="text" name="middlename" value="<?= htmlspecialchars($middlename) ?>"><br>
                <label>Birthdate:</label>
                <input type="date" name="birthdate" required value="<?= htmlspecialchars($birthdate) ?>"><br>
                <label>Course:</label>
                <select name="courseID" required>
                    <option value="" disabled <?= is_null($courseID) ? 'selected' : '' ?>>Select Course</option>
                    <?php
                    $programsResult->data_seek(0);
                    while ($course = $programsResult->fetch_assoc()):
                        $selected = ($courseID !== null && $courseID === (int)$course['courseID']) ? 'selected' : '';
                    ?>
                        <option value="<?= $course['courseID'] ?>"<?= $selected ?>>
                            <?= htmlspecialchars($course['courseDesc']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <div class="buttons">
                    <button type="submit">Add Student</button>
                    <button type="button" onclick="window.location.href='/enrollment_system/public/index.php'">Back to Dashboard</button>
                </div>
            </form>
        </div>
    </body>
</html>