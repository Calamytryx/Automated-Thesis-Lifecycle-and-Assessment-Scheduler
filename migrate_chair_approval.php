<?php
/**
 * Migration: Add 'pending_chair' to approval_status enum in defense_schedules
 * This enables a 2-step approval workflow: Chair Review → Panelist Approval
 */
require_once 'assets/setup/db.inc.php';

try {
    // Step 1: Modify the enum to include 'pending_chair'
    $pdo->exec("ALTER TABLE defense_schedules MODIFY COLUMN approval_status ENUM('pending_chair','pending','approved','rejected') DEFAULT 'pending_chair'");
    echo "1. approval_status enum updated to include 'pending_chair'\n";

    // Step 2: Verify column
    $stmt = $pdo->query("SHOW COLUMNS FROM defense_schedules LIKE 'approval_status'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Column type: {$col['Type']}, Default: {$col['Default']}\n";

    // Step 3: Check current data
    $stmt = $pdo->query("SELECT approval_status, COUNT(*) as cnt FROM defense_schedules GROUP BY approval_status");
    echo "2. Current approval_status distribution:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   {$row['approval_status']}: {$row['cnt']}\n";
    }

    echo "\nMigration complete!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
