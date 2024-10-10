<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../assets/setup/db.inc.php';

class DefenseSchedule {
    public $chromosomes = [];
    public $fitness = 0;

    public function __construct($students, $panelists, $rooms, $timeSlots, $days) {
        $startDate = new DateTime('2024-12-09'); // Start date: December 9, 2024
        foreach ($students as $student) {
            $dayOffset = array_rand($days); // 0 to 5 (Monday to Saturday)
            $defenseDate = clone $startDate;
            $defenseDate->modify("+$dayOffset days");
            $this->chromosomes[] = [
                'student_id' => $student['id'],
                'panelist_ids' => array_rand(array_flip($panelists), 3),
                'room' => $rooms[array_rand($rooms)],
                'time_slot' => $timeSlots[array_rand($timeSlots)],
                'day' => $defenseDate->format('Y-m-d')
            ];
        }
    }

    public function calculateFitness($userSchedules) {
        $conflicts = 0;
        foreach ($this->chromosomes as $i => $defense1) {
            // Check for conflicts with other defenses
            foreach (array_slice($this->chromosomes, $i + 1) as $defense2) {
                if ($defense1['day'] == $defense2['day'] && 
                    $defense1['time_slot'] == $defense2['time_slot']) {
                    if ($defense1['room'] == $defense2['room']) $conflicts++;
                    foreach ($defense1['panelist_ids'] as $panelist) {
                        if (in_array($panelist, $defense2['panelist_ids'])) $conflicts++;
                    }
                }
            }
            
            // Check for conflicts with user schedules
            $defenseTime = strtotime($defense1['day'] . ' ' . $defense1['time_slot']);
            $defenseEndTime = $defenseTime + 3600; // Assuming 1 hour defense
            
            $dayOfWeek = date('l', strtotime($defense1['day']));
            
            // Check student schedule
            if (isset($userSchedules[$defense1['student_id']])) {
                foreach ($userSchedules[$defense1['student_id']] as $schedule) {
                    if ($schedule['day_of_week'] == $dayOfWeek) {
                        $scheduleStart = strtotime($defense1['day'] . ' ' . $schedule['start_time']);
                        $scheduleEnd = strtotime($defense1['day'] . ' ' . $schedule['end_time']);
                        if ($defenseTime < $scheduleEnd && $defenseEndTime > $scheduleStart) $conflicts++;
                    }
                }
            }
            
            // Check panelists' schedules
            foreach ($defense1['panelist_ids'] as $panelistId) {
                if (isset($userSchedules[$panelistId])) {
                    foreach ($userSchedules[$panelistId] as $schedule) {
                        if ($schedule['day_of_week'] == $dayOfWeek) {
                            $scheduleStart = strtotime($defense1['day'] . ' ' . $schedule['start_time']);
                            $scheduleEnd = strtotime($defense1['day'] . ' ' . $schedule['end_time']);
                            if ($defenseTime < $scheduleEnd && $defenseEndTime > $scheduleStart) $conflicts++;
                        }
                    }
                }
            }
        }
        $this->fitness = 1 / (1 + $conflicts);
        return $this->fitness;
    }
}

function createInitialPopulation($populationSize, $students, $panelists, $rooms, $timeSlots, $days) {
    $population = [];
    for ($i = 0; $i < $populationSize; $i++) {
        $population[] = new DefenseSchedule($students, $panelists, $rooms, $timeSlots, $days);
    }
    return $population;
}

function selection($population) {
    usort($population, function($a, $b) {
        return $b->fitness - $a->fitness;
    });
    return array_slice($population, 0, count($population) / 2);
}

function crossover($parent1, $parent2) {
    $child = new DefenseSchedule([], [], [], [], []);
    $crossoverPoint = rand(0, count($parent1->chromosomes) - 1);
    $child->chromosomes = array_merge(
        array_slice($parent1->chromosomes, 0, $crossoverPoint),
        array_slice($parent2->chromosomes, $crossoverPoint)
    );
    return $child;
}

function mutation($schedule, $mutationRate, $panelists, $rooms, $timeSlots, $days) {
    $startDate = new DateTime('2024-12-09');
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
                    $dayOffset = array_rand($days);
                    $defenseDate = clone $startDate;
                    $defenseDate->modify("+$dayOffset days");
                    $defense['day'] = $defenseDate->format('Y-m-d');
                    break;
            }
        }
    }
}

function geneticAlgorithm($students, $panelists, $rooms, $timeSlots, $days, $userSchedules, $populationSize = 50, $generations = 100, $mutationRate = 0.01) {
    $population = createInitialPopulation($populationSize, $students, $panelists, $rooms, $timeSlots, $days);
    
    for ($i = 0; $i < $generations; $i++) {
        foreach ($population as $schedule) {
            $schedule->calculateFitness($userSchedules);
        }
        
        $selected = selection($population);
        
        $newPopulation = $selected;
        while (count($newPopulation) < $populationSize) {
            $parent1 = $selected[array_rand($selected)];
            $parent2 = $selected[array_rand($selected)];
            $child = crossover($parent1, $parent2);
            mutation($child, $mutationRate, $panelists, $rooms, $timeSlots, $days);
            $newPopulation[] = $child;
        }
        
        $population = $newPopulation;
    }
    
    usort($population, function($a, $b) {
        return $b->fitness - $a->fitness;
    });
    
    return $population[0];
}

// Main execution
try {
    $students = fetchStudents($pdo);
    $panelists = fetchPanelists($pdo);
    $rooms = ['Defense Room A', 'Defense Room B'];
    $timeSlots = ['07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00'];
    $days = [0, 1, 2, 3, 4, 5]; // 0 = Monday, 5 = Saturday
    $userSchedules = fetchUserSchedules($pdo);

    $bestSchedule = geneticAlgorithm($students, $panelists, $rooms, $timeSlots, $days, $userSchedules);
    saveScheduleToDatabase($pdo, $bestSchedule->chromosomes);

    echo json_encode(['success' => true, 'message' => 'Schedule generated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function fetchStudents($pdo) {
    $stmt = $pdo->query("SELECT id FROM users WHERE usertype = 1");
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

function saveScheduleToDatabase($pdo, $schedule) {
    $pdo->beginTransaction();
    try {
        foreach ($schedule as $defense) {
            $formattedDate = date('Y-m-d', strtotime($defense['day'])); // Change to Y-m-d format for MySQL
            $stmt = $pdo->prepare("INSERT INTO defense_schedules (student_id, panelist_id, panelist_id2, panelist_id3, schedule_date, start_time, end_time, room) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $defense['student_id'],
                $defense['panelist_ids'][0],
                $defense['panelist_ids'][1],
                $defense['panelist_ids'][2],
                $formattedDate,
                $defense['time_slot'],
                date('H:i:s', strtotime($defense['time_slot']) + 3600), // Assuming 1 hour defense
                $defense['room']
            ]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}