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
 * - updateProgram($pdo, $id, $college, $department, $name, $specialization)
 *   Updates program information in the database.
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
require_once __DIR__ . '/../../assets/includes/security_functions.php';

function defenseScheduleColumnExists($pdo, $columnName) {
    static $columnCache = [];

    $columnName = trim((string)$columnName);
    if ($columnName === '') {
        return false;
    }

    if (isset($columnCache[$columnName])) {
        return $columnCache[$columnName];
    }

        $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'defense_schedules' AND COLUMN_NAME = ? LIMIT 1");
    $stmt->execute([$columnName]);
    $columnCache[$columnName] = (bool)$stmt->fetchColumn();
    return $columnCache[$columnName];
}

function getDefenseScheduleAccessContext($pdo, $userId, $usertype) {
    if ((int)$usertype === 0 && (int)$userId === 0) {
        return ['scope' => 'all', 'college' => null, 'sections' => []];
    }

    require_once __DIR__ . '/../../assets/includes/auth_functions.php';
    require_once __DIR__ . '/section_access.php';

    $college = get_user_college($pdo, (int)$userId);
    $sections = ((int)$usertype === 2) ? getProfessorSections($pdo, (int)$userId) : [];

    if ((int)$usertype === 0) {
        return ['scope' => 'college', 'college' => $college, 'sections' => []];
    }

    if ((int)$usertype === 2) {
        if (!empty($sections)) {
            return ['scope' => 'sections', 'college' => $college, 'sections' => $sections];
        }
        return ['scope' => 'college', 'college' => $college, 'sections' => []];
    }

    return ['scope' => 'none', 'college' => null, 'sections' => []];
}

function canUserAccessDefenseScheduleByTeam($pdo, $userId, $usertype, $teamId) {
    $ctx = getDefenseScheduleAccessContext($pdo, (int)$userId, (int)$usertype);

    if ($ctx['scope'] === 'all') {
        return true;
    }
    if ($ctx['scope'] === 'none') {
        return false;
    }

    if ($ctx['scope'] === 'sections') {
        if (empty($ctx['sections'])) {
            return false;
        }
        $placeholders = implode(',', array_fill(0, count($ctx['sections']), '?'));
        $query = "
            SELECT COUNT(*)
            FROM team_members tm
            JOIN users u ON u.id = tm.user_id
            WHERE tm.team_id = ? AND u.usertype = 1 AND u.section IN ($placeholders)
        ";
        $params = array_merge([(int)$teamId], $ctx['sections']);
        $checkStmt = $pdo->prepare($query);
        $checkStmt->execute($params);
        return ((int)$checkStmt->fetchColumn()) > 0;
    }

    if ($ctx['scope'] === 'college' && !empty($ctx['college'])) {
        $stmt = $pdo->prepare("
            SELECT p.college
            FROM teams t
            LEFT JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)
            WHERE t.id = ?
            LIMIT 1
        ");
        $stmt->execute([(int)$teamId]);
        $teamCollege = $stmt->fetchColumn();
        return !empty($teamCollege) && $teamCollege === $ctx['college'];
    }

    return false;
}

function isDefenseScheduleFinalized($pdo, $scheduleId) {
    if (!defenseScheduleColumnExists($pdo, 'is_finalized')) {
        return false;
    }

    $stmt = $pdo->prepare("SELECT COALESCE(is_finalized, 0) FROM defense_schedules WHERE id = ?");
    $stmt->execute([(int)$scheduleId]);
    return ((int)$stmt->fetchColumn()) === 1;
}

