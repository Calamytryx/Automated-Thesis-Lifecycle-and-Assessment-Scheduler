<?php
session_start();
require_once '../setup/db.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['id'];

// Debug: Log the user ID
error_log("DEBUG get_pending_approvals: User ID = " . $userId);

try {
    // Get pending defense approvals for the current user
    $stmt = $pdo->prepare("
        SELECT 
            pa.defense_schedule_id as schedule_id,
            pa.approval_status,
            ds.schedule_date,
            ds.start_time,
            ds.end_time,
            ds.room,
            t.name as team_name,
            t.program,
            rt.title as research_title,
            
            -- Get other panelists for this defense
            CONCAT(
                COALESCE(CONCAT(u1.first_name, ' ', u1.last_name), 'TBA'),
                ', ',
                COALESCE(CONCAT(u2.first_name, ' ', u2.last_name), 'TBA'),
                ', ',
                COALESCE(CONCAT(u3.first_name, ' ', u3.last_name), 'TBA')
            ) as all_panelists,
            
            -- Format date and time for display
            DATE_FORMAT(ds.schedule_date, '%M %e, %Y') as formatted_date,
            CONCAT(
                DATE_FORMAT(ds.start_time, '%l:%i %p'), 
                ' - ', 
                DATE_FORMAT(ds.end_time, '%l:%i %p')
            ) as formatted_time
            
        FROM panelist_approvals pa
        JOIN defense_schedules ds ON pa.defense_schedule_id = ds.id
        JOIN teams t ON ds.team_id = t.id
        LEFT JOIN research_titles rt ON t.id = rt.team_id
        LEFT JOIN users u1 ON ds.panelist_id = u1.id
        LEFT JOIN users u2 ON ds.panelist_id2 = u2.id  
        LEFT JOIN users u3 ON ds.panelist_id3 = u3.id
        
        WHERE pa.panelist_id = ? 
        AND pa.approval_status = 'pending'
        AND ds.approval_status = 'pending'
        
        ORDER BY ds.schedule_date, ds.start_time
    ");
    
    $stmt->execute([$userId]);
    $pendingApprovals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the query results
    error_log("DEBUG get_pending_approvals: Found " . count($pendingApprovals) . " pending approvals for user " . $userId);
    
    // Process the data to clean up panelist names (remove the current user from the list)
    foreach ($pendingApprovals as &$approval) {
        // Get current user's name to remove from panelist list
        $userStmt = $pdo->prepare("
            SELECT CONCAT(first_name, ' ', last_name) as full_name 
            FROM users WHERE id = ?
        ");
        $userStmt->execute([$userId]);
        $currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($currentUser) {
            // Remove current user from the panelist list and clean it up
            $panelistArray = explode(', ', $approval['all_panelists']);
            $panelistArray = array_filter($panelistArray, function($name) use ($currentUser) {
                return $name !== $currentUser['full_name'] && $name !== 'TBA';
            });
            $approval['other_panelists'] = implode(', ', $panelistArray);
            
            // If no other panelists, show appropriate message
            if (empty($approval['other_panelists'])) {
                $approval['other_panelists'] = 'No other panelists assigned yet';
            }
        } else {
            $approval['other_panelists'] = $approval['all_panelists'];
        }
        
        // Remove the full panelist list from response
        unset($approval['all_panelists']);
    }
    
    echo json_encode([
        'success' => true,
        'pendingApprovals' => $pendingApprovals,
        'count' => count($pendingApprovals)
    ]);
    
} catch (Exception $e) {
    error_log("Error getting pending approvals: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error retrieving pending approvals'
    ]);
}
?>
