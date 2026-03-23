<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../includes/auth.php";

$message = $status = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $academicYear = trim($_POST['academicYear'] ?? '');

    // -------------------------
    // Validate format YYYY-YYYY
    // -------------------------
    if (empty($academicYear)) {
        $status  = "error";
        $message = "Academic year cannot be empty.";
    } elseif (!preg_match('/^\d{4}-\d{4}$/', $academicYear)) {
        $status  = "error";
        $message = "Invalid format. Use YYYY-YYYY, e.g., 2026-2027.";
    } else {
        list($start, $end) = explode('-', $academicYear);
        if ((int)$end !== (int)$start + 1) {
            $status  = "error";
            $message = "Invalid range. Second year must be first year + 1.";
        } else {
            // -------------------------
            // Check for duplicates
            // -------------------------
            $check = $conn->prepare("SELECT 1 FROM academic_years WHERE academicYear = ? LIMIT 1");
            $check->bind_param("s", $academicYear);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $status = "error";
                $message = "This academic year already exists.";
            } else {
                // -------------------------
                // Insert new academic year
                // -------------------------
                $stmt = $conn->prepare("INSERT INTO academic_years (academicYear) VALUES (?)");
                $stmt->bind_param("s", $academicYear);

                if ($stmt->execute()) {
                    $newAcadYearID = $stmt->insert_id;
                    $status = "success";
                    $message = "Academic year added successfully.";

                    // -------------------------
                    // Find last revision (most recent academic year)
                    // -------------------------
                    $lastYearRes = $conn->query("
                        SELECT acadYearID
                        FROM academic_years
                        WHERE acadYearID != $newAcadYearID
                        ORDER BY STR_TO_DATE(CONCAT(SUBSTRING_INDEX(academicYear,'-',1),'-01-01'), '%Y-%m-%d') DESC
                        LIMIT 1
                    ");

                    if ($lastYearRow = $lastYearRes->fetch_assoc()) {
                        $lastAcadYearID = $lastYearRow['acadYearID'];

                        // -------------------------
                        // Copy schedules from last revision
                        // -------------------------
                        $copyStmt = $conn->prepare("
                            INSERT INTO schedule (courCurID, acadYearID, semester, day, start_time, end_time, room, section)
                            SELECT courCurID, ?, semester, day, start_time, end_time, room, section
                            FROM schedule
                            WHERE acadYearID = ?
                        ");
                        $copyStmt->bind_param("ii", $newAcadYearID, $lastAcadYearID);
                        $copyStmt->execute();
                    }

                    $academicYear = ""; // clear form
                } else {
                    $status = "error";
                    $message = "Failed to add academic year. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Academic Year</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        .container { max-width: 500px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; margin-bottom: 20px; }
        input[type="text"], button { width: 100%; padding: 10px; margin-bottom: 12px; border-radius: 4px; border: 1px solid #ccc; }
        button { background: #3498db; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #2980b9; }
        .message { padding: 10px; border-radius: 4px; margin-bottom: 12px; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        a { text-decoration: none; color: #3498db; display: inline-block; margin-top: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Academic Year</h2>

    <?php if ($message): ?>
        <div class="message <?= $status ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Academic Year (e.g., 2026-2027)</label>
        <input type="text" name="academicYear" value="<?= htmlspecialchars($academicYear ?? '') ?>" required>
        <button type="submit">Add Academic Year</button>
    </form>

    <a href="/enrollment_system/public/academic_year/index.php">&larr; Back to Academic Years</a>
</div>

</body>
</html>