function getTeamStudentIds($pdo, $teamId) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id
        FROM team_members tm
        JOIN users u ON u.id = tm.user_id
        WHERE tm.team_id = ? AND u.usertype = 1
    ");
    $stmt->execute([(int)$teamId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function validateStudentScheduleConflicts($pdo, $teamId, $scheduleDate, $startTime, $endTime, $excludeScheduleId = null) {
    $studentIds = getTeamStudentIds($pdo, (int)$teamId);
    if (empty($studentIds)) {
        return ['ok' => true, 'message' => ''];
    }

    $start = (strlen((string)$startTime) === 5) ? $startTime . ':00' : $startTime;
    $end = (strlen((string)$endTime) === 5) ? $endTime . ':00' : $endTime;

    if ($start >= $end) {
        return ['ok' => false, 'message' => 'End time must be later than start time.'];
    }

    $studentPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));
    $params = $studentIds;
    $params[] = (int)$teamId;
    $params[] = $scheduleDate;
    $params[] = $end;
    $params[] = $start;

    $excludeSql = '';
    if ($excludeScheduleId !== null) {
        $excludeSql = ' AND ds.id != ?';
        $params[] = (int)$excludeScheduleId;
    }

    $defenseConflictStmt = $pdo->prepare("
        SELECT ds.id
        FROM defense_schedules ds
        JOIN team_members tm ON tm.team_id = ds.team_id
        WHERE tm.user_id IN ($studentPlaceholders)
          AND ds.team_id != ?
          AND ds.schedule_date = ?
          AND ds.start_time < ?
          AND ds.end_time > ?
          AND COALESCE(ds.status, 'scheduled') != 'cancelled'
          AND COALESCE(ds.approval_status, 'pending_chair') != 'rejected'
          $excludeSql
        LIMIT 1
    ");
    $defenseConflictStmt->execute($params);
    if ($defenseConflictStmt->fetch(PDO::FETCH_ASSOC)) {
        return [
            'ok' => false,
            'message' => 'Student schedule conflict: one or more team members already have another defense schedule at this time.'
        ];
    }

    $dayOfWeek = date('l', strtotime($scheduleDate));
    $classParams = $studentIds;
    $classParams[] = $dayOfWeek;
    $classParams[] = $end;
    $classParams[] = $start;

    $classConflictStmt = $pdo->prepare("
        SELECT us.id
        FROM user_schedules us
        WHERE us.user_id IN ($studentPlaceholders)
          AND us.day_of_week = ?
          AND us.start_time < ?
          AND us.end_time > ?
        LIMIT 1
    ");
    $classConflictStmt->execute($classParams);
    if ($classConflictStmt->fetch(PDO::FETCH_ASSOC)) {
        return [
            'ok' => false,
            'message' => 'Student class conflict detected. Defense schedules cannot overlap with student class schedules.'
        ];
    }

    return ['ok' => true, 'message' => ''];
}

// Function to update user information
function updateUser($pdo, $id, $username, $email, $first_name, $last_name, $gender, $headline, $bio, $usertype) {
    // Sanitize all text inputs to prevent HTML/script injection
    $username = sanitize_html_input($username);
    $email = sanitize_html_input($email);
    $first_name = sanitize_html_input($first_name);
    $last_name = sanitize_html_input($last_name);
    $gender = sanitize_html_input($gender);
    $headline = sanitize_html_input($headline);
    $bio = sanitize_html_input($bio);
    
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
function updateResearchTitle($pdo, $id, $title, $team_id, $program, $approved_at, $defended_at) {
    // Sanitize text inputs to prevent HTML/script injection
    $title = sanitize_html_input($title);
    $program = sanitize_html_input($program);
    
    $sql = "UPDATE research_titles 
            SET title = ?, 
                team_id = ?, 
                program = ?, 
                approved_at = ?, 
                defended_at = ?, 
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$title, $team_id, $program, $approved_at, $defended_at, $id]);
}

// Function to update defense schedule
function updateDefenseSchedule($pdo, $id, $student_id, $panelist_id, $schedule_date, $start_time, $end_time, $room, $status) {
    $sql = "UPDATE defense_schedules SET student_id = ?, panelist_id = ?, schedule_date = ?, start_time = ?, end_time = ?, room = ?, status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    fetchAllDefenseSchedules($pdo);
    return $stmt->execute([$student_id, $panelist_id, $schedule_date, $start_time, $end_time, $room, $status, $id]);
}

// Function to update team
function updateTeam($pdo, $id, $name, $title, $members) {
    // Sanitize text inputs to prevent HTML/script injection
    $name = sanitize_html_input($name);
    $title = sanitize_html_input($title);
    
    try {
        $pdo->beginTransaction();

        // Update team name
        $sql = "UPDATE `teams` SET `name` = ?, `program` = ? WHERE `id` = ?";
        $stmt = $pdo->prepare($sql);
        $program = isset($_POST['program']) ? sanitize_html_input($_POST['program']) : null;
        $stmt->execute([$name, $program, $id]);

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
    // Sanitize text fields to prevent HTML/script injection
    $key = sanitize_html_input($key);
    $value = sanitize_html_input($value);
    $description = sanitize_html_input($description);
    
    $sql = "UPDATE env_variables SET `key` = ?, `value` = ?, `description` = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$key, $value, $description, $id]);
}

// Function to update page content
function updatePageContent($pdo, $id, $title, $slug, $content, $status) {
    $sql = "UPDATE page_content SET title = ?, slug = ?, content = ?, status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$title, $slug, $content, $status, $id]);
}

// Function to update user schedule
function updateUserSchedule($pdo, $id, $user_id, $day_of_week, $start_time, $end_time, $class_name) {
    $sql = "UPDATE user_schedules SET user_id = ?, day_of_week = ?, start_time = ?, end_time = ?, class_name = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$user_id, $day_of_week, $start_time, $end_time, $class_name, $id]);
}

// Function to update program information
function updateProgram($pdo, $id, $college, $department, $name, $specialization) {
    $sql = "UPDATE programs SET college = ?, department = ?, name = ?, specialization = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    // Handle potentially null values for department and specialization
    $department = empty($department) ? null : $department;
    $specialization = empty($specialization) ? null : $specialization;
    return $stmt->execute([$college, $department, $name, $specialization, $id]);
}


// Generic function to handle form submissions and update database
function handleEditSubmission($pdo, $table, $id, $data) {
    // If no fields to update return false
    if (empty($data)) {
        return false;
    }

    // For teams table, handle special processing
    if ($table === 'teams') {
        // Sanitize team title
        $teamTitle = isset($data['title']) ? sanitize_html_input($data['title']) : null;

        // Extract existing member roles if present
        $memberRoles = isset($data['member_role']) && is_array($data['member_role']) ? $data['member_role'] : [];

        // Extract new member data if present
        $newUserIds = isset($data['new_user_id']) && is_array($data['new_user_id']) ? $data['new_user_id'] : [];
        $newUsernames = isset($data['new_username']) && is_array($data['new_username']) ? $data['new_username'] : [];
        $newRoles = isset($data['new_role']) && is_array($data['new_role']) ? $data['new_role'] : [];

        // Filter out all member-related and title fields from data
        $cleanData = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'member_') === false &&
                strpos($key, 'new_') === false &&
                $key !== 'title') {
                // Sanitize team data fields
                if (in_array($key, ['name', 'program', 'area_of_expertise'])) {
                    $cleanData[$key] = sanitize_html_input($value);
                } else {
                    $cleanData[$key] = $value;
                }
            }
        }

        try {
            $pdo->beginTransaction();

            // 1. Update team basic info
            if (!empty($cleanData)) {
                $fields = array_keys($cleanData);
                $setParts = [];
                foreach ($fields as $field) {
                    $setParts[] = "`$field` = :$field";
                }
                $setStr = implode(', ', $setParts);
                $sql = "UPDATE `$table` SET $setStr WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $cleanData['id'] = $id;
                $stmt->execute($cleanData);
            }

            // 2. Update research title if provided
            if ($teamTitle !== null) {
                $sqlTitle = "UPDATE `research_titles` SET `title` = :title WHERE `team_id` = :id";
                $stmtTitle = $pdo->prepare($sqlTitle);
                $stmtTitle->execute(['title' => $teamTitle, 'id' => $id]);
            }

            // 3. Update existing team members' roles
            if (!empty($memberRoles)) {
                $sql = "SELECT `id`, `user_id`, `role` FROM `team_members` WHERE `team_id` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                $currentMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($currentMembers as $index => $member) {
                    if (isset($memberRoles[$index])) {
                        $sql = "UPDATE `team_members` SET `role` = ? WHERE `id` = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$memberRoles[$index], $member['id']]);
                    }
                }
            }

            // 4. Add new members to team
            for ($i = 0; $i < count($newRoles); $i++) {
                $userId = null;

                // If new_user_id is provided, use it directly
                if (!empty($newUserIds[$i])) {
                    $userId = $newUserIds[$i];
                }
                // If new_username is provided, look up the user ID
                else if (!empty($newUsernames[$i])) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$newUsernames[$i]]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $userId = $result['id'];
                    }
                }

                // If we have a user ID and role, add to team_members
                if ($userId && !empty($newRoles[$i])) {
                    $sql = "INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$id, $userId, $newRoles[$i]]);
                }
            }

            $pdo->commit();
            return true;
        }
        catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error updating team: " . $e->getMessage());
            return false;
        }
    }
    // Handle programs table
    elseif ($table === 'programs') {
        // Extract program fields
        $college = $data['college'] ?? null;
        $department = $data['department'] ?? null;
        $name = $data['name'] ?? null;
        $specialization = $data['specialization'] ?? null;

        // Call the specific update function
        return updateProgram($pdo, $id, $college, $department, $name, $specialization);
    }
    // Handle env_variables table
    elseif ($table === 'env_variables') {
        // Extract env_variables fields
        $key = $data['key'] ?? null;
        $value = $data['value'] ?? null;
        $description = $data['description'] ?? null;

        // Call the specific update function
        return updateEnvVariable($pdo, $id, $key, $value, $description);
    }
    // Handle page_content table
    elseif ($table === 'page_content') {
        // Extract page_content fields
        $title = $data['title'] ?? null;
        $slug = $data['slug'] ?? null;
        $content = $data['content'] ?? null;
        $status = $data['status'] ?? 'draft';

        // Call the specific update function
        return updatePageContent($pdo, $id, $title, $slug, $content, $status);
    }

    // Special handling for defense_schedules table
    if ($table === 'defense_schedules' && isset($data['panelist_id']) && is_array($data['panelist_id'])) {
        // Map panelist array indices to specific columns
        $panelistIds = $data['panelist_id'];

        // Remove the array from data to avoid JSON conversion
        unset($data['panelist_id']);

        // Map the first three panelists to their respective columns
        if (isset($panelistIds[0])) {
            $data['panelist_id'] = $panelistIds[0];
        }

        if (isset($panelistIds[1])) {
            $data['panelist_id2'] = $panelistIds[1];
        }

        if (isset($panelistIds[2])) {
            $data['panelist_id3'] = $panelistIds[2];
        }
    }
    // Process other arrays normally
    else {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Convert arrays to JSON strings
                $data[$key] = json_encode($value);
            }
        }
    }

    // Build the SET clause dynamically using the POST keys
    $fields = array_keys($data);
    $setParts = [];
    foreach ($fields as $field) {
        $setParts[] = "`$field` = :$field";
    }
    
    // Apply general sanitization for text fields in other tables
    if ($table === 'research_titles') {
        $textFields = ['title', 'description', 'program'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = sanitize_html_input($data[$field]);
            }
        }
    } elseif ($table === 'users') {
        $textFields = ['username', 'email', 'first_name', 'last_name', 'gender', 'headline', 'bio'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = sanitize_html_input($data[$field]);
            }
        }
    }
    
    $setStr = implode(', ', $setParts);
    $sql = "UPDATE `$table` SET $setStr WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $data['id'] = $id;
    return $stmt->execute($data);
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

