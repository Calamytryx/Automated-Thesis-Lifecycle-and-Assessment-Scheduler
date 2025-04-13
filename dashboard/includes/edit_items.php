<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

// Set header to return JSON
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'] ?? null;
    $id = $_POST['id'] ?? null;

    // Remove table and id from $_POST data that will be used for update
    $data = $_POST;
    unset($data['table'], $data['id']);

    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables'];

    if (!$table || !in_array($table, $allowedTables)) {
        $response['message'] = 'Invalid table specified.';
        echo json_encode($response);
        exit;
    }
    if (!$id) {
        $response['message'] = 'Item ID not provided.';
        echo json_encode($response);
        exit;
    }

    // --- NEW Rubric Handling ---
    if ($table === 'rubrics') {
        error_log("=== START EDIT RUBRIC (NEW SCHEMA) ID: $id ===");
        error_log("Received POST data for rubric update: " . print_r($data, true));

        $pdo->beginTransaction();
        try {
            // 1. Update the main `rubrics` table (Added defense_type)
            $rubricSql = "UPDATE rubrics SET
                            name = :name,
                            description = :description,
                            rubric_type = :rubric_type,
                            defense_type = :defense_type,
                            rubric_description = :rubric_description,
                            pass_recommendation_text = :pass_recommendation_text,
                            fail_recommendation_text = :fail_recommendation_text,
                            fail_option_text = :fail_option_text,
                            pass_threshold_1 = :pass_threshold_1,
                            pass_threshold_2 = :pass_threshold_2,
                            pass_threshold_3 = :pass_threshold_3,
                            max_total_score = :max_total_score
                          WHERE id = :id";
            $stmtRubric = $pdo->prepare($rubricSql);

            // Prepare data for main rubric update
            $rubricData = [
                ':id' => $id,
                ':name' => $data['name'] ?? 'Unnamed Rubric',
                ':description' => $data['description'] ?? '',
                ':rubric_type' => $data['rubric_type'] ?? 'numerical',
                ':defense_type' => empty($data['defense_type']) ? null : $data['defense_type'], // Added defense_type (allow null)
                ':rubric_description' => $data['rubric_description'] ?? null,
                // Pass/Fail specific fields
                ':pass_recommendation_text' => ($data['rubric_type'] === 'passfail') ? ($data['pass_recommendation_text'] ?? null) : null,
                ':fail_recommendation_text' => ($data['rubric_type'] === 'passfail') ? ($data['fail_recommendation_text'] ?? null) : null,
                ':fail_option_text' => ($data['rubric_type'] === 'passfail') ? ($data['fail_option_text'] ?? null) : null,
                ':pass_threshold_1' => ($data['rubric_type'] === 'passfail') ? ($data['total_pass'] ?? null) : null,
                ':pass_threshold_2' => ($data['rubric_type'] === 'passfail') ? ($data['minor_revision_pass'] ?? null) : null,
                ':pass_threshold_3' => ($data['rubric_type'] === 'passfail') ? ($data['major_revision_pass'] ?? null) : null,
                ':max_total_score' => ($data['rubric_type'] === 'numerical') ? ($data['max_total_score'] ?? 0) : 0
            ];

            $stmtRubric->execute($rubricData);
            error_log("Updated rubrics table for ID: " . $id);

            // 2. Delete existing `rubric_levels` for this rubric
            $stmtDeleteLevels = $pdo->prepare("DELETE FROM rubric_levels WHERE rubric_id = :rubric_id");
            $stmtDeleteLevels->execute([':rubric_id' => $id]);
            error_log("Deleted existing rubric_levels for ID: " . $id);

            // 3. Insert new `rubric_levels`
            if (isset($data['levels'])) {
                $levels = json_decode($data['levels'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($levels)) {
                    $levelSql = "INSERT INTO rubric_levels (rubric_id, level_index, name, description, points_min, points_max, is_range)
                                 VALUES (:rubric_id, :level_index, :name, :description, :points_min, :points_max, :is_range)";
                    $stmtLevel = $pdo->prepare($levelSql);

                    foreach ($levels as $level) {
                        $stmtLevel->execute([
                            ':rubric_id' => $id, // Use the existing rubric ID
                            ':level_index' => $level['level_index'] ?? 0,
                            ':name' => $level['name'] ?? 'Unnamed Level',
                            ':description' => $level['description'] ?? null,
                            ':points_min' => ($data['rubric_type'] === 'numerical') ? ($level['points_min'] ?? null) : null,
                            ':points_max' => ($data['rubric_type'] === 'numerical') ? ($level['points_max'] ?? null) : null,
                            ':is_range' => ($data['rubric_type'] === 'numerical') ? ($level['is_range'] ?? 0) : 0,
                        ]);
                    }
                    error_log("Inserted " . count($levels) . " new rows into rubric_levels for ID: " . $id);
                } else {
                     error_log("Failed to decode levels JSON or it's not an array for update. Error: " . json_last_error_msg());
                }
            } else {
                 error_log("No 'levels' data found in POST for update.");
            }

            // 4. Delete existing `rubric_criteria` for this rubric
            $stmtDeleteCriteria = $pdo->prepare("DELETE FROM rubric_criteria WHERE rubric_id = :rubric_id");
            $stmtDeleteCriteria->execute([':rubric_id' => $id]);
            error_log("Deleted existing rubric_criteria for ID: " . $id);

            // 5. Insert new `rubric_criteria` (Numerical or Yes/No rows)
            if (($data['rubric_type'] === 'numerical' || $data['rubric_type'] === 'yesno') && isset($data['criteria'])) {
                $criteria = json_decode($data['criteria'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($criteria)) {
                    $criteriaSql = "INSERT INTO rubric_criteria (rubric_id, criterion_text, criterion_detail, order_index)
                                    VALUES (:rubric_id, :criterion_text, :criterion_detail, :order_index)";
                    $stmtCriteria = $pdo->prepare($criteriaSql);

                    foreach ($criteria as $criterion) {
                        $stmtCriteria->execute([
                            ':rubric_id' => $id, // Use the existing rubric ID
                            ':criterion_text' => $criterion['criterion_text'] ?? 'Unnamed Criterion',
                            ':criterion_detail' => $criterion['criterion_detail'] ?? null,
                            ':order_index' => $criterion['order_index'] ?? 0,
                        ]);
                    }
                    error_log("Inserted " . count($criteria) . " new rows into rubric_criteria for ID: " . $id);
                } else {
                     error_log("Failed to decode criteria JSON or it's not an array for update. Error: " . json_last_error_msg());
                }
            } else {
                 error_log("No 'criteria' data found in POST or type is not numerical/yesno for update.");
            }

            // 6. Delete existing `rubric_programs` for this rubric
            $stmtDeletePrograms = $pdo->prepare("DELETE FROM rubric_programs WHERE rubric_id = :rubric_id");
            $stmtDeletePrograms->execute([':rubric_id' => $id]);
            error_log("Deleted existing rubric_programs for ID: " . $id);

            // 7. Insert new `rubric_programs`
            if (isset($data['programs'])) {
                $programs = json_decode($data['programs'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($programs) && !empty($programs)) {
                    $programSql = "INSERT INTO rubric_programs (rubric_id, program_name) VALUES (:rubric_id, :program_name)";
                    $stmtProgram = $pdo->prepare($programSql);
                    foreach ($programs as $programName) {
                         if (!empty($programName)) { // Ensure program name is not empty
                            $stmtProgram->execute([
                                ':rubric_id' => $id, // Use the existing rubric ID
                                ':program_name' => $programName
                            ]);
                        }
                    }
                    error_log("Inserted " . count($programs) . " new rows into rubric_programs for ID: " . $id);
                } else {
                     error_log("Failed to decode programs JSON, it's not an array, or it's empty for update. Error: " . json_last_error_msg());
                }
            } else {
                 error_log("No 'programs' data found in POST for update.");
            }

            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'Rubric updated successfully.';
            error_log("=== END EDIT RUBRIC (NEW SCHEMA) - SUCCESS ===");

        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = 'Error updating rubric: ' . $e->getMessage();
            error_log("Error occurred, transaction rolled back: " . $e->getMessage());
            error_log("=== END EDIT RUBRIC (NEW SCHEMA) - ERROR ===");
        }

        echo json_encode($response);
        exit; // Stop script after handling rubric
    }
    // --- END NEW Rubric Handling ---

    // For other tables, use the generic handler (ensure edit_functions.php is updated if needed)
    require_once __DIR__ . '/edit_functions.php'; // Make sure this file exists and functions are correct

    // Check if handleEditSubmission exists and call it
    if (function_exists('handleEditSubmission')) {
        $result = handleEditSubmission($pdo, $table, $id, $data); // Pass $data instead of $_POST
        if ($result !== false) {
            $response['success'] = true;
            $response['message'] = ucfirst($table) . ' updated successfully.';
        } else {
            $response['message'] = ucfirst($table) . ' update failed.';
        }
    } else {
         $response['message'] = 'Update handler function not found.';
         error_log("handleEditSubmission function not found in edit_functions.php");
    }

    echo json_encode($response);

} else {
     $response['message'] = 'Invalid request method.';
     echo json_encode($response);
}
?>