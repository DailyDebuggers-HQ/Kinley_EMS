<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Student.php";
require_once __DIR__ . "/../../includes/header.php";

$order = (isset($_GET['sort']) && $_GET['sort'] === 'desc') ? 'DESC' : 'ASC';
$toggleOrder = ($order === "ASC") ? 'desc' : 'asc';

/* SEARCH */
$search = $_GET['search'] ?? null;

/* GET STUDENTS */
$result = Student::all($conn, $order, $search);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Students List</title>
        <style>
            .action-cell{
                display: flex;
                gap: 6px;
                justify-content: center;
            }
            .action-form{
                margin:0;
            }
            .btn { 
                padding: 4px 10px; 
                border-radius: 4px; 
                border: none; 
                cursor: pointer; 
                font-size: 0.85rem; 
            } 
            .btn-view { 
                background: #3498db; 
                color: #fff; 
            } 
            .btn-view:hover { 
                background: #2980b9; 
            } 
            .btn-enroll { 
                background: #2ecc71; 
                color: #fff; 
            } 
            .btn-enroll:hover { 
                background: #27ae60; 
            }
        </style>
    </head>
    <body>

        <div class="table-container">

            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3>Students List</h3>
                <a href='/enrollment_system/public/index.php'>Return to Dashboard</a>
            </div>

            <!-- SEARCH FORM -->
            <form method="GET" style="margin-bottom:10px;">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search student..."
                    value="<?= htmlspecialchars($search ?? '') ?>"
                >

                <button type="submit">Search</button>
            </form>

            <table>
                <tr>
                    <th>No.</th>

                    <th>
                        Last Name
                        <a href="?sort=<?= $toggleOrder ?>&search=<?= urlencode($search) ?>"
                        style="text-decoration:none; font-size:0.9em;">
                            <?= ($order === 'ASC') ? '▲' : '▼' ?>
                        </a>
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

                    <td class="action-cell">
                        <form action="/enrollment_system/public/students/student_history.php" method="GET" class="action-form">
                            <input type="hidden" name="studentID" value="<?= $row['studentID'] ?>"> 
                            <button type="submit" class="btn btn-view">View History</button> 
                        </form> <br> 
                        <form action="/enrollment_system/public/students/enroll_student.php" method="GET" class="action-form">
                            <input type="hidden" name="studentID" value="<?= $row['studentID'] ?>"> 
                            <button type="submit" class = "btn btn-enroll">Enroll</button> 
                        </form>
                    </td>

                </tr>

                <?php endwhile; ?>

            </table>
        </div>
    </body>
</html>