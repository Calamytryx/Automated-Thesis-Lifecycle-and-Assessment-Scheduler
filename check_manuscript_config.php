<?php
/**
 * Diagnostic script to check program_manuscript_requirements configuration
 */
require 'assets/setup/db.inc.php';

echo "<!DOCTYPE html><html><head><title>Manuscript Config Check</title>";
echo "<style>body{font-family:sans-serif;margin:20px;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background-color:#4CAF50;color:white;}.warning{color:orange;}.error{color:red;}.success{color:green;}</style>";
echo "</head><body>";

echo "<h1>Program Manuscript Requirements Configuration Check</h1>";

// Check if table exists
try {
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'program_manuscript_requirements'");
    if ($tableCheck->rowCount() === 0) {
        echo "<p class='error'>❌ ERROR: Table 'program_manuscript_requirements' does not exist!</p>";
        echo "<p>Run the migration script: <code>assets/setup/20251122_program_manuscript_mapping.sql</code></p>";
        exit;
    }
    echo "<p class='success'>✓ Table 'program_manuscript_requirements' exists</p>";
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Check programs
echo "<h2>Programs in Database</h2>";
$programsStmt = $pdo->query("SELECT id, name, college FROM programs ORDER BY name");
$programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($programs)) {
    echo "<p class='error'>❌ No programs found!</p>";
} else {
    echo "<table><tr><th>ID</th><th>Name</th><th>College</th></tr>";
    foreach ($programs as $prog) {
        echo "<tr><td>{$prog['id']}</td><td>{$prog['name']}</td><td>{$prog['college']}</td></tr>";
    }
    echo "</table>";
}

// Check requirements (manuscripts)
echo "<h2>Requirements (Potential Manuscripts)</h2>";
$reqStmt = $pdo->query("SELECT id, name, is_defense_manuscript, requirement_type FROM requirements ORDER BY id");
$requirements = $reqStmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($requirements)) {
    echo "<p class='error'>❌ No requirements found!</p>";
} else {
    echo "<table><tr><th>ID</th><th>Name</th><th>Is Defense Manuscript?</th><th>Type</th></tr>";
    foreach ($requirements as $req) {
        $isManuscript = $req['is_defense_manuscript'] ? '✓' : '';
        echo "<tr><td>{$req['id']}</td><td>{$req['name']}</td><td>{$isManuscript}</td><td>{$req['requirement_type']}</td></tr>";
    }
    echo "</table>";
}

// Check program_manuscript_requirements entries
echo "<h2>Program Manuscript Requirements Configuration</h2>";
$configStmt = $pdo->query("
    SELECT 
        pmr.id,
        pmr.requirement_id,
        r.name as requirement_name,
        pmr.program_id,
        p.name as program_name,
        pmr.defense_type,
        pmr.is_required,
        pmr.visibility_to_panelist
    FROM program_manuscript_requirements pmr
    LEFT JOIN requirements r ON pmr.requirement_id = r.id
    LEFT JOIN programs p ON pmr.program_id = p.id
    ORDER BY p.name, pmr.defense_type, r.name
");
$configs = $configStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($configs)) {
    echo "<p class='error'>❌ NO CONFIGURATION FOUND!</p>";
    echo "<p class='warning'>⚠️ This is why decision-support is falling back to requirement_id = 5!</p>";
    echo "<p><strong>To fix this:</strong></p>";
    echo "<ol>";
    echo "<li>Go to Dashboard → Requirements → Manuscript Requirements</li>";
    echo "<li>Select a defense type (e.g., 'Title Proposal')</li>";
    echo "<li>Select programs that should use this manuscript</li>";
    echo "<li>Click 'Save Program Configuration'</li>";
    echo "</ol>";
} else {
    echo "<p class='success'>✓ Found " . count($configs) . " configuration entries</p>";
    echo "<table><tr><th>ID</th><th>Requirement</th><th>Program</th><th>Defense Type</th><th>Required?</th><th>Visible to Panelist?</th></tr>";
    foreach ($configs as $cfg) {
        $required = $cfg['is_required'] ? '✓' : '';
        $visible = $cfg['visibility_to_panelist'] ? '✓' : '';
        echo "<tr>";
        echo "<td>{$cfg['id']}</td>";
        echo "<td>{$cfg['requirement_name']} (ID: {$cfg['requirement_id']})</td>";
        echo "<td>{$cfg['program_name']} (ID: {$cfg['program_id']})</td>";
        echo "<td>{$cfg['defense_type']}</td>";
        echo "<td>{$required}</td>";
        echo "<td>{$visible}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check a sample team
echo "<h2>Sample Team Check</h2>";
$teamStmt = $pdo->query("SELECT id, name, program FROM teams LIMIT 5");
$teams = $teamStmt->fetchAll(PDO::FETCH_ASSOC);
if (!empty($teams)) {
    echo "<table><tr><th>Team ID</th><th>Team Name</th><th>Program (stored value)</th><th>Expected Requirement</th></tr>";
    foreach ($teams as $team) {
        // Try to find matching config
        $matchStmt = $pdo->prepare("
            SELECT pmr.requirement_id, r.name as req_name
            FROM program_manuscript_requirements pmr
            JOIN programs p ON pmr.program_id = p.id
            JOIN requirements r ON pmr.requirement_id = r.id
            WHERE LOWER(TRIM(p.name)) = LOWER(TRIM(?))
              AND pmr.defense_type = 'title_proposal'
              AND pmr.is_required = 1
              AND pmr.visibility_to_panelist = 1
            LIMIT 1
        ");
        $matchStmt->execute([$team['program']]);
        $match = $matchStmt->fetch(PDO::FETCH_ASSOC);
        
        $expected = $match ? "{$match['req_name']} (ID: {$match['requirement_id']})" : "<span class='error'>No match found!</span>";
        
        echo "<tr>";
        echo "<td>{$team['id']}</td>";
        echo "<td>{$team['name']}</td>";
        echo "<td>{$team['program']}</td>";
        echo "<td>{$expected}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "</body></html>";
?>
