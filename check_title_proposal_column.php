<?php
/**
 * Check Teams Table Structure
 * Verify if title_proposal column exists
 */

require_once __DIR__ . '/assets/setup/db.inc.php';

try {
    $stmt = $pdo->query("DESCRIBE teams");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $title_proposal_exists = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'title_proposal') {
            $title_proposal_exists = true;
            break;
        }
    }
    
    if ($title_proposal_exists) {
        echo "✅ Column 'title_proposal' EXISTS in teams table\n";
        exit(0);
    } else {
        echo "❌ Column 'title_proposal' MISSING from teams table\n";
        echo "\nCurrent columns:\n";
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(2);
}
?>
