<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../includes/edit_functions.php';

header('Content-Type: application/json');

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection error');
    }

    $schedules = fetchAllDefenseSchedules($pdo);

    echo json_encode([
        'success' => true,
        'schedules' => $schedules
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
