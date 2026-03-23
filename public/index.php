<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if logged in
$isLoggedIn = isset($_SESSION['admin']);

if ($isLoggedIn) {
    $totalStudents = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
    $totalCourses = $conn->query("SELECT COUNT(*) as total FROM course")->fetch_assoc()['total'];
    $totalSchedules = $conn->query("SELECT COUNT(*) as total FROM schedule")->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollment Management System</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: #1e293b;
            color: white;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        .navbar a:hover {
            text-decoration: underline;
            color: #60a5fa;
        }

        .hero {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(to right, #2563eb, #1e40af);
            color: white;
        }

        .hero h1 {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
        }

        .btn {
            padding: 12px 25px;
            background: white;
            color: #1e40af;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .section {
            padding: 60px 40px;
            text-align: center;
        }

        .cards {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 280px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .stat-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 250px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
        }

        footer {
            text-align: center;
            padding: 20px;
            background: #1e293b;
            color: white;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <h2>Enrollment Management System</h2>

        <div class="nav-links">
            <?php if ($isLoggedIn): ?>
                <a href="index.php">Home</a>
                <a href="students/index.php">Students</a>
                <a href="course/index.php">Courses</a>
                <a href="curriculum/index.php">Curriculum</a>
                <a href="schedule/index.php">Enrollment</a>
                <a href="logout.php" class="logout">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$isLoggedIn): ?>

        <!-- PUBLIC LANDING -->
        <div class="hero">
            <h1>Enrollment Management System</h1>
            <p>Streamlining enrollment, scheduling, and grading in one centralized platform.</p>
            <a href="login.php" class="btn">Get Started</a>
        </div>

        <div class="section">
            <h2>System Features</h2>
            <div class="cards">
                <div class="card">
                    <h3>Student Enrollment</h3>
                    <p>Efficient student registration and record management.</p>
                </div>

                <div class="card">
                    <h3>Schedule Management</h3>
                    <p>Create and organize class schedules with ease.</p>
                </div>

                <div class="card">
                    <h3>Grade Monitoring</h3>
                    <p>Track and update academic performance accurately.</p>
                </div>
            </div>
        </div>

    <?php else: ?>

        <!-- LOGGED IN OVERVIEW -->
        <div class="section">
            <h2>System Overview</h2>

            <div class="stats">
                <div class="stat-box">
                    <h1><?= $totalStudents ?></h1>
                    <p>Total Students</p>
                </div>

                <div class="stat-box">
                    <h1><?= $totalCourses ?></h1>
                    <p>Total Courses</p>
                </div>

                <div class="stat-box">
                    <h1><?= $totalSchedules ?></h1>
                    <p>Total Schedules</p>
                </div>
            </div>
        </div>

    <?php endif; ?>

</body>
</html>