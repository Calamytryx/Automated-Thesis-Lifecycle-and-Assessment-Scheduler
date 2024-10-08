<?php
// dashboard/includes/edit_functions.php

// Include database connection
require_once '../assets/setup/db.inc.php';

// Function to update user information
function updateUser($pdo, $id, $username, $email, $first_name, $last_name, $gender, $headline, $bio, $usertype) {
    $sql = "UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, gender = ?, headline = ?, bio = ?, usertype = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$username, $email, $first_name, $last_name, $gender, $headline, $bio, $usertype, $id]);
}

// Function to update thesis topic
function updateThesisTopic($pdo, $id, $topic, $description, $category, $suggested_by) {
    $sql = "UPDATE thesis_topics SET topic = ?, description = ?, category = ?, suggested_by = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$topic, $description, $category, $suggested_by, $id]);
}

// Function to update research title
function updateResearchTitle($pdo, $id, $title, $user_id, $status, $uniqueness_score, $feedback) {
    $sql = "UPDATE research_titles SET title = ?, user_id = ?, status = ?, uniqueness_score = ?, feedback = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$title, $user_id, $status, $uniqueness_score, $feedback, $id]);
}

// Function to update defense schedule
function updateDefenseSchedule($pdo, $id, $student_id, $panelist_id, $schedule_date, $start_time, $end_time, $room, $status) {
    $sql = "UPDATE defense_schedules SET student_id = ?, panelist_id = ?, schedule_date = ?, start_time = ?, end_time = ?, room = ?, status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$student_id, $panelist_id, $schedule_date, $start_time, $end_time, $room, $status, $id]);
}

// Function to update rubric
function updateRubric($pdo, $id, $name, $description, $created_by) {
    $sql = "UPDATE rubrics SET name = ?, description = ?, created_by = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$name, $description, $created_by, $id]);
}

// Function to update team
function updateTeam($pdo, $id, $name) {
    $sql = "UPDATE teams SET name = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$name, $id]);
}

// Function to update requirement
function updateRequirement($pdo, $id, $name, $description, $due_date) {
    $sql = "UPDATE requirements SET name = ?, description = ?, due_date = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$name, $description, $due_date, $id]);
}

// Function to update evaluation
function updateEvaluation($pdo, $id, $defense_schedule_id, $evaluator_id, $total_score, $comments, $recommendation) {
    $sql = "UPDATE evaluations SET defense_schedule_id = ?, evaluator_id = ?, total_score = ?, comments = ?, recommendation = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$defense_schedule_id, $evaluator_id, $total_score, $comments, $recommendation, $id]);
}

// Function to update environment variable
function updateEnvVariable($pdo, $id, $key, $value, $description) {
    $sql = "UPDATE env_variables SET `key` = ?, `value` = ?, `description` = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$key, $value, $description, $id]);
}

// Function to handle form submissions and route to the appropriate update function
function handleEditSubmission($pdo) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $table = $_POST['table'] ?? '';
        $id = $_POST['id'] ?? '';

        switch ($table) {
            case 'users':
                return updateUser($pdo, $id, $_POST['username'], $_POST['email'], $_POST['first_name'], $_POST['last_name'], $_POST['gender'], $_POST['headline'], $_POST['bio'], $_POST['usertype']);
            case 'thesis_topics':
                return updateThesisTopic($pdo, $id, $_POST['topic'], $_POST['description'], $_POST['category'], $_POST['suggested_by']);
            case 'research_titles':
                return updateResearchTitle($pdo, $id, $_POST['title'], $_POST['user_id'], $_POST['status'], $_POST['uniqueness_score'], $_POST['feedback']);
            case 'defense_schedules':
                return updateDefenseSchedule($pdo, $id, $_POST['student_id'], $_POST['panelist_id'], $_POST['schedule_date'], $_POST['start_time'], $_POST['end_time'], $_POST['room'], $_POST['status']);
            case 'rubrics':
                return updateRubric($pdo, $id, $_POST['name'], $_POST['description'], $_POST['created_by']);
            case 'teams':
                return updateTeam($pdo, $id, $_POST['name']);
            case 'requirements':
                return updateRequirement($pdo, $id, $_POST['name'], $_POST['description'], $_POST['due_date']);
            case 'evaluations':
                return updateEvaluation($pdo, $id, $_POST['defense_schedule_id'], $_POST['evaluator_id'], $_POST['total_score'], $_POST['comments'], $_POST['recommendation']);
            case 'env_variables':
                return updateEnvVariable($pdo, $id, $_POST['key'], $_POST['value'], $_POST['description']);
            default:
                return false;
        }
    }
    return false;
}