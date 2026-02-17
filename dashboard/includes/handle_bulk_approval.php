<?php
/**
 * Handle bulk chair approval/rejection of defense schedules.
 * Accepts an array of schedule objects (possibly with edits) and an action (approve/reject).
 * On approve: updates each schedule's fields, changes status to 'pending', creates panelist approvals & notifications.
 * On reject: changes status to 'rejected'.
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../../assets/includes/notification_functions.php';

try {
    if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != 0) {
        throw new Exception('Unauthorized: Only administrators can perform bulk approval.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['action']) || !isset($input['schedules'])) {
        throw new Exception('Invalid input: action and schedules required.');
    }

    $action = $input['action']; // 'approve' or 'reject'
    $schedules = $input['schedules'];
    $rejectionReason = $input['rejection_reason'] ?? '';

    if (!in_array($action, ['approve', 'reject'])) {
        throw new Exception('Invalid action. Must be approve or reject.');
    }

    if (empty($schedules)) {
        throw new Exception('No schedules selected.');
    }

    $pdo->beginTransaction();

    $processedCount = 0;

    if ($action === 'approve') {
        // Update statement for editable fields
        $updateStmt = $pdo->prepare("
            UPDATE defense_schedules 
            SET schedule_date = ?, start_time = ?, end_time = ?, room = ?,
                panelist_id = ?, panelist_id2 = ?, panelist_id3 = ?,
                approval_status = 'pending'
            WHERE id = ? AND approval_status = 'pending_chair'
        ");

        // Create panelist approval records
        $approvalStmt = $pdo->prepare("
            INSERT INTO panelist_approvals (defense_schedule_id, panelist_id, approval_status, created_at)
            VALUES (?, ?, 'pending', NOW())
        ");

        foreach ($schedules as $sched) {
            if (empty($sched['id'])) continue;

            // Update the schedule with any edits + change status
            $updateStmt->execute([
                $sched['schedule_date'] ?? null,
                $sched['start_time'] ?? null,
                $sched['end_time'] ?? null,
                $sched['room'] ?? null,
                $sched['panelist_id'] ?? null,
                $sched['panelist_id2'] ?? null,
                $sched['panelist_id3'] ?? null,
                $sched['id']
            ]);

            if ($updateStmt->rowCount() > 0) {
                // Create panelist approval records
                $panelistIds = array_filter([
                    $sched['panelist_id'] ?? null,
                    $sched['panelist_id2'] ?? null,
                    $sched['panelist_id3'] ?? null
                ]);
                foreach ($panelistIds as $panelistId) {
                    $approvalStmt->execute([$sched['id'], $panelistId]);
                }

                // Get fresh schedule data for notifications
                $freshStmt = $pdo->prepare("SELECT ds.*, t.name AS team_name FROM defense_schedules ds JOIN teams t ON ds.team_id = t.id WHERE ds.id = ?");
                $freshStmt->execute([$sched['id']]);
                $freshSched = $freshStmt->fetch(PDO::FETCH_ASSOC);

                if ($freshSched) {
                    // Notify panelists
                    createDefenseApprovalNotifications(
                        $pdo,
                        $sched['id'],
                        $freshSched['team_id'],
                        $freshSched['schedule_date'],
                        substr($freshSched['start_time'], 0, 5),
                        substr($freshSched['end_time'], 0, 5),
                        $freshSched['room']
                    );

                    // Notify team members
                    $teamMembersStmt = $pdo->prepare("SELECT user_id FROM team_members WHERE team_id = ?");
                    $teamMembersStmt->execute([$freshSched['team_id']]);
                    $teamMembers = $teamMembersStmt->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($teamMembers as $memberId) {
                        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, created_at) VALUES (?, 'defense_schedule', ?, NOW())");
                        $notifStmt->execute([
                            $memberId,
                            "Your defense has been scheduled on {$freshSched['schedule_date']} from " .
                            substr($freshSched['start_time'], 0, 5) . " to " . substr($freshSched['end_time'], 0, 5) .
                            " in {$freshSched['room']}."
                        ]);
                    }
                }

                $processedCount++;
            }
        }

        // Mark chair_review notifications as read
        $scheduleIds = array_filter(array_column($schedules, 'id'));
        if (!empty($scheduleIds)) {
            $placeholders = implode(',', array_fill(0, count($scheduleIds), '?'));
            $markReadStmt = $pdo->prepare("
                UPDATE notifications SET is_read = 1 
                WHERE type = 'chair_review' AND JSON_EXTRACT(data, '$.schedule_id') IN ($placeholders)
            ");
            $markReadStmt->execute($scheduleIds);
        }

    } else { // reject
        $rejectStmt = $pdo->prepare("
            UPDATE defense_schedules SET approval_status = 'rejected' 
            WHERE id = ? AND approval_status = 'pending_chair'
        ");

        foreach ($schedules as $sched) {
            if (empty($sched['id'])) continue;
            $rejectStmt->execute([$sched['id']]);
            if ($rejectStmt->rowCount() > 0) {
                $processedCount++;
            }
        }

        // Mark chair_review notifications as read
        $scheduleIds = array_filter(array_column($schedules, 'id'));
        if (!empty($scheduleIds)) {
            $placeholders = implode(',', array_fill(0, count($scheduleIds), '?'));
            $markReadStmt = $pdo->prepare("
                UPDATE notifications SET is_read = 1 
                WHERE type = 'chair_review' AND JSON_EXTRACT(data, '$.schedule_id') IN ($placeholders)
            ");
            $markReadStmt->execute($scheduleIds);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => ucfirst($action) . "d {$processedCount} defense schedule(s) successfully.",
        'processed_count' => $processedCount
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("handle_bulk_approval.php ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
