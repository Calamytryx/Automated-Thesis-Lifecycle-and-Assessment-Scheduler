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
        'user_type' => (int)$userType,
        'advisee_teams' => [],
        'paneling_defenses' => []
    ];

    // ── Helper: build the advisee-teams query for a given role filter ──
    $buildAdviseeQuery = function ($roleFilter) use ($pdo, $userId) {
        $adviseeStmt = $pdo->prepare("
            SELECT 
                t.id,
                t.name,
                t.program,
                rt.title as research_title,
                COUNT(DISTINCT tm_students.user_id) as member_count,
                (SELECT COUNT(*) FROM team_requirements tr 
                 WHERE tr.team_id = t.id AND tr.status = 'approved') as completed_count,
                (SELECT COUNT(*) FROM requirements r 
                 WHERE r.is_defense_manuscript = 0) as total_requirements,
                (SELECT ds.defense_type FROM defense_schedules ds 
                 WHERE ds.team_id = t.id 
                 ORDER BY ds.schedule_date DESC LIMIT 1) as latest_defense_type,
                (SELECT ds.id FROM defense_schedules ds 
                 WHERE ds.team_id = t.id AND ds.schedule_date >= CURDATE()
                 ORDER BY ds.schedule_date ASC LIMIT 1) as next_defense_id,
                (SELECT ds.schedule_date FROM defense_schedules ds 
                 WHERE ds.team_id = t.id AND ds.schedule_date >= CURDATE()
                 ORDER BY ds.schedule_date ASC LIMIT 1) as next_defense_date,
                (SELECT dto.override_type FROM defense_type_overrides dto 
                 WHERE dto.team_id = t.id AND dto.active = 1
                 ORDER BY dto.created_at DESC LIMIT 1) as override_defense_type,
                (SELECT COUNT(DISTINCT ep.id) FROM evaluation_per_panel ep 
                 JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
                 WHERE ds.team_id = t.id) as total_evaluations,
                (SELECT ROUND(AVG(ep.total_score), 2) FROM evaluation_per_panel ep 
                 WHERE ep.defense_schedule_id = (
                     SELECT ds2.id FROM defense_schedules ds2 
                     WHERE ds2.team_id = t.id 
                     ORDER BY ds2.schedule_date DESC LIMIT 1
                 )) as avg_score,
                (SELECT GROUP_CONCAT(CONCAT(u2.first_name, ' ', u2.last_name) ORDER BY u2.last_name SEPARATOR ', ')
                 FROM team_members tm2
                 JOIN users u2 ON tm2.user_id = u2.id
                 WHERE tm2.team_id = t.id AND u2.usertype = 1) as student_names,
                (SELECT COUNT(*) FROM team_requirements tr 
                 WHERE tr.team_id = t.id AND tr.status = 'pending') as pending_requirements,
                rt.approved_at as title_approved_at
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id
            LEFT JOIN team_members tm_students ON t.id = tm_students.team_id 
                AND tm_students.user_id IN (SELECT id FROM users WHERE usertype = 1)
            LEFT JOIN research_titles rt ON t.id = rt.team_id
            WHERE tm.user_id = ? AND tm.role $roleFilter
            GROUP BY t.id, t.name, t.program, rt.title, rt.approved_at
            ORDER BY t.name ASC
        ");
        $adviseeStmt->execute([$userId]);
        return $adviseeStmt->fetchAll(PDO::FETCH_ASSOC);
    };

    // ── Helper: add pass thresholds to each team ──
    $addThresholds = function (&$teams) use ($pdo) {
        foreach ($teams as &$team) {
            $thresholdStmt = $pdo->prepare("
                SELECT 
                    MIN(r.pass_threshold_1) as pass_threshold_1,
                    MIN(r.pass_threshold_2) as pass_threshold_2,
                    MIN(r.pass_threshold_3) as pass_threshold_3
                FROM defense_schedules ds
                JOIN rubric_groups rg ON rg.defense_type = ds.defense_type
                JOIN rubric_group_items rgi ON rgi.group_id = rg.id
                JOIN rubrics r ON rgi.rubric_id = r.id
                WHERE ds.team_id = ? AND r.rubric_type = 'passfail'
                ORDER BY ds.schedule_date DESC LIMIT 1
            ");
            $thresholdStmt->execute([$team['id']]);
            $thresholds = $thresholdStmt->fetch(PDO::FETCH_ASSOC);
            $team['pass_threshold_3'] = $thresholds['pass_threshold_3'] ?? 65;
        }
        unset($team);
    };

    // ── Helper: fetch paneling defenses ──
    $fetchPanelingDefenses = function () use ($pdo, $userId) {
        $panelingStmt = $pdo->prepare("
            SELECT 
                ds.id as schedule_id,
                ds.schedule_date,
                ds.start_time,
                ds.end_time,
                ds.room,
                ds.defense_type,
                ds.defense_status,
                ds.status,
                t.id as team_id,
                t.name as team_name,
                t.program as team_program,
                rt.title as research_title,
                (SELECT COUNT(*) FROM evaluation_per_panel ep 
                 WHERE ep.defense_schedule_id = ds.id 
                 AND ep.evaluator_id = ?) as has_evaluated,
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
            AND ds.approval_status = 'approved'
            AND u.usertype = 1
            GROUP BY ds.id, ds.schedule_date, ds.start_time, ds.end_time, ds.room, 
                     ds.defense_type, ds.defense_status, ds.status, t.id, t.name, t.program, rt.title
            ORDER BY 
                CASE WHEN ds.schedule_date >= CURDATE() THEN 0 ELSE 1 END,
                ds.schedule_date DESC,
                ds.start_time DESC
        ");
        $panelingStmt->execute([$userId, $userId, $userId, $userId]);
        return $panelingStmt->fetchAll(PDO::FETCH_ASSOC);
    };

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    //  Faculty (usertype=2)  or  Admin (usertype=0)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    if ($userType == 2 || $userType == 0) {
        $response['advisee_teams'] = $buildAdviseeQuery("= 'adviser'");
        $addThresholds($response['advisee_teams']);
        $response['paneling_defenses'] = $fetchPanelingDefenses();
    }
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    //  Student (usertype=1)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    elseif ($userType == 1) {
        // Return teams the student belongs to (same card format)
        $response['advisee_teams'] = $buildAdviseeQuery("IN ('leader','member')");
        $addThresholds($response['advisee_teams']);

        // Return the student's own defense schedules (view-only)
        $teamIds = array_column($response['advisee_teams'], 'id');
        if (!empty($teamIds)) {
            $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
            $defStmt = $pdo->prepare("
                SELECT 
                    ds.id as schedule_id,
                    ds.schedule_date,
                    ds.start_time,
                    ds.end_time,
                    ds.room,
                    ds.defense_type,
                    ds.defense_status,
                    ds.status,
                    t.id as team_id,
                    t.name as team_name,
                    t.program as team_program,
                    rt.title as research_title,
                    0 as has_evaluated,
                    CONCAT_WS(', ',
                        NULLIF(CONCAT(p1.first_name,' ',p1.last_name),' '),
                        NULLIF(CONCAT(p2.first_name,' ',p2.last_name),' '),
                        NULLIF(CONCAT(p3.first_name,' ',p3.last_name),' ')
                    ) as panelist_names
                FROM defense_schedules ds
                JOIN teams t ON ds.team_id = t.id
                LEFT JOIN research_titles rt ON t.id = rt.team_id
                LEFT JOIN users p1 ON ds.panelist_id  = p1.id
                LEFT JOIN users p2 ON ds.panelist_id2 = p2.id
                LEFT JOIN users p3 ON ds.panelist_id3 = p3.id
                WHERE ds.team_id IN ($placeholders)
                AND ds.approval_status = 'approved'
                ORDER BY 
                    CASE WHEN ds.schedule_date >= CURDATE() THEN 0 ELSE 1 END,
                    ds.schedule_date DESC,
                    ds.start_time DESC
            ");
            $defStmt->execute($teamIds);
            $response['paneling_defenses'] = $defStmt->fetchAll(PDO::FETCH_ASSOC);
        }
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
