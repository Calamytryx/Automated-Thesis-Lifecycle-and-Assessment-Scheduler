<?php
/**
 * Diagnostic script to check scheduler state
 * Run this to see what teams exist and what schedules they have
 */

require_once __DIR__ . '/assets/setup/db.inc.php';

echo "=== SCHEDULER DIAGNOSTIC ===\n\n";

// Check if next_defense_type column exists
echo "1. Checking if next_defense_type column exists...\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM teams LIKE 'next_defense_type'");
    $columnExists = $stmt->fetch() !== false;
    echo $columnExists ? "   ✅ Column exists\n" : "   ❌ Column does NOT exist - run: mysql < add_next_defense_type_column.sql\n";
} catch (PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n2. Checking teams...\n";
try {
    $stmt = $pdo->query("
        SELECT t.id, t.name, t.program, 
               COALESCE(t.next_defense_type, 'title_proposal') as next_defense_type
        FROM teams t
        LIMIT 10
    ");
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Found " . count($teams) . " teams (showing first 10):\n";
    foreach ($teams as $team) {
        echo "   - Team {$team['id']}: {$team['name']} | Next defense: {$team['next_defense_type']}\n";
    }
} catch (PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n3. Checking existing schedules...\n";
try {
    $currentDate = date('Y-m-d');
    $stmt = $pdo->query("
        SELECT ds.id, ds.team_id, t.name as team_name, 
               ds.defense_date, ds.defense_type, ds.defense_status, ds.status
        FROM defense_schedules ds
        JOIN teams t ON ds.team_id = t.id
        WHERE ds.status = 'scheduled'
        ORDER BY ds.defense_date DESC
        LIMIT 20
    ");
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($schedules)) {
        echo "   ℹ️  No scheduled defenses found\n";
    } else {
        echo "   Found " . count($schedules) . " scheduled defenses (showing last 20):\n";
        foreach ($schedules as $sched) {
            $isPast = $sched['defense_date'] < $currentDate ? '(PAST)' : '(FUTURE)';
            $status = $sched['defense_status'] ?? 'pending';
            echo "   - Schedule {$sched['id']}: Team {$sched['team_id']} ({$sched['team_name']}) | {$sched['defense_date']} $isPast | Type: {$sched['defense_type']} | Status: $status\n";
        }
    }
} catch (PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n4. Checking for past schedules that need progression...\n";
try {
    $currentDate = date('Y-m-d');
    $stmt = $pdo->query("
        SELECT ds.team_id, t.name, ds.defense_date, ds.defense_type, ds.defense_status
        FROM defense_schedules ds
        JOIN teams t ON ds.team_id = t.id
        WHERE ds.status = 'scheduled'
        AND ds.defense_date < '$currentDate'
        AND ds.defense_status IN ('passed', 'failed')
        ORDER BY ds.defense_date DESC
    ");
    $pastSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($pastSchedules)) {
        echo "   ℹ️  No past schedules needing progression\n";
    } else {
        echo "   Found " . count($pastSchedules) . " past schedules ready for progression:\n";
        foreach ($pastSchedules as $past) {
            $nextType = '';
            if ($past['defense_status'] === 'passed') {
                switch ($past['defense_type']) {
                    case 'title_proposal': $nextType = '→ title_defense'; break;
                    case 'title_defense': $nextType = '→ final_defense'; break;
                    case 'final_defense': $nextType = '→ (complete)'; break;
                }
            } else {
                $nextType = '→ re_defense';
            }
            echo "   - Team {$past['team_id']} ({$past['name']}): {$past['defense_date']} | {$past['defense_type']} | {$past['defense_status']} $nextType\n";
        }
    }
} catch (PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n5. Recent scheduler log entries...\n";
$logFile = __DIR__ . '/dashboard/includes/php_errors.log';
if (file_exists($logFile)) {
    $logs = file($logFile);
    $schedulerLogs = array_filter($logs, function($line) {
        return strpos($line, 'SCHEDULER') !== false || strpos($line, 'saveScheduleToDatabase') !== false || strpos($line, 'removeExistingSchedules') !== false;
    });
    $recentLogs = array_slice($schedulerLogs, -10);
    
    if (empty($recentLogs)) {
        echo "   ℹ️  No recent scheduler logs found\n";
    } else {
        echo "   Last 10 scheduler log entries:\n";
        foreach ($recentLogs as $log) {
            echo "   " . trim($log) . "\n";
        }
    }
} else {
    echo "   ℹ️  Log file not found at: $logFile\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
