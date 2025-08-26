<?php

require_once '../../assets/setup/db.inc.php';

header('Content-Type: application/json');
$college = $_GET['college'] ?? '';
if ($college) {
    $stmt = $pdo->prepare("SELECT DISTINCT name FROM programs WHERE college = ? ORDER BY name ASC");
    $stmt->execute([$college]);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($programs);
} else {
    echo json_encode([]);
}