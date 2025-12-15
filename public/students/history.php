<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../controllers/StudentHistoryController.php";

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Student History</title>
        <link rel="stylesheet" href="/enrollment_system/public/assets/css/history.css">
    </head>
    <body>
        <h1>Student History</h1>


        <table border='1' cellpadding='8'>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($historyresult->num_rows > 0): ?>
                    <?php while ($row  = $historyresult-> fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['courseName']) ?></td>
                            <td><?= htmlspecialchars($row['grades'] ?? "Not graded") ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else:?>
                    <tr>
                        <td>
                            No enrollment data found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <button onclick="window.location.href='/enrollment_system/public/index.php'">Return to students</button>
    </body>
</html>