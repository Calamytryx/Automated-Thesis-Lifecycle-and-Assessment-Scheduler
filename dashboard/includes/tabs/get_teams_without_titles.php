<?php
// Include the database connection
require_once '../../../assets/setup/db.inc.php';

// Set header for JSON response
header('Content-Type: application/json');

try {
    // Query to find teams without research titles
    // Uses LEFT JOIN to find teams with no entries in research_titles table
    $sql = "SELECT t.id, t.name, t.program 
            FROM teams t 
            LEFT JOIN research_titles rt ON t.id = rt.team_id 
            WHERE rt.id IS NULL 
            ORDER BY t.name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $teams = $stmt->fetchAll();
    
    // Return the teams as JSON
    echo json_encode($teams);
} catch (PDOException $e) {
    // Return error message if database query fails
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}