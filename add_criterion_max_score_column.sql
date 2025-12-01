-- Migration: Add max_score column to rubric_criteria table
-- This allows individual criteria in numerical rubrics to have flexible max scores
-- instead of all defaulting to 100

USE icei_38697196_coecsathesis;

ALTER TABLE `rubric_criteria` 
ADD COLUMN `max_score` DECIMAL(5,2) NULL DEFAULT NULL 
COMMENT 'Maximum score for this criterion (used for individual scoring in numerical rubrics)' 
AFTER `is_individual`;

-- Update existing records: set max_score to NULL for group scoring criteria
-- Individual criteria will get their max_score set when edited/saved
UPDATE `rubric_criteria` 
SET `max_score` = NULL 
WHERE `is_individual` = 0;

-- Note: Individual criteria (is_individual = 1) will have their max_score 
-- set through the admin interface when editing rubrics
