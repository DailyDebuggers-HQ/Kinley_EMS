<?php include 'config.php';


$message = "";

$students = $conn->query("SELECT studentID, firstname, lastname FROM student");
$courses = $conn->query("SELECT courseCode, courseName from course");

$choicegrades = 
["5.00", "4.75", "4.50", "4.25", "4.00", 
"3.75", "3.50", "3.25", "3.00", "2.75", 
"2.50", "2.25", "2.00", "1.75", "1.50", 
"1.25", "1.00", "NC"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentID = $_POST['studentID'];
    $courseCode = $_POST['courseCode'];
    $grades = $_POST['grades'];



    $check = $conn->prepare("SELECT * FROM enrollment where studentID = ? and courseCode = ?");
    $check->bind_param("ii", $studentID, $courseCode);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "This student is already enrolled in this course!";
    }

    else {
        $stmt = $conn->prepare("INSERT INTO enrollment (studentID, courseCode, grades) values (?, ?, ?)");
        $stmt->bind_param("iis", $studentID, $courseCode, $grades);

        if($stmt->execute()) {
            $message = "Enrollment added successfully!";
        }
        else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $check->close();

    /* this is the task that we need to fix: 
    1. (optional) we should be able to modify the grades instead of alerting them. 
    2. the alert is not right. we could do no.1 to fix that.*/

}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Add Enrollment</title>
    </head>
    <body>
        <h2>Add Enrollment</h2>

        <?php if($message != ""): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            Student:
            <select name="studentID" required>
                <option value="" disabled selected>Select a student</option>
                <?php while($row = $students->fetch_assoc()): ?>
                    <option value="<?php echo $row['studentID']; ?>">
                        <?php echo $row['firstname'] . ", " . $row['lastname']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <br><br>

            Course:
            <select name="courseCode" required>
                <option value="" disabled selected>Select a course</option>
                <?php while($row = $courses->fetch_assoc()): ?>
                    <option value="<?php echo $row['courseCode']; ?>">
                        <?php echo $row['courseName']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <br><br>

            Grade:
            <select name="grades" required>
                <option value="" disabled selected>Select a grade</option>
                <?php foreach($choicegrades as $grades): ?>
                    <option value="<?php echo $grades; ?>"><?php echo $grades; ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <input type="submit" value="Add Enrollment">
    </body>

</html>
