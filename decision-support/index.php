<?php
session_start();
require '../assets/setup/db.inc.php'; // Adjust path as needed

$done_evaluating = false; // Initialize evaluation status

// --- Configuration ---
$page_title = "Defense Evaluation"; // Updated Title
$error_ref_prefix = "DS-FETCH-"; // Prefix for error references
define('TITLE', $page_title); // Define TITLE for header layout

// --- Get Input Parameters (New Logic) ---
$schedule_id = filter_input(INPUT_GET, 'schedule_id', FILTER_VALIDATE_INT);
$group_id = filter_input(INPUT_GET, 'group_id', FILTER_VALIDATE_INT); // Optional - will be auto-determined if not provided

echo '<script>';
echo '  console.log("Rubric Group ID (from URL):", ' . json_encode($group_id, JSON_NUMERIC_CHECK) . ');';
echo '</script>';

$evaluator_id = $_SESSION['id'] ?? null;

// Helper function to fetch all evaluations (if needed)
function fetchevaluations($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM evaluation_per_panel");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if evaluator has already evaluated this schedule
$done_evaluating = false;

// --- Validate Input (New Logic) ---
if (!$schedule_id || !$evaluator_id) {
    $missing = [];
    if (!$schedule_id) $missing[] = 'Schedule ID';
    if (!$evaluator_id) $missing[] = 'Evaluator ID (Session)';
    error_log("Missing required parameters for decision-support: Schedule={$schedule_id}, Evaluator={$evaluator_id}");
    // Redirect or display error using layout if possible
    include '../assets/layouts/header.php';
    echo "<div class='container mt-5'><div class='alert alert-danger'>Error: Missing required parameters. Please ensure you are logged in and accessing this page with a valid schedule ID. Missing: " . implode(', ', $missing) . "</div></div>";
    include '../assets/layouts/footer.php';
    exit;
}
// Note: group_id is optional and will be auto-determined based on defense type and program

// --- Data Fetching (New Logic + PDF Filename Fetch + Rubric Group Details) ---
$schedule_info = null;
$students = [];
$rubric_group_details = null; // <-- Added for group name/desc
$rubrics_in_group = [];
$existing_evaluation = null;
$pdf_file_name = null;
$adviser_name = null;
$defense_type = null; // <-- NEW: Store defense type

try {
    // 1. Fetch Defense Schedule Info & Team ID (including college from team leader's program)
    $stmt_schedule = $pdo->prepare("
        SELECT ds.schedule_date, ds.start_time, ds.end_time, ds.room, ds.team_id, 
               t.name as team_name, t.program as team_program, ds.defense_type,
               p.college as team_college
        FROM defense_schedules ds
        JOIN teams t ON ds.team_id = t.id
        LEFT JOIN team_members tm ON tm.team_id = t.id AND LOWER(tm.role) = 'leader'
        LEFT JOIN users u ON u.id = tm.user_id
        LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization IS NOT NULL AND p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
        WHERE ds.id = ?
    ");
    $stmt_schedule->execute([$schedule_id]);
    $schedule_info = $stmt_schedule->fetch(PDO::FETCH_ASSOC);

    if (!$schedule_info) {
        throw new Exception("Defense schedule not found for ID: {$schedule_id}. Verify the schedule ID is correct.");
    }
    $team_id = $schedule_info['team_id'];
    $defense_type = $schedule_info['defense_type'] ?? 'general'; // <-- NEW: Get defense type from schedule
    if (!$team_id) {
        throw new Exception("Team ID missing for defense schedule ID: {$schedule_id}. Check data integrity.");
    }
    error_log("DS-Index: Fetched schedule info for ID {$schedule_id}, Team ID {$team_id}, Defense Type: {$defense_type}, Team Program: " . ($schedule_info['team_program'] ?? 'NULL') . ", Team College: " . ($schedule_info['team_college'] ?? 'NULL'));

    // Fallback: Get college from programs table if not already set
    if (empty($schedule_info['team_college']) && !empty($schedule_info['team_program'])) {
        $collegeStmt = $pdo->prepare("
            SELECT college 
            FROM programs 
            WHERE CONCAT(name, CASE WHEN specialization IS NOT NULL AND specialization != '' THEN CONCAT(' - ', specialization) ELSE '' END) = ?
            LIMIT 1
        ");
        $collegeStmt->execute([$schedule_info['team_program']]);
        $college = $collegeStmt->fetchColumn();
        if ($college) {
            $schedule_info['team_college'] = $college;
            error_log("DS-Index: College resolved via fallback query: {$college}");
        }
    }

    // <-- NEW: Check for admin override in defense_type_overrides table ---
    $overrideStmt = $pdo->prepare("SELECT override_type FROM defense_type_overrides WHERE team_id = ? AND active = 1 ORDER BY created_at DESC LIMIT 1");
    $overrideStmt->execute([$team_id]);
    $override = $overrideStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($override && !empty($override['override_type'])) {
        $original_defense_type = $defense_type;
        $defense_type = $override['override_type'];
        error_log("DS-Index: OVERRIDE APPLIED - Changed defense_type from '{$original_defense_type}' to '{$defense_type}' for team {$team_id} (admin override)");
    }

    // <-- NEW: If no defense_type in schedule, try to get from function ---
    if (!$defense_type || $defense_type === 'general') {
        require_once '../dashboard/includes/defense_type_functions.php';
        $defense_type = getTeamDefenseType($pdo, $team_id);
        error_log("DS-Index: Determined defense type from function: {$defense_type}");
    }
    
    // *** AUTO-DETERMINE GROUP_ID if not provided ***
    if (!$group_id) {
        // Get team's program to help determine the correct rubric group
        $teamProgramStmt = $pdo->prepare("SELECT program FROM teams WHERE id = ?");
        $teamProgramStmt->execute([$team_id]);
        $teamProgram = $teamProgramStmt->fetchColumn();
        
        // Try to resolve program string to program_id
        $resolvedProgramId = null;
        if (is_numeric($teamProgram)) {
            $resolvedProgramId = (int)$teamProgram;
        } else {
            $programLookup = $pdo->prepare("SELECT id FROM programs WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1");
            $programLookup->execute([$teamProgram]);
            $resolvedProgramId = $programLookup->fetchColumn();
        }
        
        // Find appropriate rubric group based on defense_type and program_id
        $groupStmt = $pdo->prepare("
            SELECT id FROM rubric_groups 
            WHERE defense_type = ? 
              AND (program_id = ? OR program_id IS NULL)
            ORDER BY program_id DESC
            LIMIT 1
        ");
        $groupStmt->execute([$defense_type, $resolvedProgramId]);
        $group_id = $groupStmt->fetchColumn();
        
        // Fallback: try with general defense type
        if (!$group_id) {
            $groupStmt = $pdo->prepare("SELECT id FROM rubric_groups WHERE defense_type = 'general' LIMIT 1");
            $groupStmt->execute();
            $group_id = $groupStmt->fetchColumn();
        }
        
        if (!$group_id) {
            throw new Exception("No rubric group found for defense_type='{$defense_type}' and program_id=" . ($resolvedProgramId ?? 'NULL') . ". Please configure rubric groups in the admin dashboard.");
        }
        
        error_log("DS-Index: Auto-determined group_id={$group_id} for defense_type='{$defense_type}', program_id=" . ($resolvedProgramId ?? 'NULL'));
    } else {
        error_log("DS-Index: Using manually provided group_id={$group_id}");
    }
    // *** END AUTO-DETERMINE GROUP_ID ***

    // Fetch Research Title (From Old Logic, using team_id)
    $researchTitleStmt = $pdo->prepare("SELECT title FROM research_titles WHERE team_id = ?");
    $researchTitleStmt->execute([$team_id]);
    $researchTitle = $researchTitleStmt->fetchColumn();
    if (!$researchTitle) {
        error_log("DS-Index: Warning - No research title found for team ID: {$team_id}.");
        $researchTitle = "Research Title Not Found"; // Default value
    }

    // Fetch PDF Filename - Get the correct requirement ID based on defense type
    // First get the team's program name
    $teamProgramStmt = $pdo->prepare("SELECT program FROM teams WHERE id = ?");
    $teamProgramStmt->execute([$team_id]);
    $teamProgram = $teamProgramStmt->fetchColumn();
    
    error_log("DS-Index DEBUG: Team ID={$team_id}, Team Program='{$teamProgram}', Defense Type='{$defense_type}'");
    
    // Find the requirement ID that's configured for this program + defense type
    // Try multiple matching strategies
    $reqIdRow = null;
    $resolvedProgramId = null;
    
    // First, try to resolve the team's program string to a program_id
    // Strategy 0: Check if team.program is actually a program_id (numeric)
    if (is_numeric($teamProgram)) {
        $resolvedProgramId = (int)$teamProgram;
        error_log("DS-Index DEBUG: Team program is numeric, using as program_id: {$resolvedProgramId}");
    } else {
        // Strategy 0a: Try exact match on programs.name
        $programLookup = $pdo->prepare("SELECT id, name FROM programs WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1");
        $programLookup->execute([$teamProgram]);
        $programMatch = $programLookup->fetch(PDO::FETCH_ASSOC);
        
        if ($programMatch) {
            $resolvedProgramId = $programMatch['id'];
            error_log("DS-Index DEBUG: Resolved team program '{$teamProgram}' to program_id={$resolvedProgramId} ('{$programMatch['name']}')");
        } else {
            // Strategy 0b: Try partial match (team program might contain the program name)
            $programLookup2 = $pdo->prepare("SELECT id, name FROM programs WHERE LOWER(TRIM(?)) LIKE CONCAT('%', LOWER(TRIM(name)), '%') ORDER BY LENGTH(name) DESC LIMIT 1");
            $programLookup2->execute([$teamProgram]);
            $programMatch = $programLookup2->fetch(PDO::FETCH_ASSOC);
            
            if ($programMatch) {
                $resolvedProgramId = $programMatch['id'];
                error_log("DS-Index DEBUG: Resolved team program '{$teamProgram}' via partial match to program_id={$resolvedProgramId} ('{$programMatch['name']}')");
            } else {
                error_log("DS-Index WARNING: Could not resolve team program '{$teamProgram}' to any program_id in programs table!");
            }
        }
    }
    
    // Now use the resolved program_id to find the manuscript requirement
    if ($resolvedProgramId) {
        $reqIdStmt = $pdo->prepare("
            SELECT pmr.requirement_id, pmr.program_id, p.name as program_name, r.name as requirement_name
            FROM program_manuscript_requirements pmr
            JOIN programs p ON pmr.program_id = p.id
            JOIN requirements r ON pmr.requirement_id = r.id
            WHERE pmr.program_id = ?
              AND pmr.defense_type = ?
              AND pmr.is_required = 1
              AND pmr.visibility_to_panelist = 1
            LIMIT 1
        ");
        $reqIdStmt->execute([$resolvedProgramId, $defense_type]);
        $reqIdRow = $reqIdStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reqIdRow) {
            error_log("DS-Index DEBUG: Found requirement via program_id lookup: requirement_id={$reqIdRow['requirement_id']} ('{$reqIdRow['requirement_name']}'), program_id={$reqIdRow['program_id']} ('{$reqIdRow['program_name']}')");
        } else {
            error_log("DS-Index WARNING: No manuscript requirement found for program_id={$resolvedProgramId}, defense_type='{$defense_type}'");
        }
    }
    
    $requirement_id = $reqIdRow['requirement_id'] ?? 5; // Fallback to 5 if not found
    
    if (!$reqIdRow) {
        error_log("DS-Index WARNING: No manuscript requirement found for program='{$teamProgram}' (resolved_program_id=" . ($resolvedProgramId ?? 'NULL') . "), defense_type='{$defense_type}'. Falling back to requirement_id=5. Please configure program manuscript requirements in the dashboard.");
    }
    
    error_log("DS-Index: Using requirement_id = {$requirement_id} for defense_type = {$defense_type}");
    
    // First, check if there are files explicitly linked to this defense schedule
    require_once '../dashboard/includes/defense_type_functions.php';
    $linkedFiles = getDefenseScheduleFiles($pdo, $schedule_id);
    
    $pdf_file_name = null;
    $activeSubmissionId = null;
    $submissionFiles = [];
    
    if (!empty($linkedFiles)) {
        // Use explicitly linked files (preferred method)
        error_log("DS-Index: Found " . count($linkedFiles) . " file(s) explicitly linked to defense schedule {$schedule_id}");
        $submissionFiles = $linkedFiles;
        
        // Use the first linked file as the active one
        $firstLinked = $linkedFiles[0];
        $pdf_file_name = $firstLinked['file_name'];
        $activeSubmissionId = $firstLinked['id'];
        error_log("DS-Index: Using explicitly linked file: {$pdf_file_name} (ID: {$activeSubmissionId})");
    } else {
        // Fallback: lookup based on requirement_id from program_manuscript_requirements
        error_log("DS-Index: No explicitly linked files for schedule {$schedule_id}, falling back to requirement_id lookup");
        
        // Now fetch the PDF using the correct requirement ID
        $requirementStmt = $pdo->prepare("SELECT id, file_name FROM team_requirements WHERE team_id = ? AND requirement_id = ?");
        $requirementStmt->execute([$team_id, $requirement_id]);
        $requirement = $requirementStmt->fetch(PDO::FETCH_ASSOC);
        
        // Also fetch ALL submitted files for this requirement (for multi-file selection)
        $allFilesStmt = $pdo->prepare("SELECT id, file_name, original_file_name, submission_number, submitted_at FROM team_requirement_files WHERE team_id = ? AND requirement_id = ? ORDER BY submission_number DESC");
        $allFilesStmt->execute([$team_id, $requirement_id]);
        $submissionFiles = $allFilesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($requirement && !empty($requirement['file_name'])) {
            $pdf_file_name = $requirement['file_name'];
            error_log("DS-Index: Fetched PDF filename: {$pdf_file_name} for team ID {$team_id}, requirement_id {$requirement_id}");
            $activeSubmissionId = $requirement['id'] ?? null;
        } else {
            error_log("DS-Index: Warning - PDF requirement (ID {$requirement_id}) not found or filename empty for team ID: {$team_id}. PDF viewer may not work.");
            // Don't throw an error, but the PDF tab might be non-functional
        }
    }

    // 2. Fetch Team Members (Students) (New Logic)
    $stmt_students = $pdo->prepare("
        SELECT u.id, u.username, u.first_name, u.last_name, CONCAT(u.first_name, ' ', u.last_name) AS fullname
        FROM team_members tm
        JOIN users u ON tm.user_id = u.id
        WHERE tm.team_id = ? AND tm.role != 'Adviser' AND u.usertype = 1
        ORDER BY u.last_name, u.first_name
    ");
    $stmt_students->execute([$team_id]);
    $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);
    if (empty($students)) {
        error_log("DS-Index: Warning - No students found for team ID: {$team_id}.");
    } else {
        error_log("DS-Index: Fetched " . count($students) . " students for team ID {$team_id}.");
    }

    // Fetch Adviser Name (From Old Logic, using team_id)
    $adviserStmt = $pdo->prepare("
        SELECT CONCAT(users.first_name, ' ', users.last_name) AS fullname
        FROM team_members
        JOIN users ON team_members.user_id = users.id
        WHERE team_members.team_id = ? AND team_members.role = 'Adviser'
        LIMIT 1
    ");
    $adviserStmt->execute([$team_id]);
    $adviser = $adviserStmt->fetch(PDO::FETCH_ASSOC);
    $adviser_name = $adviser['fullname'] ?? 'No adviser assigned';
    error_log("DS-Index: Fetched adviser name: {$adviser_name} for team ID {$team_id}.");

    // Fetch Evaluator Name (Currently logged in user)
    $evaluatorStmt = $pdo->prepare("
        SELECT CONCAT(last_name, ', ', first_name) AS evaluator_fullname
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $evaluatorStmt->execute([$evaluator_id]);
    $evaluator = $evaluatorStmt->fetch(PDO::FETCH_ASSOC);
    $evaluator_name = $evaluator['evaluator_fullname'] ?? 'Unknown Evaluator';
    error_log("DS-Index: Fetched evaluator name: {$evaluator_name} for evaluator ID {$evaluator_id}.");

    // *** NEW: Fetch Rubric Group Details ***
    $stmt_group = $pdo->prepare("SELECT name, description FROM rubric_groups WHERE id = ?");
    $stmt_group->execute([$group_id]);
    $rubric_group_details = $stmt_group->fetch(PDO::FETCH_ASSOC);
    if (!$rubric_group_details) {
        // Log warning but don't necessarily stop, maybe group name isn't critical
        error_log("DS-Index: Warning - Rubric group details not found for ID: {$group_id}.");
        $rubric_group_details = ['name' => 'Evaluation Group', 'description' => 'Details not found.']; // Default values
    } else {
        error_log("DS-Index: Fetched rubric group details for ID {$group_id}.");
    }
    // *** END NEW ***

    // 3. Fetch Rubric IDs, Order Index, and Weight associated with the Group ID (Modified)
    $stmt_rubric_ids = $pdo->prepare("SELECT rubric_id, order_index, weight FROM rubric_group_items WHERE group_id = ? ORDER BY order_index ASC"); // Fetch weight as well
    $stmt_rubric_ids->execute([$group_id]);
    // Fetch as an array where each item contains 'rubric_id', 'order_index', 'weight'
    $rubric_group_items = $stmt_rubric_ids->fetchAll(PDO::FETCH_ASSOC);

    // Create maps for easier lookup
    $rubric_order_map = [];
    $rubric_weight_map = [];
    $rubric_ids = [];
    foreach ($rubric_group_items as $item) {
        $r_id = $item['rubric_id'];
        $rubric_ids[] = $r_id;
        $rubric_order_map[$r_id] = $item['order_index'];
        $rubric_weight_map[$r_id] = $item['weight']; // Store weight
    }
    error_log("DS-Index: Fetched Rubric IDs, Order, and Weight for Group {$group_id}: " . print_r($rubric_group_items, true));


    // 4. Fetch Details for each Rubric in the Group (New Logic - Modified)
    if (!empty($rubric_ids)) {
        $placeholders = implode(',', array_fill(0, count($rubric_ids), '?'));
        // Removed ORDER BY name, will sort later using order_index
        $stmt_rubrics = $pdo->prepare("
            SELECT * FROM rubrics WHERE id IN ($placeholders) AND is_active = 1
        ");
        $stmt_rubrics->execute($rubric_ids);
        $rubrics_main = $stmt_rubrics->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log which rubrics were fetched vs requested
        $fetched_ids = array_column($rubrics_main, 'id');
        $missing_ids = array_diff($rubric_ids, $fetched_ids);
        if (!empty($missing_ids)) {
            error_log("DS-Index: WARNING - Some rubrics not fetched (possibly inactive): " . implode(', ', $missing_ids));
        }
        error_log("DS-Index: Fetched " . count($rubrics_main) . " active rubrics: " . implode(', ', array_map(function($r) { return "ID {$r['id']} ({$r['name']})"; }, $rubrics_main)));

        $stmt_levels = $pdo->prepare("
        SELECT rubric_id, id, level_index, name, description, points_min, points_max, is_range
            FROM rubric_levels
        WHERE rubric_id IN ($placeholders)
        ORDER BY rubric_id, level_index
        ");
        $stmt_levels->execute($rubric_ids);
        $levels_all = $stmt_levels->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

        $stmt_criteria = $pdo->prepare("
        SELECT rubric_id, id, criterion_text, criterion_detail, order_index, is_individual, min_score, max_score, is_blank
            FROM rubric_criteria
        WHERE rubric_id IN ($placeholders)
        ORDER BY rubric_id, order_index
        ");
        $stmt_criteria->execute($rubric_ids);
        $criteria_all = $stmt_criteria->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

        foreach ($rubrics_main as $rubric) {
            $r_id = $rubric['id'];
            $rubric_type = $rubric['rubric_type'];
            error_log("DS-Index: Combining data for Rubric ID {$r_id} ('{$rubric['name']}'). Type: {$rubric_type}");

            // Store the order_index and weight fetched earlier
            $rubric['order_index'] = $rubric_order_map[$r_id] ?? 999; // Default high index if missing
            $rubric['weight'] = $rubric_weight_map[$r_id] ?? null; // Store weight, default null

            if (isset($levels_all[$r_id]) && !empty($levels_all[$r_id])) {
                $rubric['levels'] = $levels_all[$r_id];
            } else {
                $rubric['levels'] = [];
                if ($rubric_type !== 'yesno') {
                    error_log("DS-Index: COMBINE WARNING - No levels found in fetched data for active rubric ID {$r_id} ('{$rubric['name']}').");
                }
            }

            if (isset($criteria_all[$r_id]) && !empty($criteria_all[$r_id])) {
                $rubric['criteria'] = $criteria_all[$r_id];
            } else {
                $rubric['criteria'] = [];
                if ($rubric_type === 'numerical' || $rubric_type === 'yesno') {
                    error_log("DS-Index: COMBINE WARNING - No criteria found in fetched data for active rubric ID {$r_id} ('{$rubric['name']}').");
                }
            }

            $rubrics_in_group[$r_id] = $rubric;
            error_log("DS-Index: Successfully added Rubric ID {$r_id} with order_index {$rubric['order_index']} and weight {$rubric['weight']} to \$rubrics_in_group array.");
        }

        // Sort the $rubrics_in_group array by the stored order_index
        uasort($rubrics_in_group, function($a, $b) {
            return ($a['order_index'] ?? 999) <=> ($b['order_index'] ?? 999);
        });

        error_log("DS-Index: Finished combining and sorting rubric details into \$rubrics_in_group by order_index.");
        error_log("DS-Index: Final rubrics_in_group contains " . count($rubrics_in_group) . " rubrics: " . implode(', ', array_map(function($r) { return "ID {$r['id']} ({$r['name']})"; }, $rubrics_in_group)));
    } else {
        error_log("DS-Index: No rubric IDs were found associated with Group ID {$group_id} in 'rubric_group_items'.");
    }

    // 5. Fetch Existing Evaluation Data (New Logic)
    $stmt_existing_eval = $pdo->prepare("
        SELECT epp.id as evaluation_id, epp.comments, ed.rubric_id, ed.criterion_id, ed.student_id, ed.score, ed.selected_option
        FROM evaluation_per_panel epp
        LEFT JOIN evaluation_details ed ON epp.id = ed.evaluation_id
        WHERE epp.defense_schedule_id = :schedule_id AND epp.evaluator_id = :evaluator_id
    ");
     $stmt_existing_eval->execute([':schedule_id' => $schedule_id, ':evaluator_id' => $evaluator_id]);
     $existing_raw = $stmt_existing_eval->fetchAll(PDO::FETCH_ASSOC);
     error_log("DS-Index: Fetched " . count($existing_raw) . " rows for existing evaluation data.");

     if (!empty($existing_raw)) {
         $existing_evaluation = [
             'evaluation_id' => $existing_raw[0]['evaluation_id'],
             'comments' => $existing_raw[0]['comments'],
             'details' => []
         ];
         foreach ($existing_raw as $detail) {
             if ($detail['rubric_id'] === null) {
                 error_log("DS-Index: Warning - Skipping existing evaluation detail with null rubric_id.");
                 continue;
             }
             $r_id = $detail['rubric_id'];
             $c_id = $detail['criterion_id'];
             $s_id = $detail['student_id'];
             $score = $detail['score'];
             $option = $detail['selected_option'];

             if (!isset($existing_evaluation['details'][$r_id])) {
                 $existing_evaluation['details'][$r_id] = [];
             }
             $current_rubric = $rubrics_in_group[$r_id] ?? null;
             if (!$current_rubric) {
                 error_log("DS-Index: Warning - Found existing evaluation detail for rubric ID {$r_id}, but rubric info not loaded. Skipping detail processing.");
                 continue;
             }
             if ($c_id !== null) {
                 if ($s_id !== null) {
                     if (!isset($existing_evaluation['details'][$r_id][$c_id])) $existing_evaluation['details'][$r_id][$c_id] = [];
                     $existing_evaluation['details'][$r_id][$c_id][$s_id] = $score ?? $option;
                 } else {
                     $existing_evaluation['details'][$r_id][$c_id]['group'] = $score ?? $option;
                 }
             } else {
                 $existing_evaluation['details'][$r_id]['overall'] = $option;
             }
         }
         error_log("DS-Index: Processed existing evaluation data.");
     } else {
         error_log("DS-Index: No existing evaluation found for this schedule/evaluator.");
     }

} catch (PDOException $e) {
    $error_ref = $error_ref_prefix . time();
    error_log($error_ref . " - PDO Database Error: " . $e->getMessage() . " | SQLSTATE: " . $e->getCode() . " | Trace: " . $e->getTraceAsString());

    $errorMessage = "Database Error: Could not retrieve evaluation details. Please contact support. Error Ref: " . $error_ref;
    if ($e->getCode() == '42S22' || str_contains($e->getMessage(), '1054')) {
         if (str_contains($e->getMessage(), 'ed.rubric_id') || str_contains($e->getMessage(), 'evaluation_details.rubric_id')) {
              $errorMessage = "Database Schema Error: The 'evaluation_details' table is missing the required 'rubric_id' column. Please update the database schema.";
         } elseif (str_contains($e->getMessage(), 'ed.criterion_id')) {
              $errorMessage = "Database Schema Error: The 'evaluation_details' table is missing the required 'criterion_id' column. Please update the database schema.";
         } elseif (str_contains($e->getMessage(), 'ed.student_id')) {
              $errorMessage = "Database Schema Error: The 'evaluation_details' table is missing the required 'student_id' column. Please update the database schema.";
         } elseif (str_contains($e->getMessage(), 'ed.score')) {
              $errorMessage = "Database Schema Error: The 'evaluation_details' table is missing the required 'score' column. Please update the database schema.";
         } elseif (str_contains($e->getMessage(), 'ed.selected_option')) {
              $errorMessage = "Database Schema Error: The 'evaluation_details' table is missing the required 'selected_option' column. Please update the database schema.";
         } else {
             $errorMessage = "Database Schema Error: A required column is missing in the database. Details: " . htmlspecialchars($e->getMessage()) . " Please contact support. Error Ref: " . $error_ref;
         }
    } else {
        $errorMessage = "Database Error: Could not retrieve evaluation details. Details: " . htmlspecialchars($e->getMessage()) . " (Code: " . htmlspecialchars($e->getCode()) . "). Please contact support if the issue persists. Error Ref: " . $error_ref;
    }
    include '../assets/layouts/header.php';
    echo "<div class='container mt-5'><div class='alert alert-danger'>{$errorMessage}</div></div>";
    include '../assets/layouts/footer.php';
    exit;

} catch (Exception $e) {
    $error_ref = $error_ref_prefix . time();
    error_log($error_ref . " - Application Error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    include '../assets/layouts/header.php';
    echo "<div class='container mt-5'><div class='alert alert-danger'>Application Error: " . htmlspecialchars($e->getMessage()) . " Please contact support. Error Ref: " . $error_ref . "</div></div>";
    include '../assets/layouts/footer.php';
    exit;
}

// --- Helper Function to Render Numerical Rubric ---
function render_numerical_rubric($rubric, $students, $existing_details) {
    $rubric_id = $rubric['id'];
    $levels = $rubric['levels'] ?? [];
    $criteria = $rubric['criteria'] ?? [];
    $is_individual_rubric = !empty($rubric['is_individual_enabled']); // Check if the rubric supports individual scoring at all
    $flag = isset($rubric['is_individual_enabled'])
                ? (int)$rubric['is_individual_enabled']
                : 0;

    echo '<script>';
    echo '  console.log("is_individual_enabled:", ' . $flag . ');';
    echo '</script>';
    $max_members = $rubric['max_members'] ?? count($students); // Use max_members if set, otherwise use actual student count

    if (empty($criteria) || empty($levels)) {
        return "<p class='text-danger'>Cannot render rubric: Missing criteria or quality levels.</p>";
    }

    // --- MODIFICATION START: Add original index before sorting levels ---
    $levels_with_original_index = [];
    foreach ($levels as $original_index => $level_data) {
        // Ensure level_index exists, otherwise use original_index as fallback
        if (!isset($level_data['level_index'])) {
             $level_data['level_index'] = $original_index + 1; // Assuming 1-based index if missing
             error_log("DS-Index: RENDER WARNING - Missing 'level_index' for level in rubric ID {$rubric_id}. Using array index + 1 as fallback.");
        }
        $level_data['original_index'] = $level_data['level_index'] - 1; // Store 0-based index matching saved JSON array
        $levels_with_original_index[] = $level_data;
    }

    // Sort levels by min points (descending)
    usort($levels_with_original_index, function($a, $b) {
        return ($b['points_min'] ?? 0) <=> ($a['points_min'] ?? 0);
    });
    // --- MODIFICATION END ---


    $html = '<div class="table-responsive"><table class="table table-bordered table-hover rubric-table numerical-rubric">';
    $html .= '<thead><tr>';

    $html .= '<th style="min-width: 200px;">Criteria</th>';

    if ($is_individual_rubric) {
        $student_count = 0;
        foreach ($students as $student) {
            if ($student_count >= $max_members) break;
            $html .= '<th class="text-center student-col">' . htmlspecialchars($student['first_name']) . '<br>' . htmlspecialchars($student['last_name']) . '</th>';
            $student_count++;
        }
    } else {
        // --- MODIFICATION: Use sorted levels array for headers ---
        foreach ($levels_with_original_index as $level) {
        // --- END MODIFICATION ---
            $level_name = htmlspecialchars($level['name']);
            $points_min = $level['points_min'] ?? 0;
            $points_max = $level['points_max'] ?? $points_min;
            $is_range = !empty($level['is_range']) && ($points_min != $points_max);
            $points_text = $is_range ? "({$points_max}-{$points_min})" : "({$points_min})";
            $html .= '<th class="text-center level-col">' . $level_name . '<br><small>' . $points_text . ' pts</small></th>';
        }
        $html .= '<th class="text-center score-col">Score</th>';
    }

    $html .= '</tr></thead>';
    $html .= '<tbody>';

    foreach ($criteria as $index => $criterion) {
        $criterion_id = $criterion['id'];
        $criterion_text = htmlspecialchars($criterion['criterion_text']);
        
        // Check if this is a blank/section header criterion
        $is_blank = !empty($criterion['is_blank']);
        
        // --- MODIFICATION: Decode criterion_detail JSON ---
        $criterion_detail_json = $criterion['criterion_detail'];
        $criterion_details_array = json_decode($criterion_detail_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $criterion_details_array = []; // Fallback to empty array on error
            error_log("DS-Index: RENDER WARNING - Failed to decode criterion_detail JSON for criterion ID {$criterion_id}: " . json_last_error_msg() . " | JSON: " . $criterion_detail_json);
        }
        // --- END MODIFICATION ---
        $is_individual_criterion = $is_individual_rubric && !empty($criterion['is_individual']);

        // Render blank/section header row if is_blank is set
        if ($is_blank) {
            // Calculate colspan based on rubric type
            if ($is_individual_rubric) {
                // For individual rubrics: 1 (criteria) + number of students
                $student_count_for_colspan = min(count($students), $max_members);
                $colspan = 1 + $student_count_for_colspan;
            } else {
                // For group rubrics: 1 (criteria) + quality levels + 1 (score)
                $colspan = 1 + count($levels_with_original_index) + 1;
            }
            
            $html .= '<tr class="table-active blank-criterion-row" data-criterion-id="' . $criterion_id . '">';
            $html .= '<td colspan="' . $colspan . '" style="font-weight: bold; background-color: #e9ecef; padding: 12px;">' . $criterion_text . '</td>';
            $html .= '</tr>';
            continue; // Skip normal rendering for blank criteria
        }

        $html .= '<tr data-criterion-id="' . $criterion_id . '" data-is-individual="' . ($is_individual_criterion ? '1' : '0') . '">';
        $html .= '<td>' . $criterion_text . '</td>';

        if ($is_individual_criterion) {
            $rubric['levels'] = $levels_with_original_index; // Use the sorted levels with original index for individual rubrics
            
            // Use criterion's own min_score and max_score if available
            $criterion_min = $criterion['min_score'] ?? 0;
            $criterion_max = $criterion['max_score'] ?? 100;
            
            // Fallback: If min/max not set in criterion, compute from levels
            if ($criterion_min === null && $criterion_max === null) {
                $level_points_min = PHP_INT_MAX;
                $level_points_max = PHP_INT_MIN;
                foreach ($levels_with_original_index as $lvl) {
                    $min = $lvl['points_min'] ?? 0;
                    $max = $lvl['points_max'] ?? $min;
                    $level_points_min = min($level_points_min, $min);
                    $level_points_max = max($level_points_max, $max);
                }
                // Handle case where no levels defined min/max properly
                if ($level_points_min === PHP_INT_MAX) $level_points_min = 0;
                if ($level_points_max === PHP_INT_MIN) $level_points_max = 0;
                $criterion_min = $level_points_min;
                $criterion_max = $level_points_max;
            }

            $student_count = 0;
            foreach ($students as $student) {
                if ($student_count++ >= $max_members) break;

                $student_id     = $student['id'];
                $input_name     = "score[{$rubric_id}][{$criterion_id}][{$student_id}]";
                $existing_score = $existing_details[$criterion_id][$student_id] ?? '';

                $html .= '<td class="text-center student-score-cell">';
                $html .=    '<input
                                    type="number"
                                    class="entered-score criterion-score-input form-control form-control-sm"
                                    name="' . $input_name . '"
                                    id="r' . $rubric_id . 'c' . $criterion_id . 's' . $student_id . '"
                                    value="' . htmlspecialchars($existing_score, ENT_QUOTES) . '"
                                    min="' . $criterion_min . '"
                                    max="' . $criterion_max . '"
                                    step="1"
                                    data-rubric-id="' . $rubric_id . '"
                                    data-criterion-id="' . $criterion_id . '"
                                    data-student-id="' . $student_id . '"
                                    onchange="updateIndividualTotalScore(' . $rubric_id . ', ' . $student_id . ')"
                                    title="Score range: ' . $criterion_min . ' - ' . $criterion_max . ' points"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                >'; // Close input tag
                $html .= '<small class="text-muted d-block mt-1">(' . $criterion_min . '-' . $criterion_max . ')</small>';
                $html .= '</td>'; // Close td tag
            } // Close foreach ($students as $student)
        } else {
            $input_name = "score[{$rubric_id}][{$criterion_id}][group]";
            $existing_score = $existing_details[$criterion_id]['group'] ?? null;

            // Check if criterion has its own min/max limits
            $criterion_min = $criterion['min_score'] ?? null;
            $criterion_max = $criterion['max_score'] ?? null;
            
            // If criterion has custom limits, use them; otherwise calculate from quality levels
            if ($criterion_min !== null && $criterion_max !== null) {
                $group_min_score = $criterion_min;
                $group_max_score = $criterion_max;
            } else {
                // Determine overall min/max from quality levels
                $group_min_score = PHP_INT_MAX;
                $group_max_score = PHP_INT_MIN;
                // --- MODIFICATION: Use sorted levels array for min/max calculation ---
                foreach ($levels_with_original_index as $level) {
                // --- END MODIFICATION ---
                    $min = $level['points_min'] ?? 0;
                    $max = $level['points_max'] ?? $min;
                    $group_min_score = min($group_min_score, $min);
                    $group_max_score = max($group_max_score, $max);
                }
                // Handle case where no levels defined min/max properly
                if ($group_min_score === PHP_INT_MAX) $group_min_score = 0;
                if ($group_max_score === PHP_INT_MIN) $group_max_score = 0;
            }

            // --- MODIFICATION: Use sorted levels array and display specific detail ---
            foreach ($levels_with_original_index as $level) { // Use the sorted array with original index
                $level_id = $level['id'];
                $original_level_index = $level['original_index']; // Get the original 0-based index

                $html .= '<td class="text-left level-cell">';
                // Fetch the description from the decoded array using the original index
                $detail_text = isset($criterion_details_array[$original_level_index])
                               ? htmlspecialchars($criterion_details_array[$original_level_index])
                               : ''; // Fallback if index doesn't exist or detail wasn't an array
                $html .= $detail_text;
                $html .= '</td>';
            }
            // --- END MODIFICATION ---

            $html .= '<td class="text-center score-cell group-score-display" style="font-weight: bold;">';
            $html .= '<input type="number" class="entered-score form-control form-control-sm"
                             name="' . $input_name . '"
                             id="r'.$rubric_id.'c'.$criterion_id.'g_score"
                             title="Score range: ' . $group_min_score . ' - ' . $group_max_score . ' points"
                             data-bs-toggle="tooltip"
                             data-bs-placement="top"
                             value="' . htmlspecialchars($existing_score ?? '', ENT_QUOTES) . '"
                             min="' . $group_min_score . '"
                             max="' . $group_max_score . '"
                             onchange="updateGroupTotalScore('.$rubric_id.')"
                             data-rubric-id="'.$rubric_id.'"
                             data-criterion-id="'.$criterion_id.'"
                             required
                             >'; // Added required and form-control classes
            $html .= '</td>';
        }

        $html .= '</tr>';
    }

    $html .= '</tbody>';
    
    // Add subtotal row at the bottom of the table
    $html .= '<tfoot>';
    $html .= '<tr class="table-secondary" style="font-weight: bold;">';
    if ($is_individual_rubric) {
        $html .= '<td>Subtotal</td>';
        $student_count = 0;
        foreach ($students as $student) {
            if ($student_count >= $max_members) break;
            $html .= '<td class="text-center" id="r' . $rubric_id . '-student-' . $student['id'] . '-subtotal">0</td>';
            $student_count++;
        }
    } else {
        $colspan = count($levels_with_original_index) + 1;
        $html .= '<td colspan="' . $colspan . '" class="text-right">Subtotal Score:</td>';
        $html .= '<td class="text-center" id="r' . $rubric_id . '-subtotal">0</td>';
    }
    $html .= '</tr>';
    $html .= '</tfoot>';
    
    $html .= '</table></div>';

    return $html;
}

function render_yes_no_rubric($rubric, $students, $existing_details) {
    $rubric_id = $rubric['id'];
    $is_individual_rubric = !empty($rubric['is_individual_enabled']);
    $max_members = $rubric['max_members'] ?? count($students);

    // Our only two options
    $options = [
        ['key' => 'yes', 'label' => 'Yes', 'value' => 1],
        ['key' => 'no',  'label' => 'No',  'value' => 0],
    ];

    // Basic check
    if (empty($rubric['criteria'])) {
        return "<p class='text-danger'>Cannot render rubric: Missing criteria.</p>";
    }

    // Start table
    $html  = '<div class="table-responsive">';
    $html .= '<table class="table table-bordered rubric-table yes-no-rubric">';
    $html .= '<thead><tr>';
    $html .= '<th style="min-width:200px;">Criteria</th>';

    // Header columns
    if ($is_individual_rubric) {
        $count = 0;
        foreach ($students as $stu) {
            if ($count++ >= $max_members) break;
            $html .= '<th class="text-center">'
                  . htmlspecialchars($stu['first_name']) . '<br>'
                  . htmlspecialchars($stu['last_name'])
                  . '</th>';
        }
    } else {
        foreach ($options as $opt) {
            $html .= '<th class="text-center">'
                  . $opt['label']
                  . '</th>';
        }
        $html .= '<th class="text-center">Selection</th>';
    }

    $html .= '</tr></thead><tbody>';

    // Rows per criterion
    foreach ($rubric['criteria'] as $criterion) {
        $crit_id   = $criterion['id'];
        $crit_text = htmlspecialchars($criterion['criterion_text']);
        $is_indiv  = $is_individual_rubric && !empty($criterion['is_individual']);

        $html .= '<tr data-criterion-id="'.$crit_id.'"'
               . ' data-is-individual="'.($is_indiv?1:0).'">';
        $html .= '<td>'.$crit_text.'</td>';

        if ($is_indiv) {
            // Individual cells: one Yes/No per student
            $count = 0;
            foreach ($students as $stu) {
                if ($count++ >= $max_members) break;
                $stu_id = $stu['id'];
                $field  = "score[{$rubric_id}][{$crit_id}][{$stu_id}]";
                $existing = $existing_details[$crit_id][$stu_id] ?? null;

                $html .= '<td class="text-center">';
                $html .= '<div class="btn-group btn-group-sm" role="group">';
                foreach ($options as $opt) {
                    $cid = "r{$rubric_id}c{$crit_id}s{$stu_id}{$opt['key']}";
                    $checked = ($existing !== null && (int)$existing === $opt['value'])
                             ? 'checked' : '';
                    $html .= '<input type="radio" class="btn-check yesno-option" required' // Added required
                          .  ' name="'.$field.'" id="'.$cid.'"'
                          .  ' value="'.$opt['value'].'" autocomplete="off" '
                          .  $checked . '>';
                    $html .= '<label class="btn btn-outline-primary" for="'.$cid.'">'
                          .  $opt['label']
                          .  '</label>';
                }
                $html .= '</div>';
                // Removed the display div as the button group shows the selection
                // $html .= '<div class="mt-1" style="font-weight:bold;">'
                //       . 'Selected: '
                //       . (($existing === null) ? '-'
                //          : (($existing==1)?'Yes':'No'))
                //       . '</div>';
                $html .= '</td>';
            }
        } else {
            // Group cells: one Yes/No for the whole group
            $field    = "score[{$rubric_id}][{$crit_id}][group]";
            $existing = $existing_details[$crit_id]['group'] ?? null;

            foreach ($options as $opt) {
                $cid     = "r{$rubric_id}c{$crit_id}g{$opt['key']}";
                $checked = ($existing !== null && (int)$existing === $opt['value'])
                         ? 'checked' : '';
                $html .= '<td class="text-center">';
                $html .= '<input type="radio" class="form-check-input yesno-option" required' // Added required
                      .  ' name="'.$field.'" id="'.$cid.'"'
                      .  ' value="'.$opt['value'].'" '
                      .  $checked
                      .  ' onchange="updateGroupSelection('.$rubric_id.');">'; // Keep JS hook if needed
                $html .= '<label class="form-check-label ms-1" for="'.$cid.'">'.$opt['label'].'</label>'; // Added ms-1 for spacing
                $html .= '</td>';
            }

            // Display the group’s current choice (optional, can be removed if redundant)
            $html .= '<td class="text-center group-selection-display" style="font-weight:bold;">'
                  . (($existing === null) ? '-'
                     : (($existing==1)? 'Yes' : 'No'))
                  . '</td>';
        }

        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';

    // (Optional) a place to show overall yes% or whatever logic you like:
    // $html .= '<div class="text-right mt-2">'
    //       .   'Overall Yes: <span id="r'.$rubric_id.'-overall">0%</span>'
    //       . '</div>';

    return $html;
}

// --- NEW Helper Function to Render Pass/Fail Rubric (Automated based on score) ---
function render_passfail_rubric($rubric, $existing_details) {
    $rubric_id = $rubric['id'];
    $levels = $rubric['levels'] ?? []; // These are the pass options
    
    // Get recommendation texts and thresholds from the main rubric data
    $pass_recommendation_text = $rubric['pass_recommendation_text'] ?? 'Pass Recommendation';
    $fail_recommendation_text = $rubric['fail_recommendation_text'] ?? 'Fail Recommendation';
    $fail_option_text = $rubric['fail_option_text'] ?? 'Fail';
    
    // Get thresholds for different pass levels
    $threshold_1 = $rubric['pass_threshold_1'] ?? 81; // Total Pass
    $threshold_2 = $rubric['pass_threshold_2'] ?? 80;  // Minor Revision Pass
    $threshold_3 = $rubric['pass_threshold_3'] ?? 75;  // Major Revision Pass

    // Basic validation
    if (empty($levels)) {
        return "<p class='text-danger'>Cannot render Pass/Fail rubric '{$rubric['name']}': Missing pass option definitions (levels).</p>";
    }

    // Sort levels (pass options) by level_index in descending order (highest threshold first)
    usort($levels, function($a, $b) {
        return ($b['level_index'] ?? 0) <=> ($a['level_index'] ?? 0);
    });

    $html = '<div class="alert alert-info">';
    $html .= '<h6 class="alert-heading"><i class="bi bi-info-circle"></i> Automated Pass/Fail Determination</h6>';
    $html .= '<p class="mb-2">This rubric automatically determines pass/fail status based on your total numerical score:</p>';
    $html .= '<ul class="mb-0">';
    
    // Display threshold information
    foreach ($levels as $index => $level) {
        $level_index = $level['level_index'];
        $level_description = htmlspecialchars($level['description'] ?? "Pass Option {$level_index}");
        
        // Map level_index to threshold
        $threshold = 0;
        if ($level_index == 1) $threshold = $threshold_1;
        elseif ($level_index == 2) $threshold = $threshold_2;
        elseif ($level_index == 3) $threshold = $threshold_3;
        
        $html .= '<li><strong>' . $level_description . ':</strong> ' . $threshold . '% or higher</li>';
    }
    
    $html .= '<li><strong>' . htmlspecialchars($fail_option_text) . ':</strong> Below ' . min($threshold_3, $threshold_2, $threshold_1) . '%</li>';
    $html .= '</ul>';
    $html .= '</div>';
    
    // Hidden input that will be auto-populated by JavaScript based on calculated score
    $html .= '<input type="hidden" class="auto-passfail-result" name="selected_option[' . $rubric_id . ']" id="passfail_result_' . $rubric_id . '" value="0" data-threshold-1="' . $threshold_1 . '" data-threshold-2="' . $threshold_2 . '" data-threshold-3="' . $threshold_3 . '" data-level-count="' . count($levels) . '">';
    
    // Display current status (will be updated by JavaScript)
    $html .= '<div class="alert alert-secondary" id="passfail_status_' . $rubric_id . '">';
    $html .= '<strong>Current Status:</strong> <span class="passfail-status-text">Calculating...</span>';
    $html .= '</div>';

    return $html;
}
// --- END NEW Helper Function ---

// --- Start HTML Output ---
include '../assets/layouts/header.php';
?>

<!-- PDF.js Integration (from Old Logic) -->
<script type="module">
  import { getDocument, GlobalWorkerOptions } from 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.7.76/pdf.min.mjs';
  GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.7.76/pdf.worker.min.mjs';

  const predefinedPdfUrl = `../assets/uploads/submission/<?php echo htmlspecialchars($pdf_file_name ?? ''); ?>`;
  const outputPdfTextarea = document.getElementById('output-pdf'); // Hidden textarea for AI

  window.extractText = async function(pdfUrl) {
    if (!pdfUrl || pdfUrl.includes('/submission/') && pdfUrl.endsWith('/submission/')) {
        console.log("PDF URL not set or empty, skipping text extraction.");
        return;
    }
    if (!outputPdfTextarea) {
        console.warn("Output element not available for text extraction.");
        return;
    }
    try {
      const response = await fetch(pdfUrl);
      if (!response.ok) {
        console.warn(`Failed to fetch PDF: HTTP ${response.status}. PDF might not be uploaded yet.`);
        return;
      }
      const arrayBuffer = await response.arrayBuffer();
      const pdfData = new Uint8Array(arrayBuffer);
      const pdf = await getDocument(pdfData).promise;
      let extractedText = '';
      for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page = await pdf.getPage(pageNum);
        const textContent = await page.getTextContent();
        let pageText = `--- Page ${pageNum} ---\n`;
        let lastY = null;
        textContent.items.forEach(item => {
          const currentY = item.transform[5];
          if (lastY !== null && Math.abs(currentY - lastY) > 5) pageText += '\n';
          pageText += item.str;
          lastY = currentY;
        });
        extractedText += pageText + '\n\n';
      }
      outputPdfTextarea.value = extractedText.trim();
      console.log("PDF text extracted for AI analysis.");
      if (typeof window.initiateAiAnalysis === 'function') {
          window.initiateAiAnalysis();
      }
    } catch (error) {
      console.warn('Could not extract text from PDF:', error.message);
    }
  }

  window.addEventListener('DOMContentLoaded', () => {
    if (predefinedPdfUrl && !predefinedPdfUrl.endsWith('/submission/')) {
        extractText(predefinedPdfUrl);
    } else {
        console.log("No PDF file available for this evaluation.");
    }
  });
</script>
<textarea id="output-pdf" style="display:none;"></textarea>
<main role="main" class="decision-support-bg">
  <section class="jumbotron py-5 mb-4 jbtron">
    <div class="container">
        <?php
        if ($evaluator_id && $schedule_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluation_per_panel WHERE defense_schedule_id = ? AND evaluator_id = ?");
    $stmt->execute([$schedule_id, $evaluator_id]);
    $done_evaluating = $stmt->fetchColumn() > 0;
    if ($done_evaluating) {
        // CHANGED: Removed "You can no longer edit" message to allow re-evaluation
        echo "<div class='container mt-5'><div class='alert alert-info'><i class='fas fa-info-circle'></i> You can update your previous evaluation by re-submitting below.</div></div>";
    }
}

        ?>
        <div class="text-center mb-4">
            <h1 class="display-6 fw-bold mb-5" style="color: var(--main-black)"><?php echo htmlspecialchars($researchTitle); ?></h1>
            <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
                <span class="badge px-3 py-2" style="background-color: var(--main-bg-dark)">
                    <i class="fas fa-users me-2" style="color: inherit;"></i><?php echo htmlspecialchars($schedule_info['team_name'] ?? 'N/A'); ?>
                </span>
                 <span class="badge px-3 py-2" style="background-color: var(--main-bg-dark)">
                    <i class="fas fa-calendar-alt me-2" style="color: inherit;"></i><?php echo htmlspecialchars(date('M d, Y', strtotime($schedule_info['schedule_date'] ?? ''))); ?>
                </span>
                 <span class="badge px-3 py-2" style="background-color: var(--main-bg-dark)">
                    <i class="fas fa-clock me-2" style="color: inherit;"></i><?php echo htmlspecialchars(date('g:i A', strtotime($schedule_info['start_time'] ?? ''))) . ' - ' . htmlspecialchars(date('g:i A', strtotime($schedule_info['end_time'] ?? ''))); ?>
                </span>
                <!-- NEW: Defense Type Badge -->
                <?php 
                    $typeColor = [
                        'title_proposal' => '#0dcaf0',
                        'title_defense' => '#0d6efd',
                        'final_defense' => '#198754'
                    ];
                    $typeLabel = [
                        'title_proposal' => 'Title Proposal Defense',
                        'title_defense' => 'Title Defense',
                        'final_defense' => 'Final Defense'
                    ];
                    $bgColor = $typeColor[$defense_type] ?? '#6c757d';
                    $label = $typeLabel[$defense_type] ?? ucfirst(str_replace('_', ' ', $defense_type));
                ?>
                <span class="badge px-3 py-2" style="background-color: <?php echo $bgColor; ?>;">
                    <i class="fas fa-flag me-2" style="color: white;"></i><?php echo htmlspecialchars($label); ?>
                </span>
                <!-- Rubric Group Badge for debugging -->
                <span class="badge px-3 py-2" style="background-color: #6c757d;">
                    <i class="fas fa-clipboard-list me-2" style="color: white;"></i><?php echo htmlspecialchars($rubric_group_details['name'] ?? 'Unknown Group'); ?>
                </span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card team-members-card" id="team-members-card">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center mb-3">
                            <i class="fas fa-users me-2" style="color: var(--main-primary)"></i>
                            <span class="feature-title">Team Members</span>
                        </h5>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student): ?>
                                    <span class="badge px-3 py-2 rounded-pill team-member-item" style="background-color: var(--primary-100); color: var(--main-black)">
                                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($student['fullname']); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0">No members found</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card adviser-card" id="adviser-card">
                            <div class="card-body">
                                <h5 class="card-title d-flex align-items-center mb-3">
                                    <i class="fas fa-chalkboard-teacher me-2" style="color: var(--main-primary)"></i>
                                    <span class="feature-title">Adviser</span>
                                </h5>
                                <p class="card-text mb-0 adviser-name" style="color: var(--main-black)">
                                    <?php echo htmlspecialchars($adviser_name); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card program-card" id="program-card">
                            <div class="card-body">
                                <h5 class="card-title d-flex align-items-center mb-3">
                                    <i class="fas fa-graduation-cap me-2" style="color: var(--main-primary)"></i>
                                    <span class="feature-title">Program</span>
                                </h5>
                                <p class="card-text mb-0 program-name" style="color: var(--main-black)">
                                    <?php echo htmlspecialchars($schedule_info['team_program'] ?? 'N/A'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

  <div class="container mb-4">
    <ul class="nav nav-tabs" id="defenseContentTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="research-paper-tab" data-bs-toggle="tab" data-bs-target="#research-paper" type="button" role="tab" aria-controls="research-paper" aria-selected="true">
          Research Paper
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="score-sheet-tab" data-bs-toggle="tab" data-bs-target="#score-sheet-content" type="button" role="tab" aria-controls="score-sheet-content" aria-selected="false">
          Score Sheet
        </button>
      </li>
    </ul>
  </div>

  <div class="tab-content decision-support-bg" id="defenseContentTabsContent">
    <div class="tab-pane fade show active" id="research-paper" role="tabpanel" aria-labelledby="research-paper-tab">
      <div class="album">
        <div class="container">
          <div class="toggle-container">
            <div class="btn-group w-100" role="group" aria-label="View toggles">
              <button type="button" class="btn toggle-btn active" data-target="pdf-section">
                <i class="fas fa-file-pdf me-2"></i>PDF View
              </button>
              <button type="button" class="btn toggle-btn" data-target="ai-section">
                <i class="fas fa-robot me-2"></i>AI Analysis
              </button>
            </div>
          </div>

          <div class="section-toggle" id="pdf-section">
                         <?php if ($pdf_file_name): ?>
                                <div class="card pdf-view-card" id="pdf-view-card">
                                    <div class="card-body">
                                        <div class="panel-header d-flex justify-content-between align-items-center">
                                            <h4>PDF Document View</h4>
                                            <div>
                                                <button class="fullscreen-btn me-2" onclick="toggleFullScreen()"><i class="fas fa-expand"></i> Full Screen</button>
                                                <?php if (isset($_SESSION['team_role']) && in_array($_SESSION['team_role'], ['leader','adviser']) || (isset($_SESSION['usertype']) && in_array($_SESSION['usertype'], [2,3]))): ?>
                                                    <button id="setSelectedFileBtn" class="btn btn-sm btn-outline-primary">Set selected file for evaluation</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if (!empty($submissionFiles) && count($submissionFiles) > 1): ?>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="list-group" id="submissionFileList">
                                                    <?php foreach ($submissionFiles as $sf): ?>
                                                        <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <input type="radio" name="selected_submission" value="<?php echo (int)$sf['id']; ?>" <?php echo ((int)$sf['id'] === $activeSubmissionId) ? 'checked' : ''; ?>>
                                                                <strong> #<?php echo htmlspecialchars($sf['submission_number'] ?? ''); ?></strong>
                                                                <div class="small"><?php echo htmlspecialchars($sf['original_file_name'] ?? $sf['file_name']); ?></div>
                                                                <div class="small text-muted">Submitted: <?php echo htmlspecialchars($sf['submitted_at'] ?? ''); ?></div>
                                                            </div>
                                                            <a class="btn btn-sm btn-secondary" href="../assets/uploads/submission/<?php echo urlencode($sf['file_name']); ?>" download>Download</a>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="panel-content">
                                                    <iframe id="pdf" src="../assets/uploads/submission/<?php echo urlencode($pdf_file_name); ?>"
                                                        frameborder="0" style="width: 100%; height: 600px;" allowfullscreen>
                                                    </iframe>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="panel-content">
                                            <iframe id="pdf" src="../assets/uploads/submission/<?php echo urlencode($pdf_file_name); ?>"
                                                frameborder="0" style="width: 100%; height: 600px;" allowfullscreen>
                                            </iframe>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                         <?php else: ?>
                                <div class="alert alert-warning">
                                    <h5 class="alert-heading"><i class="bi bi-file-earmark-pdf"></i> PDF File Not Available</h5>
                                    <p><strong>Defense Type:</strong> <?php echo htmlspecialchars($defense_type); ?></p>
                                    <p><strong>Team ID:</strong> <?php echo htmlspecialchars($team_id); ?></p>
                                    <p><strong>Requirement ID Used:</strong> <?php echo htmlspecialchars($requirement_id); ?></p>
                                    
                                    <hr>
                                    
                                    <p class="mb-0"><strong>Possible causes:</strong></p>
                                    <ul class="mb-2">
                                        <li>The team has not submitted the required manuscript for this defense type</li>
                                        <li>The defense schedule does not have files explicitly linked to it</li>
                                        <li>No manuscript requirement is configured for this program and defense type combination</li>
                                        <li>The manuscript requirement configuration is incorrect in <code>program_manuscript_requirements</code> table</li>
                                    </ul>
                                    
                                    <p class="mb-0"><strong>To fix this issue:</strong></p>
                                    <ol class="mb-0">
                                        <li>Ensure the team has submitted their manuscript (check Team Requirements in Dashboard)</li>
                                        <li>Verify the defense type matches the submitted requirement</li>
                                        <li>If multiple files exist, an admin can explicitly link the correct file to this defense schedule</li>
                                        <li>Check that manuscript requirements are properly configured for this program</li>
                                    </ol>
                                </div>
                         <?php endif; ?>
          </div>

          <div class="section-toggle d-none" id="ai-section">
            <div class="card ai-analysis-card mb-4 box-shadow h-100 ai-container" id="ai-analysis-card" style="max-height: 90vh; overflow: hidden;">
              <div class="panel-header">
                <h4>AI Evaluation Results</h4>
              </div>
              <div class="ai-analysis-container" style="height: 100%; overflow-y: auto;">
                <div id="ai-output"><p class="text-center text-muted p-3">Loading AI analysis...</p></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="score-sheet-content" role="tabpanel" aria-labelledby="score-sheet-tab">
        <div class="container mt-4">

            <!-- Display Rubric Group Info -->
            <div class="mb-4 p-3 rounded" style="background-color: var(--neutral-50);">
                <h2 class="display-7 fw-bold mb-2" style="color: var(--main-black)"><?php echo htmlspecialchars($rubric_group_details['name'] ?? 'Evaluation Group'); ?></h2>
                <?php if (!empty($rubric_group_details['description'])): ?>
                    <p class="lead" style="color: var(--neutral-700);"><?php echo htmlspecialchars($rubric_group_details['description']); ?></p>
                <?php endif; ?>
            </div>

            <!-- Evaluation Form -->
            <form id="evaluationForm">
                <input type="hidden" name="defense_schedule_id" value="<?php echo $schedule_id; ?>">
                <input type="hidden" name="rubric_group_id" value="<?php echo $group_id; ?>">

                <?php
                if (empty($rubrics_in_group)) {
                    error_log("DS-Index: Rendering check - \$rubrics_in_group is EMPTY. Displaying 'No active rubrics' message.");
                    error_log("DS-Index: Final state of \$rubrics_in_group before render: " . print_r($rubrics_in_group, true));
                } else {
                    error_log("DS-Index: Rendering check - \$rubrics_in_group is NOT empty. Proceeding to render rubrics.");
                }
                ?>
                <?php if (empty($rubrics_in_group)): ?>
                    <div class="alert alert-warning">
                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> No Active Rubrics Found</h5>
                        <p><strong>Evaluation Group:</strong> <?php echo htmlspecialchars($rubric_group_details['name']); ?> (Group ID: <?php echo $group_id; ?>)</p>
                        <p><strong>Expected Rubric IDs:</strong> <?php echo implode(', ', $rubric_ids ?: ['none']); ?></p>
                        
                        <hr>
                        
                        <p class="mb-0"><strong>Possible causes:</strong></p>
                        <ul class="mb-2">
                            <li>The rubrics with these IDs do not exist in the database</li>
                            <li>The rubrics are marked as inactive (<code>is_active = 0</code>)</li>
                            <li>The rubric group items table has incorrect rubric IDs</li>
                        </ul>
                        
                        <p class="mb-0"><strong>To fix this issue:</strong></p>
                        <ol class="mb-0">
                            <li>Verify rubrics exist: <code>SELECT id, name, is_active FROM rubrics WHERE id IN (<?php echo implode(', ', $rubric_ids ?: [0]); ?>)</code></li>
                            <li>Check if rubrics are active in the database</li>
                            <li>Run the migration script: <code>/assets/setup/fix_missing_rubrics.sql</code></li>
                            <li>Contact your system administrator if the problem persists</li>
                        </ol>
                    </div>
                <?php else: ?>
                    <?php 
                    // Prepare weight summary data
                    $total_configured_weight = 0;
                    $total_actual_weight = 0;
                    $weight_summary = [];
                    $seen_rubric_ids = []; // Track which rubric IDs we've added to prevent duplicates
                    
                    // Only include numerical rubrics in the weight summary
                    foreach ($rubrics_in_group as $rubric_id => $rubric) {
                        // Skip non-numerical rubrics (pass/fail, yes/no)
                        if ($rubric['rubric_type'] !== 'numerical') {
                            error_log("DS-Index: Skipping rubric '{$rubric['name']}' (ID: {$rubric_id}) from weight summary - type is '{$rubric['rubric_type']}'");
                            continue;
                        }
                        
                        // Skip duplicates
                        if (in_array($rubric_id, $seen_rubric_ids)) {
                            error_log("DS-Index: WARNING - Duplicate rubric ID {$rubric_id} ('{$rubric['name']}') detected in rubrics_in_group, skipping duplicate");
                            continue;
                        }
                        $seen_rubric_ids[] = $rubric_id;
                        
                        $configured_weight = isset($rubric['weight']) && $rubric['weight'] !== null ? floatval($rubric['weight']) : 0;
                        $total_configured_weight += $configured_weight;
                        
                        // Check if this is an individual scoring rubric
                        $is_individual_rubric = !empty($rubric['is_individual_enabled']);
                        $max_members = $rubric['max_members'] ?? count($students);
                        
                        // Collect student IDs for individual rubrics
                        $student_ids_for_rubric = [];
                        if ($is_individual_rubric && !empty($students)) {
                            $student_count = 0;
                            foreach ($students as $student) {
                                if ($student_count >= $max_members) break;
                                $student_ids_for_rubric[] = $student['id'];
                                $student_count++;
                            }
                        }
                        
                        error_log("DS-Index: Adding rubric '{$rubric['name']}' (ID: {$rubric_id}) to weight summary - weight: {$configured_weight}%, is_individual: " . ($is_individual_rubric ? 'yes' : 'no'));
                        $weight_summary[] = [
                            'name' => $rubric['name'],
                            'configured' => $configured_weight,
                            'rubric_id' => $rubric_id,
                            'rubric_type' => $rubric['rubric_type'],
                            'is_individual' => $is_individual_rubric,
                            'student_ids' => $student_ids_for_rubric // Array of student IDs for individual rubrics
                        ];
                    }
                    
                    // Calculate actual weights (normalized to 100%)
                    if ($total_configured_weight > 0) {
                        foreach ($weight_summary as $key => $item) {
                            $weight_summary[$key]['actual'] = ($item['configured'] / $total_configured_weight) * 100;
                            $total_actual_weight += $weight_summary[$key]['actual'];
                        }
                    }
                    
                    // Debug: show exactly what's in the array
                    echo '<!-- FINAL weight_summary (' . count($weight_summary) . ' items): ';
                    foreach ($weight_summary as $idx => $ws) {
                        echo "[$idx] ID=" . $ws['rubric_id'] . " " . $ws['name'] . " " . $ws['configured'] . "% | ";
                    }
                    echo '-->';
                    ?>
                    
                    <?php foreach ($rubrics_in_group as $rubric_id => $rubric): // This loop now iterates in the correct order ?>
                        <!-- Rubric Card -->
                        <div class="card mb-4 rubric-card" data-rubric-id="<?php echo $rubric_id; ?>" data-rubric-type="<?php echo $rubric['rubric_type']; ?>" data-weight="<?php echo isset($rubric['weight']) && $rubric['weight'] !== null ? $rubric['weight'] : 0; ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5><?php echo htmlspecialchars($rubric['name']); ?></h5>
                                    <?php if (!empty($rubric['description'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($rubric['description']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php if (isset($rubric['weight']) && $rubric['weight'] !== null): ?>
                                    <span class="badge bg-secondary">Weight: <?php echo htmlspecialchars(number_format($rubric['weight'], 2)); ?>%</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php
                                $existing_details = $existing_evaluation['details'][$rubric_id] ?? [];
                                if ($rubric['rubric_type'] === 'numerical') {
                                    if (empty($rubric['criteria']) || empty($rubric['levels'])) {
                                        $error_message = "DS-Index: RENDER ERROR - Skipping render for numerical rubric ID {$rubric_id}";
                                        if (empty($rubric['criteria']) && empty($rubric['levels'])) {
                                            echo "<script>console.log('$error_message - Both criteria and levels are empty.');</script>";
                                            echo "<p class='text-danger'>Error: Cannot display rubric '{$rubric['name']}' (ID: {$rubric_id}) - configuration incomplete (missing both criteria and levels). Please check rubric setup in the dashboard.</p>";
                                        } else if (empty($rubric['criteria'])) {
                                            echo "<script>console.log('$error_message - Criteria is empty.');</script>";
                                            echo "<p class='text-danger'>Error: Cannot display rubric '{$rubric['name']}' (ID: {$rubric_id}) - configuration incomplete (missing criteria). Please check rubric setup in the dashboard.</p>";
                                        } else {
                                            echo "<script>console.log('$error_message - Levels is empty.');</script>";
                                            echo "<p class='text-danger'>Error: Cannot display rubric '{$rubric['name']}' (ID: {$rubric_id}) - configuration incomplete (missing levels). Please check rubric setup in the dashboard.</p>";
                                        }
                                    } else {
                                        echo render_numerical_rubric($rubric, $students, $existing_details);
                                    }
                                } elseif ($rubric['rubric_type'] === 'yesno') {
                                    if (empty($rubric['criteria'])) {
                                        echo "<script>console.log('DS-Index: RENDER ERROR - Skipping render for yes/no rubric ID {$rubric_id} - criteria is empty.');</script>";
                                        echo "<p class='text-danger'>Error: Cannot display rubric '{$rubric['name']}' (ID: {$rubric_id}) - configuration incomplete (missing criteria). Please check rubric setup in the dashboard.</p>";
                                    } else {
                                        echo render_yes_no_rubric($rubric, $students, $existing_details);
                                    }
                                } elseif ($rubric['rubric_type'] === 'passfail') {
                                    if (empty($rubric['levels'])) {
                                        echo "<script>console.log('DS-Index: RENDER ERROR - Skipping render for pass/fail rubric ID {$rubric_id} - levels is empty.');</script>";
                                        echo "<p class='text-danger'>Error: Cannot display rubric '{$rubric['name']}' (ID: {$rubric_id}) - configuration incomplete (missing pass/fail options). Please check rubric setup in the dashboard.</p>";
                                    } else {
                                        echo render_passfail_rubric($rubric, $existing_details);
                                    }
                                }
                                ?>
                            </div>
                        </div> <!-- End Rubric Card -->
                    <?php endforeach; ?>
                    
                    <!-- Weight Summary Card - Rendered ONCE after all rubrics -->
                    <?php if (!empty($weight_summary)): ?>
                    <div class="card mb-4" id="rubric-weight-summary-card" style="background-color: #f8f9fa;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Rubric Weight Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="weight-summary-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">Rubric</th>
                                            <th class="text-center" style="width: 15%;">Configured Weight</th>
                                            <th class="text-center" style="width: 35%;">Current Score(s)</th>
                                            <th class="text-center" style="width: 20%;">Actual %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- PHP RENDERING START - <?php echo date('Y-m-d H:i:s'); ?> - <?php echo count($weight_summary); ?> items -->
                                        <?php $row_num = 0; foreach ($weight_summary as $item): $row_num++; 
                                            $is_individual = !empty($item['is_individual']);
                                            $student_ids = $item['student_ids'] ?? [];
                                        ?>
                                        <!-- ROW <?php echo $row_num; ?>: ID=<?php echo $item['rubric_id']; ?> IS_INDIVIDUAL=<?php echo $is_individual ? 'yes' : 'no'; ?> NAME=<?php echo $item['name']; ?> -->
                                        <tr data-rubric-id="<?php echo $item['rubric_id']; ?>" data-is-individual="<?php echo $is_individual ? '1' : '0'; ?>" data-row-num="<?php echo $row_num; ?>">
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td class="text-center"><?php echo number_format($item['configured'], 1); ?>%</td>
                                            <td class="text-center rubric-current-score">
                                                <?php if ($is_individual && !empty($student_ids)): ?>
                                                    <!-- Individual scores displayed horizontally -->
                                                    <div class="d-flex justify-content-center align-items-center flex-wrap gap-1">
                                                        <?php foreach ($student_ids as $idx => $sid): ?>
                                                            <span class="badge bg-light text-dark border" id="weight-score-<?php echo $item['rubric_id']; ?>-s<?php echo $sid; ?>" title="Student ID: <?php echo $sid; ?>">0</span>
                                                            <?php if ($idx < count($student_ids) - 1): ?><span class="text-muted">|</span><?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <!-- Group score - single value -->
                                                    <span id="weight-score-<?php echo $item['rubric_id']; ?>">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center rubric-actual-percent" id="weight-percent-<?php echo $item['rubric_id']; ?>">0.0%</td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <!-- PHP RENDERING END - rendered <?php echo $row_num; ?> rows -->
                                    </tbody>
                                    <tfoot class="table-secondary" style="font-weight: bold;">
                                        <tr>
                                            <td>Total</td>
                                            <td class="text-center"><?php echo number_format($total_configured_weight, 1); ?>%</td>
                                            <td class="text-center" id="weight-total-score">
                                                <?php 
                                                // Check if we have individual scoring rubrics
                                                $hasIndividualRubrics = false;
                                                foreach ($weight_summary as $item) {
                                                    if (!empty($item['is_individual'])) {
                                                        $hasIndividualRubrics = true;
                                                        break;
                                                    }
                                                }
                                                if ($hasIndividualRubrics && !empty($students)): 
                                                ?>
                                                    <!-- Display individual totals for each student -->
                                                    <div class="d-flex justify-content-center align-items-center flex-wrap gap-1" id="weight-total-individual">
                                                        <?php foreach ($students as $idx => $student): ?>
                                                            <span class="badge bg-primary" id="weight-total-s<?php echo $student['id']; ?>">0</span>
                                                            <?php if ($idx < count($students) - 1): ?><span class="text-muted">|</span><?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    0
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center" id="weight-total-percent">
                                                <?php if ($hasIndividualRubrics && !empty($students)): ?>
                                                    <!-- Display individual percentages for each student -->
                                                    <div class="d-flex justify-content-center align-items-center flex-wrap gap-1" id="weight-total-percent-individual">
                                                        <?php foreach ($students as $idx => $student): ?>
                                                            <span class="badge bg-success" id="weight-percent-s<?php echo $student['id']; ?>">0.0%</span>
                                                            <?php if ($idx < count($students) - 1): ?><span class="text-muted">|</span><?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    0.0%
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <small><i class="bi bi-info-circle"></i> <strong>Note:</strong> Rubrics are marked as <span class="badge bg-info" style="font-size: 0.7em;">Individual</span> or <span class="badge bg-secondary" style="font-size: 0.7em;">Group</span>. For individual scoring, scores are shown per student (e.g., <code>40 | 35 | 42 | 38</code>). "Actual %" shows the weighted percentage contribution. Total row shows individual student totals when applicable.</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                <?php endif; ?>

                <!-- Overall Comments Section -->
                <div class="mb-3">
                    <label for="comments" class="form-label">Overall Comments</label>
                    <textarea class="form-control" id="comments" name="comments" rows="4"><?php echo htmlspecialchars($existing_evaluation['comments'] ?? ''); ?></textarea>
                    <small class="form-text text-muted">Provide overall feedback, strengths, weaknesses, and recommendations based on the rubrics above.</small>
                </div>

                <!-- Submit Button -->
                <!-- CHANGED: Allow re-evaluation/updates by removing $done_evaluating check -->
                <?php if (!empty($rubrics_in_group)): ?>
                    <div class="text-center mb-5">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <?php echo $done_evaluating ? 'Update Evaluation' : 'Submit Evaluation'; ?>
                        </button>
                    </div>
                <?php endif; ?>
                <div id="formStatus" class="mt-3"></div>

            </form>
        </div>
    </div>
  </div>
</main>

<!-- Evaluation Summary Modal -->
<div class="modal fade" id="evaluationSummaryModal" tabindex="-1" aria-labelledby="evaluationSummaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--main-white); color: white; border-bottom: 1px solid var(--neutral-300);">
        <h5 class="modal-title" id="evaluationSummaryModalLabel">
          <i class="fas fa-clipboard-check me-2"></i>Evaluation Summary - Review Before Submitting
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
        <div id="summaryContent">
          <!-- Content will be populated by JavaScript -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-arrow-left me-2"></i>Back to Edit
        </button>
        <button type="button" class="btn btn-info" id="downloadPdfBtn" title="Download summary as PDF">
          <i class="fas fa-file-pdf me-2"></i>Download PDF
        </button>
        <button type="button" class="btn btn-primary" id="confirmSubmitBtn">
          <i class="fas fa-paper-plane me-2"></i><?php echo $done_evaluating ? 'Confirm Update' : 'Confirm Submission'; ?>
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> <!-- Keep jQuery for now if other parts rely on it -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<!-- PDF Generation Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const toggleBtns = document.querySelectorAll('.toggle-btn');
  const sections = document.querySelectorAll('.section-toggle');
  const evaluationForm = document.getElementById('evaluationForm');
  const formStatusDiv = document.getElementById('formStatus');

  toggleBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const parentGroup = this.closest('.btn-group');
      if (!parentGroup) return;

      parentGroup.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      const targetId = this.dataset.target;
      // Find sections only within the same tab pane as the button
      const currentTabPane = this.closest('.tab-pane');
      if (currentTabPane) {
          currentTabPane.querySelectorAll('.section-toggle').forEach(section => {
              section.classList.add('d-none');
              section.classList.remove('d-block');
          });
      }

      const targetSection = document.getElementById(targetId);
      if (targetSection) {
          targetSection.classList.remove('d-none');
          targetSection.classList.add('d-block');
      }
    });
  });

    // --- Submission files map for preview and selection ---
    const submissionMap = <?php echo json_encode(array_reduce($submissionFiles, function($carry, $item){ $carry[$item['id']] = $item['file_name']; return $carry; }, []), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?> || {};

    // When a radio is selected, update preview iframe (client-side only)
    document.querySelectorAll('input[name="selected_submission"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const fid = this.value;
            const fname = submissionMap[fid];
            const iframe = document.getElementById('pdf');
            if (iframe && fname) {
                iframe.src = `../assets/uploads/submission/${encodeURIComponent(fname)}`;
            }
        });
    });

    // Set selected file for evaluation (persist to defense_schedule.related_requirement_files)
    const setBtn = document.getElementById('setSelectedFileBtn');
    if (setBtn) {
        setBtn.addEventListener('click', function() {
            const selected = document.querySelector('input[name="selected_submission"]:checked');
            if (!selected) {
                Swal.fire('No file selected', 'Please select a submitted file to set for evaluation.', 'warning');
                return;
            }
            const fileId = selected.value;
            // Send to server
            fetch('link_file_to_schedule.php', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new URLSearchParams({ schedule_id: '<?php echo intval($schedule_id); ?>', 'file_ids[]': fileId })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    Swal.fire('Updated', 'Selected file is now set for decision-support.', 'success');
                } else {
                    Swal.fire('Error', res.error || 'Failed to set file for evaluation.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Server error while setting file.', 'error');
            });
        });
    }

  const researchTab = document.getElementById('research-paper-tab');
  if (researchTab) {
      researchTab.addEventListener('shown.bs.tab', function() {
          const pdfToggleBtn = document.querySelector('#research-paper .toggle-btn[data-target="pdf-section"]');
          if (pdfToggleBtn) pdfToggleBtn.click();
      });
  }

    // -------------------------------------------------------------------
    // FOR DISPLAY OF GROUP SCORE DATA (Numerical)
    // -------------------------------------------------------------------
    window.updateGroupTotalScore = function(rubric_id) {
        let total = 0;
        // Select only number inputs associated with the specific rubric's group score
        // Exclude blank criteria (they don't have score inputs)
        document.querySelectorAll(`#evaluationForm input[type="number"][data-rubric-id="${rubric_id}"][name*="[group]"]`).forEach(input => {
            let val = parseFloat(input.value);
            if (!isNaN(val)) {
                total += val;
            }
        });
        
        // Update subtotal display in the table footer
        const subtotalCell = document.getElementById(`r${rubric_id}-subtotal`);
        if (subtotalCell) {
            subtotalCell.textContent = total.toFixed(2);
        }
        
        // Update weight summary table
        updateWeightSummary();
        
        // Update pass/fail status based on new totals
        updatePassFailStatus();
        
        console.log("Subtotal calculated for rubric", rubric_id, ":", total);
    }

    // -------------------------------------------------------------------
    // FOR DISPLAY OF GROUP SELECTION DATA (Yes/No)
    // -------------------------------------------------------------------
    window.updateGroupSelection = function(rubric_id) {
        // Find all group radio buttons for this rubric and update their corresponding display cell
        document.querySelectorAll(`#evaluationForm input[type="radio"][data-rubric-id="${rubric_id}"][name*="[group]"]`).forEach(radio => {
            const displayCell = radio.closest('tr').querySelector('.group-selection-display');
            if (displayCell) {
                const checkedRadio = radio.closest('tr').querySelector(`input[name="${radio.name}"]:checked`);
                if (checkedRadio) {
                    displayCell.textContent = checkedRadio.value === '1' ? 'Yes' : 'No';
                } else {
                    displayCell.textContent = '-';
                }
            }
        });
    }

    // -------------------------------------------------------------------
    // FOR DISPLAY OF INDIVIDUAL SCORE DATA (Numerical) - Calculate subtotals per student
    // -------------------------------------------------------------------
    window.updateIndividualTotalScore = function(rubric_id, student_id) {
        let total = 0;
        // Get all score inputs for this rubric and student
        document.querySelectorAll(`#evaluationForm input[type="number"][data-rubric-id="${rubric_id}"][data-student-id="${student_id}"]`).forEach(input => {
            let val = parseFloat(input.value);
            if (!isNaN(val)) {
                total += val;
            }
        });
        
        // Update subtotal display for this student
        const subtotalCell = document.getElementById(`r${rubric_id}-student-${student_id}-subtotal`);
        if (subtotalCell) {
            subtotalCell.textContent = total.toFixed(2);
        }
        
        // Update weight summary table (for individual scoring, we need to recalculate)
        updateWeightSummary();
        
        // Update pass/fail status based on new totals
        updatePassFailStatus();
        
        console.log("Individual subtotal calculated for rubric", rubric_id, "student", student_id, ":", total);
    }


    // -------------------------------------------------------------------
    // UPDATE WEIGHT SUMMARY TABLE
    // -------------------------------------------------------------------
    window.updateWeightSummary = function() {
        const summaryTable = document.getElementById('weight-summary-table');
        if (!summaryTable) return;
        
        let totalWeightedScore = 0;
        let totalCurrentScore = 0;
        
        // Process each row in the weight summary table
        summaryTable.querySelectorAll('tbody tr[data-rubric-id]').forEach(row => {
            const rubricId = row.dataset.rubricId;
            const isIndividual = row.dataset.isIndividual === '1';
            
            let currentScore = 0;
            let maxPossibleScore = 0;
            let configuredWeight = 0;
            
            // Get configured weight from the row
            const weightCell = row.querySelector('td:nth-child(2)');
            if (weightCell) {
                configuredWeight = parseFloat(weightCell.textContent) || 0;
            }
            
            // Find the rubric card
            const card = document.querySelector(`.rubric-card[data-rubric-id="${rubricId}"]`);
            if (card) {
                const rubricType = card.dataset.rubricType;
                
                if (rubricType === 'numerical') {
                    if (isIndividual) {
                        // Individual scoring - get scores for ALL students and update each badge
                        const subtotalCells = card.querySelectorAll('[id^="r' + rubricId + '-student-"][id$="-subtotal"]');
                        let totalStudentScores = 0;
                        let studentCount = 0;
                        
                        subtotalCells.forEach(cell => {
                            const score = parseFloat(cell.textContent) || 0;
                            totalStudentScores += score;
                            studentCount++;
                            
                            // Extract student ID from cell ID (format: r{rubricId}-student-{studentId}-subtotal)
                            const match = cell.id.match(/r\d+-student-(\d+)-subtotal/);
                            if (match) {
                                const studentId = match[1];
                                // Update the individual score badge in weight summary
                                const scoreBadge = document.getElementById(`weight-score-${rubricId}-s${studentId}`);
                                if (scoreBadge) {
                                    scoreBadge.textContent = score.toFixed(0);
                                }
                            }
                        });
                        
                        // Calculate average score for percentage calculation
                        currentScore = studentCount > 0 ? totalStudentScores / studentCount : 0;
                        
                        // Calculate max possible score (same for all students, use first student)
                        const firstStudentInput = card.querySelector('input[type="number"][data-rubric-id="' + rubricId + '"][data-student-id]');
                        if (firstStudentInput) {
                            const firstStudentId = firstStudentInput.dataset.studentId;
                            card.querySelectorAll(`input[type="number"][data-rubric-id="${rubricId}"][data-student-id="${firstStudentId}"]`).forEach(input => {
                                const max = parseFloat(input.getAttribute('max')) || 0;
                                maxPossibleScore += max;
                            });
                        }
                    } else {
                        // Group scoring - get the single subtotal
                        const subtotalCell = document.getElementById(`r${rubricId}-subtotal`);
                        if (subtotalCell) {
                            currentScore = parseFloat(subtotalCell.textContent) || 0;
                        }
                        
                        // Update the score display
                        const scoreCell = document.getElementById(`weight-score-${rubricId}`);
                        if (scoreCell) {
                            scoreCell.textContent = currentScore.toFixed(2);
                        }
                        
                        // Calculate max possible score for group
                        card.querySelectorAll(`input[type="number"][data-rubric-id="${rubricId}"][name*="[group]"]`).forEach(input => {
                            const max = parseFloat(input.getAttribute('max')) || 0;
                            maxPossibleScore += max;
                        });
                    }
                }
            }
            
            // Calculate weighted score for this row
            let weightedScore = 0;
            if (maxPossibleScore > 0) {
                const scorePercentage = (currentScore / maxPossibleScore);
                weightedScore = scorePercentage * configuredWeight;
            }
            
            // Update percentage cell - show the weighted contribution
            const percentCell = document.getElementById(`weight-percent-${rubricId}`);
            if (percentCell) {
                percentCell.textContent = weightedScore.toFixed(1) + '%';
            }
            
            totalCurrentScore += currentScore;
            totalWeightedScore += weightedScore;
        });
        
        // Check if we have individual scoring - need to calculate per-student totals
        const hasIndividualScoring = summaryTable.querySelector('tbody tr[data-is-individual="1"]') !== null;
        
        if (hasIndividualScoring) {
            // Calculate individual student totals and percentages
            const studentTotals = {};
            const studentWeightedScores = {};
            
            // Iterate through all rubrics again to collect per-student data
            summaryTable.querySelectorAll('tbody tr[data-rubric-id]').forEach(row => {
                const rubricId = row.dataset.rubricId;
                const isIndividual = row.dataset.isIndividual === '1';
                const card = document.querySelector(`.rubric-card[data-rubric-id="${rubricId}"]`);
                
                if (!card) return;
                
                const rubricType = card.dataset.rubricType;
                let configuredWeight = 0;
                const weightCell = row.querySelector('td:nth-child(2)');
                if (weightCell) {
                    configuredWeight = parseFloat(weightCell.textContent) || 0;
                }
                
                if (rubricType === 'numerical') {
                    if (isIndividual) {
                        // Individual rubric - get each student's score
                        const subtotalCells = card.querySelectorAll('[id^="r' + rubricId + '-student-"][id$="-subtotal"]');
                        
                        // Get max possible score
                        let maxPossibleScore = 0;
                        const firstStudentInput = card.querySelector('input[type="number"][data-rubric-id="' + rubricId + '"][data-student-id]');
                        if (firstStudentInput) {
                            const firstStudentId = firstStudentInput.dataset.studentId;
                            card.querySelectorAll(`input[type="number"][data-rubric-id="${rubricId}"][data-student-id="${firstStudentId}"]`).forEach(input => {
                                maxPossibleScore += parseFloat(input.getAttribute('max')) || 0;
                            });
                        }
                        
                        subtotalCells.forEach(cell => {
                            const score = parseFloat(cell.textContent) || 0;
                            const match = cell.id.match(/r\d+-student-(\d+)-subtotal/);
                            if (match) {
                                const studentId = match[1];
                                if (!studentTotals[studentId]) {
                                    studentTotals[studentId] = 0;
                                    studentWeightedScores[studentId] = 0;
                                }
                                studentTotals[studentId] += score;
                                
                                // Calculate this student's weighted score for this rubric
                                if (maxPossibleScore > 0) {
                                    const scorePercentage = score / maxPossibleScore;
                                    studentWeightedScores[studentId] += scorePercentage * configuredWeight;
                                }
                            }
                        });
                    } else {
                        // Group rubric - apply same score to all students
                        const subtotalCell = document.getElementById(`r${rubricId}-subtotal`);
                        const score = subtotalCell ? parseFloat(subtotalCell.textContent) || 0 : 0;
                        
                        // Get max possible score
                        let maxPossibleScore = 0;
                        card.querySelectorAll(`input[type="number"][data-rubric-id="${rubricId}"][name*="[group]"]`).forEach(input => {
                            maxPossibleScore += parseFloat(input.getAttribute('max')) || 0;
                        });
                        
                        // Apply to all students
                        const allStudentIds = Array.from(summaryTable.querySelectorAll('tbody tr[data-is-individual="1"]')).map(r => {
                            const scoreCell = r.querySelector('.rubric-current-score');
                            const badges = scoreCell ? scoreCell.querySelectorAll('.badge[id*="-s"]') : [];
                            return Array.from(badges).map(b => {
                                const match = b.id.match(/weight-score-\d+-s(\d+)/);
                                return match ? match[1] : null;
                            }).filter(id => id !== null);
                        }).flat();
                        
                        // Use unique student IDs
                        const uniqueStudentIds = [...new Set(allStudentIds)];
                        uniqueStudentIds.forEach(studentId => {
                            if (!studentTotals[studentId]) {
                                studentTotals[studentId] = 0;
                                studentWeightedScores[studentId] = 0;
                            }
                            studentTotals[studentId] += score;
                            
                            if (maxPossibleScore > 0) {
                                const scorePercentage = score / maxPossibleScore;
                                studentWeightedScores[studentId] += scorePercentage * configuredWeight;
                            }
                        });
                    }
                }
            });
            
            // Update individual student totals in footer
            Object.keys(studentTotals).forEach(studentId => {
                const totalBadge = document.getElementById(`weight-total-s${studentId}`);
                if (totalBadge) {
                    totalBadge.textContent = studentTotals[studentId].toFixed(0);
                }
                
                const percentBadge = document.getElementById(`weight-percent-s${studentId}`);
                if (percentBadge) {
                    percentBadge.textContent = studentWeightedScores[studentId].toFixed(1) + '%';
                }
            });
        } else {
            // No individual scoring - update single totals
            const totalScoreCell = document.getElementById('weight-total-score');
            if (totalScoreCell && !totalScoreCell.querySelector('.badge')) {
                totalScoreCell.textContent = totalCurrentScore.toFixed(2);
            }
            
            const totalPercentCell = document.getElementById('weight-total-percent');
            if (totalPercentCell && !totalPercentCell.querySelector('.badge')) {
                totalPercentCell.textContent = totalWeightedScore.toFixed(1) + '%';
            }
        }
    };

    // -------------------------------------------------------------------
    // EVALUATION SUMMARY GENERATION
    // -------------------------------------------------------------------
    window.showEvaluationSummary = function() {
        // Update weight summary first to ensure latest scores are calculated
        updateWeightSummary();
        
        const summaryContent = document.getElementById('summaryContent');
        if (!summaryContent) return;
        
        let html = '';
        
        // Team Details Section - Reorganized Layout
        html += '<div class="summary-section" style="margin-bottom: 30px; page-break-inside: avoid;">';
        
        // College - Top Center
        html += '<div style="text-align: center; margin-bottom: 10px;"><h3 style="font-weight: normal; border: none; padding: 0; margin: 0; display: inline-block;"><?php echo htmlspecialchars($schedule_info['team_college'] ?? 'N/A'); ?></h3></div>';
        
        // Defense Type - Bold, Below College
        <?php 
            $typeLabel = [
                'title_proposal' => 'Title Proposal Defense',
                'title_defense' => 'Title Defense',
                'final_defense' => 'Final Defense'
            ];
            $label = $typeLabel[$defense_type] ?? ucfirst(str_replace('_', ' ', $defense_type));
        ?>
        html += '<div style="text-align: center; margin-bottom: 20px;"><strong style="font-size: 1.1em;"><?php echo htmlspecialchars($label); ?></strong></div>';
        
        // Two Column Layout (using table for PDF compatibility)
        html += '<table style="width: 100%; border: none; margin-bottom: 20px;"><tr>';
        
        // Column 1 - Proponents
        html += '<td style="width: 50%; vertical-align: top; border: none; padding-right: 15px;">';
        html += '<strong>Proponents:</strong><br>';
        <?php if (!empty($students)): ?>
            html += '<ul style="margin: 5px 0; padding-left: 20px;">';
            <?php foreach ($students as $student): ?>
            html += '<li><?php echo htmlspecialchars($student['fullname']); ?></li>';
            <?php endforeach; ?>
            html += '</ul>';
        <?php else: ?>
            html += '<span style="font-style: italic; color: #666;">No members found</span>';
        <?php endif; ?>
        html += '</td>';
        
        // Column 2 - Defense Info, Program, Adviser
        html += '<td style="width: 50%; vertical-align: top; border: none; padding-left: 15px;">';
        html += '<strong>Defense Date:</strong> <?php echo htmlspecialchars(date('F d, Y', strtotime($schedule_info['schedule_date'] ?? ''))); ?><br>';
        html += '<strong>Defense Time:</strong> <?php echo htmlspecialchars(date('g:i A', strtotime($schedule_info['start_time'] ?? ''))) . ' - ' . htmlspecialchars(date('g:i A', strtotime($schedule_info['end_time'] ?? ''))); ?><br>';
        html += '<strong>Program:</strong> <?php echo htmlspecialchars($schedule_info['team_program'] ?? 'N/A'); ?><br>';
        html += '<strong>Research Adviser:</strong> <?php echo htmlspecialchars($adviser_name); ?>';
        html += '</td>';
        
        html += '</tr></table>'; // End two-column layout
        
        // Research Title - Below the Two Columns
        html += '<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">';
        html += '<strong>Research Title:</strong><br>';
        html += '<span style="font-size: 1.05em;"><?php echo htmlspecialchars($researchTitle); ?></span>';
        html += '</div>';
        
        html += '</div>'; // End summary-section
        
        // Rubrics/Score Sheets Section
        html += '<div class="summary-section" style="margin-bottom: 30px;">';
        html += '<h3 style="margin-bottom: 15px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 8px;">EVALUATION SCORE SHEETS</h3>';
        
        // Iterate through each rubric card
        const rubricCards = document.querySelectorAll('.rubric-card');
        rubricCards.forEach((card, index) => {
            const rubricId = card.dataset.rubricId;
            const rubricType = card.dataset.rubricType;
            const rubricWeight = card.dataset.weight;
            const rubricName = card.querySelector('.card-header h5')?.textContent?.trim() || 'Rubric ' + (index + 1);
            const rubricDesc = card.querySelector('.card-header small')?.textContent?.trim() || '';
            
            html += '<div class="rubric-summary" style="margin-bottom: 25px; page-break-inside: avoid;">';
            html += '<h4 style="margin-bottom: 10px; font-weight: bold;">' + (index + 1) + '. ' + rubricName;
            if (rubricWeight && rubricWeight != '0') {
                html += ' (Weight: ' + parseFloat(rubricWeight).toFixed(1) + '%)';
            }
            html += '</h4>';
            if (rubricDesc) {
                html += '<p style="font-style: italic; color: #555; margin-bottom: 10px;">' + rubricDesc + '</p>';
            }
            
            if (rubricType === 'numerical') {
                html += generateNumericalSummary(card);
            } else if (rubricType === 'yesno') {
                html += generateYesNoSummary(card);
            } else if (rubricType === 'passfail') {
                html += generatePassFailSummary(card);
            }
            
            html += '</div>';
        });
        
        html += '</div>';
        
        // Comments Section
        html += '<div class="summary-section" style="margin-bottom: 20px; page-break-inside: avoid;">';
        html += '<h3 style="margin-bottom: 15px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 8px;">OVERALL COMMENTS</h3>';
        const comments = document.getElementById('comments')?.value || 'No comments provided.';
        html += '<div style="padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd; white-space: pre-wrap; font-family: inherit;">' + escapeHtml(comments) + '</div>';
        html += '</div>';
        
        // Evaluator Section
        html += '<div class="summary-section" style="margin-bottom: 20px; page-break-inside: avoid;">';
        html += '<h3 style="margin-bottom: 15px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 8px;">EVALUATED BY</h3>';
        html += '<div style="padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd;">';
        html += '<strong><?php echo htmlspecialchars($evaluator_name); ?></strong>';
        html += '</div>';
        html += '</div>';
        
        summaryContent.innerHTML = html;
        
        // Show the modal
        const summaryModal = new bootstrap.Modal(document.getElementById('evaluationSummaryModal'));
        summaryModal.show();
    };
    
    function generateWeightSummaryForModal() {
        console.log('=== generateWeightSummaryForModal() called ==='); // Debug
        
        let html = '<div class="summary-section" style="margin-bottom: 30px; page-break-inside: avoid;">';
        html += '<h3 style="margin-bottom: 15px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 8px;"><i class="bi bi-bar-chart"></i> RUBRIC WEIGHT SUMMARY</h3>';
        
        // Get data from the weight summary table
        const summaryTable = document.getElementById('weight-summary-table');
        
        console.log('Weight summary table found:', summaryTable); // Debug log
        
        if (!summaryTable) {
            console.warn('Weight summary table not found - ID: weight-summary-table');
            html += '<div style="padding: 15px; background-color: #fff3cd; border: 1px solid #ffc107;">';
            html += '<p style="margin: 0; color: #856404;"><strong>⚠️ Weight summary table not found</strong></p>';
            html += '</div>';
            html += '</div>';
            return html;
        }
        
        const rows = summaryTable.querySelectorAll('tbody tr[data-rubric-id]');
        console.log('Number of rubric rows found:', rows.length); // Debug log
        
        if (rows.length === 0) {
            html += '<p style="font-style: italic; color: #666;">No rubric data available</p>';
            html += '</div>';
            return html;
        }
        
        html += '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;" class="summary-table">';
        html += '<thead><tr style="background-color: #f0f0f0;">';
        html += '<th style="border: 1px solid #999; padding: 8px; text-align: left; font-weight: bold;">Rubric</th>';
        html += '<th style="border: 1px solid #999; padding: 8px; text-align: center; font-weight: bold;">Configured Weight</th>';
        html += '<th style="border: 1px solid #999; padding: 8px; text-align: center; font-weight: bold;">Current Score(s)</th>';
        html += '<th style="border: 1px solid #999; padding: 8px; text-align: center; font-weight: bold;">Actual %</th>';
        html += '</tr></thead><tbody>';
        
        // Process each row
        rows.forEach(row => {
            const rubricId = row.dataset.rubricId;
            const isIndividual = row.dataset.isIndividual === '1';
            
            // Rubric name
            const rubricName = row.querySelector('td:nth-child(1)')?.textContent?.trim() || 'N/A';
            
            // Configured weight
            const configWeight = row.querySelector('td:nth-child(2)')?.textContent?.trim() || '0%';
            
            // Current scores
            let currentScores = '';
            if (isIndividual) {
                // Get individual scores from badges
                const badges = row.querySelectorAll('.rubric-current-score .badge');
                const scores = [];
                badges.forEach(badge => {
                    if (badge.textContent.trim() !== '|') {
                        scores.push(badge.textContent.trim());
                    }
                });
                currentScores = scores.join(' | ');
            } else {
                // Group score
                const scoreSpan = row.querySelector('.rubric-current-score span:not(.badge)');
                currentScores = scoreSpan?.textContent?.trim() || '0';
            }
            
            // Actual percentage
            const actualPercent = row.querySelector('.rubric-actual-percent')?.textContent?.trim() || '0.0%';
            
            html += '<tr>';
            html += '<td style="border: 1px solid #999; padding: 8px;">' + rubricName + '</td>';
            html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;">' + configWeight + '</td>';
            html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;">' + currentScores + '</td>';
            html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;">' + actualPercent + '</td>';
            html += '</tr>';
        });
        
        // Total row
        const totalConfigWeight = summaryTable.querySelector('tfoot td:nth-child(2)')?.textContent?.trim() || '0%';
        
        // Check if we have individual scoring
        const hasIndividualScoring = summaryTable.querySelector('tbody tr[data-is-individual="1"]') !== null;
        
        let totalScore = '';
        let totalPercent = '';
        
        if (hasIndividualScoring) {
            // Get individual student totals
            const totalScoreDiv = document.getElementById('weight-total-score');
            const totalPercentDiv = document.getElementById('weight-total-percent');
            
            if (totalScoreDiv && totalScoreDiv.querySelector('.badge')) {
                const scoreBadges = totalScoreDiv.querySelectorAll('.badge');
                const scores = [];
                scoreBadges.forEach(badge => {
                    if (badge.textContent.trim() !== '|') {
                        scores.push(badge.textContent.trim());
                    }
                });
                totalScore = scores.join(' | ');
            } else {
                totalScore = document.getElementById('weight-total-score')?.textContent?.trim() || '0';
            }
            
            if (totalPercentDiv && totalPercentDiv.querySelector('.badge')) {
                const percentBadges = totalPercentDiv.querySelectorAll('.badge');
                const percents = [];
                percentBadges.forEach(badge => {
                    if (badge.textContent.trim() !== '|') {
                        percents.push(badge.textContent.trim());
                    }
                });
                totalPercent = percents.join(' | ');
            } else {
                totalPercent = document.getElementById('weight-total-percent')?.textContent?.trim() || '0.0%';
            }
        } else {
            totalScore = document.getElementById('weight-total-score')?.textContent?.trim() || '0';
            totalPercent = document.getElementById('weight-total-percent')?.textContent?.trim() || '0.0%';
        }
        
        html += '</tbody><tfoot><tr style="background-color: #f0f0f0; font-weight: bold;">';
        html += '<td style="border: 1px solid #999; padding: 8px;">Total</td>';
        html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;">' + totalConfigWeight + '</td>';
        html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;">' + totalScore + '</td>';
        html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;">' + totalPercent + '</td>';
        html += '</tr></tfoot></table>';
        
        html += '<div style="padding: 10px; background-color: #e7f3ff; border-left: 4px solid #0066cc; margin-top: 15px;">';
        html += '<small><i class="bi bi-info-circle"></i> <strong>Note:</strong> For individual scoring rubrics, scores are shown per student (e.g., <code>40 | 35 | 42 | 38</code>). "Actual %" shows the weighted percentage contribution based on the average.</small>';
        html += '</div>';
        
        html += '</div>';
        return html;
    }
    
    function generateNumericalSummary(card) {
        let html = '<table style="width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: avoid;" class="summary-table">';
        html += '<thead><tr style="background-color: #f0f0f0;">';
        html += '<th style="border: 1px solid #999; padding: 8px; text-align: left; font-weight: bold;">Criteria</th>';
        
        const tbody = card.querySelector('tbody');
        if (!tbody) return '<p style="font-style: italic; color: #666;">No data available</p>';
        
        const firstRow = tbody.querySelector('tr[data-criterion-id]:not(.blank-criterion-row)');
        if (!firstRow) return '<p style="font-style: italic; color: #666;">No criteria found</p>';
        
        const isIndividual = firstRow.dataset.isIndividual === '1';
        
        // Get student info for individual rubrics
        let studentIds = [];
        if (isIndividual) {
            // Individual scoring - show student columns
            firstRow.querySelectorAll('.student-score-cell').forEach(cell => {
                const input = cell.querySelector('input');
                const studentId = input?.dataset?.studentId;
                if (studentId) {
                    studentIds.push(studentId);
                    const studentName = getStudentName(studentId);
                    html += '<th style="border: 1px solid #999; padding: 8px; text-align: center; font-weight: bold;">' + studentName + '</th>';
                }
            });
        } else {
            // Group scoring - show level description columns and score column
            // Get level headers from thead
            const thead = card.querySelector('thead tr');
            if (thead) {
                const levelHeaders = thead.querySelectorAll('th.level-col');
                levelHeaders.forEach(header => {
                    const levelName = header.innerHTML.split('<')[0].trim();
                    html += '<th style="border: 1px solid #999; padding: 8px; text-align: center; font-weight: bold;">' + levelName + '</th>';
                });
            }
            html += '<th style="border: 1px solid #999; padding: 8px; text-align: center; font-weight: bold;">Score</th>';
        }
        
        html += '</tr></thead><tbody>';
        
        // For individual rubrics, track subtotals per student
        let studentSubtotals = {};
        if (isIndividual) {
            studentIds.forEach(id => { studentSubtotals[id] = 0; });
        }
        
        // Iterate through criteria rows
        tbody.querySelectorAll('tr[data-criterion-id]').forEach(row => {
            const criterionText = row.querySelector('td:first-child')?.textContent?.trim() || 'N/A';
            
            // Check if this is a section header (blank criterion row)
            const isBlankRow = row.classList.contains('blank-criterion-row');
            
            if (isBlankRow) {
                // Section header row - render as a spanning row without score
                let colspan;
                if (isIndividual) {
                    colspan = studentIds.length + 1;
                } else {
                    // Count level columns + criteria + score
                    const levelCount = card.querySelectorAll('thead th.level-col').length;
                    colspan = levelCount + 2;
                }
                html += '<tr style="background-color: #e9ecef;"><td colspan="' + colspan + '" style="border: 1px solid #999; padding: 10px; font-weight: bold;">' + criterionText + '</td></tr>';
                return; // Skip to next row
            }
            
            html += '<tr><td style="border: 1px solid #999; padding: 8px;">' + criterionText + '</td>';
            
            if (isIndividual) {
                // For individual scoring, iterate through each student
                studentIds.forEach(studentId => {
                    const input = row.querySelector(`.criterion-score-input[data-student-id="${studentId}"]`);
                    const value = input?.value || '—';
                    const min = input?.min || '0';
                    const max = input?.max || '0';
                    html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;">';
                    html += '<strong>' + value + '</strong><br>';
                    html += '<span style="font-size: 0.85em; color: #666;">(' + min + '-' + max + ')</span>';
                    html += '</td>';
                    // Add to subtotal
                    if (value !== '—' && !isNaN(parseFloat(value))) {
                        studentSubtotals[studentId] += parseFloat(value);
                    }
                });
            } else {
                // Group scoring - show level descriptions then score
                row.querySelectorAll('.level-cell').forEach(cell => {
                    const description = cell.textContent?.trim() || '';
                    html += '<td style="border: 1px solid #999; padding: 8px; font-size: 0.9em;">' + description + '</td>';
                });
                
                const scoreInput = row.querySelector('.entered-score');
                const value = scoreInput?.value || '—';
                html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;">';
                html += '<strong style="font-size: 1.1em;">' + value + '</strong>';
                html += '</td>';
            }
            
            html += '</tr>';
        });
        
        // Add subtotal row for individual rubrics
        if (isIndividual && studentIds.length > 0) {
            html += '<tr style="background-color: #f0f0f0; font-weight: bold;"><td style="border: 1px solid #999; padding: 8px;">Subtotal</td>';
            studentIds.forEach(studentId => {
                html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;">' + studentSubtotals[studentId].toFixed(2) + '</td>';
            });
            html += '</tr>';
        }
        
        html += '</tbody></table>';
        return html;
    }
    
    function generateYesNoSummary(card) {
        let html = '<table style="width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: avoid;" class="summary-table">';
        html += '<thead><tr style="background-color: #f0f0f0;">';
        html += '<th style="border: 1px solid #999; padding: 8px; text-align: left; font-weight: bold;">Criteria</th>';
        
        const tbody = card.querySelector('tbody');
        if (!tbody) return '<p style="font-style: italic; color: #666;">No data available</p>';
        
        const firstRow = tbody.querySelector('tr[data-criterion-id]');
        if (!firstRow) return '<p style="font-style: italic; color: #666;">No criteria found</p>';
        
        const isIndividual = firstRow.dataset.isIndividual === '1';
        
        if (isIndividual) {
            // Individual - show student columns
            firstRow.querySelectorAll('td[class*="text-center"]:not(:first-child)').forEach((cell, idx) => {
                const radioInputs = cell.querySelectorAll('input[type="radio"]');
                if (radioInputs.length > 0) {
                    const studentId = radioInputs[0].name.match(/\[(\d+)\]$/)?.[1];
                    if (studentId) {
                        const studentName = getStudentName(studentId);
                        html += '<th style="border: 1px solid #999; padding: 8px; text-align: center; font-weight: bold;">' + studentName + '</th>';
                    }
                }
            });
        } else {
            html += '<th style="border: 1px solid #999; padding: 8px; text-align: center; font-weight: bold;">Selection</th>';
        }
        
        html += '</tr></thead><tbody>';
        
        tbody.querySelectorAll('tr[data-criterion-id]').forEach(row => {
            const criterionText = row.querySelector('td:first-child')?.textContent?.trim() || 'N/A';
            html += '<tr><td style="border: 1px solid #999; padding: 8px;">' + criterionText + '</td>';
            
            if (isIndividual) {
                row.querySelectorAll('td[class*="text-center"]:not(:first-child)').forEach(cell => {
                    const checked = cell.querySelector('input[type="radio"]:checked');
                    const value = checked ? (checked.value === '1' ? 'Yes' : 'No') : '—';
                    html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;"><strong>' + value + '</strong></td>';
                });
            } else {
                const checked = row.querySelector('input[type="radio"]:checked');
                const value = checked ? (checked.value === '1' ? 'Yes' : 'No') : '—';
                html += '<td style="border: 1px solid #999; padding: 8px; text-align: center;"><strong>' + value + '</strong></td>';
            }
            
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        return html;
    }
    
    function generatePassFailSummary(card) {
        const statusDiv = card.querySelector('[id^="passfail_status_"]');
        const statusText = statusDiv?.querySelector('.passfail-status-text')?.textContent || 'Status not determined';
        
        // Get the hidden input with threshold data
        const hiddenInput = card.querySelector('.auto-passfail-result');
        const threshold1 = hiddenInput?.dataset?.threshold1 || '0';
        const threshold2 = hiddenInput?.dataset?.threshold2 || '0';
        const threshold3 = hiddenInput?.dataset?.threshold3 || '0';
        
        // Get the current percentage from the weight summary
        const totalPercentElement = document.getElementById('weight-total-percent');
        const currentPercentage = totalPercentElement ? parseFloat(totalPercentElement.textContent) : 0;
        
        // Get threshold descriptions from the alert
        const alertDiv = card.querySelector('.alert-info');
        const thresholdDescriptions = [];
        if (alertDiv) {
            const listItems = alertDiv.querySelectorAll('li');
            listItems.forEach(li => {
                thresholdDescriptions.push(li.textContent.trim());
            });
        }
        
        let html = '<div style="padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; margin-top: 10px;">';
        html += '<h5 style="font-weight: bold; margin-bottom: 10px;"><i class="bi bi-info-circle"></i> Automated Pass/Fail Determination</h5>';
        html += '<p style="margin-bottom: 10px;">This rubric automatically determines pass/fail status based on your total numerical score:</p>';
        
        if (thresholdDescriptions.length > 0) {
            html += '<ul style="margin-bottom: 15px;">';
            thresholdDescriptions.forEach(desc => {
                html += '<li>' + desc + '</li>';
            });
            html += '</ul>';
        }
        
        html += '<div style="padding: 12px; background-color: #d1ecf1; border: 2px solid #0c5460; border-radius: 5px; margin-top: 15px;">';
        html += '<strong style="font-size: 1.1em;">Current Status:</strong> <span style="font-size: 1.1em; color: #0c5460;">' + statusText + '</span>';
        html += '</div>';
        
        html += '</div>';

        // Rubric Weight Summary Section - ADD BEFORE SCORE SHEETS
        html += generateWeightSummaryForModal();
        
        return html;
    }
    
    function getStudentName(studentId) {
        // Map student IDs to names from PHP data
        const studentMap = {
            <?php foreach ($students as $student): ?>
            <?php echo $student['id']; ?>: '<?php echo htmlspecialchars($student['first_name']); ?>',
            <?php endforeach; ?>
        };
        return studentMap[studentId] || 'Student ' + studentId;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // -------------------------------------------------------------------
    // PDF DOWNLOAD FUNCTIONALITY
    // -------------------------------------------------------------------
    window.downloadEvaluationPDF = function() {
        const downloadBtn = document.getElementById('downloadPdfBtn');
        const originalBtnHtml = downloadBtn?.innerHTML;
        
        if (downloadBtn) {
            downloadBtn.disabled = true;
            downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating PDF...';
        }
        
        const summaryContent = document.getElementById('summaryContent');
        if (!summaryContent) {
            Swal.fire('Error', 'Summary content not found', 'error');
            if (downloadBtn) {
                downloadBtn.disabled = false;
                downloadBtn.innerHTML = originalBtnHtml;
            }
            return;
        }
        
        // Clone the content for PDF generation
        const clonedContent = summaryContent.cloneNode(true);
        
        // Create a temporary container with better styling for PDF
        const tempContainer = document.createElement('div');
        tempContainer.style.position = 'absolute';
        tempContainer.style.left = '-9999px';
        tempContainer.style.top = '0';
        tempContainer.style.width = '210mm'; // A4 width
        tempContainer.style.padding = '20px';
        tempContainer.style.backgroundColor = 'white';
        tempContainer.style.fontFamily = 'Arial, sans-serif';
        tempContainer.style.fontSize = '11px';
        tempContainer.style.color = '#000';
        tempContainer.style.lineHeight = '1.4';
        tempContainer.appendChild(clonedContent);
        document.body.appendChild(tempContainer);
        
        // Enhance table styling for PDF with page break handling
        const tables = tempContainer.querySelectorAll('.summary-table');
        tables.forEach(table => {
            table.style.width = '100%';
            table.style.borderCollapse = 'collapse';
            table.style.marginBottom = '15px';
            table.style.pageBreakInside = 'avoid';
            
            // Style table headers
            table.querySelectorAll('thead th').forEach(th => {
                th.style.backgroundColor = '#e0e0e0';
                th.style.fontWeight = 'bold';
                th.style.border = '1px solid #333';
                th.style.padding = '8px';
                th.style.fontSize = '10px';
            });
            
            // Style table cells
            table.querySelectorAll('tbody td').forEach(td => {
                td.style.border = '1px solid #666';
                td.style.padding = '6px 8px';
                td.style.fontSize = '10px';
            });
            
            // Prevent table rows from breaking across pages
            table.querySelectorAll('tr').forEach(tr => {
                tr.style.pageBreakInside = 'avoid';
                tr.style.pageBreakAfter = 'auto';
            });
        });
        
        // Ensure sections don't break
        const sections = tempContainer.querySelectorAll('.summary-section, .rubric-summary');
        sections.forEach(section => {
            section.style.pageBreakInside = 'avoid';
            section.style.marginBottom = '20px';
        });
        
        // Style headings
        tempContainer.querySelectorAll('h3').forEach(h => {
            h.style.fontSize = '14px';
            h.style.fontWeight = 'bold';
            h.style.marginTop = '10px';
            h.style.marginBottom = '10px';
            h.style.pageBreakAfter = 'avoid';
        });
        
        tempContainer.querySelectorAll('h4').forEach(h => {
            h.style.fontSize = '12px';
            h.style.fontWeight = 'bold';
            h.style.marginTop = '8px';
            h.style.marginBottom = '8px';
            h.style.pageBreakAfter = 'avoid';
        });
        
        // Use html2canvas with better settings
        html2canvas(tempContainer, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff',
            windowWidth: tempContainer.scrollWidth,
            windowHeight: tempContainer.scrollHeight,
            onclone: (clonedDoc) => {
                // Additional styling adjustments in the cloned document if needed
                const clonedContainer = clonedDoc.querySelector('body > div');
                if (clonedContainer) {
                    clonedContainer.style.display = 'block';
                }
            }
        }).then(canvas => {
            document.body.removeChild(tempContainer);
            
            const imgData = canvas.toDataURL('image/png');
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4',
                compress: true
            });
            
            const imgWidth = 210; // A4 width in mm
            const pageHeight = 297; // A4 height in mm
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            let heightLeft = imgHeight;
            let position = 0;
            
            // Add first page
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight, undefined, 'FAST');
            heightLeft -= pageHeight;
            
            // Add additional pages if content is longer than one page
            while (heightLeft > 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight, undefined, 'FAST');
                heightLeft -= pageHeight;
            }
            
            // Generate filename with timestamp
            const researchTitle = '<?php echo addslashes($researchTitle ?? "Evaluation"); ?>';
            const date = new Date();
            const timestamp = date.getFullYear() + 
                             String(date.getMonth() + 1).padStart(2, '0') + 
                             String(date.getDate()).padStart(2, '0') + '_' +
                             String(date.getHours()).padStart(2, '0') + 
                             String(date.getMinutes()).padStart(2, '0');
            
            // Sanitize filename
            const sanitizedTitle = researchTitle.substring(0, 50).replace(/[^a-z0-9]/gi, '_');
            const filename = `Evaluation_Summary_${sanitizedTitle}_${timestamp}.pdf`;
            
            // Save the PDF
            pdf.save(filename);
            
            // Reset button
            if (downloadBtn) {
                downloadBtn.disabled = false;
                downloadBtn.innerHTML = originalBtnHtml;
            }
            
            Swal.fire({
                title: 'Success!',
                text: 'PDF downloaded successfully',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(error => {
            console.error('PDF generation error:', error);
            if (document.body.contains(tempContainer)) {
                document.body.removeChild(tempContainer);
            }
            
            if (downloadBtn) {
                downloadBtn.disabled = false;
                downloadBtn.innerHTML = originalBtnHtml;
            }
            
            Swal.fire('Error', 'Failed to generate PDF. Please try again.', 'error');
        });
    };
    
    // Handle download PDF button click
    document.getElementById('downloadPdfBtn')?.addEventListener('click', function() {
        downloadEvaluationPDF();
    });
    
    // Handle confirm submit button in modal
    document.getElementById('confirmSubmitBtn')?.addEventListener('click', function() {
        // Close the modal
        const summaryModal = bootstrap.Modal.getInstance(document.getElementById('evaluationSummaryModal'));
        if (summaryModal) {
            summaryModal.hide();
        }
        
        // Show final confirmation
        Swal.fire({
            title: 'Final Confirmation',
            html: '<p>Are you sure you want to submit this evaluation?</p><p class="text-muted small">Once submitted, you can still update it later if needed.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Submit',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with actual submission
                submitEvaluationForm();
            }
        });
    });

    // -------------------------------------------------------------------
    // PDF Fullscreen Toggle
    // -------------------------------------------------------------------
    window.toggleFullScreen = function() {
        const iframe = document.getElementById('pdf');
        if (!iframe) return;
        if (iframe.requestFullscreen) {
            iframe.requestFullscreen();
        } else if (iframe.mozRequestFullScreen) { /* Firefox */
            iframe.mozRequestFullScreen();
        } else if (iframe.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
            iframe.webkitRequestFullscreen();
        } else if (iframe.msRequestFullscreen) { /* IE/Edge */
            iframe.msRequestFullscreen();
        }
    }

    // -------------------------------------------------------------------
    // LIMITS THE ENTERED SCORE ON THE SCORE COLUMN IN NUMERIC CRITERIA
    // -------------------------------------------------------------------
    evaluationForm.querySelectorAll('.entered-score').forEach(input => {
        const validateValue = (eventSource) => {
            const element = input; // Use 'input' from the outer scope closure
            const minAttr = element.min;
            const maxAttr = element.max;
            const currentValue = element.value;

            // Only proceed if min/max attributes are present and valid numbers
            if (minAttr === '' || maxAttr === '') return; // Skip if attributes missing

            const parsedMin = parseFloat(minAttr);
            const parsedMax = parseFloat(maxAttr);

            if (isNaN(parsedMin) || isNaN(parsedMax)) {
                console.error(`Validation Error (ID: ${element.id}): Could not parse min (${minAttr}) or max (${maxAttr}) attributes.`);
                return; // Skip if attributes are not numbers
            }

            // Determine the effective minimum value
            // Use strict equality check, assuming points are typically integers or simple decimals
            let effectiveMin = parsedMin;
            if (parsedMin === parsedMax) {
                effectiveMin = 0;
                // console.log(`${eventSource} Event (ID: ${element.id}): min (${parsedMin}) equals max (${parsedMax}). Effective min set to 0.`);
            }

            // Handle the current value
            if (currentValue === '') {
                // Allow empty value during input; validation occurs on submit or blur potentially
                return;
            }

            let value = parseFloat(currentValue);

            if (isNaN(value)) {
                // If the value is not a number (and not empty), clear it on blur
                if (eventSource === 'Blur') {
                     // console.log(`${eventSource} Event (ID: ${element.id}): Invalid non-empty value "${currentValue}". Clearing.`);
                     element.value = '';
                }
                return; // Stop validation if value is not a number
            }

            // Validate against the effective minimum and the parsed maximum
            let correctedValue = value; // Start with the current parsed value

            if (value < effectiveMin) {
                // console.log(`${eventSource} Event (ID: ${element.id}): Value ${value} < effectiveMin ${effectiveMin}. Correcting to ${effectiveMin}.`);
                correctedValue = effectiveMin;
            } else if (value > parsedMax) {
                // console.log(`${eventSource} Event (ID: ${element.id}): Value ${value} > parsedMax ${parsedMax}. Correcting to ${parsedMax}.`);
                correctedValue = parsedMax;
            }

            // Update the input field only if the value was corrected or if reformatting is desired (e.g., removing leading zeros)
            // Use toString() to avoid potential floating point representation issues when setting the value back
            if (correctedValue !== value || element.value !== correctedValue.toString()) {
                 element.value = correctedValue.toString();
            }
        };

        // Validate on input event
        input.addEventListener('input', () => {
            validateValue('Input');
            updateTotalScore(); // Update total score on input change
        });

        // Validate and potentially clear invalid input on blur event
        input.addEventListener('blur', () => {
            validateValue('Blur');
            updateTotalScore(); // Update total score on blur
        });
    });

    // -------------------------------------------------------------------
    // CALCULATE SCORE AND AUTO-DETERMINE PASS/FAIL STATUS
    // -------------------------------------------------------------------
    function updatePassFailStatus() {
        // Get the weighted percentage from the weight summary total
        // Check if we have individual scoring to get the correct percentage
        const totalPercentDiv = document.getElementById('weight-total-percent');
        let percentage = 0;
        
        if (totalPercentDiv) {
            const individualPercentDiv = document.getElementById('weight-total-percent-individual');
            if (individualPercentDiv && individualPercentDiv.querySelectorAll('.badge').length > 0) {
                // Individual scoring - calculate average of all student percentages
                const percentBadges = individualPercentDiv.querySelectorAll('.badge');
                let totalPercentage = 0;
                let count = 0;
                percentBadges.forEach(badge => {
                    const text = badge.textContent.trim();
                    if (text && text !== '|') {
                        const value = parseFloat(text.replace('%', ''));
                        if (!isNaN(value)) {
                            totalPercentage += value;
                            count++;
                        }
                    }
                });
                percentage = count > 0 ? totalPercentage / count : 0;
            } else {
                // Group scoring or single total - get the percentage directly
                const percentText = totalPercentDiv.textContent.trim().replace('%', '');
                percentage = parseFloat(percentText) || 0;
            }
        }
        
        // Auto-determine pass/fail status for all pass/fail rubrics based on weighted percentage
        evaluationForm.querySelectorAll('.auto-passfail-result').forEach(input => {
            const threshold1 = parseFloat(input.dataset.threshold1) || 100;
            const threshold2 = parseFloat(input.dataset.threshold2) || 75;
            const threshold3 = parseFloat(input.dataset.threshold3) || 70;
            const levelCount = parseInt(input.dataset.levelCount) || 3;
            const rubricId = input.id.replace('passfail_result_', '');
            
            let selectedValue = 0; // Default to fail (0)
            let statusText = '';
            let statusClass = 'alert-danger';
            
            // Determine which pass level applies (check highest threshold first)
            if (percentage >= threshold1) {
                selectedValue = 1; // Highest pass level
                statusText = `<i class="bi bi-check-circle-fill text-success"></i> <strong>PASSED</strong> (Score: ${percentage.toFixed(2)}% ≥ ${threshold1}%)`;
                statusClass = 'alert-success';
            } else if (levelCount >= 2 && percentage >= threshold2) {
                selectedValue = 2; // Medium pass level
                statusText = `<i class="bi bi-check-circle text-warning"></i> <strong>PASSED with Minor Revisions</strong> (Score: ${percentage.toFixed(2)}% ≥ ${threshold2}%)`;
                statusClass = 'alert-warning';
            } else if (levelCount >= 3 && percentage >= threshold3) {
                selectedValue = 3; // Lower pass level
                statusText = `<i class="bi bi-check-circle text-info"></i> <strong>PASSED with Major Revisions</strong> (Score: ${percentage.toFixed(2)}% ≥ ${threshold3}%)`;
                statusClass = 'alert-info';
            } else {
                selectedValue = 0; // Fail
                const lowestThreshold = Math.min(threshold1, threshold2, threshold3);
                statusText = `<i class="bi bi-x-circle-fill text-danger"></i> <strong>FAILED</strong> (Score: ${percentage.toFixed(2)}% < ${lowestThreshold}%)`;
                statusClass = 'alert-danger';
            }
            
            // Update hidden input value
            input.value = selectedValue;
            
            // Update status display
            const statusDiv = document.getElementById('passfail_status_' + rubricId);
            if (statusDiv) {
                statusDiv.className = 'alert ' + statusClass;
                const statusSpan = statusDiv.querySelector('.passfail-status-text');
                if (statusSpan) {
                    statusSpan.innerHTML = statusText;
                }
            }
        });
    }
    
    function updateTotalScore() {
        // First, update the weight summary to get the correct weighted percentage
        updateWeightSummary();
        
        // Then update the pass/fail status based on the weighted percentage
        updatePassFailStatus();
    }
    
    // Initialize on page load
    updateTotalScore();

    // -------------------------------------------------------------------
    // Initialize Bootstrap Tooltips for score range indicators
    // -------------------------------------------------------------------
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // -------------------------------------------------------------------
    // FORM SUBMISSION LOGIC (Using Fetch API)
    // -------------------------------------------------------------------
    if (evaluationForm) {
        evaluationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            formStatusDiv.innerHTML = ''; // Clear previous status

            // --- Form Validation ---
            var isValid = true;
            var firstInvalidElement = null;

            // Remove previous invalid states
            evaluationForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            evaluationForm.querySelectorAll('input[required], select[required], textarea[required]').forEach(input => {
                var isRadio = input.type === 'radio';
                var isNumber = input.type === 'number';
                var isHidden = input.type === 'hidden';
                var groupName = input.name;
                var needsValidation = true;

                // Skip validation for auto-passfail hidden inputs (they're auto-populated)
                if (isHidden && input.classList.contains('auto-passfail-result')) {
                    return;
                }

                if (isRadio) {
                    // Check radio groups only once per group
                    if (groupName && evaluationForm.querySelector(`input[name="${groupName}"]`)._validated) {
                        needsValidation = false;
                    } else if (groupName) {
                        const groupRadios = evaluationForm.querySelectorAll(`input[name="${groupName}"]`);
                        if (!evaluationForm.querySelector(`input[name="${groupName}"]:checked`)) {
                            isValid = false;
                            groupRadios.forEach(radio => {
                                radio.classList.add('is-invalid');
                                if (!firstInvalidElement) firstInvalidElement = radio;
                            });
                            var cardHeader = input.closest('.rubric-card')?.querySelector('.card-header h5')?.textContent;
                            console.warn("Validation Error: No option selected for", cardHeader || groupName);
                        }
                        // Mark group as validated
                        groupRadios.forEach(radio => radio._validated = true);
                    }
                } else if (isNumber) {
                    // Check for empty string OR non-numeric value (after trying parseFloat)
                    if (input.value.trim() === '' || isNaN(parseFloat(input.value))) {
                        isValid = false;
                        input.classList.add('is-invalid');
                        if (!firstInvalidElement) firstInvalidElement = input;
                        var cardHeader = input.closest('.rubric-card')?.querySelector('.card-header h5')?.textContent;
                        var criterionText = input.closest('tr')?.querySelector('td:first-child')?.textContent;
                        console.warn("Validation Error: Invalid or empty score in", cardHeader || 'Unknown Rubric', "for criterion:", criterionText || input.name);
                    }
                } else {
                    // For other required inputs (like comments, though not currently marked required)
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                        if (!firstInvalidElement) firstInvalidElement = input;
                    }
                }
            });

            // Reset validation markers for next time
            evaluationForm.querySelectorAll('input[type="radio"]').forEach(radio => delete radio._validated);


            if (!isValid) {
                formStatusDiv.innerHTML = '<div class="alert alert-danger">Please fill in all required score fields or make a selection for each rubric.</div>';
                if (firstInvalidElement) {
                    // Scroll to the first invalid field
                    firstInvalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Try focusing, might not work on all elements (like hidden radios)
                    try { firstInvalidElement.focus(); } catch (err) {}
                }
                Swal.fire({
                    title: 'Incomplete Evaluation',
                    text: 'Please fill in all required score fields or make a selection for each rubric.',
                    icon: 'warning'
                });
                return; // Stop submission
            }

            // Show summary modal before submission
            showEvaluationSummary();
        });

        function submitEvaluationForm() {
            // --- End Form Validation ---


            formStatusDiv.innerHTML = '<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm me-2" role="status"><span class="visually-hidden">Loading...</span></div> Submitting...</div>';

            // --- Data Collection ---
            var evaluationData = {};
            document.querySelectorAll('.rubric-card').forEach(card => {
                var rubricId = card.dataset.rubricId;
                var rubricType = card.dataset.rubricType;
                var rubricResult = {};

                if (rubricType === 'numerical') {
                    var scores = {};
                    card.querySelectorAll('tbody tr[data-criterion-id]').forEach(row => {
                        var criterionId = row.dataset.criterionId;
                        var isIndividualCriterion = row.dataset.isIndividual == '1';

                        if (isIndividualCriterion) {
                            row.querySelectorAll('.criterion-score-input').forEach(input => {
                                var studentId = input.dataset.studentId;
                                var score = input.value;
                                if (studentId !== undefined && score.trim() !== '') {
                                    if (!scores[criterionId]) scores[criterionId] = {};
                                    scores[criterionId][studentId] = score;
                                }
                            });
                        } else {
                            var scoreInput = row.querySelector('.entered-score'); // Assuming one score input per group row
                            if (scoreInput && scoreInput.value.trim() !== '') {
                                scores[criterionId] = { 'group': scoreInput.value };
                            }
                        }
                    });
                    if (Object.keys(scores).length > 0) {
                        rubricResult = { scores: scores };
                    }
                } else if (rubricType === 'yesno') {
                    var options = {};
                    card.querySelectorAll('tbody tr[data-criterion-id]').forEach(row => {
                        var criterionId = row.dataset.criterionId;
                        var isIndividualCriterion = row.dataset.isIndividual == '1';

                        if (isIndividualCriterion) {
                            row.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                                var nameMatch = radio.name.match(/\[(\d+)\]$/); // Extract student ID
                                if (nameMatch && nameMatch[1]) {
                                    var studentId = nameMatch[1];
                                    if (!options[criterionId]) options[criterionId] = {};
                                    options[criterionId][studentId] = radio.value;
                                }
                            });
                        } else {
                            var groupInputName = `score[${rubricId}][${criterionId}][group]`;
                            var checkedRadio = evaluationForm.querySelector(`input[name="${groupInputName}"]:checked`);
                            if (checkedRadio) {
                                options[criterionId] = { 'group': checkedRadio.value };
                            }
                        }
                    });
                    if (Object.keys(options).length > 0) {
                        rubricResult = { options: options };
                    }
                } else if (rubricType === 'passfail') {
                    var selectedOption = evaluationForm.querySelector(`input[name="selected_option[${rubricId}]"]:checked`);
                    if (selectedOption) {
                        rubricResult = { selected_option: selectedOption.value };
                    } else {
                        console.warn("No option selected for Pass/Fail rubric:", rubricId); // Should be caught by validation
                    }
                }

                if (Object.keys(rubricResult).length > 0) {
                    evaluationData[rubricId] = rubricResult;
                }
            });

            var comments = document.getElementById('comments').value;

            // Prepare data payload as a JavaScript object
            var payload = {
                defense_schedule_id: evaluationForm.querySelector('input[name="defense_schedule_id"]').value,
                rubric_group_id: evaluationForm.querySelector('input[name="rubric_group_id"]').value,
                comments: comments,
                evaluation_data: JSON.stringify(evaluationData) // Send structured data as JSON string within the main payload
            };

            console.log("Submitting Data (Payload):", JSON.stringify(payload, null, 2));

            // --- Submit using Fetch API ---
            fetch('submit_evaluation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', // Indicate we're sending JSON
                    'Accept': 'application/json' // Indicate we expect JSON back
                },
                body: JSON.stringify(payload) // Send the whole payload as a JSON string
            })
            .then(response => {
                if (!response.ok) {
                    // Try to get text for more detailed error, otherwise use statusText
                    return response.text().then(text => {
                        throw new Error(`HTTP error ${response.status} (${response.statusText}): ${text || 'Server error'}`);
                    });
                }
                return response.json(); // Parse JSON body if response is OK
            })
            .then(result => {
                console.log('Submission response:', result); // Log the result

                // **** CORRECTED LOGIC ****
                // Check the 'status' field in the JSON response
                if (result.status === 'success') {
                    formStatusDiv.innerHTML = `<div class="alert alert-success">Evaluation submitted successfully! ${result.message || ''}</div>`;
                    Swal.fire({
                        title: 'Success!',
                        text: result.message || 'Your evaluation has been submitted.',
                        icon: 'success', // Use success icon
                        timer: 2500,
                        showConfirmButton: false
                    });
                    // Optionally disable form fields after successful submission
                    // evaluationForm.querySelectorAll('input, textarea, button').forEach(el => el.disabled = true);
                } else {
                    // Handle application-level errors reported by the backend (where status is not 'success')
                    throw new Error(result.message || 'An unknown error occurred during submission.');
                }
                // **** END CORRECTED LOGIC ****
            })
            .catch(error => {
                // Handle fetch errors, network errors, or errors thrown from .then() blocks
                console.error('Submission Error:', error);
                formStatusDiv.innerHTML = `<div class="alert alert-danger">Error submitting evaluation: ${error.message}</div>`;
                Swal.fire({
                    title: 'Error!',
                    text: `Could not submit evaluation: ${error.message}`,
                    icon: 'error' // Use error icon
                });
            });
        } // end submitEvaluationForm function
    } // end if(evaluationForm)

    // -------------------------------------------------------------------
    // INITIALIZE SUBTOTALS AND WEIGHT SUMMARY ON PAGE LOAD
    // -------------------------------------------------------------------
    // Initialize subtotal calculations for all rubrics on page load
    document.querySelectorAll('.rubric-card').forEach(card => {
        const rubricId = card.dataset.rubricId;
        const rubricType = card.dataset.rubricType;
        
        if (rubricType === 'numerical') {
            // Check if it's an individual scoring rubric
            const hasIndividualScores = card.querySelector('input[data-student-id]');
            
            if (hasIndividualScores) {
                // Calculate subtotals for each student
                const studentIds = new Set();
                card.querySelectorAll('input[data-student-id]').forEach(input => {
                    studentIds.add(input.dataset.studentId);
                });
                studentIds.forEach(studentId => {
                    updateIndividualTotalScore(rubricId, studentId);
                });
            } else {
                // Calculate group subtotal
                updateGroupTotalScore(rubricId);
            }
        }
    });
    
    // Initialize weight summary table
    if (typeof updateWeightSummary === 'function') {
        updateWeightSummary();
    }

}); // end DOMContentLoaded
</script>

<script type="module" src="../assets/js/mainModule.js"></script>
<script type="module" src="../assets/js/app.js"></script>

<?php
include '../assets/layouts/footer.php';
?>