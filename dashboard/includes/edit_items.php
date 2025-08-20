<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../../assets/includes/security_functions.php';

// Set header to return JSON
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// At the beginning of the file, after starting the session and including required files:
require_once '../../assets/includes/auth_functions.php';

// Current user info
$userId = $_SESSION['id'] ?? 0;
$usertype = $_SESSION['usertype'] ?? -1;

// For admin users who aren't superadmin, get their college for validation
$userCollege = null;
if ($usertype == 0 && $userId != 0) {
    $userCollege = get_user_college($pdo, $userId);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'] ?? null;
    $id = $_POST['id'] ?? null;

    // Remove table and id from $_POST data that will be used for update
    $data = $_POST;
    unset($data['table'], $data['id']);

    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables', 'programs', 'default_schedules', 'user_schedules', 'page_content'];

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
    

    // Inside form processing logic (before updating an item)
function handleRequirementTemplateUpload($file) {
    $uploadDir = '../uploads/requirements/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            return ['success' => false, 'error' => 'Failed to create upload directory'];
        }
    }
    
    // Validate file
    $allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'xlsx', 'pptx'];
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)];
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        return ['success' => false, 'error' => 'File size too large. Maximum 10MB allowed.'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'original_name' => $file['name']
        ];
    } else {
        return ['success' => false, 'error' => 'Failed to upload file'];
    }
}
    // Special file upload handling for requirements
    if ($table === 'requirements' && isset($_FILES['template_file']) && $_FILES['template_file']['error'] === UPLOAD_ERR_OK) {
        // Remove old file if exists
        $stmt = $pdo->prepare("SELECT template_file FROM requirements WHERE id = ?");
        $stmt->execute([$id]);
        $oldFile = $stmt->fetchColumn();
        if ($oldFile && file_exists('../uploads/requirements/' . $oldFile)) {
            unlink('../uploads/requirements/' . $oldFile);
        }
        $uploadResult = handleRequirementTemplateUpload($_FILES['template_file']);
        if ($uploadResult['success']) {
            $data['template_file'] = $uploadResult['filename'];
            $data['template_original_name'] = $uploadResult['original_name'];
        } else {
            $response['message'] = 'File upload failed: ' . $uploadResult['error'];
            echo json_encode($response);
            exit;
        }
    }
    // For programs
    if ($table === 'programs' && $usertype == 0 && $userId != 0 && $userCollege) {
        // First check if the program being edited belongs to the admin's college
        $stmtCheck = $pdo->prepare("SELECT college FROM programs WHERE id = :id");
        $stmtCheck->execute([':id' => $id]);
        $currentCollege = $stmtCheck->fetchColumn();
        
        if ($currentCollege != $userCollege) {
            echo json_encode([
                'success' => false, 
                'message' => 'You can only edit programs from your own college.'
            ]);
            exit;
        }
        
        // Also ensure they're not changing the college to something else
        if (isset($data['college']) && $data['college'] != $userCollege) {
            echo json_encode([
                'success' => false, 
                'message' => 'You cannot change the college of a program.'
            ]);
            exit;
        }
    }

    // Special handling for programs update
    if ($table === 'programs') {
        // Sanitize text fields to prevent HTML/script injection
        $textFields = ['college', 'department', 'name', 'specialization'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = sanitize_html_input($data[$field]);
            }
        }

        // Extract and validate data
        $college = $data['college'] ?? null;
        $department = $data['department'] ?? null;
        $name = $data['name'] ?? null;
        $specialization = $data['specialization'] ?? null;

        // Enhanced validation for required fields
        if (empty($college) || empty($name)) {
            $response['message'] = 'College and Program Name are required fields.';
            echo json_encode($response);
            exit;
        }

        // Validate college field - should not be empty or just whitespace
        if (strlen(trim($college)) < 2) {
            $response['message'] = 'College name must be at least 2 characters long.';
            echo json_encode($response);
            exit;
        }

        // Validate program name - should not be empty or just whitespace
        if (strlen(trim($name)) < 2) {
            $response['message'] = 'Program name must be at least 2 characters long.';
            echo json_encode($response);
            exit;
        }

        // Validate program name length (reasonable limit)
        if (strlen($name) > 255) {
            $response['message'] = 'Program name cannot exceed 255 characters.';
            echo json_encode($response);
            exit;
        }

        // Validate college name length
        if (strlen($college) > 255) {
            $response['message'] = 'College name cannot exceed 255 characters.';
            echo json_encode($response);
            exit;
        }

        // Validate department name length if provided
        if (!empty($department) && strlen($department) > 255) {
            $response['message'] = 'Department name cannot exceed 255 characters.';
            echo json_encode($response);
            exit;
        }

        // Validate specialization length if provided
        if (!empty($specialization) && strlen($specialization) > 255) {
            $response['message'] = 'Specialization cannot exceed 255 characters.';
            echo json_encode($response);
            exit;
        }

        // Check for duplicate program name within the same college (excluding current record)
        try {
            $duplicateCheckSql = "SELECT COUNT(*) FROM programs WHERE college = :college AND name = :name AND id != :current_id";
            $params = [':college' => $college, ':name' => $name, ':current_id' => $id];
            
            // If specialization is provided, include it in the uniqueness check
            if (!empty($specialization)) {
                $duplicateCheckSql .= " AND specialization = :specialization";
                $params[':specialization'] = $specialization;
            } else {
                $duplicateCheckSql .= " AND (specialization IS NULL OR specialization = '')";
            }
            
            $stmtDuplicate = $pdo->prepare($duplicateCheckSql);
            $stmtDuplicate->execute($params);
            
            if ($stmtDuplicate->fetchColumn() > 0) {
                $response['message'] = 'A program with this name and specialization already exists in this college.';
                echo json_encode($response);
                exit;
            }
        } catch (PDOException $e) {
            error_log('Error checking for duplicate program: ' . $e->getMessage());
            $response['message'] = 'Error validating program data.';
            echo json_encode($response);
            exit;
        }

        try {
            $programUpdateSql = "UPDATE programs SET
                                    college = :college,
                                    department = :department,
                                    name = :name,
                                    specialization = :specialization,
                                    updated_at = NOW()
                                 WHERE id = :id";
            $stmtProgram = $pdo->prepare($programUpdateSql);

            $programData = [
                ':id' => $id,
                ':college' => $college,
                ':department' => empty($department) ? null : $department,
                ':name' => $name,
                ':specialization' => empty($specialization) ? null : $specialization
            ];

            $stmtProgram->execute($programData);

            $response['success'] = true;
            $response['message'] = "Program updated successfully.";
            error_log("Program with ID: $id updated successfully.");

        } catch (PDOException $e) {
            $response['message'] = "Error updating program: " . $e->getMessage();
            error_log("Error updating program (ID: $id): " . $e->getMessage());
            if ($e->getCode() == '23000') {
                 if (strpos(strtolower($e->getMessage()), 'duplicate entry') !== false) {
                     $response['message'] = "Error: A program with this name might already exist.";
                 } else {
                     $response['message'] = "Error: Database constraint violation. Please check your input.";
                 }
            }
        }
        echo json_encode($response);
        exit;
    }

    // --- UPDATED Rubric Handling ---
    if ($table === 'rubrics') {
        error_log("=== START EDIT RUBRIC (Individual as Numerical) ID: $id ===");
        error_log("Received POST data for rubric update: " . print_r($data, true));

        try {
            // Disable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
            error_log("Foreign key checks disabled for rubric edit (ID: $id).");

            $pdo->beginTransaction();
            // 1. Update the main `rubrics` table (Added is_individual_enabled, handle max_members conditionally)
            $rubricSql = "UPDATE rubrics SET
                            name = :name,
                            description = :description,
                            rubric_type = :rubric_type,
                            is_individual_enabled = :is_individual_enabled, -- Added flag
                            defense_type = :defense_type,
                            rubric_description = :rubric_description,
                            pass_recommendation_text = :pass_recommendation_text,
                            fail_recommendation_text = :fail_recommendation_text,
                            fail_option_text = :fail_option_text,
                            pass_threshold_1 = :pass_threshold_1,
                            pass_threshold_2 = :pass_threshold_2,
                            pass_threshold_3 = :pass_threshold_3,
                            max_total_score = :max_total_score,
                            max_members = :max_members, -- Updated conditionally
                            updated_at = NOW()
                          WHERE id = :id";
            $stmtRubric = $pdo->prepare($rubricSql);

            $is_individual_enabled = ($data['rubric_type'] === 'numerical' && isset($data['is_individual_enabled']) && $data['is_individual_enabled'] == '1') ? 1 : 0;

            // Prepare data for main rubric update
            $rubricData = [
                ':id' => $id, // Bind the ID for the WHERE clause
                ':name' => $data['name'] ?? 'Unnamed Rubric',
                ':description' => $data['description'] ?? '',
                ':rubric_type' => $data['rubric_type'] ?? 'numerical',
                ':is_individual_enabled' => $is_individual_enabled, // New flag
                ':defense_type' => empty($data['defense_type']) ? null : $data['defense_type'],
                ':rubric_description' => $data['rubric_description'] ?? null,
                // Pass/Fail specific fields
                ':pass_recommendation_text' => ($data['rubric_type'] === 'passfail') ? ($data['pass_recommendation_text'] ?? null) : null,
                ':fail_recommendation_text' => ($data['rubric_type'] === 'passfail') ? ($data['fail_recommendation_text'] ?? null) : null,
                ':fail_option_text' => ($data['rubric_type'] === 'passfail') ? ($data['fail_option_text'] ?? null) : null,
                ':pass_threshold_1' => ($data['rubric_type'] === 'passfail') ? ($data['total_pass'] ?? null) : null,
                ':pass_threshold_2' => ($data['rubric_type'] === 'passfail') ? ($data['minor_revision_pass'] ?? null) : null,
                ':pass_threshold_3' => ($data['rubric_type'] === 'passfail') ? ($data['major_revision_pass'] ?? null) : null,
                // Numerical specific fields
                ':max_total_score' => ($data['rubric_type'] === 'numerical') ? ($data['max_total_score'] ?? 0) : 0,
                // Max members only relevant if individual scoring is enabled
                ':max_members' => ($is_individual_enabled) ? ($data['max_members'] ?? 5) : null
            ];

            $stmtRubric->execute($rubricData);
            error_log("Updated rubrics table for ID: " . $id);

            // 2. Clear and Re-insert `rubric_levels`
            $clearLevelsSql = "DELETE FROM rubric_levels WHERE rubric_id = :rubric_id";
            $pdo->prepare($clearLevelsSql)->execute([':rubric_id' => $id]);
            error_log("Cleared rubric_levels for ID: " . $id);

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
                    error_log("Re-inserted " . count($levels) . " rows into rubric_levels for ID: " . $id);
                } else {
                    error_log("Failed to decode levels JSON or it's not an array. Error: " . json_last_error_msg());
                }
            } else {
                error_log("No 'levels' data found in POST for update.");
            }

            // 3. Clear and Re-insert `rubric_criteria` (Added conditional is_individual)
            $clearCriteriaSql = "DELETE FROM rubric_criteria WHERE rubric_id = :rubric_id";
            $pdo->prepare($clearCriteriaSql)->execute([':rubric_id' => $id]);
            error_log("Cleared rubric_criteria for ID: " . $id);

            if (($data['rubric_type'] === 'numerical' || $data['rubric_type'] === 'yesno') && isset($data['criteria'])) {
                $criteria = json_decode($data['criteria'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($criteria)) {
                    $criteriaSql = "INSERT INTO rubric_criteria (rubric_id, criterion_text, criterion_detail, order_index, is_individual)
                                    VALUES (:rubric_id, :criterion_text, :criterion_detail, :order_index, :is_individual)";
                    $stmtCriteria = $pdo->prepare($criteriaSql);
                    foreach ($criteria as $criterion) {
                        // Only save is_individual flag if the rubric itself has individual scoring enabled
                        $criterion_is_individual = ($is_individual_enabled && isset($criterion['is_individual'])) ? $criterion['is_individual'] : 0;

                        $stmtCriteria->execute([
                            ':rubric_id' => $id, // Use the existing rubric ID
                            ':criterion_text' => $criterion['criterion_text'] ?? 'Unnamed Criterion',
                            ':criterion_detail' => $criterion['criterion_detail'] ?? null,
                            ':order_index' => $criterion['order_index'] ?? 0,
                            ':is_individual' => $criterion_is_individual // Store flag conditionally
                        ]);
                    }
                    error_log("Re-inserted " . count($criteria) . " rows into rubric_criteria for ID: " . $id);
                } else {
                    error_log("Failed to decode criteria JSON or it's not an array. Error: " . json_last_error_msg());
                }
            } else {
                error_log("No 'criteria' data found in POST or type is not numerical/yesno for update.");
            }

            // 4. Clear and Re-insert `rubric_programs`
            $clearProgramsSql = "DELETE FROM rubric_programs WHERE rubric_id = :rubric_id";
            $pdo->prepare($clearProgramsSql)->execute([':rubric_id' => $id]);
            error_log("Cleared rubric_programs for ID: " . $id);

            if (isset($data['programs'])) {
                $programs = json_decode($data['programs'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($programs) && !empty($programs)) {
                    $programSql = "INSERT INTO rubric_programs (rubric_id, program_name) VALUES (:rubric_id, :program_name)";
                    $stmtProgram = $pdo->prepare($programSql);
                    foreach ($programs as $programName) {
                        if (!empty($programName)) {
                            $stmtProgram->execute([
                                ':rubric_id' => $id, // Use the existing rubric ID
                                ':program_name' => $programName
                            ]);
                        }
                    }
                    error_log("Re-inserted " . count($programs) . " rows into rubric_programs for ID: " . $id);
                } else {
                    error_log("Failed to decode programs JSON, it's not an array, or it's empty. Error: " . json_last_error_msg());
                }
            } else {
                error_log("No 'programs' data found in POST for update.");
            }

            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'Rubric updated successfully.';
            error_log("=== END EDIT RUBRIC (Individual as Numerical) - SUCCESS ===");

        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = 'Error updating rubric: ' . $e->getMessage();
            error_log("Error occurred, transaction rolled back: " . $e->getMessage());
            error_log("=== END EDIT RUBRIC (Individual as Numerical) - ERROR ===");
        } finally {
            // Always re-enable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
            error_log("Foreign key checks re-enabled for rubric edit (ID: $id).");
        }

        echo json_encode($response);
        exit; // Stop script after handling rubric
    }
    // --- END UPDATED Rubric Handling ---

    // Special handling for teams
    if ($table === 'teams') {
        // Sanitize text fields to prevent HTML/script injection
        $textFields = ['name', 'area_of_expertise', 'program', 'title'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = sanitize_html_input($data[$field]);
            }
        }

        $pdo->beginTransaction();
        try {
            // Fix for program_id field
            if (isset($data['program_id'])) {
                $data['program'] = $data['program_id'];
                unset($data['program_id']);
            }
            
            if (!isset($data['program']) || $data['program'] === '') {
                $data['program'] = 'Unspecified';
            }
            
            // Update the teams table
            $teamUpdateSql = "UPDATE teams SET 
                             name = :name, 
                             area_of_expertise = :area_of_expertise, 
                             program = :program 
                             WHERE id = :id";
            $stmtTeam = $pdo->prepare($teamUpdateSql);
            $teamData = [
                ':id' => $id,
                ':name' => $data['name'],
                ':area_of_expertise' => $data['area_of_expertise'] ?? null,
                ':program' => $data['program']
            ];
            $stmtTeam->execute($teamData);
            
            // Update research title if provided
            if (isset($data['title']) && !empty(trim($data['title']))) {
                $stmtTitle = $pdo->prepare("UPDATE research_titles SET title = :title, updated_at = NOW() WHERE team_id = :team_id");
                $stmtTitle->execute([
                    ':title' => $data['title'],
                    ':team_id' => $id
                ]);
            }
            
            // Get all current members in the team
            $stmtGetAllMembers = $pdo->prepare("SELECT user_id, role FROM team_members WHERE team_id = :team_id");
            $stmtGetAllMembers->execute([':team_id' => $id]);
            $existingMembers = $stmtGetAllMembers->fetchAll(PDO::FETCH_ASSOC);
            $existingMemberIds = array_column($existingMembers, 'user_id');
            
            // Collect the members that should remain from the form submission
            $membersToKeep = [];
            
            // Process existing member roles from the form
            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                $memberIds = $data['member_ids'];
                $memberRoles = isset($data['member_role']) ? $data['member_role'] : [];
                
                for ($i = 0; $i < count($memberIds); $i++) {
                    $userId = $memberIds[$i];
                    $role = isset($memberRoles[$i]) ? $memberRoles[$i] : null;
                    
                    if ($userId && $role) {
                        $membersToKeep[] = $userId;
                        
                        // Update role if it has changed
                        $stmtUpdateRole = $pdo->prepare("UPDATE team_members 
                                                       SET role = :role 
                                                       WHERE team_id = :team_id AND user_id = :user_id");
                        $stmtUpdateRole->execute([
                            ':role' => $role,
                            ':team_id' => $id,
                            ':user_id' => $userId
                        ]);
                        
                        error_log("Updated team member (ID: {$userId}) role to: {$role}");
                    }
                }
            }
            
            // Find members to remove (in existing but not in membersToKeep)
            $membersToRemove = array_diff($existingMemberIds, $membersToKeep);
            
            // Remove members that should no longer be in the team
            if (!empty($membersToRemove)) {
                $placeholders = implode(',', array_fill(0, count($membersToRemove), '?'));
                $stmtRemoveMembers = $pdo->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id IN ($placeholders)");
                
                // First parameter is team_id, followed by each user_id
                $params = array_merge([$id], $membersToRemove);
                $stmtRemoveMembers->execute($params);
                
                error_log("Removed members from team $id: " . implode(", ", $membersToRemove));
            }
            
            // Add new members if any
            if (isset($data['new_user_id']) && is_array($data['new_user_id'])) {
                $newUserIds = $data['new_user_id'];
                $newRoles = isset($data['new_role']) ? $data['new_role'] : [];
                
                for ($i = 0; $i < count($newUserIds); $i++) {
                    $userId = $newUserIds[$i];
                    $role = isset($newRoles[$i]) ? $newRoles[$i] : 'member';
                    
                    if (!empty($userId)) {
                        // Check if this member already exists in the team (shouldn't be needed but just in case)
                        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM team_members WHERE team_id = ? AND user_id = ?");
                        $stmtCheck->execute([$id, $userId]);
                        $exists = $stmtCheck->fetchColumn();
                        
                        if (!$exists) {
                            // Add new member
                            $stmtAddMember = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, ?)");
                            $stmtAddMember->execute([$id, $userId, $role]);
                            
                            error_log("Added new member to team $id: User ID $userId with role $role");
                        } else {
                            // Update role if member already exists
                            $stmtUpdateRole = $pdo->prepare("UPDATE team_members SET role = ? WHERE team_id = ? AND user_id = ?");
                            $stmtUpdateRole->execute([$role, $id, $userId]);
                            
                            error_log("Updated existing member in team $id: User ID $userId with role $role");
                        }
                    }
                }
            }
            
            $pdo->commit();
            $response['success'] = true;
            $response['message'] = "Team updated successfully.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            
            if ($e->getCode() == '23000') {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $response['message'] = "Error: This team name is already in use. Please choose a different name.";
                } else if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                    $response['message'] = "Error: One of the selected team members or program doesn't exist.";
                } else {
                    $response['message'] = "Error: Database constraint violation. Please check your input values.";
                }
            } else {
                $response['message'] = "Error updating team: " . $e->getMessage();
            }
            error_log("Error updating team: " . $e->getMessage());
        }
        
        echo json_encode($response);
        exit; // Stop script after handling team
    }

    // Special handling for users
    if ($table === 'users') {
        // Sanitize text fields to prevent HTML/script injection
        $textFields = ['username', 'email', 'first_name', 'last_name', 'gender', 'headline', 'bio'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = sanitize_html_input($data[$field]);
            }
        }

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // Remove password from update if empty
            unset($data['password']);
        }

        // Fix for program_id field
        if (isset($data['program_id'])) {
            $data['program'] = $data['program_id'];
            unset($data['program_id']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
    }

    // Special handling for research_titles BEFORE calling generic handler
    if ($table === 'research_titles') {
        // Check if team_id is being changed to a team that already has a research title
        if (isset($data['team_id']) && !empty($data['team_id'])) {
            // Get current team_id for this research title
            $stmtCurrent = $pdo->prepare("SELECT team_id FROM research_titles WHERE id = ?");
            $stmtCurrent->execute([$id]);
            $currentTeamId = $stmtCurrent->fetchColumn();
            
            // If team_id is being changed
            if ($currentTeamId != $data['team_id']) {
                // Check if the new team already has a research title
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM research_titles WHERE team_id = ? AND id != ?");
                $stmtCheck->execute([$data['team_id'], $id]);
                $existingCount = $stmtCheck->fetchColumn();
                
                if ($existingCount > 0) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'The selected team already has a research title assigned. Each team can only have one research title.'
                    ]);
                    exit;
                }
            }
        }
        
        // Sanitize text fields to prevent HTML/script injection
        $textFields = ['title', 'description', 'program'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = sanitize_html_input($data[$field]);
            }
        }

        // Map program_id to program if it exists
        if (isset($data['program_id'])) {
            $data['program'] = $data['program_id'];
            unset($data['program_id']);
        }
        
        // Handle the 'approved_at' timestamp based on checkbox value
        $titleWasApproved = false;
        if (isset($data['approved_at'])) {
            if ($data['approved_at'] == '1') {
                // Set to current timestamp only if it's not already set
                $stmtCheck = $pdo->prepare("SELECT approved_at FROM research_titles WHERE id = ?");
                $stmtCheck->execute([$id]);
                $currentApprovedAt = $stmtCheck->fetchColumn();
                if ($currentApprovedAt === null) {
                    $data['approved_at'] = date('Y-m-d H:i:s');
                    $titleWasApproved = true;
                    error_log("DEBUG: Title ID $id was just approved");
                } else {
                    unset($data['approved_at']); 
                    error_log("DEBUG: Title ID $id was already approved - no notification needed");
                }
            } else {
                $data['approved_at'] = null;
                error_log("DEBUG: Title ID $id was unapproved");
            }
        } else {
             $data['approved_at'] = null;
             error_log("DEBUG: Title ID $id approval key not set");
        }

        // Remove team_id if present
        unset($data['team_id']); 
        $data['updated_at'] = date('Y-m-d H:i:s');
    }
    if ($table === 'user_schedules') {
        // Map program_id to program if it exists
        if (isset($data['program_id'])) {
            $data['program'] = $data['program_id'];
            unset($data['program_id']);
        }
        // Ensure year is an integer and within 1-5 (if provided)
        if (isset($data['year'])) {
            $data['year'] = intval($data['year']);
            if ($data['year'] < 1 || $data['year'] > 5) {
                $data['year'] = null;
            }
        }
        // Ensure start_time and end_time are valid time strings
        if (isset($data['start_time'])) {
            $data['start_time'] = date('H:i:s', strtotime($data['start_time']));
        }
        if (isset($data['end_time'])) {
            $data['end_time'] = date('H:i:s', strtotime($data['end_time']));
        }
        // Validate day_of_week
        $validDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        if (isset($data['day_of_week']) && !in_array($data['day_of_week'], $validDays)) {
            $data['day_of_week'] = 'Monday';
        }
    }

    // For other tables, use the generic handler
    require_once __DIR__ . '/edit_functions.php';

    if (function_exists('handleEditSubmission')) {
        $result = handleEditSubmission($pdo, $table, $id, $data); 
        if ($result !== false) {
            $response['success'] = true;
            $response['message'] = ucfirst($table) . ' updated successfully.';
            
            // Send title approval notification if a title was just approved
            if ($table === 'research_titles' && isset($titleWasApproved) && $titleWasApproved) {
                error_log("DEBUG: Sending title approval notifications for title ID $id");
                
                $lockKey = "title_approval_lock_$id";
                if (!isset($_SESSION[$lockKey])) {
                    $_SESSION[$lockKey] = time();
                    
                    $titleStmt = $pdo->prepare("SELECT title, team_id FROM research_titles WHERE id = ?");
                    $titleStmt->execute([$id]);
                    $titleInfo = $titleStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($titleInfo) {
                        error_log("DEBUG: Title info found - Title: " . $titleInfo['title'] . ", Team ID: " . $titleInfo['team_id']);
                        require_once dirname(__DIR__, 2) . '/assets/includes/notification_functions.php';
                        
                        createTitleApprovalNotifications($pdo, $titleInfo['team_id'], $titleInfo['title']);
                        error_log("DEBUG: Title approval notifications sent successfully");
                        
                        if (isset($_SESSION[$lockKey]) && (time() - $_SESSION[$lockKey]) > 30) {
                            unset($_SESSION[$lockKey]);
                        }
                    } else {
                        error_log("DEBUG: No title info found for ID: " . $id);
                        unset($_SESSION[$lockKey]);
                    }
                } else {
                    error_log("DEBUG: Title approval notification blocked by session lock for ID: $id");
                }
            }
        } else {
            if (isset($GLOBALS['edit_error_message'])) {
                 $response['message'] = $GLOBALS['edit_error_message'];
                 unset($GLOBALS['edit_error_message']);
            } else {
                 $response['message'] = ucfirst($table) . ' update failed. Check logs for details.';
            }
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

<?php
function updateItem($pdo, $table, $id, $data) {
    try {
        switch ($table) {
            // ...existing cases...

            case 'requirements':
                // Handle file upload and removal
                $templateFile = null;
                $templateOriginalName = null;
                $updateTemplate = false;
                
                // Check if removing current template
                if (!empty($data['remove_template'])) {
                    // Get current file to delete it
                    $stmt = $pdo->prepare("SELECT template_file FROM requirements WHERE id = ?");
                    $stmt->execute([$id]);
                    $currentFile = $stmt->fetchColumn();
                    
                    if ($currentFile && file_exists('../uploads/requirements/' . $currentFile)) {
                        unlink('../uploads/requirements/' . $currentFile);
                    }
                    
                    $templateFile = null;
                    $templateOriginalName = null;
                    $updateTemplate = true;
                }
                
                // Check if uploading new file
                if (!empty($_FILES['template_file']['name'])) {
                    // Delete old file if exists
                    $stmt = $pdo->prepare("SELECT template_file FROM requirements WHERE id = ?");
                    $stmt->execute([$id]);
                    $oldFile = $stmt->fetchColumn();
                    
                    if ($oldFile && file_exists('../uploads/requirements/' . $oldFile)) {
                        unlink('../uploads/requirements/' . $oldFile);
                    }
                    
                    $uploadResult = handleRequirementTemplateUpload($_FILES['template_file']);
                    if ($uploadResult['success']) {
                        $templateFile = $uploadResult['filename'];
                        $templateOriginalName = $uploadResult['original_name'];
                        $updateTemplate = true;
                    } else {
                        return ['success' => false, 'message' => $uploadResult['error']];
                    }
                }
                
                // Build SQL query
                if ($updateTemplate) {
                    $sql = "UPDATE requirements SET name = ?, description = ?, due_date = ?, template_file = ?, template_original_name = ? WHERE id = ?";
                    $params = [$data['name'], $data['description'], $data['due_date'], $templateFile, $templateOriginalName, $id];
                } else {
                    $sql = "UPDATE requirements SET name = ?, description = ?, due_date = ? WHERE id = ?";
                    $params = [$data['name'], $data['description'], $data['due_date'], $id];
                }
                
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute($params);
                return ['success' => $result, 'message' => $result ? 'Requirement updated successfully' : 'Failed to update requirement'];

            // ...existing cases...
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
    
}
?>