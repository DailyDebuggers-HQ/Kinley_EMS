<?php 
require_once __DIR__ . "/../config/database.php";


class Enrollment {
    public static function exists($conn, $studProgID, $curID){
        $stmt= $conn->prepare("SELECT * from sub_enrolled where studProgID = ? and curID = ?");
        $stmt->bind_param("ii", $studProgID, $curID);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public static function enroll ($conn, $studProgID, $curID) {
        $stmt = $conn->prepare("INSERT INTO sub_enrolled (studProgID, curID) VALUES (?, ?)");
        $stmt->bind_param("ii", $studProgID, $curID);
        return $stmt->execute();
    }
}