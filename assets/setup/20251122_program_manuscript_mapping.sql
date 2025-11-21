-- ============================================================
-- Program-Defense Type Manuscript Requirements Mapping
-- Purpose: Map which manuscripts are required for which programs and defense types
-- Created: 2025-11-22
-- ============================================================

-- Create table to track manuscript requirements per program and defense type
CREATE TABLE IF NOT EXISTS program_manuscript_requirements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    requirement_id INT UNSIGNED NOT NULL,
    program_id INT NOT NULL,
    defense_type ENUM('title_proposal', 'title_defense', 'final_defense', 're-defense', 'general') NOT NULL,
    is_required BOOLEAN DEFAULT 1 COMMENT 'Is this manuscript required for this combination?',
    submission_stage ENUM('before_defense', 'at_defense', 'optional') DEFAULT 'before_defense' COMMENT 'When should it be submitted?',
    can_revise_after BOOLEAN DEFAULT 0 COMMENT 'Can team revise after this stage?',
    visibility_to_panelist BOOLEAN DEFAULT 1 COMMENT 'Should panelist see this at defense?',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE KEY unique_req_program_defense (requirement_id, program_id, defense_type),
    
    -- Indexes for fast queries
    KEY idx_program_defense_type (program_id, defense_type),
    KEY idx_requirement_program (requirement_id, program_id),
    KEY idx_is_required (is_required)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Maps manuscript requirements to specific program + defense type combinations';

-- ============================================================
-- Add column to requirements to mark as manuscript
-- (if not already added from file_visibility system)
-- ============================================================
ALTER TABLE requirements 
ADD COLUMN IF NOT EXISTS is_defense_manuscript BOOLEAN DEFAULT 0 AFTER requirement_type;

-- ============================================================
-- Create view for easy querying of manuscript requirements
-- ============================================================
CREATE OR REPLACE VIEW program_manuscript_view AS
SELECT 
    pmr.id as mapping_id,
    r.id as requirement_id,
    r.name as requirement_name,
    r.is_defense_manuscript,
    p.id as program_id,
    p.name as program_name,
    pmr.defense_type,
    pmr.is_required,
    pmr.submission_stage,
    pmr.can_revise_after,
    pmr.visibility_to_panelist
FROM program_manuscript_requirements pmr
JOIN requirements r ON pmr.requirement_id = r.id
JOIN programs p ON pmr.program_id = p.id
WHERE r.is_defense_manuscript = 1
ORDER BY p.name, 
         FIELD(pmr.defense_type, 'title_proposal', 'title_defense', 'final_defense', 're-defense', 'general'),
         r.name;

-- ============================================================
-- Sample data (optional - comment out if you have existing data)
-- ============================================================
-- INSERT INTO program_manuscript_requirements (requirement_id, program_id, defense_type, is_required, submission_stage, can_revise_after, visibility_to_panelist)
-- SELECT 5, p.id, 'final_defense', 1, 'before_defense', 1, 1
-- FROM programs p
-- WHERE p.name IN ('WebDevelopment', 'Mobile Development')
-- ON DUPLICATE KEY UPDATE is_required = VALUES(is_required);
