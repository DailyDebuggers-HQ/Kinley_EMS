<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/Student.php';
require_once __DIR__ . '/../../controllers/StudentController.php';


$message="";
$status="";

if(isset($_SESSION['message'])){
    $message = $_SESSION['message'];
    $status = $_SESSION['status'];
    unset($_SESSION['message'], $_SESSION['status']);
}

$lastname="";
$firstname="";
$middlename="";
$age="";


if ($_SERVER['REQUEST_METHOD'] === "POST"){
    $lastname = trim($_POST['lastname']);
    $firstname = trim($_POST['firstname']);
    $middlename = $_POST['middlename'];
    $age = $_POST['age'];

    $result = StudentController::add($conn, $lastname, $firstname, $middlename, $age);

    if ($result['status'] === "success"){
        $_SESSION['message']=$result['message'];
        $_SESSION['status'] = $result['status'];
        header("Location: /enrollment_system/public/students/add_students.php");
        exit();
    }

    $message = $result['message'];
    $status = $result['status'];
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
                <input type="text" name="firstname" required><br>
                <label>Last Name:</label><br>
                <input type="text" name="lastname" required><br>
                <label>Middle Name:</label><br>
                <input type="text" name="middlename"><br>
                <label>Age:</label>
                <input type="number" name="age" min = "10" max = "200" required><br>

                <div class="buttons">
                    <button type="submit">Add Student</button>
                    <button type="button" onclick="window.location.href='/enrollment_system/public/index.php'">Back to Dashboard</button>
                </div>
            </form>
        </div>
    </body>
</html>