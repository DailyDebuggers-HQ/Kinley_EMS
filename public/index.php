<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

/* Dashboard statistics */
$totalStudents = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$totalCourses = $conn->query("SELECT COUNT(*) as total FROM course")->fetch_assoc()['total'];
$totalSchedules = $conn->query("SELECT COUNT(*) as total FROM schedule")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html>
<head>

<title>Enrollment System Dashboard</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>


body{
    overflow-x:hidden;
}


.sidebar{
    height:100vh;
    width:230px;
    position:fixed;
    background:#1f2d3d;
    color:white;
}

.sidebar h4{
    text-align:center;
    padding:20px 0;
    border-bottom:1px solid rgba(255,255,255,0.2);
}

.sidebar a{
    display:block;
    color:white;
    text-decoration:none;
    padding:12px 20px;
}

.sidebar a:hover{
    background:#2c3e50;
}

.main{
    margin-left:230px;
}


.topbar{
    background:white;
    border-bottom:1px solid #ddd;
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
}

.dashboard-cards .card{
    border:none;
    box-shadow:0 2px 6px rgba(0,0,0,0.08);
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <h4>Enrollment</h4>

    <a href="/enrollment_system/public/index.php">Dashboard</a>
    <a href="/enrollment_system/public/students/index.php">Students</a>
    <a href="/enrollment_system/public/course/index.php">Courses</a>
    <a href="/enrollment_system/public/curriculum/index.php">Curriculum</a>
    <a href="/enrollment_system/public/schedule/index.php">Schedule</a>
    <a href="/enrollment_system/public/logout.php">Logout</a>

</div>

<!-- MAIN -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">
        <h4>Welcome, Jhon Kinley Laviña</h4>
        <span>Enrollment Management System</span>
    </div>

    <div class="container-fluid mt-4">

        <!-- DASHBOARD CARDS -->

        <div class="row dashboard-cards g-4">

            <div class="col-md-4">
                <div class="card p-3">
                    <h2><?= $totalStudents ?></h2>
                    <p>Total Students</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3">
                    <h2><?= $totalCourses ?></h2>
                    <p>Total Courses</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3">
                    <h2><?= $totalSchedules ?></h2>
                    <p>Total Schedules</p>
                </div>
            </div>

        </div>

        <!-- QUICK ACTIONS -->

        <div class="mt-5">

            <h5>Quick Actions</h5>

            <a href="/enrollment_system/public/students/add_students.php"
            class="btn btn-primary me-2">
            Add Student
            </a>

            <a href="/enrollment_system/public/schedule/add_schedule.php"
            class="btn btn-success me-2">
            Add Schedule
            </a>

        </div>

    </div>

</div>

</body>
</html>