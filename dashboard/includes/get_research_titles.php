<?php
/**
 * AJAX handler for research titles operations
 * Supports fetching, searching, sorting, and pagination for research titles
 */

require_once '../../assets/setup/db.inc.php';
require_once '../../assets/includes/auth_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['usertype'] != 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$userId = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Get request parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'id:desc';
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 10);
$offset = ($page - 1) * $limit;

try {
    // Parse sort parameter
    $sortParts = explode(':', $sort);
    $sortColumn = $sortParts[0] ?? 'id';
    $sortDirection = strtoupper($sortParts[1] ?? 'DESC');

    // Validate sort column
    $allowedSortColumns = ['id', 'title', 'team_id', 'approved_at', 'updated_at'];
    if (!in_array($sortColumn, $allowedSortColumns)) {
        $sortColumn = 'id';
    }

    // Validate sort direction
    if (!in_array($sortDirection, ['ASC', 'DESC'])) {
        $sortDirection = 'DESC';
    }

    // Build base query with joins
    $baseQuery = "
        FROM research_titles rt
        LEFT JOIN teams t ON rt.team_id = t.id
        LEFT JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization != '' 
            THEN CONCAT(' - ', p.specialization) ELSE '' END)
    ";

    // Add college restriction for non-super admins
    $whereCondition = "WHERE 1=1";
    $queryParams = [];

    if ($userId !== 0) {
        // Get admin's college
        $userCollege = get_user_college($pdo, $userId);
        if ($userCollege) {
            $whereCondition .= " AND (p.college = :college OR rt.team_id IS NULL)";
            $queryParams[':college'] = $userCollege;
        }
    }

    // Add search condition
    if (!empty($search)) {
        $whereCondition .= " AND (
            rt.title LIKE :search OR 
            t.name LIKE :search OR
            CASE 
                WHEN rt.approved_at IS NOT NULL THEN 'approved'
                ELSE 'pending'
            END LIKE :search
        )";
        $queryParams[':search'] = "%$search%";
    }

    // Get total count
    $countQuery = "SELECT COUNT(*) as total " . $baseQuery . " " . $whereCondition;
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($queryParams);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get research titles data
    $dataQuery = "
        SELECT 
            rt.id,
            rt.team_id,
            rt.title,
            rt.approved_at,
            rt.updated_at,
            t.name as team_name,
            p.college
        " . $baseQuery . " " . $whereCondition . "
        ORDER BY rt.$sortColumn $sortDirection
        LIMIT :limit OFFSET :offset
    ";

    $dataStmt = $pdo->prepare($dataQuery);
    foreach ($queryParams as $key => $value) {
        $dataStmt->bindValue($key, $value);
    }
    $dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->execute();

    $researchTitles = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data for display
    $formattedTitles = array_map(function($title) {
        $isApproved = $title['approved_at'] ? true : false;
        return [
            'id' => $title['id'],
            'team_id' => $title['team_id'],
            'title' => $title['title'],
            'team_name' => $title['team_name'] ?? 'No Team Assigned',
            'approved_at' => $title['approved_at'],
            'updated_at' => $title['updated_at'],
            'status_text' => $isApproved ? 'Approved' : 'Pending',
            'status_class' => $isApproved ? 'approved' : 'pending',
            'status_date' => date('Y-m-d H:i:s', strtotime($title['updated_at']))
        ];
    }, $researchTitles);

    $totalPages = ceil($totalCount / $limit);

    echo json_encode([
        'success' => true,
        'data' => $formattedTitles,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
            'limit' => $limit,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_research_titles.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching research titles: ' . $e->getMessage()
    ]);
}
?>
