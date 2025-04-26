<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_log("POST data: " . print_r($_POST, true));

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

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection error');
    }

    // Main execution
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate required inputs
        if (!validateInputs()) {
            throw new Exception("Please check all required fields are filled correctly");
        }

        // Get the selected program(s) for filtering - now supports multiple programs
        $selectedPrograms = [];
        if (isset($_POST['program']) && !empty($_POST['program'])) {
            if (is_array($_POST['program'])) {
                $selectedPrograms = array_filter($_POST['program'], function($p) { return !empty(trim($p)); });
            } else {
                $selectedPrograms = [trim($_POST['program'])];
            }
        } elseif (isset($_POST['selectedProgram']) && !empty($_POST['selectedProgram'])) {
            if (is_array($_POST['selectedProgram'])) {
                $selectedPrograms = array_filter($_POST['selectedProgram'], function($p) { return !empty(trim($p)); });
            } else {
                $selectedPrograms = [trim($_POST['selectedProgram'])];
            }
        }

        // Check for teams that already have schedules
        $scheduledTeams = checkExistingSchedules($pdo, $selectedPrograms);
        
        // If there are scheduled teams and overwrite confirmation is not received
        if (!empty($scheduledTeams) && (!isset($_POST['confirm_overwrite']) || $_POST['confirm_overwrite'] !== 'true')) {
            $teamNames = getTeamNames($pdo, array_keys($scheduledTeams));
            // FIX: Don't use return, actually echo the JSON response and exit
            echo json_encode([
                'success' => false,
                'requireConfirmation' => true,
                'message' => 'The following teams already have schedules and will be overwritten:',
                'scheduledTeams' => $teamNames
            ]);
            exit; // Make sure we exit after sending the response
        }

        $teams = fetchTeams($pdo, $selectedPrograms);
        $panelists = fetchPanelists($pdo);
        $duration = $_POST['timeDuration'];
        // Convert to a proper number
        if (!is_numeric($duration) || floatval($duration) <= 0) {
            throw new Exception("Invalid duration. It must be a positive number.");
        }
        $duration = floatval($duration);
        // FIX: Set duration to a global variable so functions use it instead of $_POST
        $GLOBALS['timeDuration'] = $duration;

        $rooms = $_POST['rooms'];
        $timeSlots = $_POST['timeSlots'];
        $days = $_POST['days'];

        $userSchedules = fetchUserSchedules($pdo);

        // Validate input parameters
        if (empty($teams) || empty($panelists)) {
            throw new Exception("No teams or panelists available for scheduling");
        }

        // If confirmed, now remove existing schedules for the affected teams
        if (!empty($scheduledTeams)) {
            removeExistingSchedules($pdo, array_keys($scheduledTeams));
        }

        // Optimize parameters for better performance-quality balance
        $populationSize = 200;     // Reduced from 200 for faster execution
        $generations = 500;       // Reduced from 500 for faster execution
        $mutationRate = 0.2;
        $earlyStopGenerations = 500; // Stop if no improvements after 30 generations

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
            $earlyStopGenerations
        );

        if (saveScheduleToDatabase($pdo, $bestSchedule)) {
            $result = [
                'success' => true,
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
    error_log("Error in run_scheduler.php: " . $e->getMessage());
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

// New function to check existing schedules instead of clearing them
function checkExistingSchedules($pdo, $programs = []) {
    try {
        $query = "SELECT ds.*, t.name AS team_name 
                 FROM defense_schedules ds
                 JOIN teams t ON ds.team_id = t.id
                 WHERE ds.status = 'scheduled'";

        $params = [];
        if (!empty($programs)) {
            $placeholders = implode(',', array_fill(0, count($programs), '?'));
            $query .= " AND t.program IN ($placeholders)";
            $params = $programs;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        $scheduledTeams = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $scheduledTeams[$row['team_id']] = $row;
        }
        
        error_log("Found " . count($scheduledTeams) . " teams with existing schedules");
        return $scheduledTeams;
    } catch (PDOException $e) {
        error_log("Error checking existing schedules: " . $e->getMessage());
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

// Function to remove existing schedules for specific teams
function removeExistingSchedules($pdo, $teamIds) {
    if (empty($teamIds)) return;
    
    try {
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));
        $stmt = $pdo->prepare("DELETE FROM defense_schedules WHERE team_id IN ($placeholders) AND status = 'scheduled'");
        $stmt->execute($teamIds);
        error_log("Removed schedules for " . count($teamIds) . " teams");
    } catch (PDOException $e) {
        error_log("Error removing existing schedules: " . $e->getMessage());
    }
}

// Update fetchTeams to handle multiple programs
function fetchTeams($pdo, $programs = [])
{
    if (empty($programs)) {
        $stmt = $pdo->query("
            SELECT t.id, tm.user_id as adviser_id, t.program, t.area_of_expertise
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id
            WHERE tm.role = 'adviser'
        ");
    } else {
        $placeholders = implode(',', array_fill(0, count($programs), '?'));
        $stmt = $pdo->prepare("
            SELECT t.id, tm.user_id as adviser_id, t.program, t.area_of_expertise
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id
            WHERE tm.role = 'adviser' AND t.program IN ($placeholders)
        ");
        $stmt->execute($programs);
    }
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched " . count($teams) . " teams for programs: " . (!empty($programs) ? implode(", ", $programs) : 'All Programs'));
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
    $earlyStopGenerations = 30
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
        $pdo->beginTransaction();

        // Get all teams that should be scheduled
        $expectedTeams = [];
        foreach ($GLOBALS['teams'] as $team) {
            $expectedTeams[] = $team['id'];
        }
        $expectedTeamCount = count($expectedTeams);

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
            (team_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')
        ");

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

            $stmt->execute([
                $defense['team_id'],
                $defense['panelist_ids'][0],
                $defense['panelist_ids'][1],
                $defense['panelist_ids'][2],
                $date,
                $startTime->format('H:i:s'),
                $endTime->format('H:i:s'),
                $defense['room']
            ]);

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

                $stmt->execute([
                    $teamDefense['team_id'],
                    $teamDefense['panelist_ids'][0],
                    $teamDefense['panelist_ids'][1],
                    $teamDefense['panelist_ids'][2],
                    $date,
                    $startTime->format('H:i:s'),
                    $endTime->format('H:i:s'),
                    $teamDefense['room']
                ]);

                $scheduledTeams[] = $missingTeamId;
            }
        }

        $scheduledCount = count($scheduledTeams);
        error_log("Teams scheduled: $scheduledCount out of $expectedTeamCount expected");

        if ($scheduledCount < $expectedTeamCount) {
            error_log("WARNING: Not all teams were scheduled!");
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error saving schedule to database: " . $e->getMessage());
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

            $defense = [
                'team_id' => $team['id'],
                'panelist_ids' => $selectedPanelists,
                'room' => $rooms[array_rand($rooms)],
                'time_slot' => $timeSlots[array_rand($timeSlots)],
                'day' => $days[array_rand($days)]
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
    
    $teamProgram = (string)$teamData['program'];
    $teamDepartment = getDepartment($teamProgram);

    // Candidate 0: Panelist from the exact team program (same defense title)
    $pool0 = [];
    foreach (array_keys($allPanelists) as $id) {
        if ($id == $adviserId) continue;
        $pdata = getPanelistData($pdo, $id);
        if (strcasecmp((string)($pdata['program'] ?? ''), $teamProgram) === 0) {
            $pool0[] = $id;
        }
    }
    if (!empty($pool0)) {
        $selectedPanelists[] = $pool0[array_rand($pool0)];
    } else {
        // Fallback: choose from those in the same department
        $poolDept = [];
        foreach (array_keys($allPanelists) as $id) {
            if ($id == $adviserId) continue;
            $pdata = getPanelistData($pdo, $id);
            if (strcasecmp(getDepartment($pdata['program'] ?? ''), $teamDepartment) === 0) {
                $poolDept[] = $id;
            }
        }
        $selectedPanelists[] = !empty($poolDept) ? $poolDept[array_rand($poolDept)] 
                                                 : array_rand($allPanelists);
    }

    // Candidate 1: Panelist from the same department
    $pool1 = [];
    foreach (array_keys($allPanelists) as $id) {
        if ($id == $adviserId || in_array($id, $selectedPanelists)) continue;
        $pdata = getPanelistData($pdo, $id);
        if (strcasecmp(getDepartment($pdata['program'] ?? ''), $teamDepartment) === 0) {
            $pool1[] = $id;
        }
    }
    if (!empty($pool1)) {
        $selectedPanelists[] = $pool1[array_rand($pool1)];
    } else {
        // Fallback: choose a remaining candidate
        $remaining = array_diff(array_keys($allPanelists), array_merge([$adviserId], $selectedPanelists));
        $selectedPanelists[] = !empty($remaining) ? array_rand(array_flip($remaining)) 
                                                  : array_rand($allPanelists);
    }

    // Candidate 2: Panelist from a different department
    $pool2 = [];
    foreach (array_keys($allPanelists) as $id) {
        if ($id == $adviserId || in_array($id, $selectedPanelists)) continue;
        $pdata = getPanelistData($pdo, $id);
        if (strcasecmp(getDepartment($pdata['program'] ?? ''), $teamDepartment) !== 0) {
            $pool2[] = $id;
        }
    }
    if (!empty($pool2)) {
        $selectedPanelists[] = $pool2[array_rand($pool2)];
    } else {
        // Fallback: choose a remaining candidate
        $remaining = array_diff(array_keys($allPanelists), array_merge([$adviserId], $selectedPanelists));
        $selectedPanelists[] = !empty($remaining) ? array_rand(array_flip($remaining)) 
                                                  : array_rand($allPanelists);
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
