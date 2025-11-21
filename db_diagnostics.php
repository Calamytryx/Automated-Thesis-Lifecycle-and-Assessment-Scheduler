<?php
session_start();
require 'assets/setup/db.inc.php';

echo "<h2>Database Diagnostics</h2>";

// Check requirements table structure
echo "<h3>Requirements Table Structure:</h3>";
$struct = $pdo->query("DESC requirements");
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
foreach ($struct as $row) {
    echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . $row['Null'] . "</td><td>" . ($row['Key'] ?? '') . "</td></tr>";
}
echo "</table>";

// Check sample requirements
echo "<h3>Sample Requirements:</h3>";
$reqs = $pdo->query("SELECT id, name, allow_multiple_submissions, max_submissions FROM requirements LIMIT 5");
echo "<pre>";
print_r($reqs->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";

// Check team_requirement_files table existence
echo "<h3>Checking team_requirement_files table:</h3>";
try {
    $test = $pdo->query("DESC team_requirement_files LIMIT 1");
    if ($test) {
        echo "<p style='color:green'>✓ team_requirement_files table EXISTS</p>";
        echo "<pre>";
        print_r($test->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ team_requirement_files table MISSING: " . $e->getMessage() . "</p>";
}

// Check program_manuscript_requirements collations
echo "<h3>program_manuscript_requirements table:</h3>";
$collation = $pdo->query("SHOW CREATE TABLE program_manuscript_requirements");
echo "<pre>";
print_r($collation->fetchAll());
echo "</pre>";

?>
