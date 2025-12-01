<?php
// CRITICAL: Start session FIRST before accessing $_SESSION
session_start();

require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../../assets/includes/security_functions.php';

// Set header to return JSON
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// At the beginning of the file, after starting the session and including required files:
require_once '../../assets/includes/auth_functions.php';
require_once __DIR__ . '/section_access.php';

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

    // Remove table from $_POST data that will be used for insertion/update
    $data = $_POST;
    unset($data['table']);

    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables', 'programs', 'default_schedules', 'user_schedules', 'page_content', 'team_members'];

    if (!$table || !in_array($table, $allowedTables)) {
        $response['message'] = 'Invalid table specified.';
        echo json_encode($response);
        exit;
    }

    // Special file upload handling for requirements
    if ($table === 'requirements' && isset($_FILES['template_file']) && $_FILES['template_file']['error'] === UPLOAD_ERR_OK) {
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
        // If non-super admin is adding a program, ensure it's for their college
        if (isset($data['college']) && $data['college'] != $userCollege) {
            echo json_encode([
                'success' => false, 
                'message' => 'You can only add programs for your own college.'
            ]);
            exit;
        }
    }

    if ($table === 'programs') {
        // Sanitize text fields to prevent HTML/script injection
        $textFields = ['college', 'department', 'name', 'specialization'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = sanitize_html_input($data[$field]);
            }
        }

        // Extract data for programs table
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

        // Check for duplicate program name within the same college
        try {
            $duplicateCheckSql = "SELECT COUNT(*) FROM programs WHERE college = :college AND name = :name";
            $params = [':college' => $college, ':name' => $name];
            
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
            $sql = "INSERT INTO programs (college, department, name, specialization, updated_at)
                    VALUES (:college, :department, :name, :specialization, NOW())";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':college' => $college,
                ':department' => empty($department) ? null : $department,
                ':name' => $name,
                ':specialization' => empty($specialization) ? null : $specialization
            ]);

            $response['success'] = true;
            $response['message'] = 'Program added successfully.';

        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                 if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                     $response['message'] = 'A program with this name might already exist.';
                 } else {
                     $response['message'] = 'Database constraint violation. Please check your input values.';
                 }
            } else {
                $response['message'] = 'Error adding program: ' . $e->getMessage();
            }
            error_log('Error adding program: ' . $e->getMessage());
        }

        echo json_encode($response);
        exit;

    } else if ($table === 'rubrics') {
        error_log("=== START ADD RUBRIC (Individual as Numerical) ===");
        error_log("Received POST data for rubric add: " . print_r($data, true));

        try {
            // Disable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
            error_log("Foreign key checks disabled.");

            $pdo->beginTransaction();

            // Sanitize basic rubric fields
            $textFields = ['name', 'description', 'rubric_description', 'pass_recommendation_text', 'fail_recommendation_text', 'fail_option_text'];
            foreach ($textFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = sanitize_html_input($data[$field]);
                }
            }

            // 1. Insert into the main `rubrics` table (Added is_individual_enabled, handle max_members conditionally)
            $rubricSql = "INSERT INTO rubrics (
                            name, description, rubric_type, is_individual_enabled, defense_type,
                            rubric_description, pass_recommendation_text, fail_recommendation_text,
                            fail_option_text, pass_threshold_1, pass_threshold_2, pass_threshold_3,
                            max_total_score, max_members, created_at, updated_at
                          ) VALUES (
                            :name, :description, :rubric_type, :is_individual_enabled, :defense_type,
                            :rubric_description, :pass_recommendation_text, :fail_recommendation_text,
                            :fail_option_text, :pass_threshold_1, :pass_threshold_2, :pass_threshold_3,
                            :max_total_score, :max_members, NOW(), NOW()
                          )";
            $stmtRubric = $pdo->prepare($rubricSql);

            $is_individual_enabled = ($data['rubric_type'] === 'numerical' && isset($data['is_individual_enabled']) && $data['is_individual_enabled'] == '1') ? 1 : 0;

            $rubricData = [
                ':name' => $data['name'] ?? 'Unnamed Rubric',
                ':description' => $data['description'] ?? '',
                ':rubric_type' => $data['rubric_type'] ?? 'numerical',
                ':is_individual_enabled' => $is_individual_enabled, // New flag
                ':defense_type' => empty($data['defense_type']) ? null : $data['defense_type'],
                ':rubric_description' => $data['rubric_description'] ?? null,
                // Pass/Fail specific fields (null if not passfail)
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
            $rubricId = $pdo->lastInsertId();
            error_log("Inserted into rubrics table. Attempted ID: " . $rubricId);

            // --- REFINED VALIDATION ---
            // Check if lastInsertId returned a valid, positive integer ID.
            // lastInsertId can return "0" as a string if the ID column is BIGINT and the value is 0,
            // or false/0 if the operation failed or no ID was generated.
            $rubricIdInt = filter_var($rubricId, FILTER_VALIDATE_INT);
            if ($rubricIdInt === false || $rubricIdInt <= 0) {
                error_log("Failed to retrieve a valid positive integer ID after inserting into rubrics table. Value received: " . print_r($rubricId, true));
                throw new Exception("Failed to retrieve a valid ID after inserting into rubrics table.");
            }
            $rubricId = $rubricIdInt; // Use the validated integer ID
            error_log("Validated Rubric ID: " . $rubricId);
            // --- END REFINED VALIDATION ---

            // 2. Insert into `rubric_levels` (Numerical levels or Pass/Fail modifiers) with sanitization
            if (isset($data['levels'])) {
                $levels = json_decode($data['levels'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($levels)) {
                    $levelSql = "INSERT INTO rubric_levels (rubric_id, level_index, name, description, points_min, points_max, is_range)
                                 VALUES (:rubric_id, :level_index, :name, :description, :points_min, :points_max, :is_range)";
                    $stmtLevel = $pdo->prepare($levelSql);

                    foreach ($levels as $level) {
                        // Sanitize level fields
                        $levelName = sanitize_html_input($level['name'] ?? 'Unnamed Level');
                        $levelDescription = sanitize_html_input($level['description'] ?? '');
                        
                        $stmtLevel->execute([
                            ':rubric_id' => $rubricId, // Use the validated integer ID
                            ':level_index' => $level['level_index'] ?? 0,
                            ':name' => $levelName,
                            ':description' => empty($levelDescription) ? null : $levelDescription,
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

            // 3. Insert into `rubric_criteria` (Numerical or Yes/No rows) with sanitization
            // Added conditional is_individual handling for numerical type
            if (($data['rubric_type'] === 'numerical' || $data['rubric_type'] === 'yesno') && isset($data['criteria'])) {
                $criteria = json_decode($data['criteria'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($criteria)) {
                    $criteriaSql = "INSERT INTO rubric_criteria (rubric_id, criterion_text, criterion_detail, order_index, is_individual, max_score, min_score)
                                    VALUES (:rubric_id, :criterion_text, :criterion_detail, :order_index, :is_individual, :max_score, :min_score)";
                    $stmtCriteria = $pdo->prepare($criteriaSql);

                    foreach ($criteria as $criterion) {
                        // Determine if the criterion is individual (only applicable for numerical rubrics)
                        $criterion_is_individual = ($data['rubric_type'] === 'numerical' && $is_individual_enabled && isset($criterion['is_individual']) && $criterion['is_individual'] == '1') ? 1 : 0;

                        // Sanitize criterion fields
                        $criterionText = sanitize_html_input($criterion['criterion_text'] ?? 'Unnamed Criterion');
                        $criterionDetail = '';
                        
                        // Handle criterion_detail based on type
                        if (isset($criterion['criterion_detail'])) {
                            if (is_array($criterion['criterion_detail'])) {
                                // If it's an array, sanitize each element
                                $sanitizedDetails = array_map('sanitize_html_input', $criterion['criterion_detail']);
                                $criterionDetail = json_encode($sanitizedDetails);
                            } else {
                                // If it's a string (possibly JSON), try to decode and sanitize
                                $decoded = json_decode($criterion['criterion_detail'], true);
                                if (is_array($decoded)) {
                                    $sanitizedDetails = array_map('sanitize_html_input', $decoded);
                                    $criterionDetail = json_encode($sanitizedDetails);
                                } else {
                                    // Plain string, just sanitize
                                    $criterionDetail = sanitize_html_input($criterion['criterion_detail']);
                                }
                            }
                        }

                        // Handle max_score and min_score for individual criteria
                        $maxScore = null;
                        $minScore = null;
                        if ($criterion_is_individual) {
                            if (isset($criterion['criterion_score'])) {
                                $maxScore = filter_var($criterion['criterion_score'], FILTER_VALIDATE_FLOAT);
                                if ($maxScore === false || $maxScore < 0) {
                                    $maxScore = null;
                                }
                            }
                            if (isset($criterion['criterion_min_score'])) {
                                $minScore = filter_var($criterion['criterion_min_score'], FILTER_VALIDATE_FLOAT);
                                if ($minScore === false || $minScore < 0) {
                                    $minScore = 0.00; // Default to 0 if invalid
                                }
                            } else {
                                $minScore = 0.00; // Default min score
                            }
                        }

                        $stmtCriteria->execute([
                            ':rubric_id' => $rubricId, // Use the validated integer ID
                            ':criterion_text' => $criterionText,
                            ':criterion_detail' => empty($criterionDetail) ? null : $criterionDetail, // For Yes/No description
                            ':order_index' => $criterion['order_index'] ?? 0,
                            ':is_individual' => $criterion_is_individual, // Store flag conditionally
                            ':max_score' => $maxScore, // Store max score for individual criteria
                            ':min_score' => $minScore // Store min score for individual criteria
                        ]);
                    }
                    error_log("Inserted " . count($criteria) . " rows into rubric_criteria.");
                } else {
                    error_log("Failed to decode criteria JSON or it's not an array. Error: " . json_last_error_msg());
                }
            } else {
                error_log("No 'criteria' data found in POST or type is not numerical/yesno.");
            }

            // 4. Insert into `rubric_programs` (no sanitization needed for program associations)
            if (isset($data['programs'])) {
                $programs = json_decode($data['programs'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($programs)) {
                    $programSql = "INSERT INTO rubric_programs (rubric_id, program_name) VALUES (:rubric_id, :program_name)";
                    $stmtProgram = $pdo->prepare($programSql);
                    foreach ($programs as $programName) {
                        $stmtProgram->execute([
                            ':rubric_id' => $rubricId, // Use the validated integer ID
                            ':program_name' => $programName
                        ]);
                    }
                    error_log("Inserted " . count($programs) . " rows into rubric_programs.");
                } else {
                    error_log("Failed to decode programs JSON or it's not an array. Error: " . json_last_error_msg());
                }
            } else {
                error_log("No 'programs' data found in POST.");
            }

            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'Rubric added successfully.';
            error_log("=== END ADD RUBRIC (Individual as Numerical) - SUCCESS ===");
        } catch (Exception $e) {
            // Rollback transaction if started
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $response['message'] = 'Error adding rubric: ' . $e->getMessage();
            error_log("Error occurred, transaction rolled back: " . $e->getMessage());
            error_log("=== END ADD RUBRIC (Individual as Numerical) - ERROR ===");
        } finally {
            // Always re-enable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
            error_log("Foreign key checks re-enabled.");
        }

        echo json_encode($response);
        exit; // Stop script after handling rubric
    }
    // --- END UPDATED Rubric Handling ---

    // Special handling for research_titles
    if ($table === 'research_titles') {
        // Check if the team already has a research title assigned
        if (isset($data['team_id']) && !empty($data['team_id'])) {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM research_titles WHERE team_id = ?");
            $stmtCheck->execute([$data['team_id']]);
            $existingCount = $stmtCheck->fetchColumn();
            
            if ($existingCount > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'This team already has a research title assigned. Each team can only have one research title.'
                ]);
                exit;
            }
        }
        
        // Sanitize text fields to prevent HTML/script injection
        $textFields = ['title', 'description', 'program'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = sanitize_html_input($data[$field]);
            }
        }

        $approved = isset($data['approved']) ? date('Y-m-d H:i:s') : null;
        unset($data['approved']);
        $data['approved_at'] = $approved;
    }

    // Special handling for teams
    if ($table === 'teams' && $usertype == 0 && $userId != 0 && $userCollege) {
        // Check if the program belongs to the admin's college
        $stmtCheck = $pdo->prepare("SELECT college FROM programs WHERE CONCAT(name, IFNULL(CONCAT(' - ', specialization), '')) = :program_name");
        $stmtCheck->execute([':program_name' => $data['program'] ?? '']);
        $programCollege = $stmtCheck->fetchColumn();
        
        if ($programCollege && $programCollege != $userCollege) {
            echo json_encode([
                'success' => false, 
                'message' => 'You can only add teams for programs in your own college.'
            ]);
            exit;
        }
    }

    // 🔐 Section-based permission check for professors (usertype 2)
    if ($table === 'teams' && $usertype == 2 && $userId != 0) {
        // Collect all member IDs that will be added to this team
        $memberIds = [];
        
        // Get member IDs from 'members' field if provided (JSON or array format)
        if (isset($data['members']) && !empty($data['members'])) {
            $members = $data['members'];
            if (is_string($members)) {
                $members = json_decode($members, true);
            }
            if (is_array($members)) {
                foreach ($members as $member) {
                    if (isset($member['id'])) {
                        $memberIds[] = $member['id'];
                    }
                }
            }
        }
        
        // Get member IDs from 'new_user_id' field (form input)
        if (isset($data['new_user_id']) && is_array($data['new_user_id'])) {
            foreach ($data['new_user_id'] as $userId_member) {
                if (!empty($userId_member)) {
                    $memberIds[] = $userId_member;
                }
            }
        }
        
        // Check if professor can create team with these members
        $permCheck = canProfessorCreateTeam($pdo, $userId, $memberIds);
        if (!$permCheck['canCreate']) {
            echo json_encode([
                'success' => false, 
                'message' => $permCheck['message']
            ]);
            exit;
        }
    }

    if ($table === 'teams') {
        // Sanitize text fields to prevent HTML/script injection
        $textFields = ['name', 'program', 'area_of_expertise', 'title'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = sanitize_html_input($data[$field]);
            }
        }

        $pdo->beginTransaction();
        try {
            // Fix for program_id field - rename it to match the database column name
            if (isset($data['program_id'])) {
                $data['program'] = $data['program_id'];
                unset($data['program_id']);
            }

            // Ensure program has a value to avoid NULL constraint errors
            if (!isset($data['program']) || $data['program'] === '') {
                $data['program'] = 'Unspecified';
            }
            
            // Handle title_proposal checkbox
            $titleProposal = isset($data['title_proposal']) && $data['title_proposal'] == 1 ? 1 : 0;

            $stmt = $pdo->prepare("INSERT INTO teams (name, program, area_of_expertise, title_proposal, created_at) VALUES (:name, :program, :area_of_expertise, :title_proposal, NOW())");
            $stmt->execute([
                'name' => $data['name'],
                'program' => $data['program'],
                'area_of_expertise' => $data['area_of_expertise'] ?? null,
                'title_proposal' => $titleProposal
            ]);

            $teamId = $pdo->lastInsertId();

            // Use title if provided, otherwise use team name
            $title = !empty($data['title']) ? $data['title'] : $data['name'];

            $stmt = $pdo->prepare("INSERT INTO research_titles (team_id, title, created_at, updated_at) VALUES (:team_id, :title, NOW(), NOW())");
            $stmt->execute([
                'team_id' => $teamId,
                'title' => $title
            ]);

            // 🎓 TITLE PROPOSAL AUTO-ASSIGNMENT: If title_proposal=1, auto-assign current user as adviser
            if ($titleProposal == 1) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
                    $stmt->execute([
                        'team_id' => $teamId,
                        'user_id' => $userId,
                        'role' => 'adviser'
                    ]);
                    error_log("Title proposal team created: Auto-assigned current user (ID: $userId) as adviser to team $teamId");
                } catch (PDOException $adviserError) {
                    error_log("Note: Could not auto-assign user as adviser: " . $adviserError->getMessage());
                    // Don't fail the team creation if adviser assignment fails
                }
            }

            // Improved team member handling with better error messages
            if (isset($data['members']) && !empty($data['members'])) {
                $members = $data['members'];
                if (is_string($members)) {
                    $members = json_decode($members, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception('Invalid team members data structure: ' . json_last_error_msg());
                    }
                }

                if (is_array($members)) {
                    foreach ($members as $member) {
                        if (isset($member['id']) && isset($member['role'])) {
                            try {
                                $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
                                $stmt->execute([
                                    'team_id' => $teamId,
                                    'user_id' => $member['id'],
                                    'role' => $member['role']
                                ]);
                            } catch (PDOException $memberError) {
                                // Log the specific error for each member but continue adding others
                                error_log("Error adding team member (ID: {$member['id']}): " . $memberError->getMessage());
                            }
                        } else {
                            error_log("Missing required fields for team member: " . print_r($member, true));
                        }
                    }
                }
            }

            // Also handle new member inputs from form
            if (isset($data['new_user_id']) && is_array($data['new_user_id'])) {
                $newUserIds = $data['new_user_id'];
                $newRoles = isset($data['new_role']) ? $data['new_role'] : [];

                for ($i = 0; $i < count($newUserIds); $i++) {
                    $userId = $newUserIds[$i];
                    $role = isset($newRoles[$i]) ? $newRoles[$i] : 'member';

                    if (!empty($userId)) {
                        try {
                            // Check if user exists first
                            $checkStmt = $pdo->prepare("SELECT id, first_name, last_name, usertype FROM users WHERE id = ?");
                            $checkStmt->execute([$userId]);
                            $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($user) {
                                $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
                                $stmt->execute([
                                    'team_id' => $teamId,
                                    'user_id' => $userId,
                                    'role' => $role
                                ]);
                                error_log("Added team member: {$user['first_name']} {$user['last_name']} as $role to team $teamId");
                            } else {
                                error_log("User not found: ID $userId");
                            }
                        } catch (PDOException $memberError) {
                            error_log("Error adding team member (ID: {$userId}): " . $memberError->getMessage());
                        }
                    }
                }
            }

            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'Team added successfully.';
        } catch (Exception $e) {
            $pdo->rollBack();

            if ($e instanceof PDOException && $e->getCode() == '23000') {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $response['message'] = "Error: This team name is already in use. Please choose a different name.";
                } else if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                    $response['message'] = "Error: One of the selected team members or program doesn't exist.";
                } else if (strpos($e->getMessage(), 'Column \'program\' cannot be null') !== false) {
                    $response['message'] = "Error: Program field cannot be empty. Please select a program.";
                } else {
                    $response['message'] = "Error: Database constraint violation. Please check your input values.";
                }
            } else {
                $response['message'] = 'Error adding team: ' . $e->getMessage();
            }
            error_log('Error adding team: ' . $e->getMessage());
        }

        echo json_encode($response);
        exit;
    }

    // 🎓 Special handling for team_members (to add adviser to teams)
    if ($table === 'team_members') {
        try {
            // Required fields for adding team member
            $teamId = $data['team_id'] ?? null;
            $userId = $data['user_id'] ?? null;
            $role = $data['role'] ?? 'member';
            
            if (!$teamId || !$userId) {
                $response['message'] = 'Team ID and User ID are required.';
                echo json_encode($response);
                exit;
            }
            
            // Insert team member
            $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
            $stmt->execute([
                'team_id' => $teamId,
                'user_id' => $userId,
                'role' => $role
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Team member added successfully.';
        } catch (Exception $e) {
            if ($e instanceof PDOException && $e->getCode() == '23000') {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $response['message'] = "Error: This team member is already in the team.";
                } else if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                    $response['message'] = "Error: Team or user does not exist.";
                } else {
                    $response['message'] = "Error: Database constraint violation.";
                }
            } else {
                $response['message'] = 'Error adding team member: ' . $e->getMessage();
            }
            error_log('Error adding team member: ' . $e->getMessage());
        }

        echo json_encode($response);
        exit;
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

        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Fix for program_id field
        if (isset($data['program_id'])) {
            $data['program'] = $data['program_id'];
            unset($data['program_id']);
        }

        // Set default values for required fields
        $data['created_at'] = $data['created_at'] ?? null;
        $data['updated_at'] = date('Y-m-d H:i:s');
    }

    if ($table === 'defense_schedules') {
        $panelist_ids = isset($data['panelist_id']) ? (array)$data['panelist_id'] : [];
        $data['panelist_id']  = $panelist_ids[0] ?? null;
        $data['panelist_id2'] = $panelist_ids[1] ?? null;
        $data['panelist_id3'] = $panelist_ids[2] ?? null;
        
        // Set default values
        $data['status'] = $data['status'] ?? 'scheduled';
        $data['approval_status'] = $data['approval_status'] ?? 'pending';
        $data['created_at'] = date('Y-m-d H:i:s');
    }

    // Handle env_variables tables
    if ($table === 'env_variables') {
        // Sanitize text fields to prevent HTML/script injection
        $textFields = ['key', 'value', 'description'];
        foreach ($textFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = sanitize_html_input($data[$field]);
            }
        }

        // Validate required fields
        $requiredFields = ['key', 'value'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $response['message'] = "Field '$field' is required.";
                echo json_encode($response);
                exit;
            }
        }

        // Validate key format - should be alphanumeric with underscores
        if (!preg_match('/^[A-Z0-9_]+$/', $data['key'])) {
            $response['message'] = 'Key must contain only uppercase letters, numbers, and underscores.';
            echo json_encode($response);
            exit;
        }

        // Set defaults
        $data['description'] = $data['description'] ?? '';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
    }

    // Handle page_content tables
    if ($table === 'page_content') {
        // Sanitize text fields - but allow HTML content in the content field
        if (isset($data['title'])) {
            $data['title'] = sanitize_html_input($data['title']);
        }
        if (isset($data['slug'])) {
            $data['slug'] = sanitize_html_input($data['slug']);
        }
        // Note: content field allows HTML, so we don't sanitize it

        // Validate required fields
        $requiredFields = ['title', 'slug', 'content'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $response['message'] = "Field '$field' is required.";
                echo json_encode($response);
                exit;
            }
        }

        // Validate slug format - should be URL-friendly
        if (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
            $response['message'] = 'Slug must contain only lowercase letters, numbers, and hyphens.';
            echo json_encode($response);
            exit;
        }

        // Set defaults
        $data['status'] = $data['status'] ?? 'draft';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
    }

    // Handle  user_schedules tables
    if ($table === 'user_schedules') {
            $requiredFields = ['room', 'day_of_week', 'class_name', 'start_time', 'end_time'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $response['message'] = "Field '$field' is required.";
                    echo json_encode($response);
                    exit;
                }
            }

            $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            if (!in_array($data['day_of_week'], $validDays)) {
                $response['message'] = 'Invalid day of week value.';
                echo json_encode($response);
                exit;
            }
    }

    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));

    $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");

    try {
        $stmt->execute($data);
        
        // CREATE DEFENSE SCHEDULE NOTIFICATIONS AND APPROVAL WORKFLOW FOR MANUAL CREATION
        if ($table === 'defense_schedules') {
            require_once __DIR__ . '/../../assets/includes/notification_functions.php';
            
            $scheduleId = $pdo->lastInsertId();
            $teamId = $data['team_id'];
            $panelistIds = [$data['panelist_id'], $data['panelist_id2'], $data['panelist_id3']];
            $scheduleDate = $data['schedule_date'];
            $startTime = date('H:i', strtotime($data['start_time']));
            $endTime = date('H:i', strtotime($data['end_time']));
            $room = $data['room'];
            
            // CREATE PANELIST APPROVAL RECORDS (same as generated schedules)
            $approvalStmt = $pdo->prepare("
                INSERT INTO panelist_approvals (defense_schedule_id, panelist_id) 
                VALUES (?, ?)
            ");
            
            // Create approval record for each panelist
            foreach ($panelistIds as $panelistId) {
                if ($panelistId && $panelistId !== '') {
                    $approvalStmt->execute([$scheduleId, $panelistId]);
                }
            }
            
            // Create approval notifications for panelists (consistent with generated schedules)
            createDefenseApprovalNotifications($pdo, $scheduleId, $teamId, $panelistIds, $scheduleDate, $startTime, $endTime, $room);
            
            // Create regular notifications for team members (they don't need to approve)
            $teamMemberIds = getTeamMembersForNotifications($teamId);
            $formattedDate = date('F j, Y', strtotime($scheduleDate));
            $formattedTime = date('g:i A', strtotime($startTime)) . ' - ' . date('g:i A', strtotime($endTime));
            $messageForTeam = "Your team's defense has been scheduled for {$formattedDate} at {$formattedTime} in {$room}. Waiting for panelist approval.";
            
            foreach ($teamMemberIds as $userId) {
                createNotification($pdo, $userId, 'Defense Schedule Created', $messageForTeam, 'defense_scheduled', $scheduleId);
            }
            
            error_log("Manual defense schedule created with approval workflow for schedule ID: $scheduleId");
        }
        
        $response['success'] = true;
        $response['message'] = ucfirst($table) . ' added successfully.';
    } catch (Exception $e) {
        $errorCode = $e->getCode();

        if ($errorCode == 23000) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if ($table === 'users' && strpos($e->getMessage(), 'username') !== false) {
                    $response['message'] = 'This username already exists. Please choose a different username.';
                } else if ($table === 'users' && strpos($e->getMessage(), 'email') !== false) {
                    $response['message'] = 'This email address is already registered. Please use a different email.';
                } else if ($table === 'teams' && strpos($e->getMessage(), 'name') !== false) {
                    $response['message'] = 'A team with this name already exists. Please choose a different team name.';
                } else {
                    $response['message'] = 'This record already exists. Please check your input for duplicates.';
                }
            } else if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $response['message'] = 'Error: Invalid reference to another record. Please ensure all related items exist.';
            } else {
                $response['message'] = 'Database constraint violation. Please check your input values.';
            }
        } else {
            $response['message'] = 'Error adding ' . $table . '. Please check your input and try again.';
        }

        error_log('Database error in add_items.php: ' . $e->getMessage());
    }

    echo json_encode($response);
} else {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
}

