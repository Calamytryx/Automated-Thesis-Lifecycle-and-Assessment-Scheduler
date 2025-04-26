<?php
require_once '../../../assets/setup/db.inc.php';

header('Content-Type: application/json');

$table = isset($_GET['table']) ? $_GET['table'] : null;
if (!$table) {
    echo json_encode(['error' => 'No table specified.']);
    exit;
}

$allowedTables = ['users', 'thesis_topics', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'programs'];
if (!in_array($table, $allowedTables)) {
    echo json_encode(['error' => 'Invalid table.']);
    exit;
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- add support for programs table ---
if ($table === 'programs') {
    $offset = ($page - 1) * $limit;
    $stmt = $pdo->prepare("
        SELECT id, college, department, name, specialization
        FROM programs
        ORDER BY id DESC
        LIMIT :offset, :perPage
    ");
    $stmt->bindValue(':offset',  $offset,   PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $limit,    PDO::PARAM_INT);
    $stmt->execute();
    $rows  = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = (int)$pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();

    echo json_encode([
        'data'         => $rows,
        'total_pages'  => ceil($total / $limit),
        'current_page' => $page
    ]);
    exit;
}

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
                    DISTINCT CASE 
                        WHEN u_member.usertype != 2 THEN CONCAT(u_member.first_name, ' ', u_member.last_name) 
                    END
                    ORDER BY tm.id SEPARATOR ', '
                ) AS team_members,
                GROUP_CONCAT(
                    DISTINCT CONCAT(u_panelist.first_name, ' ', u_panelist.last_name) 
                    ORDER BY FIELD(u_panelist.id, ds.panelist_id, ds.panelist_id2, ds.panelist_id3) SEPARATOR ', '
                ) AS panelists,
                (SELECT CONCAT(u_adviser.first_name, ' ', u_adviser.last_name) 
                 FROM team_members tm_adviser 
                 JOIN users u_adviser ON tm_adviser.user_id = u_adviser.id 
                 WHERE tm_adviser.team_id = t.id AND u_adviser.usertype = 2 -- Assuming usertype 2 is adviser
                 ORDER BY tm_adviser.id ASC LIMIT 1) AS adviser
            FROM defense_schedules ds
            JOIN teams t ON ds.team_id = t.id
            LEFT JOIN research_titles rt ON t.id = rt.team_id -- Join research_titles table
            LEFT JOIN team_members tm ON t.id = tm.team_id -- Join team_members for members
            LEFT JOIN users u_member ON tm.user_id = u_member.id -- Join users for members
            LEFT JOIN users u_panelist ON u_panelist.id IN (ds.panelist_id, ds.panelist_id2, ds.panelist_id3) -- Join users for panelists
            GROUP BY ds.id, t.name, rt.title -- Group by schedule ID
            ORDER BY ds.schedule_date DESC, ds.start_time ASC -- Keep original sorting
            LIMIT :limit OFFSET :offset";
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
        ";
        
        $conditions = [];
        $params = [];
        
        // Apply search filter if provided
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $searchTerm = '%' . $_GET['search'] . '%';
            $conditions[] = "(t.name LIKE :search_name OR rt.title LIKE :search_title)";
            $params[':search_name'] = $searchTerm;
            $params[':search_title'] = $searchTerm;
        }
        
        // Add WHERE clause if conditions exist
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $query .= " GROUP BY t.id, rt.title";
        
        // Add sorting
        $allowedSortFields = ['id', 'name'];
        $sortBy = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowedSortFields) ? $_GET['sort_by'] : 'id';
        $sortDir = isset($_GET['sort_dir']) && in_array(strtoupper($_GET['sort_dir']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_dir']) : 'DESC';
        
        $query .= " ORDER BY t.$sortBy $sortDir";
        $query .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($query);
        
        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
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
            $conditions[] = "(username LIKE :search_username OR email LIKE :search_email OR first_name LIKE :search_fname OR last_name LIKE :search_lname)";
            $params[':search_username'] = $searchTerm;
            $params[':search_email'] = $searchTerm;
            $params[':search_fname'] = $searchTerm;
            $params[':search_lname'] = $searchTerm;
        }

        // Add WHERE clause if conditions exist
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        // Add sorting
        $allowedSortFields = ['id', 'username', 'email', 'first_name', 'last_name', 'usertype'];
        $sortBy = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowedSortFields) ? $_GET['sort_by'] : 'id';
        $sortDir = isset($_GET['sort_dir']) && in_array(strtoupper($_GET['sort_dir']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_dir']) : 'DESC';
        
        $query .= " ORDER BY $sortBy $sortDir";
        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($query);

        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    } elseif ($table === 'thesis_topics') {
        $query = "SELECT * FROM thesis_topics";
        $conditions = [];
        $params = [];
        
        // Apply search filter if provided
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $searchTerm = '%' . $_GET['search'] . '%';
            $conditions[] = "(topic LIKE :search_topic OR description LIKE :search_desc)";
            $params[':search_topic'] = $searchTerm;
            $params[':search_desc'] = $searchTerm;
        }
        
        // Apply category filter if provided
        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $conditions[] = "category = :category";
            $params[':category'] = $_GET['category'];
        }
        
        // Add WHERE clause if conditions exist
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        
        // Add sorting
        $allowedSortFields = ['id', 'topic', 'category'];
        $sortBy = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowedSortFields) ? $_GET['sort_by'] : 'id';
        $sortDir = isset($_GET['sort_dir']) && in_array(strtoupper($_GET['sort_dir']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_dir']) : 'DESC';
        
        $query .= " ORDER BY $sortBy $sortDir";
        $query .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($query);
        
        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
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

        // Apply search filter if provided for count query
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $searchTerm = '%' . $_GET['search'] . '%';
            $countConditions[] = "(username LIKE :search_username OR email LIKE :search_email OR first_name LIKE :search_fname OR last_name LIKE :search_lname)";
            $countParams[':search_username'] = $searchTerm;
            $countParams[':search_email'] = $searchTerm;
            $countParams[':search_fname'] = $searchTerm;
            $countParams[':search_lname'] = $searchTerm;
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
    } elseif ($table === 'teams') {
        $countQuery = "
            SELECT COUNT(DISTINCT t.id) 
            FROM teams t
            JOIN research_titles rt ON t.id = rt.team_id
        ";
        
        $countConditions = [];
        $countParams = [];
        
        // Apply search filter if provided
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $searchTerm = '%' . $_GET['search'] . '%';
            $countConditions[] = "(t.name LIKE :search_name OR rt.title LIKE :search_title)";
            $countParams[':search_name'] = $searchTerm;
            $countParams[':search_title'] = $searchTerm;
        }
        
        // Add WHERE clause if conditions exist
        if (!empty($countConditions)) {
            $countQuery .= " WHERE " . implode(' AND ', $countConditions);
        }
        
        $countStmt = $pdo->prepare($countQuery);
        
        // Bind all parameters
        foreach ($countParams as $key => $value) {
            $countStmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        
        $countStmt->execute();
        $total_rows = $countStmt->fetchColumn();
        $total_pages = ceil($total_rows / $limit);
    } elseif ($table === 'thesis_topics') {
        $countQuery = "SELECT COUNT(*) FROM thesis_topics";
        $countConditions = [];
        $countParams = [];
        
        // Apply search filter if provided
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $searchTerm = '%' . $_GET['search'] . '%';
            $countConditions[] = "(topic LIKE :search_topic OR description LIKE :search_desc)";
            $countParams[':search_topic'] = $searchTerm;
            $countParams[':search_desc'] = $searchTerm;
        }
        
        // Apply category filter if provided
        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $countConditions[] = "category = :category";
            $countParams[':category'] = $_GET['category'];
        }
        
        // Add WHERE clause if conditions exist
        if (!empty($countConditions)) {
            $countQuery .= " WHERE " . implode(' AND ', $countConditions);
        }
        
        $countStmt = $pdo->prepare($countQuery);
        
        // Bind all parameters
        foreach ($countParams as $key => $value) {
            $countStmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        
        $countStmt->execute();
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