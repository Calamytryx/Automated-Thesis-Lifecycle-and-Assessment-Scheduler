<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $table = $_POST['table'];
    
    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        exit;
    }
    
    try {
        // Disable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        
        // Delete the row
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Re-enable foreign key checks in case of an error
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>