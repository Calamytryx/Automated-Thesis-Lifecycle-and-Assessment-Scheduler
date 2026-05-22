<?php
require 'assets/setup/db.inc.php';

echo "<h3>Teams with members:</h3><pre>";
$stmt = $pdo->query("SELECT t.id, t.name, t.program as team_program, tm.role, tm.user_id, u.program as user_program 
FROM teams t 
LEFT JOIN team_members tm ON tm.team_id = t.id 
LEFT JOIN users u ON u.id = tm.user_id 
WHERE tm.role IS NOT NULL
LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";

echo "<h3>Programs table sample:</h3><pre>";
$stmt2 = $pdo->query("SELECT id, name, college FROM programs LIMIT 10");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";

echo "<h3>Test the full join (Leader match):</h3><pre>";
$stmt3 = $pdo->query("
SELECT t.id as team_id, t.name as team_name, 
       tm.role, 
       u.id as user_id, u.first_name, u.program as user_program,
       p.id as program_id, p.name as program_name, p.college
FROM teams t 
LEFT JOIN team_members tm ON tm.team_id = t.id AND LOWER(tm.role) = 'leader'
LEFT JOIN users u ON u.id = tm.user_id
LEFT JOIN programs p ON p.name = u.program OR CAST(u.program AS UNSIGNED) = p.id
LIMIT 10
");
print_r($stmt3->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
