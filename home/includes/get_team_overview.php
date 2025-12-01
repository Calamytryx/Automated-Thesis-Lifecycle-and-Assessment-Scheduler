<?php
session_start();
include '../../assets/setup/db.inc.php';

header('Content-Type: application/json');

// Enable error logging
error_log("GET_TEAM_OVERVIEW: Starting request");

$userId = $_SESSION['id'];
$userType = $_SESSION['usertype'];
$teamId = isset($_GET['team_id']) ? $_GET['team_id'] : null;

error_log("GET_TEAM_OVERVIEW: userId={$userId}, userType={$userType}, teamId=" . ($teamId ?? 'null'));
 
try {
    // Fetch teams based on user type and role
    if ($userType == 1) { // Student
        error_log("GET_TEAM_OVERVIEW: Fetching teams for student");
        $teamsStmt = $pdo->prepare("SELECT t.id, t.name FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ?");
        $teamsStmt->execute([$userId]);
        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
    } else { // Staff/Adviser
        error_log("GET_TEAM_OVERVIEW: Fetching teams for staff/adviser");
        $teamsStmt = $pdo->prepare("SELECT t.id, t.name FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ? AND tm.role IN ('adviser', 'panelist')");
        $teamsStmt->execute([$userId]);
        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    error_log("GET_TEAM_OVERVIEW: Found " . count($teams) . " teams");

    if (empty($teams)) {
        echo json_encode(['success' => false, 'message' => 'No teams found']);
        exit;
    }

    // If no specific team is requested, use the first team
    if (!$teamId) {
        $teamId = $teams[0]['id'];
    }

    // Find the selected team details
    $selectedTeam = null;
    foreach ($teams as $team) {
        if ($team['id'] == $teamId) {
            $selectedTeam = $team;
            break;
        }
    }

    if (!$selectedTeam) {
        echo json_encode(['success' => false, 'message' => 'Team not found']);
        exit;
    }

    // Load helper functions for program-specific manuscript requirements
    // require_once '../../dashboard/includes/manuscript_requirements_functions.php';
    
    // Get applicable manuscripts for this team
    // NOTE: Commented out because this is not used in the response and was causing collation issues
    // $applicableManuscripts = getTeamApplicableManuscripts($pdo, $teamId);
    // $applicableManuscriptIds = [];
    
    // if ($applicableManuscripts['success'] && !empty($applicableManuscripts['manuscripts'])) {
    //     $applicableManuscriptIds = array_column($applicableManuscripts['manuscripts'], 'id');
    // }

    // Get team's program and resolve to program_id
    error_log("GET_TEAM_OVERVIEW: Getting team program for team_id={$teamId}");
    $teamProgramStmt = $pdo->prepare("SELECT program FROM teams WHERE id = ?");
    $teamProgramStmt->execute([$teamId]);
    $teamProgram = $teamProgramStmt->fetchColumn();
    
    error_log("GET_TEAM_OVERVIEW: Team program = " . ($teamProgram ?? 'null'));
    
    // Resolve program name to program_id
    $resolvedProgramId = null;
    if ($teamProgram) {
        // Try exact match first
        error_log("GET_TEAM_OVERVIEW: Trying exact program match");
        $programStmt = $pdo->prepare("SELECT id FROM programs WHERE name = ?");
        $programStmt->execute([$teamProgram]);
        $resolvedProgramId = $programStmt->fetchColumn();
        
        // If no exact match, try partial match (team program contains program name)
        if (!$resolvedProgramId) {
            error_log("GET_TEAM_OVERVIEW: Trying partial program match");
            $programStmt = $pdo->prepare("SELECT id FROM programs WHERE ? LIKE CONCAT('%', name COLLATE utf8mb4_general_ci, '%')");
            $programStmt->execute([$teamProgram]);
            $resolvedProgramId = $programStmt->fetchColumn();
        }
    }
    
    error_log("GET_TEAM_OVERVIEW: Resolved program_id = " . ($resolvedProgramId ?? 'null'));
    
    // Fetch requirement progress with categorization
    // Include program-specific manuscript filtering
    if ($resolvedProgramId) {
        error_log("GET_TEAM_OVERVIEW: Fetching requirements with program filter");
        $requirementsStmt = $pdo->prepare("
            SELECT r.id, r.name, r.description, r.due_date, r.is_defense_manuscript,
                   COALESCE(tr.status, 'pending') as status,
                   tr.submitted_at, tr.feedback
            FROM requirements r 
            LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = ?
            WHERE r.is_defense_manuscript = 0 OR r.id IN (
                SELECT requirement_id FROM program_manuscript_requirements 
                WHERE program_id = ?
                AND defense_type COLLATE utf8mb4_general_ci = (
                    SELECT defense_type COLLATE utf8mb4_general_ci FROM defense_schedules 
                    WHERE team_id = ? 
                    ORDER BY schedule_date DESC 
                    LIMIT 1
                )
                AND is_required = 1
            )
            ORDER BY r.due_date ASC, r.name ASC
        ");
        $requirementsStmt->execute([$teamId, $resolvedProgramId, $teamId]);
    } else {
        error_log("GET_TEAM_OVERVIEW: Fetching requirements without program filter (fallback)");
        // Fallback: only show non-defense manuscripts if program can't be resolved
        $requirementsStmt = $pdo->prepare("
            SELECT r.id, r.name, r.description, r.due_date, r.is_defense_manuscript,
                   COALESCE(tr.status, 'pending') as status,
                   tr.submitted_at, tr.feedback
            FROM requirements r 
            LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = ?
            WHERE r.is_defense_manuscript = 0
            ORDER BY r.due_date ASC, r.name ASC
        ");
        $requirementsStmt->execute([$teamId]);
    }
    $requirements = $requirementsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Categorize requirements
    $categorized = [
        'completed' => [],
        'pending' => []
    ];

    foreach ($requirements as $req) {
        if ($req['status'] === 'approved') {
            $categorized['completed'][] = $req;
        } else {
            $categorized['pending'][] = $req;
        }
    }

    // Fetch next defense schedule
    $defenseStmt = $pdo->prepare("SELECT * FROM defense_schedules WHERE team_id = ? ORDER BY schedule_date ASC LIMIT 1");
    $defenseStmt->execute([$teamId]);
    $defense = $defenseStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'teams' => $teams,
        'selectedTeam' => $selectedTeam,
        'requirements' => $categorized,
        'defense' => $defense,
        'totalRequirements' => count($requirements),
        'completedCount' => count($categorized['completed']),
        'pendingCount' => count($categorized['pending'])
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>