// New file upload handling functions
function addItem($pdo, $table, $data) {
    try {
        switch ($table) {
            // ...existing cases...

            case 'requirements':
                // Handle file upload
                $templateFile = null;
                $templateOriginalName = null;
                
                if (!empty($_FILES['template_file']['name'])) {
                    $uploadResult = handleRequirementTemplateUpload($_FILES['template_file']);
                    if ($uploadResult['success']) {
                        $templateFile = $uploadResult['filename'];
                        $templateOriginalName = $uploadResult['original_name'];
                    } else {
                        return ['success' => false, 'message' => $uploadResult['error']];
                    }
                }
                
                // Get new requirement type and multi-submission fields
                $requirementType = $data['requirement_type'] ?? 'general';
                $allowMultipleSubmissions = isset($data['allow_multiple_submissions']) && $data['allow_multiple_submissions'] == '1' ? 1 : 0;
                $maxSubmissions = intval($data['max_submissions'] ?? 1);
                
                // Validate max_submissions (max is 3)
                if ($maxSubmissions < 1) $maxSubmissions = 1;
                if ($maxSubmissions > 3) $maxSubmissions = 3;
                
                $sql = "INSERT INTO requirements (name, description, due_date, template_file, template_original_name, requirement_type, allow_multiple_submissions, max_submissions) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    $data['name'],
                    $data['description'],
                    $data['due_date'],
                    $templateFile,
                    $templateOriginalName,
                    $requirementType,
                    $allowMultipleSubmissions,
                    $maxSubmissions
                ]);
                return ['success' => $result, 'message' => $result ? 'Requirement added successfully' : 'Failed to add requirement'];

            // ...existing cases...
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

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
    
    if ($file['size'] > 10 * 1024 * 1024) // 10MB limit
        return ['success' => false, 'error' => 'File size too large. Maximum 10MB allowed.'];
    
    
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
?>
