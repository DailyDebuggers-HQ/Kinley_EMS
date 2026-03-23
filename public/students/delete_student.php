<?php
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = $_POST['studentID'] ?? null;

    if ($studentID) {
        // Delete student
        $stmt = $conn->prepare("UPDATE students SET status = 'INACTIVE' WHERE studentID = ?");
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to students list
header("Location: index.php");
exit();