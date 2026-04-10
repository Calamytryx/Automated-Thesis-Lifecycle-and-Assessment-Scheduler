<?php
/**
 * Handle program chair approval/rejection of defense schedules.
 * 
 * When chair approves:
 *   - approval_status changes from 'pending_chair' to 'approved'
 *   - Panelist assignments are auto-accepted
 *   - Notice notifications are sent (no accept/reject workflow)
 *   - Team members are notified that schedule is finalized
 * 
 * When chair rejects:
 *   - approval_status changes to 'rejected'
 *   - Admin/coordinator is notified
 */
session_start();
require_once '../setup/db.inc.php';
require_once 'notification_functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';
$scheduleId = $_POST['schedule_id'] ?? '';
$userId = $_SESSION['id'] ?? '';
$rejectionReason = $_POST['rejection_reason'] ?? '';

// Validate user is a program chair, admin, or faculty from same college
$usertype = intval($_SESSION['usertype'] ?? -1);
$userId = intval($_SESSION['id'] ?? -1);
$isAdmin = ($usertype === 0);
$isAuthorizedFaculty = false;

if ($usertype === 2 && $userId > 0) {
    require_once __DIR__ . '/../../dashboard/includes/section_access.php';
    $sections = getProfessorSections($pdo, $userId);
    if (!empty($sections)) {
        $isAuthorizedFaculty = true;
    } else {
        // Also allow faculty from the same college
        require_once __DIR__ . '/auth_functions.php';
        $userCollege = get_user_college($pdo, $userId);
        if ($userCollege) {
            $isAuthorizedFaculty = true;
        }
    }
}

if (!$isAdmin && !$isAuthorizedFaculty) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. You do not have permission to review schedules.']);
    exit;
}

if (!$scheduleId || !$userId || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verify the schedule exists and is in pending_chair status
    $checkStmt = $pdo->prepare("SELECT * FROM defense_schedules WHERE id = ?");
    $checkStmt->execute([$scheduleId]);
    $schedule = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        throw new Exception('Defense schedule not found');
    }

    if ($schedule['approval_status'] !== 'pending_chair') {
        throw new Exception('This schedule is not pending chair review (current status: ' . $schedule['approval_status'] . ')');
    }

    // Mark chair review notifications as read
    $notifStmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE user_id = ? AND related_id = ? AND type = 'chair_review'
    ");
    $notifStmt->execute([$userId, $scheduleId]);

    // Mark notification actions as completed
    $actionStmt = $pdo->prepare("
        UPDATE notification_actions na
        JOIN notifications n ON na.notification_id = n.id
        SET na.is_completed = 1, na.completed_at = NOW()
        WHERE n.user_id = ? AND n.related_id = ? AND n.type = 'chair_review'
    ");
    $actionStmt->execute([$userId, $scheduleId]);

    if ($action === 'approve') {
        // Chair approved -> auto-accept panelists and finalize the schedule
        $updateStmt = $pdo->prepare("
            UPDATE defense_schedules 
            SET approval_status = 'approved' 
            WHERE id = ?
        ");
        $updateStmt->execute([$scheduleId]);

        // Now create panelist approval records
        $panelistIds = array_filter([
            $schedule['panelist_id'],
            $schedule['panelist_id2'],
            $schedule['panelist_id3']
        ]);

        $approvalStmt = $pdo->prepare("\
            INSERT INTO panelist_approvals (defense_schedule_id, panelist_id, approval_status, response_date) 
            VALUES (?, ?, 'approved', NOW())
        ");

        foreach ($panelistIds as $panelistId) {
            if ($panelistId && $panelistId !== '') {
                $approvalStmt->execute([$scheduleId, $panelistId]);
            }
        }

        // Send panelist notice-only notifications
        createDefenseApprovalNotifications(
            $pdo,
            $scheduleId,
            $schedule['team_id'],
            $panelistIds,
            $schedule['schedule_date'],
            date('H:i', strtotime($schedule['start_time'])),
            date('H:i', strtotime($schedule['end_time'])),
            $schedule['room']
        );

        // Notify team members
        $teamMemberIds = getTeamMembersForNotifications($schedule['team_id']);
        $formattedDate = date('F j, Y', strtotime($schedule['schedule_date']));
        $formattedTime = date('g:i A', strtotime($schedule['start_time'])) . ' - ' . date('g:i A', strtotime($schedule['end_time']));
        $defenseTypeLabel = ucwords(str_replace('_', ' ', $schedule['defense_type']));
        $messageForTeam = "Your team's {$defenseTypeLabel} has been scheduled for {$formattedDate} at {$formattedTime}" .
            ($schedule['room'] ? " in {$schedule['room']}" : "") . ". The schedule is now approved.";

        foreach ($teamMemberIds as $memberId) {
            createNotification($pdo, $memberId, 'Defense Schedule Created', $messageForTeam, 'defense_scheduled', $scheduleId);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Schedule approved by chair. Panelists have been notified.',
            'new_status' => 'approved'
        ]);

    } else {
        // Chair rejected
        $updateStmt = $pdo->prepare("
            UPDATE defense_schedules 
            SET approval_status = 'rejected' 
            WHERE id = ?
        ");
        $updateStmt->execute([$scheduleId]);

        // Get team name for notification
        $teamStmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
        $teamStmt->execute([$schedule['team_id']]);
        $teamName = $teamStmt->fetchColumn();

        $formattedDate = date('F j, Y', strtotime($schedule['schedule_date']));

        // Notify other admins about rejection
        $adminStmt = $pdo->prepare("SELECT id FROM users WHERE usertype = 0 AND id != ? AND deleted_at IS NULL");
        $adminStmt->execute([$userId]);
        $adminIds = $adminStmt->fetchAll(PDO::FETCH_COLUMN);

        $rejectTitle = "Defense Schedule Rejected by Chair";
        $rejectMessage = "A defense schedule was rejected during chair review:\n\n" .
                        "🎓 Team: {$teamName}\n" .
                        "📅 Date: {$formattedDate}\n" .
                        "🏢 Room: {$schedule['room']}\n" .
                        ($rejectionReason ? "❌ Reason: {$rejectionReason}\n" : "") .
                        "\nPlease create a new schedule for this team.";

        foreach ($adminIds as $adminId) {
            createNotification($pdo, $adminId, $rejectTitle, $rejectMessage, 'defense_scheduled', $scheduleId);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Schedule rejected. Admins have been notified.',
            'new_status' => 'rejected'
        ]);
    }

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error handling chair approval: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
