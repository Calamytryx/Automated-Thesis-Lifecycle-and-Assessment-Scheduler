<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_log("POST data: " . print_r($_POST, true));

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../assets/setup/db.inc.php';
    require_once __DIR__ . '/../includes/edit_functions.php';

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection error');
    }

    // Main execution
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $teams = fetchTeams($pdo);
        $panelists = fetchPanelists($pdo);
        $duration = $_POST['duration'];
        $rooms = $_POST['rooms'];
        $timeSlots = $_POST['timeSlots'];
        $days = $_POST['days'];
        
        if (!is_numeric($duration) || intval($duration) <= 0) {
            throw new Exception("Invalid duration. It must be a positive integer.");
        }
        $duration = intval($duration); // Ensure it's an integer
        
        $userSchedules = fetchUserSchedules($pdo);

        $bestSchedule = geneticAlgorithm($pdo, $teams, $panelists, $rooms, $timeSlots, $days, $userSchedules, 100, 200, 0.1);

        if (saveScheduleToDatabase($pdo, $bestSchedule)) {
            $result = [
                'success' => true,
                'initialPopulationSize' => count(DefenseSchedule::$initialPopulation), // This should now be correct
                'crossoverCount' => DefenseSchedule::$crossoverCount,
                'mutationCount' => DefenseSchedule::$mutationCount,
                'conflictCounts' => DefenseSchedule::$averageConflictCounts, // Use averaged conflict counts
                'fitnessScores' => DefenseSchedule::$averageFitnessScores, // Use averaged fitness scores
                'populationPerGeneration' => DefenseSchedule::$populationPerGeneration,
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

function fetchTeams($pdo)
{
    $stmt = $pdo->query("
        SELECT t.id, tm.user_id as adviser_id
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        WHERE tm.role = 'adviser'
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchPanelists($pdo)
{
    $stmt = $pdo->query("SELECT id FROM users WHERE usertype = 2");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
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

function geneticAlgorithm($pdo, $teams, $panelists, $rooms, $timeSlots, $days, $userSchedules, $populationSize, $generations, $mutationRate)
{
    $population = createInitialPopulation($pdo, $populationSize, $teams, $panelists, $rooms, $timeSlots, $days);
    DefenseSchedule::$initialPopulation = $population; // Store only the initial population
    DefenseSchedule::$populationPerGeneration = [];
    DefenseSchedule::$conflictCounts = []; // Reset for each run
    DefenseSchedule::$fitnessScores = []; // Reset for each run
    DefenseSchedule::$averageConflictCounts = []; // Reset for averages
    DefenseSchedule::$averageFitnessScores = []; // Reset for averages

    for ($i = 0; $i < $generations; $i++) {
        foreach ($population as $schedule) {
            $schedule->calculateFitness($userSchedules);
        }

        // Track population data for the current generation
        DefenseSchedule::$populationPerGeneration[] = array_map(function ($schedule) {
            return [
                'fitness' => $schedule->fitness,
                'chromosomes' => $schedule->chromosomes
            ];
        }, $population);

        // Collect total conflict counts and total fitness scores for the generation
        $totalConflicts = array_sum(array_map(function ($schedule) {
            return $schedule->fitness < 0 ? 1 : 0; // Count conflicts based on fitness
        }, $population));

        // Store average conflict count per generation
        DefenseSchedule::$averageConflictCounts[] = $totalConflicts / $populationSize; // Average per generation
        // Store average fitness score per generation
        DefenseSchedule::$averageFitnessScores[] = array_sum(array_column($population, 'fitness')) / $populationSize; // Average per generation

        $selected = selection($population);

        $newPopulation = $selected;
        while (count($newPopulation) < $populationSize) {
            $parent1 = $selected[array_rand($selected)];
            $parent2 = $selected[array_rand($selected)];
            $child = crossover($parent1, $parent2, $userSchedules, $timeSlots, $days, $rooms);
            mutation($child, $mutationRate, $panelists, $rooms, $timeSlots, $days, $userSchedules);
            $newPopulation[] = $child;
        }

        $population = $newPopulation;
    }

    usort($population, function ($a, $b) {
        return $b->fitness - $a->fitness;
    });

    return $population[0];
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

function crossover($parent1, $parent2, $userSchedules, $timeSlots, $days, $rooms)
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
                    $defense['panelist_ids'] = array_rand(array_flip($panelists), 3);
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
    foreach ($defense['panelist_ids'] as $panelist_id) {
        if (hasScheduleConflict($pdo, $panelist_id, $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $all_defenses)) {
            return true;
        }
    }

    // Check conflicts with team members
    $teamMembers = getTeamMembers($pdo, $defense['team_id'], 'array');
    foreach ($teamMembers as $member) {
        if (hasScheduleConflict($pdo, $member['id'], $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $all_defenses)) {
            return true;
        }
    }

    return false;
}

function hasScheduleConflict($pdo, $user_id, $day, $time_slot, $userSchedules, $room, $all_defenses)
{
    if (!isset($userSchedules[$user_id])) return false;

    $defense_start = strtotime($time_slot);
    $duration = $_POST['duration'];
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
                $duration = $_POST['duration'];
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

        $stmt = $pdo->prepare("
            INSERT INTO defense_schedules 
            (team_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')
        ");

        foreach ($schedule->chromosomes as $defense) {
            $date = new DateTime($defense['day']);
            $startTime = new DateTime($defense['time_slot']);
            $endTime = clone $startTime;
            $duration = $_POST['duration'];
            $endTime->modify('+' . $duration . ' hour');

            $stmt->execute([
                $defense['team_id'],
                $defense['panelist_ids'][0],
                $defense['panelist_ids'][1],
                $defense['panelist_ids'][2],
                $date->format('Y-m-d'),
                $startTime->format('H:i:s'),
                $endTime->format('H:i:s'),
                $defense['room']
            ]);
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error saving schedule to database: " . $e->getMessage());
        throw new Exception("Failed to save schedule: " . $e->getMessage());
    }
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
            $availablePanelists = array_diff($panelists, [$team['adviser_id']]);
            $defense = [
                'team_id' => $team['id'],
                'panelist_ids' => array_rand(array_flip($availablePanelists), 3),
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
        $this->fitness = 0;
        $conflicts = 0;
        foreach ($this->chromosomes as $defenseKey => $defense) {
            $teamMembers = getTeamMembers($this->pdo, $defense['team_id'], 'array');

            if (is_array($teamMembers)) {
                foreach ($teamMembers as $member) {
                    if (hasScheduleConflict($this->pdo, $member['id'], $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $this->all_defenses)) {
                        $this->fitness -= 5;
                        $conflicts++;
                    }
                }
            } else {
                error_log("Invalid team members data for team ID: " . $defense['team_id']);
                $this->fitness -= 10;
                $conflicts++;
            }

            foreach ($defense['panelist_ids'] as $panelist_id) {
                if (hasScheduleConflict($this->pdo, $panelist_id, $defense['day'], $defense['time_slot'], $userSchedules, $defense['room'], $this->all_defenses)) {
                    $this->fitness -= 5;
                    $conflicts++;
                }
            }

            // Check for overlapping times in the same room
            foreach ($this->chromosomes as $otherKey => $otherDefense) {
                if ($defenseKey != $otherKey && $defense['room'] == $otherDefense['room'] && $defense['day'] == $otherDefense['day']) {
                    $duration = intval($_POST['duration']);
                    $defenseStart = strtotime($defense['time_slot']);
                    $defenseEnd = strtotime('+' . $duration . ' hours', $defenseStart);

                    $otherStart = strtotime($otherDefense['time_slot']);
                    $otherEnd = strtotime('+' . $duration . ' hours', $otherStart);

                    if (($defenseStart < $otherEnd) && ($defenseEnd > $otherStart)) {
                        $this->fitness -= 10;
                        $conflicts++;
                        break;
                    }
                }
            }
        }
        self::$conflictCounts[] = $conflicts;
        self::$fitnessScores[] = $this->fitness;
    }
}

function getTeamMembers($pdo, $team_id, $format = 'array')
{
    $stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, tm.role FROM team_members tm JOIN users u ON tm.user_id = u.id WHERE tm.team_id = ? ORDER BY FIELD(tm.role, 'adviser', 'leader', 'member')");
    $stmt->execute([$team_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($format === 'array') {
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

function getTeamMembersForScheduling($pdo, $team_id, $return_type = 'array')
{
    $query = "SELECT u.id, u.first_name, u.last_name, tm.role 
              FROM team_members tm 
              JOIN users u ON tm.user_id = u.id 
              WHERE tm.team_id = :team_id";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute(['team_id' => $team_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($return_type === 'array') {
            return $members;
        } else {
            return implode(', ', array_map(function ($member) {
                return $member['first_name'] . ' ' . $member['last_name'];
            }, $members));
        }
    } catch (PDOException $e) {
        error_log("Error in getTeamMembersForScheduling: " . $e->getMessage());
        return ($return_type === 'array') ? [] : '';
    }
}
