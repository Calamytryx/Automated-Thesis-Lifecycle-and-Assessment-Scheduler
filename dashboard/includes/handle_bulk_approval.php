<?php
/**
 * Handle bulk chair approval/rejection of defense schedules.
 * Accepts an array of schedule objects (possibly with edits) and an action (approve/reject).
 * On approve: updates each schedule's fields, changes status to 'approved', auto-accepts panelist assignments, and sends notice notifications.
 * On reject: changes status to 'rejected'.
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../../assets/includes/notification_functions.php';
require_once __DIR__ . '/edit_functions.php';
require_once __DIR__ . '/section_access.php';
$conflictItems = [];

try {
    // Authorization: Use shared dashboard access rules for program chairs and section professors.
    $usertype = intval($_SESSION['usertype'] ?? -1);
    $userId = intval($_SESSION['id'] ?? -1);

    if (!userCanAccessDashboard($pdo, $userId, $usertype)) {
        error_log("handle_bulk_approval.php: Unauthorized - userId=$userId, usertype=$usertype");
        throw new Exception('Unauthorized: You do not have permission to perform bulk approval.');
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

    $supportsFinalization = defenseScheduleColumnExists($pdo, 'is_finalized');

    if (empty($schedules)) {
        throw new Exception('No schedules selected.');
    }

    // Scope-check all schedules up front so bulk actions match table visibility rules.
    $scopeCheckStmt = $pdo->prepare("SELECT team_id FROM defense_schedules WHERE id = ? LIMIT 1");
    foreach ($schedules as $sched) {
        $scheduleId = isset($sched['id']) ? (int)$sched['id'] : 0;
        if ($scheduleId <= 0) {
            throw new Exception('Invalid schedule ID in bulk request.');
        }

        $scopeCheckStmt->execute([$scheduleId]);
        $teamId = (int)$scopeCheckStmt->fetchColumn();
        if ($teamId <= 0) {
            throw new Exception('Schedule ID ' . $scheduleId . ' was not found.');
        }

        if (!canUserAccessDefenseScheduleByTeam($pdo, $userId, $usertype, $teamId)) {
            throw new Exception('You do not have access to schedule ID ' . $scheduleId . '.');
        }
    }

    $pdo->beginTransaction();

    $processedCount = 0;

    if ($action === 'approve') {
            // Pre-validate all schedules for student conflicts before approving/finalizing.
            $currentScheduleStmt = $pdo->prepare("
                SELECT ds.team_id, ds.schedule_date, ds.start_time, ds.end_time,
                       ds.panelist_id, ds.panelist_id2, ds.panelist_id3, t.name AS team_name
                FROM defense_schedules ds
                LEFT JOIN teams t ON t.id = ds.team_id
                WHERE ds.id = ?
                LIMIT 1
            ");

            $normalizeTime = static function ($timeValue) {
                $time = trim((string)$timeValue);
                if ($time === '') {
                    return '';
                }
                if (strlen($time) === 5) {
                    return $time . ':00';
                }
                return substr($time, 0, 8);
            };

            $proposedSchedules = [];

            foreach ($schedules as $sched) {
                if (empty($sched['id'])) {
                    continue;
                }

                $currentScheduleStmt->execute([$sched['id']]);
                $currentSchedule = $currentScheduleStmt->fetch(PDO::FETCH_ASSOC);
                if (!$currentSchedule) {
                    $conflictItems[] = 'Schedule ID ' . (int)$sched['id'] . ' was not found.';
                    continue;
                }

                $teamId = isset($sched['team_id']) && (int)$sched['team_id'] > 0
                    ? (int)$sched['team_id']
                    : (int)$currentSchedule['team_id'];
                $scheduleDate = $sched['schedule_date'] ?? $currentSchedule['schedule_date'];
                $startTime = $normalizeTime($sched['start_time'] ?? $currentSchedule['start_time']);
                $endTime = $normalizeTime($sched['end_time'] ?? $currentSchedule['end_time']);

                $panelistsBulk = array_values(array_filter(array_map('intval', [
                    (int) ($sched['panelist_id'] ?? $currentSchedule['panelist_id'] ?? 0),
                    (int) ($sched['panelist_id2'] ?? $currentSchedule['panelist_id2'] ?? 0),
                    (int) ($sched['panelist_id3'] ?? $currentSchedule['panelist_id3'] ?? 0),
                ])));

                $conflictCheck = validateStudentScheduleConflicts(
                    $pdo,
                    $teamId,
                    $scheduleDate,
                    $startTime,
                    $endTime,
                    (int)$sched['id'],
                    $panelistsBulk
                );

                if (!$conflictCheck['ok']) {
                    $conflictItems[] = $conflictCheck['message'];
                }

                $teamName = trim((string)($currentSchedule['team_name'] ?? ''));
                if ($teamName === '') {
                    $teamName = 'Team ' . $teamId;
                }

                $proposedSchedules[] = [
                    'id' => (int)$sched['id'],
                    'team_name' => $teamName,
                    'schedule_date' => (string)$scheduleDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'student_ids' => array_map('intval', getTeamStudentIds($pdo, (int)$teamId))
                ];
            }

            $proposedCount = count($proposedSchedules);
            for ($i = 0; $i < $proposedCount; $i++) {
                for ($j = $i + 1; $j < $proposedCount; $j++) {
                    $a = $proposedSchedules[$i];
                    $b = $proposedSchedules[$j];

                    if ($a['schedule_date'] !== $b['schedule_date']) {
                        continue;
                    }
                    if ($a['start_time'] >= $b['end_time'] || $a['end_time'] <= $b['start_time']) {
                        continue;
                    }
                    if (empty(array_intersect($a['student_ids'], $b['student_ids']))) {
                        continue;
                    }

                    $conflictItems[] = $a['team_name'] . ' schedule conflict with: ' . $b['team_name'] .
                        ' on ' . $a['schedule_date'] .
                        ' (' . substr($a['start_time'], 0, 5) . '-' . substr($a['end_time'], 0, 5) .
                        ' and ' . substr($b['start_time'], 0, 5) . '-' . substr($b['end_time'], 0, 5) . ').';
                }
            }

            if (!empty($conflictItems)) {
                $conflictItems = array_values(array_unique($conflictItems));
                throw new Exception('Cannot approve schedules with conflicts. Please fix the teams listed below first.');
            }

        // Update statement for editable fields
        if ($supportsFinalization) {
            $updateStmt = $pdo->prepare("
                UPDATE defense_schedules 
                SET schedule_date = ?, start_time = ?, end_time = ?, room = ?,
                    panelist_id = ?, panelist_id2 = ?, panelist_id3 = ?,
                    approval_status = 'approved',
                    is_finalized = 1, finalized_by = ?, finalized_at = NOW()
                WHERE id = ? AND approval_status = 'pending_chair'
            ");
        } else {
            $updateStmt = $pdo->prepare("
                UPDATE defense_schedules 
                SET schedule_date = ?, start_time = ?, end_time = ?, room = ?,
                    panelist_id = ?, panelist_id2 = ?, panelist_id3 = ?,
                    approval_status = 'approved'
                WHERE id = ? AND approval_status = 'pending_chair'
            ");
        }

        // Create panelist approval records
        $approvalStmt = $pdo->prepare("
            INSERT INTO panelist_approvals (defense_schedule_id, panelist_id, approval_status, response_date, created_at)
            VALUES (?, ?, 'approved', NOW(), NOW())
        ");

        foreach ($schedules as $sched) {
            if (empty($sched['id'])) continue;

            // Update the schedule with any edits + change status
            if ($supportsFinalization) {
                $updateStmt->execute([
                    $sched['schedule_date'] ?? null,
                    $sched['start_time'] ?? null,
                    $sched['end_time'] ?? null,
                    $sched['room'] ?? null,
                    $sched['panelist_id'] ?? null,
                    $sched['panelist_id2'] ?? null,
                    $sched['panelist_id3'] ?? null,
                    $userId,
                    $sched['id']
                ]);
            } else {
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
            }

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
                        $panelistIds,
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
                WHERE type IN ('defense_approval', 'defense_scheduled') AND related_id IN ($placeholders)
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
                WHERE type IN ('defense_approval', 'defense_scheduled') AND related_id IN ($placeholders)
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
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];

    if (!empty($conflictItems)) {
        $numberedLines = [];
        foreach ($conflictItems as $idx => $line) {
            $numberedLines[] = ($idx + 1) . '. ' . $line;
        }
        $response['message'] .= "\n" . implode("\n", $numberedLines);
        $response['conflict_items'] = $conflictItems;
    }

    echo json_encode($response);
}
