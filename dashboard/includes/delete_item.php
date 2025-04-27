<?php
/**
 * This script handles the deletion of a row from a specified table in the database.
 * 
 * 
 * Dependencies:
 * - Requires the database connection setup file located at ../../assets/setup/db.inc.php
 * 
 * Request Method:
 * - POST
 * 
 * POST Parameters:
 * - id: The ID of the row to be deleted.
 * - table: The name of the table from which the row should be deleted.
 * 
 * Allowed Tables:
 * - users
 * - thesis_topics
 * - research_titles
 * - defense_schedules
 * - rubrics
 * - teams
 * - requirements
 * - evaluations
 * - env_variables
 * - programs
 * 
 * Functionality:
 * - Validates the table name against a list of allowed tables.
 * - Disables foreign key checks.
 * - Deletes the row with the specified ID from the specified table.
 * - Re-enables foreign key checks.
 * - Returns a JSON response indicating success or failure.
 * 
 * Error Handling:
 * - Catches exceptions and returns a JSON response with the error message.
 * - Ensures foreign key checks are re-enabled in case of an error.
 */

require_once __DIR__ . '/../../assets/setup/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $table = $_POST['table'];
    
    $allowedTables = ['users', 'thesis_topics', 'research_titles', 'defense_schedules', 'rubrics', 'teams', 'requirements', 'evaluations', 'env_variables', 'programs'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        exit;
    }
    
    try {
        // Disable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        
        // Delete the row
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Re-enable foreign key checks in case of an error
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>