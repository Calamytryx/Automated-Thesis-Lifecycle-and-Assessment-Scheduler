<?php
require_once '../../assets/setup/db.inc.php';

if(isset($_POST['table']) && isset($_POST['id'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];
    
    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables'];
    
    if(!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        exit;
    }
    
    $updateFields = [];
    $updateValues = [];
    
    $excludedFields = ['id', 'password', 'profile_image', 'verified_at', 'created_at', 'updated_at', 'deleted_at', 'last_login_at'];
    
    foreach($_POST as $key => $value) {
        if(!in_array($key, $excludedFields) && $key !== 'table') {
            // Escape reserved keywords
            $escapedKey = ($key === 'key' || $key === 'table') ? "`$key`" : $key;
            $updateFields[] = "$escapedKey = ?";
            $updateValues[] = $value;
        }
    }
    
    $updateValues[] = $id;
    
    $sql = "UPDATE `$table` SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute($updateValues)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . implode(', ', $stmt->errorInfo())]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
}