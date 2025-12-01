-- Migration: Standardize defense_type ENUM to use underscores
-- Date: December 2, 2025
-- Purpose: Make defense_type consistent across all tables (use re_defense not re-defense)

-- Step 1: Update rubric_groups to use underscore
ALTER TABLE `rubric_groups` 
MODIFY COLUMN `defense_type` ENUM('title_proposal','title_defense','final_defense','re_defense','general') 
DEFAULT 'general';

-- Step 2: Verify the change
SELECT 'Updated rubric_groups:' as status;
SELECT id, name, defense_type, program_id 
FROM rubric_groups 
ORDER BY id;

SELECT 'Defense type distribution in defense_schedules:' as status;
SELECT defense_type, COUNT(*) as count 
FROM defense_schedules 
GROUP BY defense_type;
