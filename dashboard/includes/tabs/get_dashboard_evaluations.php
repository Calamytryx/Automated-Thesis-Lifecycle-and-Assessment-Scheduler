<?php
require_once '../../../assets/setup/db.inc.php';
require_once '../../../assets/includes/auth_functions.php';

header('Content-Type: application/json');

// Session check
if (session_status() == PHP_SESSION_NONE) session_start();

// Authentication check - Admin only
if (!isset($_SESSION['id']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] != 0) {
    echo json_encode(['error' => 'Admin access required.']);
    exit;
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    // Count query - count distinct teams that have evaluations
    $countQuery = "SELECT COUNT(DISTINCT t.id)
        FROM teams t
        INNER JOIN team_members tm ON t.id = tm.team_id
        INNER JOIN evaluation_per_panel ep ON ep.student_id = tm.user_id";
    
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute();
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
        GROUP_CONCAT(DISTINCT CONCAT(adv.first_name, ' ', adv.last_name) SEPARATOR ', ') AS adviser
    FROM teams t
    LEFT JOIN research_titles rt ON t.id = rt.team_id
    INNER JOIN team_members tm ON t.id = tm.team_id
    INNER JOIN evaluation_per_panel ep ON ep.student_id = tm.user_id
    LEFT JOIN team_members tm_adv ON t.id = tm_adv.team_id AND tm_adv.role = 'Adviser'
    LEFT JOIN users adv ON tm_adv.user_id = adv.id
    GROUP BY t.id
    ORDER BY latest_evaluation DESC
    LIMIT ? OFFSET ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$perPage, $offset]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'data' => $data,
        'page' => $page,
        'per_page' => $perPage,
        'total_rows' => (int)$totalRows,
        'total_pages' => ceil($totalRows / $perPage)
    ]);

} catch (PDOException $e) {
    error_log('Database error in get_dashboard_evaluations.php: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Database error occurred.',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('General error in get_dashboard_evaluations.php: ' . $e->getMessage());
    echo json_encode(['error' => 'An unexpected error occurred.']);
}
