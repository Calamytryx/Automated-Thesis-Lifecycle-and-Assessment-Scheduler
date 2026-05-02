<?php
header('Content-Type: application/json');
session_start();
require '../../assets/setup/db.inc.php';

if (!isset($_SESSION['auth'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $userId = (int)($_SESSION['id'] ?? 0);
    $userType = (int)($_SESSION['usertype'] ?? -1);

    // Only admin/program chair and faculty advisers can update requirement statuses.
    if (!in_array($userType, [0, 2], true)) {
        throw new Exception('Permission denied.');
    }

    $sessionTeamIds = isset($_SESSION['team_id']) ? (array)$_SESSION['team_id'] : [];
    $fallbackTeamId = isset($sessionTeamIds[0]) ? (int)$sessionTeamIds[0] : 0;
    $postedTeamId = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
    $targetTeamId = $postedTeamId > 0 ? $postedTeamId : $fallbackTeamId;

    if ($targetTeamId <= 0) {
        throw new Exception('No valid team selected.');
    }

    if ($userType === 2) {
        $accessStmt = $pdo->prepare("SELECT COUNT(*) FROM team_members WHERE team_id = ? AND user_id = ? AND LOWER(role) = 'adviser'");
        $accessStmt->execute([$targetTeamId, $userId]);
        if ((int)$accessStmt->fetchColumn() === 0) {
            throw new Exception('You are not the adviser of this team.');
        }
    }

    $statusMap = isset($_POST['status']) && is_array($_POST['status']) ? $_POST['status'] : [];
    $requirementsFromStatus = array_keys($statusMap);
    $requirementsFromCheckbox = isset($_POST['requirements']) && is_array($_POST['requirements']) ? $_POST['requirements'] : [];
    $requirementIds = array_unique(array_map('intval', array_merge($requirementsFromStatus, $requirementsFromCheckbox)));
    $requirementIds = array_values(array_filter($requirementIds, function($id) { return $id > 0; }));

    if (empty($requirementIds)) {
        throw new Exception('No requirement status data received.');
    }

    $allowedStatuses = ['approved', 'submitted', 'pending', 'rejected'];
    $updatedCount = 0;

    $teamStmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
    $teamStmt->execute([$targetTeamId]);
    $teamName = $teamStmt->fetchColumn() ?: ('team-' . $targetTeamId);

    $reqNameStmt = $pdo->prepare("SELECT name FROM requirements WHERE id = ?");
    $existingStmt = $pdo->prepare("SELECT id FROM team_requirements WHERE team_id = ? AND requirement_id = ? LIMIT 1");
    $insertStmt = $pdo->prepare("INSERT INTO team_requirements (team_id, requirement_id, status, feedback, feedback_file, submitted_at) VALUES (?, ?, ?, ?, ?, NULL)");
    $updateWithFileStmt = $pdo->prepare("UPDATE team_requirements SET status = ?, feedback = ?, feedback_file = ? WHERE team_id = ? AND requirement_id = ?");
    $updateWithoutFileStmt = $pdo->prepare("UPDATE team_requirements SET status = ?, feedback = ? WHERE team_id = ? AND requirement_id = ?");

    $uploadDir = __DIR__ . '/../feedback/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $pdo->beginTransaction();

    foreach ($requirementIds as $requirementId) {
        $status = isset($statusMap[$requirementId]) ? trim((string)$statusMap[$requirementId]) : 'pending';
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'pending';
        }

        $feedback = isset($_POST['feedback'][$requirementId]) ? trim((string)$_POST['feedback'][$requirementId]) : '';

        $feedbackFile = $_FILES['feedbackFile']['name'][$requirementId] ?? '';
        $newFileName = null;

        if (!empty($feedbackFile) && !empty($_FILES['feedbackFile']['tmp_name'][$requirementId])) {
            $reqNameStmt->execute([$requirementId]);
            $requirementName = $reqNameStmt->fetchColumn() ?: ('requirement-' . $requirementId);

            $extension = pathinfo($feedbackFile, PATHINFO_EXTENSION);
            $safeTeam = preg_replace('/[^A-Za-z0-9\-_]/', '_', $teamName);
            $safeReq = preg_replace('/[^A-Za-z0-9\-_]/', '_', $requirementName);
            $newFileName = 'feedback-' . $safeTeam . '-' . $safeReq . '-' . date('Ymd-His') . '-' . $requirementId . '.' . $extension;
            $uploadFilePath = $uploadDir . $newFileName;

            if (!move_uploaded_file($_FILES['feedbackFile']['tmp_name'][$requirementId], $uploadFilePath)) {
                throw new Exception('Failed to upload feedback file for requirement ID ' . $requirementId);
            }
        }

        $existingStmt->execute([$targetTeamId, $requirementId]);
        $exists = $existingStmt->fetchColumn();

        if ($exists) {
            if ($newFileName !== null) {
                $updateWithFileStmt->execute([$status, $feedback, $newFileName, $targetTeamId, $requirementId]);
            } else {
                $updateWithoutFileStmt->execute([$status, $feedback, $targetTeamId, $requirementId]);
            }
        } else {
            $insertStmt->execute([$targetTeamId, $requirementId, $status, $feedback, $newFileName]);
        }

        $updatedCount++;

        if ($feedback !== '') {
            try {
                require_once dirname(__DIR__, 2) . '/assets/includes/notification_functions.php';
                createRequirementFeedbackNotifications($pdo, $targetTeamId, $requirementId, $feedback);
            } catch (Exception $notifException) {
                error_log('Failed to create requirement feedback notification: ' . $notifException->getMessage());
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Requirements updated successfully.',
        'updated_count' => $updatedCount,
        'team_id' => $targetTeamId,
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>