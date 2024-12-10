<?php
require_once '../../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$table = isset($_GET['table']) ? $_GET['table'] : null;
if (!$table) {
    echo json_encode(['error' => 'No table specified.']);
    exit;
}

$allowedTables = ['users', 'thesis_topics', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations'];
if (!in_array($table, $allowedTables)) {
    echo json_encode(['error' => 'Invalid table.']);
    exit;
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    if ($table === 'defense_schedules') {
        $query = "
            SELECT 
        ds.id,
        ds.schedule_date,
        ds.start_time,
        ds.end_time,
        ds.room,
        t.name AS team_name,
        rt.title AS thesis_title,
        GROUP_CONCAT(
            DISTINCT CONCAT(u_student.first_name, ' ', u_student.last_name) 
            ORDER BY tm.id SEPARATOR ', '
        ) AS team_members,
        GROUP_CONCAT(
            DISTINCT CONCAT(u_panelist.first_name, ' ', u_panelist.last_name) 
            ORDER BY FIELD(ds.panelist_id, ds.panelist_id2, ds.panelist_id3) SEPARATOR ', '
        ) AS panelists,
        (SELECT CONCAT(u_adviser.first_name, ' ', u_adviser.last_name) 
         FROM team_members tm_adviser 
         JOIN users u_adviser ON tm_adviser.user_id = u_adviser.id 
         WHERE tm_adviser.team_id = t.id AND tm_adviser.role = 'adviser' 
         ORDER BY tm_adviser.id ASC LIMIT 1) AS adviser
    FROM defense_schedules ds
    JOIN teams t ON ds.team_id = t.id
    JOIN research_titles rt ON t.id = rt.team_id
    JOIN team_members tm ON t.id = tm.team_id
    JOIN users u_student ON tm.user_id = u_student.id AND u_student.usertype != 1 -- Exclude usertype == 1
    LEFT JOIN users u_panelist ON u_panelist.id IN (ds.panelist_id, ds.panelist_id2, ds.panelist_id3)
    GROUP BY ds.id, t.name, rt.title
    ORDER BY ds.schedule_date, ds.start_time
    LIMIT :limit OFFSET :offset;";

        $stmt = $pdo->prepare($query);
    } elseif ($table === 'teams') {
        $query = "
            SELECT 
    t.id,
    t.name,
    rt.title AS research_title,
    GROUP_CONCAT(
        DISTINCT CASE 
            WHEN u.usertype != 2 THEN CONCAT(u.first_name, ' ', u.last_name)
        END 
        ORDER BY tm.id SEPARATOR ', '
    ) AS team_members,
    GROUP_CONCAT(
        DISTINCT CASE 
            WHEN u.usertype = 2 THEN CONCAT(u.first_name, ' ', u.last_name)
        END 
        ORDER BY tm.id SEPARATOR ', '
    ) AS adviser
FROM 
    teams t
JOIN 
    research_titles rt ON t.id = rt.team_id
JOIN 
    team_members tm ON t.id = tm.team_id
JOIN 
    users u ON tm.user_id = u.id
GROUP BY 
    t.id, rt.title

            LIMIT :limit OFFSET :offset;
        ";

        $stmt = $pdo->prepare($query);
    } else {
        $query = "SELECT * FROM $table LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($query);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total rows for pagination
    if ($table === 'defense_schedules') {
        $countQuery = "
            SELECT COUNT(DISTINCT ds.id) 
            FROM defense_schedules ds 
            JOIN teams t ON ds.team_id = t.id
            JOIN research_titles rt ON t.id = rt.team_id";
    } else {
        $countQuery = "SELECT COUNT(*) FROM $table";
    }

    $countStmt = $pdo->query($countQuery);
    $total_rows = $countStmt->fetchColumn();
    $total_pages = ceil($total_rows / $limit);

    echo json_encode([
        'data' => $data,
        'total_pages' => $total_pages
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'stacktrace' => $e->getTrace(),
        'query' => $query,
        'parameters' => [
            'limit' => $limit,
            'offset' => $offset
        ]
    ]);
}
