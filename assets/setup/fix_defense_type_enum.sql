-- Migration: Update defense_type ENUM and set default values
-- Date: December 2, 2025
-- Purpose: Fix ENUM values and update NULL defense_type records

-- Step 1: Modify ENUM to use underscore instead of hyphen for consistency
ALTER TABLE `defense_schedules` 
MODIFY COLUMN `defense_type` ENUM('title_proposal','title_defense','final_defense','re_defense','general') 
DEFAULT 'general' COMMENT 'Type of defense being scheduled';

-- Step 2: Update NULL defense_type values to 'general'
UPDATE `defense_schedules` 
SET `defense_type` = 'general' 
WHERE `defense_type` IS NULL;

-- Step 3: Make defense_type NOT NULL since we have a default
ALTER TABLE `defense_schedules` 
MODIFY COLUMN `defense_type` ENUM('title_proposal','title_defense','final_defense','re_defense','general') 
NOT NULL DEFAULT 'general' COMMENT 'Type of defense being scheduled';

-- Verify the changes
SELECT 'Updated defense_schedules records:' as status;
SELECT id, team_id, schedule_date, defense_type, room 
FROM defense_schedules 
ORDER BY id;

SELECT 'Defense type distribution:' as status;
SELECT defense_type, COUNT(*) as count 
FROM defense_schedules 
GROUP BY defense_type;
