<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../../assets/setup/db.inc.php';

$response = ['success' => false, 'message' => 'Invalid request', 'data' => null];

if (isset($_GET['id'])) {
    $rubricId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($rubricId) {
        try {
            // Fetch main rubric details
            $stmtRubric = $pdo->prepare("SELECT * FROM rubrics WHERE id = :id");
            $stmtRubric->execute([':id' => $rubricId]);
            $rubric = $stmtRubric->fetch(PDO::FETCH_ASSOC);

            if ($rubric) {
                // Fetch associated levels (ordered by index)
                $stmtLevels = $pdo->prepare("SELECT * FROM rubric_levels WHERE rubric_id = :rubric_id ORDER BY level_index ASC");
                $stmtLevels->execute([':rubric_id' => $rubricId]);
                $levels = $stmtLevels->fetchAll(PDO::FETCH_ASSOC);

                // Fetch associated criteria (ordered by index) - Only relevant for numerical/yesno
                $criteria = [];
                if ($rubric['rubric_type'] === 'numerical' || $rubric['rubric_type'] === 'yesno') {
                    $stmtCriteria = $pdo->prepare("SELECT * FROM rubric_criteria WHERE rubric_id = :rubric_id ORDER BY order_index ASC");
                    $stmtCriteria->execute([':rubric_id' => $rubricId]);
                    $criteria = $stmtCriteria->fetchAll(PDO::FETCH_ASSOC);
                }

                $response['success'] = true;
                $response['message'] = 'Rubric details fetched successfully.';
                $response['data'] = [
                    'rubric' => $rubric,
                    'levels' => $levels,
                    'criteria' => $criteria
                ];

            } else {
                $response['message'] = 'Rubric not found.';
            }
        } catch (PDOException $e) {
            error_log("Database Error fetching rubric details: " . $e->getMessage());
            $response['message'] = 'Database error occurred.';
        } catch (Exception $e) {
             error_log("General Error fetching rubric details: " . $e->getMessage());
            $response['message'] = 'An unexpected error occurred.';
        }
    } else {
        $response['message'] = 'Invalid Rubric ID provided.';
    }
} else {
    $response['message'] = 'Rubric ID not provided.';
}

echo json_encode($response);
exit;
?>