function fetchAllDefenseSchedules($pdo, $viewerId = null, $viewerType = null) {
    if ($viewerId === null) {
        $viewerId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
    }
    if ($viewerType === null) {
        $viewerType = isset($_SESSION['usertype']) ? (int)$_SESSION['usertype'] : -1;
    }

    $finalizedSelect = defenseScheduleColumnExists($pdo, 'is_finalized')
        ? "COALESCE(ds.is_finalized, 0) AS is_finalized, ds.finalized_at, ds.finalized_by,"
        : "0 AS is_finalized, NULL AS finalized_at, NULL AS finalized_by,";

    $sql = "
        SELECT
            ds.id,
            ds.team_id,
            ds.schedule_date,
            ds.start_time,
            ds.end_time,
            ds.room,
            ds.defense_type,
            ds.approval_status,
            $finalizedSelect
            t.name AS team_name,
            rt.title AS thesis_title,
            GROUP_CONCAT(
                DISTINCT CONCAT(u_student.first_name, ' ', u_student.last_name)
                ORDER BY tm.id SEPARATOR ', '
            ) AS team_members,
            GROUP_CONCAT(
                DISTINCT CONCAT(u_panelist.first_name, ' ', u_panelist.last_name)
                ORDER BY FIELD(ds.panelist_id, ds.panelist_id2, ds.panelist_id3) SEPARATOR ', '
            ) AS panelists,
            (SELECT CONCAT(u_adviser.first_name, ' ', u_adviser.last_name)
             FROM team_members tm_adviser
             JOIN users u_adviser ON tm_adviser.user_id = u_adviser.id
             WHERE tm_adviser.team_id = t.id AND tm_adviser.role = 'adviser'
             ORDER BY tm_adviser.id ASC LIMIT 1) AS adviser
        FROM defense_schedules ds
        JOIN teams t ON ds.team_id = t.id
        LEFT JOIN programs p ON t.program = CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END)
        JOIN research_titles rt ON t.id = rt.team_id
        JOIN team_members tm ON t.id = tm.team_id
        JOIN users u_student ON tm.user_id = u_student.id AND u_student.usertype != 2
        LEFT JOIN users u_panelist ON u_panelist.id IN (ds.panelist_id, ds.panelist_id2, ds.panelist_id3)
    ";

    $params = [];
    $ctx = getDefenseScheduleAccessContext($pdo, (int)$viewerId, (int)$viewerType);

    if ($ctx['scope'] === 'none') {
        return [];
    }

    if ($ctx['scope'] === 'college' && !empty($ctx['college'])) {
        $sql .= " WHERE p.college = :college ";
        $params[':college'] = $ctx['college'];
    } elseif ($ctx['scope'] === 'sections' && !empty($ctx['sections'])) {
        $sectionPlaceholders = [];
        foreach ($ctx['sections'] as $idx => $section) {
            $ph = ':section_' . $idx;
            $sectionPlaceholders[] = $ph;
            $params[$ph] = $section;
        }
        $sql .= " WHERE u_student.section IN (" . implode(',', $sectionPlaceholders) . ") ";
    }

    $sql .= " GROUP BY ds.id, t.name, rt.title ORDER BY ds.schedule_date, ds.start_time";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

