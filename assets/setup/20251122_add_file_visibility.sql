-- ============================================================
-- File Visibility Management Migration
-- Purpose: Add file visibility controls based on defense type
-- Created: 2025-11-22
-- ============================================================

-- 1. Add columns to requirements table for defense manuscript marking
ALTER TABLE requirements 
ADD COLUMN is_defense_manuscript BOOLEAN DEFAULT 0 COMMENT 'Mark if this requirement produces the main defense manuscript' AFTER requirement_type,
ADD COLUMN visibility_scope ENUM('all_stages', 'specific_stages', 'current_stage_only', 'hidden') DEFAULT 'all_stages' COMMENT 'Control which defense stages can see files from this requirement' AFTER is_defense_manuscript;

-- ============================================================
-- Create new file_visibility_rules table
-- ============================================================
-- This table defines which files are visible to which defense types
CREATE TABLE IF NOT EXISTS file_visibility_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    requirement_id INT UNSIGNED NOT NULL,
    defense_type ENUM('title_proposal', 'title_defense', 'final_defense', 're-defense', 'general') NOT NULL,
    can_view BOOLEAN DEFAULT 1 COMMENT 'Whether this defense type can view files from this requirement',
    can_download BOOLEAN DEFAULT 1 COMMENT 'Whether this defense type can download files',
    visibility_label VARCHAR(100) COMMENT 'Label for this visibility setting (e.g., "Visible for Title Defense onwards")',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_req_defense_type (requirement_id, defense_type),
    FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE,
    KEY idx_defense_type (defense_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Rules for controlling file visibility based on defense type';

-- ============================================================
-- Create new defense_file_access_log table
-- ============================================================
-- Optional: Track when teams access defense files
CREATE TABLE IF NOT EXISTS defense_file_access_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    team_id INT UNSIGNED NOT NULL,
    requirement_id INT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    action ENUM('view', 'download') DEFAULT 'view',
    access_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT UNSIGNED,
    
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    KEY idx_team_requirement (team_id, requirement_id),
    KEY idx_access_timestamp (access_timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Audit log for defense file access by teams';

-- ============================================================
-- Create helper view for manuscript visibility
-- ============================================================
-- Shows defense manuscripts and their visibility settings
CREATE OR REPLACE VIEW defense_manuscripts_view AS
SELECT 
    r.id as requirement_id,
    r.name as requirement_name,
    r.is_defense_manuscript,
    r.visibility_scope,
    GROUP_CONCAT(fvr.defense_type SEPARATOR ', ') as visible_to_defense_types,
    COUNT(DISTINCT fvr.defense_type) as num_visible_types
FROM requirements r
LEFT JOIN file_visibility_rules fvr ON r.id = fvr.requirement_id AND fvr.can_view = 1
WHERE r.is_defense_manuscript = 1
GROUP BY r.id, r.name, r.is_defense_manuscript, r.visibility_scope;

-- ============================================================
-- Insert default visibility rules for existing requirements
-- ============================================================
-- This will be commented out but shows how to populate for existing reqs
-- INSERT INTO file_visibility_rules (requirement_id, defense_type, can_view, can_download)
-- SELECT 
--     r.id,
--     dt.defense_type,
--     1,
--     CASE WHEN r.is_defense_manuscript = 1 THEN 1 ELSE 1 END
-- FROM requirements r
-- CROSS JOIN (
--     SELECT 'title_proposal' as defense_type UNION
--     SELECT 'title_defense' UNION
--     SELECT 'final_defense' UNION
--     SELECT 're-defense' UNION
--     SELECT 'general'
-- ) dt
-- WHERE NOT EXISTS (
--     SELECT 1 FROM file_visibility_rules fvr 
--     WHERE fvr.requirement_id = r.id AND fvr.defense_type = dt.defense_type
-- );
