<?php
require_once '../assets/setup/db.inc.php';

echo "<h3>Sections with student counts:</h3>";
$stmt = $pdo->query("SELECT section, COUNT(*) as count FROM users WHERE section IS NOT NULL GROUP BY section ORDER BY section");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['section'] . ': ' . $row['count'] . " students<br>";
}

echo "<br><h3>Teams with their member sections:</h3>";
$stmt = $pdo->query("
    SELECT t.id, t.name, GROUP_CONCAT(DISTINCT u.section) as sections
    FROM teams t
    JOIN team_members tm ON t.id = tm.team_id
    JOIN users u ON tm.user_id = u.id
    GROUP BY t.id
    ORDER BY t.id
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Team " . $row['id'] . " (" . ($row['name'] ?: 'Unnamed') . "): Sections [" . $row['sections'] . "]<br>";
}

echo "<br><h3>Team count for IT401:</h3>";
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT t.id) as total 
    FROM teams t
    JOIN team_members tm ON t.id = tm.team_id
    JOIN users u ON tm.user_id = u.id
    WHERE u.section = ?
");
$stmt->execute(['IT401']);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "IT401: " . $result['total'] . " teams<br>";

echo "<br><h3>Team count for IT403:</h3>";
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT t.id) as total 
    FROM teams t
    JOIN team_members tm ON t.id = tm.team_id
    JOIN users u ON tm.user_id = u.id
    WHERE u.section = ?
");
$stmt->execute(['IT403']);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "IT403: " . $result['total'] . " teams<br>";
