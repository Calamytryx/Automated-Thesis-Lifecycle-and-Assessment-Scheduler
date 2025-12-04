<?php
require_once '../../../assets/setup/db.inc.php';
require_once '../../../assets/includes/auth_functions.php';

header('Content-Type: application/json');

// Session check
if (session_status() == PHP_SESSION_NONE) session_start();

// Authentication check
if (!isset($_SESSION['id']) || !isset($_SESSION['usertype'])) {
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

$userId = $_SESSION['id'];
$usertype = $_SESSION['usertype'];
$teamId = intval($_GET['team_id'] ?? 0);

if ($teamId <= 0) {
    echo json_encode(['error' => 'Invalid team ID.']);
    exit;
}

try {
    // Verify access permissions
    $hasAccess = false;

    if ($usertype == 1) {
        // Student - check if they're in the team
        $checkStmt = $pdo->prepare("SELECT 1 FROM team_members WHERE team_id = ? AND user_id = ?");
        $checkStmt->execute([$teamId, $userId]);
        $hasAccess = (bool)$checkStmt->fetch();
    } elseif ($usertype == 2) {
        // Faculty - check if they're adviser or have evaluated this team
        $checkStmt = $pdo->prepare("
            SELECT 1 FROM team_members tm 
            WHERE tm.team_id = ? AND tm.user_id = ? AND tm.role = 'Adviser'
            UNION
            SELECT 1 FROM evaluation_per_panel ep
            JOIN team_members tm ON ep.student_id = tm.user_id
            WHERE tm.team_id = ? AND ep.evaluator_id = ?
        ");
        $checkStmt->execute([$teamId, $userId, $teamId, $userId]);
        $hasAccess = (bool)$checkStmt->fetch();
    } else {
        // Admins should use dashboard
        echo json_encode(['error' => 'Please use the dashboard for evaluation management.']);
        exit;
    }

    if (!$hasAccess) {
        echo json_encode(['error' => 'Access denied to this team\'s evaluations.']);
        exit;
    }

    // Get team information
    $teamQuery = "SELECT 
        t.id,
        t.name AS team_name,
        rt.title AS research_title,
        t.program,
        GROUP_CONCAT(DISTINCT CONCAT(adv.first_name, ' ', adv.last_name) SEPARATOR ', ') AS adviser
    FROM teams t
    LEFT JOIN research_titles rt ON t.id = rt.team_id
    LEFT JOIN team_members tm_adv ON t.id = tm_adv.team_id AND tm_adv.role = 'Adviser'
    LEFT JOIN users adv ON tm_adv.user_id = adv.id
    WHERE t.id = ?
    GROUP BY t.id";
    
    $teamStmt = $pdo->prepare($teamQuery);
    $teamStmt->execute([$teamId]);
    $teamInfo = $teamStmt->fetch(PDO::FETCH_ASSOC);

    if (!$teamInfo) {
        echo json_encode(['error' => 'Team not found.']);
        exit;
    }

    // Get detailed evaluations for this team
    $evaluationsQuery = "SELECT 
        ep.id AS evaluation_id,
        CONCAT(e.first_name, ' ', e.last_name) AS evaluator_name,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        s.id AS student_id,
        ep.group_score,
        ep.solo_score,
        ep.total_score,
        ep.comments,
        ep.created_at,
        ds.schedule_date,
        ds.defense_type
    FROM evaluation_per_panel ep
    JOIN users e ON ep.evaluator_id = e.id
    JOIN users s ON ep.student_id = s.id
    JOIN team_members tm ON ep.student_id = tm.user_id AND tm.team_id = ?
    LEFT JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
    ORDER BY ds.schedule_date DESC, e.last_name ASC, s.last_name ASC";

    $evalStmt = $pdo->prepare($evaluationsQuery);
    $evalStmt->execute([$teamId]);
    $evaluations = $evalStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get team members
    $membersQuery = "SELECT 
        u.id,
        CONCAT(u.first_name, ' ', u.last_name) AS name,
        tm.role
    FROM team_members tm
    JOIN users u ON tm.user_id = u.id
    WHERE tm.team_id = ?
    ORDER BY 
        CASE tm.role 
            WHEN 'Adviser' THEN 1
            WHEN 'Leader' THEN 2
            ELSE 3
        END,
        u.last_name";

    $membersStmt = $pdo->prepare($membersQuery);
    $membersStmt->execute([$teamId]);
    $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'team_info' => $teamInfo,
        'evaluations' => $evaluations,
        'members' => $members,
        'usertype' => $usertype
    ]);

} catch (PDOException $e) {
    error_log('Database error in get_evaluation_details.php: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Database error occurred.',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('General error in get_evaluation_details.php: ' . $e->getMessage());
    echo json_encode(['error' => 'An unexpected error occurred.']);
}
