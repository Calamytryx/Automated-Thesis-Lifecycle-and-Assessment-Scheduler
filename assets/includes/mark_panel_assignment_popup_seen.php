<?php
session_start();
require_once __DIR__ . '/../setup/db.inc.php';
require_once __DIR__ . '/notification_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$userId = (int)$_SESSION['id'];
$scheduleId = isset($input['schedule_id']) ? (int)$input['schedule_id'] : 0;
$notificationId = isset($input['notification_id']) ? (int)$input['notification_id'] : 0;

if ($scheduleId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid schedule_id'
    ]);
    exit;
}

try {
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

    $savePopupState = $pdo->prepare(" 
        INSERT INTO panel_assignment_popup_state
            (user_id, schedule_id, notification_id, shown_at, created_at, updated_at)
        VALUES
            (?, ?, ?, NOW(), NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            notification_id = IFNULL(VALUES(notification_id), notification_id),
            shown_at = NOW(),
            updated_at = NOW()
    ");
    $savePopupState->execute([
        $userId,
        $scheduleId,
        $notificationId > 0 ? $notificationId : null
    ]);

    if ($notificationId > 0) {
        markAsRead($notificationId, $userId);
    } else {
        $markScheduleNotifs = $pdo->prepare(" 
            UPDATE notifications
            SET is_read = 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?
              AND related_id = ?
              AND type IN ('defense_notice', 'defense_approval', 'defense_schedule', 'defense_scheduled')
              AND is_read = 0
        ");
        $markScheduleNotifs->execute([$userId, $scheduleId]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Panel assignment popup state saved.'
    ]);
} catch (Exception $e) {
    error_log('mark_panel_assignment_popup_seen.php ERROR: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save popup state'
    ]);
}
