<?php

/**
 * Description: this file will check active campus for enquiry.
 * Author: An Bao Le
 */
?>
<?php
require_once __DIR__ . '/inc/db.php';
header('Content-Type: application/json');
$course_id = $_GET['course_id'] ?? null;
if ($course_id) {
    try {
        // query check active campus
        $sql = "SELECT cp.campus_id, cp.name 
                FROM campus cp
                INNER JOIN course_location cl ON cp.campus_id = cl.campus_id
                WHERE cl.course_id = :course_id AND cl.status = 'active'
                ORDER BY cp.name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
    // use json_encode like it is api, like when use yahoo finance to link in python but it is api endpoint.
}
