-- Helper script to configure program_manuscript_requirements
-- This maps which manuscript requirement appears for which program + defense type
-- Date: December 2, 2025

-- First, check what requirements exist in your system
SELECT '=== CURRENT REQUIREMENTS ===' as info;
SELECT id, name, requirement_type, is_visible, created_at
FROM requirements
WHERE requirement_type IN ('title_proposal', 'title_defense', 'final_defense', 're-defense', 'general')
ORDER BY requirement_type, id;

-- Check what programs exist
SELECT '=== CURRENT PROGRAMS ===' as info;
SELECT id, name, created_at
FROM programs
ORDER BY name;

-- Check what's already configured
SELECT '=== CURRENT CONFIGURATIONS ===' as info;
SELECT 
    pmr.id,
    p.name as program_name,
    r.name as requirement_name,
    pmr.defense_type,
    pmr.is_required,
    pmr.visibility_to_panelist
FROM program_manuscript_requirements pmr
JOIN programs p ON pmr.program_id = p.id
JOIN requirements r ON pmr.requirement_id = r.id
ORDER BY p.name, pmr.defense_type;

-- ================================================================================
-- TEMPLATE: Configure for your programs
-- ================================================================================
-- Replace these variables with your actual values:
-- @PROGRAM_ID_1 = Your first program ID (e.g., 79 for BSIT)
-- @PROGRAM_ID_2 = Your second program ID (e.g., 80 for BSCS)
-- @REQ_TITLE_PROPOSAL = Requirement ID for title proposal manuscript
-- @REQ_TITLE_DEFENSE = Requirement ID for title defense manuscript
-- @REQ_FINAL_DEFENSE = Requirement ID for final defense manuscript
-- @REQ_RE_DEFENSE = Requirement ID for re-defense manuscript

-- Example configuration (uncomment and modify based on your data):

/*
-- For BSIT (program_id = 79)
INSERT INTO program_manuscript_requirements 
(requirement_id, program_id, defense_type, is_required, submission_stage, can_revise_after, visibility_to_panelist)
VALUES
(46, 79, 'title_proposal', 1, 'before_defense', 0, 1),
(47, 79, 'title_defense', 1, 'before_defense', 0, 1),
(48, 79, 'final_defense', 1, 'before_defense', 0, 1),
(49, 79, 're-defense', 1, 'before_defense', 1, 1)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- For BSCS (program_id = 80)
INSERT INTO program_manuscript_requirements 
(requirement_id, program_id, defense_type, is_required, submission_stage, can_revise_after, visibility_to_panelist)
VALUES
(46, 80, 'title_proposal', 1, 'before_defense', 0, 1),
(47, 80, 'title_defense', 1, 'before_defense', 0, 1),
(48, 80, 'final_defense', 1, 'before_defense', 0, 1),
(49, 80, 're-defense', 1, 'before_defense', 1, 1)
ON DUPLICATE KEY UPDATE updated_at = NOW();
*/

-- ================================================================================
-- VERIFICATION QUERIES
-- ================================================================================

-- Verify all programs have requirements configured
SELECT '=== PROGRAMS WITHOUT CONFIGURATIONS ===' as info;
SELECT p.id, p.name
FROM programs p
LEFT JOIN program_manuscript_requirements pmr ON p.id = pmr.program_id
WHERE pmr.id IS NULL
ORDER BY p.name;

-- Verify all defense types are covered for each program
SELECT '=== CONFIGURATION COVERAGE ===' as info;
SELECT 
    p.name as program_name,
    GROUP_CONCAT(DISTINCT pmr.defense_type ORDER BY pmr.defense_type) as configured_types,
    CASE 
        WHEN COUNT(DISTINCT pmr.defense_type) >= 4 THEN 'COMPLETE'
        ELSE 'INCOMPLETE - Missing some defense types'
    END as status
FROM programs p
LEFT JOIN program_manuscript_requirements pmr ON p.id = pmr.program_id
GROUP BY p.id, p.name
ORDER BY p.name;

-- Check for conflicts (multiple requirements for same program + defense type)
SELECT '=== POTENTIAL CONFLICTS ===' as info;
SELECT 
    program_id,
    defense_type,
    COUNT(*) as count
FROM program_manuscript_requirements
GROUP BY program_id, defense_type
HAVING COUNT(*) > 1;
