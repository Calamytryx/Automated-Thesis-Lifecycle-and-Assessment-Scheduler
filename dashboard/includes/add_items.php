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
    
    // Special handling for teams
    if ($table === 'teams') {
        $pdo->beginTransaction();
        
        try {
            // Insert into teams table
            $stmt = $pdo->prepare("INSERT INTO teams (name) VALUES (:name)");
            $stmt->execute(['name' => $_POST['name']]);
            $team_id = $pdo->lastInsertId();
            
            // Insert into research_titles table
            $stmt = $pdo->prepare("INSERT INTO research_titles (team_id, title) VALUES (:team_id, :title)");
            $stmt->execute(['team_id' => $team_id, 'title' => $_POST['title']]);
            
            // Insert team members
            $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
            foreach ($_POST['new_member_id'] as $key => $user_id) {
                $stmt->execute([
                    'team_id' => $team_id,
                    'user_id' => $user_id,
                    'role' => $_POST['new_member_role'][$key]
                ]);
            }
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    $columns = implode(', ', array_keys($_POST));
    $values = ':' . implode(', :', array_keys($_POST));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($values)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($_POST)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Insert failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}