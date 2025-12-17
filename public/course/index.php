<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Course.php";
require_once __DIR__ . "/../../includes/header.php";

$order = (isset($_GET['sort']) && $_GET['sort'] === 'desc') ? 'DESC' : 'ASC';
$result = Course::all($conn, $order);
$toggleOrder = ($order === "ASC") ? 'desc' : 'asc';

?>

<div class="table-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Course List</h3>
        <a href='/enrollment_system/public/index.php'>Return to Dashboard</a>
    </div>
    <table>
        <tr>
            <th>Course Name
                <a href="?sort=<?= $toggleOrder ?>" style="text-decoration: none; font-size: 0.9em;"><?= ($order === 'ASC') ? '▲' : '▼' ?></a>
            </th>
            <th>Course Description</th>
        </tr>

        <?php 
            while ($row = $result->fetch_assoc()):
        ?>

        <tr>
            <td><?= $row['courseName'] ?></td>
            <td><?= $row['courseDesc'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>