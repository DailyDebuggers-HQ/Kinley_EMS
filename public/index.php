<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Student.php';

$result = Student::all($conn);
?>

<div class="container">
    <h2>
        Dashboard
    </h2>
    <p>Welcome to my Simple Enrollment System</p>

    <div class="cards">
        <a class="card" href="/enrollment_system/public/students/add_students.php">Add Students</a>
        <a class="card" href="/enrollment_system/public/courses/add_courses.php">Add Courses</a>
        <a class="card" href="/enrollment_system/public/enrollments/index.php">Manage Enrollments</a>
    </div>

    <div class="table-container">
        <h3>Students List</h3>
        <table>
            <tr>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Action</th>
            </tr>

            <?php 
                while ($row = $result->fetch_assoc()):
            ?>

                <tr>
                    <td><?= $row['lastname'] ?></td>
                    <td><?= $row['firstname'] ?></td>
                    <td>
                        <a href="viewHistory.php?studentID=<?= $row['studentID'] ?>">View History</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>