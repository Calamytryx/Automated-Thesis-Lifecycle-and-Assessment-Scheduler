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
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    $data = [];
    $totalRows = 0;

    // ==================================================================
    // STUDENT VIEW (usertype = 1)
    // Shows their own team evaluations (aggregate by team, not individual)
    // ==================================================================
    if ($usertype == 1) {
        // Get student's team(s)
        $teamQuery = "SELECT DISTINCT team_id FROM team_members WHERE user_id = ?";
        $teamStmt = $pdo->prepare($teamQuery);
        $teamStmt->execute([$userId]);
        $teams = $teamStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($teams)) {
            echo json_encode([
                'data' => [],
                'page' => $page,
                'per_page' => $perPage,
                'total_rows' => 0,
                'total_pages' => 0,
                'message' => 'You are not part of any team yet.'
            ]);
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($teams), '?'));
        
        // Get team evaluations with average scores
        $query = "SELECT 
            t.id AS team_id,
            t.name AS team_name,
            rt.title AS research_title,
            t.program,
            COUNT(DISTINCT ep.id) AS evaluation_count,
            ROUND(AVG(ep.group_score), 2) AS avg_group_score,
            ROUND(AVG(ep.solo_score), 2) AS avg_individual_score,
            ROUND(AVG(ep.total_score), 2) AS avg_total_score,
            MAX(ep.created_at) AS latest_evaluation,
            GROUP_CONCAT(DISTINCT CONCAT(adv.first_name, ' ', adv.last_name) SEPARATOR ', ') AS adviser
        FROM teams t
        LEFT JOIN research_titles rt ON t.id = rt.team_id
        LEFT JOIN evaluation_per_panel ep ON ep.student_id IN (
            SELECT user_id FROM team_members WHERE team_id = t.id
        )
        LEFT JOIN team_members tm_adv ON t.id = tm_adv.team_id AND tm_adv.role = 'Adviser'
        LEFT JOIN users adv ON tm_adv.user_id = adv.id
        WHERE t.id IN ($placeholders)
        GROUP BY t.id
        ORDER BY latest_evaluation DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($teams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalRows = count($data);

    // ==================================================================
    // FACULTY VIEW (usertype = 2) - Adviser/Panelist
    // Shows teams they advise + teams they evaluated as panelist
    // ==================================================================
    } elseif ($usertype == 2) {
        // Count query
        $countQuery = "SELECT COUNT(DISTINCT t.id)
            FROM teams t
            LEFT JOIN team_members tm ON t.id = tm.team_id AND tm.user_id = ? AND tm.role = 'Adviser'
            LEFT JOIN evaluation_per_panel ep ON ep.evaluator_id = ?
            LEFT JOIN team_members tm_eval ON ep.student_id = tm_eval.user_id
            WHERE tm.user_id = ? OR tm_eval.team_id IS NOT NULL";
        
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute([$userId, $userId, $userId]);
        $totalRows = $countStmt->fetchColumn();

        // Data query with pagination
        $query = "SELECT 
            t.id AS team_id,
            t.name AS team_name,
            rt.title AS research_title,
            t.program,
            COUNT(DISTINCT ep.id) AS evaluation_count,
            ROUND(AVG(ep.group_score), 2) AS avg_group_score,
            ROUND(AVG(ep.solo_score), 2) AS avg_individual_score,
            ROUND(AVG(ep.total_score), 2) AS avg_total_score,
            MAX(ep.created_at) AS latest_evaluation,
            GROUP_CONCAT(DISTINCT CONCAT(adv.first_name, ' ', adv.last_name) SEPARATOR ', ') AS adviser,
            CASE 
                WHEN tm_adviser.user_id = ? THEN 'Adviser'
                ELSE 'Panelist'
            END AS role
        FROM teams t
        LEFT JOIN research_titles rt ON t.id = rt.team_id
        LEFT JOIN team_members tm_adviser ON t.id = tm_adviser.team_id AND tm_adviser.user_id = ? AND tm_adviser.role = 'Adviser'
        LEFT JOIN evaluation_per_panel ep_check ON ep_check.evaluator_id = ?
        LEFT JOIN team_members tm_eval ON ep_check.student_id = tm_eval.user_id AND tm_eval.team_id = t.id
        LEFT JOIN evaluation_per_panel ep ON ep.student_id IN (
            SELECT user_id FROM team_members WHERE team_id = t.id
        )
        LEFT JOIN team_members tm_adv ON t.id = tm_adv.team_id AND tm_adv.role = 'Adviser'
        LEFT JOIN users adv ON tm_adv.user_id = adv.id
        WHERE tm_adviser.user_id = ? OR tm_eval.team_id IS NOT NULL
        GROUP BY t.id
        ORDER BY latest_evaluation DESC
        LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId, $userId, $userId, $userId, $perPage, $offset]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // Invalid user type - admins should use dashboard
        echo json_encode(['error' => 'Please use the dashboard for evaluation management.']);
        exit;
    }

    echo json_encode([
        'data' => $data,
        'page' => $page,
        'per_page' => $perPage,
        'total_rows' => (int)$totalRows,
        'total_pages' => ceil($totalRows / $perPage),
        'usertype' => $usertype
    ]);

} catch (PDOException $e) {
    error_log('Database error in get_evaluations.php: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Database error occurred.',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('General error in get_evaluations.php: ' . $e->getMessage());
    echo json_encode(['error' => 'An unexpected error occurred.']);
}
