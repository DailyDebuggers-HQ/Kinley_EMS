<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

/* Dashboard statistics */
$totalStudents = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$totalCourses = $conn->query("SELECT COUNT(*) as total FROM course")->fetch_assoc()['total'];
$totalSchedules = $conn->query("SELECT COUNT(*) as total FROM schedule")->fetch_assoc()['total'];

require_once __DIR__ . '/../includes/header.php';
?>

<h2>Dashboard</h2>

<div style="display:flex; gap:20px; margin-top:20px;">

    <div style="background:white; padding:20px; border-radius:8px; flex:1;">
        <h1><?= $totalStudents ?></h1>
        <p>Total Students</p>
    </div>

    <div style="background:white; padding:20px; border-radius:8px; flex:1;">
        <h1><?= $totalCourses ?></h1>
        <p>Total Courses</p>
    </div>

    <div style="background:white; padding:20px; border-radius:8px; flex:1;">
        <h1><?= $totalSchedules ?></h1>
        <p>Total Schedules</p>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>