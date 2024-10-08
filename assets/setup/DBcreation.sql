-- Database: `coecsa_thesis`

CREATE SCHEMA IF NOT EXISTS coecsa_thesis;
USE coecsa_thesis;

-- Table structure for table `auth_tokens`
CREATE TABLE IF NOT EXISTS `auth_tokens` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `auth_type` varchar(255) NOT NULL,
  `selector` text NOT NULL,
  `token` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `users`
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usertype` int(1) NOT NULL DEFAULT 1,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) NOT NULL DEFAULT '_defaultUser.png',
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `id` (`id`,`username`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thesis Topics
CREATE TABLE IF NOT EXISTS `thesis_topics` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(100),
  `suggested_by` varchar(100),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Research Titles
CREATE TABLE IF NOT EXISTS `research_titles` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `user_id` int(11) UNSIGNED,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `uniqueness_score` float,
  `feedback` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Defense Schedules
CREATE TABLE IF NOT EXISTS `defense_schedules` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` int(11) UNSIGNED,
  `panelist_id` int(11) UNSIGNED,
  `schedule_date` date,
  `start_time` time,
  `end_time` time,
  `room` varchar(50),
  `status` ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`panelist_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rubrics
CREATE TABLE IF NOT EXISTS `rubrics` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_by` int(11) UNSIGNED,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rubric Criteria
CREATE TABLE IF NOT EXISTS `rubric_criteria` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` int(11) UNSIGNED,
  `criterion` varchar(255) NOT NULL,
  `max_score` int(11),
  `weight` float,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`rubric_id`) REFERENCES `rubrics`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Teams
CREATE TABLE IF NOT EXISTS `teams` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Team Members
CREATE TABLE IF NOT EXISTS `team_members` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` int(11) UNSIGNED,
  `user_id` int(11) UNSIGNED,
  `role` varchar(50),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Requirements
CREATE TABLE IF NOT EXISTS `requirements` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `due_date` date,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Requirements
CREATE TABLE IF NOT EXISTS `user_requirements` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED,
  `requirement_id` int(11) UNSIGNED,
  `status` ENUM('pending', 'submitted', 'approved', 'rejected') DEFAULT 'pending',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `feedback` text,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`requirement_id`) REFERENCES `requirements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Evaluations
CREATE TABLE IF NOT EXISTS `evaluations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `defense_schedule_id` int(11) UNSIGNED,
  `evaluator_id` int(11) UNSIGNED,
  `total_score` float,
  `comments` text,
  `recommendation` ENUM('pass', 'fail', 'revise'),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`defense_schedule_id`) REFERENCES `defense_schedules`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`evaluator_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Evaluation Details
CREATE TABLE IF NOT EXISTS `evaluation_details` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(11) UNSIGNED,
  `criterion_id` int(11) UNSIGNED,
  `score` float,
  `comment` text,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`criterion_id`) REFERENCES `rubric_criteria`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `env_variables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `users`
INSERT INTO `users` (`usertype`, `username`, `email`, `password`, `first_name`, `last_name`, `gender`, `headline`, `bio`, `created_at`) VALUES
(0, 'supahot', 'supa@hot.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Supahot', 'Soverysupahot', 'm', 'Headline of a supa hot user', 'This is the bio of a supa hot user. Now i will say needless stuff to make this longer so this looks like a bio and not anything other than a bio.', NOW());
