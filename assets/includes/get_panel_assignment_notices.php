<?php
session_start();
require_once __DIR__ . '/../setup/db.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in',
        'assignments' => []
    ]);
    exit;
}

$userId = (int)$_SESSION['id'];
$userType = (int)($_SESSION['usertype'] ?? -1);

// Panel assignments are only relevant to faculty/admin accounts.
if (!in_array($userType, [0, 2], true)) {
    echo json_encode([
        'success' => true,
        'assignments' => [],
        'count' => 0
    ]);
    exit;
}

function ensurePanelAssignmentPopupStateTable(PDO $pdo): void {
    $pdo->exec(" 
        CREATE TABLE IF NOT EXISTS panel_assignment_popup_state (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id INT UNSIGNED NOT NULL,
            schedule_id INT UNSIGNED NOT NULL,
            notification_id INT UNSIGNED DEFAULT NULL,
            shown_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user_schedule (user_id, schedule_id),
            KEY idx_user_shown (user_id, shown_at),
            KEY idx_schedule (schedule_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
}

try {
    // Track popup display separately from notifications.is_read so legacy inbox data
    // cannot suppress assignment modals.
    ensurePanelAssignmentPopupStateTable($pdo);

    $stmt = $pdo->prepare("
        SELECT
            ds.id AS schedule_id,
            ds.schedule_date,
            ds.start_time,
            ds.end_time,
            ds.room,
            ds.defense_type,
            t.name AS team_name,
            t.program,
            (
                SELECT rt.title
                FROM research_titles rt
                WHERE rt.team_id = t.id
                ORDER BY rt.id DESC
                LIMIT 1
            ) AS research_title,
            p1.id AS p1_id,
            p1.first_name AS p1_first_name,
            p1.last_name AS p1_last_name,
            p2.id AS p2_id,
            p2.first_name AS p2_first_name,
            p2.last_name AS p2_last_name,
            p3.id AS p3_id,
            p3.first_name AS p3_first_name,
            p3.last_name AS p3_last_name,
            n.id AS notification_id,
            n.type AS notification_type,
            n.title,
            n.message,
            n.created_at AS notification_created_at
        FROM defense_schedules ds
        LEFT JOIN teams t ON t.id = ds.team_id
        LEFT JOIN users p1 ON p1.id = ds.panelist_id
        LEFT JOIN users p2 ON p2.id = ds.panelist_id2
        LEFT JOIN users p3 ON p3.id = ds.panelist_id3
        LEFT JOIN notifications n ON n.id = (
            SELECT MAX(n2.id)
            FROM notifications n2
            WHERE n2.user_id = ?
              AND n2.related_id = ds.id
              AND n2.type IN ('defense_notice', 'defense_approval', 'defense_schedule', 'defense_scheduled')
        )
        LEFT JOIN panel_assignment_popup_state ps ON ps.user_id = ? AND ps.schedule_id = ds.id
        WHERE ds.approval_status = 'approved'
          AND ds.schedule_date IS NOT NULL
          AND ds.start_time IS NOT NULL
          AND ds.end_time IS NOT NULL
          AND TIMESTAMP(ds.schedule_date, ds.end_time) >= NOW()
          AND ? IN (ds.panelist_id, ds.panelist_id2, ds.panelist_id3)
          AND ps.id IS NULL
        ORDER BY ds.schedule_date ASC, ds.start_time ASC, ds.id ASC
        LIMIT 25
    ");

    $stmt->execute([$userId, $userId, $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $assignments = [];

    foreach ($rows as $row) {
        $formattedDate = '';
        if (!empty($row['schedule_date']) && $row['schedule_date'] !== '0000-00-00') {
            $formattedDate = date('F j, Y', strtotime($row['schedule_date']));
        }

        $formattedTime = '';
        if (!empty($row['start_time']) && !empty($row['end_time'])) {
            $formattedTime = date('g:i A', strtotime($row['start_time'])) . ' - ' . date('g:i A', strtotime($row['end_time']));
        }

        $otherPanelists = [];
        $panelColumns = [
            ['id' => 'p1_id', 'first' => 'p1_first_name', 'last' => 'p1_last_name'],
            ['id' => 'p2_id', 'first' => 'p2_first_name', 'last' => 'p2_last_name'],
            ['id' => 'p3_id', 'first' => 'p3_first_name', 'last' => 'p3_last_name']
        ];

        foreach ($panelColumns as $panelColumn) {
            $panelId = isset($row[$panelColumn['id']]) ? (int)$row[$panelColumn['id']] : 0;
            if ($panelId <= 0 || $panelId === $userId) {
                continue;
            }

            $firstName = trim((string)($row[$panelColumn['first']] ?? ''));
            $lastName = trim((string)($row[$panelColumn['last']] ?? ''));
            $fullName = trim($firstName . ' ' . $lastName);

            if ($fullName !== '') {
                $otherPanelists[] = $fullName;
            }
        }

        $otherPanelistsText = !empty($otherPanelists)
            ? implode(', ', array_unique($otherPanelists))
            : 'No additional panelists listed';

        $fallbackTitle = 'Defense Panel Assignment Notice';
        $fallbackMessage = 'You have a new approved defense panel assignment.';

        $assignments[] = [
            'notification_id' => isset($row['notification_id']) ? (int)$row['notification_id'] : 0,
            'notification_type' => (string)($row['notification_type'] ?? ''),
            'title' => (string)($row['title'] ?? $fallbackTitle),
            'message' => (string)($row['message'] ?? $fallbackMessage),
            'schedule_id' => isset($row['schedule_id']) ? (int)$row['schedule_id'] : 0,
            'team_name' => (string)($row['team_name'] ?? 'Unknown Team'),
            'program' => (string)($row['program'] ?? ''),
            'research_title' => (string)($row['research_title'] ?? ''),
            'room' => trim((string)($row['room'] ?? '')),
            'defense_type' => (string)($row['defense_type'] ?? ''),
            'formatted_date' => $formattedDate,
            'formatted_time' => $formattedTime,
            'other_panelists' => $otherPanelistsText,
            'created_at' => (string)($row['notification_created_at'] ?? '')
        ];
    }

    echo json_encode([
        'success' => true,
        'assignments' => $assignments,
        'count' => count($assignments)
    ]);
} catch (Exception $e) {
    error_log('get_panel_assignment_notices.php ERROR: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load panel assignments',
        'assignments' => []
    ]);
}
