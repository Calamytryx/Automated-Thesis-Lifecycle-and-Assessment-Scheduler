<?php
require_once __DIR__ . '/../../../assets/setup/db.inc.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Failed to fetch rubric groups.', 'data' => []];

try {
    // Fetch groups and count rubrics in each group
    $stmt = $pdo->query("
        SELECT rg.id, rg.name, rg.description, COUNT(rgi.id) as rubric_count
        FROM rubric_groups rg
        LEFT JOIN rubric_group_items rgi ON rg.id = rgi.group_id
        GROUP BY rg.id, rg.name, rg.description
        ORDER BY rg.name ASC
    ");

    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['message'] = 'Rubric groups fetched successfully.';
    $response['data'] = $groups;

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("Error fetching rubric groups: " . $e->getMessage());
}

echo json_encode($response);
?>
