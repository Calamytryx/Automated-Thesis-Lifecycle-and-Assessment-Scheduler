<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'];
    
    // Remove table from $_POST
    unset($_POST['table']);
    
    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        exit;
    }
    
    // Special handling for research_titles
    if ($table === 'research_titles') {
        $approved = isset($_POST['approved']) ? date('Y-m-d H:i:s') : null;
        unset($_POST['approved']);
        $_POST['approved_at'] = $approved;
    }

    // Special handling for rubrics
    if($table === 'rubrics'){
        $pdo->beginTransaction();
        try {
            // Disable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
            
            // Insert into rubrics table
            $stmt = $pdo->prepare("INSERT INTO rubrics (name, description) VALUES (:name, :description)");
            $stmt->execute(['name' => $_POST['name'], 'description' => $_POST['description']]);
            
            // Enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
            
            // Get the last inserted ID
            $rubricId = $pdo->lastInsertId();
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit;
    }
    
    // Special handling for teams
    if ($table === 'teams') {
        $pdo->beginTransaction();
        
        try {
            // Insert into teams table
            $stmt = $pdo->prepare("INSERT INTO teams (name) VALUES (:name)");
            $stmt->execute(['name' => $_POST['name']]);
            
            // Get the last inserted ID
            $teamId = $pdo->lastInsertId();
            
            // Insert team members if any
            if (isset($_POST['members'])) {
                foreach ($_POST['members'] as $member) {
                    $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
                    $stmt->execute([
                        'team_id' => $teamId,
                        'user_id' => $member['id'],
                        'role' => $member['role']
                    ]);
                }
            }
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit;
    }
    
    // Special handling for users
    if ($table === 'users') {
        // Hash the password
        if (isset($_POST['password'])) {
            $_POST['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
    }
    
    // General handling for other tables
    $columns = implode(", ", array_keys($_POST));
    $values = ":" . implode(", :", array_keys($_POST));
    
    $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($values)");
    
    try {
        $stmt->execute($_POST);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
/**
 * This file is part of the COECSA Thesis Dashboard.
 * 
 * 
 * Description:
 * This script is responsible for adding items to the dashboard.
 * 
 * Usage:
 * Include this file where item addition functionality is required.
 * 
 * Note:
 * Ensure that the necessary dependencies and configurations are set up before including this file.
 */
?>