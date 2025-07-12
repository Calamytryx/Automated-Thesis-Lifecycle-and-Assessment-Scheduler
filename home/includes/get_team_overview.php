<?php
session_start();
include '../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$userId = $_SESSION['id'];
$userType = $_SESSION['usertype'];
$teamId = isset($_GET['team_id']) ? $_GET['team_id'] : null;
 
try {
    // Fetch teams based on user type and role
    if ($userType == 1) { // Student
        $teamsStmt = $pdo->prepare("SELECT t.id, t.name FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ?");
        $teamsStmt->execute([$userId]);
        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
    } else { // Staff/Adviser
        $teamsStmt = $pdo->prepare("SELECT t.id, t.name FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ? AND tm.role IN ('adviser', 'panelist')");
        $teamsStmt->execute([$userId]);
        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (empty($teams)) {
        echo json_encode(['success' => false, 'message' => 'No teams found']);
        exit;
    }

    // If no specific team is requested, use the first team
    if (!$teamId) {
        $teamId = $teams[0]['id'];
    }

    // Find the selected team details
    $selectedTeam = null;
    foreach ($teams as $team) {
        if ($team['id'] == $teamId) {
            $selectedTeam = $team;
            break;
        }
    }

    if (!$selectedTeam) {
        echo json_encode(['success' => false, 'message' => 'Team not found']);
        exit;
    }

    // Fetch requirement progress with categorization
    $requirementsStmt = $pdo->prepare("
        SELECT r.id, r.name, r.description, r.due_date,
               COALESCE(tr.status, 'pending') as status,
               tr.submitted_at, tr.feedback
        FROM requirements r 
        LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = ?
        ORDER BY r.due_date ASC, r.name ASC
    ");
    $requirementsStmt->execute([$teamId]);
    $requirements = $requirementsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Categorize requirements
    $categorized = [
        'completed' => [],
        'pending' => []
    ];

    foreach ($requirements as $req) {
        if ($req['status'] === 'approved') {
            $categorized['completed'][] = $req;
        } else {
            $categorized['pending'][] = $req;
        }
    }

    // Fetch next defense schedule
    $defenseStmt = $pdo->prepare("SELECT * FROM defense_schedules WHERE team_id = ? ORDER BY schedule_date ASC LIMIT 1");
    $defenseStmt->execute([$teamId]);
    $defense = $defenseStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'teams' => $teams,
        'selectedTeam' => $selectedTeam,
        'requirements' => $categorized,
        'defense' => $defense,
        'totalRequirements' => count($requirements),
        'completedCount' => count($categorized['completed']),
        'pendingCount' => count($categorized['pending'])
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>