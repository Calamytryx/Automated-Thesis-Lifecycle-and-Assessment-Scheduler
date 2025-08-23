<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');

try {
    // Fetch teams with their research title status
    $teams_stmt = $pdo->query("
        SELECT t.id, t.name, 
               CASE WHEN rt.team_id IS NOT NULL THEN 1 ELSE 0 END as has_research_title
        FROM teams t 
        LEFT JOIN research_titles rt ON t.id = rt.team_id 
        ORDER BY t.name
    ");
    $teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch staff (users with usertype 2) who are NOT adviser in any team
    $staff_stmt = $pdo->query("
        SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name
        FROM users u
        WHERE u.usertype = 2
        AND u.id NOT IN (
            SELECT user_id FROM team_members WHERE role = 'adviser'
        )
        ORDER BY name
    ");
    $staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);
    

    // Fetch IDs of teams that already have a defense schedule
    $scheduled_teams_stmt = $pdo->query("SELECT DISTINCT team_id FROM defense_schedules WHERE team_id IS NOT NULL");
    $scheduled_team_ids = $scheduled_teams_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Add has_schedule flag to teams
    foreach ($teams as &$team) {
        $team['has_schedule'] = in_array($team['id'], $scheduled_team_ids);
    }
    unset($team); // Unset reference

    echo json_encode(['success' => true, 'teams' => $teams, 'staff' => $staff]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>