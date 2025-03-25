<?php
session_start();
include '../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$userId = $_SESSION['id'];
$userType = $_SESSION['usertype'];
 
try {
    // Fetch team details
    $teamStmt = $pdo->prepare("SELECT t.id, t.name FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ?");
    $teamStmt->execute([$userId]);
    $team = $teamStmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        echo json_encode(['success' => false, 'message' => 'No team found']);
        exit;
    }

    $teamId = $team['id'];

    // Fetch requirement progress
    $requirementsStmt = $pdo->prepare("SELECT r.name, tr.status FROM requirements r LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = ?");
    $requirementsStmt->execute([$teamId]);
    $requirements = $requirementsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch next defense schedule
    $defenseStmt = $pdo->prepare("SELECT * FROM defense_schedules WHERE team_id = ? ORDER BY schedule_date ASC LIMIT 1");
    $defenseStmt->execute([$teamId]);
    $defense = $defenseStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'team' => $team,
        'requirements' => $requirements,
        'defense' => $defense
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>