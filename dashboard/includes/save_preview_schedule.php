<?php
/**
 * Save preview schedule data to database.
 * Receives the (possibly edited) schedule array from the preview calendar
 * and inserts into defense_schedules with pending_chair status.
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../../assets/includes/notification_functions.php';
require_once __DIR__ . '/edit_functions.php';
require_once __DIR__ . '/section_access.php';

try {
    // Allow users that pass shared dashboard defense access rules.
    $usertype = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : -1;
    $userId = isset($_SESSION['id']) ? intval($_SESSION['id']) : -1;

    if (!userCanAccessDashboard($pdo, $userId, $usertype)) {
        throw new Exception('Unauthorized: Only administrators and subject teachers can save schedules.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['schedules']) || !is_array($input['schedules'])) {
        throw new Exception('Invalid input: schedules array required.');
    }

    $schedules = $input['schedules'];
    if (empty($schedules)) {
        throw new Exception('No schedules to save.');
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO defense_schedules 
        (team_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, defense_type, status, approval_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', 'pending_chair')
    ");

    $savedCount = 0;
    $savedIds = [];

    foreach ($schedules as $sched) {
        // Validate required fields
        if (empty($sched['team_id']) || empty($sched['schedule_date']) || empty($sched['start_time']) || empty($sched['end_time'])) {
            continue;
        }

        $teamId = (int)$sched['team_id'];
        if (!canUserAccessDefenseScheduleByTeam($pdo, $userId, $usertype, $teamId)) {
            throw new Exception('Team ' . $teamId . ': no access.');
        }

        $panelistIds = array_values(array_filter([
            isset($sched['panelist_id']) ? (int) $sched['panelist_id'] : 0,
            isset($sched['panelist_id2']) ? (int) $sched['panelist_id2'] : 0,
            isset($sched['panelist_id3']) ? (int) $sched['panelist_id3'] : 0,
        ]));

        $conflictCheck = validateStudentScheduleConflicts(
            $pdo,
            $teamId,
            $sched['schedule_date'],
            $sched['start_time'],
            $sched['end_time'],
            null,
            $panelistIds
        );
        if (!$conflictCheck['ok']) {
            throw new Exception($conflictCheck['message']);
        }

        $stmt->execute([
            $teamId,
            $sched['panelist_id'] ?? null,
            $sched['panelist_id2'] ?? null,
            $sched['panelist_id3'] ?? null,
            $sched['schedule_date'],
            $sched['start_time'],
            $sched['end_time'],
            $sched['room'] ?? '',
            $sched['defense_type'] ?? 'title_proposal'
        ]);

        $scheduleId = $pdo->lastInsertId();
        $savedIds[] = $scheduleId;

        // Create chair review notifications
        createChairReviewNotifications(
            $pdo,
            $scheduleId,
            $sched['team_id'],
            $sched['schedule_date'],
            substr($sched['start_time'], 0, 5),
            substr($sched['end_time'], 0, 5),
            $sched['room'] ?? ''
        );

        $savedCount++;
    }

    if ($savedCount === 0) {
        throw new Exception('No schedules were saved. Please check team access and conflict details.');
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully saved {$savedCount} defense schedule(s). Awaiting chair review.",
        'saved_count' => $savedCount,
        'saved_ids' => $savedIds
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("save_preview_schedule.php ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
