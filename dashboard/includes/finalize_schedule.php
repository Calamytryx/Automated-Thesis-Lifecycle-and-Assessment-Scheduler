<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/edit_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$userId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : -1;
$usertype = isset($_SESSION['usertype']) ? (int)$_SESSION['usertype'] : -1;

if ($usertype !== 0) {
    echo json_encode(['success' => false, 'message' => 'Only administrators/program chairs can finalize schedules.']);
    exit;
}

$scheduleId = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;
$action = $_POST['action'] ?? 'finalize';

if ($scheduleId <= 0 || !in_array($action, ['finalize', 'unfinalize'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters.']);
    exit;
}

if (!defenseScheduleColumnExists($pdo, 'is_finalized')) {
    echo json_encode([
        'success' => false,
        'message' => 'Finalization columns are not available. Run the finalization migration first.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT team_id FROM defense_schedules WHERE id = ? LIMIT 1");
    $stmt->execute([$scheduleId]);
    $teamId = (int)$stmt->fetchColumn();

    if ($teamId <= 0) {
        throw new Exception('Schedule not found.');
    }

    if (!canUserAccessDefenseScheduleByTeam($pdo, $userId, $usertype, $teamId)) {
        throw new Exception('You do not have access to this schedule.');
    }

    if ($action === 'finalize') {
        $update = $pdo->prepare("\
            UPDATE defense_schedules\
            SET is_finalized = 1, finalized_by = ?, finalized_at = NOW()\
            WHERE id = ?\
        ");
        $update->execute([$userId, $scheduleId]);
        echo json_encode(['success' => true, 'message' => 'Schedule finalized and locked.']);
    } else {
        $canUnfinalize = ($userId === 0 || $usertype === 0);
        if (!$canUnfinalize) {
            throw new Exception('You do not have permission to unfinalize this schedule.');
        }

        $update = $pdo->prepare("\
            UPDATE defense_schedules\
            SET is_finalized = 0, finalized_by = NULL, finalized_at = NULL\
            WHERE id = ?\
        ");
        $update->execute([$scheduleId]);
        echo json_encode(['success' => true, 'message' => 'Schedule unlocked.']);
    }
} catch (Exception $e) {
    error_log('finalize_schedule.php ERROR: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
