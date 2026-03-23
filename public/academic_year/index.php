<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/auth.php";

// Handle deletion
if (isset($_GET['delete'])) {
    $deleteID = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM academic_years WHERE acadYearID = ?");
    $stmt->bind_param("i", $deleteID);
    $stmt->execute();
    header("Location: index.php");
    exit;
}

/* FETCH ALL ACADEMIC YEARS WITH UNIQUE STUDENT COUNT */
$query = "
    SELECT ay.acadYearID, ay.academicYear, 
           COUNT(DISTINCT se.studEnrollID) AS studentCount
    FROM academic_years ay
    LEFT JOIN student_enrollments se ON se.acadYearID = ay.acadYearID
    GROUP BY ay.acadYearID, ay.academicYear
    ORDER BY ay.academicYear DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Academic Years</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #f2f2f2; }
        a.button { display: inline-block; padding: 6px 12px; margin-bottom: 12px; background: #3498db; color: #fff; text-decoration: none; border-radius: 4px; }
        a.button:hover { background: #2980b9; }
        a.delete { background: #e74c3c; color: #fff; padding: 4px 8px; border-radius: 4px; text-decoration: none; }
        a.delete:hover { background: #c0392b; }
    </style>
</head>
<body>

<div class="container">
    <h2>Academic Years</h2>

    <a href="/enrollment_system/public/academic_year/add_year.php" class="button">Add Academic Year</a>
    <a href="/enrollment_system/public/students/index.php" class="button">Return to Students</a>

    <table>
        <thead>
            <tr>
                <th>Academic Year</th>
                <th>Number of Students</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['academicYear']) ?></td>
                        <td><?= (int)$row['studentCount'] ?></td>
                        <td>
                            <a href="?delete=<?= $row['acadYearID'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this academic year?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No academic years found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>