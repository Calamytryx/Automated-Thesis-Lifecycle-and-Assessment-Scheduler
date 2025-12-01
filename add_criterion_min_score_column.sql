-- Migration: Add min_score column to rubric_criteria table
-- This allows individual criteria to have flexible ranges (e.g., 1-5, 1-20, 0-100)

USE icei_38697196_coecsathesis;

ALTER TABLE `rubric_criteria` 
ADD COLUMN `min_score` DECIMAL(5,2) NULL DEFAULT 0.00 
COMMENT 'Minimum score for this criterion (used for individual scoring in numerical rubrics)' 
AFTER `max_score`;

-- Update existing records: set min_score to 0 for existing individual criteria
UPDATE `rubric_criteria` 
SET `min_score` = 0.00 
WHERE `is_individual` = 1 AND `min_score` IS NULL;

-- Note: Both min_score and max_score define the range for individual criteria
-- Example: min_score=1, max_score=5 means the scorer can enter values from 1 to 5
