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
?>