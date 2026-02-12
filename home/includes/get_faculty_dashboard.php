<?php
session_start();
require_once '../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$userId = $_SESSION['id'];
$userType = $_SESSION['usertype'];

error_log("GET_FACULTY_DASHBOARD: userId={$userId}, userType={$userType}");

try {
    $response = [
        'success' => true,
        'advisee_teams' => [],
        'paneling_defenses' => []
    ];
    
    if ($userType == 2) { // Faculty only
        // 1. Fetch teams where user is adviser with detailed evaluation data
        $adviseeStmt = $pdo->prepare("
            SELECT 
                t.id,
                t.name,
                t.program,
                rt.title as research_title,
                COUNT(DISTINCT tm_students.user_id) as member_count,
                -- Get completed requirements count
                (SELECT COUNT(*) FROM team_requirements tr 
                 WHERE tr.team_id = t.id AND tr.status = 'approved') as completed_count,
                -- Get total requirements count
                (SELECT COUNT(*) FROM requirements r 
                 WHERE r.is_defense_manuscript = 0) as total_requirements,
                -- Get latest defense type
                (SELECT ds.defense_type FROM defense_schedules ds 
                 WHERE ds.team_id = t.id 
                 ORDER BY ds.schedule_date DESC LIMIT 1) as latest_defense_type,
                -- Get next defense schedule
                (SELECT ds.id FROM defense_schedules ds 
                 WHERE ds.team_id = t.id 
                 AND ds.schedule_date >= CURDATE()
                 ORDER BY ds.schedule_date ASC LIMIT 1) as next_defense_id,
                (SELECT ds.schedule_date FROM defense_schedules ds 
                 WHERE ds.team_id = t.id 
                 AND ds.schedule_date >= CURDATE()
                 ORDER BY ds.schedule_date ASC LIMIT 1) as next_defense_date,
                -- Get override status
                (SELECT dto.override_type FROM defense_type_overrides dto 
                 WHERE dto.team_id = t.id AND dto.active = 1
                 ORDER BY dto.created_at DESC LIMIT 1) as override_defense_type,
                -- Get evaluation counts
                (SELECT COUNT(DISTINCT ep.id) FROM evaluation_per_panel ep 
                 JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
                 WHERE ds.team_id = t.id) as total_evaluations,
                -- Get average score (from latest defense only)
                (SELECT ROUND(AVG(ep.total_score), 2) FROM evaluation_per_panel ep 
                 WHERE ep.defense_schedule_id = (
                     SELECT ds2.id FROM defense_schedules ds2 
                     WHERE ds2.team_id = t.id 
                     ORDER BY ds2.schedule_date DESC LIMIT 1
                 )) as avg_score,
                -- Get student member list
                (SELECT GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) ORDER BY u.last_name SEPARATOR ', ')
                 FROM team_members tm2
                 JOIN users u ON tm2.user_id = u.id
                 WHERE tm2.team_id = t.id AND u.usertype = 1) as student_names,
                -- Get pending requirements count
                (SELECT COUNT(*) FROM team_requirements tr 
                 WHERE tr.team_id = t.id AND tr.status = 'pending') as pending_requirements,
                -- Get research title status
                rt.approved_at as title_approved_at
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id
            LEFT JOIN team_members tm_students ON t.id = tm_students.team_id 
                AND tm_students.user_id IN (SELECT id FROM users WHERE usertype = 1)
            LEFT JOIN research_titles rt ON t.id = rt.team_id
            WHERE tm.user_id = ? AND tm.role = 'adviser'
            GROUP BY t.id, t.name, t.program, rt.title, rt.approved_at
            ORDER BY t.name ASC
        ");
        $adviseeStmt->execute([$userId]);
        $response['advisee_teams'] = $adviseeStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add pass thresholds for each advisee team
        foreach ($response['advisee_teams'] as &$team) {
            $thresholdQuery = "SELECT 
                MIN(r.pass_threshold_1) as pass_threshold_1,
                MIN(r.pass_threshold_2) as pass_threshold_2,
                MIN(r.pass_threshold_3) as pass_threshold_3
            FROM defense_schedules ds
            JOIN rubric_groups rg ON rg.defense_type = ds.defense_type
            JOIN rubric_group_items rgi ON rgi.group_id = rg.id
            JOIN rubrics r ON rgi.rubric_id = r.id
            WHERE ds.team_id = ? AND r.rubric_type = 'passfail'
            ORDER BY ds.schedule_date DESC LIMIT 1";
            $thresholdStmt = $pdo->prepare($thresholdQuery);
            $thresholdStmt->execute([$team['id']]);
            $thresholds = $thresholdStmt->fetch(PDO::FETCH_ASSOC);
            $team['pass_threshold_3'] = $thresholds['pass_threshold_3'] ?? 65;
        }
        unset($team);
        
        // 2. Fetch defense schedules where user is panelist
        $panelingStmt = $pdo->prepare("
            SELECT 
                ds.id as schedule_id,
                ds.schedule_date,
                ds.start_time,
                ds.end_time,
                ds.room,
                ds.defense_type,
                ds.status,
                t.id as team_id,
                t.name as team_name,
                t.program as team_program,
                rt.title as research_title,
                -- Check if already evaluated
                (SELECT COUNT(*) FROM evaluation_per_panel ep 
                 WHERE ep.defense_schedule_id = ds.id 
                 AND ep.evaluator_id = ?) as has_evaluated,
                -- Get team members
                GROUP_CONCAT(
                    DISTINCT CONCAT(u.first_name, ' ', u.last_name)
                    ORDER BY tm.role = 'leader' DESC, u.last_name ASC
                    SEPARATOR ', '
                ) as team_members
            FROM defense_schedules ds
            JOIN teams t ON ds.team_id = t.id
            LEFT JOIN research_titles rt ON t.id = rt.team_id
            JOIN team_members tm ON t.id = tm.team_id
            JOIN users u ON tm.user_id = u.id
            WHERE (ds.panelist_id = ? OR ds.panelist_id2 = ? OR ds.panelist_id3 = ?)
            AND u.usertype = 1
            GROUP BY ds.id, ds.schedule_date, ds.start_time, ds.end_time, ds.room, 
                     ds.defense_type, ds.status, t.id, t.name, t.program, rt.title
            ORDER BY 
                CASE 
                    WHEN ds.schedule_date >= CURDATE() THEN 0 
                    ELSE 1 
                END,
                ds.schedule_date DESC,
                ds.start_time DESC
        ");
        $panelingStmt->execute([$userId, $userId, $userId, $userId]);
        $response['paneling_defenses'] = $panelingStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error in get_faculty_dashboard.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading dashboard data: ' . $e->getMessage()
    ]);
}
?>
