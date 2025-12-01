<?php
session_start();
require 'assets/setup/db.inc.php';

// For a test team with title_proposal
$team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 1;

echo "<h2>Debug: Team $team_id - Requirements</h2>";

// 1. Get team info
$teamStmt = $pdo->prepare("SELECT id, program, program_status FROM teams WHERE id = ?");
$teamStmt->execute([$team_id]);
$team = $teamStmt->fetch(PDO::FETCH_ASSOC);
echo "<h3>Team Info:</h3>";
echo "<pre>";
print_r($team);
echo "</pre>";

// 2. Get program ID
if ($team) {
    $progStmt = $pdo->prepare("SELECT id, name FROM programs WHERE name COLLATE utf8mb4_unicode_ci = ? COLLATE utf8mb4_unicode_ci");
    $progStmt->execute([$team['program']]);
    $prog = $progStmt->fetch(PDO::FETCH_ASSOC);
    echo "<h3>Program Info:</h3>";
    echo "<pre>";
    print_r($prog);
    echo "</pre>";
    
    // 3. Get defense schedules
    $defStmt = $pdo->prepare("SELECT * FROM defense_schedules WHERE team_id = ? ORDER BY schedule_date DESC");
    $defStmt->execute([$team_id]);
    $defenses = $defStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Defense Schedules:</h3>";
    echo "<pre>";
    print_r($defenses);
    echo "</pre>";
    
    // 4. Get current defense type (as the function does)
    $currentDefType = 'title_proposal';
    if (!empty($defenses)) {
        $latest = $defenses[0];
        $currentDefType = $latest['defense_type'] ?? 'title_proposal';
    }
    echo "<h3>Current Defense Type: $currentDefType</h3>";
    
    // 5. Check program_manuscript_requirements with explicit collation
    if ($prog) {
        $mansStmt = $pdo->prepare("
            SELECT pmr.*, r.name
            FROM program_manuscript_requirements pmr
            JOIN requirements r ON pmr.requirement_id = r.id
            WHERE pmr.program_id = ? 
              AND pmr.defense_type COLLATE utf8mb4_unicode_ci = ? COLLATE utf8mb4_unicode_ci
              AND pmr.is_required = 1
        ");
        $mansStmt->execute([$prog['id'], $currentDefType]);
        $mans = $mansStmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Manuscripts for $currentDefType:</h3>";
        echo "<pre>";
        print_r($mans);
        echo "</pre>";
    }
}
?>
