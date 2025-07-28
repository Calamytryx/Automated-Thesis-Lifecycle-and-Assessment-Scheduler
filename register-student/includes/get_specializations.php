<?php
header('Content-Type: application/json');
require_once '../../assets/setup/db.inc.php';

$programName = isset($_GET['program']) ? $_GET['program'] : '';

if (empty($programName)) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT specialization FROM programs WHERE name = ? AND specialization IS NOT NULL AND specialization != '' ORDER BY specialization ASC");
    $stmt->execute([$programName]);
    $specializations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($specializations);
} catch (PDOException $e) {
    // Optionally log the error
    echo json_encode([]);
}
?>
