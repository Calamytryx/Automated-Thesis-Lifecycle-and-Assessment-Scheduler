<?php
require 'assets/setup/db.inc.php';

try {
    // Check if column exists
    $stmt = $pdo->prepare("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME='teams' AND COLUMN_NAME='title_proposal'
    ");
    $stmt->execute();
    $exists = $stmt->fetch();
    
    if (!$exists) {
        // Add column
        $pdo->exec("ALTER TABLE teams ADD COLUMN title_proposal TINYINT DEFAULT 0");
        echo "✅ Column 'title_proposal' added successfully to teams table";
    } else {
        echo "ℹ️ Column 'title_proposal' already exists";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
