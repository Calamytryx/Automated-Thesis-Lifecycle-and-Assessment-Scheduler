<?php
/**
 * Class schedules for schedule preview overlay: rows from user_schedules that match
 * program + section of student members on the given teams only (same scope as conflict checks).
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/section_access.php';
require_once __DIR__ . '/edit_functions.php';

try {
    $usertype = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : -1;
    $userId = isset($_SESSION['id']) ? intval($_SESSION['id']) : -1;

    if (!userCanAccessDashboard($pdo, $userId, $usertype)) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized', 'data' => []]);
        exit;
    }

    $teamIds = [];
    $rawBody = file_get_contents('php://input');
    if ($rawBody !== false && trim($rawBody) !== '') {
        $input = json_decode($rawBody, true);
        if (is_array($input) && isset($input['team_ids']) && is_array($input['team_ids'])) {
            $teamIds = $input['team_ids'];
        }
    }
    if (empty($teamIds)) {
        $csv = isset($_GET['team_ids']) ? trim((string)$_GET['team_ids']) : '';
        if ($csv !== '') {
            $teamIds = array_map('intval', explode(',', $csv));
        }
    }

    $teamIds = array_values(array_unique(array_filter(array_map('intval', $teamIds), static function ($id) {
        return $id > 0;
    })));

    if (empty($teamIds)) {
        echo json_encode(['data' => []]);
        exit;
    }

    foreach ($teamIds as $tid) {
        if (!canUserAccessDefenseScheduleByTeam($pdo, $userId, $usertype, $tid)) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied for one or more teams.', 'data' => []]);
            exit;
        }
    }

    $placeholders = implode(',', array_fill(0, count($teamIds), '?'));

    $allowOverlapColumnExists = false;
    $isResearchClassColumnExists = false;
    try {
        $colStmt = $pdo->query("SHOW COLUMNS FROM user_schedules WHERE Field IN ('allow_overlap','is_research_class')");
        $foundCols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
        $allowOverlapColumnExists = in_array('allow_overlap', $foundCols, true);
        $isResearchClassColumnExists = in_array('is_research_class', $foundCols, true);
    } catch (Throwable $e) {
        error_log('get_preview_overlay_schedules column check failed: ' . $e->getMessage());
    }
    $allowOverlapExpr = $allowOverlapColumnExists ? 'COALESCE(us.allow_overlap, 0)' : '0';
    $isResearchClassExpr = $isResearchClassColumnExists ? 'COALESCE(us.is_research_class, 0)' : '0';

    /* Per-team rows: section template applies only to students on that specific team_id. */
    $sql = "
        SELECT DISTINCT
            tm.team_id AS applies_to_team_id,
            us.id,
            us.user_id,
            u_fac.first_name,
            u_fac.last_name,
            us.day_of_week,
            us.start_time,
            us.end_time,
            us.class_name,
            us.room,
            us.section,
            {$allowOverlapExpr} AS allow_overlap,
            {$isResearchClassExpr} AS is_research_class,
            p.name AS program_name,
            p.specialization
        FROM team_members tm
        JOIN users u_student ON u_student.id = tm.user_id AND u_student.usertype = 1
        LEFT JOIN programs p_student ON (
            u_student.program = p_student.name OR
            u_student.program = CONCAT(
                p_student.name,
                CASE
                    WHEN p_student.specialization IS NOT NULL AND p_student.specialization != ''
                    THEN CONCAT(' - ', p_student.specialization)
                    ELSE ''
                END
            )
        )
        INNER JOIN user_schedules us ON us.program = p_student.id
            AND TRIM(us.section) = TRIM(u_student.section)
        LEFT JOIN users u_fac ON us.user_id = u_fac.id
        LEFT JOIN programs p ON us.program = p.id
        WHERE tm.team_id IN ($placeholders)
          AND u_student.section IS NOT NULL
          AND TRIM(u_student.section) <> ''
          AND p_student.id IS NOT NULL
        ORDER BY tm.team_id, us.day_of_week, us.start_time
        LIMIT 4000
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($teamIds);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Personal timetable rows keyed to student user_id — matches fetchUserSchedules / merge templates. */
    $sqlPersonal = "
        SELECT DISTINCT
            tm.team_id AS applies_to_team_id,
            us.id,
            us.user_id,
            u_fac.first_name,
            u_fac.last_name,
            us.day_of_week,
            us.start_time,
            us.end_time,
            us.class_name,
            us.room,
            us.section,
            {$allowOverlapExpr} AS allow_overlap,
            {$isResearchClassExpr} AS is_research_class,
            p.name AS program_name,
            p.specialization
        FROM team_members tm
        JOIN users u_student ON u_student.id = tm.user_id AND u_student.usertype = 1
        INNER JOIN user_schedules us ON us.user_id = u_student.id
        LEFT JOIN users u_fac ON us.user_id = u_fac.id
        LEFT JOIN programs p ON us.program = p.id
        WHERE tm.team_id IN ($placeholders)
        ORDER BY tm.team_id, us.day_of_week, us.start_time
        LIMIT 4000
    ";
    $stmtP = $pdo->prepare($sqlPersonal);
    $stmtP->execute($teamIds);
    $rows = array_merge($rows, $stmtP->fetchAll(PDO::FETCH_ASSOC));

    // Fallback: timetable rows matched by student section only (broader overlay when program string vs programs.id linkage misses)
    if (empty($rows)) {
        $sqlSection = "
            SELECT DISTINCT
                tm.team_id AS applies_to_team_id,
                us.id,
                us.user_id,
                u_fac.first_name,
                u_fac.last_name,
                us.day_of_week,
                us.start_time,
                us.end_time,
                us.class_name,
                us.room,
                us.section,
                {$allowOverlapExpr} AS allow_overlap,
                {$isResearchClassExpr} AS is_research_class,
                p.name AS program_name,
                p.specialization
            FROM team_members tm
            JOIN users u_student ON u_student.id = tm.user_id AND u_student.usertype = 1
            INNER JOIN user_schedules us ON TRIM(us.section) = TRIM(u_student.section)
            LEFT JOIN users u_fac ON us.user_id = u_fac.id
            LEFT JOIN programs p ON us.program = p.id
            WHERE tm.team_id IN ($placeholders)
              AND u_student.section IS NOT NULL
              AND TRIM(u_student.section) <> ''
              AND COALESCE(TRIM(us.section), '') <> ''
            ORDER BY tm.team_id, us.day_of_week, us.start_time
            LIMIT 4000
        ";
        $stmt2 = $pdo->prepare($sqlSection);
        $stmt2->execute($teamIds);
        $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    $normalized = [];
    $seenKeys = [];
    foreach ($rows as $row) {
        $d = normalize_user_schedule_day_to_week_int($row['day_of_week'] ?? '');
        if ($d === null) {
            continue;
        }
        $tid = isset($row['applies_to_team_id']) ? (int) $row['applies_to_team_id'] : 0;
        $k = (string) ($row['id'] ?? $row['user_schedule_id'] ?? '') . '|' . $d . '|'
            . trim((string) $row['start_time']) . '|' . trim((string) $row['end_time']) . '|'
            . trim((string) ($row['class_name'] ?? '')) . '|' . trim((string) ($row['section'] ?? '')) . '|' . trim((string) ($row['room'] ?? ''));
        if (isset($seenKeys[$k])) {
            $existingIndex = $seenKeys[$k];
            if (!isset($normalized[$existingIndex]['applies_to_team_ids'])) {
                $normalized[$existingIndex]['applies_to_team_ids'] = [];
            }
            if ($tid > 0 && !in_array($tid, $normalized[$existingIndex]['applies_to_team_ids'], true)) {
                $normalized[$existingIndex]['applies_to_team_ids'][] = $tid;
            }
            continue;
        }
        $seenKeys[$k] = count($normalized);
        $row['day_of_week'] = $d;
        $row['applies_to_team_id'] = $tid > 0 ? $tid : null;
        $row['applies_to_team_ids'] = $tid > 0 ? [$tid] : [];
        $row['allow_overlap'] = (int) ($row['allow_overlap'] ?? 0);
        $row['is_research_class'] = (int) ($row['is_research_class'] ?? 0);
        $normalized[] = $row;
    }

    echo json_encode(['data' => $normalized]);
} catch (Throwable $e) {
    error_log('get_preview_overlay_schedules.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'data' => []]);
}
