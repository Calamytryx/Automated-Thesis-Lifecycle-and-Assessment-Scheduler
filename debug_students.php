<?php
session_start();
$_SESSION['id'] = 1;  // Assume professor ID is 1
$_SESSION['usertype'] = 2;

require_once __DIR__ . '/assets/setup/db.inc.php';
require_once __DIR__ . '/dashboard/includes/section_access.php';

echo "=== Database Check ===\n\n";

// Check students
$stmt = $pdo->query("SELECT id, first_name, last_name, username, section, usertype FROM users WHERE usertype = 1 LIMIT 10");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Students in database: " . count($students) . "\n";
foreach ($students as $s) {
    echo "  - ID: {$s['id']}, Name: {$s['first_name']} {$s['last_name']}, Section: {$s['section']}\n";
}

echo "\n\nCheck Professor Section Assignment:\n";
$stmt = $pdo->prepare("SELECT * FROM section_professors WHERE professor_id = ?");
$stmt->execute([1]);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Professor ID 1 section assignments: " . count($sections) . "\n";
foreach ($sections as $s) {
    echo "  - Section: {$s['section']}\n";
}

echo "\n\nCheck getAvailableStudentsForProfessor:\n";
$available = getAvailableStudentsForProfessor($pdo, 1);
echo "Available students for professor 1: " . count($available) . "\n";
foreach ($available as $s) {
    echo "  - ID: {$s['id']}, Name: {$s['first_name']} {$s['last_name']}, Section: {$s['section']}\n";
}

echo "\n\nCheck all users:\n";
$stmt = $pdo->query("SELECT id, first_name, last_name, usertype, section FROM users LIMIT 20");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total users in database: ";
$countStmt = $pdo->query("SELECT COUNT(*) as cnt FROM users");
$countResult = $countStmt->fetch();
echo $countResult['cnt'] . "\n";
echo "Breakdown:\n";
$typeStmt = $pdo->query("SELECT usertype, COUNT(*) as cnt FROM users GROUP BY usertype");
$types = $typeStmt->fetchAll();
foreach ($types as $t) {
    $typeStr = ($t['usertype'] == 0) ? 'Admin' : (($t['usertype'] == 1) ? 'Student' : 'Faculty');
    echo "  - $typeStr: {$t['cnt']}\n";
}

?>
