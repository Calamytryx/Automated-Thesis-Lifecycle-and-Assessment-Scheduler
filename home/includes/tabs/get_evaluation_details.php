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
            WHERE tm.team_id = ? AND tm.user_id = ? AND LOWER(tm.role) = 'adviser'
            UNION
            SELECT 1 FROM evaluation_per_panel ep
            JOIN team_members tm ON ep.student_id = tm.user_id
            WHERE tm.team_id = ? AND ep.evaluator_id = ?
        ");
        $checkStmt->execute([$teamId, $userId, $teamId, $userId]);
        $hasAccess = (bool)$checkStmt->fetch();
    } elseif ($usertype == 0) {
        // Admin - check if they're adviser of this team
        $checkStmt = $pdo->prepare("SELECT 1 FROM team_members WHERE team_id = ? AND user_id = ? AND LOWER(role) = 'adviser'");
        $checkStmt->execute([$teamId, $userId]);
        $hasAccess = (bool)$checkStmt->fetch();
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

    // Get latest defense schedule for this team
    $defenseQuery = "SELECT id, defense_type FROM defense_schedules 
        WHERE team_id = ? ORDER BY schedule_date DESC LIMIT 1";
    $defenseStmt = $pdo->prepare($defenseQuery);
    $defenseStmt->execute([$teamId]);
    $latestDefense = $defenseStmt->fetch(PDO::FETCH_ASSOC);
    $latestDefenseId = $latestDefense ? $latestDefense['id'] : null;

    // Get pass thresholds from rubric
    $passThreshold1 = 81;
    $passThreshold2 = 75;
    $passThreshold3 = 65;
    if ($latestDefense) {
        $thresholdQuery = "SELECT 
            MIN(r.pass_threshold_1) as pass_threshold_1,
            MIN(r.pass_threshold_2) as pass_threshold_2,
            MIN(r.pass_threshold_3) as pass_threshold_3
        FROM defense_schedules ds
        JOIN rubric_groups rg ON rg.defense_type = ds.defense_type
        JOIN rubric_group_items rgi ON rgi.group_id = rg.id
        JOIN rubrics r ON rgi.rubric_id = r.id
        WHERE ds.id = ? AND r.rubric_type = 'passfail'";
        $thresholdStmt = $pdo->prepare($thresholdQuery);
        $thresholdStmt->execute([$latestDefenseId]);
        $thresholds = $thresholdStmt->fetch(PDO::FETCH_ASSOC);
        if ($thresholds) {
            $passThreshold1 = $thresholds['pass_threshold_1'] ?? 81;
            $passThreshold2 = $thresholds['pass_threshold_2'] ?? 75;
            $passThreshold3 = $thresholds['pass_threshold_3'] ?? 65;
        }
    }

    // Build defense schedule filter for queries
    $defenseFilter = $latestDefenseId ? " AND ep.defense_schedule_id = ?" : "";
    $defenseParam = $latestDefenseId ? [$latestDefenseId] : [];

    // Get detailed evaluations for this team
    // For students: Hide scores from evaluations less than 1 week old
    $oneWeekAgo = date('Y-m-d H:i:s', strtotime('-1 week'));
    
    if ($usertype == 1) {
        // Student view - mask scores for recent evaluations (< 1 week)
        $evaluationsQuery = "SELECT 
            ep.id AS evaluation_id,
            e.id AS evaluator_id,
            CONCAT(e.first_name, ' ', e.last_name) AS evaluator_name,
            CONCAT(s.first_name, ' ', s.last_name) AS student_name,
            s.id AS student_id,
            CASE WHEN ep.created_at <= ? THEN ep.group_score ELSE NULL END AS group_score,
            CASE WHEN ep.created_at <= ? THEN ep.solo_score ELSE NULL END AS solo_score,
            CASE WHEN ep.created_at <= ? THEN ep.total_score ELSE NULL END AS total_score,
            ep.comments,
            ep.created_at,
            ds.schedule_date,
            ds.defense_type,
            CASE WHEN ep.created_at > ? THEN 1 ELSE 0 END AS score_pending
        FROM evaluation_per_panel ep
        JOIN users e ON ep.evaluator_id = e.id
        JOIN users s ON ep.student_id = s.id
        JOIN team_members tm ON ep.student_id = tm.user_id AND tm.team_id = ?
        LEFT JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
        WHERE 1=1" . $defenseFilter . "
        ORDER BY s.last_name ASC, s.first_name ASC, e.last_name ASC";
        
        $evalStmt = $pdo->prepare($evaluationsQuery);
        $evalStmt->execute(array_merge([$oneWeekAgo, $oneWeekAgo, $oneWeekAgo, $oneWeekAgo, $teamId], $defenseParam));
    } else {
        // Faculty view - show all scores immediately
        $evaluationsQuery = "SELECT 
            ep.id AS evaluation_id,
            e.id AS evaluator_id,
            CONCAT(e.first_name, ' ', e.last_name) AS evaluator_name,
            CONCAT(s.first_name, ' ', s.last_name) AS student_name,
            s.id AS student_id,
            ep.group_score,
            ep.solo_score,
            ep.total_score,
            ep.comments,
            ep.created_at,
            ds.schedule_date,
            ds.defense_type,
            0 AS score_pending
        FROM evaluation_per_panel ep
        JOIN users e ON ep.evaluator_id = e.id
        JOIN users s ON ep.student_id = s.id
        JOIN team_members tm ON ep.student_id = tm.user_id AND tm.team_id = ?
        LEFT JOIN defense_schedules ds ON ep.defense_schedule_id = ds.id
        WHERE 1=1" . $defenseFilter . "
        ORDER BY s.last_name ASC, s.first_name ASC, e.last_name ASC";
        
        $evalStmt = $pdo->prepare($evaluationsQuery);
        $evalStmt->execute(array_merge([$teamId], $defenseParam));
    }
    $rawEvaluations = $evalStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unique panelists who have evaluated this team (latest defense only)
    $panelistsQuery = "SELECT DISTINCT 
        e.id AS evaluator_id,
        CONCAT(e.first_name, ' ', e.last_name) AS evaluator_name
    FROM evaluation_per_panel ep
    JOIN users e ON ep.evaluator_id = e.id
    JOIN team_members tm ON ep.student_id = tm.user_id AND tm.team_id = ?
    WHERE 1=1" . $defenseFilter . "
    ORDER BY e.last_name ASC";
    
    $panelistsStmt = $pdo->prepare($panelistsQuery);
    $panelistsStmt->execute(array_merge([$teamId], $defenseParam));
    $panelists = $panelistsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get student members only (exclude advisers)
    $studentsQuery = "SELECT 
        u.id AS student_id,
        CONCAT(u.first_name, ' ', u.last_name) AS student_name,
        tm.role
    FROM team_members tm
    JOIN users u ON tm.user_id = u.id
    WHERE tm.team_id = ? AND tm.role != 'Adviser'
    ORDER BY u.last_name ASC, u.first_name ASC";
    
    $studentsStmt = $pdo->prepare($studentsQuery);
    $studentsStmt->execute([$teamId]);
    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group evaluations by student, then by panelist
    $evaluationsByStudent = [];
    foreach ($rawEvaluations as $eval) {
        $studentId = $eval['student_id'];
        $evaluatorId = $eval['evaluator_id'];
        
        if (!isset($evaluationsByStudent[$studentId])) {
            $evaluationsByStudent[$studentId] = [
                'student_name' => $eval['student_name'],
                'panelist_scores' => [],
                'has_pending' => false
            ];
        }
        
        $evaluationsByStudent[$studentId]['panelist_scores'][$evaluatorId] = [
            'evaluator_name' => $eval['evaluator_name'],
            'group_score' => $eval['group_score'],
            'solo_score' => $eval['solo_score'],
            'total_score' => $eval['total_score'],
            'comments' => $eval['comments'],
            'score_pending' => $eval['score_pending']
        ];
        
        if ($eval['score_pending'] == 1) {
            $evaluationsByStudent[$studentId]['has_pending'] = true;
        }
    }

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
        'evaluations_by_student' => $evaluationsByStudent,
        'panelists' => $panelists,
        'students' => $students,
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
