<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Student.php';

$order = (isset($_GET['sort']) && $_GET['sort'] === 'desc') ? 'DESC' : 'ASC';
$toggleOrder = ($order === "ASC") ? 'desc' : 'asc';

$search = $_GET['search'] ?? null;
$result = Student::all($conn, $order, $search);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Enrollment System</title>
        <link rel="stylesheet" href="/enrollment_system/public/assets/css/style.css">
        <style>
            .action-cell {
                display: flex;
                gap: 6px;
                justify-content: center;
            }

            .action-form {
                margin: 0;
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

            .search-form {
                padding-bottom: 10px;
            }

            .search-form button {
                background: #3498db;
                color: #fff;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                cursor: pointer;
            }
            .search-form input {
                height: 20px;
            }
        </style>
    </head>

    <body>
        <div class="headContainer">
            <nav class="headerLinks">
                <div class="left-links">
                    <a href="/enrollment_system/public/students/index.php">Students</a>
                    <a href="/enrollment_system/public/curriculum/index.php">Curriculum</a>
                    <a href="/enrollment_system/public/course/index.php">Courses</a>
                </div>

                <a class="logout" href="/enrollment_system/public/logout.php">Logout</a>
            </nav>
        </div>

        <div class="container">
            <h2>
                Student Management System
            </h2>

            <div class="cards">
                <a class="card" href="/enrollment_system/public/students/add_students.php">Add Students</a>
            </div>

            <div class="table-container">
                <h3>Students List</h3>
                <form method = "GET" class="search-form">
                    <input type="text" name="search" placeholder="Search student..." 
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit">Search</button>
                </form>
                <table>
                    <tr>
                        <th>No.</th>
                        <th>Last Name
                        <a href="?sort=<?= $toggleOrder ?>&search=<?= urlencode($search) ?>"
                        style="text-decoration: none; font-size: 0.9em;">
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
                            <td class = "action-cell">
                                <form action="/enrollment_system/public/students/student_history.php" method="GET" class="action-form">
                                    <input type="hidden" name="studentID" value="<?= $row['studentID'] ?>">
                                    <button type="submit" class="btn btn-view">View History</button>
                                </form>
                                <br>
                                <form action="/enrollment_system/public/students/enroll_student.php" method="GET" class="action-form">
                                    <input type="hidden" name="studentID" value="<?= $row['studentID'] ?>">
                                    <button type="submit" class = "btn btn-enroll">Enroll</button>
                                </form>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </body>
</html>