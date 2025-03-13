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
    } elseif ($table === 'evaluations') {
        $query = "
            SELECT 
                ep.id,
                ds.team_id,
                t.name AS team_name,
                e.first_name AS evaluator_first_name,
                e.last_name AS evaluator_last_name,
                s.first_name AS student_first_name,
                s.last_name AS student_last_name,
                ep.group_score,
                ep.solo_score,
                ep.total_score,
                ep.comments
            FROM 
                evaluation_per_panel ep
            JOIN 
                defense_schedules ds ON ep.defense_schedule_id = ds.id
            JOIN 
                teams t ON ds.team_id = t.id
            JOIN 
                users e ON ep.evaluator_id = e.id
            JOIN 
                users s ON ep.student_id = s.id
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($query);
    } elseif ($table === 'users') {
        $query = "SELECT * FROM users";
        $conditions = [];
        $params = [];

        // Conditionally apply usertype filter
        if (isset($_GET['usertype']) && $_GET['usertype'] !== '' && $_GET['usertype'] !== 'all') {
            $conditions[] = "usertype = :usertype";
            $params[':usertype'] = (int)$_GET['usertype'];
        }

        // Apply search filter if provided
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $searchTerm = '%' . $_GET['search'] . '%';
            $conditions[] = "(username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
            $params[':search'] = $searchTerm;
        }

        // Add WHERE clause if conditions exist
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($query);

        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    } else {
        $query = "SELECT * FROM $table LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($query);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total rows for pagination
    if ($table === 'users') {
        $countQuery = "SELECT COUNT(*) FROM users";
        $countConditions = [];
        $countParams = [];

        // Conditionally apply usertype filter
        if (isset($_GET['usertype']) && $_GET['usertype'] !== '' && $_GET['usertype'] !== 'all') {
            $countConditions[] = "usertype = :usertype";
            $countParams[':usertype'] = (int)$_GET['usertype'];
        }

        // Apply search filter if provided
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $searchTerm = '%' . $_GET['search'] . '%';
            $countConditions[] = "(username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
            $countParams[':search'] = $searchTerm;
        }

        // Add WHERE clause if conditions exist
        if (!empty($countConditions)) {
            $countQuery .= " WHERE " . implode(' AND ', $countConditions);
        }

        $countStmt = $pdo->prepare($countQuery);
        
        // Bind all parameters
        foreach ($countParams as $key => $value) {
            $countStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $countStmt->execute();
        $total_rows = $countStmt->fetchColumn();
        $total_pages = ceil($total_rows / $limit);
    } elseif ($table === 'defense_schedules') {
        $countQuery = "
            SELECT COUNT(DISTINCT ds.id) 
            FROM defense_schedules ds 
            JOIN teams t ON ds.team_id = t.id
            JOIN research_titles rt ON t.id = rt.team_id";
        $countStmt = $pdo->query($countQuery);
        $total_rows = $countStmt->fetchColumn();
        $total_pages = ceil($total_rows / $limit);
    } elseif ($table === 'evaluations') {
        $countQuery = "SELECT COUNT(*) FROM evaluation_per_panel";
        $countStmt = $pdo->query($countQuery);
        $total_rows = $countStmt->fetchColumn();
        $total_pages = ceil($total_rows / $limit);
    } else {
        $countQuery = "SELECT COUNT(*) FROM $table";
        $countStmt = $pdo->query($countQuery);
        $total_rows = $countStmt->fetchColumn();
        $total_pages = ceil($total_rows / $limit);
    }

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
