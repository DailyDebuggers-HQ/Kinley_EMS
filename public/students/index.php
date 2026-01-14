<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Student.php";
require_once __DIR__ . "/../../includes/header.php";

$order = (isset($_GET['sort']) && $_GET['sort'] === 'desc') ? 'DESC' : 'ASC';
$result = Student::all($conn, $order);
$toggleOrder = ($order === "ASC") ? 'desc' : 'asc';

?>

<div class="table-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Students List</h3>
        <a href='/enrollment_system/public/index.php'>Return to Dashboard</a>
    </div>
    <table>
        <tr>
            <th>Last Name
                <a href="?sort=<?= $toggleOrder ?>" style="text-decoration: none; font-size: 0.9em;"><?= ($order === 'ASC') ? '▲' : '▼' ?></a>
            </th>
            <th>First Name</th>
            <th>Middle name</th>
            <th>Age</th>
            <th>Action</th>
        </tr>

        <?php 
            while ($row = $result->fetch_assoc()):
        ?>

        <tr>
            <td><?= $row['lastname'] ?></td>
            <td><?= $row['firstname'] ?></td>
            <td><?= $row['middlename'] ?></td>
            <td><?= $row['age'] ?></td>
            <td>
                <a href="/enrollment_system/public/students/student_history.php?studentID=<?= $row['studentID'] ?>">View History</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>