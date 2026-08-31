<?php
// Admin Course Add Page
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';

try {
    $stmt = $pdo->query("SELECT target_audience FROM course");
    $target_audience = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
try {
    $stmt = $pdo->query("SELECT name FROM campus");
    $campus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$success_msg = "";
$error_msg = "";
