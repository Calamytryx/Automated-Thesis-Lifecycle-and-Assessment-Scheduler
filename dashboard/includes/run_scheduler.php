
<?php

/**
 * This script is responsible for generating and scheduling defense schedules using a genetic algorithm.
 * It includes various functions and a class to handle the scheduling process, fitness calculation, 
 * conflict detection, and database operations.
 *
 * @file /c:/xampp/htdocs/coecsathesis/dashboard/includes/run_scheduler.php
 *
 * @requires /../../assets/setup/db.inc.php
 * @requires /../includes/edit_functions.php
 *
 * @class DefenseSchedule
 * @property PDO $pdo - The PDO instance for database operations.
 * @property array $chromosomes - The chromosomes representing the schedule.
 * @property int $fitness - The fitness score of the schedule.
 * @property static array $initialPopulation - The initial population of schedules.
 * @property static int $crossoverCount - The count of crossover operations.
 * @property static int $mutationCount - The count of mutation operations.
 * @property static array $conflictCounts - The counts of conflicts in schedules.
 * @property static array $fitnessScores - The fitness scores of schedules.
 *
 * @method __construct(PDO $pdo, array $teams, array $panelists, array $rooms, array $timeSlots, array $days) - Initializes the schedule.
 * @method calculateFitness(array $userSchedules) - Calculates the fitness of the schedule.
 *
 * @function getTeamMembers(PDO $pdo, int $team_id, string $return_type = 'array') - Retrieves team members.
 * @function hasScheduleConflict(int $user_id, string $day, string $time_slot, array $userSchedules) - Checks for schedule conflicts.
 * @function createInitialPopulation(PDO $pdo, int $populationSize, array $teams, array $panelists, array $rooms, array $timeSlots, array $days) - Creates the initial population of schedules.
 * @function selection(array $population) - Selects the best schedules from the population.
 * @function crossover(DefenseSchedule $parent1, DefenseSchedule $parent2, array $userSchedules, array $timeSlots, array $days, array $rooms) - Performs crossover between two schedules.
 * @function mutation(DefenseSchedule $schedule, float $mutationRate, array $panelists, array $rooms, array $timeSlots, array $days, array $userSchedules) - Mutates a schedule.
 * @function isTimeSlotAvailable(DefenseSchedule $schedule, string $day, string $timeSlot, string $room) - Checks if a time slot is available.
 * @function getAvailableTimeSlot(DefenseSchedule $schedule, array $days, array $timeSlots, array $rooms) - Gets an available time slot.
 * @function geneticAlgorithm(PDO $pdo, array $teams, array $panelists, array $rooms, array $timeSlots, array $days, array $userSchedules, int $populationSize = 50, int $generations = 100, float $mutationRate = 0.01) - Runs the genetic algorithm to generate the best schedule.
 * @function saveScheduleToDatabase(PDO $pdo, DefenseSchedule $schedule) - Saves the schedule to the database.
 * @function getTeamMembersForScheduling(PDO $pdo, int $team_id, string $return_type = 'array') - Retrieves team members for scheduling.
 * @function fetchTeams(PDO $pdo) - Fetches teams from the database.
 * @function fetchPanelists(PDO $pdo) - Fetches panelists from the database.
 * @function fetchUserSchedules(PDO $pdo) - Fetches user schedules from the database.
 *
 * Main Execution:
 * - Handles POST requests to generate and save defense schedules.
 * - Fetches necessary data (teams, panelists, rooms, time slots, days, user schedules).
 * - Runs the genetic algorithm to generate the best schedule.
 * - Saves the generated schedule to the database.
 * - Returns a JSON response indicating success or failure.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_log("Error log file location: " . ini_get('error_log'));

require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/../includes/edit_functions.php';

// Ensure $pdo is a valid PDO object
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die(json_encode(['success' => false, 'message' => 'Database connection error']));
}

class DefenseSchedule {
    private $pdo;
    public $chromosomes = [];
    public $fitness = 0;
    public static $initialPopulation = [];
    public static $crossoverCount = 0;
    public static $mutationCount = 0;
    public static $conflictCounts = [];
    public static $fitnessScores = [];

    public function __construct($pdo, $teams, $panelists, $rooms, $timeSlots, $days) {
        $this->pdo = $pdo;
        foreach ($teams as $team) {
            $availablePanelists = array_diff($panelists, [$team['adviser_id']]);
            $this->chromosomes[] = [
                'team_id' => $team['id'],
                'panelist_ids' => array_rand(array_flip($availablePanelists), 3),
                'room' => $rooms[array_rand($rooms)],
                'time_slot' => $timeSlots[array_rand($timeSlots)],
                'day' => $days[array_rand($days)]
            ];
        }
        self::$initialPopulation[] = $this->chromosomes;
    }

    public function calculateFitness($userSchedules) {
        $this->fitness = 0;
        $conflicts = 0;
        foreach ($this->chromosomes as $defense) {
            $teamMembers = getTeamMembers($this->pdo, $defense['team_id'], 'array');

            if (is_array($teamMembers)) {
                foreach ($teamMembers as $member) {
                    if (hasScheduleConflict($member['id'], $defense['day'], $defense['time_slot'], $userSchedules)) {
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
                if (hasScheduleConflict($panelist_id, $defense['day'], $defense['time_slot'], $userSchedules)) {
                    $this->fitness -= 5;
                    $conflicts++;
                }
            }
        }
        self::$conflictCounts[] = $conflicts;
        self::$fitnessScores[] = $this->fitness;
    }
}

function getTeamMembers($pdo, $team_id, $return_type = 'array') {
    if (!($pdo instanceof PDO)) {
        error_log("Error in getTeamMembers: \$pdo is not a PDO object");
        return ($return_type === 'array') ? [] : '';
    }

    try {
        $stmt = $pdo->prepare("
            SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name 
            FROM users u
            JOIN team_members tm ON u.id = tm.user_id
            WHERE tm.team_id = ?
        ");
        $stmt->execute([$team_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($return_type === 'array') {
            return $members;
        } else {
            return implode(', ', array_column($members, 'name'));
        }
    } catch (PDOException $e) {
        error_log("Error in getTeamMembers: " . $e->getMessage());
        return ($return_type === 'array') ? [] : '';
    }
}

function hasScheduleConflict($user_id, $day, $time_slot, $userSchedules) {
    if (!isset($userSchedules[$user_id])) return false;
    foreach ($userSchedules[$user_id] as $schedule) {
        if ($schedule['day_of_week'] == $day && 
            $time_slot >= $schedule['start_time'] && 
            $time_slot < $schedule['end_time']) {
            return true;
        }
    }
    return false;
}

function createInitialPopulation($pdo, $populationSize, $teams, $panelists, $rooms, $timeSlots, $days) {
    $population = [];
    for ($i = 0; $i < $populationSize; $i++) {
        $population[] = new DefenseSchedule($pdo, $teams, $panelists, $rooms, $timeSlots, $days);
    }
    return $population;
}

function selection($population) {
    usort($population, function($a, $b) {
        return $b->fitness - $a->fitness;
    });
    return array_slice($population, 0, count($population) / 2);
}

function crossover($parent1, $parent2, $userSchedules, $timeSlots, $days, $rooms) {
    $child = new DefenseSchedule([], [], [], [], [], []);
    $crossoverPoint = rand(0, count($parent1->chromosomes) - 1);
    $child->chromosomes = array_merge(
        array_slice($parent1->chromosomes, 0, $crossoverPoint),
        array_slice($parent2->chromosomes, $crossoverPoint)
    );

    foreach ($child->chromosomes as &$defense) {
        foreach ($defense['panelist_ids'] as $panelist_id) {
            while (hasScheduleConflict($panelist_id, $defense['day'], $defense['time_slot'], $userSchedules)) {
                $defense['time_slot'] = $timeSlots[array_rand($timeSlots)];
                $defense['day'] = $days[array_rand($days)];
                $defense['room'] = $rooms[array_rand($rooms)];
            }
        }
    }

    DefenseSchedule::$crossoverCount++;
    return $child;
}

function mutation($schedule, $mutationRate, $panelists, $rooms, $timeSlots, $days, $userSchedules) {
    foreach ($schedule->chromosomes as &$defense) {
        if (rand() / getrandmax() < $mutationRate) {
            $mutationType = rand(0, 3);
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
            DefenseSchedule::$mutationCount++;
        }

        foreach ($defense['panelist_ids'] as $panelist_id) {
            while (hasScheduleConflict($panelist_id, $defense['day'], $defense['time_slot'], $userSchedules)) {
                $defense['time_slot'] = $timeSlots[array_rand($timeSlots)];
                $defense['day'] = $days[array_rand($days)];
            }
        }
    }
}

function isTimeSlotAvailable($schedule, $day, $timeSlot, $room) {
    foreach ($schedule->chromosomes as $defense) {
        if ($defense['day'] == $day && $defense['time_slot'] == $timeSlot && $defense['room'] == $room) {
            return false;
        }
    }
    return true;
}

function getAvailableTimeSlot($schedule, $days, $timeSlots, $rooms) {
    $availableSlots = [];
    foreach ($days as $day) {
        foreach ($timeSlots as $timeSlot) {
            foreach ($rooms as $room) {
                if (isTimeSlotAvailable($schedule, $day, $timeSlot, $room)) {
                    $availableSlots[] = ['day' => $day, 'time_slot' => $timeSlot, 'room' => $room];
                }
            }
        }
    }
    return $availableSlots ? $availableSlots[array_rand($availableSlots)] : null;
}

function geneticAlgorithm($pdo, $teams, $panelists, $rooms, $timeSlots, $days, $userSchedules, $populationSize = 50, $generations = 100, $mutationRate = 0.01) {
    $population = createInitialPopulation($pdo, $populationSize, $teams, $panelists, $rooms, $timeSlots, $days);
    
    for ($i = 0; $i < $generations; $i++) {
        foreach ($population as $schedule) {
            $schedule->calculateFitness($userSchedules);
        }
        
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
    
    usort($population, function($a, $b) {
        return $b->fitness - $a->fitness;
    });
    
    return $population[0];
}

function saveScheduleToDatabase($pdo, $schedule) {
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
            $endTime->modify('+1 hour');

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

function getTeamMembersForScheduling($pdo, $team_id, $return_type = 'array') {
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
            return implode(', ', array_map(function($member) {
                return $member['first_name'] . ' ' . $member['last_name'];
            }, $members));
        }
    } catch (PDOException $e) {
        error_log("Error in getTeamMembersForScheduling: " . $e->getMessage());
        return ($return_type === 'array') ? [] : '';
    }
}

// Main execution
// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $teams = fetchTeams($pdo);
        $panelists = fetchPanelists($pdo);
        $rooms = ['Defense Room A', 'Defense Room B'];
        $timeSlots = ['07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00'];
        $days = ['2024-12-09', '2024-12-10', '2024-12-11', '2024-12-12', '2024-12-13', '2024-12-14'];
        $userSchedules = fetchUserSchedules($pdo);

        $bestSchedule = geneticAlgorithm($pdo, $teams, $panelists, $rooms, $timeSlots, $days, $userSchedules, 100, 200, 0.05);
        
        if (saveScheduleToDatabase($pdo, $bestSchedule)) {
            echo json_encode([
                'success' => true,
                'initialPopulationSize' => count(DefenseSchedule::$initialPopulation),
                'crossoverCount' => DefenseSchedule::$crossoverCount,
                'mutationCount' => DefenseSchedule::$mutationCount,
                'conflictCounts' => DefenseSchedule::$conflictCounts,
                'fitnessScores' => DefenseSchedule::$fitnessScores,
                'message' => 'Schedule generated and saved successfully'
            ]);
        } else {
            throw new Exception("Failed to save schedule to database");
        }
    } catch (Exception $e) {
        error_log("Error in run_scheduler.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function fetchTeams($pdo) {
    $stmt = $pdo->query("
        SELECT t.id, tm.user_id as adviser_id
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        WHERE tm.role = 'adviser'
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchPanelists($pdo) {
    $stmt = $pdo->query("SELECT id FROM users WHERE usertype = 2");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function fetchUserSchedules($pdo) {
    $stmt = $pdo->query("SELECT user_id, day_of_week, start_time, end_time FROM user_schedules");
    $schedules = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $schedules[$row['user_id']][] = $row;
    }
    return $schedules;
}