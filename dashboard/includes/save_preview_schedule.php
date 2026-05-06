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

    $normalizedSchedules = [];
    $conflictItems = [];
    foreach ($schedules as $index => $sched) {
        if (empty($sched['team_id']) || empty($sched['schedule_date']) || empty($sched['start_time']) || empty($sched['end_time']) || empty($sched['room'])) {
            $conflictItems[] = 'Row ' . ($index + 1) . ': team/date/time/room are required.';
            continue;
        }
        $teamId = (int)$sched['team_id'];
        if (!canUserAccessDefenseScheduleByTeam($pdo, $userId, $usertype, $teamId)) {
            $conflictItems[] = 'Team ' . $teamId . ': no access.';
            continue;
        }
        $normalizedSchedules[] = [
            'team_id' => $teamId,
            'panelist_id' => isset($sched['panelist_id']) ? (int) $sched['panelist_id'] : null,
            'panelist_id2' => isset($sched['panelist_id2']) ? (int) $sched['panelist_id2'] : null,
            'panelist_id3' => isset($sched['panelist_id3']) ? (int) $sched['panelist_id3'] : null,
            'schedule_date' => $sched['schedule_date'],
            'start_time' => $sched['start_time'],
            'end_time' => $sched['end_time'],
            'room' => trim((string) ($sched['room'] ?? '')),
            'defense_type' => $sched['defense_type'] ?? 'title_proposal'
        ];
    }
    if (!empty($conflictItems)) {
        $conflictItems = array_values(array_unique($conflictItems));
        throw new Exception(implode("\n", $conflictItems));
    }

    // Validate overlaps inside the incoming preview batch itself
    // (not only against already-saved defense schedules).
    $batchCount = count($normalizedSchedules);
    for ($i = 0; $i < $batchCount; $i++) {
        $a = $normalizedSchedules[$i];
        $aDate = trim((string) ($a['schedule_date'] ?? ''));
        $aStartTs = strtotime($aDate . ' ' . trim((string) ($a['start_time'] ?? '')));
        $aEndTs = strtotime($aDate . ' ' . trim((string) ($a['end_time'] ?? '')));
        $aRoomNorm = strtolower(trim((string) ($a['room'] ?? '')));
        $aPanelists = array_values(array_unique(array_filter([
            (int) ($a['panelist_id'] ?? 0),
            (int) ($a['panelist_id2'] ?? 0),
            (int) ($a['panelist_id3'] ?? 0),
        ])));

        if ($aStartTs === false || $aEndTs === false || $aStartTs >= $aEndTs) {
            $conflictItems[] = 'Row ' . ($i + 1) . ': invalid time range.';
            continue;
        }

        for ($j = $i + 1; $j < $batchCount; $j++) {
            $b = $normalizedSchedules[$j];
            $bDate = trim((string) ($b['schedule_date'] ?? ''));
            if ($aDate !== $bDate) {
                continue;
            }

            $bStartTs = strtotime($bDate . ' ' . trim((string) ($b['start_time'] ?? '')));
            $bEndTs = strtotime($bDate . ' ' . trim((string) ($b['end_time'] ?? '')));
            if ($bStartTs === false || $bEndTs === false) {
                $conflictItems[] = 'Row ' . ($j + 1) . ': invalid time range.';
                continue;
            }

            // True overlap test: catches 1:00-3:00 vs 2:00-3:00.
            $overlaps = ($aStartTs < $bEndTs) && ($aEndTs > $bStartTs);
            if (!$overlaps) {
                continue;
            }

            $bRoomNorm = strtolower(trim((string) ($b['room'] ?? '')));
            if ($aRoomNorm !== '' && $bRoomNorm !== '' && $aRoomNorm === $bRoomNorm) {
                $conflictItems[] = sprintf(
                    'Rows %d and %d: room conflict (%s) with overlapping time on %s.',
                    $i + 1,
                    $j + 1,
                    $a['room'],
                    $aDate
                );
            }

            $bPanelists = array_values(array_unique(array_filter([
                (int) ($b['panelist_id'] ?? 0),
                (int) ($b['panelist_id2'] ?? 0),
                (int) ($b['panelist_id3'] ?? 0),
            ])));
            $sharedPanelists = array_values(array_intersect($aPanelists, $bPanelists));
            if (!empty($sharedPanelists)) {
                $conflictItems[] = sprintf(
                    'Rows %d and %d: panelist conflict (ID %s) with overlapping time on %s.',
                    $i + 1,
                    $j + 1,
                    implode(', ', $sharedPanelists),
                    $aDate
                );
            }
        }
    }

    foreach ($normalizedSchedules as $sched) {
        $panelistIds = array_values(array_filter([
            (int) ($sched['panelist_id'] ?? 0),
            (int) ($sched['panelist_id2'] ?? 0),
            (int) ($sched['panelist_id3'] ?? 0),
        ]));
        $conflictCheck = validateStudentScheduleConflicts(
            $pdo,
            (int) $sched['team_id'],
            $sched['schedule_date'],
            $sched['start_time'],
            $sched['end_time'],
            null,
            $panelistIds,
            $sched['room']
        );
        if (!$conflictCheck['ok']) {
            $conflictItems = array_merge($conflictItems, (array) ($conflictCheck['conflict_items'] ?? [$conflictCheck['message']]));
        }
    }
    if (!empty($conflictItems)) {
        $conflictItems = array_values(array_unique($conflictItems));
        throw new Exception(implode("\n", $conflictItems));
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO defense_schedules 
        (team_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, defense_type, status, approval_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', 'pending_chair')
    ");

    $savedCount = 0;
    $savedIds = [];

    foreach ($normalizedSchedules as $sched) {
        $stmt->execute([
            (int) ($sched['team_id'] ?? 0),
            $sched['panelist_id'],
            $sched['panelist_id2'],
            $sched['panelist_id3'],
            $sched['schedule_date'],
            $sched['start_time'],
            $sched['end_time'],
            $sched['room'],
            $sched['defense_type']
        ]);

        $scheduleId = $pdo->lastInsertId();
        $savedIds[] = $scheduleId;

        createChairReviewNotifications(
            $pdo,
            $scheduleId,
            $sched['team_id'],
            $sched['schedule_date'],
            substr($sched['start_time'], 0, 5),
            substr($sched['end_time'], 0, 5),
            $sched['room']
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
    $errorMessage = $e->getMessage();
    $items = array_values(array_filter(array_map('trim', explode("\n", $errorMessage))));
    echo json_encode([
        'success' => false,
        'message' => $errorMessage,
        'conflict_items' => $items
    ]);
}
