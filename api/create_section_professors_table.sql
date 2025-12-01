-- Simple section_professors table for class professor assignments
-- Uses section as string (from users.section column) instead of referencing sections table

CREATE TABLE IF NOT EXISTS `section_professors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(255) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_section_professor` (`section`, `professor_id`),
  FOREIGN KEY (`professor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_professor_id` (`professor_id`),
  INDEX `idx_section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
