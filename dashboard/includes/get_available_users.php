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
$include_advisers = isset($_GET['include_advisers']) ? (bool)$_GET['include_advisers'] : false;

try {
    // Query to get users who are not in any team (excluding current team if editing)
    // Exclude root admin (id = 0) and only show staff/admins for adviser roles
// Build query differently for adviser case vs other usertypes.
$params = [];
if ($usertype_filter === 2) {
    // For advisers, return faculty users and include how many teams they currently advise.
    // We don't filter out those with 3+ teams here because the client may allow override after confirmation.
    $sql = "SELECT u.id, u.first_name, u.last_name, u.usertype, u.username, u.email,
                (
                    SELECT COUNT(*) FROM team_members tm2 JOIN teams t2 ON tm2.team_id = t2.id
                    WHERE tm2.user_id = u.id AND tm2.role = 'adviser'";

    if ($currentTeamId) {
        $sql .= " AND t2.id != ?";
        $params[] = $currentTeamId;
    }

    $sql .= ") AS adviser_count
            FROM users u
            WHERE u.id != 0 AND u.usertype = 2
            ORDER BY u.last_name, u.first_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Default: return users who are not assigned to any team (excluding current team if provided)
    $sql = "SELECT u.id, u.first_name, u.last_name, u.usertype, u.username, u.email
            FROM users u
            WHERE u.id != 0";

    // Exclude users who are assigned to any team other than currentTeamId
    if ($currentTeamId) {
        $sql .= " AND u.id NOT IN (
                    SELECT DISTINCT tm.user_id FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE t.id != ?
                )";
        $params[] = $currentTeamId;
    } else {
        $sql .= " AND u.id NOT IN (
                    SELECT DISTINCT tm.user_id FROM team_members tm
                )";
    }

    // Filter by usertype if specified
    if ($usertype_filter !== null) {
        $sql .= " AND u.usertype = ?";
        $params[] = $usertype_filter;
    } else {
        $sql .= " AND u.usertype IN (0,1,2)";
    }

    $sql .= " ORDER BY u.usertype, u.last_name, u.first_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If caller requested to include advisers even if they are assigned elsewhere,
    // also fetch all advisers with their adviser_count and merge them into the result
    if ($include_advisers) {
        $advParams = [];
        $advSql = "SELECT u.id, u.first_name, u.last_name, u.usertype, u.username, u.email,
                    (
                        SELECT COUNT(*) FROM team_members tm2 JOIN teams t2 ON tm2.team_id = t2.id
                        WHERE tm2.user_id = u.id AND tm2.role = 'adviser'";
        if ($currentTeamId) {
            $advSql .= " AND t2.id != ?";
            $advParams[] = $currentTeamId;
        }
        $advSql .= ") AS adviser_count
                FROM users u
                WHERE u.id != 0 AND u.usertype = 2
                ORDER BY u.last_name, u.first_name";

        $stmt2 = $pdo->prepare($advSql);
        $stmt2->execute($advParams);
        $advisers = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // Merge advisers into users list (adviser records overwrite by id)
        $byId = [];
        foreach ($users as $u) {
            $byId[$u['id']] = $u;
        }
        foreach ($advisers as $a) {
            $byId[$a['id']] = $a; // overwrite or add
        }

        // Rebuild users array preserving alphabetical order by last_name
        $users = array_values($byId);
        usort($users, function ($a, $b) {
            return strcmp($a['last_name'] . $a['first_name'], $b['last_name'] . $b['first_name']);
        });
    }
}

    header('Content-Type: application/json');
    echo json_encode($users);

} catch (Exception $e) {
    error_log('Error fetching available users: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to fetch available users']);
}
?>
