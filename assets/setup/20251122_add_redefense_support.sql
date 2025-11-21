-- Migration: Add Re-Defense Support to Defense Type System
-- Date: 2025-11-22
-- Purpose: Extend defense type system to support re-defense evaluations with:
--   1. Re-defense as a valid defense type across all relevant tables
--   2. Per-program, per-defense-type requirement mappings
--   3. Support for re-defense overrides and panelist assignments

-- ============================================================================
-- Step 1: Verify tables exist (verification queries - commented for safety)
-- ============================================================================
-- Database: icei_38697196_coecsathesis

-- Step 2: Add 're-defense' to requirement_type ENUM if not already present
-- ============================================================================
ALTER TABLE `requirements` 
MODIFY `requirement_type` ENUM(
    'title_proposal',
    'title_defense',
    'final_defense',
    're-defense',
    'general'
) DEFAULT 'general'
COMMENT 'Defense type this requirement applies to: title_proposal, title_defense, final_defense, re-defense, or general';

-- Step 3: Add 're-defense' to defense_type ENUM in defense_schedules
-- ============================================================================
ALTER TABLE `defense_schedules` 
MODIFY `defense_type` ENUM(
    'title_proposal',
    'title_defense', 
    'final_defense',
    're-defense'
) DEFAULT 'title_proposal'
COMMENT 'Type of defense evaluation for this schedule';

-- Step 4: Add 're-defense' to defense_type ENUM in team_panelists
-- ============================================================================
ALTER TABLE `team_panelists` 
MODIFY `defense_type` ENUM(
    'title_proposal',
    'title_defense',
    'final_defense',
    're-defense'
) NOT NULL
COMMENT 'The defense stage this assignment is for';

-- Step 5: Add 're-defense' to override_type ENUM in defense_type_overrides
-- ============================================================================
ALTER TABLE `defense_type_overrides` 
MODIFY `override_type` ENUM(
    'title_proposal',
    'title_defense',
    'final_defense',
    're-defense'
) NOT NULL
COMMENT 'The defense stage to force for this team';

-- ============================================================================
-- Step 6: Create program_requirements_mapping table
-- Purpose: Store which requirements apply to which defense types per program
-- This allows fine-grained control over requirements for each defense stage
-- ============================================================================
CREATE TABLE IF NOT EXISTS `program_requirements_mapping` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `program_id` INT(11) NOT NULL,
    `defense_type` ENUM(
        'title_proposal',
        'title_defense',
        'final_defense',
        're-defense',
        'general'
    ) NOT NULL,
    `requirement_id` INT(11) UNSIGNED NOT NULL,
    `is_mandatory` TINYINT(1) DEFAULT 1 COMMENT 'Whether this requirement must be completed for this defense type',
    `display_order` INT DEFAULT 0 COMMENT 'Order to display requirements in UI',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) COMMENT 'Admin user ID who created this mapping',
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_program_defense_requirement` (`program_id`, `defense_type`, `requirement_id`),
    KEY `idx_program_defense` (`program_id`, `defense_type`),
    KEY `idx_program_requirements` (`program_id`, `requirement_id`),
    
    CONSTRAINT `fk_prm_program` FOREIGN KEY (`program_id`) 
        REFERENCES `programs` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_prm_requirement` FOREIGN KEY (`requirement_id`) 
        REFERENCES `requirements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Per-program, per-defense-type requirement mappings for granular control';

-- ============================================================================
-- Step 7: Create re_defense_assessments table (optional, for tracking re-defense evaluations)
-- Purpose: Track re-defense evaluations separately for audit trail
-- ============================================================================
CREATE TABLE IF NOT EXISTS `re_defense_assessments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `team_id` INT(11) NOT NULL,
    `defense_schedule_id` INT(11) NOT NULL,
    `reason_for_redefense` TEXT COMMENT 'Why was re-defense required?',
    `initial_defense_schedule_id` INT(11) COMMENT 'Reference to initial defense schedule',
    `status` ENUM('pending', 'completed', 'passed', 'failed') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `initiated_by` INT(11) COMMENT 'Admin user ID who initiated re-defense',
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_team_redefense` (`team_id`, `defense_schedule_id`),
    KEY `idx_team_redefense` (`team_id`, `status`),
    KEY `idx_defense_schedule` (`defense_schedule_id`),
    
    CONSTRAINT `fk_rda_team` FOREIGN KEY (`team_id`) 
        REFERENCES `teams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rda_schedule` FOREIGN KEY (`defense_schedule_id`) 
        REFERENCES `defense_schedules` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rda_initial_schedule` FOREIGN KEY (`initial_defense_schedule_id`) 
        REFERENCES `defense_schedules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Tracks re-defense evaluations for audit and status tracking';

-- ============================================================================
-- Step 8: Add indexes for performance optimization
-- ============================================================================
ALTER TABLE `defense_schedules` ADD INDEX `idx_defense_type` (`defense_type`);
ALTER TABLE `defense_schedules` ADD INDEX `idx_team_defense_type` (`team_id`, `defense_type`);
ALTER TABLE `team_panelists` ADD INDEX `idx_defense_type` (`defense_type`);
ALTER TABLE `defense_type_overrides` ADD INDEX `idx_override_type` (`override_type`);

-- ============================================================================
-- Verification queries (run these to verify the migration was successful)
-- ============================================================================

-- Verify requirement_type ENUM includes 're-defense'
-- SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'requirements' 
-- AND COLUMN_NAME = 'requirement_type'
-- AND TABLE_SCHEMA = DATABASE();

-- Verify program_requirements_mapping table was created
-- SELECT TABLE_NAME, TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES 
-- WHERE TABLE_NAME = 'program_requirements_mapping' 
-- AND TABLE_SCHEMA = DATABASE();

-- Verify re_defense_assessments table was created
-- SELECT TABLE_NAME, TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES 
-- WHERE TABLE_NAME = 're_defense_assessments' 
-- AND TABLE_SCHEMA = DATABASE();

