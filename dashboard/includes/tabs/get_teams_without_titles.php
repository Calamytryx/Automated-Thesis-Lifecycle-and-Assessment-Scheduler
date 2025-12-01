<?php
require_once '../../../assets/setup/db.inc.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT *
            FROM teams t 
            LEFT JOIN research_titles rt ON t.id = rt.team_id 
            WHERE rt.id IS NULL 
            ORDER BY t.name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format response to highlight name and program
    $result = [];
    foreach ($teams as $team) {
        $result[] = [
            'id' => $team['id'],
            'name' => $team['name'],
            'program' => $team['program']
        ];
    }
    
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}