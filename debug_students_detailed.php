<?php
/**
 * Debug Script for Students List Issue
 * This script helps diagnose why students aren't showing up
 */

session_start();

// Simulate being logged in as a professor
$_SESSION['id'] = isset($_GET['prof_id']) ? intval($_GET['prof_id']) : 1;
$_SESSION['usertype'] = 2;  // Faculty/Professor

require_once __DIR__ . '/assets/setup/db.inc.php';
require_once __DIR__ . '/dashboard/includes/section_access.php';

$profId = $_SESSION['id'];

echo "=== Students Debug Report ===\n";
echo "Professor ID: $profId\n";
echo "Professor Usertype: {$_SESSION['usertype']}\n\n";

try {
    // 1. Check if professor exists
    echo "1. Checking if professor exists...\n";
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, usertype, section FROM users WHERE id = ? AND usertype = 2");
    $stmt->execute([$profId]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($prof) {
        echo "   ✓ Professor found: {$prof['first_name']} {$prof['last_name']}\n";
        echo "   - Section field value: " . ($prof['section'] ? "'{$prof['section']}'" : "NULL/Empty") . "\n";
    } else {
        echo "   ✗ Professor not found!\n";
    }

    // 2. Check professor's section assignment in section_professors table
    echo "\n2. Checking section_professors table for professor assignments...\n";
    $stmt = $pdo->prepare("SELECT * FROM section_professors WHERE professor_id = ?");
    $stmt->execute([$profId]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($assignments)) {
        echo "   ✓ Found " . count($assignments) . " section assignment(s):\n";
        foreach ($assignments as $assign) {
            echo "     - Section: '{$assign['section']}'\n";
        }
    } else {
        echo "   ⚠ No section assignments found in section_professors table\n";
        echo "   (Professor will see ALL students, not filtered by section)\n";
    }

    // 3. Check getProfessorSections function
    echo "\n3. Testing getProfessorSections() function...\n";
    $sections = getProfessorSections($pdo, $profId);
    echo "   Returned " . count($sections) . " section(s): " . json_encode($sections) . "\n";

    // 4. Check total students in database
    echo "\n4. Checking total students in database...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users WHERE usertype = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalStudents = $result['cnt'];
    echo "   Total students: $totalStudents\n";

    if ($totalStudents > 0) {
        echo "   Sample students:\n";
        $stmt = $pdo->query("SELECT id, first_name, last_name, section FROM users WHERE usertype = 1 LIMIT 5");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($samples as $s) {
            echo "     - ID: {$s['id']}, Name: {$s['first_name']} {$s['last_name']}, Section: " . ($s['section'] ? "'{$s['section']}'" : "NULL/Empty") . "\n";
        }
    } else {
        echo "   ✗ No students in database!\n";
    }

    // 5. Check students by section
    echo "\n5. Checking students by section...\n";
    $stmt = $pdo->query("SELECT DISTINCT section, COUNT(*) as cnt FROM users WHERE usertype = 1 GROUP BY section");
    $bySections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($bySections)) {
        foreach ($bySections as $row) {
            $sec = $row['section'] ? "'{$row['section']}'" : "NULL/Empty";
            echo "   - Section $sec: {$row['cnt']} students\n";
        }
    }

    // 6. Test getAvailableStudentsForProfessor
    echo "\n6. Testing getAvailableStudentsForProfessor() function...\n";
    $available = getAvailableStudentsForProfessor($pdo, $profId);
    echo "   Returned " . count($available) . " students\n";
    
    if (!empty($available)) {
        echo "   Sample available students:\n";
        foreach (array_slice($available, 0, 5) as $s) {
            echo "     - ID: {$s['id']}, Name: {$s['first_name']} {$s['last_name']}, Section: {$s['section']}\n";
        }
    } else {
        echo "   ✗ No students returned!\n";
    }

    // 7. Run the exact SQL query that would be used
    echo "\n7. Running the exact SQL queries from getAvailableStudentsForProfessor()...\n";
    
    if (empty($sections)) {
        echo "   (No sections assigned, would query all students)\n";
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, username, section
            FROM users
            WHERE usertype = 1
            ORDER BY first_name, last_name
        ");
        $stmt->execute();
    } else {
        echo "   (Has sections assigned: " . json_encode($sections) . ")\n";
        $placeholders = implode(',', array_fill(0, count($sections), '?'));
        $sql = "
            SELECT id, first_name, last_name, username, section
            FROM users
            WHERE usertype = 1 AND section IN ($placeholders)
            ORDER BY section, first_name, last_name
        ";
        echo "   SQL: $sql\n";
        echo "   Params: " . json_encode($sections) . "\n";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($sections);
    }
    
    $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Query returned: " . count($queryResults) . " students\n";
    if (!empty($queryResults)) {
        echo "   Sample results:\n";
        foreach (array_slice($queryResults, 0, 3) as $s) {
            echo "     - {$s['first_name']} {$s['last_name']} (Section: " . ($s['section'] ? "'{$s['section']}'" : "NULL") . ")\n";
        }
    }

    // 8. Summary and recommendations
    echo "\n=== SUMMARY ===\n";
    echo "Total students: $totalStudents\n";
    echo "Students returned: " . count($available) . "\n";
    
    if (count($available) === 0 && $totalStudents > 0) {
        echo "\n⚠ PROBLEM DETECTED: Students exist but none are being returned!\n\n";
        echo "Possible causes:\n";
        
        if (!empty($sections)) {
            echo "1. Students in database don't have the right section values\n";
            echo "   - Professor assigned sections: " . json_encode($sections) . "\n";
            echo "   - Check if students have matching values in 'section' field\n";
        } else {
            echo "1. getAvailableStudentsForProfessor() returned empty even with no sections\n";
            echo "   - This shouldn't happen - should return all students\n";
            echo "   - Check for database connection issues\n";
        }
        
        echo "\n2. Check if students have null/empty section field\n";
        echo "3. Check if section names don't match between users and section_professors\n";
    } else if (count($available) > 0) {
        echo "\n✓ SUCCESS: Students are being returned correctly!\n";
        echo "Check the JavaScript to ensure the response is being handled properly.\n";
    }

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

?>
