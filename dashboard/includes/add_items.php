<?php
require_once __DIR__ . '/../../assets/setup/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'];
    
    // Special handling for rubrics
    if($table === 'rubrics'){
        $pdo->beginTransaction();
        try {
            // Validate structure JSON
            $structure = isset($_POST['structure']) ? $_POST['structure'] : null;
            
            if ($structure) {
                $decoded = json_decode($structure);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid rubric structure: ' . json_last_error_msg());
                }
            }
            
            // Insert into rubrics table
            $stmt = $pdo->prepare("INSERT INTO rubrics (name, description, structure, created_by) VALUES (:name, :description, :structure, :created_by)");
            $result = $stmt->execute([
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'structure' => $structure,
                'created_by' => $_SESSION['id'] ?? null
            ]);
            
            $pdo->commit();
            
            // Send proper JSON response
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Rubric added successfully']);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}

// If we get here, something went wrong
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
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