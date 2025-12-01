-- Fix for Rubric Groups to support defense type and program filtering (Simplified)
-- Date: December 2, 2025

-- Step 1: Check if defense_type column exists in rubric_groups
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'rubric_groups' 
    AND COLUMN_NAME = 'defense_type'
);

-- Add defense_type column if it doesn't exist
SET @query1 = IF(
    @col_exists = 0,
    'ALTER TABLE `rubric_groups` ADD COLUMN `defense_type` ENUM(''title_proposal'',''title_defense'',''final_defense'',''re_defense'',''general'') DEFAULT ''general'' AFTER `description`',
    'SELECT ''Column defense_type already exists in rubric_groups'' as result'
);

PREPARE stmt1 FROM @query1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

-- Step 2: Check if program_id column exists
SET @col_exists2 = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'rubric_groups' 
    AND COLUMN_NAME = 'program_id'
);

-- Add program_id column if it doesn't exist
SET @query2 = IF(
    @col_exists2 = 0,
    'ALTER TABLE `rubric_groups` ADD COLUMN `program_id` INT(11) DEFAULT NULL AFTER `defense_type`',
    'SELECT ''Column program_id already exists in rubric_groups'' as result'
);

PREPARE stmt2 FROM @query2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Step 3: Add index if it doesn't exist
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'rubric_groups' 
    AND INDEX_NAME = 'idx_defense_program'
);

SET @query3 = IF(
    @idx_exists = 0,
    'ALTER TABLE `rubric_groups` ADD KEY `idx_defense_program` (`defense_type`, `program_id`)',
    'SELECT ''Index idx_defense_program already exists'' as result'
);

PREPARE stmt3 FROM @query3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- Step 4: Update existing rubric groups with appropriate defense types
UPDATE `rubric_groups` SET `defense_type` = 'title_proposal' WHERE `id` = 2 OR `name` LIKE '%Title Proposal%';
UPDATE `rubric_groups` SET `defense_type` = 'final_defense' WHERE `id` = 5 OR `name` LIKE '%Final Defense%';
UPDATE `rubric_groups` SET `defense_type` = 're_defense' WHERE `id` = 6 OR `name` LIKE '%Re-Presentation%';
UPDATE `rubric_groups` SET `defense_type` = 'title_defense' WHERE `id` = 7 OR `name` LIKE '%Proposal Defense%';
UPDATE `rubric_groups` SET `defense_type` = 're_defense' WHERE `id` = 1;

-- Verify the changes
SELECT 'Updated rubric groups:' as status;
SELECT id, name, description, defense_type, program_id 
FROM rubric_groups 
ORDER BY defense_type, id;
