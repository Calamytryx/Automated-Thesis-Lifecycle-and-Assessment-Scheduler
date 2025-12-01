-- Migration: Add dynamic requirements, panelist persistence, and multi-submission support
-- Created: 2025-11-21
-- Purpose: Enable dynamic defense type mapping, persistent panelist assignments, and multiple file submissions
-- NOTE: This migration is idempotent and checks for existing columns/tables before adding them

-- ============================================================================
-- 1. ALTER requirements TABLE: Add dynamic defense type and multi-submission support (IF NOT EXISTS)
-- ============================================================================

-- Add requirement_type column if it doesn't exist
ALTER TABLE `requirements` 
ADD COLUMN IF NOT EXISTS `requirement_type` ENUM('title_proposal', 'title_defense', 'final_defense', 'general') DEFAULT 'general' 
COMMENT 'Defense type this requirement applies to' AFTER `name`;

-- Add allow_multiple_submissions column if it doesn't exist
ALTER TABLE `requirements` 
ADD COLUMN IF NOT EXISTS `allow_multiple_submissions` TINYINT(1) DEFAULT 0 
COMMENT 'Whether teams can submit multiple files (max 3) for this requirement' AFTER `requirement_type`;

-- Add max_submissions column if it doesn't exist
ALTER TABLE `requirements` 
ADD COLUMN IF NOT EXISTS `max_submissions` INT DEFAULT 1 
COMMENT 'Maximum number of submissions allowed (default 1, max 3)' AFTER `allow_multiple_submissions`;

