-- Test Script: Verify Individual Criterion Flexible Scoring
-- Run this to verify the max_score column is working correctly

USE icei_38697196_coecsathesis;

-- 1. Check if max_score column exists
SHOW COLUMNS FROM rubric_criteria LIKE 'max_score';

-- 2. Check sample data for individual criteria (should have max_score values)
SELECT 
    rc.id,
    rc.rubric_id,
    rc.criterion_text,
    rc.is_individual,
    rc.max_score,
    r.name as rubric_name,
    r.is_individual_enabled
FROM rubric_criteria rc
JOIN rubrics r ON rc.rubric_id = r.id
WHERE r.is_individual_enabled = 1
ORDER BY rc.rubric_id, rc.order_index
LIMIT 20;

-- 3. Check that group criteria have NULL max_score
SELECT 
    rc.id,
    rc.rubric_id,
    rc.criterion_text,
    rc.is_individual,
    rc.max_score,
    r.name as rubric_name
FROM rubric_criteria rc
JOIN rubrics r ON rc.rubric_id = r.id
WHERE r.is_individual_enabled = 0
  AND rc.max_score IS NOT NULL
LIMIT 10;
-- (This should return no rows - group criteria shouldn't have max_score)

-- 4. Show rubrics with individual scoring enabled
SELECT 
    id,
    name,
    is_individual_enabled,
    max_members,
    created_at
FROM rubrics
WHERE rubric_type = 'numerical' 
  AND is_individual_enabled = 1
ORDER BY created_at DESC
LIMIT 5;
