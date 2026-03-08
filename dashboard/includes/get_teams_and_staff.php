<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');

try {
    // Determine if this is a request for a filtered staff list
    $selected_team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : '';

    // If a specific team ID is provided, just return the filtered staff
    if ($selected_team_id) {
        // First get the team's college
        $teamCollegeStmt = $pdo->prepare("
            SELECT p.college 
            FROM teams t
            JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)
            WHERE t.id = :team_id
            LIMIT 1
        ");
        $teamCollegeStmt->execute(['team_id' => $selected_team_id]);
        $teamCollege = $teamCollegeStmt->fetchColumn();
        
        // Build staff query - include faculty AND program chairs from same college
        // Users don't have a college column, so we join to programs to get their college
        if ($teamCollege) {
            $staff_stmt = $pdo->prepare("
                SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) as name
                FROM users u
                LEFT JOIN programs p2 ON u.program = CONCAT(p2.name, CASE WHEN p2.specialization IS NOT NULL AND p2.specialization != '' THEN CONCAT(' - ', p2.specialization) ELSE '' END)
                WHERE (
                    u.usertype = 2 
                    OR (u.usertype = 0 AND u.id != 0 AND p2.college = :college)
                )
                AND u.id NOT IN (
                    SELECT user_id FROM team_members WHERE role = 'adviser' AND team_id = :team_id
                )
                ORDER BY name
            ");
            $staff_stmt->execute(['college' => $teamCollege, 'team_id' => $selected_team_id]);
        } else {
            // Just include all faculty members
            $staff_stmt = $pdo->prepare("
                SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) as name
                FROM users u
                WHERE u.usertype = 2
                AND u.id NOT IN (
                    SELECT user_id FROM team_members WHERE role = 'adviser' AND team_id = :team_id
                )
                ORDER BY name
            ");
            $staff_stmt->execute(['team_id' => $selected_team_id]);
        }
        
        $staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'staff' => $staff]);
        exit;
    }

    // --- Initial page load logic (original code) ---
    // Fetch teams with their research title status
    $teams_stmt = $pdo->query("
        SELECT t.id, t.name, 
               CASE WHEN rt.team_id IS NOT NULL THEN 1 ELSE 0 END as has_research_title
        FROM teams t 
        LEFT JOIN research_titles rt ON t.id = rt.team_id 
        ORDER BY t.name
    ");
    $teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch IDs of teams that already have a defense schedule
    $scheduled_teams_stmt = $pdo->query("SELECT DISTINCT team_id FROM defense_schedules WHERE team_id IS NOT NULL");
    $scheduled_team_ids = $scheduled_teams_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Add has_schedule flag to teams
    foreach ($teams as &$team) {
        $team['has_schedule'] = in_array($team['id'], $scheduled_team_ids);
    }
    unset($team); // Unset reference

    // For the initial load, the staff list will be all faculty and program chairs
    $staff_stmt = $pdo->query("
        SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name
        FROM users u
        WHERE u.usertype = 2 OR (u.usertype = 0 AND u.id != 0)
        ORDER BY name
    ");
    $staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode(['success' => true, 'teams' => $teams, 'staff' => $staff]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>