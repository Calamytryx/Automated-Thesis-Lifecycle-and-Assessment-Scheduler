<?php
session_start();
include '../../assets/setup/db.inc.php';

header('Content-Type: application/json');

if (!isset($_GET['team_id'])) {
    echo json_encode(['success' => false, 'message' => 'Team ID is required']);
    exit;
}

$teamId = $_GET['team_id'];
$userId = $_SESSION['id'];
$userType = $_SESSION['usertype'];

try {
    // Verify user has access to this team
    if ($userType == 1) { // Student
        $accessStmt = $pdo->prepare("SELECT COUNT(*) FROM team_members WHERE team_id = ? AND user_id = ?");
        $accessStmt->execute([$teamId, $userId]);
    } else { // Staff/Adviser
        $accessStmt = $pdo->prepare("SELECT COUNT(*) FROM team_members WHERE team_id = ? AND user_id = ? AND role IN ('adviser', 'panelist')");
        $accessStmt->execute([$teamId, $userId]);
    }
    
    if ($accessStmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Access denied to this team']);
        exit;
    }

    // Fetch requirements for the team, including template file fields
    $requirementsStmt = $pdo->prepare("
        SELECT r.id, r.name, r.description, r.due_date,
               r.template_file, r.template_original_name,
               COALESCE(tr.status, 'pending') as status,
               tr.submitted_at, tr.feedback
        FROM requirements r 
        LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = ?
        ORDER BY r.due_date ASC, r.name ASC
    ");
    $requirementsStmt->execute([$teamId]);
    $requirements = $requirementsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'requirements' => $requirements
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
