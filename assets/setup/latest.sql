-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 05, 2026 at 04:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `icei_38697196_coecsathesis`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `auth_type` varchar(255) NOT NULL,
  `selector` text NOT NULL,
  `token` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `default_schedules`
--

CREATE TABLE `default_schedules` (
  `id` int(11) NOT NULL,
  `program` varchar(255) NOT NULL,
  `year` enum('1','2','3','4','5') NOT NULL,
  `section` int(2) NOT NULL,
  `building` varchar(45) NOT NULL,
  `room` varchar(45) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `class_name` varchar(45) NOT NULL,
  `start_time` varchar(45) NOT NULL,
  `end_time` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_panelists`
--

CREATE TABLE `defense_panelists` (
  `id` int(11) UNSIGNED NOT NULL,
  `defense_id` int(11) UNSIGNED NOT NULL,
  `panelist_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_schedules`
--

CREATE TABLE `defense_schedules` (
  `id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED DEFAULT NULL,
  `panelist_id` int(11) UNSIGNED DEFAULT NULL,
  `panelist_id2` int(11) UNSIGNED DEFAULT NULL,
  `panelist_id3` int(11) UNSIGNED DEFAULT NULL,
  `schedule_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re_defense','general') NOT NULL DEFAULT 'general' COMMENT 'Type of defense being scheduled',
  `related_requirement_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array of team_requirement_files IDs for multi-submission requirements',
  `admin_override_defense_type` tinyint(1) DEFAULT 0 COMMENT 'Whether defense type was manually overridden by admin',
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval_status` enum('pending_chair','pending','approved','rejected') DEFAULT 'pending_chair',
  `is_finalized` tinyint(1) NOT NULL DEFAULT 0,
  `finalized_at` datetime DEFAULT NULL,
  `finalized_by` int(11) UNSIGNED DEFAULT NULL,
  `defense_status` enum('pending','passed','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_type_overrides`
--

CREATE TABLE `defense_type_overrides` (
  `id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `override_type` enum('title_proposal','title_defense','final_defense','re-defense') NOT NULL,
  `reason` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1 COMMENT 'Whether this override is currently active',
  `created_by` int(11) UNSIGNED NOT NULL COMMENT 'Admin user ID who created this override',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Optional expiry date for temporary overrides'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Admin overrides for defense type mapping';

-- --------------------------------------------------------

--
-- Table structure for table `env_variables`
--

CREATE TABLE `env_variables` (
  `id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `env_variables`
--

INSERT INTO `env_variables` (`id`, `key`, `value`, `description`) VALUES
(1, 'APP_NAME_ID', 'ATLAS', 'Application name'),
(2, 'APP_ORGANIZATION', 'LPU-C CoECSA', 'Organization name'),
(3, 'APP_OWNER', '120ms', 'Application owner'),
(4, 'APP_DESCRIPTION', 'taga schedule', 'Application description'),
(5, 'ALLOWED_INACTIVITY_TIME', '7200', ''),
(11, 'MAIL_HOST', 'smtp.gmail.com', 'Mail host'),
(12, 'MAIL_USERNAME', 'ton.agustin09@gmail.com', 'Mail username'),
(13, 'MAIL_PASSWORD', 'kkjh zktq xuhn mhml ', 'Mail password'),
(14, 'MAIL_ENCRYPTION', 'ssl', 'Mail encryption'),
(15, 'MAIL_PORT', '465', 'Mail port'),
(16, 'APP_LOGO_NAVBAR', 'img_69f9cf791950b8.61672100.png', ''),
(17, 'APP_LOGO_FOOTER', 'img_69f9cf86679631.68289516.png', ''),
(18, 'APP_GEMINI_API', '', ''),
(19, 'DEFENSE_APPROVED_FINALIZATION_BACKFILL_DONE', '1', 'One-time backfill marker for legacy approved defense schedules finalized state');

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) UNSIGNED NOT NULL,
  `defense_schedule_id` int(11) UNSIGNED DEFAULT NULL,
  `evaluator_id` int(11) UNSIGNED DEFAULT NULL,
  `total_score` float DEFAULT NULL,
  `pass_fail_status` enum('pass','fail') DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `recommendation` enum('pass','fail','revise minor','revise major') DEFAULT NULL,
  `yes_no` enum('yes','no') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_details`
--

CREATE TABLE `evaluation_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `evaluation_id` int(11) UNSIGNED DEFAULT NULL,
  `rubric_id` int(11) UNSIGNED DEFAULT NULL,
  `criterion_id` int(11) UNSIGNED DEFAULT NULL,
  `student_id` int(11) UNSIGNED DEFAULT NULL,
  `score` float DEFAULT NULL,
  `selected_option` varchar(50) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_per_panel`
--

CREATE TABLE `evaluation_per_panel` (
  `id` int(11) UNSIGNED NOT NULL,
  `defense_schedule_id` int(11) UNSIGNED NOT NULL,
  `evaluator_id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `group_score` float DEFAULT NULL,
  `solo_score` float DEFAULT NULL,
  `total_score` float DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form_assignments`
--

CREATE TABLE `form_assignments` (
  `id` int(11) UNSIGNED NOT NULL,
  `defense_schedule_id` int(11) UNSIGNED NOT NULL,
  `embed_link` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merged_evaluations`
--

CREATE TABLE `merged_evaluations` (
  `id` int(11) UNSIGNED NOT NULL,
  `defense_schedule_id` int(11) UNSIGNED DEFAULT NULL,
  `evaluator_id` int(11) UNSIGNED DEFAULT NULL,
  `student_id` int(11) UNSIGNED DEFAULT NULL,
  `group_score` float DEFAULT NULL,
  `solo_score` float DEFAULT NULL,
  `total_score` float DEFAULT NULL,
  `pass_fail_status` enum('pass','fail') DEFAULT NULL,
  `recommendation` enum('pass','fail','revise minor','revise major') DEFAULT NULL,
  `yes_no` enum('yes','no') DEFAULT NULL,
  `rubric_id` int(11) UNSIGNED DEFAULT NULL,
  `criterion_id` int(11) UNSIGNED DEFAULT NULL,
  `score` float DEFAULT NULL,
  `selected_option` varchar(50) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `detail_comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `type` enum('defense_scheduled','title_approved','requirement_created','requirement_submitted','requirement_approved','requirement_rejected','defense_approval') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID of related entity (team_id, requirement_id, etc.)',
  `related_type` enum('team','requirement','defense_schedule','research_title') DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_actions`
--

CREATE TABLE `notification_actions` (
  `id` int(11) UNSIGNED NOT NULL,
  `notification_id` int(11) UNSIGNED NOT NULL,
  `action_type` enum('approve_defense','reject_defense') NOT NULL,
  `action_data` longtext DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `defense_scheduled` tinyint(1) NOT NULL DEFAULT 1,
  `title_approved` tinyint(1) NOT NULL DEFAULT 1,
  `requirement_created` tinyint(1) NOT NULL DEFAULT 1,
  `requirement_submitted` tinyint(1) NOT NULL DEFAULT 1,
  `requirement_approved` tinyint(1) NOT NULL DEFAULT 1,
  `requirement_rejected` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_content`
--

CREATE TABLE `page_content` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `status` enum('published','draft') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `panelist_approvals`
--

CREATE TABLE `panelist_approvals` (
  `id` int(11) UNSIGNED NOT NULL,
  `defense_schedule_id` int(11) UNSIGNED NOT NULL,
  `panelist_id` int(11) UNSIGNED NOT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `response_date` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `panel_assignment_popup_state`
--

CREATE TABLE `panel_assignment_popup_state` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `schedule_id` int(10) UNSIGNED NOT NULL,
  `notification_id` int(10) UNSIGNED DEFAULT NULL,
  `shown_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `college` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `college`, `department`, `name`, `specialization`, `updated_at`) VALUES
(58, 'College of Allied Medical Sciences', NULL, 'Bachelor of Science in Medical Technology', NULL, '2025-11-20 23:01:31'),
(59, 'College of Allied Medical Sciences', '', 'Bachelor of Science in Pharmacy', '', '2025-11-20 23:01:31'),
(60, 'College of Allied Medical Sciences', NULL, 'Bachelor of Science in Radiologic Technology', NULL, '2025-11-20 23:01:31'),
(61, 'College of Allied Medical Sciences', NULL, 'Bachelor of Science in Biology', NULL, '2025-11-20 23:01:31'),
(62, 'College of Liberal Arts and Education', NULL, 'Bachelor of Arts in Communication', NULL, '2025-04-29 09:02:55'),
(63, 'College of Liberal Arts and Education', NULL, 'Bachelor of Arts in Foreign Service', NULL, '2025-11-20 23:01:57'),
(64, 'College of Liberal Arts and Education', NULL, 'Bachelor of Arts in Legal Studies', NULL, '2025-11-20 23:01:57'),
(65, 'College of Liberal Arts and Education', NULL, 'Bachelor of Early Childhood Education', NULL, '2025-04-29 09:02:55'),
(66, 'College of Liberal Arts and Education', NULL, 'Bachelor in Secondary Education', NULL, '2025-04-29 09:02:55'),
(67, 'College of Liberal Arts and Education', NULL, 'Bachelor of Science in Psychology', NULL, '2025-11-20 23:01:31'),
(68, 'College of Business Administration', NULL, 'Bachelor of Science in Accountancy', NULL, '2025-11-20 23:01:31'),
(69, 'College of Business Administration', NULL, 'Bachelor of Science in Business Administration', 'Human Resource Development Management', '2025-11-20 23:01:31'),
(70, 'College of Business Administration', NULL, 'Bachelor of Science in Business Administration', 'Management Accounting', '2025-11-20 23:01:31'),
(71, 'College of Business Administration', NULL, 'Bachelor of Science in Business Administration', 'Marketing Management', '2025-11-20 23:01:31'),
(72, 'College of Business Administration', NULL, 'Bachelor of Science in Business Administration', 'Operations Management', '2025-11-20 23:01:31'),
(73, 'College of Business Administration', NULL, 'Bachelor of Science in Customs Administration', NULL, '2025-11-20 23:01:31'),
(74, 'College of Business Administration', NULL, 'Bachelor of Science in Entrepreneurship', 'Aesthetics Industry Management', '2025-11-20 23:01:31'),
(75, 'College of Business Administration', NULL, 'Bachelor of Science in Real Estate Management', NULL, '2025-11-20 23:01:31'),
(76, 'College of Engineering and Architecture', 'Architecture', 'Bachelor of Science in Architecture (Arch)', '', '2025-07-20 11:14:34'),
(77, 'College of Information Technology and Computer Science', 'Computer Studies', 'Bachelor of Science in Computer Science', 'Data Science', '2025-12-05 01:47:45'),
(78, 'College of Information Technology and Computer Science', 'Computer Studies', 'Bachelor of Science in Computer Science', 'Software Engineering', '2025-12-05 01:47:40'),
(79, 'College of Information Technology and Computer Science', 'Computer Studies', 'Bachelor of Science in Information Technology', 'Network and Information Security', '2025-12-05 01:47:33'),
(80, 'College of Information Technology and Computer Science', 'Computer Studies', 'Bachelor of Science in Information Technology', 'Web and Mobile Technology', '2025-12-05 01:47:27'),
(81, 'College of Information Technology and Computer Science', 'Computer Studies', 'Bachelor of Library and Information Science', NULL, '2025-12-05 01:47:21'),
(82, 'College of Engineering and Architecture', 'Engineering', 'Bachelor of Science in Aeronautical Engineering', '', '2025-07-20 11:14:26'),
(83, 'College of Engineering and Architecture', 'Engineering', 'Bachelor of Science in Civil Engineering', 'Construction Engineering & Management', '2025-07-20 11:14:15'),
(84, 'College of Engineering and Architecture', 'Engineering', 'Bachelor of Science in Civil Engineering', 'Structural Engineering', '2025-07-20 11:14:03'),
(85, 'College of Engineering and Architecture', 'Engineering', 'Bachelor of Science in Civil Engineering', 'Transportation Engineering', '2025-07-20 11:13:45'),
(86, 'College of Engineering and Architecture', 'Engineering', 'Bachelor of Science in Computer Engineering', '', '2025-07-20 11:13:55'),
(87, 'College of Engineering and Architecture', 'Engineering', 'Bachelor of Engineering Technology', 'Construction Technology and Management', '2025-07-20 11:13:29'),
(88, 'College of Engineering and Architecture', 'Engineering', 'Bachelor of Science in Electrical Engineering', '', '2025-07-20 11:13:19'),
(89, 'College of Engineering and Architecture', 'Engineering', 'Bachelor of Science in Electronics Engineering', '', '2025-07-20 11:13:06'),
(90, 'College of Engineering and Architecture', 'Engineering', 'Bachelor of Science in Industrial Engineering', '', '2025-07-20 11:12:54'),
(91, 'College of Engineering and Architecture', 'Engineering', 'Bachelor of Science in Mechanical Engineering', '', '2025-07-20 11:12:36'),
(92, 'College of Fine Arts and Design', NULL, 'Bachelor of Fine Arts', NULL, '2025-04-29 09:02:55'),
(93, 'College of Fine Arts and Design', '', 'Bachelor of Multimedia Arts', '', '2025-07-20 11:15:44'),
(94, 'College of Fine Arts and Design', NULL, 'Bachelor in Photography', NULL, '2025-04-29 09:02:55'),
(95, 'College of International Tourism and Hospitality Management', NULL, 'Bachelor of Science in International Travel and Tourism Management', NULL, '2025-11-20 23:01:31'),
(96, 'College of International Tourism and Hospitality Management', NULL, 'Bachelor of Science in International Travel and Tourism Management', 'Health and Wellness', '2025-11-20 23:01:31'),
(97, 'College of International Tourism and Hospitality Management', NULL, 'Bachelor of Science in International Hospitality Management', 'Cruise Line Operations in Culinary Arts', '2025-11-20 23:01:31'),
(98, 'College of International Tourism and Hospitality Management', NULL, 'Bachelor of Science in International Hospitality Management', 'Cruise Line Operations in Hotel Services', '2025-11-20 23:01:31'),
(99, 'College of International Tourism and Hospitality Management', NULL, 'Bachelor of Science in International Hospitality Management', 'Culinary Arts and Kitchen Operations', '2025-11-20 23:01:31'),
(100, 'College of International Tourism and Hospitality Management', NULL, 'Bachelor of Science in International Hospitality Management', 'Hotel and Restaurant Administration', '2025-11-20 23:01:31'),
(101, 'College of International Tourism and Hospitality Management', NULL, 'Bachelor of Science in Nutrition and Dietetics', NULL, '2025-11-20 23:01:31'),
(102, 'College of Nursing', NULL, 'Bachelor of Science in Nursing', NULL, '2025-11-20 23:01:31'),
(103, 'College of Law', NULL, 'Juris Doctor', NULL, '2025-04-29 09:02:55'),
(104, 'Claro M. Recto Academy of Advanced Studies', NULL, 'Master of Arts in Education', 'Educational Management', '2025-04-29 09:02:55'),
(105, 'Claro M. Recto Academy of Advanced Studies', NULL, 'Master in Business Administration', NULL, '2025-04-29 09:02:55'),
(106, 'Claro M. Recto Academy of Advanced Studies', NULL, 'Master in International Hospitality Management', NULL, '2025-04-29 09:02:55'),
(107, 'Claro M. Recto Academy of Advanced Studies', NULL, 'Master in International Travel and Tourism Management', NULL, '2025-04-29 09:02:55'),
(108, 'Claro M. Recto Academy of Advanced Studies', NULL, 'Master in Public Administration', NULL, '2025-04-29 09:02:55'),
(109, 'Claro M. Recto Academy of Advanced Studies', NULL, 'Ph.D. in Business Management', NULL, '2025-04-29 09:02:55'),
(110, 'Claro M. Recto Academy of Advanced Studies', NULL, 'Ph.D. in Public Policy and Management', NULL, '2025-04-29 09:02:55'),
(111, 'Claro M. Recto Academy of Advanced Studies', NULL, 'Ph.D. in International Hospitality Management', NULL, '2025-04-29 09:02:55'),
(112, 'Claro M. Recto Academy of Advanced Studies', NULL, 'Ph.D. in International Tourism Management', NULL, '2025-04-29 09:02:55'),
(113, 'Claro M. Recto Academy of Advanced Studies', NULL, 'Ph.D. in English Language', NULL, '2025-04-29 09:02:55');

-- --------------------------------------------------------

--
-- Table structure for table `program_manuscript_requirements`
--

CREATE TABLE `program_manuscript_requirements` (
  `id` int(11) NOT NULL,
  `requirement_id` int(10) UNSIGNED NOT NULL,
  `program_id` int(11) NOT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re-defense','general') NOT NULL,
  `is_required` tinyint(1) DEFAULT 1 COMMENT 'Is this manuscript required for this combination?',
  `submission_stage` enum('before_defense','at_defense','optional') DEFAULT 'before_defense' COMMENT 'When should it be submitted?',
  `can_revise_after` tinyint(1) DEFAULT 0 COMMENT 'Can team revise after this stage?',
  `visibility_to_panelist` tinyint(1) DEFAULT 1 COMMENT 'Should panelist see this at defense?',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Maps manuscript requirements to specific program + defense type combinations';

-- --------------------------------------------------------

--
-- Table structure for table `program_manuscript_table`
--

CREATE TABLE `program_manuscript_table` (
  `mapping_id` int(11) NOT NULL,
  `requirement_id` int(11) UNSIGNED NOT NULL,
  `requirement_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_defense_manuscript` tinyint(1) DEFAULT 0,
  `program_id` int(11) NOT NULL,
  `program_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re-defense','general') NOT NULL,
  `is_required` tinyint(1) DEFAULT 1 COMMENT 'Is this manuscript required for this combination?',
  `submission_stage` enum('before_defense','at_defense','optional') DEFAULT 'before_defense' COMMENT 'When should it be submitted?',
  `can_revise_after` tinyint(1) DEFAULT 0 COMMENT 'Can team revise after this stage?',
  `visibility_to_panelist` tinyint(1) DEFAULT 1 COMMENT 'Should panelist see this at defense?'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_manuscript_view`
--

CREATE TABLE `program_manuscript_view` (
  `mapping_id` int(11) DEFAULT NULL,
  `requirement_id` int(11) UNSIGNED DEFAULT NULL,
  `requirement_name` varchar(255) DEFAULT NULL,
  `is_defense_manuscript` tinyint(1) DEFAULT NULL,
  `program_id` int(11) DEFAULT NULL,
  `program_name` varchar(255) DEFAULT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re-defense','general') DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT NULL,
  `submission_stage` enum('before_defense','at_defense','optional') DEFAULT NULL,
  `can_revise_after` tinyint(1) DEFAULT NULL,
  `visibility_to_panelist` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_requirements_mapping`
--

CREATE TABLE `program_requirements_mapping` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re-defense','general') NOT NULL,
  `requirement_id` int(11) UNSIGNED NOT NULL,
  `is_mandatory` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requirements`
--

CREATE TABLE `requirements` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `requirement_type` enum('title_proposal','title_defense','final_defense','re-defense','general') DEFAULT 'general',
  `is_defense_manuscript` tinyint(1) DEFAULT 0,
  `allow_multiple_submissions` tinyint(1) DEFAULT 0 COMMENT 'Whether teams can submit multiple files (max 3) for this requirement',
  `max_submissions` int(11) DEFAULT 1 COMMENT 'Maximum number of submissions allowed (default 1, max 3)',
  `description` text DEFAULT NULL,
  `template_file` varchar(255) DEFAULT NULL,
  `template_original_name` varchar(255) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requirements`
--

INSERT INTO `requirements` (`id`, `name`, `requirement_type`, `is_defense_manuscript`, `allow_multiple_submissions`, `max_submissions`, `description`, `template_file`, `template_original_name`, `due_date`, `created_at`) VALUES
(2, 'Capstone 2', 'final_defense', 1, 0, 1, 'This includes the template', '687d025450e81_1753023060.docx', 'CAPSTONE 1-2 TEMPLATES.docx', '2030-10-31', '2024-11-11 02:52:54'),
(3, 'Capstone 1', 'title_defense', 0, 0, 1, 'This includes the template\r\n(IT ONLY)', '687d0244896eb_1753023044.docx', 'CAPSTONE 1-2 TEMPLATES.docx', '2030-11-30', '2024-11-11 02:52:41'),
(5, 'Final Manuscript', 'final_defense', 1, 0, 1, 'Also used for Research Repository (DO NOT REMOVE)', '687d0263dbbb8_1753023075.docx', 'FULL MANUSCRIPT_template_crd2025.docx', '2030-12-04', '2024-11-11 02:52:22'),
(46, 'Research methods Template A', 'title_proposal', 1, 1, 3, 'Template of title proposal template', NULL, NULL, '2030-11-28', '2025-11-21 06:47:43'),
(47, 'Thesis 1', 'title_defense', 1, 0, 1, 'CS only', NULL, NULL, '2030-12-31', '2025-12-01 09:50:13');

-- --------------------------------------------------------

--
-- Table structure for table `research_titles`
--

CREATE TABLE `research_titles` (
  `id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `program` varchar(255) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `defended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `research_titles`
--

INSERT INTO `research_titles` (`id`, `team_id`, `title`, `program`, `approved_at`, `defended_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'atlas', NULL, NULL, NULL, '2026-05-05 13:53:52', '2026-05-05 13:53:52');

-- --------------------------------------------------------

--
-- Table structure for table `re_defense_assessments`
--

CREATE TABLE `re_defense_assessments` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `defense_schedule_id` int(11) NOT NULL,
  `reason_for_redefense` text DEFAULT NULL,
  `initial_defense_schedule_id` int(11) DEFAULT NULL,
  `status` enum('pending','completed','passed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `initiated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rubrics`
--

CREATE TABLE `rubrics` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `rubric_type` enum('numerical','yesno','passfail') NOT NULL DEFAULT 'numerical',
  `is_individual_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `defense_type` varchar(50) DEFAULT NULL,
  `rubric_description` text DEFAULT NULL,
  `pass_recommendation_text` varchar(255) DEFAULT NULL,
  `fail_recommendation_text` varchar(255) DEFAULT NULL,
  `fail_option_text` text DEFAULT NULL,
  `pass_threshold_1` decimal(5,2) DEFAULT NULL,
  `pass_threshold_2` decimal(5,2) DEFAULT NULL,
  `pass_threshold_3` decimal(5,2) DEFAULT NULL,
  `max_total_score` int(11) DEFAULT 0,
  `max_members` int(11) DEFAULT 5,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `max_score_per_criterion` int(11) DEFAULT 100 COMMENT 'Maximum score allowed per criterion for individual scoring mode'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rubric_criteria`
--

CREATE TABLE `rubric_criteria` (
  `id` int(11) UNSIGNED NOT NULL,
  `rubric_id` int(11) UNSIGNED NOT NULL COMMENT 'FK to rubrics table',
  `criterion_text` text NOT NULL COMMENT 'The main text for the criterion row',
  `criterion_detail` text DEFAULT NULL COMMENT 'Optional secondary description (e.g., for Yes/No)',
  `order_index` int(11) NOT NULL COMMENT 'Order of this criterion row within the rubric',
  `is_individual` tinyint(1) NOT NULL DEFAULT 0,
  `max_score` int(11) DEFAULT NULL COMMENT 'Maximum score for this criterion (used for individual scoring in numerical rubrics)',
  `min_score` int(11) DEFAULT 0 COMMENT 'Minimum score for this criterion (used for individual scoring in numerical rubrics)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_blank` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores criteria rows for Numerical and Yes/No rubrics';

-- --------------------------------------------------------

--
-- Table structure for table `rubric_groups`
--

CREATE TABLE `rubric_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re_defense','general') DEFAULT 'general',
  `program_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rubric_group_items`
--

CREATE TABLE `rubric_group_items` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `rubric_id` int(11) UNSIGNED NOT NULL,
  `order_index` int(11) NOT NULL DEFAULT 0,
  `weight` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rubric_levels`
--

CREATE TABLE `rubric_levels` (
  `id` int(11) UNSIGNED NOT NULL,
  `rubric_id` int(11) UNSIGNED NOT NULL COMMENT 'FK to rubrics table',
  `level_index` tinyint(3) UNSIGNED NOT NULL COMMENT 'Order of the level/modifier (1, 2, 3...)',
  `name` varchar(100) NOT NULL COMMENT 'Name of the level (e.g., Excellent) or Pass Modifier',
  `description` text DEFAULT NULL COMMENT 'Description of the level or Pass Modifier',
  `points_min` int(11) DEFAULT NULL COMMENT 'Numerical Only: Min points for this level',
  `points_max` int(11) DEFAULT NULL COMMENT 'Numerical Only: Max points (same as min if not range)',
  `is_range` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Numerical Only: 1 if points_min/max define a range, 0 otherwise',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores Numerical quality levels or Pass/Fail modifier definitions';

-- --------------------------------------------------------

--
-- Table structure for table `rubric_programs`
--

CREATE TABLE `rubric_programs` (
  `rubric_id` int(10) UNSIGNED NOT NULL,
  `program_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Associates rubrics with specific programs';

-- --------------------------------------------------------

--
-- Table structure for table `schedule_progress`
--

CREATE TABLE `schedule_progress` (
  `id` varchar(50) NOT NULL,
  `status` enum('running','completed','error') DEFAULT 'running',
  `message` text DEFAULT NULL,
  `percentage` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_progress`
--

INSERT INTO `schedule_progress` (`id`, `status`, `message`, `percentage`, `created_at`, `updated_at`) VALUES
('sched_69f9f802b1c460.65208303', 'completed', 'Preview ready — conflict-free after validation. 3 layout option(s): use the dropdown to compare.', 100, '2026-05-05 14:00:34', '2026-05-05 14:00:34'),
('sched_69f9f8bf2297c5.18979584', 'completed', 'Preview ready — conflict-free after validation. 3 layout option(s): use the dropdown to compare.', 100, '2026-05-05 14:03:43', '2026-05-05 14:03:43'),
('sched_69f9f8c131fdb3.37160597', 'completed', 'Preview ready — conflict-free after validation. 3 layout option(s): use the dropdown to compare.', 100, '2026-05-05 14:03:45', '2026-05-05 14:03:45'),
('sched_69f9f8c32ff823.35940400', 'completed', 'Preview ready — conflict-free after validation. 3 layout option(s): use the dropdown to compare.', 100, '2026-05-05 14:03:47', '2026-05-05 14:03:47'),
('sched_69f9f92a904b61.02555836', 'completed', 'Preview ready — conflict-free after validation. 3 layout option(s): use the dropdown to compare.', 100, '2026-05-05 14:05:30', '2026-05-05 14:05:30'),
('sched_69f9f937751ae3.02357446', 'completed', 'Preview ready — conflict-free after validation. 3 layout option(s): use the dropdown to compare.', 100, '2026-05-05 14:05:43', '2026-05-05 14:05:43'),
('sched_69f9f959d4aff1.21845269', 'completed', 'Preview ready — conflict-free after validation. 3 layout option(s): use the dropdown to compare.', 100, '2026-05-05 14:06:17', '2026-05-05 14:06:18');

-- --------------------------------------------------------

--
-- Table structure for table `section_professors`
--

CREATE TABLE `section_professors` (
  `id` int(11) NOT NULL,
  `section` varchar(255) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `academic_year` varchar(100) DEFAULT NULL COMMENT 'Academic year, e.g. 2025-2026, 1st Semester',
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_professors`
--

INSERT INTO `section_professors` (`id`, `section`, `professor_id`, `status`, `academic_year`, `assigned_by`, `assigned_at`) VALUES
(2, 'CS401', 490, 'active', '2026-2027, 1st Semester', 488, '2026-05-05 13:42:43');

-- --------------------------------------------------------

--
-- Table structure for table `specialization_pool`
--

CREATE TABLE `specialization_pool` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `college` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `team_code` varchar(50) NOT NULL,
  `program` varchar(255) NOT NULL,
  `area_of_expertise` varchar(255) DEFAULT NULL,
  `next_defense_type` varchar(50) DEFAULT 'title_proposal' COMMENT 'Next defense type for automatic progression: title_proposal, title_defense, final_defense, re_defense',
  `title_proposal` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark team as title proposal - professor role will be automatic',
  `locked_panelist1` int(11) UNSIGNED DEFAULT NULL,
  `locked_panelist2` int(11) UNSIGNED DEFAULT NULL,
  `locked_panelist3` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `created_at`, `team_code`, `program`, `area_of_expertise`, `next_defense_type`, `title_proposal`, `locked_panelist1`, `locked_panelist2`, `locked_panelist3`) VALUES
(1, 'atlas', '2026-05-05 13:53:52', 'CS2627-1-001', 'Bachelor of Science in Computer Science - Software Engineering', 'Software Engineering', 'title_proposal', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `team_defense_status`
--

CREATE TABLE `team_defense_status` (
  `team_id` int(11) UNSIGNED DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `current_defense_type` varchar(14) DEFAULT NULL,
  `approved_titles` bigint(21) DEFAULT NULL,
  `completed_evaluations` bigint(21) DEFAULT NULL,
  `override_defense_type` enum('title_proposal','title_defense','final_defense','re-defense') DEFAULT NULL,
  `override_active` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_defense_status_table`
--

CREATE TABLE `team_defense_status_table` (
  `team_id` int(11) UNSIGNED NOT NULL,
  `team_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `current_defense_type` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approved_titles` bigint(21) NOT NULL,
  `completed_evaluations` bigint(21) NOT NULL,
  `override_defense_type` enum('title_proposal','title_defense','final_defense','re-defense') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `override_active` tinyint(1) DEFAULT NULL COMMENT 'Whether this override is currently active'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED DEFAULT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `team_id`, `user_id`, `role`) VALUES
(1, 1, 489, 'adviser'),
(2, 1, 484, 'leader');

-- --------------------------------------------------------

--
-- Table structure for table `team_panelists`
--

CREATE TABLE `team_panelists` (
  `id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re-defense') NOT NULL,
  `panelist_id` int(11) UNSIGNED NOT NULL,
  `panelist_position` int(1) DEFAULT 1 COMMENT 'Position: 1=primary, 2=secondary, 3=tertiary',
  `locked` tinyint(1) DEFAULT 0 COMMENT 'Whether this assignment is locked and cannot be changed',
  `admin_override` tinyint(1) DEFAULT 0 COMMENT 'Whether this assignment was manually set by admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'User ID who created/locked this assignment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks persistent panelist assignments across defense stages';

-- --------------------------------------------------------

--
-- Table structure for table `team_requirements`
--

CREATE TABLE `team_requirements` (
  `id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED DEFAULT NULL,
  `requirement_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('pending','submitted','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `feedback_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_requirement_files`
--

CREATE TABLE `team_requirement_files` (
  `id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `requirement_id` int(11) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `submission_number` int(11) DEFAULT 1 COMMENT 'For multi-submission requirements: 1, 2, or 3',
  `status` enum('pending','submitted','approved','rejected') DEFAULT 'submitted',
  `feedback` text DEFAULT NULL,
  `feedback_file` varchar(255) DEFAULT NULL,
  `submitted_by` int(11) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Individual file submissions for requirements with multi-submission support';

-- --------------------------------------------------------

--
-- Table structure for table `team_specializations`
--

CREATE TABLE `team_specializations` (
  `id` int(11) NOT NULL,
  `team_id` int(10) UNSIGNED NOT NULL,
  `specialization_id` int(11) NOT NULL,
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thesis_topics`
--

CREATE TABLE `thesis_topics` (
  `id` int(11) UNSIGNED NOT NULL,
  `topic` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

CREATE TABLE `uploaded_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(512) NOT NULL,
  `filesize` int(11) NOT NULL,
  `filetype` varchar(50) NOT NULL,
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `college_name` varchar(255) DEFAULT NULL,
  `program_id` int(11) DEFAULT NULL,
  `team_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `usertype` int(1) NOT NULL DEFAULT 1,
  `username` varchar(255) NOT NULL,
  `program` varchar(255) DEFAULT NULL,
  `area_of_expertise` varchar(255) DEFAULT NULL,
  `is_parttime` int(1) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
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
  `is_program_chair` int(1) DEFAULT NULL,
  `is_external` tinyint(1) DEFAULT 0 COMMENT 'Mark usertype 2 as external panelist (pure panelist, not faculty)',
  `year` int(1) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `usertype`, `username`, `program`, `area_of_expertise`, `is_parttime`, `email`, `password`, `first_name`, `middle_name`, `last_name`, `gender`, `headline`, `bio`, `profile_image`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`, `is_program_chair`, `year`, `section`) VALUES
(0, 0, 'Admin', NULL, '', 0, 'ton.agustin09@gmail.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Calamyty', 'Chaos', 'Mytryx', 'm', 'SUPER ADMIN', '', 'profile_69f9cfc42573f9.00926720.png', '2024-10-05 05:55:38', '2024-10-05 05:55:38', '2026-05-05 11:12:47', '0000-00-00 00:00:00', '2026-05-05 11:12:47', 0, NULL, NULL),
(484, 1, 'aa.aa', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'aa.aa@aa.aa', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'aa', 'aa', 'aa', NULL, '', '', '_defaultUser.png', '2024-10-05 05:55:38', NULL, '2026-05-05 13:42:25', NULL, NULL, 0, 4, 'CS401'),
(485, 2, 'zz.zz', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev', 0, 'zz.zz@zz.zz', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'zz', 'zz', 'zz', NULL, NULL, NULL, '_defaultUser.png', '2024-10-05 05:55:38', NULL, '2026-05-05 13:39:07', NULL, NULL, NULL, NULL, NULL),
(486, 2, 'xx.xx', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev', 0, 'xx.xx@xx.xx', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'xx', 'xx', 'xx', NULL, NULL, NULL, '_defaultUser.png', '2024-10-05 05:55:38', NULL, '2026-05-05 13:39:09', NULL, NULL, NULL, NULL, NULL),
(487, 2, 'yy.yy', 'Bachelor of Science in Computer Engineering', 'Hardware Electronics', 1, 'yy.yy@yy.yy', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'yy', 'yy', 'yy', NULL, '', '', '_defaultUser.png', '2024-10-05 05:55:38', NULL, '2026-05-05 13:39:11', NULL, NULL, 0, NULL, NULL),
(488, 0, 'pc.pc', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'pc.pc@pc.pc', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'pc', 'pc', 'pc', NULL, NULL, NULL, '_defaultUser.png', '2024-10-05 05:55:38', NULL, '2026-05-05 13:39:32', NULL, '2026-05-05 13:39:32', 0, NULL, NULL),
(489, 2, 'adv.adv', 'Bachelor of Science in Computer Science - Software Engineering', 'Software Engineering', 0, 'adv.adv@adv.adv', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'adv', 'adv', 'adv', NULL, NULL, NULL, '_defaultUser.png', '2024-10-05 05:55:38', NULL, '2026-05-05 13:39:15', NULL, NULL, NULL, NULL, NULL),
(490, 2, 'fac.fac', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev', 0, 'fac.fac@fac.fac', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'fac', 'fac', 'fac', NULL, NULL, NULL, '_defaultUser.png', '2024-10-05 05:55:38', NULL, '2026-05-05 13:41:59', NULL, '2026-05-05 13:41:59', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_schedules`
--

CREATE TABLE `user_schedules` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `program` varchar(255) DEFAULT NULL,
  `section` varchar(255) DEFAULT NULL,
  `room` varchar(255) DEFAULT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `allow_overlap` tinyint(1) NOT NULL DEFAULT 0,
  `is_research_class` tinyint(1) NOT NULL DEFAULT 0,
  `year` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_schedules`
--

INSERT INTO `user_schedules` (`id`, `user_id`, `program`, `section`, `room`, `day_of_week`, `start_time`, `end_time`, `class_name`, `allow_overlap`, `is_research_class`, `year`) VALUES
(1, 490, '78', 'CS401', 'C301', 'Monday', '07:00:00', '11:00:00', 'thelc01c', 0, 0, 4);

-- --------------------------------------------------------

--
-- Table structure for table `user_specializations`
--

CREATE TABLE `user_specializations` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `specialization_id` int(11) NOT NULL,
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `default_schedules`
--
ALTER TABLE `default_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `defense_panelists`
--
ALTER TABLE `defense_panelists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `defense_id` (`defense_id`),
  ADD KEY `panelist_id` (`panelist_id`);

--
-- Indexes for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_defense_type` (`defense_type`),
  ADD KEY `idx_defense_finalized` (`is_finalized`),
  ADD KEY `idx_defense_schedule_time` (`schedule_date`,`start_time`,`end_time`),
  ADD KEY `idx_defense_team` (`team_id`);

--
-- Indexes for table `defense_type_overrides`
--
ALTER TABLE `defense_type_overrides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_team_active` (`team_id`,`active`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `env_variables`
--
ALTER TABLE `env_variables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `defense_schedule_id` (`defense_schedule_id`),
  ADD KEY `evaluator_id` (`evaluator_id`);

--
-- Indexes for table `evaluation_details`
--
ALTER TABLE `evaluation_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_id` (`evaluation_id`),
  ADD KEY `criterion_id` (`criterion_id`),
  ADD KEY `fk_evaluation_details_rubric` (`rubric_id`),
  ADD KEY `fk_evaluation_details_student` (`student_id`);

--
-- Indexes for table `evaluation_per_panel`
--
ALTER TABLE `evaluation_per_panel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `defense_schedule_id` (`defense_schedule_id`),
  ADD KEY `evaluator_id` (`evaluator_id`),
  ADD KEY `evalusations_per_panel_ibfk_3_idx` (`student_id`);

--
-- Indexes for table `form_assignments`
--
ALTER TABLE `form_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `defense_schedule_id` (`defense_schedule_id`);

--
-- Indexes for table `merged_evaluations`
--
ALTER TABLE `merged_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `defense_schedule_id` (`defense_schedule_id`),
  ADD KEY `evaluator_id` (`evaluator_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `rubric_id` (`rubric_id`),
  ADD KEY `criterion_id` (`criterion_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_notifications_type` (`type`),
  ADD KEY `idx_notifications_related` (`related_id`,`related_type`);

--
-- Indexes for table `notification_actions`
--
ALTER TABLE `notification_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_id` (`notification_id`);

--
-- Indexes for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `page_content`
--
ALTER TABLE `page_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `panelist_approvals`
--
ALTER TABLE `panelist_approvals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_panelist_defense` (`defense_schedule_id`,`panelist_id`),
  ADD KEY `panelist_id` (`panelist_id`),
  ADD KEY `idx_panelist_approvals_status` (`approval_status`);

--
-- Indexes for table `panel_assignment_popup_state`
--
ALTER TABLE `panel_assignment_popup_state`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_schedule` (`user_id`,`schedule_id`),
  ADD KEY `idx_user_shown` (`user_id`,`shown_at`),
  ADD KEY `idx_schedule` (`schedule_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `program_manuscript_requirements`
--
ALTER TABLE `program_manuscript_requirements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_req_program_defense` (`requirement_id`,`program_id`,`defense_type`),
  ADD KEY `idx_program_defense_type` (`program_id`,`defense_type`),
  ADD KEY `idx_requirement_program` (`requirement_id`,`program_id`),
  ADD KEY `idx_is_required` (`is_required`);

--
-- Indexes for table `program_requirements_mapping`
--
ALTER TABLE `program_requirements_mapping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_program_defense_requirement` (`program_id`,`defense_type`,`requirement_id`),
  ADD KEY `idx_program_defense` (`program_id`,`defense_type`),
  ADD KEY `idx_program_requirements` (`program_id`,`requirement_id`);

--
-- Indexes for table `requirements`
--
ALTER TABLE `requirements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `research_titles`
--
ALTER TABLE `research_titles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `re_defense_assessments`
--
ALTER TABLE `re_defense_assessments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_team_redefense` (`team_id`,`defense_schedule_id`),
  ADD KEY `idx_team_redefense` (`team_id`,`status`),
  ADD KEY `idx_defense_schedule` (`defense_schedule_id`);

--
-- Indexes for table `rubrics`
--
ALTER TABLE `rubrics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rubric_criteria`
--
ALTER TABLE `rubric_criteria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rubric_criterion_order` (`rubric_id`,`order_index`),
  ADD KEY `rubric_id` (`rubric_id`);

--
-- Indexes for table `rubric_groups`
--
ALTER TABLE `rubric_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_defense_program` (`defense_type`,`program_id`);

--
-- Indexes for table `rubric_group_items`
--
ALTER TABLE `rubric_group_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id_idx` (`group_id`),
  ADD KEY `rubric_id_idx` (`rubric_id`);

--
-- Indexes for table `rubric_levels`
--
ALTER TABLE `rubric_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rubric_level_order` (`rubric_id`,`level_index`),
  ADD KEY `rubric_id` (`rubric_id`);

--
-- Indexes for table `rubric_programs`
--
ALTER TABLE `rubric_programs`
  ADD PRIMARY KEY (`rubric_id`,`program_name`),
  ADD KEY `fk_rubric_programs_rubric_id_idx` (`rubric_id`);

--
-- Indexes for table `schedule_progress`
--
ALTER TABLE `schedule_progress`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `section_professors`
--
ALTER TABLE `section_professors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section_professor` (`section`,`professor_id`),
  ADD KEY `idx_professor_id` (`professor_id`),
  ADD KEY `idx_section` (`section`);

--
-- Indexes for table `specialization_pool`
--
ALTER TABLE `specialization_pool`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_specialization` (`name`,`department`,`college`),
  ADD KEY `idx_college` (`college`),
  ADD KEY `idx_department` (`department`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_teams_team_code` (`team_code`),
  ADD KEY `idx_next_defense_type` (`next_defense_type`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `team_panelists`
--
ALTER TABLE `team_panelists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_team_defense_panelist` (`team_id`,`defense_type`,`panelist_id`),
  ADD KEY `idx_team_defense` (`team_id`,`defense_type`),
  ADD KEY `idx_panelist` (`panelist_id`);

--
-- Indexes for table `team_requirements`
--
ALTER TABLE `team_requirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requirement_id` (`requirement_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `team_requirement_files`
--
ALTER TABLE `team_requirement_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_team_requirement` (`team_id`,`requirement_id`),
  ADD KEY `idx_team` (`team_id`),
  ADD KEY `idx_requirement` (`requirement_id`),
  ADD KEY `idx_submission_number` (`submission_number`),
  ADD KEY `submitted_by` (`submitted_by`);

--
-- Indexes for table `team_specializations`
--
ALTER TABLE `team_specializations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_team_specialization` (`team_id`,`specialization_id`),
  ADD KEY `specialization_id` (`specialization_id`);

--
-- Indexes for table `thesis_topics`
--
ALTER TABLE `thesis_topics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_college` (`college_name`),
  ADD KEY `idx_program` (`program_id`),
  ADD KEY `idx_team` (`team_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `id` (`id`,`username`,`email`);

--
-- Indexes for table `user_schedules`
--
ALTER TABLE `user_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_specializations`
--
ALTER TABLE `user_specializations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_specialization` (`user_id`,`specialization_id`),
  ADD KEY `specialization_id` (`specialization_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_panelists`
--
ALTER TABLE `defense_panelists`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_type_overrides`
--
ALTER TABLE `defense_type_overrides`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `env_variables`
--
ALTER TABLE `env_variables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluation_details`
--
ALTER TABLE `evaluation_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluation_per_panel`
--
ALTER TABLE `evaluation_per_panel`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `form_assignments`
--
ALTER TABLE `form_assignments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merged_evaluations`
--
ALTER TABLE `merged_evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_actions`
--
ALTER TABLE `notification_actions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `panelist_approvals`
--
ALTER TABLE `panelist_approvals`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `panel_assignment_popup_state`
--
ALTER TABLE `panel_assignment_popup_state`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `program_manuscript_requirements`
--
ALTER TABLE `program_manuscript_requirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_requirements_mapping`
--
ALTER TABLE `program_requirements_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requirements`
--
ALTER TABLE `requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `research_titles`
--
ALTER TABLE `research_titles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `re_defense_assessments`
--
ALTER TABLE `re_defense_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rubrics`
--
ALTER TABLE `rubrics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rubric_criteria`
--
ALTER TABLE `rubric_criteria`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rubric_groups`
--
ALTER TABLE `rubric_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rubric_group_items`
--
ALTER TABLE `rubric_group_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rubric_levels`
--
ALTER TABLE `rubric_levels`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `section_professors`
--
ALTER TABLE `section_professors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `specialization_pool`
--
ALTER TABLE `specialization_pool`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `team_panelists`
--
ALTER TABLE `team_panelists`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_requirements`
--
ALTER TABLE `team_requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_requirement_files`
--
ALTER TABLE `team_requirement_files`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_specializations`
--
ALTER TABLE `team_specializations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `thesis_topics`
--
ALTER TABLE `thesis_topics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=491;

--
-- AUTO_INCREMENT for table `user_schedules`
--
ALTER TABLE `user_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_specializations`
--
ALTER TABLE `user_specializations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `defense_panelists`
--
ALTER TABLE `defense_panelists`
  ADD CONSTRAINT `defense_panelists_ibfk_1` FOREIGN KEY (`defense_id`) REFERENCES `defense_schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `defense_panelists_ibfk_2` FOREIGN KEY (`panelist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `defense_type_overrides`
--
ALTER TABLE `defense_type_overrides`
  ADD CONSTRAINT `defense_type_overrides_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `defense_type_overrides_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`defense_schedule_id`) REFERENCES `defense_schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `evaluation_per_panel`
--
ALTER TABLE `evaluation_per_panel`
  ADD CONSTRAINT `evalusations_per_panel_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `notification_actions`
--
ALTER TABLE `notification_actions`
  ADD CONSTRAINT `notification_actions_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_panelists`
--
ALTER TABLE `team_panelists`
  ADD CONSTRAINT `team_panelists_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_panelists_ibfk_2` FOREIGN KEY (`panelist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_requirement_files`
--
ALTER TABLE `team_requirement_files`
  ADD CONSTRAINT `team_requirement_files_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_requirement_files_ibfk_2` FOREIGN KEY (`requirement_id`) REFERENCES `requirements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_requirement_files_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `team_specializations`
--
ALTER TABLE `team_specializations`
  ADD CONSTRAINT `team_specializations_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_specializations_ibfk_2` FOREIGN KEY (`specialization_id`) REFERENCES `specialization_pool` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_specializations`
--
ALTER TABLE `user_specializations`
  ADD CONSTRAINT `user_specializations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_specializations_ibfk_2` FOREIGN KEY (`specialization_id`) REFERENCES `specialization_pool` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
