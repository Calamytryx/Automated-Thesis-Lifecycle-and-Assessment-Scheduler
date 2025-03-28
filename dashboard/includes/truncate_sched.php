<?php
require_once './../../assets/setup/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Disable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        $sql = "TRUNCATE TABLE defense_schedules";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        echo json_encode(['status' => 'success', 'message' => 'Schedules truncated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to truncate schedules.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}