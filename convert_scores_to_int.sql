-- Migration: Change min_score and max_score from DECIMAL to INT
-- This makes scores integer-only (no decimals like 12.5)

USE icei_38697196_coecsathesis;

-- Change max_score from DECIMAL(5,2) to INT
ALTER TABLE `rubric_criteria` 
MODIFY COLUMN `max_score` INT NULL DEFAULT NULL 
COMMENT 'Maximum score for this criterion (used for individual scoring in numerical rubrics)';

-- Change min_score from DECIMAL(5,2) to INT  
ALTER TABLE `rubric_criteria` 
MODIFY COLUMN `min_score` INT NULL DEFAULT 0 
COMMENT 'Minimum score for this criterion (used for individual scoring in numerical rubrics)';

-- Update any existing decimal values to integers (rounds to nearest int)
UPDATE `rubric_criteria` 
SET max_score = ROUND(max_score) 
WHERE max_score IS NOT NULL;

UPDATE `rubric_criteria` 
SET min_score = ROUND(min_score) 
WHERE min_score IS NOT NULL;
