<?php

session_start(); // Required for access control

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Increase PHP timeout for long-running scheduler
set_time_limit(600); // 10 minutes
error_log("=== SCHEDULER START === Execution time limit set to 600 seconds");
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION data: " . print_r($_SESSION, true));

// If the POST submission contains 'startTime', ignore this submission.
if (isset($_POST['startTime'])) {
    error_log("Ignoring submission with startTime. Expected submission without startTime.");
    echo json_encode(['success' => false, 'message' => 'Ignoring unintended submission']);
    exit;
}

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../assets/setup/db.inc.php';
    require_once __DIR__ . '/../includes/edit_functions.php';
    require_once __DIR__ . '/../includes/defense_type_functions.php'; // Add defense type helper

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection error');
    }

    // Progress tracking function
    function updateProgress($pdo, $progressId, $status, $message, $percentage = null) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO schedule_progress (id, status, message, percentage) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                message = VALUES(message), 
                percentage = VALUES(percentage),
                updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$progressId, $status, $message, $percentage]);
        } catch (Exception $e) {
            error_log("Progress update failed: " . $e->getMessage());
        }
    }

    /**
     * Get accessible sections for current user based on role
     * - Admin (id=0): All sections
     * - Program Chair (usertype=0, id!=0): All sections in their college
     * - Faculty (usertype=2): Only their assigned sections
     */
    function getAccessibleSections($pdo, $userId, $usertype) {
        try {
            // Admin (id=0): All sections
            if ($userId === 0 && $usertype === 0) {
                $stmt = $pdo->prepare("SELECT DISTINCT section FROM users WHERE section IS NOT NULL ORDER BY section");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Program Chair (usertype=0, id!=0): All sections in their college
            if ($usertype === 0 && $userId !== 0) {
                require_once __DIR__ . '/../../assets/includes/auth_functions.php';
                $userCollege = get_user_college($pdo, $userId);
                
                if (!$userCollege) {
                    return [];
                }

                $stmt = $pdo->prepare("
                    SELECT DISTINCT u.section FROM users u
                    LEFT JOIN programs p ON CONCAT(p.name, CASE WHEN p.specialization != '' THEN CONCAT(' - ', p.specialization) ELSE '' END) = u.program
                    WHERE u.section IS NOT NULL AND p.college = :college
                    ORDER BY u.section
                ");
                $stmt->execute([':college' => $userCollege]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Faculty (usertype=2): Only their assigned sections
            if ($usertype === 2) {
                require_once __DIR__ . '/../../dashboard/includes/section_access.php';
                return getProfessorSections($pdo, $userId);
            }

            return [];
        } catch (Exception $e) {
            error_log("Error getting accessible sections: " . $e->getMessage());
            return [];
        }
    }

    // Main execution
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Generate unique progress ID for tracking
        $progressId = uniqid('sched_', true);
        updateProgress($pdo, $progressId, 'running', 'Validating inputs...', 5);

        // Validate required inputs
        if (!validateInputs()) {
            updateProgress($pdo, $progressId, 'error', 'Please check all required fields are filled correctly', null);
            throw new Exception("Please check all required fields are filled correctly");
        }

        updateProgress($pdo, $progressId, 'running', 'Loading team data...', 10);

        updateProgress($pdo, $progressId, 'running', 'Loading team data...', 10);

        // Get the selected section(s) for filtering
        $selectedSections = [];
        if (isset($_POST['section']) && !empty($_POST['section'])) {
            if (is_array($_POST['section'])) {
                $selectedSections = array_filter($_POST['section'], function($s) { return !empty(trim($s)); });
            } else {
                $selectedSections = [trim($_POST['section'])];
            }
        } elseif (isset($_POST['selectedSection']) && !empty($_POST['selectedSection'])) {
            if (is_array($_POST['selectedSection'])) {
                $selectedSections = array_filter($_POST['selectedSection'], function($s) { return !empty(trim($s)); });
            } else {
                $selectedSections = [trim($_POST['selectedSection'])];
            }
        }

        // 🔐 APPLY ACCESS CONTROL: Get current user's accessible sections
        $currentUserId = $_SESSION['id'] ?? 0;
        $currentUsertype = $_SESSION['usertype'] ?? -1;
        $accessibleSections = getAccessibleSections($pdo, $currentUserId, $currentUsertype);

        // If user selected specific sections, validate they have access
        if (!empty($selectedSections)) {
            // Check if all selected sections are in accessible sections
            $invalidSections = array_diff($selectedSections, $accessibleSections);
            if (!empty($invalidSections)) {
                updateProgress($pdo, $progressId, 'error', 'You do not have access to one or more selected sections', null);
                echo json_encode([
                    'success' => false,
                    'message' => 'You do not have access to one or more selected sections'
                ]);
                exit;
            }
        } else {
            // If no section selected, use all accessible sections
            $selectedSections = $accessibleSections;
        }

        error_log("Scheduler Access Control: userId=$currentUserId, usertype=$currentUsertype, accessibleSections=" . implode(',', $accessibleSections) . ", selectedSections=" . implode(',', $selectedSections));

        updateProgress($pdo, $progressId, 'running', 'Checking for existing schedules...', 15);

        // Check for teams that already have schedules
        $scheduledTeams = checkExistingSchedules($pdo, $selectedSections);
        error_log("SCHEDULER: checkExistingSchedules returned " . count($scheduledTeams) . " teams");
        error_log("SCHEDULER: scheduledTeams data: " . json_encode($scheduledTeams));
        
        // Separate schedules into past and future
        $currentDate = date('Y-m-d');
        $pastSchedules = [];
        $futureSchedules = [];
        $upcomingDefenses = []; // Defenses that haven't started yet (pending status)
        $teamsNeedingGradeCheck = [];
        $teamsToAutoProgress = [];
        
        error_log("SCHEDULER: Current date for comparison: $currentDate");
        error_log("SCHEDULER: Starting to process " . count($scheduledTeams) . " scheduled teams");
        
        foreach ($scheduledTeams as $teamId => $schedule) {
            $scheduleDate = $schedule['defense_date'] ?? 'NO_DATE';
            $defenseStatus = $schedule['defense_status'] ?? 'pending';
            error_log("SCHEDULER: Processing Team $teamId - defense_date: $scheduleDate, defense_status: $defenseStatus");
            
            // Normalize schedule date to Y-m-d format
            if ($scheduleDate !== 'NO_DATE') {
                $parsedDate = parseDate($scheduleDate);
                if ($parsedDate) {
                    $scheduleDate = $parsedDate->format('Y-m-d');
                    error_log("SCHEDULER: Team $teamId normalized date: $scheduleDate");
                } else {
                    error_log("SCHEDULER: Team $teamId - FAILED to parse date: " . $schedule['defense_date']);
                }
            }
            
            if ($scheduleDate < $currentDate) {
                // Past schedule
                $pastSchedules[$teamId] = $schedule;
                error_log("SCHEDULER: Team $teamId marked as PAST schedule ($scheduleDate < $currentDate)");
                
                // Check if defense has been evaluated (passed/failed)
                if (in_array($defenseStatus, ['passed', 'failed'])) {
                    // Need to check if team has all grades before progression
                    $teamsNeedingGradeCheck[$teamId] = [
                        'old_schedule' => $schedule,
                        'status' => $defenseStatus
                    ];
                    error_log("SCHEDULER: Team $teamId needs grade check for progression (status: $defenseStatus)");
                } else {
                    error_log("SCHEDULER: Team $teamId has past schedule but status is '$defenseStatus' (not passed/failed) - skipping progression");
                }
            } else {
                // Future schedule
                $futureSchedules[$teamId] = $schedule;
                error_log("SCHEDULER: Team $teamId marked as FUTURE schedule ($scheduleDate >= $currentDate)");
                
                // Only ask for override if defense is still pending (hasn't started)
                if ($defenseStatus === 'pending') {
                    $upcomingDefenses[$teamId] = $schedule;
                    error_log("SCHEDULER: Team $teamId has UPCOMING defense (pending)");
                } else {
                    error_log("SCHEDULER: Team $teamId has future schedule but status is '$defenseStatus' (not pending) - will not ask for override");
                }
            }
        }
        
        error_log("SCHEDULER: Finished processing teams loop");
        
        error_log("SCHEDULER: Summary - Past: " . count($pastSchedules) . ", Future: " . count($futureSchedules) . ", Upcoming/Pending: " . count($upcomingDefenses) . ", Needs grade check: " . count($teamsNeedingGradeCheck));
        error_log("SCHEDULER: POST confirm_upgrade = " . (isset($_POST['confirm_upgrade']) ? $_POST['confirm_upgrade'] : 'NOT SET'));
        error_log("SCHEDULER: POST confirm_overwrite = " . (isset($_POST['confirm_overwrite']) ? $_POST['confirm_overwrite'] : 'NOT SET'));
        
        // Check if we need to ask for defense type upgrade confirmation
        if (!empty($teamsNeedingGradeCheck) && (!isset($_POST['confirm_upgrade']) || $_POST['confirm_upgrade'] !== 'true')) {
            error_log("SCHEDULER: Need to check grades for " . count($teamsNeedingGradeCheck) . " teams");
            
            // Verify all teams have complete grades
            $teamsReadyForUpgrade = [];
            $teamsMissingGrades = [];
            
            foreach ($teamsNeedingGradeCheck as $teamId => $data) {
                if (teamHasCompleteGrades($pdo, $teamId, $data['old_schedule']['id'])) {
                    $teamsReadyForUpgrade[$teamId] = $data;
                } else {
                    $teamsMissingGrades[$teamId] = $data;
                }
            }
            
            error_log("SCHEDULER: Teams ready for upgrade: " . count($teamsReadyForUpgrade) . ", Missing grades: " . count($teamsMissingGrades));
            
            if (!empty($teamsReadyForUpgrade)) {
                // Ask for confirmation to upgrade defense types
                updateProgress($pdo, $progressId, 'info', 'Defense type upgrades require confirmation', null);
                $upgradeInfo = [];
                foreach ($teamsReadyForUpgrade as $teamId => $data) {
                    $currentType = $data['old_schedule']['defense_type'] ?? 'title_proposal';
                    $status = $data['status'];
                    $newType = '';
                    if ($status === 'passed') {
                        switch ($currentType) {
                            case 'title_proposal': $newType = 'title_defense'; break;
                            case 'title_defense': $newType = 'final_defense'; break;
                            case 'final_defense': $newType = '(completed)'; break;
                        }
                    } else {
                        $newType = 're_defense';
                    }
                    
                    $teamNames = getTeamNames($pdo, [$teamId]);
                    $upgradeInfo[] = [
                        'team_id' => $teamId,
                        'team_name' => $teamNames[$teamId] ?? "Team $teamId",
                        'current_type' => ucwords(str_replace('_', ' ', $currentType)),
                        'new_type' => ucwords(str_replace('_', ' ', $newType)),
                        'status' => $status
                    ];
                }
                
                error_log("SCHEDULER: Asking for upgrade confirmation for " . count($upgradeInfo) . " teams");
                echo json_encode([
                    'success' => false,
                    'requireUpgradeConfirmation' => true,
                    'message' => 'Some teams have completed defenses and are ready for progression. Proceed with defense type upgrades?',
                    'upgradeInfo' => $upgradeInfo,
                    'missingGrades' => !empty($teamsMissingGrades) ? array_keys($teamsMissingGrades) : []
                ]);
                exit;
            }
            
            // If all teams missing grades, skip progression
            if (empty($teamsReadyForUpgrade)) {
                error_log("SCHEDULER: No teams ready for upgrade (all missing grades) - proceeding without upgrade");
                $teamsToAutoProgress = [];
            }
        } elseif (!empty($teamsNeedingGradeCheck) && isset($_POST['confirm_upgrade']) && $_POST['confirm_upgrade'] === 'true') {
            error_log("SCHEDULER: Upgrade confirmed, processing teams with complete grades");
            // Upgrade confirmed, filter teams with complete grades
            foreach ($teamsNeedingGradeCheck as $teamId => $data) {
                if (teamHasCompleteGrades($pdo, $teamId, $data['old_schedule']['id'])) {
                    $teamsToAutoProgress[$teamId] = $data;
                }
            }
        } else {
            error_log("SCHEDULER: No teams needing grade check or already processed");
        }
        
        // If there are upcoming defenses (pending) and overwrite confirmation is not received
        if (!empty($upcomingDefenses) && (!isset($_POST['confirm_overwrite']) || $_POST['confirm_overwrite'] !== 'true')) {
            error_log("SCHEDULER: Asking for overwrite confirmation for " . count($upcomingDefenses) . " upcoming defenses");
            updateProgress($pdo, $progressId, 'error', 'Confirmation required for overwriting upcoming defenses', null);
            $teamNames = getTeamNames($pdo, array_keys($upcomingDefenses));
            echo json_encode([
                'success' => false,
                'requireConfirmation' => true,
                'message' => 'The following teams have UPCOMING defenses that have not started yet. Do you want to overwrite them?',
                'scheduledTeams' => $teamNames
            ]);
            exit;
        } else {
            error_log("SCHEDULER: No upcoming defenses to confirm or confirmation received");
        }
        
        // Process automatic defense type progression for past schedules
        if (!empty($teamsToAutoProgress)) {
            updateProgress($pdo, $progressId, 'running', 'Processing defense progressions...', 18);
            $progressedTeams = handleDefenseProgression($pdo, $teamsToAutoProgress);
            error_log("SCHEDULER: Auto-progressed " . count($progressedTeams) . " teams based on past defense results");
        }
        
        // Remove ONLY upcoming/pending future schedules (past schedules and completed future schedules are kept)
        if (!empty($upcomingDefenses)) {
            updateProgress($pdo, $progressId, 'running', 'Removing upcoming schedules...', 20);
            removeExistingSchedules($pdo, array_keys($upcomingDefenses));
            error_log("SCHEDULER: Removed " . count($upcomingDefenses) . " upcoming schedules for re-scheduling");
        }

        // NOW fetch teams AFTER processing progressions and removing schedules
        updateProgress($pdo, $progressId, 'running', 'Loading teams and panelists...', 22);

        $teams = fetchTeams($pdo, $selectedSections);
        $panelists = fetchPanelists($pdo);
        
        // Filter out teams that should NOT be scheduled:
        // 1. Teams with future pending schedules that were NOT confirmed for overwrite
        // 2. Teams that have completed final_defense and passed
        $teamsWithFuturePending = [];
        if (!isset($_POST['confirm_overwrite']) || $_POST['confirm_overwrite'] !== 'true') {
            // Get teams that still have future pending schedules
            $currentDate = date('Y-m-d');
            $placeholders = implode(',', array_fill(0, count($selectedSections ?: ['ALL']), '?'));
            $sectionFilter = !empty($selectedSections) ? 
                "AND EXISTS (SELECT 1 FROM team_members tm2 JOIN users u ON tm2.user_id = u.id WHERE tm2.team_id = ds.team_id AND u.section IN ($placeholders))" : "";
            
            $checkStmt = $pdo->prepare("
                SELECT DISTINCT ds.team_id 
                FROM defense_schedules ds 
                WHERE ds.status = 'scheduled' 
                AND ds.schedule_date >= ?
                AND ds.defense_status = 'pending'
                $sectionFilter
            ");
            $params = [$currentDate];
            if (!empty($selectedSections)) {
                $params = array_merge($params, $selectedSections);
            }
            $checkStmt->execute($params);
            $teamsWithFuturePending = $checkStmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("SCHEDULER: Teams with future pending schedules (NOT overwriting): " . implode(', ', $teamsWithFuturePending));
        }
        
        // Filter teams to schedule
        $originalTeamCount = count($teams);
        $teams = array_filter($teams, function($team) use ($teamsWithFuturePending) {
            // Exclude teams with future pending schedules
            return !in_array($team['id'], $teamsWithFuturePending);
        });
        $teams = array_values($teams); // Re-index array
        
        error_log("SCHEDULER: After filtering - " . count($teams) . " teams to schedule (excluded " . ($originalTeamCount - count($teams)) . " with future pending schedules)");
        
        $duration = $_POST['timeDuration'];
        // Convert to a proper number
        if (!is_numeric($duration) || floatval($duration) <= 0) {
            updateProgress($pdo, $progressId, 'error', 'Invalid duration. It must be a positive number.', null);
            throw new Exception("Invalid duration. It must be a positive number.");
        }
        $duration = floatval($duration);
        // FIX: Set duration to a global variable so functions use it instead of $_POST
        $GLOBALS['timeDuration'] = $duration;

        $rooms = $_POST['rooms'];
        $timeSlots = $_POST['timeSlots'];
        $days = $_POST['days'];

        updateProgress($pdo, $progressId, 'running', 'Loading user schedules...', 25);

        $userSchedules = fetchUserSchedules($pdo);

        // Validate input parameters
        if (empty($teams) || empty($panelists)) {
            updateProgress($pdo, $progressId, 'error', 'No teams or panelists available for scheduling', null);
            throw new Exception("No teams or panelists available for scheduling");
        }

        error_log("SCHEDULER: About to generate schedules for " . count($teams) . " teams");

        updateProgress($pdo, $progressId, 'running', 'Starting genetic algorithm optimization...', 35);

        // Optimize parameters for better performance-quality balance
        // REDUCED for faster execution to prevent timeout
        $populationSize = 50;      // Reduced from 200 for faster execution
        $generations = 100;        // Reduced from 500 for faster execution  
        $mutationRate = 0.2;
        $earlyStopGenerations = 50; // Stop if no improvements after 50 generations
        
        error_log("Genetic Algorithm Parameters: Population=$populationSize, Generations=$generations, EarlyStop=$earlyStopGenerations");

        $bestSchedule = geneticAlgorithm(
            $pdo,
            $teams,
            $panelists,
            $rooms,
            $timeSlots,
            $days,
            $userSchedules,
            $populationSize,
            $generations,
            $mutationRate,
            $earlyStopGenerations,
            $progressId // Pass progress ID for tracking
        );
        
        error_log("Genetic Algorithm completed successfully");

        updateProgress($pdo, $progressId, 'running', 'Saving schedule to database...', 90);
        error_log("About to save schedule to database");

        if (saveScheduleToDatabase($pdo, $bestSchedule)) {
            updateProgress($pdo, $progressId, 'completed', 'Schedule generated and saved successfully!', 100);
            $result = [
                'success' => true,
                'progressId' => $progressId,
                'initialPopulationSize' => count(DefenseSchedule::$initialPopulation),
                'crossoverCount' => DefenseSchedule::$crossoverCount,
                'mutationCount' => DefenseSchedule::$mutationCount,
                'conflictCounts' => DefenseSchedule::$averageConflictCounts,
                'fitnessScores' => DefenseSchedule::$averageFitnessScores,
                'populationPerGeneration' => DefenseSchedule::$populationPerGeneration,
                'overwrittenTeams' => !empty($scheduledTeams) ? count($scheduledTeams) : 0,
                'message' => 'Schedule generated and saved successfully'
            ];

            // Save the response to a JSON file
            file_put_contents('schedule_data.json', json_encode($result));

            echo json_encode($result);
        } else {
            throw new Exception("Failed to save schedule to database");
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    error_log("ERROR IN SCHEDULER: " . $e->getMessage());
    error_log("ERROR LOCATION: " . $e->getFile() . " line " . $e->getLine());
    error_log("STACK TRACE: \n" . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// New function to validate all inputs
function validateInputs() {
    // Check for required fields
    $requiredFields = ['timeDuration', 'rooms', 'timeSlots', 'days'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            error_log("Missing required field: " . $field);
            return false;
        }
    }
    
    // Validate time duration is a positive number
    if (!is_numeric($_POST['timeDuration']) || floatval($_POST['timeDuration']) <= 0) {
        error_log("Invalid time duration: " . $_POST['timeDuration']);
        return false;
    }
    
    // Validate rooms array
    if (!is_array($_POST['rooms']) || empty($_POST['rooms'])) {
        error_log("Invalid rooms array");
        return false;
    }
    
    // Validate timeSlots array
    if (!is_array($_POST['timeSlots']) || empty($_POST['timeSlots'])) {
        error_log("Invalid timeSlots array");
        return false;
    }
    
    // Validate days array
    if (!is_array($_POST['days']) || empty($_POST['days'])) {
        error_log("Invalid days array");
        return false;
    }
    
    return true;
}

// Check existing schedules with detailed information (date, status, defense_type)
function checkExistingSchedules($pdo, $sections = []) {
    try {
        $query = "SELECT ds.*, t.name AS team_name,
                         ds.schedule_date AS defense_date,
                         ds.defense_status,
                         ds.defense_type,
                         ds.start_time AS time_slot,
                         ds.room
                  FROM defense_schedules ds
                  JOIN teams t ON ds.team_id = t.id
                  JOIN team_members tm ON t.id = tm.team_id
                  JOIN users u ON tm.user_id = u.id
                  WHERE ds.status = 'scheduled'";

        $params = [];
        if (!empty($sections)) {
            $placeholders = implode(',', array_fill(0, count($sections), '?'));
            $query .= " AND u.section IN ($placeholders)";
            $params = $sections;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        $scheduledTeams = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $scheduledTeams[$row['team_id']] = $row;
        }
        
        error_log("checkExistingSchedules: Found " . count($scheduledTeams) . " teams with existing schedules");
        if (!empty($scheduledTeams)) {
            foreach ($scheduledTeams as $teamId => $schedule) {
                error_log("  - Team $teamId: defense_date={$schedule['defense_date']}, defense_type={$schedule['defense_type']}, defense_status={$schedule['defense_status']}");
            }
        }
        return $scheduledTeams;
    } catch (PDOException $e) {
        error_log("checkExistingSchedules ERROR: " . $e->getMessage());
        return [];
    }
}

// Function to get team names from team IDs
function getTeamNames($pdo, $teamIds) {
    if (empty($teamIds)) return [];
    
    try {
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
        $stmt = $pdo->prepare("SELECT id, name FROM teams WHERE id IN ($placeholders)");
        $stmt->execute($teamIds);
        
        $teams = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $teams[$row['id']] = $row['name'];
        }
        return $teams;
    } catch (PDOException $e) {
        error_log("Error fetching team names: " . $e->getMessage());
        return [];
    }
}

// Function to remove existing FUTURE schedules for specific teams (preserves past schedules)
function removeExistingSchedules($pdo, $teamIds) {
    if (empty($teamIds)) {
        error_log("removeExistingSchedules: No team IDs provided");
        return;
    }
    
    try {
        $currentDate = date('Y-m-d');
        error_log("removeExistingSchedules: Attempting to remove FUTURE schedules (after $currentDate) for teams: " . implode(', ', $teamIds));
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
        
        // Only delete FUTURE schedules that are still pending
        // Never delete past schedules or schedules that are passed/failed
        $stmt = $pdo->prepare("
            DELETE FROM defense_schedules 
            WHERE team_id IN ($placeholders) 
            AND status = 'scheduled' 
            AND schedule_date >= ?
            AND defense_status = 'pending'
        ");
        $params = array_merge($teamIds, [$currentDate]);
        $stmt->execute($params);
        $deletedCount = $stmt->rowCount();
        error_log("removeExistingSchedules: Successfully removed $deletedCount FUTURE schedule(s) for " . count($teamIds) . " team(s)");
    } catch (PDOException $e) {
        error_log("removeExistingSchedules ERROR: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Check if team has complete grades from all panelists
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @param int $scheduleId Defense schedule ID
 * @return bool True if all panelists have submitted grades
 */
function teamHasCompleteGrades($pdo, $teamId, $scheduleId) {
    try {
        // Get the schedule with panelists
        $stmt = $pdo->prepare("
            SELECT panelist_id, panelist_id2, panelist_id3
            FROM defense_schedules
            WHERE id = ?
        ");
        $stmt->execute([$scheduleId]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$schedule) {
            error_log("teamHasCompleteGrades: Schedule $scheduleId not found");
            return false;
        }
        
        $panelists = [
            $schedule['panelist_id'],
            $schedule['panelist_id2'],
            $schedule['panelist_id3']
        ];
        
        // Check if all panelists have submitted evaluations
        foreach ($panelists as $panelistId) {
            if (empty($panelistId)) continue;
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM evaluations
                WHERE team_id = ? AND evaluator_id = ? AND defense_schedule_id = ?
            ");
            $stmt->execute([$teamId, $panelistId, $scheduleId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                error_log("teamHasCompleteGrades: Team $teamId missing evaluation from panelist $panelistId");
                return false;
            }
        }
        
        error_log("teamHasCompleteGrades: Team $teamId has all grades from all panelists");
        return true;
    } catch (PDOException $e) {
        error_log("teamHasCompleteGrades ERROR: " . $e->getMessage());
        return false;
    }
}

/**
 * Handle defense type progression for teams with past schedules
 * - If passed: Move defense type up one level
 *   title_proposal → title_defense → final_defense
 * - If failed: Set as re_defense
 */
function handleDefenseProgression($pdo, $teamsToProgress) {
    $progressedTeams = [];
    
    foreach ($teamsToProgress as $teamId => $data) {
        $oldSchedule = $data['old_schedule'];
        $status = $data['status'];
        $currentDefenseType = $oldSchedule['defense_type'] ?? 'title_proposal';
        
        // Determine new defense type based on status
        if ($status === 'passed') {
            // Progress to next defense level
            switch ($currentDefenseType) {
                case 'title_proposal':
                    $newDefenseType = 'title_defense';
                    break;
                case 'title_defense':
                    $newDefenseType = 'final_defense';
                    break;
                case 'final_defense':
                    // Already at final level, no progression needed
                    error_log("Team $teamId already completed final_defense, skipping progression");
                    continue 2;
                default:
                    $newDefenseType = 'title_defense';
            }
            error_log("Team $teamId passed $currentDefenseType, progressing to $newDefenseType");
        } else {
            // Failed: Create re_defense
            $newDefenseType = 're_defense';
            error_log("Team $teamId failed $currentDefenseType, scheduling re_defense");
        }
        
        // Store the new defense type in team metadata or mark team for re-scheduling
        // The actual schedule will be created by the genetic algorithm
        try {
            // Update team's next defense type (you may need to add this field to teams table)
            // For now, we'll track it in a way that the scheduler can pick it up
            $stmt = $pdo->prepare("
                UPDATE teams 
                SET next_defense_type = ? 
                WHERE id = ?
            ");
            $stmt->execute([$newDefenseType, $teamId]);
            
            $progressedTeams[] = [
                'team_id' => $teamId,
                'old_defense_type' => $currentDefenseType,
                'new_defense_type' => $newDefenseType,
                'status' => $status
            ];
        } catch (PDOException $e) {
            // If column doesn't exist, log and continue
            error_log("Could not update next_defense_type for team $teamId: " . $e->getMessage());
            error_log("Consider adding 'next_defense_type' column to teams table");
        }
    }
    
    return $progressedTeams;
}

// Update fetchTeams to handle multiple sections and respect next_defense_type
// Also includes locked panelists for panelist lock feature
function fetchTeams($pdo, $sections = [])
{
    if (empty($sections)) {
        $stmt = $pdo->query("
            SELECT DISTINCT t.id, tm.user_id as adviser_id, t.program, t.area_of_expertise,
                   COALESCE(t.next_defense_type, 'title_proposal') as defense_type,
                   t.locked_panelist1, t.locked_panelist2, t.locked_panelist3
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id AND tm.role = 'adviser'
            WHERE 1=1
        ");
    } else {
        $placeholders = implode(',', array_fill(0, count($sections), '?'));
        $stmt = $pdo->prepare("
            SELECT DISTINCT t.id, tm.user_id as adviser_id, t.program, t.area_of_expertise,
                   COALESCE(t.next_defense_type, 'title_proposal') as defense_type,
                   t.locked_panelist1, t.locked_panelist2, t.locked_panelist3
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id AND tm.role = 'adviser'
            WHERE EXISTS (
                SELECT 1 FROM team_members tm2
                JOIN users u ON tm2.user_id = u.id
                WHERE tm2.team_id = t.id AND u.section IN ($placeholders)
            )
        ");
        $stmt->execute($sections);
    }
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched " . count($teams) . " teams for sections: " . (!empty($sections) ? implode(", ", $sections) : 'All Sections'));
    return $teams;
}

function fetchPanelists($pdo)
{
    $stmt = $pdo->query("SELECT id, area_of_expertise, is_parttime FROM users WHERE usertype = 2");
    $panelists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert the result to a more usable format: id => [expertise, is_parttime]
    $formattedPanelists = [];
    foreach ($panelists as $panelist) {
        $formattedPanelists[$panelist['id']] = [
            'expertise' => $panelist['area_of_expertise'],
            'is_parttime' => isset($panelist['is_parttime']) ? $panelist['is_parttime'] : 0
        ];
    }

    return $formattedPanelists;
}

function fetchPanelistsByProgram($pdo, $teamProgram, $teamExpertise, $allPanelists)
{
    $sameProgramPanelists = [];
    $differentProgramPanelists = [];

    // Ensure teamExpertise is a string
    $teamExpertise = is_string($teamExpertise) ? $teamExpertise : '';

    foreach ($allPanelists as $panelistId => $panelistData) {
        $panelistInfo = getPanelistData($pdo, $panelistId);
        $expertise = is_array($panelistData) ? ($panelistData['expertise'] ?? '') : '';

        if ($panelistInfo['program'] == $teamProgram) {
            $sameProgramPanelists[] = ['id' => $panelistId, 'expertise' => $expertise];
        } else {
            $differentProgramPanelists[] = ['id' => $panelistId, 'expertise' => $expertise];
        }
    }

    // Sort panelists by expertise similarity
    usort($sameProgramPanelists, function ($a, $b) use ($teamExpertise) {
        $expertiseA = is_string($a['expertise']) ? $a['expertise'] : '';
        $expertiseB = is_string($b['expertise']) ? $b['expertise'] : '';
        return similar_text($teamExpertise, $expertiseB) - similar_text($teamExpertise, $expertiseA);
    });

    usort($differentProgramPanelists, function ($a, $b) use ($teamExpertise) {
        $expertiseA = is_string($a['expertise']) ? $a['expertise'] : '';
        $expertiseB = is_string($b['expertise']) ? $b['expertise'] : '';
        return similar_text($teamExpertise, $expertiseB) - similar_text($teamExpertise, $expertiseA);
    });

    return [
        'same' => array_column($sameProgramPanelists, 'id'),
        'different' => array_column($differentProgramPanelists, 'id')
    ];
}

function getPanelistData($pdo, $panelistId)
{
    static $cache = [];

    if (!isset($cache[$panelistId])) {
        $stmt = $pdo->prepare("SELECT program, area_of_expertise, is_parttime FROM users WHERE id = ?");
        $stmt->execute([$panelistId]);
        $cache[$panelistId] = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return $cache[$panelistId];
}

function fetchUserSchedules($pdo)
{
    $stmt = $pdo->query("SELECT user_id, day_of_week, start_time, end_time FROM user_schedules");
    $schedules = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $schedules[$row['user_id']][] = $row;
    }
    return $schedules;
}

function geneticAlgorithm(
    $pdo,
    $teams,
    $panelists,
    $rooms,
    $timeSlots,
    $days,
    $userSchedules,
    $populationSize,
    $generations,
    $mutationRate,
    $earlyStopGenerations = 30,
    $progressId = null
) {
    // Store teams globally for use in other functions
    $GLOBALS['teams'] = $teams;

    $population = createInitialPopulation($pdo, $populationSize, $teams, $panelists, $rooms, $timeSlots, $days);
    DefenseSchedule::$initialPopulation = $population;
    DefenseSchedule::$populationPerGeneration = [];
    DefenseSchedule::$conflictCounts = [];
    DefenseSchedule::$fitnessScores = [];
    DefenseSchedule::$averageConflictCounts = [];
    DefenseSchedule::$averageFitnessScores = [];

    $bestSchedule = null;
    $bestFitness = PHP_INT_MIN;
    $lastImproveGeneration = 0;
    $startTime = microtime(true);

    for ($i = 0; $i < $generations; $i++) {
        $generationImproved = false;

        // Update progress every 10 generations
        if ($progressId && $i % 10 == 0) {
            $percentage = 35 + (($i / $generations) * 50); // Progress from 35% to 85%
            updateProgress($pdo, $progressId, 'running', "Processing generation " . ($i + 1) . " of $generations", $percentage);
        }

        // Process in batches to avoid memory issues
        foreach ($population as $schedule) {
            $schedule->calculateFitness($userSchedules);
            if ($schedule->fitness > $bestFitness) {
                $bestFitness = $schedule->fitness;
                $bestSchedule = $schedule;
                $generationImproved = true;
                $lastImproveGeneration = $i;
            }
        }

        // Early stopping if no improvement for several generations
        if ($i - $lastImproveGeneration >= $earlyStopGenerations) {
            error_log("Early stopping at generation $i - no improvement for $earlyStopGenerations generations");
            break;
        }

        // Only store data every 5 generations to reduce memory usage
        if ($i % 5 == 0) {
            // Track population data for the current generation
            DefenseSchedule::$populationPerGeneration[] = array_map(function ($schedule) {
                return [
                    'fitness' => $schedule->fitness,
                    'chromosomes' => [] // Don't store full chromosomes to save memory
                ];
            }, $population);

            // Store metrics for this generation
            DefenseSchedule::$averageConflictCounts[] = array_sum(array_map(function ($schedule) {
                return $schedule->fitness < 0 ? 1 : 0;
            }, $population)) / $populationSize;

            DefenseSchedule::$averageFitnessScores[] = array_sum(array_column($population, 'fitness')) / $populationSize;
        }

        // Log progress every 20 generations
        if ($i % 20 == 0) {
            $elapsedTime = microtime(true) - $startTime;
            error_log("Generation $i completed. Best fitness: $bestFitness. Elapsed time: $elapsedTime seconds");
        }

        $selected = selection($population);

        // Create new population
        $newPopulation = $selected; // Keep selected individuals

        // Faster new population creation
        $childrenToCreate = $populationSize - count($newPopulation);
        for ($c = 0; $c < $childrenToCreate; $c++) {
            $parent1 = $selected[array_rand($selected)];
            $parent2 = $selected[array_rand($selected)];
            $child = crossover($parent1, $parent2, $userSchedules, $timeSlots, $days, $rooms, $panelists);

            // Only mutate some children to save time
            if (rand(0, 1) == 1) {
                mutation($child, $mutationRate, $panelists, $rooms, $timeSlots, $days, $userSchedules);
            }

            $newPopulation[] = $child;

            // Break early if we have enough children
            if (count($newPopulation) >= $populationSize) {
                break;
            }
        }

        // Only apply diversity preservation occasionally
        if ($i % 10 == 0) {
            $newPopulation = diversityPreservation($newPopulation, $populationSize, $pdo, $teams, $panelists, $rooms, $timeSlots, $days);
        }

        $population = $newPopulation;

        // Dynamic Mutation Rate Adjustment every few generations
        if ($i % 5 == 0) {
            $mutationRate = adjustMutationRate($mutationRate, $population);
        }
    }

    if ($bestSchedule === null) {
        usort($population, function ($a, $b) {
            return $b->fitness - $a->fitness;
        });
        $bestSchedule = $population[0];
    }

    $totalTime = microtime(true) - $startTime;
    error_log("Genetic algorithm completed in $totalTime seconds");

    return $bestSchedule;
}

function createInitialPopulation($pdo, $populationSize, $teams, $panelists, $rooms, $timeSlots, $days)
{
    $population = [];
    for ($i = 0; $i < $populationSize; $i++) {
        $population[] = new DefenseSchedule($pdo, $teams, $panelists, $rooms, $timeSlots, $days);
    }
    return $population;
}

function selection($population)
{
    usort($population, function ($a, $b) {
        return $b->fitness - $a->fitness;
    });
    return array_slice($population, 0, count($population) / 2);
}

function crossover($parent1, $parent2, $userSchedules, $timeSlots, $days, $rooms, $panelists)
{
    $child = new DefenseSchedule($parent1->pdo, [], [], $rooms, $timeSlots, $days);
    $crossoverPoint = rand(0, count($parent1->chromosomes) - 1);
    $child->chromosomes = array_merge(
        array_slice($parent1->chromosomes, 0, $crossoverPoint),
        array_slice($parent2->chromosomes, $crossoverPoint)
    );
    $child->all_defenses = $child->chromosomes;

    foreach ($child->chromosomes as &$defense) {
        $attempts = 0;
        $maxAttempts = 100;
        while (hasConflicts($parent1->pdo, $defense, $userSchedules, $child->all_defenses) && $attempts < $maxAttempts) {
            $defense['time_slot'] = $timeSlots[array_rand($timeSlots)];
            $defense['day'] = $days[array_rand($days)];
            $defense['room'] = $rooms[array_rand($rooms)];

            // Re-select panelists to resolve conflicts
            $team = fetchTeamById($parent1->pdo, $defense['team_id']);
            $panelistsByProgram = fetchPanelistsByProgram($parent1->pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $defense['panelist_ids'] = selectPanelists($panelistsByProgram, $panelists, $team['adviser_id']);

            $attempts++;
        }
        if ($attempts >= $maxAttempts) {
            $child->fitness -= 50;
        }
    }

    DefenseSchedule::$crossoverCount++;
    return $child;
}

function mutation($schedule, $mutationRate, $panelists, $rooms, $timeSlots, $days, $userSchedules)
{
    foreach ($schedule->chromosomes as $index => &$defense) {
        if (rand() / getrandmax() < $mutationRate) {
            $mutationType = rand(0, 3);
            $original = $defense;
            switch ($mutationType) {
                case 0:
                    $team = fetchTeamById($schedule->pdo, $defense['team_id']);
                    $panelistsByProgram = fetchPanelistsByProgram($schedule->pdo, $team['program'], $team['area_of_expertise'], $panelists);
                    $defense['panelist_ids'] = selectPanelists($panelistsByProgram, $panelists, $team['adviser_id']);
                    break;
                case 1:
                    $defense['room'] = $rooms[array_rand($rooms)];
                    break;
                case 2:
                    $defense['time_slot'] = $timeSlots[array_rand($timeSlots)];
                    break;
                case 3:
                    $defense['day'] = $days[array_rand($days)];
                    break;
            }
            if (hasConflicts($schedule->pdo, $defense, $userSchedules, $schedule->all_defenses)) {
                $defense = $original; // Revert if the mutation caused a conflict
            } else {
                $schedule->all_defenses[$index] = $defense; // Update all_defenses
                DefenseSchedule::$mutationCount++;
            }
        }
    }
}

function hasConflicts($pdo, $defense, $userSchedules, $all_defenses)
{
    static $conflictCache = [];

    // Create a unique key for this conflict check
    $key = md5(json_encode($defense) . json_encode(array_slice($all_defenses, 0, 5)));

    if (isset($conflictCache[$key])) {
        return $conflictCache[$key];
    }

    // Check panelist conflicts
    foreach ($defense['panelist_ids'] as $panelist_id) {
        if (hasScheduleConflict($pdo, $panelist_id, $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $all_defenses)) {
            $conflictCache[$key] = true;
            return true;
        }
    }

    // Cache team members to avoid repeated queries
    static $teamMembersCache = [];

    // Check conflicts with team members
    if (!isset($teamMembersCache[$defense['team_id']])) {
        $teamMembersCache[$defense['team_id']] = getTeamMembers($pdo, $defense['team_id'], 'array');
    }

    foreach ($teamMembersCache[$defense['team_id']] as $member) {
        if (hasScheduleConflict($pdo, $member['id'], $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $all_defenses)) {
            $conflictCache[$key] = true;
            return true;
        }
    }

    $conflictCache[$key] = false;
    return false;
}

function hasScheduleConflict($pdo, $user_id, $day, $time_slot, $userSchedules, $room, $all_defenses)
{
    if (!isset($userSchedules[$user_id])) return false;

    $defense_start = strtotime($time_slot);
    // === PROBLEMATIC LINE START ===
    // Previously: $duration = $_POST['timeDuration'];
    // FIX: Use global time duration variable instead.
    $duration = $GLOBALS['timeDuration'];
    // === PROBLEMATIC LINE END ===

    $defense_end = strtotime('+' . $duration . ' hour', $defense_start);
    $defense_day = date('w', strtotime($day));

    foreach ($userSchedules[$user_id] as $schedule) {
        if ($schedule['day_of_week'] == $defense_day) {
            $schedule_start = strtotime($schedule['start_time']);
            $schedule_end = strtotime($schedule['end_time']);

            if (($defense_start >= $schedule_start && $defense_start < $schedule_end) ||
                ($defense_end > $schedule_start && $defense_end <= $schedule_end) ||
                ($defense_start <= $schedule_start && $defense_end >= $schedule_end)
            ) {
                return true;
            }
        }
    }

    // Check for conflicts with other defenses
    if (is_array($all_defenses)) {
        foreach ($all_defenses as $existing_defense) {
            if ($existing_defense['day'] == $day && $existing_defense['room'] == $room) {
                $existing_start = strtotime($existing_defense['time_slot']);
                // === PROBLEMATIC LINE START ===
                // Previously: $duration = $_POST['timeDuration'];
                // FIX: Use global time duration variable.
                $duration = $GLOBALS['timeDuration'];
                // === PROBLEMATIC LINE END ===
                $existing_end = strtotime('+' . $duration . ' hour', $existing_start);

                if (($defense_start >= $existing_start && $defense_start < $existing_end) ||
                    ($defense_end > $existing_start && $defense_end <= $existing_end)
                ) {
                    return true;
                }
            }
        }
    }

    return false;
}

function saveScheduleToDatabase($pdo, $schedule)
{
    try {
        error_log("saveScheduleToDatabase: Starting transaction");
        $pdo->beginTransaction();

        // Get all teams that should be scheduled
        $expectedTeams = [];
        foreach ($GLOBALS['teams'] as $team) {
            $expectedTeams[] = $team['id'];
        }
        $expectedTeamCount = count($expectedTeams);
        error_log("saveScheduleToDatabase: Expecting to schedule $expectedTeamCount teams: " . implode(', ', $expectedTeams));

        // Track teams that have been scheduled to prevent duplicates
        $scheduledTeams = [];

        // Sort chromosomes by fitness score
        $defenses = $schedule->chromosomes;
        foreach ($defenses as &$defense) {
            $defense['fitness'] = calculateDefenseFitness($pdo, $defense);
        }

        usort($defenses, function ($a, $b) {
            return $b['fitness'] - $a['fitness']; // Best to worst (reversed)
        });

        $stmt = $pdo->prepare("
            INSERT INTO defense_schedules 
            (team_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, defense_type, status, approval_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Include notification functions
        require_once dirname(__DIR__) . '/../assets/includes/notification_functions.php';

        // First pass - schedule teams with best fitness
        foreach ($defenses as $defense) {
            // Skip if this team was already scheduled
            if (in_array($defense['team_id'], $scheduledTeams)) {
                continue;
            }

            $scheduledTeams[] = $defense['team_id'];

            $dateObj = parseDate($defense['day']);
            $date = $dateObj->format('Y-m-d');
            $startTime = new DateTime($defense['time_slot']);
            $endTime = clone $startTime;
            // === PROBLEMATIC LINE START ===
            // Previously: $duration = $_POST['timeDuration'];
            // FIX: Use global time duration variable.
            $duration = $GLOBALS['timeDuration'];
            // === PROBLEMATIC LINE END ===
            $endTime->modify('+' . $duration . ' hour');

            // Get defense type from defense array (set during initialization)
            $defenseType = $defense['defense_type'] ?? 'title_proposal';

            // Validate panelist_ids array has exactly 3 elements
            if (!isset($defense['panelist_ids']) || count($defense['panelist_ids']) < 3) {
                error_log("ERROR: Defense for team {$defense['team_id']} has invalid panelist_ids: " . json_encode($defense['panelist_ids'] ?? 'null'));
                continue; // Skip this defense
            }

            $stmt->execute([
                $defense['team_id'],
                $defense['panelist_ids'][0],
                $defense['panelist_ids'][1],
                $defense['panelist_ids'][2],
                $date,
                $startTime->format('H:i:s'),
                $endTime->format('H:i:s'),
                $defense['room'],
                $defenseType,      // defense_type
                'scheduled',       // status
                'pending_chair'    // approval_status - awaits chair review first
            ]);

            // CREATE DEFENSE SCHEDULE NOTIFICATIONS
            $scheduleId = $pdo->lastInsertId();
            
            error_log("DEFENSE SCHEDULER: About to create chair review notifications for schedule ID: $scheduleId, team: {$defense['team_id']}");
            
            // Step 1: Notify program chairs for review (panelists are NOT notified yet)
            $chairNotificationResult = createChairReviewNotifications($pdo, $scheduleId, $defense['team_id'],
                $date, $startTime->format('H:i'), $endTime->format('H:i'), $defense['room']);
            
            error_log("DEFENSE SCHEDULER: Chair notification creation result: " . ($chairNotificationResult ? 'SUCCESS' : 'FAILED'));

            // Track panelist assignments
            foreach ($defense['panelist_ids'] as $panelist_id) {
                global $lastAssignedPanelists;
                $lastAssignedPanelists[$defense['day']][] = $panelist_id;
            }
        }

        // Check if all teams were scheduled
        $missingTeams = array_diff($expectedTeams, $scheduledTeams);

        // Second pass - ensure all teams get scheduled
        if (!empty($missingTeams)) {
            error_log("Missing teams detected: " . implode(", ", $missingTeams));

            // For any missed teams, create a schedule forcefully
            foreach ($missingTeams as $missingTeamId) {
                // Find any solution for this team from chromosomes
                $teamDefense = null;
                foreach ($defenses as $defense) {
                    if ($defense['team_id'] == $missingTeamId) {
                        $teamDefense = $defense;
                        break;
                    }
                }

                // If no solution found in chromosomes, create a new one
                if (!$teamDefense) {
                    error_log("Creating fallback schedule for team ID: $missingTeamId");

                    $team = null;
                    foreach ($GLOBALS['teams'] as $filteredTeam) {
                        if ($filteredTeam['id'] == $missingTeamId) {
                            $team = $filteredTeam;
                            break;
                        }
                    }
                    if (!$team) {
                        error_log("Error: Filtered team data not found for ID: $missingTeamId");
                        continue;
                    }

                    $days = $_POST['days'];
                    $timeSlots = $_POST['timeSlots'];
                    $rooms = $_POST['rooms'];
                    $panelists = fetchPanelists($pdo);

                    $panelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
                    $selectedPanelists = selectPanelists($panelistsByProgram, $panelists, $team['adviser_id']);

                    $teamDefense = [
                        'team_id' => $missingTeamId,
                        'panelist_ids' => $selectedPanelists,
                        'room' => $rooms[array_rand($rooms)],
                        'time_slot' => $timeSlots[array_rand($timeSlots)],
                        'day' => $days[array_rand($days)]
                    ];
                }

                $dateObj = parseDate($teamDefense['day']);
                $date = $dateObj->format('Y-m-d');
                $startTime = new DateTime($teamDefense['time_slot']);
                $endTime = clone $startTime;
                // === PROBLEMATIC LINE START ===
                // Previously: $duration = $_POST['timeDuration'];
                // FIX: Use global time duration variable.
                $duration = $GLOBALS['timeDuration'];
                // === PROBLEMATIC LINE END ===
                $endTime->modify('+' . $duration . ' hour');

                // Get defense type for missing team
                $defenseType = $teamDefense['defense_type'] ?? 'title_proposal';

                // Validate panelist_ids array has exactly 3 elements
                if (!isset($teamDefense['panelist_ids']) || count($teamDefense['panelist_ids']) < 3) {
                    error_log("ERROR: Defense for missing team {$teamDefense['team_id']} has invalid panelist_ids: " . json_encode($teamDefense['panelist_ids'] ?? 'null'));
                    continue; // Skip this defense
                }

                $stmt->execute([
                    $teamDefense['team_id'],
                    $teamDefense['panelist_ids'][0],
                    $teamDefense['panelist_ids'][1],
                    $teamDefense['panelist_ids'][2],
                    $date,
                    $startTime->format('H:i:s'),
                    $endTime->format('H:i:s'),
                    $teamDefense['room'],
                    $defenseType,      // defense_type
                    'scheduled',       // status
                    'pending_chair'    // approval_status - awaits chair review first
                ]);

                // CREATE CHAIR REVIEW NOTIFICATIONS FOR MISSING TEAMS
                $scheduleId = $pdo->lastInsertId();
                createChairReviewNotifications($pdo, $scheduleId, $teamDefense['team_id'],
                    $date, $startTime->format('H:i'), $endTime->format('H:i'), $teamDefense['room']);

                $scheduledTeams[] = $missingTeamId;
            }
        }

        $scheduledCount = count($scheduledTeams);
        error_log("saveScheduleToDatabase: Scheduled $scheduledCount out of $expectedTeamCount expected teams");

        if ($scheduledCount < $expectedTeamCount) {
            error_log("saveScheduleToDatabase WARNING: Not all teams were scheduled! Missing " . ($expectedTeamCount - $scheduledCount) . " teams");
        }

        error_log("saveScheduleToDatabase: Committing transaction...");
        $pdo->commit();
        error_log("saveScheduleToDatabase: Transaction committed successfully. Total schedules created: $scheduledCount");
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("saveScheduleToDatabase ERROR: " . $e->getMessage());
        error_log("Error location: " . $e->getFile() . " on line " . $e->getLine());
        error_log("Stack trace: " . $e->getTraceAsString());
        error_log("Failed transaction details: " . json_encode([
            'teams_count' => count($GLOBALS['teams'] ?? []),
            'scheduled_teams' => count($scheduledTeams ?? []),
            'defense_count' => count($defenses ?? [])
        ]));
        throw new Exception("Failed to save schedule: " . $e->getMessage());
    }
}

// Helper function to parse a day string from either "Y-m-d" or "m-d-Y":
function parseDate($dayStr)
{
    $date = DateTime::createFromFormat('Y-m-d', $dayStr);
    if (!$date) {
        $date = DateTime::createFromFormat('m-d-Y', $dayStr);
    }
    return $date;
}

// Helper function to calculate fitness for a single defense
function calculateDefenseFitness($pdo, $defense)
{
    static $fitnessCache = [];

    // Create a cache key
    $key = $defense['team_id'] . '-' . implode(',', $defense['panelist_ids']);

    if (isset($fitnessCache[$key])) {
        return $fitnessCache[$key];
    }

    $fitness = 0;

    // Calculate expertise matching
    $teamExpertise = getTeamExpertise($pdo, $defense['team_id']) ?? '';
    $expertiseMatchFound = false;

    // Ensure we have strings
    $teamExpertise = is_string($teamExpertise) ? $teamExpertise : '';

    foreach ($defense['panelist_ids'] as $panelist_id) {
        $panelistExpertise = getPanelistExpertise($pdo, $panelist_id) ?? '';
        $panelistExpertise = is_string($panelistExpertise) ? $panelistExpertise : '';

        $similarity = 0;
        if (!empty($teamExpertise) && !empty($panelistExpertise)) {
            $similarity = similar_text($teamExpertise, $panelistExpertise) /
                max(strlen($teamExpertise), strlen($panelistExpertise)) * 100;
        }

        if ($similarity > 70) {
            $expertiseMatchFound = true;
            $fitness += 50; // Reward for expertise match
        }
        $fitness += $similarity; // Add similarity score
    }

    if (!$expertiseMatchFound) {
        $fitness -= 100; // Heavy penalty for no expertise match
    }

    // NEW: Add specialization matching to fitness
    $teamSpecializations = getTeamSpecializations($pdo, $defense['team_id']);
    if (!empty($teamSpecializations)) {
        foreach ($defense['panelist_ids'] as $panelist_id) {
            $panelistSpecializations = getUserSpecializations($pdo, $panelist_id);
            $specializationScore = calculateSpecializationMatch($teamSpecializations, $panelistSpecializations);
            $fitness += $specializationScore; // Add specialization matching bonus
        }
    }

    $fitnessCache[$key] = $fitness;
    return $fitness;
}

class DefenseSchedule
{
    public $pdo;  // Change this to public
    public $chromosomes = [];
    public $fitness = 0;
    public static $initialPopulation = [];
    public static $crossoverCount = 0;
    public static $mutationCount = 0;
    public static $conflictCounts = [];
    public static $fitnessScores = [];
    public static $averageConflictCounts = [];
    public static $averageFitnessScores = [];
    public static $populationPerGeneration = [];
    public $all_defenses = [];

    public function __construct($pdo, $teams, $panelists, $rooms, $timeSlots, $days)
    {
        $this->pdo = $pdo;
        foreach ($teams as $team) {
            $panelistsByProgram = fetchPanelistsByProgram($pdo, $team['program'], $team['area_of_expertise'], $panelists);
            $selectedPanelists = selectPanelists($panelistsByProgram, $panelists, $team['adviser_id']);

            // Use defense_type from team data (set by progression logic)
            $defenseType = $team['defense_type'] ?? 'title_proposal';

            $defense = [
                'team_id' => $team['id'],
                'panelist_ids' => $selectedPanelists,
                'room' => $rooms[array_rand($rooms)],
                'time_slot' => $timeSlots[array_rand($timeSlots)],
                'day' => $days[array_rand($days)],
                'defense_type' => $defenseType
            ];
            $this->chromosomes[] = $defense;
            $this->all_defenses[] = $defense;
        }
    }

    public function calculateFitness($userSchedules)
    {
        // Cache for performance
        static $teamMembersCache = [];
        static $panelistDataCache = [];

        $this->fitness = 0;
        $conflicts = 0;
        $panelistDailyAssignments = [];

        // Initialize tracking structure for panelist assignments
        foreach ($this->chromosomes as $defense) {
            foreach ($defense['panelist_ids'] as $panelist_id) {
                if (!isset($panelistDailyAssignments[$panelist_id])) {
                    $panelistDailyAssignments[$panelist_id] = [];
                }
                if (!isset($panelistDailyAssignments[$panelist_id][$defense['day']])) {
                    $panelistDailyAssignments[$panelist_id][$defense['day']] = 1;
                } else {
                    $panelistDailyAssignments[$panelist_id][$defense['day']]++;
                }
            }
        }

        foreach ($this->chromosomes as $defenseKey => $defense) {
            // Use cached team members data
            if (!isset($teamMembersCache[$defense['team_id']])) {
                $teamMembersCache[$defense['team_id']] = getTeamMembers($this->pdo, $defense['team_id'], 'array');
            }
            $teamMembers = $teamMembersCache[$defense['team_id']];

            // Team member conflict check
            if (is_array($teamMembers)) {
                foreach ($teamMembers as $member) {
                    if (hasScheduleConflict($this->pdo, $member['id'], $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $this->all_defenses)) {
                        $this->fitness -= 5;
                        $conflicts++;
                    }
                }
            } else {
                $this->fitness -= 10;
                $conflicts++;
            }

            // Check panelist assignments and expertise match
            $teamExpertise = getTeamExpertise($this->pdo, $defense['team_id']) ?? '';
            $teamExpertise = is_string($teamExpertise) ? $teamExpertise : '';
            $expertiseMatchFound = false;

            foreach ($defense['panelist_ids'] as $panelist_id) {
                // Cache panelist data
                if (!isset($panelistDataCache[$panelist_id])) {
                    $panelistDataCache[$panelist_id] = getPanelistData($this->pdo, $panelist_id);
                }
                $panelistData = $panelistDataCache[$panelist_id];

                $isPartTime = isset($panelistData['is_parttime']) ? $panelistData['is_parttime'] : 0;
                $maxAllowed = $isPartTime ? 1 : 3; // 1 for part-time, 3 for full-time

                if (isset($panelistDailyAssignments[$panelist_id][$defense['day']])) {
                    $dailyAssignments = $panelistDailyAssignments[$panelist_id][$defense['day']];
                    if ($dailyAssignments > $maxAllowed) {
                        $this->fitness -= 30;
                        $conflicts++;
                    }
                }

                // Expertise matching check (using cached data)
                $panelistExpertise = $panelistData['area_of_expertise'] ?? '';
                $panelistExpertise = is_string($panelistExpertise) ? $panelistExpertise : '';

                $similarity = 0;
                if (!empty($teamExpertise) && !empty($panelistExpertise)) {
                    $similarity = similar_text($teamExpertise, $panelistExpertise) /
                        max(strlen($teamExpertise), strlen($panelistExpertise)) * 100;
                }

                if ($similarity > 70) {
                    $expertiseMatchFound = true;
                    $this->fitness += 20;
                }

                // Other checks
                if (hasConsecutiveAssignment($panelist_id, $defense['day'], $defense['time_slot'])) {
                    $this->fitness -= 5;
                    $conflicts++;
                }

                if (hasScheduleConflict($this->pdo, $panelist_id, $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $this->all_defenses)) {
                    $this->fitness -= 5;
                    $conflicts++;
                }
            }

            if (!$expertiseMatchFound) {
                $this->fitness -= 25;
                $conflicts++;
            }

            // Room conflict check - optimized to stop after first conflict
            foreach ($this->chromosomes as $otherKey => $otherDefense) {
                if ($defenseKey != $otherKey && $defense['room'] == $otherDefense['room'] && $defense['day'] == $otherDefense['day']) {
                    $duration = intval($GLOBALS['timeDuration']);
                    $defenseStart = strtotime($defense['time_slot']);
                    $defenseEnd = strtotime('+' . $duration . ' hours', $defenseStart);

                    $otherStart = strtotime($otherDefense['time_slot']);
                    $otherEnd = strtotime('+' . $duration . ' hours', $otherStart);

                    if (($defenseStart < $otherEnd) && ($defenseEnd > $otherStart)) {
                        $this->fitness -= 10;
                        $conflicts++;
                        break; // Stop after first conflict found
                    }
                }
            }
        }

        self::$conflictCounts[] = $conflicts;
        self::$fitnessScores[] = $this->fitness;
    }
}

// Add a function to check for consecutive assignments
function hasConsecutiveAssignment($panelist_id, $day, $time_slot)
{
    // Implement logic to check if the panelist was assigned in the immediately previous schedule
    // This may require tracking the order of schedules and panelist assignments
    // For simplicity, assume a global or session-based tracking mechanism
    global $lastAssignedPanelists;
    if (isset($lastAssignedPanelists[$day])) {
        return in_array($panelist_id, $lastAssignedPanelists[$day]);
    }
    return false;
}

function getTeamMembers($pdo, $team_id, $format = 'array')
{
    static $cache = [];

    if (!isset($cache[$team_id])) {
        $stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, tm.role FROM team_members tm JOIN users u ON tm.user_id = u.id WHERE tm.team_id = ? ORDER BY FIELD(tm.role, 'adviser', 'leader', 'member')");
        $stmt->execute([$team_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cache[$team_id] = array_map(function ($member) {
            return [
                'id' => $member['id'],
                'name' => $member['first_name'] . ' ' . $member['last_name'],
                'role' => $member['role']
            ];
        }, $members);
    }

    if ($format === 'array') {
        return $cache[$team_id];
    } else {
        $output = '';
        foreach ($cache[$team_id] as $member) {
            $output .= htmlspecialchars($member['name']) . ' (' . ucfirst($member['role']) . ')<br>';
        }
        return $output;
    }
}

// Function to fetch team by ID
function fetchTeamById($pdo, $team_id)
{
    $stmt = $pdo->prepare("
        SELECT t.id, tm.user_id as adviser_id, t.program, t.area_of_expertise
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        WHERE t.id = ? AND tm.role = 'adviser'
    ");
    $stmt->execute([$team_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get panelist expertise
function getPanelistExpertise($pdo, $panelist_id)
{
    static $cache = [];

    if (!isset($cache[$panelist_id])) {
        $stmt = $pdo->prepare("SELECT area_of_expertise FROM users WHERE id = ?");
        $stmt->execute([$panelist_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache[$panelist_id] = $result ? $result['area_of_expertise'] : '';
    }

    return $cache[$panelist_id];
}

// Function to get team expertise
function getTeamExpertise($pdo, $team_id)
{
    static $cache = [];

    if (!isset($cache[$team_id])) {
        $stmt = $pdo->prepare("SELECT area_of_expertise FROM teams WHERE id = ?");
        $stmt->execute([$team_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache[$team_id] = $result ? $result['area_of_expertise'] : '';
    }

    return $cache[$team_id];
}

// Diversity Preservation function
function diversityPreservation($population, $populationSize, $pdo, $teams, $panelists, $rooms, $timeSlots, $days)
{
    usort($population, function ($a, $b) {
        return $b->fitness - $a->fitness;
    });

    $elites = array_slice($population, 0, $populationSize / 4);
    $newPopulation = $elites;

    while (count($newPopulation) < $populationSize) {
        $newSchedule = new DefenseSchedule($pdo, $teams, $panelists, $rooms, $timeSlots, $days);
        $newPopulation[] = $newSchedule;
    }

    return $newPopulation;
}

// Adjust Mutation Rate function
function adjustMutationRate($mutationRate, $population)
{
    $avgFitness = array_sum(array_column($population, 'fitness')) / count($population);
    $bestFitness = max(array_column($population, 'fitness'));

    if ($bestFitness == $avgFitness) {
        // Increase mutation rate if the population is converging
        $mutationRate *= 1.1;
    } else {
        // Otherwise, slightly decrease it
        $mutationRate *= 0.9;
    }

    // Keep mutation rate within reasonable bounds
    $mutationRate = max(0.05, min(0.5, $mutationRate));

    return $mutationRate;
}

function getDepartment($program) {
    $program = (string)$program; // Force string type
    if (stripos($program, 'Architecture') !== false) {
        return "Architecture";
    } elseif (stripos($program, 'Engineering') !== false) {
        return "Engineering";
    }
    return "Computer Studies";
}

/**
 * Get specializations for a team (from area_of_expertise field)
 */
function getTeamSpecializations($pdo, $teamId) {
    static $cache = [];
    
    if (!isset($cache[$teamId])) {
        $stmt = $pdo->prepare("
            SELECT area_of_expertise 
            FROM teams
            WHERE id = ?
        ");
        $stmt->execute([$teamId]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($team && !empty($team['area_of_expertise'])) {
            $cache[$teamId] = array_filter(array_map('trim', explode(',', $team['area_of_expertise'])));
        } else {
            $cache[$teamId] = [];
        }
    }
    
    return $cache[$teamId];
}

/**
 * Get specializations for a user/panelist (from area_of_expertise field)
 */
function getUserSpecializations($pdo, $userId) {
    static $cache = [];
    
    if (!isset($cache[$userId])) {
        $stmt = $pdo->prepare("
            SELECT area_of_expertise 
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && !empty($user['area_of_expertise'])) {
            $cache[$userId] = array_filter(array_map('trim', explode(',', $user['area_of_expertise'])));
        } else {
            $cache[$userId] = [];
        }
    }
    
    return $cache[$userId];
}

/**
 * Calculate specialization match score between team and panelist
 */
function calculateSpecializationMatch($teamSpecializations, $panelistSpecializations) {
    if (empty($teamSpecializations) || empty($panelistSpecializations)) {
        return 0; // No bonus if either has no specializations
    }
    
    $matchCount = count(array_intersect($teamSpecializations, $panelistSpecializations));
    return $matchCount * 10; // 10 points per matching specialization
}

function selectPanelists($panelistsByProgram, $allPanelists, $adviserId)
{
    global $pdo; // needed to call getPanelistData()
    $selectedPanelists = [];
    $teamData = null;
    // Find the team based on adviser
    foreach ($GLOBALS['teams'] as $team) {
        if ($team['adviser_id'] == $adviserId) {
            $teamData = $team;
            break;
        }
    }
    if (!$teamData) {
        // Fallback: randomly pick 3 panelists excluding the adviser
        $remaining = array_diff(array_keys($allPanelists), [$adviserId]);
        return array_slice($remaining, 0, 3);
    }
    
    // CHECK FOR LOCKED PANELISTS FIRST
    // If team has locked panelists, use them instead of auto-assigning
    $lockedPanelists = [];
    if (!empty($teamData['locked_panelist1'])) {
        $lockedPanelists[] = (int)$teamData['locked_panelist1'];
    }
    if (!empty($teamData['locked_panelist2'])) {
        $lockedPanelists[] = (int)$teamData['locked_panelist2'];
    }
    if (!empty($teamData['locked_panelist3'])) {
        $lockedPanelists[] = (int)$teamData['locked_panelist3'];
    }
    
    // If all 3 panelists are locked, return them directly
    if (count($lockedPanelists) >= 3) {
        error_log("Team {$teamData['id']}: Using all 3 locked panelists: " . implode(', ', $lockedPanelists));
        return array_slice($lockedPanelists, 0, 3);
    }
    
    // If some panelists are locked, start with them
    if (!empty($lockedPanelists)) {
        $selectedPanelists = $lockedPanelists;
        error_log("Team {$teamData['id']}: Starting with " . count($lockedPanelists) . " locked panelists: " . implode(', ', $lockedPanelists));
    }
    
    $teamProgram = (string)$teamData['program'];
    $teamDepartment = getDepartment($teamProgram);
    $teamSpecializations = getTeamSpecializations($pdo, $teamData['id']);

    // Score all panelists based on multiple criteria
    $panelistScores = [];
    foreach (array_keys($allPanelists) as $id) {
        if ($id == $adviserId) continue;
        
        $pdata = getPanelistData($pdo, $id);
        $score = 0;
        
        // Same program (highest priority)
        if (strcasecmp((string)($pdata['program'] ?? ''), $teamProgram) === 0) {
            $score += 100;
        }
        
        // Same department
        if (strcasecmp(getDepartment($pdata['program'] ?? ''), $teamDepartment) === 0) {
            $score += 50;
        }
        
        // Specialization matching (new!)
        $panelistSpecializations = getUserSpecializations($pdo, $id);
        $specializationScore = calculateSpecializationMatch($teamSpecializations, $panelistSpecializations);
        $score += $specializationScore;
        
        $panelistScores[$id] = $score;
    }
    
    // Sort panelists by score (descending)
    arsort($panelistScores);
    
    // Select top 3 panelists with some randomization for diversity
    $topCandidates = array_keys($panelistScores);
    
    // Candidate 0: Best match (top scorer or random from top 3)
    $top3 = array_slice($topCandidates, 0, min(3, count($topCandidates)));
    if (!empty($top3)) {
        $selectedPanelists[] = $top3[array_rand($top3)];
    }
    
    // Candidate 1: From same department (prefer not already selected)
    $sameDept = array_filter($topCandidates, function($id) use ($pdo, $teamDepartment, $selectedPanelists) {
        if (in_array($id, $selectedPanelists)) return false;
        $pdata = getPanelistData($pdo, $id);
        if (!$pdata) return false;
        return strcasecmp(getDepartment($pdata['program'] ?? ''), $teamDepartment) === 0;
    });
    
    if (!empty($sameDept)) {
        $sameDeptValues = array_values($sameDept);
        $selectedPanelists[] = $sameDeptValues[array_rand($sameDeptValues)];
    } else {
        $remaining = array_diff($topCandidates, $selectedPanelists);
        if (!empty($remaining)) {
            $remainingValues = array_values($remaining);
            $selectedPanelists[] = $remainingValues[0];
        }
    }
    
    // Candidate 2: From different department for diversity
    $diffDept = array_filter($topCandidates, function($id) use ($pdo, $teamDepartment, $selectedPanelists) {
        if (in_array($id, $selectedPanelists)) return false;
        $pdata = getPanelistData($pdo, $id);
        if (!$pdata) return false;
        return strcasecmp(getDepartment($pdata['program'] ?? ''), $teamDepartment) !== 0;
    });
    
    if (!empty($diffDept)) {
        $diffDeptValues = array_values($diffDept);
        $selectedPanelists[] = $diffDeptValues[array_rand($diffDeptValues)];
    } else {
        $remaining = array_diff($topCandidates, $selectedPanelists);
        if (!empty($remaining)) {
            $remainingValues = array_values($remaining);
            $selectedPanelists[] = $remainingValues[0];
        }
    }
    
    // Ensure we have exactly 3 panelists
    while (count($selectedPanelists) < 3 && count($selectedPanelists) < count($topCandidates)) {
        $remaining = array_diff($topCandidates, $selectedPanelists);
        if (!empty($remaining)) {
            $remainingValues = array_values($remaining);
            $selectedPanelists[] = $remainingValues[0];
        } else {
            break;
        }
    }

    return $selectedPanelists;
}

// Unused functions are kept at the end
function isTimeSlotAvailable($schedule, $day, $timeSlot, $duration, $room)
{
    foreach ($schedule->chromosomes as $defense) {
        if ($defense['day'] == $day && $defense['room'] == $room) {
            // Calculate start and end times for the existing defense
            $existingStart = strtotime($defense['time_slot']);
            $existingEnd = strtotime('+' . $duration . ' hour', $existingStart);

            // Calculate start and end times for the new defense
            $newStart = strtotime($timeSlot);
            $newEnd = strtotime('+' . $duration . ' hour', $newStart);

            // Check if the time slots overlap
            if (($newStart < $existingEnd) && ($newEnd > $existingStart)) {
                return false;
            }
        }
    }
    return true;
}

function getAvailableTimeSlot($schedule, $days, $timeSlots, $duration, $rooms)
{
    $availableSlots = [];
    foreach ($days as $day) {
        foreach ($timeSlots as $timeSlot) {
            foreach ($rooms as $room) {
                if (isTimeSlotAvailable($schedule, $day, $timeSlot, $duration, $room)) {
                    $availableSlots[] = ['day' => $day, 'time_slot' => $timeSlot, 'room' => $room];
                }
            }
        }
    }
    return $availableSlots ? $availableSlots[array_rand($availableSlots)] : null;
}
