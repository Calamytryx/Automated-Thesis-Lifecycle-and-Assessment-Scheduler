<?php
/**
 * Recalculate all evaluation_per_panel scores using actual max scores
 * from rubric criteria and quality levels instead of hardcoded *10.
 * 
 * Run this ONCE to fix all existing stored scores.
 * Usage: php recalculate_scores.php
 *    or: visit http://localhost/recalculate_scores.php (admin only)
 */

require_once 'assets/setup/db.inc.php';

// If accessed via web, require admin login
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!isset($_SESSION['id']) || $_SESSION['usertype'] != 0) {
        die('Admin access required.');
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<pre>';
}

try {
    // Get all unique (defense_schedule_id, evaluator_id) pairs
    $pairsStmt = $pdo->query("
        SELECT DISTINCT defense_schedule_id, evaluator_id
        FROM evaluation_per_panel
        ORDER BY defense_schedule_id, evaluator_id
    ");
    $pairs = $pairsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($pairs) . " evaluator-schedule pairs to process.\n\n";

    // Pre-fetch all rubric metadata
    $allRubrics = [];
    $rubStmt = $pdo->query("SELECT id, rubric_type, is_individual_enabled FROM rubrics WHERE rubric_type = 'numerical'");
    while ($row = $rubStmt->fetch(PDO::FETCH_ASSOC)) {
        $allRubrics[(int)$row['id']] = $row;
    }

    // Pre-fetch level max per rubric
    $levelMaxes = [];
    $lvlStmt = $pdo->query("SELECT rubric_id, MAX(COALESCE(points_max, points_min, 0)) as level_max FROM rubric_levels GROUP BY rubric_id");
    while ($row = $lvlStmt->fetch(PDO::FETCH_ASSOC)) {
        $val = (float)$row['level_max'];
        if ($val > 0) $levelMaxes[(int)$row['rubric_id']] = $val;
    }

    // Pre-fetch actual sum of max_score for non-blank criteria per rubric
    $criteriaMaxSums = [];
    $cmStmt = $pdo->query("
        SELECT rubric_id,
               SUM(CASE WHEN max_score IS NOT NULL AND max_score > 0 THEN max_score ELSE
                   COALESCE((SELECT MAX(COALESCE(rl.points_max, rl.points_min, 0))
                             FROM rubric_levels rl WHERE rl.rubric_id = rubric_criteria.rubric_id), 10)
               END) as sum_max
        FROM rubric_criteria
        WHERE (is_blank = 0 OR is_blank IS NULL)
        GROUP BY rubric_id
    ");
    while ($row = $cmStmt->fetch(PDO::FETCH_ASSOC)) {
        $criteriaMaxSums[(int)$row['rubric_id']] = (float)$row['sum_max'];
    }

    // Pre-fetch rubric weights: for each rubric, get weight from rubric_group_items
    // If a rubric appears in multiple groups, use the first weight found
    $rubricWeightsMap = [];
    $rwStmt = $pdo->query("SELECT rubric_id, weight FROM rubric_group_items WHERE weight IS NOT NULL ORDER BY id DESC");
    while ($row = $rwStmt->fetch(PDO::FETCH_ASSOC)) {
        $rid = (int)$row['rubric_id'];
        $rubricWeightsMap[$rid] = floatval($row['weight']) / 100;
    }

    $totalUpdated = 0;

    foreach ($pairs as $pair) {
        $dsId = (int)$pair['defense_schedule_id'];
        $evaluatorId = (int)$pair['evaluator_id'];

        // Get all evaluation_details for this evaluator + defense
        $detailStmt = $pdo->prepare("
            SELECT ed.rubric_id, ed.criterion_id, ed.student_id, ed.score
            FROM evaluation_details ed
            JOIN evaluation_per_panel ep ON ed.evaluation_id = ep.id
            WHERE ep.defense_schedule_id = ? AND ep.evaluator_id = ?
            AND ed.score IS NOT NULL
        ");
        $detailStmt->execute([$dsId, $evaluatorId]);
        $details = $detailStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($details)) continue;

        // Find which rubrics were actually evaluated
        $evaluatedRubricIds = array_unique(array_map(function($d) { return (int)$d['rubric_id']; }, $details));

        // Calculate GROUP scores (non-individual numerical rubrics)
        $totalWeightedScore = 0;
        foreach ($evaluatedRubricIds as $rid) {
            if (!isset($allRubrics[$rid])) continue;
            if ($allRubrics[$rid]['is_individual_enabled']) continue;
            if (!isset($rubricWeightsMap[$rid])) continue;

            $sum = 0;
            $count = 0;
            foreach ($details as $d) {
                if ((int)$d['rubric_id'] === $rid && $d['student_id'] === null && $d['score'] !== null) {
                    $sum += (float)$d['score'];
                    $count++;
                }
            }

            if ($count > 0) {
                $maxPerCriterion = $levelMaxes[$rid] ?? 10;
                $rubricScore = ($sum / ($count * $maxPerCriterion)) * 100;
                $totalWeightedScore += $rubricScore * $rubricWeightsMap[$rid];
            }
        }

        // Get students for this evaluation
        $studentStmt = $pdo->prepare(
            "SELECT DISTINCT student_id FROM evaluation_per_panel WHERE defense_schedule_id = ? AND evaluator_id = ?"
        );
        $studentStmt->execute([$dsId, $evaluatorId]);
        $studentIds = $studentStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($studentIds as $sid) {
            $sid = (int)$sid;
            $soloScore = 0.0;

            // Calculate SOLO scores (individual numerical rubrics)
            foreach ($evaluatedRubricIds as $rid) {
                if (!isset($allRubrics[$rid])) continue;
                if (!$allRubrics[$rid]['is_individual_enabled']) continue;
                if (!isset($rubricWeightsMap[$rid])) continue;

                $sum = 0;
                foreach ($details as $d) {
                    if ((int)$d['rubric_id'] === $rid && $d['student_id'] !== null && (int)$d['student_id'] === $sid && $d['score'] !== null) {
                        $sum += (float)$d['score'];
                    }
                }

                $maxScoreTotal = $criteriaMaxSums[$rid] ?? 40;
                if ($maxScoreTotal > 0) {
                    $pct = ($sum / $maxScoreTotal) * 100;
                    $soloScore += $pct * $rubricWeightsMap[$rid];
                }
            }

            $newTotal = $totalWeightedScore + $soloScore;

            // Get current values for comparison
            $currentStmt = $pdo->prepare("
                SELECT group_score, solo_score, total_score
                FROM evaluation_per_panel
                WHERE defense_schedule_id = ? AND evaluator_id = ? AND student_id = ?
            ");
            $currentStmt->execute([$dsId, $evaluatorId, $sid]);
            $current = $currentStmt->fetch(PDO::FETCH_ASSOC);

            $oldTotal = $current ? round((float)$current['total_score'], 2) : 'N/A';
            $newTotalRounded = round($newTotal, 2);

            if ($oldTotal != $newTotalRounded) {
                $updateStmt = $pdo->prepare("
                    UPDATE evaluation_per_panel
                    SET group_score = :group_score,
                        solo_score = :solo_score,
                        total_score = :total_score,
                        updated_at = NOW()
                    WHERE defense_schedule_id = :ds_id
                    AND evaluator_id = :eval_id
                    AND student_id = :student_id
                ");
                $updateStmt->execute([
                    ':group_score' => round($totalWeightedScore, 4),
                    ':solo_score'  => round($soloScore, 4),
                    ':total_score' => round($newTotal, 4),
                    ':ds_id'       => $dsId,
                    ':eval_id'     => $evaluatorId,
                    ':student_id'  => $sid
                ]);
                echo "DS#$dsId Eval#$evaluatorId Stu#$sid: $oldTotal -> $newTotalRounded (G:" . round($totalWeightedScore, 2) . " S:" . round($soloScore, 2) . ")\n";
                $totalUpdated++;
            }
        }
    }

    echo "\n========================================\n";
    echo "Done! Updated $totalUpdated evaluation records.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

if (php_sapi_name() !== 'cli') {
    echo '</pre>';
}

