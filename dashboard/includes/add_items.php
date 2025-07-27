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

    // Remove table from $_POST data that will be used for insertion/update
    $data = $_POST;
    unset($data['table']);

    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables', 'programs', 'default_schedules', 'user_schedules'];

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
        // Extract data for programs table
        $college = $data['college'] ?? null;
        $department = $data['department'] ?? null;
        $name = $data['name'] ?? null;
        $specialization = $data['specialization'] ?? null;

        // Basic validation for required fields
        if (empty($college) || empty($name)) {
            $response['message'] = 'College and Program Name are required fields.';
            echo json_encode($response);
            exit;
        }

        try {
            $sql = "INSERT INTO programs (college, department, name, specialization, updated_at)
                    VALUES (:college, :department, :name, :specialization, NOW())";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':college' => $college,
                ':department' => $department,
                ':name' => $name,
                ':specialization' => $specialization
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

            // 2. Insert into `rubric_levels` (Numerical levels or Pass/Fail modifiers)
            if (isset($data['levels'])) {
                $levels = json_decode($data['levels'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($levels)) {
                    $levelSql = "INSERT INTO rubric_levels (rubric_id, level_index, name, description, points_min, points_max, is_range)
                                 VALUES (:rubric_id, :level_index, :name, :description, :points_min, :points_max, :is_range)";
                    $stmtLevel = $pdo->prepare($levelSql);

                    foreach ($levels as $level) {
                        $stmtLevel->execute([
                            ':rubric_id' => $rubricId, // Use the validated integer ID
                            ':level_index' => $level['level_index'] ?? 0,
                            ':name' => $level['name'] ?? 'Unnamed Level',
                            ':description' => $level['description'] ?? null,
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

            // 3. Insert into `rubric_criteria` (Numerical or Yes/No rows)
            // Added conditional is_individual handling for numerical type
            if (($data['rubric_type'] === 'numerical' || $data['rubric_type'] === 'yesno') && isset($data['criteria'])) {
                $criteria = json_decode($data['criteria'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($criteria)) {
                    $criteriaSql = "INSERT INTO rubric_criteria (rubric_id, criterion_text, criterion_detail, order_index, is_individual)
                                    VALUES (:rubric_id, :criterion_text, :criterion_detail, :order_index, :is_individual)";
                    $stmtCriteria = $pdo->prepare($criteriaSql);

                    foreach ($criteria as $criterion) {
                        // Determine if the criterion is individual (only applicable for numerical rubrics)
                        $criterion_is_individual = ($data['rubric_type'] === 'numerical' && $is_individual_enabled && isset($criterion['is_individual']) && $criterion['is_individual'] == '1') ? 1 : 0;

                        $stmtCriteria->execute([
                            ':rubric_id' => $rubricId, // Use the validated integer ID
                            ':criterion_text' => $criterion['criterion_text'] ?? 'Unnamed Criterion',
                            ':criterion_detail' => $criterion['criterion_detail'] ?? null, // For Yes/No description
                            ':order_index' => $criterion['order_index'] ?? 0,
                            ':is_individual' => $criterion_is_individual // Store flag conditionally
                        ]);
                    }
                    error_log("Inserted " . count($criteria) . " rows into rubric_criteria.");
                } else {
                    error_log("Failed to decode criteria JSON or it's not an array. Error: " . json_last_error_msg());
                }
            } else {
                error_log("No 'criteria' data found in POST or type is not numerical/yesno.");
            }

            // 4. Insert into `rubric_programs`
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

    if ($table === 'teams') {
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

            $stmt = $pdo->prepare("INSERT INTO teams (name, program, area_of_expertise, created_at) VALUES (:name, :program, :area_of_expertise, NOW())");
            $stmt->execute([
                'name' => $data['name'],
                'program' => $data['program'],
                'area_of_expertise' => $data['area_of_expertise'] ?? null
            ]);

            $teamId = $pdo->lastInsertId();

            // Use title if provided, otherwise use team name
            $title = !empty($data['title']) ? $data['title'] : $data['name'];

            $stmt = $pdo->prepare("INSERT INTO research_titles (team_id, title, created_at, updated_at) VALUES (:team_id, :title, NOW(), NOW())");
            $stmt->execute([
                'team_id' => $teamId,
                'title' => $title
            ]);

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
                            $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
                            $stmt->execute([
                                'team_id' => $teamId,
                                'user_id' => $userId,
                                'role' => $role
                            ]);
                        } catch (PDOException $memberError) {
                            error_log("Error adding new team member (ID: {$userId}): " . $memberError->getMessage());
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

    // Special handling for users
    if ($table === 'users') {
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
        
        // CREATE DEFENSE SCHEDULE NOTIFICATIONS FOR MANUAL CREATION
        if ($table === 'defense_schedules') {
            require_once __DIR__ . '/../../assets/includes/notification_functions.php';
            
            $scheduleId = $pdo->lastInsertId();
            $teamId = $data['team_id'];
            $panelistIds = [$data['panelist_id'], $data['panelist_id2'], $data['panelist_id3']];
            $scheduleDate = $data['schedule_date'];
            $startTime = date('H:i', strtotime($data['start_time']));
            $endTime = date('H:i', strtotime($data['end_time']));
            $room = $data['room'];
            
            createDefenseScheduleNotifications($pdo, $scheduleId, $teamId, $panelistIds, $scheduleDate, $startTime, $endTime, $room);
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
                
                $sql = "INSERT INTO requirements (name, description, due_date, template_file, template_original_name) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    $data['name'],
                    $data['description'],
                    $data['due_date'],
                    $templateFile,
                    $templateOriginalName
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
