-- Adds overlap exception fields for research-class handling in scheduler conflict checks.
ALTER TABLE user_schedules
    ADD COLUMN IF NOT EXISTS allow_overlap TINYINT(1) NOT NULL DEFAULT 0 AFTER class_name,
    ADD COLUMN IF NOT EXISTS is_research_class TINYINT(1) NOT NULL DEFAULT 0 AFTER allow_overlap;
