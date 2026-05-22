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

$teamId = intval($_GET['team_id'] ?? 0);

if ($teamId <= 0) {
    echo json_encode(['error' => 'Invalid team ID.']);
    exit;
}

try {
    // Get team info
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

    // Get all panelists (evaluators) for this team
    $panelistsQuery = "SELECT DISTINCT 
        e.id AS evaluator_id,
        CONCAT(e.first_name, ' ', e.last_name) AS evaluator_name
    FROM evaluation_per_panel ep
    JOIN users e ON ep.evaluator_id = e.id
    JOIN team_members tm ON ep.student_id = tm.user_id
    WHERE tm.team_id = ?
    ORDER BY e.last_name, e.first_name";
    
    $panelistsStmt = $pdo->prepare($panelistsQuery);
    $panelistsStmt->execute([$teamId]);
    $panelists = $panelistsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all students in team (exclude advisers)
    $studentsQuery = "SELECT 
        u.id AS student_id,
        CONCAT(u.first_name, ' ', u.last_name) AS student_name
    FROM team_members tm
    JOIN users u ON tm.user_id = u.id
    WHERE tm.team_id = ? AND u.usertype != 2
    ORDER BY u.last_name, u.first_name";
    
    $studentsStmt = $pdo->prepare($studentsQuery);
    $studentsStmt->execute([$teamId]);
    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get pass thresholds from rubric configuration for this team's latest defense
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
    $thresholdStmt->execute([$teamId]);
    $thresholds = $thresholdStmt->fetch(PDO::FETCH_ASSOC);
    $passThreshold3 = $thresholds['pass_threshold_3'] ?? 75;

    // Get all evaluations for students in this team (latest defense only)
    $evaluationsQuery = "SELECT 
        ep.student_id,
        ep.evaluator_id,
        ep.group_score,
        ep.solo_score,
        ep.total_score,
        ep.comments
    FROM evaluation_per_panel ep
    JOIN team_members tm ON ep.student_id = tm.user_id
    WHERE tm.team_id = ?
    AND ep.defense_schedule_id = (
        SELECT ds2.id FROM defense_schedules ds2 
        WHERE ds2.team_id = ? 
        ORDER BY ds2.schedule_date DESC LIMIT 1
    )";
    
    $evaluationsStmt = $pdo->prepare($evaluationsQuery);
    $evaluationsStmt->execute([$teamId, $teamId]);
    $evaluations = $evaluationsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize evaluations by student
    $evaluationsByStudent = [];
    foreach ($students as $student) {
        $studentId = $student['student_id'];
        $evaluationsByStudent[$studentId] = [
            'student_name' => $student['student_name'],
            'panelist_scores' => []
        ];
    }

    // Fill in the scores with comments
    foreach ($evaluations as $eval) {
        $studentId = $eval['student_id'];
        $evaluatorId = $eval['evaluator_id'];
        
        if (isset($evaluationsByStudent[$studentId])) {
            $evaluationsByStudent[$studentId]['panelist_scores'][$evaluatorId] = [
                'group_score' => $eval['group_score'],
                'solo_score' => $eval['solo_score'],
                'total_score' => $eval['total_score'],
                'comments' => $eval['comments']
            ];
        }
    }

    echo json_encode([
        'team_info' => $teamInfo,
        'panelists' => $panelists,
        'students' => $students,
        'evaluations_by_student' => $evaluationsByStudent,
        'pass_threshold_3' => floatval($passThreshold3)
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
