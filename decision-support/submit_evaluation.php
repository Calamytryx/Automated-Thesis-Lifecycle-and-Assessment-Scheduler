<?php
// evaluation_submit.php
// Include database connection and start session
require '../assets/setup/db.inc.php';
session_start();

// Return JSON
header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Invalid request.'];

// Enable PDO exceptions for error visibility
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gather inputs
    $defense_schedule_id   = filter_input(INPUT_POST, 'defense_schedule_id', FILTER_VALIDATE_INT);
    $evaluator_id          = $_SESSION['id'] ?? null;
    $rubric_group_id       = filter_input(INPUT_POST, 'rubric_group_id', FILTER_VALIDATE_INT);
    $comments              = filter_input(INPUT_POST, 'comments', FILTER_UNSAFE_RAW);
    $comments              = htmlspecialchars($comments, ENT_QUOTES, 'UTF-8');
    $evaluation_data_json  = $_POST['evaluation_data'] ?? null;

    // Validate required params
    if (!$defense_schedule_id || !$evaluator_id || !$rubric_group_id || $evaluation_data_json === null) {
        $missing = [];
        if (!$defense_schedule_id)   $missing[] = 'Schedule ID';
        if (!$evaluator_id)          $missing[] = 'Evaluator ID (Session)';
        if (!$rubric_group_id)       $missing[] = 'Group ID';
        if ($evaluation_data_json === null) $missing[] = 'Evaluation Data';

        $response['message'] = 'Missing parameters: ' . implode(', ', $missing);
        echo json_encode($response);
        exit;
    }

    // Decode evaluation data
    $evaluation_data = json_decode($evaluation_data_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($evaluation_data)) {
        $response['message'] = 'Invalid evaluation data format.';
        echo json_encode($response);
        exit;
    }

    // Fetch team and student IDs
    $teamIdStmt = $pdo->prepare("SELECT team_id FROM defense_schedules WHERE id = ?");
    $teamIdStmt->execute([$defense_schedule_id]);
    $team_id = $teamIdStmt->fetchColumn();

    $studentIds = [];
    if ($team_id) {
        $membersStmt = $pdo->prepare(
            "SELECT user_id FROM team_members WHERE team_id = ? AND role != 'Adviser'"
        );
        $membersStmt->execute([$team_id]);
        $studentIds = $membersStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    if (empty($studentIds)) {
        $response['message'] = 'Could not find students for this defense schedule.';
        echo json_encode($response);
        exit;
    }

    // Begin transaction
    $pdo->beginTransaction();
    try {
        // Fetch rubric weights
        $weightsStmt = $pdo->prepare(
            "SELECT rubric_id, weight FROM rubric_group_items WHERE group_id = ?"
        );
        $weightsStmt->execute([$rubric_group_id]);
        $rubricWeights = [];
        while ($row = $weightsStmt->fetch(PDO::FETCH_ASSOC)) {
            $rubricWeights[$row['rubric_id']] = floatval($row['weight']) / 100;
        }

        // Fetch rubric metadata
        $rubricIds = array_keys($evaluation_data);
        $placeholders = implode(',', array_fill(0, count($rubricIds), '?'));
        $rubricDetails = [];
        if (!empty($rubricIds)) {
            $infoStmt = $pdo->prepare(
                "SELECT id, rubric_type, is_individual_enabled,
                    (SELECT COUNT(*) FROM rubric_criteria WHERE rubric_id = rubrics.id) AS criteria_count
                 FROM rubrics WHERE id IN ($placeholders)"
            );
            $infoStmt->execute($rubricIds);
            while ($row = $infoStmt->fetch(PDO::FETCH_ASSOC)) {
                $rubricDetails[(int)$row['id']] = [
                    'rubric_type'           => $row['rubric_type'],
                    'is_individual_enabled' => (bool)$row['is_individual_enabled'],
                    'criteria_count'        => (int)$row['criteria_count']
                ];
            }
        }

        // 1. Calculate group scores
        $groupScores = [];
        $totalWeightedScore = 0;
        foreach ($evaluation_data as $rid => $data) {
            $rid = (int)$rid;
            if (!isset($rubricDetails[$rid]) || !isset($rubricWeights[$rid])) continue;
            if ($rubricDetails[$rid]['rubric_type'] === 'numerical') {
                $criteriaCount = $rubricDetails[$rid]['criteria_count'];
                $sum = 0;
                $count = 0;
                if (isset($data['scores']) && is_array($data['scores'])) {
                    foreach ($data['scores'] as $val) {
                        if (!is_array($val) || isset($val['group'])) {
                            $score = is_array($val)
                                ? filter_var($val['group'], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE)
                                : filter_var($val, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                            if ($score !== null) {
                                $sum += $score;
                                $count++;
                            }
                        }
                    }
                }
                if ($count > 0) {
                    $rubricScore = ($sum / ($count * 10)) * 100;
                    $groupScores[$rid] = $rubricScore;
                    $totalWeightedScore += $rubricScore * $rubricWeights[$rid];
                }
            }
        }

        // 2. Calculate solo (individual) scores
        $soloScores = array_fill_keys($studentIds, 0.0);
        foreach ($evaluation_data as $rid => $data) {
            $rid = (int)$rid;
            if (!isset($rubricDetails[$rid]) || !isset($rubricWeights[$rid])) continue;
            $meta = $rubricDetails[$rid];
            if ($meta['rubric_type'] === 'numerical' && $meta['is_individual_enabled']) {
                $maxScoreTotal = $meta['criteria_count'] * 10;
                $weight = $rubricWeights[$rid];
                foreach ($studentIds as $sid) {
                    $sum = 0;
                    if (isset($data['scores']) && is_array($data['scores'])) {
                        foreach ($data['scores'] as $val) {
                            if (is_array($val) && isset($val[$sid])) {
                                $sum += filter_var($val[$sid], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ?? 0;
                            }
                        }
                    }
                    $pct  = ($sum / $maxScoreTotal) * 100;
                    $soloScores[$sid] += $pct * $weight;
                }
            }
        }

        // 3. Update existing evaluation_per_panel rows
        $selectStmt = $pdo->prepare(
            "SELECT id, student_id FROM evaluation_per_panel
             WHERE defense_schedule_id = :ds_id AND evaluator_id = :eval_id"
        );
        $selectStmt->execute([
            ':ds_id'   => $defense_schedule_id,
            ':eval_id' => $evaluator_id
        ]);
        $existing = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

        $evaluationIds = [];
        foreach ($existing as $row) {
            $eid = $row['id'];
            $sid = (int)$row['student_id'];
            $solo = $soloScores[$sid] ?? 0.0;
            $total = $totalWeightedScore + $solo;

            $upd = $pdo->prepare(
                "UPDATE evaluation_per_panel
                   SET comments    = :comments,
                       group_score = :group_score,
                       solo_score  = :solo_score,
                       total_score = :total_score,
                       updated_at  = NOW()
                 WHERE id = :id"
            );
            $upd->execute([
                ':comments'    => $comments,
                ':group_score' => $totalWeightedScore,
                ':solo_score'  => $solo,
                ':total_score' => $total,
                ':id'          => $eid
            ]);

            $evaluationIds[] = $eid;
        }

        // 4. Insert new rows for remaining students
        $existingStudents = array_column($existing, 'student_id');
        foreach ($studentIds as $sid) {
            if (in_array($sid, $existingStudents, true)) continue;
            $solo = $soloScores[$sid] ?? 0.0;
            $total = $totalWeightedScore + $solo;

            $ins = $pdo->prepare(
                "INSERT INTO evaluation_per_panel
                   (defense_schedule_id, evaluator_id, student_id,
                    comments, group_score, solo_score, total_score, created_at, updated_at)
                 VALUES
                   (:ds_id, :eval_id, :stud_id,
                    :comments, :group_score, :solo_score, :total_score, NOW(), NOW())"
            );
            $ins->execute([
                ':ds_id'       => $defense_schedule_id,
                ':eval_id'     => $evaluator_id,
                ':stud_id'     => $sid,
                ':comments'    => $comments,
                ':group_score' => $totalWeightedScore,
                ':solo_score'  => $solo,
                ':total_score' => $total
            ]);

            $evaluationIds[] = $pdo->lastInsertId();
        }

        // 5. Delete existing details and re-insert
        $delDetails = $pdo->prepare("DELETE FROM evaluation_details WHERE evaluation_id = ?");
        foreach ($evaluationIds as $eid) {
            $delDetails->execute([$eid]);
        }

        $detailStmt = $pdo->prepare(
            "INSERT INTO evaluation_details
                (evaluation_id, rubric_id, criterion_id,
                 student_id, score, selected_option, created_at, updated_at)
             VALUES
                (:eval_id, :rubric_id, :crit_id,
                 :student_id, :score, :option, NOW(), NOW())"
        );

        foreach ($evaluation_data as $rid => $data) {
            $rid = (int)$rid;
            if (!isset($rubricDetails[$rid])) continue;
            $meta = $rubricDetails[$rid];
            $type = $meta['rubric_type'];
            $is_ind = $meta['is_individual_enabled'];
            
            if ($type === 'passfail' && array_key_exists('selected_option', $data)) {
                $selected = (string)$data['selected_option'];
                $detailStmt->execute([
                    ':eval_id'     => $evaluationIds[0],
                    ':rubric_id'   => $rid,
                    ':crit_id'     => null,
                    ':student_id'  => null,
                    ':score'       => null,
                    ':option'      => $selected
                ]);
                continue; // skip other processing
            }
            if ($type === 'yesno' && isset($data['options']) && is_array($data['options'])) {
                $opts = $data['options'];
        
                // Individual‑enabled: one yes/no per student per criterion
                if (! empty($meta['is_individual_enabled'])) {
                    foreach ($opts as $critId => $stuMap) {
                        foreach ($stuMap as $stuId => $val) {
                            $detailStmt->execute([
                                ':eval_id'    => $evaluationIds[0],
                                ':rubric_id'  => $rid,
                                ':crit_id'    => (int)$critId,
                                ':student_id'=> (int)$stuId,
                                ':score'      => null,
                                ':option'     => (string)$val
                            ]);
                        }
                    }
                } 
                // Group Yes/No: one yes/no per criterion
                else {
                    foreach ($opts as $critId => $groupArr) {
                        $sel = array_key_exists('group', $groupArr)
                             ? (string)$groupArr['group']
                             : null;
                        $detailStmt->execute([
                            ':eval_id'    => $evaluationIds[0],
                            ':rubric_id'  => $rid,
                            ':crit_id'    => (int)$critId,
                            ':student_id'=> null,
                            ':score'      => null,
                            ':option'     => $sel
                        ]);
                    }
                }
                continue;
            }
            if (isset($data['scores']) && is_array($data['scores'])) {
                foreach ($data['scores'] as $critId => $val) {
                    $critId = (int)$critId;

                    

                    if ($type === 'numerical' && $is_ind && is_array($val)) {
                        foreach ($val as $sid => $sval) {
                            $detailStmt->execute([
                                ':eval_id'    => $evaluationIds[0],
                                ':rubric_id'  => $rid,
                                ':crit_id'    => $critId,
                                ':student_id'=> (int)$sid,
                                ':score'      => filter_var($sval, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE),
                                ':option'     => null
                            ]);
                        }
                    } else {
                        $score = null;
                        $option = null;
                        $studentId = null;

                        if ($type === 'numerical') {
                            $score = is_array($val)
                                ? filter_var($val['group'], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE)
                                : filter_var($val, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                        } elseif ($type === 'yesno' || $type === 'passfail') {
                            $option = (string)$val;
                        }

                        $detailStmt->execute([
                            ':eval_id'    => $evaluationIds[0],
                            ':rubric_id'  => $rid,
                            ':crit_id'    => $critId,
                            ':student_id'=> $studentId,
                            ':score'      => $score,
                            ':option'     => $option
                        ]);
                    }
                }
            }
        }

        // Commit and respond
        $pdo->commit();
        $response['status']      = 'success';
        $response['message']     = 'Evaluation submitted successfully.';
        $response['group_score'] = $totalWeightedScore;
        $response['solo_scores'] = $soloScores;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Evaluation Submission Error: " . $e->getMessage());
        $response['message'] = 'An error occurred: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit;
} else {
    echo json_encode($response);
    exit;
}
