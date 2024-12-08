<?php
header('Content-Type: application/json');
session_start();
require '../../assets/setup/db.inc.php';

if (!isset($_SESSION['auth'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$team_id = isset($_GET['team_id']) ? $_GET['team_id'] : null;

if (!$team_id) {
    echo json_encode(['success' => false, 'error' => 'No team selected', 'team_id' => $team_id]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM requirements ORDER BY due_date ASC, name ASC");
    $stmt->execute();
    $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $teamReqStmt = $pdo->prepare("SELECT * FROM team_requirements WHERE team_id = ?");
    $teamReqStmt->execute([$team_id]);
    $teamRequirements = $teamReqStmt->fetchAll(PDO::FETCH_ASSOC);

    $teamReqMap = [];
    foreach ($teamRequirements as $teamReq) {
        $teamReqMap[$teamReq['requirement_id']] = $teamReq;
    }

    foreach ($requirements as &$req) {
        if (isset($teamReqMap[$req['id']])) {
            $req = array_merge($req, $teamReqMap[$req['id']]);
        } else {
            $req['status'] = 'pending';
            $req['submitted_at'] = null;
            $req['feedback'] = '';
            $req['file_name'] = '';
        }
    }

    echo json_encode(['success' => true, 'requirements' => $requirements, 'team_id' => $team_id]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
