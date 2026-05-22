<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../assets/setup/db.inc.php';

$response = ['success' => false, 'message' => 'Invalid request.'];

// Check if user is authenticated and is a student (leader)
if (!isset($_SESSION['auth']) || $_SESSION['usertype'] != 1) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requirementId = filter_input(INPUT_POST, 'requirement_id', FILTER_VALIDATE_INT);
    $requestedFileName = isset($_POST['file_name']) ? basename(trim((string)$_POST['file_name'])) : '';

    // Get team ID from session
    $teamIdArray = isset($_SESSION['team_id']) ? (array)$_SESSION['team_id'] : [];
    $teamId = $teamIdArray[0] ?? null;

    if (!$requirementId || !$teamId) {
        $response['message'] = 'Invalid requirement or team ID.';
        echo json_encode($response);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Ensure there is a pending row so reverted/open submissions remain editable.
        $pendingStmt = $pdo->prepare("
            SELECT id, file_name, submitted_at
            FROM team_requirements
            WHERE team_id = ? AND requirement_id = ? AND status = 'pending'
            LIMIT 1
        ");
        $pendingStmt->execute([$teamId, $requirementId]);
        $pendingRow = $pendingStmt->fetch(PDO::FETCH_ASSOC);

        if (!$pendingRow) {
            $response['message'] = 'No pending submission found to remove.';
            $pdo->rollBack();
            echo json_encode($response);
            exit;
        }

        $currentMainFile = (string)($pendingRow['file_name'] ?? '');
        $fileName = $requestedFileName !== '' ? $requestedFileName : $currentMainFile;

        if ($fileName === '') {
            $response['message'] = 'No file selected to remove.';
            $pdo->rollBack();
            echo json_encode($response);
            exit;
        }

        $historyTableAvailable = true;
        $removedFromHistory = false;
        $latestRemaining = null;

        // Remove selected submission entry from multi-submission table when available.
        // This prevents the table from growing with removed reverted files.
        try {
            $deleteHistoryStmt = $pdo->prepare("
                DELETE FROM team_requirement_files
                WHERE team_id = ? AND requirement_id = ? AND file_name = ? AND deleted_at IS NULL
            ");
            $deleteHistoryStmt->execute([$teamId, $requirementId, $fileName]);
            $removedFromHistory = $deleteHistoryStmt->rowCount() > 0;

            $latestStmt = $pdo->prepare("
                SELECT file_name, submitted_at
                FROM team_requirement_files
                WHERE team_id = ? AND requirement_id = ? AND deleted_at IS NULL
                ORDER BY submission_number DESC, submitted_at DESC, id DESC
                LIMIT 1
            ");
            $latestStmt->execute([$teamId, $requirementId]);
            $latestRemaining = $latestStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $historyEx) {
            $historyTableAvailable = false;
            error_log('History table handling skipped in remove_file.php: ' . $historyEx->getMessage());
        }

        // If a specific file was requested but not found, prevent deleting unrelated records.
        if ($requestedFileName !== '' && !$removedFromHistory && $requestedFileName !== $currentMainFile) {
            $response['message'] = 'Selected submission was not found.';
            $pdo->rollBack();
            echo json_encode($response);
            exit;
        }

        $filePath = __DIR__ . '/../../assets/uploads/submission/' . $fileName;

        // Delete the physical file.
        if (!empty($fileName) && file_exists($filePath)) {
            if (!unlink($filePath)) {
                $response['message'] = 'Failed to delete the file from storage.';
                $pdo->rollBack();
                echo json_encode($response);
                exit;
            }
        }

        // Keep the pending row (reverted/open state). Deleting the row can trigger auto-close.
        if ($historyTableAvailable && $latestRemaining) {
            $updateStmt = $pdo->prepare("
                UPDATE team_requirements
                SET file_name = ?, submitted_at = ?
                WHERE id = ?
            ");
            $updateStmt->execute([
                $latestRemaining['file_name'],
                $latestRemaining['submitted_at'],
                (int)$pendingRow['id']
            ]);
        } else {
            $updateStmt = $pdo->prepare("
                UPDATE team_requirements
                SET file_name = NULL, submitted_at = NULL
                WHERE id = ?
            ");
            $updateStmt->execute([(int)$pendingRow['id']]);
        }

        $pdo->commit();

        $response['success'] = true;
        $response['message'] = 'File removed successfully. You can now upload a new file.';

        error_log("File removed: Team ID {$teamId}, Requirement ID {$requirementId}, File: {$fileName} by User ID " . $_SESSION['id']);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Remove file error: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>