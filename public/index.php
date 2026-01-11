<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Student.php';

$order = (isset($_GET['sort']) && $_GET['sort'] === 'desc') ? 'DESC' : 'ASC';
$result = Student::all($conn, $order);
$toggleOrder = ($order === "ASC") ? 'desc' : 'asc';

?>

<div class="container">
    <h2>
        Student Management System
    </h2>

    <div class="cards">
        <!--<a class="card" href="/enrollment_system/public/students/add_students.php">Add Students</a>
        <a class="card" href="/enrollment_system/public/curriculum/add_curriculum.php">Add Curriculum</a>
        <a class="card" href="/enrollment_system/public/course/add_courses.php">Add Courses</a>
        <a class="card" href="/enrollment_system/public/progress.php">Enroll Students</a>-->
    </div>

    <div class="table-container">
        <h3>Students List</h3>
        <table>
            <tr>
                <th>No.</th>
                <th>Last Name
                <a href="?sort=<?= $toggleOrder ?>" style="text-decoration: none; font-size: 0.9em;"><?= ($order === 'ASC') ? '▲' : '▼' ?></a>
                </th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Program</th>
                <th>Action</th>
            </tr>

            <?php
                $no = 1;
                while ($row = $result->fetch_assoc()):
            ?>

                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['lastname'] ?></td>
                    <td><?= $row['firstname'] ?></td>
                    <td><?= $row['middlename'] ?></td>
                    <td><?= $row['courseName'] ?></td>
                    <td>
                        <a href="/enrollment_system/public/students/student_history.php?studentID=<?= $row['id'] ?>">View History</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>