-- ============================================================================
-- 2. CREATE team_panelists TABLE: Persist panelist assignments across defense stages
-- ============================================================================
CREATE TABLE IF NOT EXISTS `team_panelists` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` INT(11) UNSIGNED NOT NULL,
  `defense_type` ENUM('title_proposal', 'title_defense', 'final_defense') NOT NULL COMMENT 'The defense stage this assignment is for',
  `panelist_id` INT(11) UNSIGNED NOT NULL,
  `panelist_position` INT(1) DEFAULT 1 COMMENT 'Position: 1=primary, 2=secondary, 3=tertiary',
  `locked` TINYINT(1) DEFAULT 0 COMMENT 'Whether this assignment is locked and cannot be changed',
  `admin_override` TINYINT(1) DEFAULT 0 COMMENT 'Whether this assignment was manually set by admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT(11) UNSIGNED COMMENT 'User ID who created/locked this assignment',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_defense_panelist` (`team_id`, `defense_type`, `panelist_id`),
  KEY `idx_team_defense` (`team_id`, `defense_type`),
  KEY `idx_panelist` (`panelist_id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`panelist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks persistent panelist assignments across defense stages';

-- ============================================================================
-- 3. CREATE team_requirement_files TABLE: Support multiple submissions per requirement
-- ============================================================================
CREATE TABLE IF NOT EXISTS `team_requirement_files` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` INT(11) UNSIGNED NOT NULL,
  `requirement_id` INT(11) UNSIGNED NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `original_file_name` VARCHAR(255),
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` INT,
  `submission_number` INT DEFAULT 1 COMMENT 'For multi-submission requirements: 1, 2, or 3',
  `status` ENUM('pending', 'submitted', 'approved', 'rejected') DEFAULT 'submitted',
  `feedback` TEXT,
  `feedback_file` VARCHAR(255),
  `submitted_by` INT(11) UNSIGNED,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_team_requirement` (`team_id`, `requirement_id`),
  KEY `idx_team` (`team_id`),
  KEY `idx_requirement` (`requirement_id`),
  KEY `idx_submission_number` (`submission_number`),
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`requirement_id`) REFERENCES `requirements`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`submitted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Individual file submissions for requirements with multi-submission support';

-- ============================================================================
-- 4. ALTER defense_schedules TABLE: Add defense type tracking and multi-file support (IF NOT EXISTS)
-- ============================================================================

-- Add defense_type column if it doesn't exist
ALTER TABLE `defense_schedules` 
ADD COLUMN IF NOT EXISTS `defense_type` ENUM('title_proposal', 'title_defense', 'final_defense') 
COMMENT 'Type of defense being scheduled' AFTER `room`;

-- Add related_requirement_files column if it doesn't exist
ALTER TABLE `defense_schedules` 
ADD COLUMN IF NOT EXISTS `related_requirement_files` JSON 
COMMENT 'JSON array of team_requirement_files IDs for multi-submission requirements' AFTER `defense_type`;

-- Add admin_override_defense_type column if it doesn't exist
ALTER TABLE `defense_schedules` 
ADD COLUMN IF NOT EXISTS `admin_override_defense_type` TINYINT(1) DEFAULT 0 
COMMENT 'Whether defense type was manually overridden by admin' AFTER `related_requirement_files`;

-- Add index if it doesn't exist
ALTER TABLE `defense_schedules` ADD KEY IF NOT EXISTS `idx_defense_type` (`defense_type`);

-- ============================================================================
-- 5. CREATE defense_type_overrides TABLE: Track admin overrides for special cases
-- ============================================================================
CREATE TABLE IF NOT EXISTS `defense_type_overrides` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` INT(11) UNSIGNED NOT NULL,
  `override_type` ENUM('title_proposal', 'title_defense', 'final_defense') NOT NULL COMMENT 'Force team to be treated as this defense type',
  `reason` TEXT,
  `active` TINYINT(1) DEFAULT 1 COMMENT 'Whether this override is currently active',
  `created_by` INT(11) UNSIGNED NOT NULL COMMENT 'Admin user ID who created this override',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NULL COMMENT 'Optional expiry date for temporary overrides',
  PRIMARY KEY (`id`),
  KEY `idx_team_active` (`team_id`, `active`),
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Admin overrides for defense type mapping';

-- ============================================================================
-- 6. INSERT sample requirement type mappings (adjust as needed for your system)
-- ============================================================================
-- UPDATE existing requirements with sensible defaults (only if not already set)
UPDATE `requirements` SET `requirement_type` = 'title_proposal' WHERE `requirement_type` = 'general' AND (`name` LIKE '%title%' OR `name` LIKE '%proposal%') LIMIT 1;
UPDATE `requirements` SET `requirement_type` = 'final_defense' WHERE `requirement_type` = 'general' AND (`name` LIKE '%manuscript%' OR `name` LIKE '%final%') LIMIT 1;
UPDATE `requirements` SET `requirement_type` = 'title_defense' WHERE `requirement_type` = 'general' AND `name` LIKE '%capstone%' LIMIT 1;

-- Enable multiple submissions for title proposal requirement (if not already set)
UPDATE `requirements` SET `allow_multiple_submissions` = 1, `max_submissions` = 3 WHERE `requirement_type` = 'title_proposal' AND `allow_multiple_submissions` = 0 LIMIT 1;

-- ============================================================================
-- 7. CREATE VIEW for easier defense type determination
-- ============================================================================
CREATE OR REPLACE VIEW `team_defense_status` AS
SELECT 
  t.id as team_id,
  t.name as team_name,
  CASE 
    WHEN dto.active = 1 THEN dto.override_type
    WHEN COUNT(DISTINCT ep.id) >= 2 THEN 'final_defense'
    WHEN COUNT(DISTINCT rt.id) > 0 THEN 'title_defense'
    ELSE 'title_proposal'
  END as current_defense_type,
  COUNT(DISTINCT rt.id) as approved_titles,
  COUNT(DISTINCT ep.id) as completed_evaluations,
  MAX(dto.override_type) as override_defense_type,
  MAX(dto.active) as override_active
FROM `teams` t
LEFT JOIN `research_titles` rt ON t.id = rt.team_id AND rt.approved_at IS NOT NULL
LEFT JOIN `defense_schedules` ds ON t.id = ds.team_id
LEFT JOIN `evaluation_per_panel` ep ON ds.id = ep.defense_schedule_id AND ep.created_at IS NOT NULL
LEFT JOIN `defense_type_overrides` dto ON t.id = dto.team_id AND dto.active = 1
GROUP BY t.id;

-- ============================================================================
-- Done! Migration complete - Idempotent version
-- This file can be safely run multiple times without errors
-- ============================================================================
