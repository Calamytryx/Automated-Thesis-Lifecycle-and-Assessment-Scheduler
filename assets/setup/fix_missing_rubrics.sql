-- Fix for missing rubrics (IDs 8, 9, 10, 11) referenced by rubric_group_items
-- These rubrics are needed for Group 2 (Title Proposal defense)
-- Date: December 2, 2025

-- Insert missing rubrics for Title Proposal defense
INSERT INTO `rubrics` (`id`, `name`, `description`, `rubric_type`, `is_individual_enabled`, `defense_type`, `rubric_description`, `pass_recommendation_text`, `fail_recommendation_text`, `fail_option_text`, `pass_threshold_1`, `pass_threshold_2`, `pass_threshold_3`, `max_total_score`, `max_members`, `is_active`, `created_at`, `updated_at`, `max_score_per_criterion`) VALUES
(8, 'TPS - Written Manuscript', 'Title Proposal Score Sheet - Group Grade', 'numerical', 0, 'title_proposal', 'Manuscript Quality', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, NOW(), NOW(), 100),
(9, 'TPS - Oral Defense Presentation', 'Title Proposal Score Sheet - Individual Grade', 'numerical', 1, 'title_proposal', 'Presentation Skills', NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, 1, NOW(), NOW(), 100),
(10, 'TPS - Oral Defense Delivery', 'Title Proposal Score Sheet - Individual Grade', 'numerical', 1, 'title_proposal', 'Delivery and Communication', NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, 1, NOW(), NOW(), 100),
(11, 'TPS - Final Recommendation', 'Title Proposal Score Sheet - Final Recommendation', 'passfail', 0, 'title_proposal', '', 'The title proposal is accepted:', 'The title proposal is rejected:', 'below 70% acceptability (refer to research adviser and for revision)', '100.00', '75.00', '65.00', 0, NULL, 1, NOW(), NOW(), 100)
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `rubric_type` = VALUES(`rubric_type`),
  `is_individual_enabled` = VALUES(`is_individual_enabled`),
  `defense_type` = VALUES(`defense_type`),
  `is_active` = 1,
  `updated_at` = NOW();

-- Add missing rubric IDs 12 and 13 if they're also missing (for Title Defense)
INSERT INTO `rubrics` (`id`, `name`, `description`, `rubric_type`, `is_individual_enabled`, `defense_type`, `rubric_description`, `pass_recommendation_text`, `fail_recommendation_text`, `fail_option_text`, `pass_threshold_1`, `pass_threshold_2`, `pass_threshold_3`, `max_total_score`, `max_members`, `is_active`, `created_at`, `updated_at`, `max_score_per_criterion`) VALUES
(12, 'TDR - Research Methodology', 'Title Defense Rubric - Group Grade', 'numerical', 0, 'title_defense', 'Research Methodology Quality', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, NOW(), NOW(), 100),
(13, 'TDR - Research Feasibility', 'Title Defense Rubric - Group Grade', 'numerical', 0, 'title_defense', 'Research Feasibility Assessment', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, NOW(), NOW(), 100)
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `is_active` = 1,
  `updated_at` = NOW();

-- Verify the rubrics exist
SELECT 'Checking rubrics 8-13:' as status;
SELECT id, name, defense_type, is_active FROM rubrics WHERE id IN (8, 9, 10, 11, 12, 13) ORDER BY id;

