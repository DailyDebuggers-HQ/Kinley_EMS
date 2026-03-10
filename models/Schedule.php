<?php 
require_once __DIR__ . "/../config/database.php";

class Schedule {
    public static function getByID($conn, $schedID) {
        $stmt = $conn->prepare("
            SELECT sc.schedID, cur.subdescription, sc.day, sc.startTime, sc.endTime, sc.room, sc.section
            FROM schedule sc
            JOIN course_curriculum cc ON sc.courCurID = cc.courCurID
            JOIN curriculum cur ON cc.curID = cur.curID
            WHERE sc.schedID = ?
        ");

        $stmt->bind_param("i", $schedID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
    
    
}