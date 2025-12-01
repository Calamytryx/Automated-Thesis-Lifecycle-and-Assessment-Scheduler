-- ============================================================================
-- Professor Assignment System Migration
-- Replace views with proper database tables for section-based professor assignments
-- ============================================================================

-- Step 1: Create section_professors table
-- Maps professors (faculty members) to sections/courses
CREATE TABLE IF NOT EXISTS `section_professors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `assignment_type` enum('primary', 'secondary', 'tertiary') NOT NULL DEFAULT 'primary',
  `status` enum('active', 'inactive', 'pending') NOT NULL DEFAULT 'active',
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_section_professor` (`section_id`, `professor_id`),
  FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`professor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_professor_id` (`professor_id`),
  INDEX `idx_section_id` (`section_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 2: Create team_professor_assignments table
-- Tracks assignments of professors to teams/groups for specific defenses
CREATE TABLE IF NOT EXISTS `team_professor_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `defense_type` enum('title_proposal', 'title_defense', 'final_defense', 're-defense', 'general') NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `assignment_status` enum('pending', 'accepted', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
  `assignment_notes` text DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_professor_defense` (`team_id`, `professor_id`, `defense_type`),
  FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`professor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_professor_id` (`professor_id`),
  INDEX `idx_team_id` (`team_id`),
  INDEX `idx_defense_type` (`defense_type`),
  INDEX `idx_assignment_status` (`assignment_status`),
  INDEX `idx_section_id` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 3: Create professor_assignment_history table
-- Audit trail for all professor assignments
CREATE TABLE IF NOT EXISTS `professor_assignment_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) DEFAULT NULL,
  `team_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `defense_type` varchar(50) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `action_by` int(11) NOT NULL,
  `action_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`professor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`action_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  INDEX `idx_team_id` (`team_id`),
  INDEX `idx_professor_id` (`professor_id`),
  INDEX `idx_defense_type` (`defense_type`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- DATA INTEGRITY CHECKS
-- ============================================================================

-- Verify sections table exists
-- If sections table doesn't exist, create a simple one
CREATE TABLE IF NOT EXISTS `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `course_code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `academic_year` varchar(50) DEFAULT NULL,
  `semester` enum('1', '2', 'summer') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_section_code` (`course_code`, `academic_year`, `semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- SAMPLE DATA (Optional - for testing)
-- ============================================================================

-- Note: Uncomment to insert sample data after verifying your teams and users

-- INSERT INTO section_professors (section_id, professor_id, assignment_type, status, assigned_by)
-- SELECT DISTINCT 1, u.id, 'primary', 'active', 1
-- FROM users u 
-- WHERE u.usertype = 2 AND u.id NOT IN (SELECT professor_id FROM section_professors)
-- LIMIT 5;
