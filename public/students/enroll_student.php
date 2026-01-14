<?php 
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../../includes/auth.php";
    require_once __DIR__ . "/../../controllers/EnrollmentController.php";

    $students = $conn->query("SELECT sp.studProgID, s.firstname, s.lastname 
    FROM student_programs sp 
    JOIN students s 
    ON sp.student_id = s.studentID");
    $curriculum = $conn->query("SELECT curID, subdescription FROM curriculum");

    $studentID="";
    $curID="";

    $message="";
    $status="";

    if(isset($_SESSION['message'])){
        $message = $_SESSION['message'];
        $status = $_SESSION['status'];
        unset($_SESSION['message'], $_SESSION['status']);
    }


    if ($_SERVER['REQUEST_METHOD'] === "POST"){
        $studentID = trim($_POST['studentID']);
        $curID = trim($_POST['curID']);

        $result = EnrollmentController::enroll($conn, $studentID, $curID);

        if ($result['status'] === "success"){
            $_SESSION['message']=$result['message'];
            $_SESSION['status'] = $result['status'];
            header("Location: /enrollment_system/public/students/enroll_student.php");
            exit();
    }

        $message = $result['message'];
        $status = $result['status'];
    }
?>

<div style="display: flex; justify-content: space-between; align-items: center; padding: 5px;">
    <h1>Enroll Student</h1>
    <a href="/enrollment_system/public/index.php">Return to Dashboard</a>
</div>

<div>
    <?php if ($message): ?>
        <p style="color: <?= $status === "error" ? 'red' : 'green' ?>;"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="studentID">Select Student:</label>
        <select name="studentID" id="studentID">
            <option value="">Select Student</option>
            <?php 
                while ($row = $students->fetch_assoc()): ?>
                    <option value="<?= $row['studProgID'] ?>"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="curID">Select Subject:</label>
        <select name="curID" id="curID">
            <option value="">Select Subject</option>
            <?php 
                while ($row = $curriculum->fetch_assoc()): ?>
                    <option value="<?= $row['curID'] ?>"><?= htmlspecialchars($row['subdescription']) ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Enroll</button>
    </form>
</div>