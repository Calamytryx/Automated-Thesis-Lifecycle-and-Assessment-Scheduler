<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

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

    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables', 'programs'];

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

    // Special handling for programs update (moved from generic handler)
    if ($table === 'programs') {
        try {
            // Prepare the SQL update statement
            $programUpdateSql = "UPDATE programs SET
                                    college = :college,
                                    department = :department,
                                    name = :name,
                                    specialization = :specialization,
                                    updated_at = NOW()
                                 WHERE id = :id";
            $stmtProgram = $pdo->prepare($programUpdateSql);

            // Prepare data for the update
            $programData = [
                ':id' => $id,
                ':college' => $data['college'] ?? null, // Allow null if not provided or empty
                ':department' => $data['department'] ?? null, // Allow null
                ':name' => $data['name'] ?? 'Unnamed Program', // Default if name is missing
                ':specialization' => $data['specialization'] ?? null // Allow null
            ];

            // Execute the update
            $stmtProgram->execute($programData);

            $response['success'] = true;
            $response['message'] = "Program updated successfully.";
            error_log("Program with ID: $id updated successfully.");

        } catch (PDOException $e) {
            $response['message'] = "Error updating program: " . $e->getMessage();
            // Log the detailed error for debugging
            error_log("Error updating program (ID: $id): " . $e->getMessage());
            // Optionally check for specific error codes like duplicate entries if 'name' needs to be unique
            if ($e->getCode() == '23000') { // Integrity constraint violation
                 if (strpos(strtolower($e->getMessage()), 'duplicate entry') !== false) {
                     $response['message'] = "Error: A program with this name might already exist.";
                 } else {
                     $response['message'] = "Error: Database constraint violation. Please check your input.";
                 }
            }
        }
        echo json_encode($response);
        exit; // Stop script after handling program
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
        $pdo->beginTransaction();
        try {
            // Fix for program_id field - rename it to match the database column name
            if (isset($data['program_id'])) {
                $data['program'] = $data['program_id']; // Map program_id from form to program in database
                unset($data['program_id']); // Remove the original key
            }
            
            // Ensure program has a value to avoid NULL constraint errors
            if (!isset($data['program']) || $data['program'] === '') {
                $data['program'] = 'Unspecified'; // Default value for required field
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
                $stmtTitle = $pdo->prepare("UPDATE research_titles SET title = :title WHERE team_id = :team_id");
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
            
            // Provide user-friendly error message
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
        // Fix for program_id field - rename it to match the database column name
        if (isset($data['program_id'])) {
            $data['program'] = $data['program_id']; // Map program_id from form to program in database
            unset($data['program_id']); // Remove the original key
        }
        
        // Ensure program has a value to avoid NULL constraint errors
        if (!isset($data['program']) || $data['program'] === '') {
            $data['program'] = 'Unspecified'; // Default value for required field
        }
        
        // Update the users table
        $userUpdateSql = "UPDATE users SET 
                         username = :username, 
                         email = :email, 
                         program = :program 
                         WHERE id = :id";
        $stmtUser = $pdo->prepare($userUpdateSql);
        $userData = [
            ':id' => $id,
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':program' => $data['program']
        ];
        $stmtUser->execute($userData);
        
        // Update user roles if provided
        if (isset($data['role']) && is_array($data['role'])) {
            $roleIdInputs = isset($_POST['role_ids']) ? $_POST['role_ids'] : [];
            $roles = $data['role'];
            
            for ($i = 0; $i < count($roles); $i++) {
                $roleId = $roleIdInputs[$i] ?? null;
                $role = $roles[$i];
                
                if ($roleId && $role) {
                    $stmtUpdateRole = $pdo->prepare("UPDATE user_roles SET role = :role WHERE id = :id");
                    $stmtUpdateRole->execute([
                        ':role' => $role,
                        ':id' => $roleId
                    ]);
                }
            }
        }
        
        $response['success'] = true;
        $response['message'] = "User updated successfully.";
        echo json_encode($response);
        exit; // Stop script after handling user
    }

    // Special handling for research_titles BEFORE calling generic handler
    if ($table === 'research_titles') {
        // Map program_id to program if it exists (shouldn't if JS is correct, but as fallback)
        if (isset($data['program_id'])) {
            $data['program'] = $data['program_id'];
            unset($data['program_id']);
        }
        
        // Handle the 'approved_at' timestamp based on checkbox value
        if (isset($data['approved_at'])) {
            if ($data['approved_at'] == '1') { // Checkbox was checked
                // Set to current timestamp only if it's not already set
                $stmtCheck = $pdo->prepare("SELECT approved_at FROM research_titles WHERE id = ?");
                $stmtCheck->execute([$id]);
                $currentApprovedAt = $stmtCheck->fetchColumn();
                if ($currentApprovedAt === null) {
                    $data['approved_at'] = date('Y-m-d H:i:s'); // Set current timestamp
                } else {
                    // Already approved, keep existing timestamp - remove from $data to avoid overwrite
                    unset($data['approved_at']); 
                }
            } else { // Checkbox was unchecked (value '0' or not present)
                $data['approved_at'] = null; // Set to NULL
            }
        } else {
             // If key isn't even set (e.g., form issue), assume unchecking
             $data['approved_at'] = null;
        }

        // Remove team_id if present, as it shouldn't be editable directly here
        unset($data['team_id']); 
    }

    // For other tables (and now research_titles after preprocessing), use the generic handler
    require_once __DIR__ . '/edit_functions.php'; // Make sure this file exists and functions are correct

    // Check if handleEditSubmission exists and call it
    if (function_exists('handleEditSubmission')) {
        // The $data array now has the correct 'program' key for research_titles
        $result = handleEditSubmission($pdo, $table, $id, $data); 
        if ($result !== false) {
            $response['success'] = true;
            $response['message'] = ucfirst($table) . ' updated successfully.';
        } else {
            // Check if a specific message was set in handleEditSubmission
            if (isset($GLOBALS['edit_error_message'])) {
                 $response['message'] = $GLOBALS['edit_error_message'];
                 unset($GLOBALS['edit_error_message']); // Clear global message
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