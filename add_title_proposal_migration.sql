-- Migration: Add title_proposal column to teams table
-- Date: November 24, 2025
-- Description: Add support for marking teams as title proposal type

ALTER TABLE teams ADD COLUMN IF NOT EXISTS title_proposal TINYINT DEFAULT 0 COMMENT 'Mark team as title proposal - professor role will be automatic';

-- Verify the column was added
DESCRIBE teams;
