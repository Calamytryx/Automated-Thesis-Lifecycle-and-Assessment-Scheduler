<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

if(isset($_POST['table']) && isset($_POST['id'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];
    
    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables'];
    
    if(!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        exit;
    }
    
    if ($table === 'teams') {
        $stmt = $pdo->prepare("SELECT * FROM teams WHERE id = ?");
        $stmt->execute([$id]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($team) {
            // Fetch team members
            $memberStmt = $pdo->prepare("
                SELECT tm.user_id as id, CONCAT(u.first_name, ' ', u.last_name) as name, tm.role
                FROM team_members tm
                JOIN users u ON tm.user_id = u.id
                WHERE tm.team_id = ?
            ");
            $memberStmt->execute([$id]);
            $members = $memberStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch research title
            $titleStmt = $pdo->prepare("SELECT title FROM research_titles WHERE team_id = ?");
            $titleStmt->execute([$id]);
            $researchTitle = $titleStmt->fetchColumn();

            $team['members'] = $members;
            $team['title'] = $researchTitle;

            echo json_encode(['success' => true, 'data' => $team]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Team not found']);
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($item) {
            // Add table to the response
            $item['table'] = $table;
            echo json_encode(['success' => true, 'data' => $item]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
}
