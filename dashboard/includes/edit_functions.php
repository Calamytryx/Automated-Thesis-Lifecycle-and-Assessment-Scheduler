<?php
/**
 * 
 * This file contains functions to update various entities in the database.
 * 
 * Functions:
 * 
 * - updateUser($pdo, $id, $username, $email, $first_name, $last_name, $gender, $headline, $bio, $usertype)
 *   Updates user information in the database.
 * 
 * - updateThesisTopic($pdo, $id, $topic, $description, $category, $suggested_by)
 *   Updates thesis topic information in the database.
 * 
 * - updateResearchTitle($pdo, $id, $title, $user_id, $status, $uniqueness_score, $feedback)
 *   Updates research title information in the database.
 * 
 * - updateDefenseSchedule($pdo, $id, $student_id, $panelist_id, $schedule_date, $start_time, $end_time, $room, $status)
 *   Updates defense schedule information in the database.
 * 
 * - updateRubric($pdo, $id, $name, $description, $created_by)
 *   Updates rubric information in the database.
 * 
 * - updateTeam($pdo, $id, $name, $title, $members)
 *   Updates team information and its members in the database.
 * 
 * - updateRequirement($pdo, $id, $name, $description, $due_date)
 *   Updates requirement information in the database.
 * 
 * - updateEvaluation($pdo, $id, $defense_schedule_id, $evaluator_id, $total_score, $comments, $recommendation)
 *   Updates evaluation information in the database.
 * 
 * - updateEnvVariable($pdo, $id, $key, $value, $description)
 *   Updates environment variable information in the database.
 * 
 * - updateUserSchedule($pdo, $id, $user_id, $day_of_week, $start_time, $end_time, $class_name)
 *   Updates user schedule information in the database.
 * 
 * - handleEditSubmission($pdo)
 *   Handles form submissions and routes to the appropriate update function based on the table specified in the POST request.
 * 
 * - getUserType($usertype)
 *   Returns the user type as a string based on the usertype integer.
 * 
 * - getDefenseScheduleInfo($pdo, $defense_schedule_id)
 *   Retrieves and formats defense schedule information.
 * 
 * - getUserName($pdo, $user_id)
 *   Retrieves the full name of a user based on their user ID.
 * 
 * - getRubricName($pdo, $rubric_id)
 *   Retrieves the name of a rubric based on its ID.
 * 
 * - getTeamMembersForEdit($pdo, $team_id, $format = 'html')
 *   Retrieves team members for editing, formatted as HTML or an array.
 */
// dashboard/includes/edit_functions.php

// Include database connection
require_once __DIR__ . '/../../assets/setup/db.inc.php';

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
function updateTeam($pdo, $id, $name, $title, $members) {
    try {
        $pdo->beginTransaction();

        // Update team name
        $sql = "UPDATE `teams` SET `name` = ? WHERE `id` = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $id]);

        // Update research title
        $sql = "UPDATE `research_titles` SET `title` = ? WHERE `team_id` = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $id]);

        // Fetch current team members
        $sql = "SELECT `id`, `user_id`, `role` FROM `team_members` WHERE `team_id` = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $currentMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Current members: " . print_r($currentMembers, true));
        error_log("New members data: " . print_r($members, true));

        // Update team members
        foreach ($members as $index => $member) {
            if (isset($currentMembers[$index])) {
                $sql = "UPDATE `team_members` SET `role` = ? WHERE `id` = ?";
                error_log("Executing SQL: $sql with params: " . $member['role'] . ", " . $currentMembers[$index]['id']);
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$member['role'], $currentMembers[$index]['id']]);
            }
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating team: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        error_log("Error Info: " . print_r($e->errorInfo, true));
        return false;
    }
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

