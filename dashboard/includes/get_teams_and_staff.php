<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');

try {
    // Determine if this is a request for a filtered staff list
    $selected_team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : '';

    // If a specific team ID is provided, just return the filtered staff
    if ($selected_team_id) {
        $staff_stmt = $pdo->prepare("
            SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name
            FROM users u
            WHERE u.usertype = 2
            AND u.id NOT IN (
                SELECT user_id FROM team_members WHERE role = 'adviser' AND team_id = :team_id
            )
            ORDER BY name
        ");
        $staff_stmt->execute(['team_id' => $selected_team_id]);
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

    // For the initial load, the staff list will be all staff, not filtered
    $staff_stmt = $pdo->query("
        SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name
        FROM users u
        WHERE u.usertype = 2
        ORDER BY name
    ");
    $staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode(['success' => true, 'teams' => $teams, 'staff' => $staff]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>