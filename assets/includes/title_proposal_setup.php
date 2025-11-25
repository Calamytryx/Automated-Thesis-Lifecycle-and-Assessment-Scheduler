<?php
/**
 * Automatic Title Proposal Setup
 * Run automatically when the dashboard is first accessed after deployment
 * This ensures the database column exists before any operations
 */

// Only run if not already set
if (!function_exists('ensure_title_proposal_column')) {
    function ensure_title_proposal_column($pdo) {
        try {
            // Check if the column already exists
            $dbName = null;
            try {
                $stmt = $pdo->query("SELECT DATABASE()");
                $dbName = $stmt->fetchColumn();
            } catch (Exception $e) {
                // If we can't get DB name, just try the ALTER anyway
            }
            
            if ($dbName) {
                $stmt = $pdo->prepare("
                    SELECT COLUMN_NAME 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'teams' AND COLUMN_NAME = 'title_proposal'
                ");
                $stmt->execute([$dbName]);
                
                if ($stmt->rowCount() > 0) {
                    // Column already exists
                    return true;
                }
            }
            
            // Try to add the column (will fail silently if it already exists)
            try {
                $pdo->exec("ALTER TABLE teams ADD COLUMN title_proposal TINYINT NOT NULL DEFAULT 0 COMMENT 'Mark team as title proposal - professor role will be automatic'");
            } catch (PDOException $e) {
                // If it's a duplicate column error, that's fine
                if (strpos($e->getMessage(), '1060') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
                    return true;
                }
                // For other errors, log but don't break
                error_log("Title Proposal Migration Error: " . $e->getMessage());
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Title Proposal Column Check Error: " . $e->getMessage());
            return false;
        }
    }
}
?>
