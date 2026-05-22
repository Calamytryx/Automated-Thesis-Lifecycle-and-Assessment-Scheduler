-- Fix for Rubric Groups to support defense type and program filtering
-- Date: December 2, 2025

-- Step 1: Add defense_type and program_id columns to rubric_groups
ALTER TABLE `rubric_groups` 
ADD COLUMN `defense_type` ENUM('title_proposal','title_defense','final_defense','re-defense','general') DEFAULT 'general' AFTER `description`,
ADD COLUMN `program_id` INT(11) DEFAULT NULL AFTER `defense_type`,
ADD KEY `idx_defense_program` (`defense_type`, `program_id`);

-- Step 2: Update existing rubric groups with appropriate defense types based on their names
UPDATE `rubric_groups` SET `defense_type` = 'title_proposal' WHERE `id` = 2 OR `name` LIKE '%Title Proposal%';
UPDATE `rubric_groups` SET `defense_type` = 'final_defense' WHERE `id` = 5 OR `name` LIKE '%Final Defense%';
UPDATE `rubric_groups` SET `defense_type` = 're-defense' WHERE `id` = 6 OR `name` LIKE '%Re-Presentation%';
UPDATE `rubric_groups` SET `defense_type` = 'title_defense' WHERE `id` = 7 OR `name` LIKE '%Proposal Defense%';

-- Step 3: Update group 1 to be for re-presentation (based on description)
UPDATE `rubric_groups` SET `defense_type` = 're-defense' WHERE `id` = 1;

-- Step 4: Create a function to get the appropriate rubric group
DELIMITER $$

DROP FUNCTION IF EXISTS `get_rubric_group_for_defense`$$

CREATE FUNCTION `get_rubric_group_for_defense`(
    p_defense_type VARCHAR(50),
    p_program_id INT
) RETURNS INT(11)
DETERMINISTIC
BEGIN
    DECLARE v_group_id INT(11);
    
    -- First try: exact match on both defense_type and program_id
    SELECT id INTO v_group_id
    FROM rubric_groups
    WHERE defense_type = p_defense_type
      AND program_id = p_program_id
    LIMIT 1;
    
    -- Second try: match defense_type with NULL program_id (generic for all programs)
    IF v_group_id IS NULL THEN
        SELECT id INTO v_group_id
        FROM rubric_groups
        WHERE defense_type = p_defense_type
          AND program_id IS NULL
        LIMIT 1;
    END IF;
    
    -- Third try: general defense type
    IF v_group_id IS NULL THEN
        SELECT id INTO v_group_id
        FROM rubric_groups
        WHERE defense_type = 'general'
        LIMIT 1;
    END IF;
    
    RETURN v_group_id;
END$$

DELIMITER ;

-- Verify the changes
SELECT 'Updated rubric groups:' as status;
SELECT id, name, description, defense_type, program_id 
FROM rubric_groups 
ORDER BY defense_type, id;

-- Test the function with different scenarios
SELECT 'Testing function for title_proposal with program NULL:' as test;
SELECT get_rubric_group_for_defense('title_proposal', NULL) as group_id;

SELECT 'Testing function for final_defense with program NULL:' as test;
SELECT get_rubric_group_for_defense('final_defense', NULL) as group_id;
