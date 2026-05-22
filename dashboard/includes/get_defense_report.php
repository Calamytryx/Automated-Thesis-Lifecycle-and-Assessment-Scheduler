<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../../assets/includes/auth_functions.php';
require_once __DIR__ . '/section_access.php';

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['usertype'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$start = isset($data['start_date']) ? trim($data['start_date']) : '';
$end = isset($data['end_date']) ? trim($data['end_date']) : '';
if (!$start || !$end) {
    echo json_encode(['success' => false, 'message' => 'Start and end dates are required.']);
    exit;
}

// Basic date validation (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD.']);
    exit;
}

$userId = $_SESSION['id'];
$currentUsertype = $_SESSION['usertype'];

try {
    // Base query similar to get_table defense_schedules
    $base = "SELECT
        ds.id,
        ds.team_id,
        ds.schedule_date,
        ds.start_time,
        ds.end_time,
        ds.room,
        ds.defense_type,
        ds.approval_status,
        COALESCE(ds.is_finalized, 0) AS is_finalized,
        ds.panelist_id,
        ds.panelist_id2,
        ds.panelist_id3,
        t.name AS team_name,
        rt.title AS thesis_title,
           (SELECT CONCAT(u_adviser.first_name, ' ', u_adviser.last_name)
               FROM team_members tm_adviser
               JOIN users u_adviser ON tm_adviser.user_id = u_adviser.id
               WHERE tm_adviser.team_id = t.id
                AND (LOWER(COALESCE(tm_adviser.role, '')) = 'adviser' OR u_adviser.usertype = 2)
               ORDER BY (LOWER(COALESCE(tm_adviser.role, '')) = 'adviser') DESC, tm_adviser.id ASC LIMIT 1) AS adviser,
           (SELECT GROUP_CONCAT(CONCAT(u_member.first_name, ' ', u_member.last_name)
               ORDER BY FIELD(LOWER(COALESCE(tm_member.role, '')), 'leader', 'member'), tm_member.id ASC SEPARATOR ', ')
               FROM team_members tm_member
               JOIN users u_member ON tm_member.user_id = u_member.id
               WHERE tm_member.team_id = t.id
                AND (
                    LOWER(COALESCE(tm_member.role, '')) IN ('leader', 'member')
                    OR (LOWER(COALESCE(tm_member.role, '')) = '' AND u_member.usertype = 1)
                )) AS members,
        GROUP_CONCAT(DISTINCT CONCAT(u_panelist.first_name, ' ', u_panelist.last_name)
             ORDER BY FIELD(u_panelist.id, ds.panelist_id, ds.panelist_id2, ds.panelist_id3) SEPARATOR ', ') AS panelists
    FROM defense_schedules ds
    JOIN teams t ON ds.team_id = t.id
    LEFT JOIN research_titles rt ON t.id = rt.team_id
    LEFT JOIN users u_panelist ON u_panelist.id IN (ds.panelist_id, ds.panelist_id2, ds.panelist_id3)
    JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)";

    $where = " WHERE ds.schedule_date BETWEEN :start AND :end ";
    $params = [':start' => $start, ':end' => $end];

    // Access restrictions
    $isSuperAdmin = ($currentUsertype === 0 && $userId === 0);
    if (!$isSuperAdmin) {
        // Program chairs and admins see by college
        if ($currentUsertype === 0) {
            $userCollege = get_user_college($pdo, $userId);
            if ($userCollege) {
                $where .= " AND p.college = :college ";
                $params[':college'] = $userCollege;
            }
        }

        // Faculty limited to their sections
        if ($currentUsertype === 2) {
            $assignedSections = getProfessorSections($pdo, $userId);
            if (!empty($assignedSections)) {
                $placeholders = [];
                foreach ($assignedSections as $i => $sec) {
                    $k = ":sec_$i";
                    $placeholders[] = $k;
                    $params[$k] = $sec;
                }
                $where .= " AND EXISTS (
                    SELECT 1 FROM team_members tm_scope JOIN users u_scope ON u_scope.id = tm_scope.user_id
                    WHERE tm_scope.team_id = t.id AND u_scope.usertype = 1 AND u_scope.section IN (" . implode(',', $placeholders) . ")
                )";
            } else {
                // No sections -> no access
                echo json_encode(['success' => true, 'data' => []]);
                exit;
            }
        }
    }

    $finalSql = $base . $where . " GROUP BY ds.id ORDER BY ds.schedule_date ASC, ds.room ASC, ds.start_time ASC";
    $stmt = $pdo->prepare($finalSql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
} catch (Exception $e) {
    error_log('get_defense_report.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error while fetching report.']);
    exit;
}
