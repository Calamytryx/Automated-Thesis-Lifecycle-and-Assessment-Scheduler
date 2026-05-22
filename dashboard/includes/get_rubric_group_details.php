<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Group ID not provided or invalid.'];

$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($group_id) {
    try {
        // Fetch group details
        $groupStmt = $pdo->prepare("SELECT * FROM rubric_groups WHERE id = ?");
        $groupStmt->execute([$group_id]);
        $group = $groupStmt->fetch(PDO::FETCH_ASSOC);

        if ($group) {
            // Fetch associated rubrics (items) with their details
            $itemsStmt = $pdo->prepare("
                SELECT
                    rgi.rubric_id,
                    rgi.order_index,
                    rgi.weight,
                    r.name AS rubric_name,
                    r.rubric_type
                FROM rubric_group_items rgi
                JOIN rubrics r ON rgi.rubric_id = r.id
                WHERE rgi.group_id = ?
                ORDER BY rgi.order_index ASC
            ");
            $itemsStmt->execute([$group_id]);
            $group['rubrics'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            $response['success'] = true;
            $response['message'] = 'Group details fetched successfully.';
            $response['data'] = $group;
        } else {
            $response['message'] = 'Rubric group not found.';
        }

    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Error fetching rubric group details (ID: $group_id): " . $e->getMessage());
    }
}

echo json_encode($response);
?>
