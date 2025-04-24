<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../../assets/setup/db.inc.php';

$response = ['success' => false, 'message' => 'Invalid request', 'data' => null];

if (isset($_GET['id'])) {
    $rubricId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($rubricId) {
        error_log("Attempting to fetch details for rubric ID: " . $rubricId); // Log start
        try {
            $pdo->beginTransaction();
            error_log("Transaction started for rubric ID: " . $rubricId);

            // Fetch main rubric details (including is_individual_enabled and max_members)
            $stmtRubric = $pdo->prepare("SELECT * FROM rubrics WHERE id = :id");
            $stmtRubric->execute([':id' => $rubricId]);
            $rubric = $stmtRubric->fetch(PDO::FETCH_ASSOC);
            error_log("Fetched main rubric data for ID " . $rubricId . ". Found: " . ($rubric ? 'Yes' : 'No'));

            if ($rubric) {
                // Cast boolean flag for consistency if needed
                $rubric['is_individual_enabled'] = (bool)$rubric['is_individual_enabled'];

                // Fetch associated levels (ordered by index)
                $stmtLevels = $pdo->prepare("SELECT * FROM rubric_levels WHERE rubric_id = :rubric_id ORDER BY level_index ASC");
                $stmtLevels->execute([':rubric_id' => $rubricId]);
                $levels = $stmtLevels->fetchAll(PDO::FETCH_ASSOC);
                error_log("Fetched rubric levels for ID " . $rubricId . ". Count: " . count($levels));

                // Fetch associated criteria (ordered by index, including is_individual)
                $criteria = [];
                $stmtCriteria = $pdo->prepare("SELECT * FROM rubric_criteria WHERE rubric_id = :rubric_id ORDER BY order_index ASC");
                $stmtCriteria->execute([':rubric_id' => $rubricId]);
                $criteria = $stmtCriteria->fetchAll(PDO::FETCH_ASSOC);
                error_log("Fetched rubric criteria for ID " . $rubricId . ". Count: " . count($criteria));

                // Cast boolean flag for consistency if needed
                foreach ($criteria as &$crit) {
                    $crit['is_individual'] = (bool)$crit['is_individual'];
                }
                unset($crit); // Unset reference

                // Fetch associated programs
                $stmtPrograms = $pdo->prepare("SELECT program_name FROM rubric_programs WHERE rubric_id = :rubric_id");
                $stmtPrograms->execute([':rubric_id' => $rubricId]);
                $programs = $stmtPrograms->fetchAll(PDO::FETCH_COLUMN, 0);
                error_log("Fetched rubric programs for ID " . $rubricId . ". Count: " . count($programs));

                $pdo->commit(); // Commit if all queries were successful
                error_log("Transaction committed successfully for rubric ID: " . $rubricId);

                $response['success'] = true;
                $response['message'] = 'Rubric details fetched successfully.';
                $response['data'] = [
                    'rubric' => $rubric,
                    'levels' => $levels,
                    'criteria' => $criteria,
                    'programs' => $programs // Add programs to the response
                ];

            } else {
                throw new Exception("Rubric with ID " . $rubricId . " not found in rubrics table.");
            }
        } catch (Exception $e) {
            // Rollback transaction if started
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
                error_log("Transaction rolled back for rubric ID " . $rubricId . " due to error.");
            }
            // Log the detailed error message and potentially the stack trace
            $response['message'] = 'Error fetching details for rubric ID ' . $rubricId . ': ' . $e->getMessage();
            error_log("ERROR fetching rubric details for ID $rubricId: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString()); // Log detailed error + trace
        }
    } else {
        $response['message'] = 'Invalid Rubric ID provided.';
        error_log("Invalid Rubric ID provided in GET request: " . print_r($_GET['id'], true));
    }
} else {
    $response['message'] = 'Rubric ID not provided.';
    error_log("No Rubric ID provided in GET request.");
}

echo json_encode($response);
exit;
?>