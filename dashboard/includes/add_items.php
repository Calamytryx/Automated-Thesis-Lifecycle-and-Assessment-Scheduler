<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'];
    
    // Remove table from $_POST
    unset($_POST['table']);
    
    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        exit;
    }

    // Special handling for research_titles
    if ($table === 'research_titles') {
        $approved = isset($_POST['approved']) ? date('Y-m-d H:i:s') : null;
        unset($_POST['approved']);
        $_POST['approved_at'] = $approved;
    }

    // Special handling for rubrics
    if($table === 'rubrics'){
        error_log("=== START ADD RUBRIC ===");
        error_log("Received POST data for rubric: " . print_r($_POST, true));
        
        $pdo->beginTransaction();
        try {
            // Disable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
            error_log("Foreign key checks disabled");
            
            // Get the quality criteria count
            $quality_criteria_count = count($_POST['quality_level'] ?? []);
            error_log("Quality criteria count: $quality_criteria_count");
            
            // Get the criteria count (number of rows)
            $criteria_count = count($_POST['criterion_description'] ?? []);
            error_log("Criteria count: $criteria_count");
            
            // Calculate max total score
            $max_total_score = $_POST['max_total_score'] ?? 0;
            error_log("Max total score: $max_total_score");
            
            // Create a default structure JSON
            $criteria = [];
            $levels = [];
            
            // Ensure we have at least one level and one criterion
            $quality_criteria_count = max(1, $quality_criteria_count);
            $criteria_count = max(1, $criteria_count);
            
            // Add levels from quality criteria
            for ($i = 0; $i < $quality_criteria_count; $i++) {
                $level_name = $_POST['quality_level'][$i] ?? "Level " . ($i + 1);
                $levels[] = $level_name;
            }
            
            // Add criteria rows
            for ($i = 0; $i < $criteria_count; $i++) {
                $criterion = [
                    'criterion' => $_POST['criterion_description'][$i] ?? "Criterion " . ($i + 1),
                    'levels' => []
                ];
                
                // Add level content for each criterion
                for ($j = 0; $j < $quality_criteria_count; $j++) {
                    $content = [
                        'content' => '',
                        'rowSpan' => 1,
                        'colSpan' => 1
                    ];
                    $criterion['levels'][] = $content;
                }
                
                $criteria[] = $criterion;
            }
            
            // Create the structure JSON
            $structure = [
                'levels' => $levels,
                'criteria' => $criteria
            ];
            $structure_json = json_encode($structure);
            if ($structure_json === false) {
                // JSON encoding failed, create a basic structure
                error_log("JSON encoding failed. Error: " . json_last_error_msg());
                $structure_json = '{"levels":["Level 1"],"criteria":[{"criterion":"Criterion 1","levels":[{"content":"","rowSpan":1,"colSpan":1}]}]}';
            }
            error_log("Structure JSON created: " . substr($structure_json, 0, 200) . "...");
            
            // Insert into rubrics table
            $stmt = $pdo->prepare("INSERT INTO rubrics (name, description, max_total_score, quality_criteria_count, structure) VALUES (:name, :description, :max_total_score, :quality_criteria_count, :structure)");
            $result = $stmt->execute([
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'max_total_score' => $max_total_score,
                'quality_criteria_count' => $quality_criteria_count,
                'structure' => $structure_json
            ]);
            error_log("Rubric insert result: " . ($result ? "Success" : "Failed") . " - Last insert ID: " . $pdo->lastInsertId());
            
            // Get the last inserted ID
            $rubricId = $pdo->lastInsertId();
            
            // Insert quality criteria
            if ($quality_criteria_count > 0) {
                $stmt = $pdo->prepare("INSERT INTO rubric_quality_criteria (rubric_id, quality_level, points, description) VALUES (:rubric_id, :quality_level, :points, :description)");
                
                foreach ($_POST['quality_level'] as $index => $level) {
                    if (isset($_POST['points'][$index])) {
                        $qcResult = $stmt->execute([
                            'rubric_id' => $rubricId,
                            'quality_level' => $index + 1,
                            'points' => $_POST['points'][$index],
                            'description' => $_POST['quality_description'][$index] ?? ''
                        ]);
                        error_log("Insert quality criteria level $index result: " . ($qcResult ? "Success" : "Failed"));
                    }
                }
            }
            
            // Insert rubric rows
            if ($criteria_count > 0) {
                $stmt = $pdo->prepare("INSERT INTO rubric_rows (rubric_id, description, order_index) VALUES (:rubric_id, :description, :order_index)");
                
                foreach ($_POST['criterion_description'] as $index => $description) {
                    $rowResult = $stmt->execute([
                        'rubric_id' => $rubricId,
                        'description' => $description,
                        'order_index' => $index
                    ]);
                    error_log("Insert rubric row $index result: " . ($rowResult ? "Success" : "Failed"));
                }
            }
            
            // Enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
            error_log("Foreign key checks enabled");
            
            $pdo->commit();
            error_log("Transaction committed successfully");
            error_log("=== END ADD RUBRIC ===");
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error occurred, transaction rolled back: " . $e->getMessage());
            error_log("=== END ADD RUBRIC (WITH ERROR) ===");
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit;
    }
    
    // Special handling for teams
    if ($table === 'teams') {
        $pdo->beginTransaction();
        
        try {
            // Insert into teams table with title and area_of_expertise
            $stmt = $pdo->prepare("INSERT INTO teams (name, area_of_expertise, program) VALUES (:name, :area_of_expertise, :program)");
            $stmt->execute([
                'name' => $_POST['name'],
                'area_of_expertise' => isset($_POST['area_of_expertise']) ? $_POST['area_of_expertise'] : null,
                'program' => isset($_POST['program']) ? $_POST['program'] : null
            ]);
            
            // Get the last inserted ID
            $teamId = $pdo->lastInsertId();
            
            // Check if members data exists and is in the correct format
            if (isset($_POST['members']) && !empty($_POST['members'])) {
                // If members is a JSON string, decode it
                $members = $_POST['members'];
                if (is_string($members)) {
                    $members = json_decode($members, true);
                }
                
                if (is_array($members)) {
                    foreach ($members as $member) {
                        if (isset($member['id']) && isset($member['role'])) {
                            $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
                            $stmt->execute([
                                'team_id' => $teamId,
                                'user_id' => $member['id'],
                                'role' => $member['role']
                            ]);
                        }
                    }
                }
            }
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit;
    }
    
    // Special handling for users
    if ($table === 'users') {
        // Hash the password
        if (isset($_POST['password'])) {
            $_POST['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
    }
    
    // Special handling for defense_schedules
    if ($table === 'defense_schedules') {
        $pdo->beginTransaction();
        
        try {
            // Extract panelist IDs from POST data
            $panelistIds = [];
            if (isset($_POST['panelist_id']) && is_array($_POST['panelist_id'])) {
                $panelistIds = array_filter($_POST['panelist_id'], function($value) {
                    return !empty($value);
                });
                unset($_POST['panelist_id']); // Remove from POST data to prevent array to string conversion
            }
            
            // Add up to three panelists directly to the defense_schedules table
            if (!empty($panelistIds)) {
                // Sort by array key to ensure consistent assignment order
                ksort($panelistIds);
                $panelistIds = array_values($panelistIds); // Reset keys after sorting
                
                if (isset($panelistIds[0])) {
                    $_POST['panelist_id'] = $panelistIds[0];
                }
                
                if (isset($panelistIds[1])) {
                    $_POST['panelist_id2'] = $panelistIds[1];
                }
                
                if (isset($panelistIds[2])) {
                    $_POST['panelist_id3'] = $panelistIds[2];
                }
                
                // Log a warning if there are more than 3 panelists as the database only supports 3
                if (count($panelistIds) > 3) {
                    error_log("Warning: Only the first 3 panelists were saved. The database schema only supports 3 panelists.");
                }
            }
            
            // Validation: Check if there's already a schedule with the same date + time + room
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM defense_schedules 
                                  WHERE schedule_date = :schedule_date 
                                  AND ((start_time <= :end_time AND end_time >= :start_time)
                                  OR (start_time >= :start_time AND start_time < :end_time))
                                  AND room = :room");
            $stmt->execute([
                'schedule_date' => $_POST['schedule_date'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'],
                'room' => $_POST['room']
            ]);
            $roomBooked = ($stmt->fetchColumn() > 0);
            
            // Validation: Check if the team is already scheduled for a defense
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM defense_schedules WHERE team_id = :team_id");
            $stmt->execute(['team_id' => $_POST['team_id']]);
            $teamBooked = ($stmt->fetchColumn() > 0);
            
            // Only proceed if neither condition is true
            if ($roomBooked) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'This room is already booked for this date and time.']);
                exit;
            }
            
            if ($teamBooked) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'This team already has a scheduled defense.']);
                exit;
            }
            
            $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($values)");
            $stmt->execute($_POST);
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    // General handling for other tables
    $columns = implode(", ", array_keys($_POST));
    $values = ":" . implode(", :", array_keys($_POST));
    
    $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($values)");
    
    try {
        $stmt->execute($_POST);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
}
/**
 * This file is part of the COECSA Thesis Dashboard.
 * 
 * 
 * Description:
 * This script is responsible for adding items to the dashboard.
 * 
 * Usage:
 * Include this file where item addition functionality is required.
 * 
 * Note:
 * Ensure that the necessary dependencies and configurations are set up before including this file.
 */
?>