<?php
header('Content-Type: application/json');
session_start();
require '../../assets/setup/db.inc.php';

if (!isset($_SESSION['auth'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$team_id = isset($_GET['team_id']) ? $_GET['team_id'] : $_SESSION['team_id'][0];

if (!$team_id) {
    echo json_encode(['success' => false, 'error' => 'No team selected', 'team_id' => $team_id]);
    exit;
}

try {
    $today = date('Y-m-d');

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

    // Build per-requirement file list/count for multi-submission support.
    $submissionFilesByRequirement = [];
    try {
        $filesStmt = $pdo->prepare("\n            SELECT requirement_id, file_name, original_file_name, submission_number, submitted_at\n            FROM team_requirement_files\n            WHERE team_id = ? AND deleted_at IS NULL\n            ORDER BY requirement_id ASC, submission_number DESC, submitted_at DESC\n        ");
        $filesStmt->execute([$team_id]);
        $submissionRows = $filesStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($submissionRows as $row) {
            $reqId = (int)$row['requirement_id'];
            if (!isset($submissionFilesByRequirement[$reqId])) {
                $submissionFilesByRequirement[$reqId] = [];
            }
            $submissionFilesByRequirement[$reqId][] = [
                'file_name' => $row['file_name'] ?? '',
                'original_file_name' => $row['original_file_name'] ?? '',
                'submission_number' => (int)($row['submission_number'] ?? 0),
                'submitted_at' => $row['submitted_at'] ?? null,
            ];
        }
    } catch (PDOException $e) {
        // Keep response working even if legacy DB doesn't have team_requirement_files yet.
        $submissionFilesByRequirement = [];
    }

    foreach ($requirements as &$req) {
        // Include multi-submission settings in the response
        $req['allow_multiple_submissions'] = $req['allow_multiple_submissions'] ?? 0;
        $req['max_submissions'] = $req['max_submissions'] ?? 1;

        $reqId = (int)$req['id'];
        $req['submitted_files'] = $submissionFilesByRequirement[$reqId] ?? [];
        $req['submission_count'] = count($req['submitted_files']);
        
        $hasTeamRequirementRow = isset($teamReqMap[$req['id']]);
        if ($hasTeamRequirementRow) {
            $teamReqData = $teamReqMap[$req['id']];
            // Merge explicitly while keeping `id`
            $req = array_merge($req, [
                'status' => $teamReqData['status'] ?? 'pending',
                'submitted_at' => $teamReqData['submitted_at'] ?? null,
                'feedback' => $teamReqData['feedback'] ?? '',
                'file_name' => $teamReqData['file_name'] ?? '',
            ]);
        } else {
            $req['status'] = 'pending';
            $req['submitted_at'] = null;
            $req['feedback'] = '';
            $req['file_name'] = '';
        }

        // In multi-submission mode, keep a stable fallback display file.
        if ((int)$req['allow_multiple_submissions'] === 1 && empty($req['file_name']) && !empty($req['submitted_files'])) {
            $req['file_name'] = $req['submitted_files'][0]['file_name'] ?? '';
        }

        $isPastDue = !empty($req['due_date']) && $req['due_date'] < $today;
        $hasSubmission = !empty($req['file_name']) || $req['submission_count'] > 0 || !empty($req['submitted_at']);

        // Auto-close only if there is no team_requirements record yet and no submission before deadline.
        // If adviser reopens/reverts to pending, that row exists and remains pending even when overdue.
        if (!$hasTeamRequirementRow && !$hasSubmission && $isPastDue) {
            $req['status'] = 'closed';
            $req['is_deadline_closed'] = 1;
        } else {
            // Keep DB enum unchanged; map rejected to closed label in UI/API response.
            if (($req['status'] ?? '') === 'rejected') {
                $req['status'] = 'closed';
            }
            $req['is_deadline_closed'] = 0;
        }
    }

    echo json_encode(['success' => true, 'requirements' => $requirements, 'team_id' => $team_id]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
