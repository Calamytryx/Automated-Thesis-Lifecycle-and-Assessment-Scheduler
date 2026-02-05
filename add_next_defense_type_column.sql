-- Add next_defense_type column to teams table
-- This column tracks what defense type a team should have in their next schedule
-- Used by the automatic defense progression system

ALTER TABLE teams 
ADD COLUMN next_defense_type VARCHAR(50) NULL 
DEFAULT 'title_proposal'
COMMENT 'Next defense type for automatic progression: title_proposal, title_defense, final_defense, re_defense'
AFTER area_of_expertise;

-- Add index for faster queries
CREATE INDEX idx_next_defense_type ON teams(next_defense_type);

-- Log the migration
SELECT 'Successfully added next_defense_type column to teams table' as status;
