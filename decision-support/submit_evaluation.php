<?php
// Include database connection
require '../assets/setup/db.inc.php';
// session_start(); // Ensure session is started

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Basic data
    $defense_schedule_id = filter_input(INPUT_POST, 'defense_schedule_id', FILTER_VALIDATE_INT);
    $evaluator_id = filter_input(INPUT_POST, 'evaluator_id', FILTER_VALIDATE_INT);
    $rubric_group_id = filter_input(INPUT_POST, 'rubric_group_id', FILTER_VALIDATE_INT);
    $comments = filter_input(INPUT_POST, 'comments', FILTER_UNSAFE_RAW);
    $comments = htmlspecialchars($comments, ENT_QUOTES, 'UTF-8');
    $evaluation_data_json = $_POST['evaluation_data'] ?? null;

    // Validate basic data
    if (!$defense_schedule_id || !$evaluator_id || !$rubric_group_id || $evaluation_data_json === null) {
        $response['message'] = 'Missing required data (schedule, evaluator, group, or evaluation data).';
        echo json_encode($response);
        exit;
    }

    // Decode JSON data
    $evaluation_data = json_decode($evaluation_data_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($evaluation_data)) {
        $response['message'] = 'Invalid evaluation data format.';
        echo json_encode($response);
        exit;
    }

    // TODO: Fetch student IDs associated with the defense schedule/team
    // This part needs to be implemented based on your team/schedule structure
    // Example: Fetching student IDs based on team_id from defense_schedule
     $teamIdStmt = $pdo->prepare("SELECT team_id FROM defense_schedules WHERE id = ?");
     $teamIdStmt->execute([$defense_schedule_id]);
     $team_id = $teamIdStmt->fetchColumn();

     $studentIds = [];
     if ($team_id) {
         $membersStmt = $pdo->prepare("SELECT user_id FROM team_members WHERE team_id = ? AND role != 'Adviser'");
         $membersStmt->execute([$team_id]);
         $studentIds = $membersStmt->fetchAll(PDO::FETCH_COLUMN, 0);
     }

    if (empty($studentIds)) {
         $response['message'] = 'Could not find students for this defense schedule.';
         echo json_encode($response);
         exit;
    }


    // --- Start Transaction ---
    $pdo->beginTransaction();
    try {
        // 1. Insert into `evaluation_per_panel` (once per evaluator per schedule)
        // We might need to handle potential duplicates if the evaluator submits again.
        // For simplicity, let's assume one submission or overwrite/ignore duplicates.
        // A more robust approach would check existence first.

        // Check if an evaluation already exists for this evaluator and schedule
        $checkStmt = $pdo->prepare("SELECT id FROM evaluation_per_panel WHERE defense_schedule_id = :ds_id AND evaluator_id = :eval_id");
        $checkStmt->execute([':ds_id' => $defense_schedule_id, ':eval_id' => $evaluator_id]);
        $existing_evaluation_id = $checkStmt->fetchColumn();

        $evaluation_id = null;

        if ($existing_evaluation_id) {
            // Option 1: Update existing evaluation (e.g., update comments, timestamp)
            $evalStmt = $pdo->prepare("UPDATE evaluation_per_panel SET comments = :comments, updated_at = NOW() WHERE id = :id");
            $evalStmt->execute([':comments' => $comments, ':id' => $existing_evaluation_id]);
            $evaluation_id = $existing_evaluation_id;
            // Option 2: Delete old details before inserting new ones (if overwriting)
             $delDetailsStmt = $pdo->prepare("DELETE FROM evaluation_details WHERE evaluation_id = ?");
             $delDetailsStmt->execute([$evaluation_id]);

        } else {
            // Insert new evaluation record
            // Note: group_score, solo_score, total_score might be deprecated or calculated differently now.
            // Store the overall comment here. Detailed scores go into evaluation_details.
            $evalStmt = $pdo->prepare("
                INSERT INTO evaluation_per_panel
                (defense_schedule_id, evaluator_id, comments, created_at, updated_at)
                VALUES (:defense_schedule_id, :evaluator_id, :comments, NOW(), NOW())
            ");
            $evalStmt->execute([
                ':defense_schedule_id' => $defense_schedule_id,
                ':evaluator_id' => $evaluator_id,
                ':comments' => $comments,
            ]);
            $evaluation_id = $pdo->lastInsertId();
        }

        if (!$evaluation_id) {
             throw new Exception("Failed to create or find evaluation record.");
        }


        // 2. Insert into `evaluation_details`
        $detailStmt = $pdo->prepare("
            INSERT INTO evaluation_details
            (evaluation_id, rubric_id, criterion_id, score, selected_option, created_at, updated_at)
            VALUES (:eval_id, :rubric_id, :crit_id, :score, :option, NOW(), NOW())
        ");

        foreach ($evaluation_data as $rubric_id => $data) {
            $rubric_type = $data['type'];
            $scores = $data['scores'];

            foreach ($scores as $key => $value) {
                $criterion_id = null;
                $score = null;
                $option = null;

                if ($rubric_type === 'numerical' || $rubric_type === 'yesno') {
                    $criterion_id = filter_var($key, FILTER_VALIDATE_INT);
                    if ($rubric_type === 'numerical') {
                        $score = filter_var($value, FILTER_VALIDATE_FLOAT);
                    } else { // yesno
                        $option = filter_var($value, FILTER_SANITIZE_STRING);
                    }
                } elseif ($rubric_type === 'passfail') {
                    // For pass/fail, $key is likely 'passfail'
                    $criterion_id = null; // No specific criterion
                    $option = filter_var($value, FILTER_SANITIZE_STRING); // Value is 'pass_1', 'fail', etc.
                }

                 // Basic validation before inserting
                 if (!is_numeric($rubric_id)) continue; // Skip if rubric_id is invalid

                $detailStmt->execute([
                    ':eval_id' => $evaluation_id,
                    ':rubric_id' => $rubric_id,
                    ':crit_id' => $criterion_id, // Can be null
                    ':score' => $score,         // Can be null
                    ':option' => $option        // Can be null
                ]);
            }
        }

        // --- Commit Transaction ---
        $pdo->commit();
        $response['status'] = 'success';
        $response['message'] = 'Evaluation submitted successfully.';

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Evaluation Submission Error: " . $e->getMessage()); // Log detailed error
        $response['message'] = 'An error occurred while submitting the evaluation: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit;
} else {
     echo json_encode($response);
     exit;
}
?>
