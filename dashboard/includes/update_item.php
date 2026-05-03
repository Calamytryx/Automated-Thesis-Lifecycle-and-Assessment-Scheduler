<?php
/**
 * This script handles updating items in the database based on POST data.
 * 
 * 
 * Dependencies:
 * - Requires database connection setup from db.inc.php.
 * - Requires edit functions from edit_functions.php.
 * 
 * Functionality:
 * - Enables error reporting for debugging.
 * - Logs all received POST data.
 * - Processes POST requests to update items in specified tables.
 * - Supports updating 'teams' table with nested updates for team members and research titles.
 * - Supports updating other tables with dynamic field updates.
 * - Uses transactions to ensure data integrity.
 * - Provides JSON response indicating success or failure.
 * 
 * POST Parameters:
 * - table: The name of the table to update.
 * - id: The ID of the item to update.
 * - Additional parameters depend on the table being updated.
 * 
 * Response:
 * - JSON object with 'success' (boolean) and 'message' (string) fields.
 * 
 * Error Handling:
 * - Rolls back transaction on failure.
 * - Logs detailed error messages for debugging.
 */
require_once __DIR__ . '/../../assets/setup/db.inc.php';
require_once __DIR__ . '/edit_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

$response = ['success' => false, 'message' => ''];

// Enhanced logging for all POST data and specific fields
error_log("=== START UPDATE ITEM REQUEST ===");
error_log("Received POST data: " . print_r($_POST, true));

// Log specific important fields for debugging
$table = $_POST['table'] ?? 'unknown';
$id = $_POST['id'] ?? 'unknown';
error_log("Table: $table, ID: $id");

