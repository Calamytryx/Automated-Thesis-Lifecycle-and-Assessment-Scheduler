<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

// Set header to return JSON
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'] ?? null;

    // Remove table from $_POST data that will be used for insertion/update
    $data = $_POST;
    unset($data['table']);

    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables'];

    if (!$table || !in_array($table, $allowedTables)) {
        $response['message'] = 'Invalid table specified.';
        echo json_encode($response);
        exit;
    }

    // --- NEW Rubric Handling ---
    if ($table === 'rubrics') {
        error_log("=== START ADD RUBRIC (NEW SCHEMA) ===");
        error_log("Received POST data for rubric: " . print_r($data, true));

        $pdo->beginTransaction();
        try {
            // 1. Insert into `rubrics` table (Added defense_type)
            $rubricSql = "INSERT INTO rubrics (name, description, rubric_type, defense_type, rubric_description,
                                            pass_recommendation_text, fail_recommendation_text, fail_option_text,
                                            pass_threshold_1, pass_threshold_2, pass_threshold_3, max_total_score)
                          VALUES (:name, :description, :rubric_type, :defense_type, :rubric_description,
                                  :pass_recommendation_text, :fail_recommendation_text, :fail_option_text,
                                  :pass_threshold_1, :pass_threshold_2, :pass_threshold_3, :max_total_score)";
            $stmtRubric = $pdo->prepare($rubricSql);

            // Prepare data for main rubric insert
            $rubricData = [
                ':name' => $data['name'] ?? 'Unnamed Rubric',
                ':description' => $data['description'] ?? '',
                ':rubric_type' => $data['rubric_type'] ?? 'numerical',
                ':defense_type' => empty($data['defense_type']) ? null : $data['defense_type'], // Added defense_type (allow null)
                ':rubric_description' => $data['rubric_description'] ?? null,
                // Pass/Fail specific fields (use null if not provided or not passfail type)
                ':pass_recommendation_text' => ($data['rubric_type'] === 'passfail') ? ($data['pass_recommendation_text'] ?? null) : null,
                ':fail_recommendation_text' => ($data['rubric_type'] === 'passfail') ? ($data['fail_recommendation_text'] ?? null) : null,
                ':fail_option_text' => ($data['rubric_type'] === 'passfail') ? ($data['fail_option_text'] ?? null) : null,
                ':pass_threshold_1' => ($data['rubric_type'] === 'passfail') ? ($data['total_pass'] ?? null) : null, // Note name change total_pass -> pass_threshold_1
                ':pass_threshold_2' => ($data['rubric_type'] === 'passfail') ? ($data['minor_revision_pass'] ?? null) : null, // Note name change minor_revision_pass -> pass_threshold_2
                ':pass_threshold_3' => ($data['rubric_type'] === 'passfail') ? ($data['major_revision_pass'] ?? null) : null, // Note name change major_revision_pass -> pass_threshold_3
                ':max_total_score' => ($data['rubric_type'] === 'numerical') ? ($data['max_total_score'] ?? 0) : 0 // Only relevant for numerical
            ];

            $stmtRubric->execute($rubricData);
            $rubricId = $pdo->lastInsertId();
            error_log("Inserted into rubrics table. ID: " . $rubricId);

            // 2. Insert into `rubric_levels` (Numerical levels or Pass/Fail modifiers)
            if (isset($data['levels'])) {
                $levels = json_decode($data['levels'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($levels)) {
                    $levelSql = "INSERT INTO rubric_levels (rubric_id, level_index, name, description, points_min, points_max, is_range)
                                 VALUES (:rubric_id, :level_index, :name, :description, :points_min, :points_max, :is_range)";
                    $stmtLevel = $pdo->prepare($levelSql);

                    foreach ($levels as $level) {
                        $stmtLevel->execute([
                            ':rubric_id' => $rubricId,
                            ':level_index' => $level['level_index'] ?? 0,
                            ':name' => $level['name'] ?? 'Unnamed Level',
                            ':description' => $level['description'] ?? null,
                            // Numerical specific fields (null if not numerical)
                            ':points_min' => ($data['rubric_type'] === 'numerical') ? ($level['points_min'] ?? null) : null,
                            ':points_max' => ($data['rubric_type'] === 'numerical') ? ($level['points_max'] ?? null) : null,
                            ':is_range' => ($data['rubric_type'] === 'numerical') ? ($level['is_range'] ?? 0) : 0,
                        ]);
                    }
                    error_log("Inserted " . count($levels) . " rows into rubric_levels.");
                } else {
                    error_log("Failed to decode levels JSON or it's not an array. Error: " . json_last_error_msg());
                }
            } else {
                 error_log("No 'levels' data found in POST.");
            }

            // 3. Insert into `rubric_criteria` (Numerical or Yes/No rows)
            if (($data['rubric_type'] === 'numerical' || $data['rubric_type'] === 'yesno') && isset($data['criteria'])) {
                $criteria = json_decode($data['criteria'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($criteria)) {
                    $criteriaSql = "INSERT INTO rubric_criteria (rubric_id, criterion_text, criterion_detail, order_index)
                                    VALUES (:rubric_id, :criterion_text, :criterion_detail, :order_index)";
                    $stmtCriteria = $pdo->prepare($criteriaSql);

                    foreach ($criteria as $criterion) {
                        $stmtCriteria->execute([
                            ':rubric_id' => $rubricId,
                            ':criterion_text' => $criterion['criterion_text'] ?? 'Unnamed Criterion',
                            ':criterion_detail' => $criterion['criterion_detail'] ?? null, // Used by Yes/No
                            ':order_index' => $criterion['order_index'] ?? 0,
                        ]);
                    }
                     error_log("Inserted " . count($criteria) . " rows into rubric_criteria.");
                } else {
                     error_log("Failed to decode criteria JSON or it's not an array. Error: " . json_last_error_msg());
                }
            } else {
                 error_log("No 'criteria' data found in POST or type is not numerical/yesno.");
            }

            // 4. Insert into `rubric_programs`
            if (isset($data['programs'])) {
                $programs = json_decode($data['programs'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($programs) && !empty($programs)) {
                    $programSql = "INSERT INTO rubric_programs (rubric_id, program_name) VALUES (:rubric_id, :program_name)";
                    $stmtProgram = $pdo->prepare($programSql);
                    foreach ($programs as $programName) {
                        if (!empty($programName)) { // Ensure program name is not empty
                            $stmtProgram->execute([
                                ':rubric_id' => $rubricId,
                                ':program_name' => $programName
                            ]);
                        }
                    }
                    error_log("Inserted " . count($programs) . " rows into rubric_programs.");
                } else {
                    error_log("Failed to decode programs JSON, it's not an array, or it's empty. Error: " . json_last_error_msg());
                }
            } else {
                error_log("No 'programs' data found in POST.");
            }

            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'Rubric added successfully.';
            error_log("=== END ADD RUBRIC (NEW SCHEMA) - SUCCESS ===");

        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = 'Error adding rubric: ' . $e->getMessage();
            error_log("Error occurred, transaction rolled back: " . $e->getMessage());
            error_log("=== END ADD RUBRIC (NEW SCHEMA) - ERROR ===");
        }

        echo json_encode($response);
        exit; // Stop script after handling rubric
    }
    // --- END NEW Rubric Handling ---

    // Special handling for research_titles
    if ($table === 'research_titles') {
        $approved = isset($data['approved']) ? date('Y-m-d H:i:s') : null;
        unset($data['approved']);
        $data['approved_at'] = $approved;
    }

    // Special handling for teams
    if ($table === 'teams') {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO teams (name, area_of_expertise, program) VALUES (:name, :area_of_expertise, :program)");
            $stmt->execute([
                'name' => $data['name'],
                'area_of_expertise' => $data['area_of_expertise'] ?? null,
                'program' => $data['program'] ?? null
            ]);

            $teamId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO research_titles (id, team_id, title) VALUES (:id, :team_id, :title)");
            $stmt->execute([
                'id' => $teamId,
                'team_id' => $teamId,
                'title' => $data['name']
            ]);

            if (isset($data['members']) && !empty($data['members'])) {
                $members = $data['members'];
                if (is_string($members)) {
                    $members = json_decode($members, true);
                }

                if (is_array($members)) {
                    foreach ($members as $member) {
                        if (isset($member['id']) && isset($member['role'])) {
                            $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
                            $stmt->execute([
                                'team_id' => $teamId,
                                'user_id' => $member['id'],
                                'role' => $member['role']
                            ]);
                        }
                    }
                }
            }

            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'Team added successfully.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = 'Error adding team: ' . $e->getMessage();
        }

        echo json_encode($response);
        exit;
    }

    // Special handling for users
    if ($table === 'users') {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
    }

    if ($table === 'defense_schedules') {
        $panelist_ids = isset($data['panelist_id']) ? (array)$data['panelist_id'] : [];
        $data['panelist_id']  = $panelist_ids[0] ?? null;
        $data['panelist_id2'] = $panelist_ids[1] ?? null;
        $data['panelist_id3'] = $panelist_ids[2] ?? null;
    }

    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));

    $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");

    try {
        $stmt->execute($data);
        $response['success'] = true;
        $response['message'] = ucfirst($table) . ' added successfully.';
    } catch (Exception $e) {
        $response['message'] = 'Error adding ' . $table . ': ' . $e->getMessage();
    }

    echo json_encode($response);
} else {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
}
?>