-- Add rubric criteria for rubric 8 (TPS - Written Manuscript)
INSERT INTO `rubric_criteria` (`rubric_id`, `criterion_text`, `criterion_detail`, `order_index`, `is_individual`, `created_at`, `updated_at`) VALUES
(8, 'Clarity and Organization', 'Organization and structure of the manuscript', 0, 0, NOW(), NOW()),
(8, 'Content Quality', 'Quality and depth of content presented', 1, 0, NOW(), NOW()),
(8, 'Technical Accuracy', 'Accuracy of technical details and methodology', 2, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Add rubric criteria for rubric 9 (TPS - Oral Defense Presentation)
INSERT INTO `rubric_criteria` (`rubric_id`, `criterion_text`, `criterion_detail`, `order_index`, `is_individual`, `created_at`, `updated_at`) VALUES
(9, 'Content Knowledge', 'Demonstrates understanding of the research topic', 0, 1, NOW(), NOW()),
(9, 'Visual Aids', 'Effective use of slides and visual materials', 1, 1, NOW(), NOW()),
(9, 'Time Management', 'Adheres to time limits effectively', 2, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Add rubric criteria for rubric 10 (TPS - Oral Defense Delivery)
INSERT INTO `rubric_criteria` (`rubric_id`, `criterion_text`, `criterion_detail`, `order_index`, `is_individual`, `created_at`, `updated_at`) VALUES
(10, 'Voice and Clarity', 'Clear articulation and appropriate volume', 0, 1, NOW(), NOW()),
(10, 'Eye Contact', 'Maintains appropriate eye contact with audience', 1, 1, NOW(), NOW()),
(10, 'Confidence', 'Demonstrates confidence in presentation', 2, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Add rubric levels for rubric 8 (Numerical - Group)
INSERT INTO `rubric_levels` (`rubric_id`, `level_index`, `name`, `description`, `points_min`, `points_max`, `is_range`, `created_at`, `updated_at`) VALUES
(8, 1, 'Poor', 'Below expectations', 1, 3, 1, NOW(), NOW()),
(8, 2, 'Satisfactory', 'Meets basic expectations', 4, 7, 1, NOW(), NOW()),
(8, 3, 'Excellent', 'Exceeds expectations', 8, 10, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Add rubric levels for rubric 9 (Numerical - Individual)
INSERT INTO `rubric_levels` (`rubric_id`, `level_index`, `name`, `description`, `points_min`, `points_max`, `is_range`, `created_at`, `updated_at`) VALUES
(9, 1, 'Poor', 'Below expectations', 1, 3, 1, NOW(), NOW()),
(9, 2, 'Satisfactory', 'Meets basic expectations', 4, 7, 1, NOW(), NOW()),
(9, 3, 'Excellent', 'Exceeds expectations', 8, 10, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Add rubric levels for rubric 10 (Numerical - Individual)
INSERT INTO `rubric_levels` (`rubric_id`, `level_index`, `name`, `description`, `points_min`, `points_max`, `is_range`, `created_at`, `updated_at`) VALUES
(10, 1, 'Poor', 'Below expectations', 1, 3, 1, NOW(), NOW()),
(10, 2, 'Satisfactory', 'Meets basic expectations', 4, 7, 1, NOW(), NOW()),
(10, 3, 'Excellent', 'Exceeds expectations', 8, 10, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Add rubric levels for rubric 11 (Pass/Fail)
INSERT INTO `rubric_levels` (`rubric_id`, `level_index`, `name`, `description`, `points_min`, `points_max`, `is_range`, `created_at`, `updated_at`) VALUES
(11, 1, 'Passed with Distinction', '100% acceptability or above', 100, 100, 0, NOW(), NOW()),
(11, 2, 'Passed', '75-99% acceptability', 75, 99, 1, NOW(), NOW()),
(11, 3, 'Conditional Pass', '65-74% acceptability (minor revisions required)', 65, 74, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Add rubric criteria for rubric 12 (Research Methodology)
INSERT INTO `rubric_criteria` (`rubric_id`, `criterion_text`, `criterion_detail`, `order_index`, `is_individual`, `created_at`, `updated_at`) VALUES
(12, 'Research Design', 'Appropriateness of research methodology', 0, 0, NOW(), NOW()),
(12, 'Data Collection Methods', 'Validity of data collection approach', 1, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Add rubric criteria for rubric 13 (Research Feasibility)
INSERT INTO `rubric_criteria` (`rubric_id`, `criterion_text`, `criterion_detail`, `order_index`, `is_individual`, `created_at`, `updated_at`) VALUES
(13, 'Resource Availability', 'Feasibility of resources needed', 0, 0, NOW(), NOW()),
(13, 'Timeline Realism', 'Realism of proposed timeline', 1, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Add rubric levels for rubric 12 and 13
INSERT INTO `rubric_levels` (`rubric_id`, `level_index`, `name`, `description`, `points_min`, `points_max`, `is_range`, `created_at`, `updated_at`) VALUES
(12, 1, 'Poor', 'Below expectations', 1, 3, 1, NOW(), NOW()),
(12, 2, 'Satisfactory', 'Meets basic expectations', 4, 7, 1, NOW(), NOW()),
(12, 3, 'Excellent', 'Exceeds expectations', 8, 10, 1, NOW(), NOW()),
(13, 1, 'Poor', 'Below expectations', 1, 3, 1, NOW(), NOW()),
(13, 2, 'Satisfactory', 'Meets basic expectations', 4, 7, 1, NOW(), NOW()),
(13, 3, 'Excellent', 'Exceeds expectations', 8, 10, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Verify group 2 associations
SELECT 'Checking group 2 rubric assignments:' as status;
SELECT rgi.group_id, rgi.rubric_id, rgi.order_index, rgi.weight, r.name as rubric_name, r.is_active
FROM rubric_group_items rgi
LEFT JOIN rubrics r ON rgi.rubric_id = r.id
WHERE rgi.group_id = 2
ORDER BY rgi.order_index;
