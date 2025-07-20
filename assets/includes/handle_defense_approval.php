<?php
session_start();
require_once '../setup/db.inc.php';
require_once 'notification_functions.php';

// Set JSON content type
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $scheduleId = $_POST['schedule_id'] ?? '';
    $userId = $_SESSION['id'] ?? '';
    $rejectionReason = $_POST['rejection_reason'] ?? '';
    
    if (!$scheduleId || !$userId || !in_array($action, ['approve', 'reject'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update panelist approval status
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $updateStmt = $pdo->prepare("
            UPDATE panelist_approvals 
            SET approval_status = ?, response_date = NOW(), rejection_reason = ?
            WHERE defense_schedule_id = ? AND panelist_id = ?
        ");
        $updateStmt->execute([$status, $rejectionReason, $scheduleId, $userId]);
        
        if ($updateStmt->rowCount() === 0) {
            throw new Exception('No approval record found for this user and schedule');
        }
        
        // Mark related notifications as read
        $notificationStmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = ? AND related_id = ? AND type = 'defense_approval'
        ");
        $notificationStmt->execute([$userId, $scheduleId]);
        
        // Mark notification actions as completed
        $actionStmt = $pdo->prepare("
            UPDATE notification_actions na
            JOIN notifications n ON na.notification_id = n.id
            SET na.is_completed = 1, na.completed_at = NOW()
            WHERE n.user_id = ? AND n.related_id = ? AND n.type = 'defense_approval'
        ");
        $actionStmt->execute([$userId, $scheduleId]);
        
        // Check if all panelists have responded
        $checkStmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_panelists,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending_count
            FROM panelist_approvals 
            WHERE defense_schedule_id = ?
        ");
        $checkStmt->execute([$scheduleId]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        $response = [
            'success' => true, 
            'message' => ucfirst($action) . ' recorded successfully',
            'approval_summary' => $result
        ];
        
        // If all panelists have responded, update the defense schedule status
        if ($result['pending_count'] == 0) {
            if ($result['approved_count'] == $result['total_panelists']) {
                // All approved - finalize the schedule
                $finalizeStmt = $pdo->prepare("
                    UPDATE defense_schedules 
                    SET approval_status = 'approved' 
                    WHERE id = ?
                ");
                $finalizeStmt->execute([$scheduleId]);
                
                // Notify team that schedule is finalized
                notifyScheduleFinalized($pdo, $scheduleId);
                $response['message'] = 'All panelists approved! Schedule is now finalized.';
                $response['schedule_status'] = 'finalized';
                
            } else if ($result['rejected_count'] > 0) {
                // At least one rejection - mark for rescheduling
                $finalizeStmt = $pdo->prepare("
                    UPDATE defense_schedules 
                    SET approval_status = 'rejected' 
                    WHERE id = ?
                ");
                $finalizeStmt->execute([$scheduleId]);
                
                // Notify admin/coordinator about rejection
                notifyScheduleRejected($pdo, $scheduleId);
                $response['message'] = 'Response recorded. Schedule requires rescheduling due to rejections.';
                $response['schedule_status'] = 'needs_rescheduling';
            }
        } else {
            $response['message'] = ucfirst($action) . ' recorded. Waiting for other panelists to respond.';
            $response['schedule_status'] = 'waiting_for_approval';
        }
        
        $pdo->commit();
        echo json_encode($response);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error handling defense approval: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

/**
 * Notify team members that their defense schedule has been finalized
 */
function notifyScheduleFinalized($pdo, $scheduleId) {
    try {
        // Get schedule and team information
        $stmt = $pdo->prepare("
            SELECT ds.*, t.name as team_name 
            FROM defense_schedules ds 
            JOIN teams t ON ds.team_id = t.id 
            WHERE ds.id = ?
        ");
        $stmt->execute([$scheduleId]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($schedule) {
            $formattedDate = date('F j, Y', strtotime($schedule['schedule_date']));
            $formattedTime = date('g:i A', strtotime($schedule['start_time'])) . ' - ' . date('g:i A', strtotime($schedule['end_time']));
            
            $title = "Defense Schedule Finalized";
            $message = "Great news! All panelists have approved your defense schedule:\n\n" .
                      "📅 Date: {$formattedDate}\n" .
                      "🕒 Time: {$formattedTime}\n" .
                      "🏢 Room: {$schedule['room']}\n\n" .
                      "Your defense is now officially scheduled. Good luck!";
            
            // Get team members and notify them
            $teamMembers = getTeamMembersForNotifications($schedule['team_id']);
            foreach ($teamMembers as $memberId) {
                createNotification($pdo, $memberId, $title, $message, 'defense_scheduled', $scheduleId);
            }
        }
        
    } catch (Exception $e) {
        error_log("Error notifying schedule finalized: " . $e->getMessage());
    }
}

/**
 * Notify administrators that a defense schedule was rejected and needs rescheduling
 */
function notifyScheduleRejected($pdo, $scheduleId) {
    try {
        // Get schedule, team, and rejection information
        $stmt = $pdo->prepare("
            SELECT ds.*, t.name as team_name,
                   GROUP_CONCAT(
                       CONCAT(u.first_name, ' ', u.last_name, ': ', pa.rejection_reason) 
                       SEPARATOR '\n'
                   ) as rejection_details
            FROM defense_schedules ds 
            JOIN teams t ON ds.team_id = t.id 
            JOIN panelist_approvals pa ON ds.id = pa.defense_schedule_id
            JOIN users u ON pa.panelist_id = u.id
            WHERE ds.id = ? AND pa.approval_status = 'rejected'
            GROUP BY ds.id
        ");
        $stmt->execute([$scheduleId]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($schedule) {
            $formattedDate = date('F j, Y', strtotime($schedule['schedule_date']));
            $formattedTime = date('g:i A', strtotime($schedule['start_time'])) . ' - ' . date('g:i A', strtotime($schedule['end_time']));
            
            $title = "Defense Schedule Rejected - Rescheduling Required";
            $message = "A defense schedule requires rescheduling due to panelist rejections:\n\n" .
                      "🎓 Team: {$schedule['team_name']}\n" .
                      "📅 Original Date: {$formattedDate}\n" .
                      "🕒 Original Time: {$formattedTime}\n" .
                      "🏢 Room: {$schedule['room']}\n\n" .
                      "Rejection Reasons:\n{$schedule['rejection_details']}\n\n" .
                      "Please create a new schedule for this team.";
            
            // Notify administrators (role_type = 'admin' or 'coordinator')
            $adminStmt = $pdo->prepare("
                SELECT id FROM users 
                WHERE role_type IN ('admin', 'coordinator') 
                AND account_status = 'active'
            ");
            $adminStmt->execute();
            $admins = $adminStmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($admins as $adminId) {
                createNotification($pdo, $adminId, $title, $message, 'defense_scheduled', $scheduleId);
            }
        }
        
    } catch (Exception $e) {
        error_log("Error notifying schedule rejected: " . $e->getMessage());
    }
}
?>
