<?php
/**
 * Database Schema Checker
 * Shows exact structure of relevant tables
 */

require_once __DIR__ . '/assets/setup/db.inc.php';

echo "=== DATABASE SCHEMA ===\n\n";

try {
    // Get column info for users table
    echo "1. USERS TABLE STRUCTURE:\n";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "   - {$col['Field']}: {$col['Type']}";
        if ($col['Null'] === 'NO') echo " (NOT NULL)";
        if ($col['Default']) echo " DEFAULT {$col['Default']}";
        if ($col['Key'] === 'PRI') echo " PRIMARY KEY";
        echo "\n";
    }

    // Check section_professors table exists
    echo "\n2. SECTION_PROFESSORS TABLE STRUCTURE:\n";
    try {
        $stmt = $pdo->query("DESCRIBE section_professors");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($columns)) {
            foreach ($columns as $col) {
                echo "   - {$col['Field']}: {$col['Type']}";
                if ($col['Null'] === 'NO') echo " (NOT NULL)";
                if ($col['Key'] === 'PRI') echo " PRIMARY KEY";
                echo "\n";
            }
        } else {
            echo "   ⚠ Table exists but has no columns (strange!)\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Table doesn't exist!\n";
    }

    // Get data counts
    echo "\n3. DATA COUNTS:\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users");
    $result = $stmt->fetch();
    echo "   - Total users: {$result['cnt']}\n";

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users WHERE usertype = 0");
    $result = $stmt->fetch();
    echo "   - Admins: {$result['cnt']}\n";

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users WHERE usertype = 1");
    $result = $stmt->fetch();
    echo "   - Students: {$result['cnt']}\n";

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users WHERE usertype = 2");
    $result = $stmt->fetch();
    echo "   - Professors/Faculty: {$result['cnt']}\n";

    // Check if section_professors has data
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM section_professors");
        $result = $stmt->fetch();
        echo "   - Section assignments: {$result['cnt']}\n";
    } catch (Exception $e) {
        echo "   - Section assignments: Table doesn't exist\n";
    }

    // Show students with their sections
    echo "\n4. SAMPLE STUDENTS WITH SECTION INFO:\n";
    $stmt = $pdo->query("SELECT id, first_name, last_name, usertype, section FROM users WHERE usertype = 1 LIMIT 10");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($students)) {
        foreach ($students as $s) {
            $sec = $s['section'] ? "'{$s['section']}'" : "NULL/Empty";
            echo "   - ID {$s['id']}: {$s['first_name']} {$s['last_name']} (Section: $sec)\n";
        }
    } else {
        echo "   No students found!\n";
    }

    // Show professors with their section assignments
    echo "\n5. PROFESSORS AND THEIR SECTIONS:\n";
    $stmt = $pdo->query("
        SELECT u.id, u.first_name, u.last_name, GROUP_CONCAT(sp.section SEPARATOR ', ') as sections
        FROM users u
        LEFT JOIN section_professors sp ON u.id = sp.professor_id
        WHERE u.usertype = 2
        LIMIT 10
    ");
    $profs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($profs)) {
        foreach ($profs as $p) {
            $secs = $p['sections'] ? $p['sections'] : "No sections assigned";
            echo "   - ID {$p['id']}: {$p['first_name']} {$p['last_name']} → $secs\n";
        }
    } else {
        echo "   No professors found!\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

?>