if (isset($_POST['quality_level'])) {
    error_log("Quality levels count: " . count($_POST['quality_level']));
}
if (isset($_POST['criterion_description'])) {
    error_log("Criteria descriptions count: " . count($_POST['criterion_description']));
}
if (isset($_POST['points'])) {
    error_log("Points count: " . count($_POST['points']));
}
if (isset($_POST['quality_description'])) {
    error_log("Quality descriptions count: " . count($_POST['quality_description']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'] ?? '';
    $id = $_POST['id'] ?? '';

    error_log("Table: $table, ID: $id");

    if (!empty($table) && !empty($id)) {
        try {
            $pdo->beginTransaction();

            if ($table === 'teams') {
                // Handle team update
                $name = $_POST['name'] ?? '';
                $title = $_POST['title'] ?? '';
                $members = json_decode($_POST['members'] ?? '[]', true);
                
                error_log("Updating team: Name=$name, Title=$title, Members=" . print_r($members, true));
                
                // Update team name
                $sql = "UPDATE `teams` SET `name` = ? WHERE `id` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $id]);
                error_log("Team name updated. Affected rows: " . $stmt->rowCount());
                
                // Update research title
                $sql = "UPDATE `research_titles` SET `title` = ? WHERE `team_id` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $id]);
                error_log("Research title updated. Affected rows: " . $stmt->rowCount());
                
                // Get current members in the team
                $currentMembers = $pdo->query("SELECT user_id, role FROM team_members WHERE team_id = $id")->fetchAll(PDO::FETCH_ASSOC);
                $currentMembersMap = [];
                foreach ($currentMembers as $currentMember) {
                    $currentMembersMap[$currentMember['user_id']] = $currentMember['role'];
                }
            
                // Create an associative array of the new members with their roles
                $newMembers = [];
                foreach ($members as $member) {
                    $newMembers[$member['id']] = $member['role'];
                }
            
                // Remove members not in the new list
                $membersToRemove = array_diff(array_keys($currentMembersMap), array_keys($newMembers));
                if (!empty($membersToRemove)) {
                    $sql = "DELETE FROM team_members WHERE team_id = ? AND user_id IN (" . implode(',', array_fill(0, count($membersToRemove), '?')) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array_merge([$id], $membersToRemove));
                    error_log("Removed members: " . implode(', ', $membersToRemove));
                }
            
                // Handle updating the roles for members, ensuring no duplicate user for the same team
                $sql = "INSERT INTO team_members (team_id, user_id, role) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE role = VALUES(role)";
                $stmt = $pdo->prepare($sql);
            
                // Loop through the new members and ensure no duplicates for the same team_id
                foreach ($members as $member) {
                    // Check if the user already exists in the team
                    if (isset($currentMembersMap[$member['id']])) {
                        // If the user already exists, update their role if needed
                        if ($currentMembersMap[$member['id']] !== $member['role']) {
                            // Update their role in the team
                            $stmt->execute([$id, $member['id'], $member['role']]);
                            error_log("Updated member role. User ID: {$member['id']}, Role: {$member['role']}, Team ID: $id. Affected rows: " . $stmt->rowCount());
                        }
                    } else {
                        // If the user is not in the team, insert them
                        $stmt->execute([$id, $member['id'], $member['role']]);
                        error_log("Added member. User ID: {$member['id']}, Role: {$member['role']}, Team ID: $id. Affected rows: " . $stmt->rowCount());
                    }
                }
            
                $result = true; // Assume success if no exception is thrown
            }
             elseif ($table === 'defense_schedules') {
                // Handle defense schedules update
                $schedule_date = $_POST['schedule_date'] ?? '';
                $start_time = $_POST['start_time'] ?? '';
                $end_time = $_POST['end_time'] ?? '';
                $room = $_POST['room'] ?? '';
                $team_id = $_POST['team_id'] ?? '';

                $sessionUserId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : -1;
                $sessionUserType = isset($_SESSION['usertype']) ? (int)$_SESSION['usertype'] : -1;

                if (isDefenseScheduleFinalized($pdo, (int)$id)) {
                    throw new Exception('This schedule has been finalized and cannot be edited.');
                }

                if (!canUserAccessDefenseScheduleByTeam($pdo, $sessionUserId, $sessionUserType, (int)$team_id)) {
                    throw new Exception('You do not have access to edit this schedule.');
                }
            
                // Handle panelist IDs as scalars
                $postedPanelists = $_POST['panelist_id'] ?? null;
                if (!is_array($postedPanelists)) {
                    $postedPanelists = [$postedPanelists];
                }
                $panelist_id = $postedPanelists[0] ?? null;
                $panelist_id2 = $postedPanelists[1] ?? null;
                $panelist_id3 = $postedPanelists[2] ?? null;

                $panelistsForConflict = array_values(array_filter(array_map('intval', [
                    $panelist_id,
                    $panelist_id2,
                    $panelist_id3,
                ])));

                $conflictCheck = validateStudentScheduleConflicts(
                    $pdo,
                    (int)$team_id,
                    $schedule_date,
                    $start_time,
                    $end_time,
                    (int)$id,
                    $panelistsForConflict
                );
                if (!$conflictCheck['ok']) {
                    throw new Exception($conflictCheck['message']);
                }
            
                error_log("Updating defense schedule: Date=$schedule_date, Start=$start_time, End=$end_time, Room=$room, Team ID=$team_id, Panelists=[$panelist_id, $panelist_id2, $panelist_id3]");
            
                // Validate panelist IDs
                $valid_panelists = [];
                foreach ([$panelist_id, $panelist_id2, $panelist_id3] as $pid) {
                    if ($pid !== null) {
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `id` = ?");
                        $stmt->execute([$pid]);
                        if ($stmt->fetchColumn() > 0) {
                            $valid_panelists[] = $pid;
                        } else {
                            $valid_panelists[] = null;
                            error_log("Invalid panelist ID: $pid");
                        }
                    } else {
                        $valid_panelists[] = null;
                    }
                }
            
                list($panelist_id, $panelist_id2, $panelist_id3) = $valid_panelists;
            
                // Update defense schedule
                $sql = "UPDATE `defense_schedules` SET `schedule_date` = ?, `start_time` = ?, `end_time` = ?, `room` = ?, `team_id` = ?, `panelist_id` = ?, `panelist_id2` = ?, `panelist_id3` = ? WHERE `id` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$schedule_date, $start_time, $end_time, $room, $team_id, $panelist_id, $panelist_id2, $panelist_id3, $id]);
                error_log("Defense schedule updated. Affected rows: " . $stmt->rowCount());

                // 🎯 CREATE DEFENSE SCHEDULE NOTIFICATIONS FOR MANUAL UPDATE
                if ($stmt->rowCount() > 0) {
                    require_once __DIR__ . '/../../assets/includes/notification_functions.php';
                    
                    $panelistIds = [$panelist_id, $panelist_id2, $panelist_id3];
                    $startTimeFormatted = date('H:i', strtotime($start_time));
                    $endTimeFormatted = date('H:i', strtotime($end_time));
                    
                    createDefenseScheduleNotifications($pdo, $id, $team_id, $panelistIds, $schedule_date, $startTimeFormatted, $endTimeFormatted, $room, true);
                }

                $result = true; // Assume success if no exception is thrown
            } elseif ($table === 'rubrics') {
                try {
                    // Transaction already started in the outer try block, no need to start again
                    // $pdo->beginTransaction();
                    
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
                    
                    // Update basic rubric info
                    error_log("Updating rubric with ID: $id");
                    $stmt = $pdo->prepare("UPDATE rubrics SET name = ?, description = ?, max_total_score = ?, quality_criteria_count = ?, structure = ? WHERE id = ?");
                    $updateResult = $stmt->execute([
                        $_POST['name'],
                        $_POST['description'],
                        $max_total_score,
                        $quality_criteria_count,
                        $structure_json,
                        $id
                    ]);
                    error_log("Rubric update result: " . ($updateResult ? "Success" : "Failed") . " - Rows affected: " . $stmt->rowCount());
                    
                    // Delete existing quality criteria and rows
                    $stmt = $pdo->prepare("DELETE FROM rubric_quality_criteria WHERE rubric_id = ?");
                    $deleteQCResult = $stmt->execute([$id]);
                    error_log("Delete quality criteria result: " . ($deleteQCResult ? "Success" : "Failed") . " - Rows affected: " . $stmt->rowCount());
                    
                    $stmt = $pdo->prepare("DELETE FROM rubric_rows WHERE rubric_id = ?");
                    $deleteRowsResult = $stmt->execute([$id]);
                    error_log("Delete rubric rows result: " . ($deleteRowsResult ? "Success" : "Failed") . " - Rows affected: " . $stmt->rowCount());
                    
                    // Insert new quality criteria
                    if ($quality_criteria_count > 0) {
                        $stmt = $pdo->prepare("INSERT INTO rubric_quality_criteria (rubric_id, quality_level, points, description) VALUES (?, ?, ?, ?)");
                        
                        foreach ($_POST['quality_level'] as $index => $level) {
                            if (isset($_POST['points'][$index])) {
                                $qcResult = $stmt->execute([
                                    $id,
                                    $index + 1,
                                    $_POST['points'][$index],
                                    $_POST['quality_description'][$index] ?? ''
                                ]);
                                error_log("Insert quality criteria level $index result: " . ($qcResult ? "Success" : "Failed"));
                            }
                        }
                    }
                    
                    // Insert new rubric rows
                    if ($criteria_count > 0) {
                        $stmt = $pdo->prepare("INSERT INTO rubric_rows (rubric_id, description, order_index) VALUES (?, ?, ?)");
                        
                        foreach ($_POST['criterion_description'] as $index => $description) {
                            $rowResult = $stmt->execute([
                                $id,
                                $description,
                                $index
                            ]);
                            error_log("Insert rubric row $index result: " . ($rowResult ? "Success" : "Failed"));
                        }
                    }
                    
                    // Don't commit here, let the outer try-catch handle it
                    // $pdo->commit();
                    $response['success'] = true;
                    $response['message'] = 'Rubric updated successfully';
                    error_log("Rubric update processing completed successfully");
                    
                    // Explicitly set result to true
                    $result = true;
                } catch (Exception $e) {
                    // Don't roll back here, let the outer try-catch handle it
                    // if ($pdo->inTransaction()) {
                    //     $pdo->rollBack();
                    //     error_log("Rolled back transaction due to error");
                    // }
                    $response['message'] = 'Database error: ' . $e->getMessage();
                    error_log("Error updating rubric: " . $e->getMessage());
                    $result = false;
                    // Re-throw to the outer catch block
                    throw $e;
                }
            } else {
                // Handle other tables as before
                $updateData = [];
                $params = [];
                $titleApproved = false; // Initialize approval status
                foreach ($_POST as $key => $value) {
                    if ($key !== 'table' && $key !== 'id') {
                if ($table === 'research_titles' && $key === 'approved_at') {
                            $updateData[] = "approved_at = ?";
                            $approvalValue = $value ? date('Y-m-d H:i:s') : null;
                            $params[] = $approvalValue;
                            
                            // Store approval status for notification later
                            $titleApproved = (bool)$value;
                        } else {
                            $updateData[] = "`$key` = ?";
                            $params[] = $value;
                        }
                    }
                }

                if ($table === 'research_titles' && !isset($_POST['approved_at'])) {
                    $updateData[] = "approved_at = ?";
                    $params[] = null;
                }

                $params[] = $id;

                $sql = "UPDATE `$table` SET " . implode(', ', $updateData) . " WHERE id = ?";
                error_log("SQL Query: $sql");
                error_log("Parameters: " . print_r($params, true));

                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute($params);
            }

            if ($result) {
                $pdo->commit();
                $response['success'] = true;
                $response['message'] = 'Item updated successfully';
            } else {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $response['message'] = 'Failed to update item';
                // Check if $stmt is defined before trying to access errorInfo()
                if (isset($stmt) && $stmt !== null) {
                    error_log("PDO Error Info: " . print_r($stmt->errorInfo(), true));
                } else {
                    error_log("Update failed but no statement available for error info");
                }
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $response['message'] = 'Database error: ' . $e->getMessage();
            error_log("PDO Exception: " . $e->getMessage());
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $response['message'] = $e->getMessage();
            error_log("Exception: " . $e->getMessage());
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $response['message'] = 'Unexpected server error.';
            error_log("Throwable: " . $e->getMessage());
        }
    } else {
        $response['message'] = 'Missing table or id';
        error_log("Missing table or id. Table: '$table', ID: '$id'");
    }
} else {
    $response['message'] = 'Invalid request method';
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
}

// Log the final response
error_log("Final response: " . json_encode($response));
error_log("=== END UPDATE ITEM REQUEST ===");

header('Content-Type: application/json');
echo json_encode($response);
