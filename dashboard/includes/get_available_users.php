<?php
/**
 * Fetches users who are not currently assigned to any team
 * 
 * @file /c:/xampp/htdocs/coecsathesis/dashboard/includes/get_available_users.php
 * 
 * @requires ../../assets/setup/db.inc.php
 * 
 * @return void Outputs a JSON-encoded array of available users.
 */
require_once '../../assets/setup/db.inc.php';

// Get current team ID if editing (to exclude current team members from "already assigned" check)
$currentTeamId = isset($_GET['team_id']) ? intval($_GET['team_id']) : null;
$usertype_filter = isset($_GET['usertype']) ? intval($_GET['usertype']) : null;

try {
    // Query to get users who are not in any team (excluding current team if editing)
    // Exclude root admin (id = 0) and only show staff/admins for adviser roles
    $sql = "SELECT u.id, u.first_name, u.last_name, u.usertype, u.username, u.email 
            FROM users u 
            WHERE u.id NOT IN (
                SELECT DISTINCT tm.user_id 
                FROM team_members tm 
                JOIN teams t ON tm.team_id = t.id";

    $params = [];
    if ($currentTeamId) {
        $sql .= " WHERE t.id != ?";
        $params[] = $currentTeamId;
    }

    $sql .= ") 
            AND u.id != 0"; // Exclude root admin
    
    // Filter by usertype if specified
    if ($usertype_filter !== null) {
        $sql .= " AND u.usertype = ?";
        $params[] = $usertype_filter;
    } else {
        // Default filter: Only show students (1), staff (2), and college admins (0, but not root)
        // Exclude any other user types that might exist
        $sql .= " AND u.usertype IN (0, 1, 2)";
    }
    
    $sql .= " ORDER BY u.usertype, u.last_name, u.first_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($users);

} catch (Exception $e) {
    error_log('Error fetching available users: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to fetch available users']);
}
?>
