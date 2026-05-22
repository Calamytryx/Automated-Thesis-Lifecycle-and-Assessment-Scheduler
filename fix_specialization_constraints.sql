-- Fix Specialization System for Multiple Assignments

-- 1. Drop unique constraints to allow multiple specializations per team/user
ALTER TABLE team_specializations DROP INDEX unique_team_specialization;
ALTER TABLE user_specializations DROP INDEX unique_user_specialization;

-- 2. Add team specialization field to teams table for genetic algorithm
ALTER TABLE teams ADD COLUMN IF NOT EXISTS specialization_focus VARCHAR(255) DEFAULT NULL AFTER program;

-- Done! Now teams and users can have multiple specializations