// Function to update user schedule
function updateUserSchedule($pdo, $id, $user_id, $day_of_week, $start_time, $end_time, $class_name) {
    $sql = "UPDATE user_schedules SET user_id = ?, day_of_week = ?, start_time = ?, end_time = ?, class_name = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$user_id, $day_of_week, $start_time, $end_time, $class_name, $id]);
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
                $members = json_decode($_POST['members'], true);
                return updateTeam($pdo, $id, $_POST['name'], $_POST['title'], $members);
            case 'requirements':
                return updateRequirement($pdo, $id, $_POST['name'], $_POST['description'], $_POST['due_date']);
            case 'evaluations':
                return updateEvaluation($pdo, $id, $_POST['defense_schedule_id'], $_POST['evaluator_id'], $_POST['total_score'], $_POST['comments'], $_POST['recommendation']);
            case 'env_variables':
                return updateEnvVariable($pdo, $id, $_POST['key'], $_POST['value'], $_POST['description']);
            case 'user_schedules':
                return updateUserSchedule($pdo, $id, $_POST['user_id'], $_POST['day_of_week'], $_POST['start_time'], $_POST['end_time'], $_POST['class_name']);
            default:
                return false;
        }
    }
    return false;
}

function getUserType($usertype) {
    switch ($usertype) {
        case 0:
            return 'Admin';
        case 1:
            return 'Student';
        case 2:
            return 'Faculty';
        default:
            return 'Unknown';
    }
}

function getDefenseScheduleInfo($pdo, $defense_schedule_id) {
    $stmt = $pdo->prepare("SELECT schedule_date, start_time, room FROM defense_schedules WHERE id = ?");
    $stmt->execute([$defense_schedule_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($schedule) {
        return date('Y-m-d', strtotime($schedule['schedule_date'])) . ' ' . 
               date('H:i', strtotime($schedule['start_time'])) . ' - ' . 
               $schedule['room'];
    }
    return 'N/A';
}

function getUserName($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        return $user['first_name'] . ' ' . $user['last_name'];
    }
    return 'N/A';
}

function getRubricName($pdo, $rubric_id) {
    $stmt = $pdo->prepare("SELECT name FROM rubrics WHERE id = ?");
    $stmt->execute([$rubric_id]);
    $rubric = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($rubric) {
        return $rubric['name'];
    }
    return 'N/A';
}

function getTeamMembersForEdit($pdo, $team_id, $format = 'html') {
    $stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, tm.role FROM team_members tm JOIN users u ON tm.user_id = u.id WHERE tm.team_id = ? ORDER BY FIELD(tm.role, 'adviser', 'leader', 'member')");
    $stmt->execute([$team_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format === 'html') {
        $output = '';
        foreach ($members as $member) {
            $output .= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . ' (' . ucfirst($member['role']) . ')<br>';
        }
        return $output;
    } else if ($format === 'array') {
        return array_map(function ($member) {
            return [
                'id' => $member['id'],
                'name' => $member['first_name'] . ' ' . $member['last_name'],
                'role' => $member['role']
            ];
        }, $members);
    } else {
        $output = '';
        foreach ($members as $member) {
            $output .= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . ' (' . ucfirst($member['role']) . ')<br>';
        }
        return $output;
    }
}

function fetchAllDefenseSchedules($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            ds.id,
            ds.schedule_date,
            ds.start_time,
            ds.end_time,
            ds.room,
            t.name AS team_name,
            rt.title AS thesis_title,
            GROUP_CONCAT(DISTINCT CONCAT(u_student.first_name, ' ', u_student.last_name) ORDER BY tm.id SEPARATOR ', ') AS team_members,
            GROUP_CONCAT(DISTINCT CONCAT(u_panelist.first_name, ' ', u_panelist.last_name) ORDER BY FIELD(ds.panelist_id, ds.panelist_id, ds.panelist_id2, ds.panelist_id3) SEPARATOR ', ') AS panelists,
            (SELECT CONCAT(u_adviser.first_name, ' ', u_adviser.last_name) 
             FROM team_members tm_adviser 
             JOIN users u_adviser ON tm_adviser.user_id = u_adviser.id 
             WHERE tm_adviser.team_id = t.id AND tm_adviser.role = 'adviser' 
             ORDER BY tm_adviser.id ASC LIMIT 1) AS adviser
        FROM defense_schedules ds
        JOIN teams t ON ds.team_id = t.id
        JOIN research_titles rt ON t.id = rt.team_id
        JOIN team_members tm ON t.id = tm.team_id
        JOIN users u_student ON tm.user_id = u_student.id
        LEFT JOIN users u_panelist ON u_panelist.id IN (ds.panelist_id, ds.panelist_id2, ds.panelist_id3)
        GROUP BY ds.id, t.name, rt.title
        ORDER BY ds.schedule_date, ds.start_time
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
