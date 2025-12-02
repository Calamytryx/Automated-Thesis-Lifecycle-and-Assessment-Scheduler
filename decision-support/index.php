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
    // 1. Fetch Defense Schedule Info & Team ID
    $stmt_schedule = $pdo->prepare("
        SELECT ds.schedule_date, ds.start_time, ds.end_time, ds.room, ds.team_id, t.name as team_name, t.program as team_program, ds.defense_type
        FROM defense_schedules ds
        JOIN teams t ON ds.team_id = t.id
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
    error_log("DS-Index: Fetched schedule info for ID {$schedule_id}, Team ID {$team_id}, Defense Type: {$defense_type}");

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

        $stmt_levels = $pdo->prepare("
        SELECT rubric_id, id, level_index, name, description, points_min, points_max, is_range
            FROM rubric_levels
        WHERE rubric_id IN ($placeholders)
        ORDER BY rubric_id, level_index
        ");
        $stmt_levels->execute($rubric_ids);
        $levels_all = $stmt_levels->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

        $stmt_criteria = $pdo->prepare("
        SELECT rubric_id, id, criterion_text, criterion_detail, order_index, is_individual, min_score, max_score
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
        // --- MODIFICATION: Decode criterion_detail JSON ---
        $criterion_detail_json = $criterion['criterion_detail'];
        $criterion_details_array = json_decode($criterion_detail_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $criterion_details_array = []; // Fallback to empty array on error
            error_log("DS-Index: RENDER WARNING - Failed to decode criterion_detail JSON for criterion ID {$criterion_id}: " . json_last_error_msg() . " | JSON: " . $criterion_detail_json);
        }
        // --- END MODIFICATION ---
        $is_individual_criterion = $is_individual_rubric && !empty($criterion['is_individual']);

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
                $existing_score = $existing_details[$criterion_id][$student_id] ?? 0;

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

            // Determine overall min/max for the group input
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
                             oninput="updateGroupTotalScore('.$rubric_id.')"
                             data-rubric-id="'.$rubric_id.'"
                             data-criterion-id="'.$criterion_id.'"
                             required
                             >'; // Added required and form-control classes
            $html .= '</td>';
        }

        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table></div>';

    // Display total score area (can be updated by JS)
    $html .= '<div class="text-right mt-3">Group Score: <span id="r' . $rubric_id . 'group-score-total">0</span></div>'; // Keep this for JS updates

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
    $threshold_1 = $rubric['pass_threshold_1'] ?? 100; // Total Pass
    $threshold_2 = $rubric['pass_threshold_2'] ?? 75;  // Minor Revision Pass
    $threshold_3 = $rubric['pass_threshold_3'] ?? 65;  // Major Revision Pass

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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> <!-- Keep jQuery for now if other parts rely on it -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

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
    // FOR DISPLAY OF GROUP SCORE DATA (Numerical) - DISABLED DUE TO VISUAL CALCULATION ISSUES
    // Backend submission and validation continue to work correctly
    // -------------------------------------------------------------------
    window.updateGroupTotalScore = function(rubric_id) {
        // DISABLED: Visual total calculations showing incorrect values
        // Backend submission still works correctly with proper validation
        console.log("Visual total calculation disabled for rubric:", rubric_id);
        return;
        
        /* ORIGINAL CODE - DISABLED
        let total = 0;
        // Select only number inputs associated with the specific rubric's group score
        document.querySelectorAll(`#evaluationForm input[type="number"][data-rubric-id="${rubric_id}"][name*="[group]"]`).forEach(input => {
            let val = parseFloat(input.value);
            if (!isNaN(val)) {
                total += val;
            }
        });
        const totalSpan = document.getElementById(`r${rubric_id}group-score-total`);
        if (totalSpan) {
            totalSpan.textContent = total;
        }
        */
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
    // FOR DISPLAY OF INDIVIDUAL SCORE DATA (Numerical) - Optional, if needed
    // -------------------------------------------------------------------
    window.updateIndividualTotalScore = function(rubric_id) {
        // Placeholder: Add logic here if you need to calculate/display totals for individual scores
        console.log("Individual score updated for rubric:", rubric_id);
    }


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
    function updateTotalScore() {
        let totalScore = 0;
        let maxPossibleScore = 0;
        
        // Sum all numerical scores from entered-score inputs
        evaluationForm.querySelectorAll('.entered-score').forEach(input => {
            const value = parseFloat(input.value) || 0;
            const max = parseFloat(input.max) || 0;
            totalScore += value;
            maxPossibleScore += max;
        });
        
        // Calculate percentage from numerical rubrics only
        const percentage = maxPossibleScore > 0 ? (totalScore / maxPossibleScore) * 100 : 0;
        
        // Auto-determine pass/fail status for all pass/fail rubrics based on percentage
        evaluationForm.querySelectorAll('.auto-passfail-result').forEach(input => {
            const threshold1 = parseFloat(input.dataset.threshold1) || 100;
            const threshold2 = parseFloat(input.dataset.threshold2) || 75;
            const threshold3 = parseFloat(input.dataset.threshold3) || 65;
            const levelCount = parseInt(input.dataset.levelCount) || 3;
            const rubricId = input.id.replace('passfail_result_', '');
            
            let selectedValue = 0; // Default to fail (0)
            let statusText = '';
            let statusClass = 'alert-danger';
            
            // Determine which pass level applies (check highest threshold first)
            if (percentage >= threshold1) {
                selectedValue = 1; // Highest pass level
                statusText = `<i class="bi bi-check-circle-fill text-success"></i> <strong>PASSED</strong> (Score: ${percentage.toFixed(1)}% ≥ ${threshold1}%)`;
                statusClass = 'alert-success';
            } else if (levelCount >= 2 && percentage >= threshold2) {
                selectedValue = 2; // Medium pass level
                statusText = `<i class="bi bi-check-circle text-warning"></i> <strong>PASSED with Minor Revisions</strong> (Score: ${percentage.toFixed(1)}% ≥ ${threshold2}%)`;
                statusClass = 'alert-warning';
            } else if (levelCount >= 3 && percentage >= threshold3) {
                selectedValue = 3; // Lower pass level
                statusText = `<i class="bi bi-check-circle text-info"></i> <strong>PASSED with Major Revisions</strong> (Score: ${percentage.toFixed(1)}% ≥ ${threshold3}%)`;
                statusClass = 'alert-info';
            } else {
                selectedValue = 0; // Fail
                const lowestThreshold = Math.min(threshold1, threshold2, threshold3);
                statusText = `<i class="bi bi-x-circle-fill text-danger"></i> <strong>FAILED</strong> (Score: ${percentage.toFixed(1)}% < ${lowestThreshold}%)`;
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
            
            // Also update the weight contribution to total if needed
            const card = evaluationForm.querySelector(`.rubric-card[data-rubric-id="${rubricId}"]`);
            if (card) {
                const weight = parseFloat(card.dataset.weight) || 0;
                if (selectedValue > 0) {
                    totalScore += weight;
                }
                maxPossibleScore += weight;
            }
        });
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

            // Show confirmation dialog before submitting
            Swal.fire({
                title: 'Confirm Evaluation Submission',
                html: '<p>Are you sure you want to submit this evaluation?</p><p class="text-muted small">Once submitted, you can still update it later if needed.</p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Submit',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return; // User cancelled
                }
                // Proceed with submission
                submitEvaluationForm();
            });
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

}); // end DOMContentLoaded
</script>

<script type="module" src="../assets/js/mainModule.js"></script>
<script type="module" src="../assets/js/app.js"></script>

<?php
include '../assets/layouts/footer.php';
?>