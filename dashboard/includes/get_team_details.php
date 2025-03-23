<?php
// Prevent PHP errors from breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Include database connection
    include '../../assets/setup/db.inc.php';

    header('Content-Type: application/json');

    // Check if team ID is provided
    if (!isset($_GET['team_id']) || empty($_GET['team_id'])) {
        echo json_encode(['success' => false, 'message' => 'Team ID is required']);
        exit;
    }

    $teamId = $_GET['team_id'];

    // Get team information
    $teamStmt = $pdo->prepare("SELECT * FROM teams WHERE id = ?");
    $teamStmt->execute([$teamId]);
    $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$team) {
        echo json_encode(['success' => false, 'message' => 'Team not found']);
        exit;
    }
    
    // Get research title
    $titleStmt = $pdo->prepare("
        SELECT * FROM research_titles 
        WHERE team_id = ? AND approved_at IS NOT NULL 
        ORDER BY approved_at DESC LIMIT 1
    ");
    $titleStmt->execute([$teamId]);
    $title = $titleStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get adviser - Updated to use first_name and last_name
    $adviserStmt = $pdo->prepare("
        SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name, u.email
        FROM users u
        WHERE u.id = ?
    ");
    $adviserStmt->execute([$team['adviser_id']]);
    $adviser = $adviserStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get team members - Updated to use first_name and last_name
    $membersStmt = $pdo->prepare("
        SELECT tm.*, CONCAT(u.first_name, ' ', u.last_name) as name, u.email
        FROM team_members tm
        JOIN users u ON tm.user_id = u.id
        WHERE tm.team_id = ?
    ");
    $membersStmt->execute([$teamId]);
    $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get defense schedule
    $defenseStmt = $pdo->prepare("
        SELECT * FROM defense_schedules
        WHERE team_id = ?
        ORDER BY schedule_date DESC
        LIMIT 1
    ");
    $defenseStmt->execute([$teamId]);
    $defense = $defenseStmt->fetch(PDO::FETCH_ASSOC);
    
    // Return response
    echo json_encode([
        'success' => true,
        'team' => $team,
        'title' => $title,
        'adviser' => $adviser,
        'members' => $members,
        'defense' => $defense
    ]);
    
} catch (Exception $e) {
    // Return any errors as JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 