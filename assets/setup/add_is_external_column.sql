-- Add is_external column to users table
-- External panelists are pure panelists (usertype 2) marked as external
-- They will always be assigned as panelist_id3 (third panelist position)

ALTER TABLE `users` 
ADD COLUMN `is_external` tinyint(1) DEFAULT 0 COMMENT 'Mark usertype 2 as external panelist (pure panelist, not faculty)' AFTER `is_program_chair`;

-- Create index for faster filtering of external panelists
CREATE INDEX `idx_external_panelists` ON `users` (`usertype`, `is_external`) 
WHERE `usertype` = 2 AND `is_external` = 1;
