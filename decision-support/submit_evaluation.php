<?php
// Include database connection
require '../assets\setup\db.inc.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $defense_schedule_id = $_POST['defense_schedule_id'];
    $evaluator_id = $_POST['evaluator_id'];
    $group_score = $_POST['group_score'];
    $comments = $_POST['comments'];
    $student_ids = $_POST['student_ids'];
    $solo_scores = $_POST['solo_scores'];
    $total_scores = $_POST['total_scores'];

    try {
        $pdo->beginTransaction();

        // Insert data for each student
        for ($i = 0; $i < count($student_ids); $i++) {
            $stmt = $pdo->prepare("
                INSERT INTO coecsa_thesis.evaluation_per_panel 
                (defense_schedule_id, evaluator_id, student_id, group_score, solo_score, total_score, comments, created_at) 
                VALUES (:defense_schedule_id, :evaluator_id, :student_id, :group_score, :solo_score, :total_score, :comments, NOW())
            ");
            $stmt->execute([
                ':defense_schedule_id' => $defense_schedule_id,
                ':evaluator_id' => $evaluator_id,
                ':student_id' => $student_ids[$i],
                ':group_score' => $group_score,
                ':solo_score' => $solo_scores[$i],
                ':total_score' => $total_scores[$i],
                ':comments' => $comments,
            ]);
        }

        $pdo->commit();
        // Return JSON response instead of echo
        echo json_encode(['status' => 'success', 'message' => 'Evaluation submitted successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        // Return JSON error response
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>
