-- Migration: Add defense_type column to defense_schedules table
-- Date: December 2, 2025
-- Purpose: Fix "Unknown column 'defense_type' in 'where clause'" error

-- Check if column exists before adding
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'defense_schedules' 
    AND COLUMN_NAME = 'defense_type'
);

-- Add defense_type column if it doesn't exist
SET @query = IF(
    @col_exists = 0,
    'ALTER TABLE `defense_schedules` ADD COLUMN `defense_type` ENUM(''title_proposal'', ''title_defense'', ''final_defense'', ''re_defense'', ''general'') DEFAULT ''general'' COMMENT ''Type of defense being scheduled'' AFTER `room`',
    'SELECT ''Column defense_type already exists'' as result'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index if column was just created
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'defense_schedules' 
    AND INDEX_NAME = 'idx_defense_type'
);

SET @query2 = IF(
    @idx_exists = 0,
    'ALTER TABLE `defense_schedules` ADD KEY `idx_defense_type` (`defense_type`)',
    'SELECT ''Index idx_defense_type already exists'' as result'
);

PREPARE stmt2 FROM @query2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Add related_requirement_files column if it doesn't exist
SET @col_exists2 = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'defense_schedules' 
    AND COLUMN_NAME = 'related_requirement_files'
);

SET @query3 = IF(
    @col_exists2 = 0,
    'ALTER TABLE `defense_schedules` ADD COLUMN `related_requirement_files` JSON COMMENT ''JSON array of team_requirement_files IDs for multi-submission requirements'' AFTER `defense_type`',
    'SELECT ''Column related_requirement_files already exists'' as result'
);

PREPARE stmt3 FROM @query3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- Verify the changes
SELECT 'Verification - defense_schedules structure:' as status;
DESCRIBE defense_schedules;

SELECT 'Sample data with defense_type:' as status;
SELECT id, team_id, schedule_date, defense_type, room 
FROM defense_schedules 
LIMIT 5;
