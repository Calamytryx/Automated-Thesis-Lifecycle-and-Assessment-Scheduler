-- Add is_blank column to rubric_criteria table for section header support
-- This allows criteria to be used as section headers/titles without scoring

ALTER TABLE rubric_criteria 
ADD COLUMN IF NOT EXISTS is_blank TINYINT(1) DEFAULT 0 
AFTER is_individual;

-- Update description
COMMENT ON COLUMN rubric_criteria.is_blank IS 'Flag to indicate if this criterion is a blank row (section header) - 1 for blank/header, 0 for normal criterion';
