-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 21, 2026 at 01:00 AM
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

--
-- Dumping data for table `defense_schedules`
--

INSERT INTO `defense_schedules` (`id`, `team_id`, `panelist_id`, `panelist_id2`, `panelist_id3`, `schedule_date`, `start_time`, `end_time`, `room`, `defense_type`, `related_requirement_files`, `admin_override_defense_type`, `status`, `created_at`, `approval_status`, `is_finalized`, `finalized_at`, `finalized_by`, `defense_status`) VALUES
(84, 1, 482, 488, 496, '2026-05-13', '17:00:00', '19:00:00', 'Defense Room 2', 'title_proposal', NULL, 0, 'scheduled', '2026-05-12 13:24:02', 'pending_chair', 0, NULL, NULL, 'pending'),
(85, 3, 499, 487, 489, '2026-05-13', '08:00:00', '10:00:00', 'Defense Room 2', 'title_proposal', NULL, 0, 'scheduled', '2026-05-12 13:24:02', 'pending_chair', 0, NULL, NULL, 'pending'),
(86, 4, 488, 487, 489, '2026-05-13', '15:00:00', '17:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2026-05-12 13:24:02', 'pending_chair', 0, NULL, NULL, 'pending'),
(90, 9, 499, 482, 489, '2026-05-13', '13:00:00', '15:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2026-05-12 13:24:02', 'pending_chair', 0, NULL, NULL, 'pending'),
(93, 12, 482, 488, 484, '2026-05-13', '08:00:00', '10:00:00', 'Accreditation Room', 'title_proposal', NULL, 0, 'scheduled', '2026-05-12 13:24:02', 'pending_chair', 0, NULL, NULL, 'pending'),
(94, 8, 482, 481, 496, '2026-05-13', '11:00:00', '13:00:00', 'Accreditation Room', 'title_proposal', NULL, 0, 'scheduled', '2026-05-12 13:25:26', 'pending_chair', 0, NULL, NULL, 'pending');

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
(1, 'APP_NAME', 'Automated Thesis Lifecycle and Assessment Scheduler', ''),
(2, 'APP_ORGANIZATION', 'LPU-C CCS & CEA', ''),
(3, 'APP_OWNER', '120ms', 'Application owner'),
(4, 'APP_DESCRIPTION', 'taga schedule', 'Application description'),
(5, 'ALLOWED_INACTIVITY_TIME', '7200', ''),
(11, 'MAIL_HOST', 'smtp.gmail.com', 'Mail host'),
(12, 'MAIL_USERNAME', 'ton.agustin09@gmail.com', 'Mail username'),
(13, 'MAIL_PASSWORD', 'kkjh zktq xuhn mhml ', 'Mail password'),
(14, 'MAIL_ENCRYPTION', 'ssl', 'Mail encryption'),
(15, 'MAIL_PORT', '465', 'Mail port'),
(16, 'APP_LOGO_NAVBAR', 'img_6a06962e0311c5.05609871.png', ''),
(17, 'APP_LOGO_FOOTER', 'img_6a069635251931.80633433.png', ''),
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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `related_id`, `related_type`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: DRIVEED\n📝 Research: DRIVEED HUB: A DRIVING SCHOOL CONTENT MANAGEMENT  SYSTEM\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 5, 2026\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 1, NULL, 1, '2026-05-05 15:29:54', '2026-05-12 13:23:08'),
(2, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: DENGEGUARD\n📝 Research: DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 5, 2026\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 2, NULL, 1, '2026-05-05 15:30:39', '2026-05-12 13:23:08'),
(3, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: DRIVEED\n📝 Research: DRIVEED HUB: A DRIVING SCHOOL CONTENT MANAGEMENT  SYSTEM\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 4, 2026\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 3, NULL, 1, '2026-05-05 15:38:08', '2026-05-12 13:23:08'),
(4, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: DENGEGUARD\n📝 Research: DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 4, 2026\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 4, NULL, 1, '2026-05-05 15:38:44', '2026-05-12 13:23:08'),
(5, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: GAIA\n📝 Research: GAIA - Geospatial AI-Driven Assessment: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time  Environmental Hazard Detection.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 5, 2026\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 5, NULL, 1, '2026-05-05 15:39:21', '2026-05-12 13:23:08'),
(6, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: PRIVACYGUARD\n📝 Research: PRIVACYGUARD: A BROWSER EXTENSION FOR PII PROTECTION IN LPU-CAVITE USING HYBRID NER AND RANDOM FOREST\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 5, 2026\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 6, NULL, 1, '2026-05-05 15:40:21', '2026-05-12 13:23:08'),
(7, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SalinDugo\n📝 Research: SalinDugo: Smart Blood Matching with XGBoost-Based Demand  Forecasting and Regional Insights\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 5, 2026\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 7, NULL, 1, '2026-05-05 16:30:16', '2026-05-12 13:23:08'),
(8, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SOLARI\n📝 Research: Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR  REAL-TIME VISUAL AND SCENE DESCRIPTION FOR  VISUALLY IMPAIRED\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 5, 2026\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 8, NULL, 1, '2026-05-05 16:32:11', '2026-05-12 13:23:08'),
(9, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: RECOLOR\n📝 Research: RECOLOR:  A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION   AND CLUSTERING ALGORITHMS\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 4, 2026\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 9, NULL, 1, '2026-05-05 16:33:27', '2026-05-12 13:23:08'),
(10, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: HERBASCAN\n📝 Research: HerbaScan: A CONVOLUTIONAL NEURAL NETWORK-BASED MOBILE APPLICATION FOR PLANT IDENTIFICATION AND HERBAL MEDICINE INFORMATION\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 5, 2026\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 10, NULL, 1, '2026-05-05 16:34:57', '2026-05-12 13:23:08'),
(11, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: KNOWWHERE\n📝 Research: KNOWWHERE: AN SLM-POWERED RETRIEVAL-AUGMENTED GENERATION SEMANTIC SEARCH API SYSTEM FOR ACADEMIC RESEARCH DISCOVERY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 5, 2026\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 11, NULL, 1, '2026-05-05 16:37:00', '2026-05-12 13:23:08'),
(12, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: NAVICAV\n📝 Research: NAVICAV: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 5, 2026\n🕒 Time: 12:00 PM - 2:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 12, NULL, 1, '2026-05-05 16:39:01', '2026-05-12 13:23:08'),
(13, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: POSTRA\n📝 Research: POSTRA: REAL-TIME MISSING PERSON DETECTION VIA CCTV USING YOLOv11n-FACE AND ARCFACE BUFFALO-L WITH COSINE SIMILARITY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: December 5, 2026\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 13, NULL, 1, '2026-05-05 16:40:43', '2026-05-12 13:23:08'),
(14, 523, '', 'Defense Schedule Updated', 'Your team\'s defense has been updated for December 5, 2026 at 12:00 PM - 2:00 PM in Defense Room 1.', 12, NULL, 0, '2026-05-05 16:51:34', '2026-05-05 16:51:34'),
(15, 525, '', 'Defense Schedule Updated', 'Your team\'s defense has been updated for December 5, 2026 at 12:00 PM - 2:00 PM in Defense Room 1.', 12, NULL, 0, '2026-05-05 16:51:34', '2026-05-05 16:51:34'),
(16, 527, '', 'Defense Schedule Updated', 'Your team\'s defense has been updated for December 5, 2026 at 12:00 PM - 2:00 PM in Defense Room 1.', 12, NULL, 0, '2026-05-05 16:51:34', '2026-05-05 16:51:34'),
(17, 482, '', 'Defense Assignment Updated', 'You have been assigned as a panelist for NAVICAV\'s defense on December 5, 2026 at 12:00 PM - 2:00 PM in Defense Room 1.', 12, NULL, 0, '2026-05-05 16:51:34', '2026-05-05 16:51:34'),
(18, 481, '', 'Defense Assignment Updated', 'You have been assigned as a panelist for NAVICAV\'s defense on December 5, 2026 at 12:00 PM - 2:00 PM in Defense Room 1.', 12, NULL, 0, '2026-05-05 16:51:34', '2026-05-05 16:51:34'),
(19, 489, '', 'Defense Assignment Updated', 'You have been assigned as a panelist for NAVICAV\'s defense on December 5, 2026 at 12:00 PM - 2:00 PM in Defense Room 1.', 12, NULL, 0, '2026-05-05 16:51:34', '2026-05-05 16:51:34'),
(47, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: HERBASCAN\n📝 Research: HerbaScan: A CONVOLUTIONAL NEURAL NETWORK-BASED MOBILE APPLICATION FOR PLANT IDENTIFICATION AND HERBAL MEDICINE INFORMATION\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 13, 2026\n🕒 Time: 5:00 PM - 7:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 84, NULL, 0, '2026-05-12 13:24:02', '2026-05-12 13:24:02'),
(48, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: KNOWWHERE\n📝 Research: KNOWWHERE: AN SLM-POWERED RETRIEVAL-AUGMENTED GENERATION SEMANTIC SEARCH API SYSTEM FOR ACADEMIC RESEARCH DISCOVERY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 13, 2026\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 85, NULL, 0, '2026-05-12 13:24:02', '2026-05-12 13:24:02'),
(49, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: RECOLOR\n📝 Research: RECOLOR:  A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION   AND CLUSTERING ALGORITHMS\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 13, 2026\n🕒 Time: 3:00 PM - 5:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 86, NULL, 0, '2026-05-12 13:24:02', '2026-05-12 13:24:02'),
(50, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SOLARI\n📝 Research: Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR  REAL-TIME VISUAL AND SCENE DESCRIPTION FOR  VISUALLY IMPAIRED\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 14, 2026\n🕒 Time: 5:00 PM - 7:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 87, NULL, 0, '2026-05-12 13:24:02', '2026-05-12 13:24:02'),
(51, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: NAVICAV\n📝 Research: NAVICAV: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 14, 2026\n🕒 Time: 5:00 PM - 7:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 88, NULL, 0, '2026-05-12 13:24:02', '2026-05-12 13:24:02'),
(52, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: POSTRA\n📝 Research: POSTRA: REAL-TIME MISSING PERSON DETECTION VIA CCTV USING YOLOv11n-FACE AND ARCFACE BUFFALO-L WITH COSINE SIMILARITY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 14, 2026\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 89, NULL, 0, '2026-05-12 13:24:02', '2026-05-12 13:24:02'),
(53, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: DENGEGUARD\n📝 Research: DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 13, 2026\n🕒 Time: 1:00 PM - 3:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 90, NULL, 0, '2026-05-12 13:24:02', '2026-05-12 13:24:02'),
(54, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SalinDugo\n📝 Research: SalinDugo: Smart Blood Matching with XGBoost-Based Demand  Forecasting and Regional Insights\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 14, 2026\n🕒 Time: 11:00 AM - 1:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 91, NULL, 0, '2026-05-12 13:24:02', '2026-05-12 13:24:02'),
(55, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: GAIA\n📝 Research: GAIA - Geospatial AI-Driven Assessment: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time  Environmental Hazard Detection.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 14, 2026\n🕒 Time: 3:00 PM - 5:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 92, NULL, 0, '2026-05-12 13:24:02', '2026-05-12 13:24:02'),
(56, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: PRIVACYGUARD\n📝 Research: PRIVACYGUARD: A BROWSER EXTENSION FOR PII PROTECTION IN LPU-CAVITE USING HYBRID NER AND RANDOM FOREST\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 13, 2026\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 93, NULL, 0, '2026-05-12 13:24:02', '2026-05-12 13:24:02'),
(57, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: DRIVEED\n📝 Research: DRIVEED HUB: A DRIVING SCHOOL CONTENT MANAGEMENT  SYSTEM\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📅 Date: May 13, 2026\n🕒 Time: 11:00 AM - 1:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 94, NULL, 0, '2026-05-12 13:25:26', '2026-05-12 13:25:26'),
(58, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: DENGEGUARD\n📝 Research: DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 23, 2026\n🕒 Time: 5:00 PM - 7:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 95, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(59, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: DENGEGUARD\n📝 Research: DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 23, 2026\n🕒 Time: 5:00 PM - 7:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 95, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(60, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: HERBASCAN\n📝 Research: HerbaScan: A CONVOLUTIONAL NEURAL NETWORK-BASED MOBILE APPLICATION FOR PLANT IDENTIFICATION AND HERBAL MEDICINE INFORMATION\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 7:00 AM - 9:00 AM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 96, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(61, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: HERBASCAN\n📝 Research: HerbaScan: A CONVOLUTIONAL NEURAL NETWORK-BASED MOBILE APPLICATION FOR PLANT IDENTIFICATION AND HERBAL MEDICINE INFORMATION\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 7:00 AM - 9:00 AM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 96, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(62, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: RECOLOR\n📝 Research: RECOLOR:  A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION   AND CLUSTERING ALGORITHMS\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 21, 2026\n🕒 Time: 12:00 PM - 2:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 97, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(63, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: RECOLOR\n📝 Research: RECOLOR:  A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION   AND CLUSTERING ALGORITHMS\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 21, 2026\n🕒 Time: 12:00 PM - 2:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 97, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(64, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SOLARI\n📝 Research: Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR  REAL-TIME VISUAL AND SCENE DESCRIPTION FOR  VISUALLY IMPAIRED\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 98, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(65, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SOLARI\n📝 Research: Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR  REAL-TIME VISUAL AND SCENE DESCRIPTION FOR  VISUALLY IMPAIRED\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 98, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(66, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: PRIVACYGUARD\n📝 Research: PRIVACYGUARD: A BROWSER EXTENSION FOR PII PROTECTION IN LPU-CAVITE USING HYBRID NER AND RANDOM FOREST\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 21, 2026\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 99, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(67, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: PRIVACYGUARD\n📝 Research: PRIVACYGUARD: A BROWSER EXTENSION FOR PII PROTECTION IN LPU-CAVITE USING HYBRID NER AND RANDOM FOREST\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 21, 2026\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 99, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(68, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SalinDugo\n📝 Research: SalinDugo: Smart Blood Matching with XGBoost-Based Demand  Forecasting and Regional Insights\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 22, 2026\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 100, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(69, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SalinDugo\n📝 Research: SalinDugo: Smart Blood Matching with XGBoost-Based Demand  Forecasting and Regional Insights\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 22, 2026\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 100, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(70, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: POSTRA\n📝 Research: POSTRA: REAL-TIME MISSING PERSON DETECTION VIA CCTV USING YOLOv11n-FACE AND ARCFACE BUFFALO-L WITH COSINE SIMILARITY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 21, 2026\n🕒 Time: 7:00 AM - 9:00 AM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 101, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(71, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: POSTRA\n📝 Research: POSTRA: REAL-TIME MISSING PERSON DETECTION VIA CCTV USING YOLOv11n-FACE AND ARCFACE BUFFALO-L WITH COSINE SIMILARITY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 21, 2026\n🕒 Time: 7:00 AM - 9:00 AM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 101, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(72, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: KNOWWHERE\n📝 Research: KNOWWHERE: AN SLM-POWERED RETRIEVAL-AUGMENTED GENERATION SEMANTIC SEARCH API SYSTEM FOR ACADEMIC RESEARCH DISCOVERY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 22, 2026\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 102, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(73, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: KNOWWHERE\n📝 Research: KNOWWHERE: AN SLM-POWERED RETRIEVAL-AUGMENTED GENERATION SEMANTIC SEARCH API SYSTEM FOR ACADEMIC RESEARCH DISCOVERY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 22, 2026\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 102, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(74, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: GAIA\n📝 Research: GAIA - Geospatial AI-Driven Assessment: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time  Environmental Hazard Detection.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 12:00 PM - 2:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 103, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(75, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: GAIA\n📝 Research: GAIA - Geospatial AI-Driven Assessment: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time  Environmental Hazard Detection.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 12:00 PM - 2:00 PM\n🏢 Room: Accreditation Room\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 103, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(76, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: NAVICAV\n📝 Research: NAVICAV: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 104, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(77, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: NAVICAV\n📝 Research: NAVICAV: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 104, NULL, 0, '2026-05-15 06:00:22', '2026-05-15 06:00:22'),
(78, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SalinDugo\n📝 Research: SalinDugo: Smart Blood Matching with XGBoost-Based Demand  Forecasting and Regional Insights\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 18, 2026\n🕒 Time: 6:00 PM - 8:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 105, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(79, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SalinDugo\n📝 Research: SalinDugo: Smart Blood Matching with XGBoost-Based Demand  Forecasting and Regional Insights\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 18, 2026\n🕒 Time: 6:00 PM - 8:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 105, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(80, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: PRIVACYGUARD\n📝 Research: PRIVACYGUARD: A BROWSER EXTENSION FOR PII PROTECTION IN LPU-CAVITE USING HYBRID NER AND RANDOM FOREST\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 23, 2026\n🕒 Time: 5:00 PM - 7:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 106, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(81, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: PRIVACYGUARD\n📝 Research: PRIVACYGUARD: A BROWSER EXTENSION FOR PII PROTECTION IN LPU-CAVITE USING HYBRID NER AND RANDOM FOREST\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 23, 2026\n🕒 Time: 5:00 PM - 7:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 106, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(82, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: KNOWWHERE\n📝 Research: KNOWWHERE: AN SLM-POWERED RETRIEVAL-AUGMENTED GENERATION SEMANTIC SEARCH API SYSTEM FOR ACADEMIC RESEARCH DISCOVERY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 12:00 PM - 2:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 107, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(83, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: KNOWWHERE\n📝 Research: KNOWWHERE: AN SLM-POWERED RETRIEVAL-AUGMENTED GENERATION SEMANTIC SEARCH API SYSTEM FOR ACADEMIC RESEARCH DISCOVERY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 12:00 PM - 2:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 107, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(84, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: GAIA\n📝 Research: GAIA - Geospatial AI-Driven Assessment: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time  Environmental Hazard Detection.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 6:00 PM - 8:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 108, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(85, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: GAIA\n📝 Research: GAIA - Geospatial AI-Driven Assessment: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time  Environmental Hazard Detection.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 6:00 PM - 8:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 108, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(86, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SOLARI\n📝 Research: Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR  REAL-TIME VISUAL AND SCENE DESCRIPTION FOR  VISUALLY IMPAIRED\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 19, 2026\n🕒 Time: 3:00 PM - 5:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 109, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(87, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: SOLARI\n📝 Research: Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR  REAL-TIME VISUAL AND SCENE DESCRIPTION FOR  VISUALLY IMPAIRED\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 19, 2026\n🕒 Time: 3:00 PM - 5:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 109, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(88, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: NAVICAV\n📝 Research: NAVICAV: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 19, 2026\n🕒 Time: 5:00 PM - 7:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 110, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(89, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: NAVICAV\n📝 Research: NAVICAV: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 19, 2026\n🕒 Time: 5:00 PM - 7:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 110, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(90, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: HERBASCAN\n📝 Research: HerbaScan: A CONVOLUTIONAL NEURAL NETWORK-BASED MOBILE APPLICATION FOR PLANT IDENTIFICATION AND HERBAL MEDICINE INFORMATION\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 111, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(91, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: HERBASCAN\n📝 Research: HerbaScan: A CONVOLUTIONAL NEURAL NETWORK-BASED MOBILE APPLICATION FOR PLANT IDENTIFICATION AND HERBAL MEDICINE INFORMATION\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 111, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(92, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: DENGEGUARD\n📝 Research: DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 112, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(93, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: DENGEGUARD\n📝 Research: DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 20, 2026\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 1\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 112, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(94, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: POSTRA\n📝 Research: POSTRA: REAL-TIME MISSING PERSON DETECTION VIA CCTV USING YOLOv11n-FACE AND ARCFACE BUFFALO-L WITH COSINE SIMILARITY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 18, 2026\n🕒 Time: 1:00 PM - 3:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 113, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(95, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: POSTRA\n📝 Research: POSTRA: REAL-TIME MISSING PERSON DETECTION VIA CCTV USING YOLOv11n-FACE AND ARCFACE BUFFALO-L WITH COSINE SIMILARITY\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 18, 2026\n🕒 Time: 1:00 PM - 3:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 113, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(96, 486, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: RECOLOR\n📝 Research: RECOLOR:  A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION   AND CLUSTERING ALGORITHMS\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 18, 2026\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 114, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52'),
(97, 673, '', 'Defense Schedule - Chair Review Required', 'A new defense schedule requires your review before panelists are notified:\n\n🎓 Team: RECOLOR\n📝 Research: RECOLOR:  A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION   AND CLUSTERING ALGORITHMS\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📅 Date: May 18, 2026\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 2\n\nPlease approve or reject this schedule in the Defense Schedules tab.', 114, NULL, 0, '2026-05-15 10:13:52', '2026-05-15 10:13:52');

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

--
-- Dumping data for table `notification_actions`
--

INSERT INTO `notification_actions` (`id`, `notification_id`, `action_type`, `action_data`, `is_completed`, `completed_at`, `created_at`) VALUES
(1, 1, '', '{\"schedule_id\":\"1\"}', 0, NULL, '2026-05-05 15:29:54'),
(2, 1, '', '{\"schedule_id\":\"1\"}', 0, NULL, '2026-05-05 15:29:54'),
(3, 2, '', '{\"schedule_id\":\"2\"}', 0, NULL, '2026-05-05 15:30:39'),
(4, 2, '', '{\"schedule_id\":\"2\"}', 0, NULL, '2026-05-05 15:30:39'),
(5, 3, '', '{\"schedule_id\":\"3\"}', 0, NULL, '2026-05-05 15:38:08'),
(6, 3, '', '{\"schedule_id\":\"3\"}', 0, NULL, '2026-05-05 15:38:08'),
(7, 4, '', '{\"schedule_id\":\"4\"}', 0, NULL, '2026-05-05 15:38:44'),
(8, 4, '', '{\"schedule_id\":\"4\"}', 0, NULL, '2026-05-05 15:38:44'),
(9, 5, '', '{\"schedule_id\":\"5\"}', 0, NULL, '2026-05-05 15:39:21'),
(10, 5, '', '{\"schedule_id\":\"5\"}', 0, NULL, '2026-05-05 15:39:21'),
(11, 6, '', '{\"schedule_id\":\"6\"}', 0, NULL, '2026-05-05 15:40:21'),
(12, 6, '', '{\"schedule_id\":\"6\"}', 0, NULL, '2026-05-05 15:40:21'),
(13, 7, '', '{\"schedule_id\":\"7\"}', 0, NULL, '2026-05-05 16:30:16'),
(14, 7, '', '{\"schedule_id\":\"7\"}', 0, NULL, '2026-05-05 16:30:16'),
(15, 8, '', '{\"schedule_id\":\"8\"}', 0, NULL, '2026-05-05 16:32:11'),
(16, 8, '', '{\"schedule_id\":\"8\"}', 0, NULL, '2026-05-05 16:32:11'),
(17, 9, '', '{\"schedule_id\":\"9\"}', 0, NULL, '2026-05-05 16:33:27'),
(18, 9, '', '{\"schedule_id\":\"9\"}', 0, NULL, '2026-05-05 16:33:27'),
(19, 10, '', '{\"schedule_id\":\"10\"}', 0, NULL, '2026-05-05 16:34:57'),
(20, 10, '', '{\"schedule_id\":\"10\"}', 0, NULL, '2026-05-05 16:34:57'),
(21, 11, '', '{\"schedule_id\":\"11\"}', 0, NULL, '2026-05-05 16:37:00'),
(22, 11, '', '{\"schedule_id\":\"11\"}', 0, NULL, '2026-05-05 16:37:00'),
(23, 12, '', '{\"schedule_id\":\"12\"}', 0, NULL, '2026-05-05 16:39:01'),
(24, 12, '', '{\"schedule_id\":\"12\"}', 0, NULL, '2026-05-05 16:39:01'),
(25, 13, '', '{\"schedule_id\":\"13\"}', 0, NULL, '2026-05-05 16:40:43'),
(26, 13, '', '{\"schedule_id\":\"13\"}', 0, NULL, '2026-05-05 16:40:43'),
(27, 47, '', '{\"schedule_id\":\"84\"}', 0, NULL, '2026-05-12 13:24:02'),
(28, 47, '', '{\"schedule_id\":\"84\"}', 0, NULL, '2026-05-12 13:24:02'),
(29, 48, '', '{\"schedule_id\":\"85\"}', 0, NULL, '2026-05-12 13:24:02'),
(30, 48, '', '{\"schedule_id\":\"85\"}', 0, NULL, '2026-05-12 13:24:02'),
(31, 49, '', '{\"schedule_id\":\"86\"}', 0, NULL, '2026-05-12 13:24:02'),
(32, 49, '', '{\"schedule_id\":\"86\"}', 0, NULL, '2026-05-12 13:24:02'),
(33, 50, '', '{\"schedule_id\":\"87\"}', 0, NULL, '2026-05-12 13:24:02'),
(34, 50, '', '{\"schedule_id\":\"87\"}', 0, NULL, '2026-05-12 13:24:02'),
(35, 51, '', '{\"schedule_id\":\"88\"}', 0, NULL, '2026-05-12 13:24:02'),
(36, 51, '', '{\"schedule_id\":\"88\"}', 0, NULL, '2026-05-12 13:24:02'),
(37, 52, '', '{\"schedule_id\":\"89\"}', 0, NULL, '2026-05-12 13:24:02'),
(38, 52, '', '{\"schedule_id\":\"89\"}', 0, NULL, '2026-05-12 13:24:02'),
(39, 53, '', '{\"schedule_id\":\"90\"}', 0, NULL, '2026-05-12 13:24:02'),
(40, 53, '', '{\"schedule_id\":\"90\"}', 0, NULL, '2026-05-12 13:24:02'),
(41, 54, '', '{\"schedule_id\":\"91\"}', 0, NULL, '2026-05-12 13:24:02'),
(42, 54, '', '{\"schedule_id\":\"91\"}', 0, NULL, '2026-05-12 13:24:02'),
(43, 55, '', '{\"schedule_id\":\"92\"}', 0, NULL, '2026-05-12 13:24:02'),
(44, 55, '', '{\"schedule_id\":\"92\"}', 0, NULL, '2026-05-12 13:24:02'),
(45, 56, '', '{\"schedule_id\":\"93\"}', 0, NULL, '2026-05-12 13:24:02'),
(46, 56, '', '{\"schedule_id\":\"93\"}', 0, NULL, '2026-05-12 13:24:02'),
(47, 57, '', '{\"schedule_id\":\"94\"}', 0, NULL, '2026-05-12 13:25:26'),
(48, 57, '', '{\"schedule_id\":\"94\"}', 0, NULL, '2026-05-12 13:25:26'),
(49, 58, '', '{\"schedule_id\":\"95\"}', 0, NULL, '2026-05-15 06:00:22'),
(50, 58, '', '{\"schedule_id\":\"95\"}', 0, NULL, '2026-05-15 06:00:22'),
(51, 59, '', '{\"schedule_id\":\"95\"}', 0, NULL, '2026-05-15 06:00:22'),
(52, 59, '', '{\"schedule_id\":\"95\"}', 0, NULL, '2026-05-15 06:00:22'),
(53, 60, '', '{\"schedule_id\":\"96\"}', 0, NULL, '2026-05-15 06:00:22'),
(54, 60, '', '{\"schedule_id\":\"96\"}', 0, NULL, '2026-05-15 06:00:22'),
(55, 61, '', '{\"schedule_id\":\"96\"}', 0, NULL, '2026-05-15 06:00:22'),
(56, 61, '', '{\"schedule_id\":\"96\"}', 0, NULL, '2026-05-15 06:00:22'),
(57, 62, '', '{\"schedule_id\":\"97\"}', 0, NULL, '2026-05-15 06:00:22'),
(58, 62, '', '{\"schedule_id\":\"97\"}', 0, NULL, '2026-05-15 06:00:22'),
(59, 63, '', '{\"schedule_id\":\"97\"}', 0, NULL, '2026-05-15 06:00:22'),
(60, 63, '', '{\"schedule_id\":\"97\"}', 0, NULL, '2026-05-15 06:00:22'),
(61, 64, '', '{\"schedule_id\":\"98\"}', 0, NULL, '2026-05-15 06:00:22'),
(62, 64, '', '{\"schedule_id\":\"98\"}', 0, NULL, '2026-05-15 06:00:22'),
(63, 65, '', '{\"schedule_id\":\"98\"}', 0, NULL, '2026-05-15 06:00:22'),
(64, 65, '', '{\"schedule_id\":\"98\"}', 0, NULL, '2026-05-15 06:00:22'),
(65, 66, '', '{\"schedule_id\":\"99\"}', 0, NULL, '2026-05-15 06:00:22'),
(66, 66, '', '{\"schedule_id\":\"99\"}', 0, NULL, '2026-05-15 06:00:22'),
(67, 67, '', '{\"schedule_id\":\"99\"}', 0, NULL, '2026-05-15 06:00:22'),
(68, 67, '', '{\"schedule_id\":\"99\"}', 0, NULL, '2026-05-15 06:00:22'),
(69, 68, '', '{\"schedule_id\":\"100\"}', 0, NULL, '2026-05-15 06:00:22'),
(70, 68, '', '{\"schedule_id\":\"100\"}', 0, NULL, '2026-05-15 06:00:22'),
(71, 69, '', '{\"schedule_id\":\"100\"}', 0, NULL, '2026-05-15 06:00:22'),
(72, 69, '', '{\"schedule_id\":\"100\"}', 0, NULL, '2026-05-15 06:00:22'),
(73, 70, '', '{\"schedule_id\":\"101\"}', 0, NULL, '2026-05-15 06:00:22'),
(74, 70, '', '{\"schedule_id\":\"101\"}', 0, NULL, '2026-05-15 06:00:22'),
(75, 71, '', '{\"schedule_id\":\"101\"}', 0, NULL, '2026-05-15 06:00:22'),
(76, 71, '', '{\"schedule_id\":\"101\"}', 0, NULL, '2026-05-15 06:00:22'),
(77, 72, '', '{\"schedule_id\":\"102\"}', 0, NULL, '2026-05-15 06:00:22'),
(78, 72, '', '{\"schedule_id\":\"102\"}', 0, NULL, '2026-05-15 06:00:22'),
(79, 73, '', '{\"schedule_id\":\"102\"}', 0, NULL, '2026-05-15 06:00:22'),
(80, 73, '', '{\"schedule_id\":\"102\"}', 0, NULL, '2026-05-15 06:00:22'),
(81, 74, '', '{\"schedule_id\":\"103\"}', 0, NULL, '2026-05-15 06:00:22'),
(82, 74, '', '{\"schedule_id\":\"103\"}', 0, NULL, '2026-05-15 06:00:22'),
(83, 75, '', '{\"schedule_id\":\"103\"}', 0, NULL, '2026-05-15 06:00:22'),
(84, 75, '', '{\"schedule_id\":\"103\"}', 0, NULL, '2026-05-15 06:00:22'),
(85, 76, '', '{\"schedule_id\":\"104\"}', 0, NULL, '2026-05-15 06:00:22'),
(86, 76, '', '{\"schedule_id\":\"104\"}', 0, NULL, '2026-05-15 06:00:22'),
(87, 77, '', '{\"schedule_id\":\"104\"}', 0, NULL, '2026-05-15 06:00:22'),
(88, 77, '', '{\"schedule_id\":\"104\"}', 0, NULL, '2026-05-15 06:00:22'),
(89, 78, '', '{\"schedule_id\":\"105\"}', 0, NULL, '2026-05-15 10:13:52'),
(90, 78, '', '{\"schedule_id\":\"105\"}', 0, NULL, '2026-05-15 10:13:52'),
(91, 79, '', '{\"schedule_id\":\"105\"}', 0, NULL, '2026-05-15 10:13:52'),
(92, 79, '', '{\"schedule_id\":\"105\"}', 0, NULL, '2026-05-15 10:13:52'),
(93, 80, '', '{\"schedule_id\":\"106\"}', 0, NULL, '2026-05-15 10:13:52'),
(94, 80, '', '{\"schedule_id\":\"106\"}', 0, NULL, '2026-05-15 10:13:52'),
(95, 81, '', '{\"schedule_id\":\"106\"}', 0, NULL, '2026-05-15 10:13:52'),
(96, 81, '', '{\"schedule_id\":\"106\"}', 0, NULL, '2026-05-15 10:13:52'),
(97, 82, '', '{\"schedule_id\":\"107\"}', 0, NULL, '2026-05-15 10:13:52'),
(98, 82, '', '{\"schedule_id\":\"107\"}', 0, NULL, '2026-05-15 10:13:52'),
(99, 83, '', '{\"schedule_id\":\"107\"}', 0, NULL, '2026-05-15 10:13:52'),
(100, 83, '', '{\"schedule_id\":\"107\"}', 0, NULL, '2026-05-15 10:13:52'),
(101, 84, '', '{\"schedule_id\":\"108\"}', 0, NULL, '2026-05-15 10:13:52'),
(102, 84, '', '{\"schedule_id\":\"108\"}', 0, NULL, '2026-05-15 10:13:52'),
(103, 85, '', '{\"schedule_id\":\"108\"}', 0, NULL, '2026-05-15 10:13:52'),
(104, 85, '', '{\"schedule_id\":\"108\"}', 0, NULL, '2026-05-15 10:13:52'),
(105, 86, '', '{\"schedule_id\":\"109\"}', 0, NULL, '2026-05-15 10:13:52'),
(106, 86, '', '{\"schedule_id\":\"109\"}', 0, NULL, '2026-05-15 10:13:52'),
(107, 87, '', '{\"schedule_id\":\"109\"}', 0, NULL, '2026-05-15 10:13:52'),
(108, 87, '', '{\"schedule_id\":\"109\"}', 0, NULL, '2026-05-15 10:13:52'),
(109, 88, '', '{\"schedule_id\":\"110\"}', 0, NULL, '2026-05-15 10:13:52'),
(110, 88, '', '{\"schedule_id\":\"110\"}', 0, NULL, '2026-05-15 10:13:52'),
(111, 89, '', '{\"schedule_id\":\"110\"}', 0, NULL, '2026-05-15 10:13:52'),
(112, 89, '', '{\"schedule_id\":\"110\"}', 0, NULL, '2026-05-15 10:13:52'),
(113, 90, '', '{\"schedule_id\":\"111\"}', 0, NULL, '2026-05-15 10:13:52'),
(114, 90, '', '{\"schedule_id\":\"111\"}', 0, NULL, '2026-05-15 10:13:52'),
(115, 91, '', '{\"schedule_id\":\"111\"}', 0, NULL, '2026-05-15 10:13:52'),
(116, 91, '', '{\"schedule_id\":\"111\"}', 0, NULL, '2026-05-15 10:13:52'),
(117, 92, '', '{\"schedule_id\":\"112\"}', 0, NULL, '2026-05-15 10:13:52'),
(118, 92, '', '{\"schedule_id\":\"112\"}', 0, NULL, '2026-05-15 10:13:52'),
(119, 93, '', '{\"schedule_id\":\"112\"}', 0, NULL, '2026-05-15 10:13:52'),
(120, 93, '', '{\"schedule_id\":\"112\"}', 0, NULL, '2026-05-15 10:13:52'),
(121, 94, '', '{\"schedule_id\":\"113\"}', 0, NULL, '2026-05-15 10:13:52'),
(122, 94, '', '{\"schedule_id\":\"113\"}', 0, NULL, '2026-05-15 10:13:52'),
(123, 95, '', '{\"schedule_id\":\"113\"}', 0, NULL, '2026-05-15 10:13:52'),
(124, 95, '', '{\"schedule_id\":\"113\"}', 0, NULL, '2026-05-15 10:13:52'),
(125, 96, '', '{\"schedule_id\":\"114\"}', 0, NULL, '2026-05-15 10:13:52'),
(126, 96, '', '{\"schedule_id\":\"114\"}', 0, NULL, '2026-05-15 10:13:52'),
(127, 97, '', '{\"schedule_id\":\"114\"}', 0, NULL, '2026-05-15 10:13:52'),
(128, 97, '', '{\"schedule_id\":\"114\"}', 0, NULL, '2026-05-15 10:13:52');

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

--
-- Dumping data for table `panel_assignment_popup_state`
--

INSERT INTO `panel_assignment_popup_state` (`id`, `user_id`, `schedule_id`, `notification_id`, `shown_at`, `created_at`, `updated_at`) VALUES
(1, 486, 23, NULL, '2026-05-06 02:35:48', '2026-05-06 02:35:48', '2026-05-06 02:35:48'),
(2, 481, 113, NULL, '2026-05-15 10:18:30', '2026-05-15 10:18:30', '2026-05-15 10:18:30'),
(3, 481, 110, NULL, '2026-05-15 10:32:02', '2026-05-15 10:32:02', '2026-05-15 10:32:02'),
(4, 481, 106, NULL, '2026-05-15 10:32:04', '2026-05-15 10:32:04', '2026-05-15 10:32:04');

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
(2, 'Capstone 2', 'final_defense', 1, 0, 1, 'This includes the template', '687d025450e81_1753023060.docx', 'CAPSTONE 1-2 TEMPLATES.docx', '2030-10-31', '2024-11-11 18:52:54'),
(3, 'Capstone 1', 'title_defense', 0, 0, 1, 'This includes the template\r\n(IT ONLY)', '687d0244896eb_1753023044.docx', 'CAPSTONE 1-2 TEMPLATES.docx', '2030-11-30', '2024-11-11 18:52:41'),
(5, 'Final Manuscript', 'final_defense', 1, 0, 1, 'Also used for Research Repository (DO NOT REMOVE)', '687d0263dbbb8_1753023075.docx', 'FULL MANUSCRIPT_template_crd2025.docx', '2030-12-04', '2024-11-11 18:52:22'),
(46, 'Research methods Template A', 'title_proposal', 1, 1, 3, 'Template of title proposal template', NULL, NULL, '2030-11-28', '2025-11-21 22:47:43'),
(47, 'Thesis 1', 'title_defense', 1, 0, 1, 'CS only', NULL, NULL, '2030-12-31', '2025-12-02 01:50:13');

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
(1, 1, 'HerbaScan: A CONVOLUTIONAL NEURAL NETWORK-BASED MOBILE APPLICATION FOR PLANT IDENTIFICATION AND HERBAL MEDICINE INFORMATION', NULL, NULL, NULL, '2026-05-05 14:52:34', '2026-05-05 15:01:25'),
(3, 3, 'KNOWWHERE: AN SLM-POWERED RETRIEVAL-AUGMENTED GENERATION SEMANTIC SEARCH API SYSTEM FOR ACADEMIC RESEARCH DISCOVERY', NULL, NULL, NULL, '2026-05-05 14:55:20', '2026-05-05 16:51:12'),
(4, 4, 'RECOLOR:  A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION   AND CLUSTERING ALGORITHMS', NULL, NULL, NULL, '2026-05-05 14:58:57', '2026-05-05 16:50:52'),
(5, 5, 'Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR  REAL-TIME VISUAL AND SCENE DESCRIPTION FOR  VISUALLY IMPAIRED', NULL, NULL, NULL, '2026-05-05 15:00:45', '2026-05-05 16:50:35'),
(6, 6, 'NAVICAV: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.', NULL, NULL, NULL, '2026-05-05 15:05:00', '2026-05-05 17:45:32'),
(7, 7, 'POSTRA: REAL-TIME MISSING PERSON DETECTION VIA CCTV USING YOLOv11n-FACE AND ARCFACE BUFFALO-L WITH COSINE SIMILARITY', NULL, NULL, NULL, '2026-05-05 15:06:04', '2026-05-05 16:49:47'),
(8, 8, 'DRIVEED HUB: A DRIVING SCHOOL CONTENT MANAGEMENT  SYSTEM', NULL, NULL, NULL, '2026-05-05 15:09:15', '2026-05-05 18:40:04'),
(9, 9, 'DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING', NULL, NULL, NULL, '2026-05-05 15:11:21', '2026-05-05 16:49:04'),
(10, 10, 'SalinDugo: Smart Blood Matching with XGBoost-Based Demand  Forecasting and Regional Insights', NULL, NULL, NULL, '2026-05-05 15:12:10', '2026-05-05 16:47:53'),
(11, 11, 'GAIA - Geospatial AI-Driven Assessment: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time  Environmental Hazard Detection.', NULL, NULL, NULL, '2026-05-05 15:12:29', '2026-05-05 17:45:07'),
(12, 12, 'PRIVACYGUARD: A BROWSER EXTENSION FOR PII PROTECTION IN LPU-CAVITE USING HYBRID NER AND RANDOM FOREST', NULL, NULL, NULL, '2026-05-05 15:13:32', '2026-05-05 15:54:23'),
(13, 13, 'Smart Retail Shelf Analytics System (IoT + Cloud)', NULL, NULL, NULL, '2026-05-15 07:07:37', '2026-05-15 07:07:37'),
(14, 14, 'GuardTrack: AI-Powered Campus Security Patrol Verification', NULL, NULL, NULL, '2026-05-15 07:09:30', '2026-05-15 07:09:30'),
(15, 15, 'MediChain: Decentralized Patient Referral & Medical History Ledger', NULL, NULL, NULL, '2026-05-15 07:11:02', '2026-05-15 07:11:02'),
(16, 16, 'BYTEFORGE: Design and Development of an AI-Based Smart Task Management System with Productivity Analytics for Students an AI-Powered Cybersecurity Threat Detection System for Small Enterprises', '', NULL, NULL, '2026-05-15 07:11:03', '2026-05-16 00:04:27'),
(17, 17, 'EcoRoute: Dynamic Fleet Optimization for Waste Management', NULL, NULL, NULL, '2026-05-15 07:12:33', '2026-05-15 07:12:33'),
(18, 18, 'CODENOVA: A Cloud-Based Inventory Management System with Real-Time Sales Analytics for Small Businesses', NULL, NULL, NULL, '2026-05-15 07:12:34', '2026-05-15 07:12:34'),
(19, 19, 'NEXUSLAB: Development of a Mobile-Based Emergency Response System with GPS Location Tracking and Alert Notifications', NULL, NULL, NULL, '2026-05-15 07:13:43', '2026-05-15 07:13:43'),
(20, 20, 'SkillMatch: Automated Resume Screening & Interview Prep Portal', NULL, NULL, NULL, '2026-05-15 07:13:49', '2026-05-15 07:13:49'),
(21, 21, 'DATASPHERE: Design of a Machine Learning-Based Student Performance Prediction System Using Academic Records', NULL, NULL, NULL, '2026-05-15 07:16:02', '2026-05-15 07:16:02'),
(22, 22, 'CYBERNOVA: Implementation of a Network Intrusion Detection System Using Machine Learning Algorithms', NULL, NULL, NULL, '2026-05-15 07:17:41', '2026-05-15 07:19:14'),
(23, 23, 'DefendNet: Automated Network Intrusion Detection and Mitigation System Using Machine Learning', NULL, NULL, NULL, '2026-05-19 06:01:53', '2026-05-19 06:08:08'),
(24, 24, 'SmartAgri: An IoT-Based Automated Soil Quality Monitoring and Irrigation System', NULL, NULL, NULL, '2026-05-19 06:03:28', '2026-05-19 06:07:51'),
(25, 25, 'MediScan: AI-Powered Chest X-Ray Anomaly Detection and Preliminary Reporting System', NULL, NULL, NULL, '2026-05-19 06:05:30', '2026-05-20 04:09:26'),
(26, 26, 'TransitBuddy: A Real-Time Crowdsourced Public Transportation Mapping and Arrival Prediction App', NULL, NULL, NULL, '2026-05-19 06:06:42', '2026-05-20 04:12:17'),
(27, 27, 'EduVerify: A Decentralized Blockchain System for Academic Credential and Transcript Verification', NULL, NULL, NULL, '2026-05-19 06:09:36', '2026-05-19 06:09:36'),
(28, 28, 'CloudScale: Dynamic Multi-Cloud Resource Allocation and Cost Optimization Engine', NULL, NULL, NULL, '2026-05-19 06:18:43', '2026-05-19 06:20:49'),
(29, 29, 'EcoMap: A GIS-Based Spatial Analysis and Tracking Platform for Urban Deforestation', NULL, NULL, NULL, '2026-05-19 06:24:33', '2026-05-19 06:24:33'),
(30, 30, 'B2B-Link: An Automated Supply Chain and Vendor Management System for Small Retailers', NULL, NULL, NULL, '2026-05-19 06:26:30', '2026-05-19 06:26:30'),
(31, 31, 'CodeQuest: A 2D RPG Game Designed for Teaching Object-Oriented Programming Fundamentals', NULL, NULL, NULL, '2026-05-19 06:28:36', '2026-05-19 06:28:36'),
(32, 32, 'CareSync: A Centralized Electronic Health Record (EHR) and Patient Queue Management System', NULL, NULL, NULL, '2026-05-19 06:31:21', '2026-05-20 04:08:51');

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

--
-- Dumping data for table `rubrics`
--

INSERT INTO `rubrics` (`id`, `name`, `description`, `rubric_type`, `is_individual_enabled`, `defense_type`, `rubric_description`, `pass_recommendation_text`, `fail_recommendation_text`, `fail_option_text`, `pass_threshold_1`, `pass_threshold_2`, `pass_threshold_3`, `max_total_score`, `max_members`, `is_active`, `created_at`, `updated_at`, `max_score_per_criterion`) VALUES
(1, 'A. Degree of Design / Level of Technical Complexity (30%)', '(RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 01:32:37', '2025-07-21 01:36:37', 100),
(2, 'B. Safety, Functionality, & Workmanship (20%)', '(RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 01:36:20', '2025-07-21 05:25:50', 100),
(3, 'Content (20%)', 'Final Manuscript Rubric (RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 01:41:50', '2025-07-21 01:45:42', 100),
(4, 'Organization (10%)', 'Final Manuscript Rubric (RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 01:45:09', '2025-07-21 01:45:09', 100),
(5, 'Presentation and Defense', 'Final Manuscript Rubric (RE-PRESENTATION)', 'numerical', 1, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, 1, '2025-07-21 01:48:07', '2025-07-21 01:48:20', 100),
(6, 'FINAL RECOMMENDATION:', '(RE-PRESENTATION)', 'passfail', 0, 'Proposal Defense', '', 'System is accepted:', 'System is rejected:', 'below 65% acceptability; refer to thesis adviser', 100.00, 75.00, 65.00, 0, NULL, 1, '2025-07-21 01:52:11', '2025-07-21 01:53:02', 100),
(14, 'FDR - Written Manuscript', 'Final Defense Rubric – Group Grade', 'numerical', 0, 'Final Defense', 'Written Manuscript', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 04:12:43', '2025-11-30 04:26:09', 100),
(15, 'FDR - Developed System', 'Final Defense Rubric – Group Grade', 'numerical', 0, 'Final Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 04:23:47', '2025-11-30 04:25:56', 100),
(16, 'FDS - Written Manuscript', 'Final Defense Score Sheet - Group Grade', 'numerical', 0, 'Final Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 04:30:29', '2025-11-30 04:34:29', 100),
(17, 'FDS - Developed System', 'Final Defense Score Sheet - Group Grade', 'numerical', 0, 'Final Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 04:32:58', '2025-11-30 04:36:14', 100),
(18, 'FDS - Oral Defense', 'Final Defense Score Sheet - Individual Grade', 'numerical', 1, 'Final Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 40, 4, 1, '2025-11-30 04:45:58', '2025-12-02 08:42:50', 100),
(19, 'FDS - Final Recommendation', 'Final Defense Score Sheet - Final Recommendation', 'passfail', 0, 'Final Defense', '', 'Manuscript is accepted:', 'The Manuscript is rejected:', 'below 70% acceptability (refer to research adviser and for re-defense)', 100.00, 75.00, 65.00, 0, NULL, 1, '2025-11-30 04:47:53', '2025-11-30 04:48:15', 100),
(20, 'PRS - Written Manuscript and Quality of the Developed System', 'Proposal Re-Presentation Score Sheet - Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 05:47:51', '2025-11-30 06:56:39', 100),
(21, 'PRS - Oral Defense Presentation All Content', 'Proposal Re-Presentation Score Sheet - Individual Grade', 'numerical', 1, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 35, 4, 1, '2025-11-30 05:50:26', '2025-12-04 07:44:34', 100),
(22, 'PRS - Oral Defense', 'Proposal Re-Presentation Score Sheet - Individual Grade', 'numerical', 1, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 40, 4, 1, '2025-11-30 05:51:55', '2025-12-04 09:04:22', 100),
(23, 'PRS - Final Recommendation', 'Proposal Re-Presentation Score Sheet', 'passfail', 0, 'Re-Defense', '', 'The research proposal is accepted:', 'The research proposal is rejected:', '(below 70% acceptability) – subject for re-enrollment', 100.00, 75.00, 65.00, 0, NULL, 1, '2025-11-30 05:56:45', '2025-11-30 05:59:08', 100),
(24, 'PRR - Content – System and Manuscript', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 06:07:10', '2025-12-04 19:46:03', 100),
(25, 'PRR - A2 - Content – Manuscript', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 06:13:35', '2025-11-30 06:13:35', 100),
(26, 'PRR - A3 - Content – Manuscript', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 06:17:36', '2025-11-30 06:17:36', 100),
(27, 'PRR - B - Content – Manuscript', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 06:22:32', '2025-11-30 06:22:32', 100),
(28, 'PRR - Organization - Manuscript', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 06:28:31', '2025-11-30 06:28:31', 100),
(29, 'PRR - Novelty and Impact', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 06:32:31', '2025-11-30 06:32:31', 100),
(30, 'PDS - Written Manuscript and Quality of the Developed System', 'Proposal Defense Score Sheet - Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 06:55:01', '2025-11-30 07:13:12', 100),
(31, 'PDS - Oral Defense Presentation All Content', 'Proposal Defense Score Sheet - Individual Grade', 'numerical', 1, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 35, 4, 1, '2025-11-30 06:59:54', '2025-12-04 07:41:54', 100),
(32, 'PDS - Oral Defense', 'Proposal Defense Score Sheet - Individual Grade', 'numerical', 1, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 40, 4, 1, '2025-11-30 07:08:02', '2025-12-05 05:06:19', 100),
(33, 'PDS - Final Recommendation', 'Proposal Defense Score Sheet', 'passfail', 0, 'Proposal Defense', '', 'The research proposal is accepted:', 'The research proposal is rejected:', 'below 70% acceptability (refer to research adviser and for redefense)', 81.00, 80.00, 70.00, 0, NULL, 1, '2025-11-30 07:12:43', '2025-12-05 22:09:17', 100),
(34, 'PDR - Content – System and Manuscript', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 07:20:54', '2025-12-04 19:45:34', 100),
(35, 'PDR - A2 - Content - Manuscript', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 07:23:47', '2025-11-30 07:23:47', 100),
(36, 'PDR - A3 - Content - Manuscript', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 07:27:06', '2025-11-30 07:27:06', 100),
(37, 'PDR - B - Content - Quality of the Developed System', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 07:31:36', '2025-11-30 07:31:36', 100),
(38, 'PDR - Organization - Manuscript', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 07:35:49', '2025-11-30 07:35:49', 100),
(39, 'PDR - Novelty and Impact', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 07:38:47', '2025-11-30 07:38:47', 100);

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

--
-- Dumping data for table `rubric_criteria`
--

INSERT INTO `rubric_criteria` (`id`, `rubric_id`, `criterion_text`, `criterion_detail`, `order_index`, `is_individual`, `max_score`, `min_score`, `created_at`, `updated_at`, `is_blank`) VALUES
(8, 1, 'Modules and Features', '[\"Modules and features are missing, non-functional, or incomplete.\",\"Core modules present and functional but may lack advanced or complete features.\",\"All modules and features are fully functional and demonstrate advanced or extended capabilities.\"]', 0, 0, NULL, 0, '2025-07-21 01:36:37', '2025-07-21 01:36:37', 0),
(9, 1, 'User Interface (UI) Design', '[\"UI is hard to use, lacks structure, and does not follow any design principles.\",\"Functional and moderately user-friendly but lacks visual polish and consistency.\",\"Intuitive, professional, visually appealing, responsive, and adheres to usability and design principles.\"]', 1, 0, NULL, 0, '2025-07-21 01:36:37', '2025-07-21 01:36:37', 0),
(10, 1, 'Innovation and Creativity', '[\"\",\"\",\"\"]', 2, 0, NULL, 0, '2025-07-21 01:36:37', '2025-07-21 01:36:37', 0),
(20, 4, 'Table of Contents', '[\"Missing or disorganized.\",\"Mostly consistent.\",\"Complete, consistent, easy to navigate.\"]', 0, 0, NULL, 0, '2025-07-21 01:45:09', '2025-07-21 01:45:09', 0),
(21, 4, 'Acknowledgment', '[\"Informal or irrelevant.\",\"Somewhat formal and relevant.\",\"Formal, well-written, and appropriate.\"]', 1, 0, NULL, 0, '2025-07-21 01:45:09', '2025-07-21 01:45:09', 0),
(22, 4, 'References', '[\"Missing or not in proper format.\",\"APA followed but inconsistently.\",\"APA fully followed and well-organized.\"]', 2, 0, NULL, 0, '2025-07-21 01:45:09', '2025-07-21 01:45:09', 0),
(23, 4, 'Appendices', '[\"Missing or not supportive.\",\"Present and somewhat relevant.\",\"Highly relevant and supportive.\"]', 3, 0, NULL, 0, '2025-07-21 01:45:09', '2025-07-21 01:45:09', 0),
(24, 4, 'Manuscript Layout', '[\"Poor formatting and structure.\",\"Mostly follows academic standards.\",\"Professionally formatted and consistent.\"]', 4, 0, NULL, 0, '2025-07-21 01:45:09', '2025-07-21 01:45:09', 0),
(25, 4, 'Grammar and Fluidity', '[\"Many errors and weak coherence.\",\"Minor issues; decent flow.\",\"Grammatically strong with excellent flow.\"]', 5, 0, NULL, 0, '2025-07-21 01:45:09', '2025-07-21 01:45:09', 0),
(26, 3, 'Relevance of Introduction', '[\"Lacks relevance or is disconnected.\",\"Relevant and provides sufficient background.\",\"Highly relevant, compelling, and comprehensive.\"]', 0, 0, NULL, 0, '2025-07-21 01:45:42', '2025-07-21 01:45:42', 0),
(27, 3, 'Clarity of Objectives', '[\"Objectives unclear or poorly stated.\",\"Clear and defined.\",\"Exceptionally clear, specific, and integrated.\"]', 1, 0, NULL, 0, '2025-07-21 01:45:42', '2025-07-21 01:45:42', 0),
(28, 3, 'Relevance of Literature', '[\"Outdated or irrelevant.\",\"Mostly relevant and updated.\",\"Comprehensive, current, and well-integrated.\"]', 2, 0, NULL, 0, '2025-07-21 01:45:42', '2025-07-21 01:45:42', 0),
(29, 3, 'Critical Analysis of Literature', '[\"Lacks critical evaluation.\",\"Demonstrates basic synthesis.\",\"Deep analysis with meaningful integration.\"]', 3, 0, NULL, 0, '2025-07-21 01:45:42', '2025-07-21 01:45:42', 0),
(30, 3, 'Appropriateness of Methodology', '[\"Poorly described or irrelevant.\",\"Appropriate and sufficiently described.\",\"Clearly justified, highly appropriate.\"]', 4, 0, NULL, 0, '2025-07-21 01:45:42', '2025-07-21 01:45:42', 0),
(31, 3, 'Alignment with Objectives', '[\"Methodology does not align.\",\"Some alignment with objectives.\",\"Strong, justified alignment.\"]', 5, 0, NULL, 0, '2025-07-21 01:45:42', '2025-07-21 01:45:42', 0),
(32, 3, 'Clarity of Results', '[\"Results unclear or incomplete.\",\"Adequately clear and complete.\",\"Clearly presented and comprehensive.\"]', 6, 0, NULL, 0, '2025-07-21 01:45:42', '2025-07-21 01:45:42', 0),
(33, 3, 'Depth of Discussion', '[\"Superficial with limited insight.\",\"Moderately insightful.\",\"Thorough, insightful, and critically evaluates findings.\"]', 7, 0, NULL, 0, '2025-07-21 01:45:42', '2025-07-21 01:45:42', 0),
(34, 3, 'Relevance of Conclusions', '[\"Vague or unsupported conclusions.\",\"Supported by results.\",\"Clear, relevant, and strongly supported.\"]', 8, 0, NULL, 0, '2025-07-21 01:45:42', '2025-07-21 01:45:42', 0),
(35, 3, 'Practicality of Recommendations', '[\"Impractical or irrelevant.\",\"Feasible and related to findings.\",\"Highly practical and forward-looking.\"]', 9, 0, NULL, 0, '2025-07-21 01:45:42', '2025-07-21 01:45:42', 0),
(40, 5, '1.	The student has passed due to demonstrating mastery in presenting the research findings with clarity in conveying the findings, conclusions, and recommendations; providing ', NULL, 0, 1, NULL, 0, '2025-07-21 01:48:20', '2025-07-21 01:48:20', 0),
(41, 5, 'The student has failed due to lack of mastery in presenting the research findings, unclear delivery of the findings, conclusions, and recommendations; inability to respond effectively to the examiners’ inquiries;', NULL, 1, 1, NULL, 0, '2025-07-21 01:48:20', '2025-07-21 01:48:20', 0),
(42, 5, 'empty', NULL, 2, 1, NULL, 0, '2025-07-21 01:48:20', '2025-07-21 01:48:20', 0),
(43, 5, 'empty again', NULL, 3, 1, NULL, 0, '2025-07-21 01:48:20', '2025-07-21 01:48:20', 0),
(45, 2, '', '[\"\",\"\",\"\"]', 0, 0, NULL, 0, '2025-07-21 05:25:50', '2025-07-21 05:25:50', 0),
(48, 7, 'a', '[\"a\",\"a\",\"a\"]', 0, 0, NULL, 0, '2025-07-21 05:27:25', '2025-07-21 05:27:25', 0),
(49, 7, 'b', '[\"b\",\"b\",\"b\"]', 1, 0, NULL, 0, '2025-07-21 05:27:25', '2025-07-21 05:27:25', 0),
(69, 8, '1. Clarity of Research Problem and Objectives', '[\"5%\"]', 0, 0, NULL, 0, '2025-11-30 01:09:24', '2025-11-30 01:09:24', 0),
(70, 8, '2. Extent of Review of Related Literature', '[\"5%\"]', 1, 0, NULL, 0, '2025-11-30 01:09:24', '2025-11-30 01:09:24', 0),
(71, 8, '3. Appropriateness of Methodology', '[\"5%\"]', 2, 0, NULL, 0, '2025-11-30 01:09:24', '2025-11-30 01:09:24', 0),
(72, 8, '4. Data Presentation and Depth of Analysis', '[\"5%\"]', 3, 0, NULL, 0, '2025-11-30 01:09:24', '2025-11-30 01:09:24', 0),
(73, 8, '5. Logic of Conclusion and Recommendations', '[\"5%\"]', 4, 0, NULL, 0, '2025-11-30 01:09:24', '2025-11-30 01:09:24', 0),
(74, 8, '6. Order and Neatness of the Manuscript', '[\"5%\"]', 5, 0, NULL, 0, '2025-11-30 01:09:24', '2025-11-30 01:09:24', 0),
(75, 9, '1. Completeness of Features', '[\"5%\"]', 0, 0, NULL, 0, '2025-11-30 01:10:26', '2025-11-30 01:10:26', 0),
(76, 9, '2. Accuracy and Reliability', '[\"5%\"]', 1, 0, NULL, 0, '2025-11-30 01:10:26', '2025-11-30 01:10:26', 0),
(77, 9, '3. Error Handling and Validation', '[\"5%\"]', 2, 0, NULL, 0, '2025-11-30 01:10:26', '2025-11-30 01:10:26', 0),
(78, 9, '4. Design and Layout', '[\"5%\"]', 3, 0, NULL, 0, '2025-11-30 01:10:26', '2025-11-30 01:10:26', 0),
(79, 9, '5. Security and Data Integrity', '[\"5%\"]', 4, 0, NULL, 0, '2025-11-30 01:10:26', '2025-11-30 01:10:26', 0),
(80, 9, '6. Innovation and Scalability', '[\"5%\"]', 5, 0, NULL, 0, '2025-11-30 01:10:26', '2025-11-30 01:10:26', 0),
(81, 10, '1. Clarity and mastery in the presentation', NULL, 0, 1, NULL, 0, '2025-11-30 01:17:01', '2025-11-30 01:17:01', 0),
(82, 10, '2. Articulate response to the inquiries', NULL, 1, 1, NULL, 0, '2025-11-30 01:17:01', '2025-11-30 01:17:01', 0),
(83, 10, '3. Proper demeanor and dress code', NULL, 2, 1, NULL, 0, '2025-11-30 01:17:01', '2025-11-30 01:17:01', 0),
(84, 12, 'Clarity of Research Problem and Objectives', '[\"Problem \\/ objectives are unclear, unfocused, or missing\",\"Problem \\/ objectives are stated but vague or loosely connected\",\"Problem \\/ objectives are clear but may lack depth or refinement\",\"Problem \\/ objectives are clearly presented and logically aligned\",\"Problem \\/ objectives are exceptionally clear, well-defined, and strongly aligned to the study\"]', 0, 0, NULL, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15', 0),
(85, 12, 'Extent of Review of Related Literature', '[\"Lacks relevant literature; lacks synthesis; sources are outdated\",\"Limited sources or weak connection to the topic; minimal synthesis\",\"Adequate sources with acceptable synthesis; some gaps\",\"Well-selected and current sources; clear synthesis and connection to the topic\",\"Comprehensive, updated, and well-synthesized literature showing strong theoretical grounding\"]', 1, 0, NULL, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15', 0),
(86, 12, 'Appropriateness of Methodology', '[\"Methodology is inappropriate, incomplete, or not described\",\"Method is partly appropriate but lacks clarity or justification\",\"Method is appropriate with sufficient explanation; minor gaps\",\"Clearly appropriate method with good justification and detail\",\"Highly appropriate, well-justified, and thoroughly detailed methodology\"]', 2, 0, NULL, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15', 0),
(87, 12, 'Data Presentation and Depth of Analysis', '[\"Data is unclear, disorganized, or confusing; tables\\/charts missing or irrelevant\",\"Data somewhat understandable but lacks organization or proper labeling\",\"Data is clear with adequate tables\\/charts; minor organizational issues\",\"Data is clear, well-organized; visuals enhance understanding\",\"Data is professionally presented, highly organized, and strongly enhances analysis\"]', 3, 0, NULL, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15', 0),
(88, 12, 'Logic of Conclusion and Recommendations', '[\"Conclusions are unsupported or irrelevant; recommendations missing\",\"Conclusions somewhat related but weakly supported\",\"Conclusions are aligned with findings; recommendations present\",\"Conclusions are strong, logical, and well-supported\",\"Conclusions are compelling, insightful, and strongly supported with actionable recommendations\"]', 4, 0, NULL, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15', 0),
(89, 12, 'Order and Neatness of the Manuscript', '[\"Manuscript is messy, disorganized, and difficult to follow; formatting inconsistent\",\"Some organization present but contains clutter, inconsistencies, or poor formatting\",\"Manuscript is generally organized and readable; minor formatting issues\",\"Well-organized, neat, and easy to follow with consistent formatting\",\"Exceptionally neat, professionally formatted, and highly organized throughout\"]', 5, 0, NULL, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15', 0),
(96, 13, 'Completeness of Features', '[\"Key features missing; system incomplete\",\"Some features implemented but lacks core functionality\",\"Most features implemented; minor missing elements\",\"All required features complete and functional\",\"Features fully complete, exceeding expectations with enhancements\"]', 0, 0, NULL, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40', 0),
(97, 13, 'Accuracy and Reliability', '[\"System frequently fails or produces incorrect results\",\"Occasional errors; limited reliability\",\"Generally accurate; minor inconsistencies\",\"Accurate and reliable under most conditions\",\"Highly accurate, stable, and consistently reliable\"]', 1, 0, NULL, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40', 0),
(98, 13, 'Error Handling and Validation', '[\"No validation; frequent errors and crashes\",\"Minimal validation; some unhandled errors\",\"Adequate validation; occasional issues\",\"Strong validation and consistent error handling\",\"Robust validation with comprehensive error handling mechanisms\"]', 2, 0, NULL, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40', 0),
(99, 13, 'Design and Layout', '[\"Interface is cluttered, inconsistent, or difficult to navigate\",\"Basic layout; lacks visual structure or usability\",\"Clean and acceptable design with some usability issues\",\"Visually appealing, consistent, and user-friendly\",\"Professional, intuitive, and polished interface with excellent user experience\"]', 3, 0, NULL, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40', 0),
(100, 13, 'Security and Data Integrity', '[\"Security and Data Integrity\",\"Minimal security; vulnerable to breaches\",\"Basic security implemented; some risks remain\",\"Strong security measures and good data protection\",\"Highly secure system with strong integrity, proper encryption, and safeguards\"]', 4, 0, NULL, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40', 0),
(101, 13, 'Innovation and Scalability', '[\"No innovative features; limited to basic functions\",\"No innovative features; limited to basic functions\",\"Some innovative elements; moderate scalability\",\"Innovative design with good potential for growth\",\"Highly innovative and fully scalable system with future-ready architecture\"]', 5, 0, NULL, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40', 0),
(115, 15, 'Completeness of Features', '[\"Features fully complete, exceeding expectations with enhancements\",\"All required features complete and functional\",\"Most features implemented; minor missing elements\",\"Some features implemented but lacks core functionality\",\"Key features missing; system incomplete\"]', 0, 0, NULL, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56', 0),
(116, 15, 'Accuracy and Reliability', '[\"Highly accurate, stable, and consistently reliable\",\"Accurate and reliable under most conditions\",\"Generally accurate; minor inconsistencies\",\"Occasional errors; limited reliability\",\"System frequently fails or produces incorrect results\"]', 1, 0, NULL, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56', 0),
(117, 15, 'Error Handling and Validation', '[\"Robust validation with comprehensive error handling mechanisms\",\"Strong validation and consistent error handling\",\"Adequate validation; occasional issues\",\"Minimal validation; some unhandled errors\",\"No validation; frequent errors and crashes\"]', 2, 0, NULL, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56', 0),
(118, 15, 'Design and Layout', '[\"Professional, intuitive, and polished interface with excellent user experience\",\"Visually appealing, consistent, and user-friendly\",\"Clean and acceptable design with some usability issues\",\"Basic layout; lacks visual structure or usability\",\"Interface is cluttered, inconsistent, or difficult to navigate\"]', 3, 0, NULL, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56', 0),
(119, 15, 'Security and Data Integrity', '[\"Highly secure system with strong integrity, proper encryption, and safeguards\",\"Strong security measures and good data protection\",\"Basic security implemented; some risks remain\",\"Minimal security; vulnerable to breaches\",\"No security measures; high risk of data exposure\"]', 4, 0, NULL, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56', 0),
(120, 15, 'Innovation and Scalability', '[\"Highly innovative and fully scalable system with future-ready architecture\",\"Innovative design with good potential for growth\",\"Some innovative elements; moderate scalability\",\"Minimal innovation; system not scalable\",\"No innovative features; limited to basic functions\"]', 5, 0, NULL, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56', 0),
(121, 14, 'Clarity of Research Problem and Objectives', '[\"Problem \\/ objectives are exceptionally clear, well-defined, and strongly aligned to the study\",\"Problem \\/ objectives are clearly presented and logically aligned\",\"Problem \\/ objectives are clear but may lack depth or refinement\",\"Problem \\/ objectives are stated but vague or loosely connected\",\"Problem \\/ objectives are unclear, unfocused, or missing\"]', 0, 0, NULL, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09', 0),
(122, 14, 'Extent of Review of Related Literature', '[\"Comprehensive, updated, and well-synthesized literature showing strong theoretical grounding\",\"Well-selected and current sources; clear synthesis and connection to the topic\",\"Adequate sources with acceptable synthesis; some gaps\",\"Limited sources or weak connection to the topic; minimal synthesis\",\"Lacks relevant literature; lacks synthesis; sources are outdated\"]', 1, 0, NULL, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09', 0),
(123, 14, 'Appropriateness of Methodology', '[\"Highly appropriate, well-justified, and thoroughly detailed methodology\",\"Clearly appropriate method with good justification and detail\",\"Method is appropriate with sufficient explanation; minor gaps\",\"Method is partly appropriate but lacks clarity or justification\",\"Methodology is inappropriate, incomplete, or not described\"]', 2, 0, NULL, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09', 0),
(124, 14, 'Data Presentation and Depth of Analysis', '[\"Data is professionally presented, highly organized, and strongly enhances analysis\",\"Data is clear, well-organized; visuals enhance understanding\",\"Data is clear with adequate tables\\/charts; minor organizational issues\",\"Data somewhat understandable but lacks organization or proper labeling\",\"Data is unclear, disorganized, or confusing; tables\\/charts missing or irrelevant\"]', 3, 0, NULL, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09', 0),
(125, 14, 'Logic of Conclusion and Recommendations', '[\"Conclusions are compelling, insightful, and strongly supported with actionable recommendations\",\"Conclusions are strong, logical, and well-supported\",\"Conclusions are aligned with findings; recommendations present\",\"Conclusions somewhat related but weakly supported\",\"Conclusions are unsupported or irrelevant; recommendations missing\"]', 4, 0, NULL, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09', 0),
(126, 14, 'Order and Neatness of the Manuscript', '[\"Exceptionally neat, professionally formatted, and highly organized throughout\",\"Well-organized, neat, and easy to follow with consistent formatting\",\"Manuscript is generally organized and readable; minor formatting issues\",\"Some organization present but contains clutter, inconsistencies, or poor formatting\",\"Manuscript is messy, disorganized, and difficult to follow; formatting inconsistent\"]', 5, 0, NULL, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09', 0),
(134, 16, '1. Clarity of Research Problem and Objectives', '[\"\"]', 0, 0, NULL, 0, '2025-11-30 04:34:29', '2025-11-30 04:34:29', 0),
(135, 16, '2. Extent of Review of Related Literature', '[\"\"]', 1, 0, NULL, 0, '2025-11-30 04:34:29', '2025-11-30 04:34:29', 0),
(136, 16, '3. Appropriateness of Methodology', '[\"\"]', 2, 0, NULL, 0, '2025-11-30 04:34:29', '2025-11-30 04:34:29', 0),
(137, 16, '4. Data Presentation and Depth of Analysis', '[\"\"]', 3, 0, NULL, 0, '2025-11-30 04:34:29', '2025-11-30 04:34:29', 0),
(138, 16, '5. Logic of Conclusion and Recommendations', '[\"\"]', 4, 0, NULL, 0, '2025-11-30 04:34:29', '2025-11-30 04:34:29', 0),
(139, 16, '6. Order and Neatness of the Manuscript', '[\"\"]', 5, 0, NULL, 0, '2025-11-30 04:34:29', '2025-11-30 04:34:29', 0),
(140, 17, '1. Completeness of Features', '[\"5%\"]', 0, 0, NULL, 0, '2025-11-30 04:36:14', '2025-11-30 04:36:14', 0),
(141, 17, '2. Accuracy and Reliability', '[\"5%\"]', 1, 0, NULL, 0, '2025-11-30 04:36:14', '2025-11-30 04:36:14', 0),
(142, 17, '3. Error Handling and Validation', '[\"5%\"]', 2, 0, NULL, 0, '2025-11-30 04:36:14', '2025-11-30 04:36:14', 0),
(143, 17, '4. Design and Layout', '[\"5%\"]', 3, 0, NULL, 0, '2025-11-30 04:36:14', '2025-11-30 04:36:14', 0),
(144, 17, '5. Security and Data Integrity', '[\"5%\"]', 4, 0, NULL, 0, '2025-11-30 04:36:14', '2025-11-30 04:36:14', 0),
(145, 17, '6. Innovation and Scalability', '[\"5%\"]', 5, 0, NULL, 0, '2025-11-30 04:36:14', '2025-11-30 04:36:14', 0),
(164, 25, 'Chapter II', '[\"Well-curated, up-todate, and highly relevant sources; shows clear synthesis and strong critical insight connecting to the study\",\"Adequate selection of relevant sources; demonstrates synthesis and some critical thinking\",\"Limited and weakly connected sources; minimal synthesis of ideas\",\"Lacks relevant sources or contains outdated\\/irrelevant literature; no synthesis or connection to study\"]', 0, 0, NULL, 0, '2025-11-30 06:13:35', '2025-11-30 06:13:35', 0),
(165, 25, 'Chapter II', '[\"Strongly developed, insightful framework; clearly demonstrates the study\'s direction and theoretical foundation\",\"Clearly presented with visual and narrative explanation; ogically aligned with objectives\",\"Present but vague; weak linkage to study or confusing flow\",\"Absent, unclear, or inappropriate; lacks coherence and logical structure\"]', 1, 0, NULL, 0, '2025-11-30 06:13:35', '2025-11-30 06:13:35', 0),
(166, 26, 'Chapter III', '[\"Strongly articulated design; logically aligned with study goals and appropriate for the project type\",\"Clearly described and generally fits the nature of the study\",\"Design is stated but lacks detail or partial relevance to the objectives\",\"No clear design or methodology; lacks direction or coherence\"]', 0, 0, NULL, 0, '2025-11-30 06:17:36', '2025-11-30 06:17:36', 0),
(167, 26, 'Chapter III', '[\"Well-justified and systematically selected; shows thoughtful planning and alignment with the study\\u2019s purpose\",\"Sampling method is described with clear rationale and participant relevance\",\"Described briefly with weak rationale or fit\",\"Sampling approach is missing, unjustified, or inappropriate\"]', 1, 0, NULL, 0, '2025-11-30 06:17:36', '2025-11-30 06:17:36', 0),
(168, 26, 'Chapter III', '[\"Clearly follows a structured model (e.g., Agile, SDLC) with well-explained development phases, roles, and iterations\",\"Identifies an organized process or model; explains phases of development\",\"Process is mentioned but lacks structure or coherence (e.g., unclear use of models like Agile\\/Waterfall)\",\"Development process is missing, disorganized, or inappropriate (e.g., no methodology used)\"]', 2, 0, NULL, 0, '2025-11-30 06:17:36', '2025-11-30 06:17:36', 0),
(169, 26, 'Chapter III', '[\"Well-structured and technically sound system architecture; clearly shows components, flow, and integration logic\",\"Provides basic structure and understandable architecture diagram\",\"Architecture is present but unclear or lacks technical coherence\",\"Absent or contains irrelevant\\/confusing diagrams or explanations\"]', 3, 0, NULL, 0, '2025-11-30 06:17:36', '2025-11-30 06:17:36', 0),
(170, 27, 'Modules and Features Implementation', '[\"All core modules are complete, integrated, and align well with project objectives\",\"Most modules are implemented and function as intended\",\"Some modules are working; lacks completeness or integration\",\"Very few features\\/modules are implemented; most are missing or do not work\"]', 0, 0, NULL, 0, '2025-11-30 06:22:32', '2025-11-30 06:22:32', 0),
(171, 27, 'User Interface & Navigation', '[\"Highly intuitive, visually consistent, and user-friendly interface\",\"UI is clean and usable with minor inconsistencies\",\"Basic layout and working navigation, but lacks consistency or usability\",\"Interface is confusing or unattractive; navigation is broken or unclear\"]', 1, 0, NULL, 0, '2025-11-30 06:22:32', '2025-11-30 06:22:32', 0),
(172, 27, 'Performance', '[\"Smooth, fast, and stable system; handles load, input, and errors efficiently\",\"Acceptable performance; generally stable and responsive\",\"Occasionally slow or error-prone; inconsistent handling of input\",\"Very poor performance; system is slow, unresponsive, or crashes frequently\"]', 2, 0, NULL, 0, '2025-11-30 06:22:32', '2025-11-30 06:22:32', 0),
(173, 27, 'System Reliability & Error Handling', '[\"Highly robust; handles exceptions, validates input, and avoids crashes gracefully\",\"Works reliably with some input validation and error alerts\",\"Basic reliability but lacks input checks or error feedback\",\"System frequently fails; no validation or feedback\"]', 3, 0, NULL, 0, '2025-11-30 06:22:32', '2025-11-30 06:22:32', 0),
(174, 27, 'Technical Complexity', '[\"Very basic; no technical challenge or creativity involved\",\"Some effort shown, but implementation is mostly standard\",\"Demonstrates thoughtful use of tools, logic, or external services\",\"Impressive technical depth or creative integration of advanced tools and logic\"]', 4, 0, NULL, 0, '2025-11-30 06:22:32', '2025-11-30 06:22:32', 0),
(175, 28, 'Logical Flow and Structure', '[\"Exceptionally clear, coherent, and logically structured from start to end\",\"Generally wellorganized with logical progression between sections\",\"Inconsistent structure; some sections unclear or out of order\",\"Manuscript lacks structure; ideas are disorganized; transitions are poor\"]', 0, 0, NULL, 0, '2025-11-30 06:28:31', '2025-11-30 06:28:31', 0),
(176, 28, 'Adherence to Format Guidelines', '[\"Fully compliant with all formatting standards\",\"Follows most formatting rules with minor errors\",\"Some formatting inconsistencies; limited adherence to guidelines\",\"Does not follow prescribed formatting; major issues with margins, spacing, fonts, etc.\"]', 1, 0, NULL, 0, '2025-11-30 06:28:31', '2025-11-30 06:28:31', 0),
(177, 28, 'Clarity and Readability', '[\"Highly readable, well-written, free of grammar and spelling errors\",\"Clear writing with minor grammar\\/wording concerns\",\"Some sentences are difficult to understand; frequent grammar issues\",\"Writing is unclear, with poor grammar, awkward phrasing, or jargon\"]', 2, 0, NULL, 0, '2025-11-30 06:28:31', '2025-11-30 06:28:31', 0),
(178, 28, 'Use of Figures, Tables, and Appendices', '[\"Visuals enhance understanding; consistently formatted and appropriately referenced in text\",\"Relevant visuals used and labeled properly\",\"Some figures\\/tables used but lack consistency or proper formatting\",\"Missing or irrelevant visuals; poorly labeled or placed\"]', 3, 0, NULL, 0, '2025-11-30 06:28:31', '2025-11-30 06:28:31', 0),
(179, 29, 'Originality and Innovation', '[\"Proposes a highly original system and algorithm with unique, creative insights.\",\"Builds upon existing work with moderate innovation or new approaches.\",\"Slight improvements to existing ideas; limited originality\",\"Replicates existing solutions with no new insights or innovation.\"]', 0, 0, NULL, 0, '2025-11-30 06:32:31', '2025-11-30 06:32:31', 0),
(180, 29, 'Contribution and Relevance', '[\"Clearly addresses a significant gap and contributes meaningfully to the body of knowledge.\",\"Addresses a known problem and adds reasonable value to the field.\",\"Limited relevance; contribution is unclear or minimal.\",\"Fails to address a meaningful problem or contribute to existing knowledge.\"]', 1, 0, NULL, 0, '2025-11-30 06:32:31', '2025-11-30 06:32:31', 0),
(181, 29, 'Societal Impact and Inclusivity', '[\"Demonstrates strong real-world application and inclusive design for diverse users.\",\"Shows potential for societal use with some inclusivity.\",\"Limited application or accessibility considerations.\",\"Lacks practical application or excludes major user groups.\"]', 2, 0, NULL, 0, '2025-11-30 06:32:31', '2025-11-30 06:32:31', 0),
(183, 20, '1. Content - System and Manuscript', '[\"40%\"]', 0, 0, NULL, 0, '2025-11-30 06:56:39', '2025-11-30 06:56:39', 0),
(184, 20, '2. Organization - Manuscript', '[\"10%\"]', 1, 0, NULL, 0, '2025-11-30 06:56:39', '2025-11-30 06:56:39', 0),
(185, 20, '3. Novelty and Impact - System and Manuscript', '[\"10%\"]', 2, 0, NULL, 0, '2025-11-30 06:56:39', '2025-11-30 06:56:39', 0),
(212, 30, '1. Content - System and Manuscript', '[\"40%\"]', 0, 0, NULL, 0, '2025-11-30 07:13:12', '2025-11-30 07:13:12', 0),
(213, 30, '2. Organization - Manuscript', '[\"10%\"]', 1, 0, NULL, 0, '2025-11-30 07:13:12', '2025-11-30 07:13:12', 0),
(214, 30, '3. Novelty and Impact - System and Manuscript', '[\"10%\"]', 2, 0, NULL, 0, '2025-11-30 07:13:12', '2025-11-30 07:13:12', 0),
(219, 35, 'Chapter II', '[\"Well-curated, up-todate, and highly relevant sources; shows clear synthesis and strong critical insight connecting to the study\",\"Adequate selection of relevant sources; demonstrates synthesis and some critical thinking\",\"Limited and weakly connected sources; minimal synthesis of ideas\",\"Lacks relevant sources or contains outdated\\/irrelevant literature; no synthesis or connection to study\"]', 0, 0, NULL, 0, '2025-11-30 07:23:47', '2025-11-30 07:23:47', 0),
(220, 35, 'Chapter II', '[\"Strongly developed, insightful framework; clearly demonstrates the study\'s direction and theoretical foundation\",\"Clearly presented with visual and narrative explanation; logically aligned with objectives\",\"Present but vague; weak linkage to study or confusing flow\",\"Absent, unclear, or inappropriate; lacks coherence and logical structure\"]', 1, 0, NULL, 0, '2025-11-30 07:23:47', '2025-11-30 07:23:47', 0),
(221, 36, 'Chapter III', '[\"Strongly articulated design; logically aligned with study goals and appropriate for the project type\",\"Clearly described and generally fits the nature of the study\",\"Design is stated but lacks detail or partial relevance to the objectives\",\"No clear design or methodology; lacks direction or coherence\"]', 0, 0, NULL, 0, '2025-11-30 07:27:06', '2025-11-30 07:27:06', 0),
(222, 36, 'Chapter III', '[\"Well-justified and systematically selected; shows thoughtful planning and alignment with the study\\u2019s purpose\",\"Sampling method is described with clear rationale and participant relevance\",\"Described briefly with weak rationale or fit\",\"Sampling approach is missing, unjustified, or inappropriate\"]', 1, 0, NULL, 0, '2025-11-30 07:27:06', '2025-11-30 07:27:06', 0),
(223, 36, 'Chapter III', '[\"Clearly follows a structured model (e.g., Agile, SDLC) with well-explained development phases, roles, and iterations\",\"Identifies an organized process or model; explains phases of development\",\"Process is mentioned but lacks structure or coherence (e.g., unclear use of models like Agile\\/Waterfall)\",\"Development process is missing, disorganized, or inappropriate (e.g., no methodology used)\"]', 2, 0, NULL, 0, '2025-11-30 07:27:06', '2025-11-30 07:27:06', 0),
(224, 36, 'Chapter III', '[\"Well-structured and technically sound system architecture; clearly shows components, flow, and integration logic\",\"Provides basic structure and understandable architecture diagram\",\"Architecture is present but unclear or lacks technical coherence\",\"Absent or contains irrelevant\\/confusing diagrams or explanations\"]', 3, 0, NULL, 0, '2025-11-30 07:27:06', '2025-11-30 07:27:06', 0),
(225, 37, 'Modules and Features Implementation', '[\"All core modules are complete, integrated, and align well with project objectives\",\"Most modules are implemented and function as intended\",\"Some modules are working; lacks completeness or integration\",\"Very few features\\/modules are implemented; most are missing or do not work\"]', 0, 0, NULL, 0, '2025-11-30 07:31:36', '2025-11-30 07:31:36', 0),
(226, 37, 'User Interface & Navigation', '[\"Highly intuitive, visually consistent, and user-friendly interface\",\"UI is clean and usable with minor inconsistencies\",\"Basic layout and working navigation, but lacks consistency or usability\",\"Interface is confusing or unattractive; navigation is broken or unclear\"]', 1, 0, NULL, 0, '2025-11-30 07:31:36', '2025-11-30 07:31:36', 0),
(227, 37, 'Performance', '[\"Smooth, fast, and stable system; handles load, input, and errors efficiently\",\"Acceptable performance; generally stable and responsive\",\"Occasionally slow or error-prone; inconsistent handling of input\",\"Very poor performance; system is slow, unresponsive, or crashes frequently\"]', 2, 0, NULL, 0, '2025-11-30 07:31:36', '2025-11-30 07:31:36', 0),
(228, 37, 'System Reliability & Error Handling', '[\"Highly robust; handles exceptions, validates input, and avoids crashes gracefully\",\"Works reliably with some input validation and error alerts\",\"Basic reliability but lacks input checks or error feedback\",\"System frequently fails; no validation or feedback\"]', 3, 0, NULL, 0, '2025-11-30 07:31:36', '2025-11-30 07:31:36', 0),
(229, 37, 'Technical Complexity', '[\"Impressive technical depth or creative integration of advanced tools and logic\",\"Demonstrates thoughtful use of tools, logic, or external services\",\"Some effort shown, but implementation is mostly standard\",\"Very basic; no technical challenge or creativity involved\"]', 4, 0, NULL, 0, '2025-11-30 07:31:36', '2025-11-30 07:31:36', 0),
(230, 38, 'Logical Flow and Structure', '[\"Exceptionally clear, coherent, and logically structured from start to end\",\"Generally wellorganized with logical progression between sections\",\"Inconsistent structure; some sections unclear or out of order\",\"Manuscript lacks structure; ideas are disorganized; transitions are poor\"]', 0, 0, NULL, 0, '2025-11-30 07:35:49', '2025-11-30 07:35:49', 0),
(231, 38, 'Adherence to Format Guidelines', '[\"Fully compliant with all formatting standards\",\"Follows most formatting rules with minor errors\",\"Some formatting inconsistencies; limited adherence to guidelines\",\"Does not follow prescribed formatting; major issues with margins, spacing, fonts, etc.\"]', 1, 0, NULL, 0, '2025-11-30 07:35:49', '2025-11-30 07:35:49', 0),
(232, 38, 'Clarity and Readability', '[\"Highly readable, well-written, free of grammar and spelling errors\",\"Clear writing with minor grammar\\/wording concerns\",\"Some sentences are difficult to understand; frequent grammar issues\",\"Writing is unclear, with poor grammar, awkward phrasing, or jargon\"]', 2, 0, NULL, 0, '2025-11-30 07:35:49', '2025-11-30 07:35:49', 0),
(233, 38, 'Use of Figures, Tables, and Appendices', '[\"Visuals enhance understanding; consistently formatted and appropriately referenced in text\",\"Relevant visuals used and labeled properly\",\"Some figures\\/tables used but lack consistency or proper formatting\",\"Missing or irrelevant visuals; poorly labeled or placed\"]', 3, 0, NULL, 0, '2025-11-30 07:35:49', '2025-11-30 07:35:49', 0),
(234, 39, 'Originality and Innovation', '[\"Proposes a highly original system and algorithm with unique, creative insights\",\"Builds upon existing work with moderate innovation or new approaches\",\"Slight improvements to existing ideas; limited originality\",\"Replicates existing solutions with no new insights or innovation.\"]', 0, 0, NULL, 0, '2025-11-30 07:38:47', '2025-11-30 07:38:47', 0),
(235, 39, 'Contribution and Relevance', '[\"Clearly addresses a significant gap and contributes meaningfully to the body of knowledge.\",\"Addresses a known problem and adds reasonable value to the field.\",\"Limited relevance; contribution is unclear or minimal.\",\"Fails to address a meaningful problem or contribute to existing knowledge.\"]', 1, 0, NULL, 0, '2025-11-30 07:38:47', '2025-11-30 07:38:47', 0),
(236, 39, 'Societal Impact and Inclusivity', '[\"Demonstrates strong real-world application and inclusive design for diverse users.\",\"Shows potential for societal use with some inclusivity.\",\"Limited application or accessibility considerations.\",\"Lacks practical application or excludes major user groups.\"]', 2, 0, NULL, 0, '2025-11-30 07:38:47', '2025-11-30 07:38:47', 0),
(243, 18, '1. Clarity and mastery in the presentation', NULL, 0, 1, 15, 1, '2025-12-02 08:42:50', '2025-12-02 08:42:50', 0),
(244, 18, '2. Articulate response to the inquiries', NULL, 1, 1, 20, 1, '2025-12-02 08:42:50', '2025-12-02 08:42:50', 0),
(245, 18, '3. Proper demeanor and dress code', NULL, 2, 1, 5, 1, '2025-12-02 08:42:50', '2025-12-02 08:42:50', 0),
(334, 31, '1. The presentation is completed in the allowed time', NULL, 0, 1, 5, 1, '2025-12-04 07:41:54', '2025-12-04 07:41:54', 0),
(335, 31, '2. The visual presentation comprehensively illustrated the concepts.', NULL, 1, 1, 5, 1, '2025-12-04 07:41:54', '2025-12-04 07:41:54', 0),
(336, 31, '1. The presenter is confident and well-prepared.', NULL, 2, 1, 5, 1, '2025-12-04 07:41:54', '2025-12-04 07:41:54', 0),
(337, 31, '2. The presenter successfully conveyed the concepts', NULL, 3, 1, 5, 1, '2025-12-04 07:41:54', '2025-12-04 07:41:54', 0),
(338, 31, '3. The presenter demonstrated mastery and logical thinking in defending the proposal.', NULL, 4, 1, 5, 1, '2025-12-04 07:41:54', '2025-12-04 07:41:54', 0),
(339, 31, '1. The presentation is completed in the allowed time.', NULL, 5, 1, 5, 1, '2025-12-04 07:41:54', '2025-12-04 07:41:54', 0),
(340, 31, '2. The visual presentation comprehensively illustrated the concepts.', NULL, 6, 1, 5, 1, '2025-12-04 07:41:54', '2025-12-04 07:41:54', 0),
(378, 21, '1. The presentation is completed in the allowed time.', NULL, 0, 1, 5, 1, '2025-12-04 07:44:34', '2025-12-04 07:44:34', 0),
(379, 21, '2. The visual presentation comprehensively illustrated the concepts.', NULL, 1, 1, 5, 1, '2025-12-04 07:44:34', '2025-12-04 07:44:34', 0),
(380, 21, '1. The presenter is confident and well-prepared.', NULL, 2, 1, 5, 1, '2025-12-04 07:44:34', '2025-12-04 07:44:34', 0),
(381, 21, '2. The presenter successfully conveyed the concepts.', NULL, 3, 1, 5, 1, '2025-12-04 07:44:34', '2025-12-04 07:44:34', 0),
(382, 21, '3. The presenter demonstrated mastery and logical thinking in defending the proposal.', NULL, 4, 1, 5, 1, '2025-12-04 07:44:34', '2025-12-04 07:44:34', 0),
(383, 21, '1. The presentation is completed in the allowed time.', NULL, 5, 1, 5, 1, '2025-12-04 07:44:34', '2025-12-04 07:44:34', 0),
(384, 21, '2. The visual presentation comprehensively illustrated the concepts.', NULL, 6, 1, 5, 1, '2025-12-04 07:44:34', '2025-12-04 07:44:34', 0),
(450, 22, 'Delivery', NULL, 0, 1, 0, 0, '2025-12-04 09:04:22', '2025-12-04 09:04:22', 1),
(451, 22, '1. The presenter is confident and well-prepared.', NULL, 1, 1, 10, 1, '2025-12-04 09:04:22', '2025-12-04 09:04:22', 0),
(452, 22, '2. The presenter successfully conveyed the concepts.', NULL, 2, 1, 10, 1, '2025-12-04 09:04:22', '2025-12-04 09:04:22', 0),
(453, 22, '3. The presenter demonstrated mastery and logical thinking in defending the proposal.', NULL, 3, 1, 10, 1, '2025-12-04 09:04:22', '2025-12-04 09:04:22', 0),
(454, 22, 'Presentation', NULL, 4, 1, 0, 0, '2025-12-04 09:04:22', '2025-12-04 09:04:22', 1),
(455, 22, '1. The presentation is completed in the allowed time.', NULL, 5, 1, 5, 1, '2025-12-04 09:04:22', '2025-12-04 09:04:22', 0),
(456, 22, '2. The visual presentation comprehensively illustrated the concepts.', NULL, 6, 1, 5, 1, '2025-12-04 09:04:22', '2025-12-04 09:04:22', 0),
(515, 34, 'Content – Quality of the Developed System (20%)', '[\"\",\"\",\"\",\"\"]', 0, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 1),
(516, 34, 'Chapter I', '[\"Thorough, wellarticulated context; strongly justifies the study with relevant support\",\"Provides clear and relevant background; rationale is present but may lack depth\",\"Provides minimal context with limited connection to the problem; lacks depth\",\"Lacks clarity, relevance, or justification; context is missing or inappropriate\"]', 1, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(517, 34, 'Chapter I', '[\"Objectives are precise, measurable, and clearly aligned with the research problem\",\"Objectives are clear and aligned with the problem statement\",\"Objectives are stated but unclear or not aligned with the problem\",\"Objectives are vague, irrelevant, or missing\"]', 2, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(518, 34, 'Chapter I', '[\"Strong justification of the study\\u2019s importance; clearly identifies impact and beneficiaries\",\"Adequately explains importance and identifies key beneficiaries\",\"Minimally explains importance; beneficiaries are unclear\",\"No clear value to stakeholders; lacks justification or impact\"]', 3, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(519, 34, 'Chapter I', '[\"Scope and limitations are specific, realistic, and critically analyzed in the context of the study\",\"Scope and limitations are clearly defined and relevant\",\"Scope and limitations are present but lack detail or clarity\",\"Scope is vague or too broad; limitations not identified or irrelevant\"]', 4, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(520, 34, 'Chapter II', '[\"Well-curated, up-todate, and highly relevant sources; shows clear synthesis and strong critical insight connecting to the study\",\"Adequate selection of relevant sources; demonstrates synthesis and some critical thinking\",\"Limited and weakly connected sources; minimal synthesis of ideas\",\"Lacks relevant sources or contains outdated\\/irrelevant literature; no synthesis or connection to study\"]', 5, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(521, 34, 'Chapter II', '[\"Strongly developed, insightful framework; clearly demonstrates the study\'s direction and theoretical foundation\",\"Clearly presented with visual and narrative explanation; logically aligned with objectives\",\"Present but vague; weak linkage to study or confusing flow\",\"Absent, unclear, or inappropriate; lacks coherence and logical structure\"]', 6, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(522, 34, 'Chapter III', '[\"Strongly articulated design; logically aligned with study goals and appropriate for the project type\",\"Clearly described and generally fits the nature of the study\",\"Design is stated but lacks detail or partial relevance to the objectives\",\"No clear design or methodology; lacks direction or coherence\"]', 7, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(523, 34, 'Chapter III', '[\"Well-justified and systematically selected; shows thoughtful planning and alignment with the study\\u2019s purpose\",\"Sampling method is described with clear rationale and participant relevance\",\"Described briefly with weak rationale or fit\",\"Sampling approach is missing, unjustified, or inappropriate\"]', 8, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(524, 34, 'Chapter III', '[\"Clearly follows a structured model (e.g., Agile, SDLC) with well-explained development phases, roles, and iterations\",\"Identifies an organized process or model; explains phases of development\",\"Process is mentioned but lacks structure or coherence (e.g., unclear use of models like Agile\\/Waterfall)\",\"Development process is missing, disorganized, or inappropriate (e.g., no methodology used)\"]', 9, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(525, 34, 'Chapter III', '[\"Well-structured and technically sound system architecture; clearly shows components, flow, and integration logic\",\"Provides basic structure and understandable architecture diagram\",\"Architecture is present but unclear or lacks technical coherence\",\"Absent or contains irrelevant\\/confusing diagrams or explanations\"]', 10, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(526, 34, 'Content – Quality of the Developed System (20%)', '[\"\",\"\",\"\",\"\"]', 11, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 1),
(527, 34, 'Modules and Features Implementation', '[\"All core modules are complete, integrated, and align well with project objectives\",\"Most modules are implemented and function as intended\",\"Some modules are working; lacks completeness or integration\",\"Very few features\\/modules are implemented; most are missing or do not work\"]', 12, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(528, 34, 'User Interface & Navigation', '[\"Highly intuitive, visually consistent, and user-friendly interface\",\"UI is clean and usable with minor inconsistencies\",\"Basic layout and working navigation, but lacks consistency or usability\",\"Interface is confusing or unattractive; navigation is broken or unclear\"]', 13, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(529, 34, 'Performance', '[\"Smooth, fast, and stable system; handles load, input, and errors efficiently\",\"Acceptable performance; generally stable and responsive\",\"Occasionally slow or error-prone; inconsistent handling of input\",\"Very poor performance; system is slow, unresponsive, or crashes frequently\"]', 14, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(530, 34, 'System Reliability & Error Handling', '[\"Highly robust; handles exceptions, validates input, and avoids crashes gracefully\",\"Works reliably with some input validation and error alerts\",\"Basic reliability but lacks input checks or error feedback\",\"System frequently fails; no validation or feedback\"]', 15, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(531, 34, 'Technical Complexity', '[\"Impressive technical depth or creative integration of advanced tools and logic\",\"Demonstrates thoughtful use of tools, logic, or external services\",\"Some effort shown, but implementation is mostly standard\",\"Very basic; no technical challenge or creativity involved\"]', 16, 0, NULL, NULL, '2025-12-04 19:45:34', '2025-12-04 19:45:34', 0),
(532, 24, 'Content – Quality of the Developed System (20%)', '[\"\",\"\",\"\",\"\"]', 0, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 1),
(533, 24, 'Chapter I', '[\"Thorough, well articulated context;  strongly justifies the  study with relevant  support\",\"Provides clear and relevant background; rationale is present but may lack depth\",\"Provides minimal context with limited connection to the problem; lacks depth\",\"Lacks clarity, relevance, or justification; context is missing or inappropriate\"]', 1, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(534, 24, 'Chapter I', '[\"Objectives are precise, measurable, and clearly aligned with the research problem\",\"Objectives are clear and aligned with the problem statement\",\"Objectives are stated but unclear or not aligned with the problem\",\"Objectives are vague, irrelevant, or missing\"]', 2, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(535, 24, 'Chapter I', '[\"Strong justification of the study\\u2019s importance; clearly identifies impact and beneficiaries\",\"Adequately explains importance and identifies key beneficiaries\",\"Minimally explains importance; beneficiaries are unclear\",\"No clear value to stakeholders; lacks justification or impact\"]', 3, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(536, 24, 'Chapter I', '[\"Scope and limitations are specific, realistic, and critically analyzed in the context of the study\",\"Scope and limitations are clearly defined and relevant\",\"Scope and limitations are present but lack detail or clarity\",\"Scope is vague or too broad; limitations not identified or irrelevant\"]', 4, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(537, 24, 'Chapter II', '[\"Well-curated, up-todate, and highly relevant sources; shows clear synthesis and strong critical insight connecting to the study\",\"Adequate selection of relevant sources; demonstrates synthesis and some critical thinking\",\"Limited and weakly connected sources; minimal synthesis of ideas\",\"Lacks relevant sources or contains outdated\\/irrelevant literature; no synthesis or connection to study\"]', 5, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(538, 24, 'Chapter II', '[\"Strongly developed, insightful framework; clearly demonstrates the study\'s direction and theoretical foundation\",\"Clearly presented with visual and narrative explanation; ogically aligned with objectives\",\"Present but vague; weak linkage to study or confusing flow\",\"Absent, unclear, or inappropriate; lacks coherence and logical structure\"]', 6, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(539, 24, 'Chapter III', '[\"Strongly articulated design; logically aligned with study goals and appropriate for the project type\",\"Clearly described and generally fits the nature of the study\",\"Design is stated but lacks detail or partial relevance to the objectives\",\"No clear design or methodology; lacks direction or coherence\"]', 7, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(540, 24, 'Chapter III', '[\"Well-justified and systematically selected; shows thoughtful planning and alignment with the study\\u2019s purpose\",\"Sampling method is described with clear rationale and participant relevance\",\"Described briefly with weak rationale or fit\",\"Sampling approach is missing, unjustified, or inappropriate\"]', 8, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(541, 24, 'Chapter III', '[\"Clearly follows a structured model (e.g., Agile, SDLC) with well-explained development phases, roles, and iterations\",\"Identifies an organized process or model; explains phases of development\",\"Process is mentioned but lacks structure or coherence (e.g., unclear use of models like Agile\\/Waterfall)\",\"Development process is missing, disorganized, or inappropriate (e.g., no methodology used)\"]', 9, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(542, 24, 'Chapter III', '[\"Well-structured and technically sound system architecture; clearly shows components, flow, and integration logic\",\"Provides basic structure and understandable architecture diagram\",\"Architecture is present but unclear or lacks technical coherence\",\"Absent or contains irrelevant\\/confusing diagrams or explanations\"]', 10, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(543, 24, 'Content – Quality of the Developed System (20%)', '[\"\",\"\",\"\",\"\"]', 11, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 1),
(544, 24, 'Modules and Features Implementation', '[\"All core modules are complete, integrated, and align well with project objectives\",\"Most modules are implemented and function as intended\",\"Some modules are working; lacks completeness or integration\",\"Very few features\\/modules are implemented; most are missing or do not work\"]', 12, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(545, 24, 'User Interface & Navigation', '[\"Highly intuitive, visually consistent, and user-friendly interface\",\"UI is clean and usable with minor inconsistencies\",\"Basic layout and working navigation, but lacks consistency or usability\",\"Interface is confusing or unattractive; navigation is broken or unclear\"]', 13, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0);
INSERT INTO `rubric_criteria` (`id`, `rubric_id`, `criterion_text`, `criterion_detail`, `order_index`, `is_individual`, `max_score`, `min_score`, `created_at`, `updated_at`, `is_blank`) VALUES
(546, 24, 'Performance', '[\"Smooth, fast, and stable system; handles load, input, and errors efficiently\",\"Acceptable performance; generally stable and responsive\",\"Occasionally slow or error-prone; inconsistent handling of input\",\"Very poor performance; system is slow, unresponsive, or crashes frequently\"]', 14, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(547, 24, 'System Reliability & Error Handling', '[\"Highly robust; handles exceptions, validates input, and avoids crashes gracefully\",\"Works reliably with some input validation and error alerts\",\"Basic reliability but lacks input checks or error feedback\",\"System frequently fails; no validation or feedback\"]', 15, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(548, 24, 'Technical Complexity', '[\"Very basic; no technical challenge or creativity involved\",\"Some effort shown, but implementation is mostly standard\",\"Demonstrates thoughtful use of tools, logic, or external services\",\"Impressive technical depth or creative integration of advanced tools and logic\"]', 16, 0, NULL, NULL, '2025-12-04 19:46:03', '2025-12-04 19:46:03', 0),
(549, 32, 'Presentation', NULL, 0, 1, 0, 0, '2025-12-05 05:06:19', '2025-12-05 05:06:19', 1),
(550, 32, '1. The presentation is completed in the allowed time.', NULL, 1, 1, 5, 1, '2025-12-05 05:06:19', '2025-12-05 05:06:19', 0),
(551, 32, '2. The visual presentation comprehensively illustrated the concepts.', NULL, 2, 1, 5, 1, '2025-12-05 05:06:19', '2025-12-05 05:06:19', 0),
(552, 32, 'Delivery', NULL, 3, 1, 0, 0, '2025-12-05 05:06:19', '2025-12-05 05:06:19', 1),
(553, 32, '1. The presenter is confident and well-prepared.', NULL, 4, 1, 10, 1, '2025-12-05 05:06:19', '2025-12-05 05:06:19', 0),
(554, 32, '2. The presenter successfully conveyed the concepts', NULL, 5, 1, 10, 1, '2025-12-05 05:06:19', '2025-12-05 05:06:19', 0),
(555, 32, '3. The presenter demonstrated mastery and logical thinking in defending the proposal.', NULL, 6, 1, 10, 1, '2025-12-05 05:06:19', '2025-12-05 05:06:19', 0),
(556, 40, '1. The presenter is confident and well-prepared.', '[\"test\"]', 0, 0, 5, 1, '2026-04-12 22:02:16', '2026-04-12 22:02:16', 0),
(557, 40, 'test', '[\"test\"]', 1, 0, 5, 1, '2026-04-12 22:02:16', '2026-04-12 22:02:16', 0),
(558, 40, 'test', '[\"test\"]', 2, 0, 5, 1, '2026-04-12 22:02:16', '2026-04-12 22:02:16', 0);

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

--
-- Dumping data for table `rubric_groups`
--

INSERT INTO `rubric_groups` (`id`, `name`, `description`, `defense_type`, `program_id`, `created_at`, `updated_at`) VALUES
(1, 'Information Technology and Computer Science', '(RE-PRESENTATION)', 're_defense', NULL, '2025-07-21 01:50:27', '2025-12-01 11:17:54'),
(5, 'CCS', 'Final Defense', 'final_defense', NULL, '2025-11-30 04:50:06', '2025-12-04 08:37:21'),
(6, 'CCS', 'Proposal Re-Presentation', 're_defense', NULL, '2025-11-30 06:37:52', '2025-12-04 08:37:17'),
(7, 'CCS', 'Proposal Defense', 'title_defense', NULL, '2025-11-30 07:42:50', '2025-12-05 05:05:48'),
(8, 'CCS', 'Title Proposal', 'title_proposal', NULL, '2025-12-01 11:20:21', '2025-12-04 08:56:42');

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

--
-- Dumping data for table `rubric_group_items`
--

INSERT INTO `rubric_group_items` (`id`, `group_id`, `rubric_id`, `order_index`, `weight`, `created_at`, `updated_at`) VALUES
(30, 1, 1, 0, 30.00, '2025-07-21 05:30:27', '2025-07-21 05:30:27'),
(31, 1, 2, 1, 20.00, '2025-07-21 05:30:27', '2025-07-21 05:30:27'),
(32, 1, 3, 2, 20.00, '2025-07-21 05:30:27', '2025-07-21 05:30:27'),
(33, 1, 4, 3, 10.00, '2025-07-21 05:30:27', '2025-07-21 05:30:27'),
(34, 1, 5, 4, 20.00, '2025-07-21 05:30:27', '2025-07-21 05:30:27'),
(35, 1, 6, 5, NULL, '2025-07-21 05:30:27', '2025-07-21 05:30:27'),
(36, 2, 8, 0, 30.00, '2025-11-30 01:24:46', '2025-11-30 01:24:46'),
(37, 2, 9, 1, 30.00, '2025-11-30 01:24:46', '2025-11-30 01:24:46'),
(38, 2, 10, 2, 40.00, '2025-11-30 01:24:46', '2025-11-30 01:24:46'),
(39, 2, 11, 3, NULL, '2025-11-30 01:24:46', '2025-11-30 01:24:46'),
(40, 3, 12, 0, 50.00, '2025-11-30 02:05:33', '2025-11-30 02:05:33'),
(41, 3, 13, 1, 50.00, '2025-11-30 02:05:33', '2025-11-30 02:05:33'),
(42, 4, 12, 0, 50.00, '2025-11-30 04:24:32', '2025-11-30 04:24:32'),
(43, 4, 13, 1, 50.00, '2025-11-30 04:24:32', '2025-11-30 04:24:32'),
(166, 6, 24, 0, 40.00, '2025-12-04 08:37:17', '2025-12-04 08:37:17'),
(167, 6, 28, 1, 10.00, '2025-12-04 08:37:17', '2025-12-04 08:37:17'),
(168, 6, 29, 2, 10.00, '2025-12-04 08:37:17', '2025-12-04 08:37:17'),
(169, 6, 22, 3, 40.00, '2025-12-04 08:37:17', '2025-12-04 08:37:17'),
(170, 6, 23, 4, NULL, '2025-12-04 08:37:17', '2025-12-04 08:37:17'),
(171, 5, 16, 0, 20.00, '2025-12-04 08:37:21', '2025-12-04 08:37:21'),
(172, 5, 17, 1, 20.00, '2025-12-04 08:37:21', '2025-12-04 08:37:21'),
(173, 5, 18, 2, 20.00, '2025-12-04 08:37:21', '2025-12-04 08:37:21'),
(174, 5, 14, 3, 20.00, '2025-12-04 08:37:21', '2025-12-04 08:37:21'),
(175, 5, 15, 4, 20.00, '2025-12-04 08:37:21', '2025-12-04 08:37:21'),
(176, 5, 19, 5, NULL, '2025-12-04 08:37:21', '2025-12-04 08:37:21'),
(187, 8, 34, 0, 40.00, '2025-12-04 08:56:42', '2025-12-04 08:56:42'),
(188, 8, 38, 1, 10.00, '2025-12-04 08:56:42', '2025-12-04 08:56:42'),
(189, 8, 39, 2, 10.00, '2025-12-04 08:56:42', '2025-12-04 08:56:42'),
(190, 8, 22, 3, 40.00, '2025-12-04 08:56:42', '2025-12-04 08:56:42'),
(191, 8, 33, 4, NULL, '2025-12-04 08:56:42', '2025-12-04 08:56:42'),
(192, 7, 32, 0, 40.00, '2025-12-05 05:05:48', '2025-12-05 05:05:48'),
(193, 7, 34, 1, 40.00, '2025-12-05 05:05:48', '2025-12-05 05:05:48'),
(194, 7, 38, 2, 10.00, '2025-12-05 05:05:48', '2025-12-05 05:05:48'),
(195, 7, 39, 3, 10.00, '2025-12-05 05:05:48', '2025-12-05 05:05:48'),
(196, 7, 33, 4, NULL, '2025-12-05 05:05:48', '2025-12-05 05:05:48'),
(197, 9, 39, 0, NULL, '2026-04-12 02:33:07', '2026-04-12 02:33:07'),
(198, 9, 30, 1, NULL, '2026-04-12 02:33:07', '2026-04-12 02:33:07'),
(210, 10, 34, 0, 60.00, '2026-04-12 22:03:33', '2026-04-12 22:03:33'),
(211, 10, 33, 1, NULL, '2026-04-12 22:03:33', '2026-04-12 22:03:33'),
(212, 10, 31, 2, 30.00, '2026-04-12 22:03:33', '2026-04-12 22:03:33'),
(213, 11, 1, 0, NULL, '2026-04-13 05:59:16', '2026-04-13 05:59:16'),
(216, 12, 37, 0, 50.00, '2026-04-13 07:16:50', '2026-04-13 07:16:50');

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

--
-- Dumping data for table `rubric_levels`
--

INSERT INTO `rubric_levels` (`id`, `rubric_id`, `level_index`, `name`, `description`, `points_min`, `points_max`, `is_range`, `created_at`, `updated_at`) VALUES
(7, 1, 1, 'Unacceptable', '', 1, 3, 1, '2025-07-21 01:36:37', '2025-07-21 01:36:37'),
(8, 1, 2, 'Acceptable ', '', 4, 7, 1, '2025-07-21 01:36:37', '2025-07-21 01:36:37'),
(9, 1, 3, 'Excellent ', '', 8, 10, 1, '2025-07-21 01:36:37', '2025-07-21 01:36:37'),
(13, 4, 1, 'Unacceptable ', '', 0, 1, 1, '2025-07-21 01:45:09', '2025-07-21 01:45:09'),
(14, 4, 2, 'Acceptable ', '', 2, 3, 1, '2025-07-21 01:45:09', '2025-07-21 01:45:09'),
(15, 4, 3, 'Exemplary ', '', 4, 5, 1, '2025-07-21 01:45:09', '2025-07-21 01:45:09'),
(16, 3, 1, 'Unacceptable', '', 0, 1, 1, '2025-07-21 01:45:42', '2025-07-21 01:45:42'),
(17, 3, 2, 'Acceptable ', '', 2, 3, 1, '2025-07-21 01:45:42', '2025-07-21 01:45:42'),
(18, 3, 3, 'Exemplary ', '', 4, 5, 1, '2025-07-21 01:45:42', '2025-07-21 01:45:42'),
(20, 5, 1, 'Level 1', '', 1, 1, 0, '2025-07-21 01:48:20', '2025-07-21 01:48:20'),
(24, 6, 1, 'Pass Option 1', 'without revision; 100% acceptability  ', NULL, NULL, 0, '2025-07-21 01:53:02', '2025-07-21 01:53:02'),
(25, 6, 2, 'Pass Option 2', 'with minor revision(s); 75-99.99% acceptability; refer to evaluation sheet', NULL, NULL, 0, '2025-07-21 01:53:02', '2025-07-21 01:53:02'),
(26, 6, 3, 'Pass Option 3', 'with major revisions; 65-74.99% acceptability; for re -presentation', NULL, NULL, 0, '2025-07-21 01:53:02', '2025-07-21 01:53:02'),
(30, 2, 1, 'Unacceptable ', '', 1, 3, 1, '2025-07-21 05:25:50', '2025-07-21 05:25:50'),
(31, 2, 2, 'Acceptable ', '', 4, 7, 1, '2025-07-21 05:25:50', '2025-07-21 05:25:50'),
(32, 2, 3, 'Excellent ', '', 8, 10, 1, '2025-07-21 05:25:50', '2025-07-21 05:25:50'),
(37, 7, 1, 'Level 1', '', 5, 6, 1, '2025-07-21 05:27:25', '2025-07-21 05:27:25'),
(38, 7, 2, 'Level 2', '', 3, 4, 1, '2025-07-21 05:27:25', '2025-07-21 05:27:25'),
(39, 7, 3, 'Level 3', '', 1, 2, 1, '2025-07-21 05:27:25', '2025-07-21 05:27:25'),
(44, 8, 1, '%', NULL, 5, 5, 0, '2025-11-30 01:09:24', '2025-11-30 01:09:24'),
(45, 9, 1, '%', NULL, 5, 5, 0, '2025-11-30 01:10:26', '2025-11-30 01:10:26'),
(46, 10, 1, 'Level 1', NULL, 1, 1, 0, '2025-11-30 01:17:01', '2025-11-30 01:17:01'),
(47, 11, 1, 'Pass Option 1', 'without revision', NULL, NULL, 0, '2025-11-30 01:21:35', '2025-11-30 01:21:35'),
(48, 11, 2, 'Pass Option 2', 'with minor revisions: at least 80% acceptability (refer to evaluation sheet)', NULL, NULL, 0, '2025-11-30 01:21:35', '2025-11-30 01:21:35'),
(49, 11, 3, 'Pass Option 3', 'with major revisions: at least 70% acceptability (for re-defense)', NULL, NULL, 0, '2025-11-30 01:21:35', '2025-11-30 01:21:35'),
(50, 12, 1, '1', NULL, 1, 1, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15'),
(51, 12, 2, '2', NULL, 2, 2, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15'),
(52, 12, 3, '3', NULL, 3, 3, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15'),
(53, 12, 4, '4', NULL, 4, 4, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15'),
(54, 12, 5, '5', NULL, 5, 5, 0, '2025-11-30 01:54:15', '2025-11-30 01:54:15'),
(60, 13, 1, '1', NULL, 1, 1, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40'),
(61, 13, 2, '2', NULL, 2, 2, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40'),
(62, 13, 3, '3', NULL, 3, 3, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40'),
(63, 13, 4, '4', NULL, 4, 4, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40'),
(64, 13, 5, '5', NULL, 5, 5, 0, '2025-11-30 02:00:40', '2025-11-30 02:00:40'),
(80, 15, 1, '5', NULL, 5, 5, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56'),
(81, 15, 2, '4', NULL, 4, 4, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56'),
(82, 15, 3, '3', NULL, 3, 3, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56'),
(83, 15, 4, '2', NULL, 2, 2, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56'),
(84, 15, 5, '1', NULL, 1, 1, 0, '2025-11-30 04:25:56', '2025-11-30 04:25:56'),
(85, 14, 1, '5', NULL, 5, 5, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09'),
(86, 14, 2, '4', NULL, 4, 4, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09'),
(87, 14, 3, '3', NULL, 3, 3, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09'),
(88, 14, 4, '2', NULL, 2, 2, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09'),
(89, 14, 5, '1', NULL, 1, 1, 0, '2025-11-30 04:26:09', '2025-11-30 04:26:09'),
(92, 16, 1, '%', NULL, 1, 5, 1, '2025-11-30 04:34:29', '2025-11-30 04:34:29'),
(93, 17, 1, '%', NULL, 1, 5, 1, '2025-11-30 04:36:14', '2025-11-30 04:36:14'),
(98, 19, 1, 'Pass Option 1', 'without revision', NULL, NULL, 0, '2025-11-30 04:48:15', '2025-11-30 04:48:15'),
(99, 19, 2, 'Pass Option 2', 'with minor revisions: at least 80% acceptability (refer to evaluation sheet)', NULL, NULL, 0, '2025-11-30 04:48:15', '2025-11-30 04:48:15'),
(100, 19, 3, 'Pass Option 3', 'with major revisions: at least 70% acceptability (for re-defense)', NULL, NULL, 0, '2025-11-30 04:48:15', '2025-11-30 04:48:15'),
(106, 23, 1, 'Pass Option 1', '(at least 70% acceptability)', NULL, NULL, 0, '2025-11-30 05:59:08', '2025-11-30 05:59:08'),
(111, 25, 1, 'Highly Accpetable', NULL, 4, 4, 0, '2025-11-30 06:13:35', '2025-11-30 06:13:35'),
(112, 25, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 06:13:35', '2025-11-30 06:13:35'),
(113, 25, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 06:13:35', '2025-11-30 06:13:35'),
(114, 25, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 06:13:35', '2025-11-30 06:13:35'),
(115, 26, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 06:17:36', '2025-11-30 06:17:36'),
(116, 26, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 06:17:36', '2025-11-30 06:17:36'),
(117, 26, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 06:17:36', '2025-11-30 06:17:36'),
(118, 26, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 06:17:36', '2025-11-30 06:17:36'),
(119, 27, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 06:22:32', '2025-11-30 06:22:32'),
(120, 27, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 06:22:32', '2025-11-30 06:22:32'),
(121, 27, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 06:22:32', '2025-11-30 06:22:32'),
(122, 27, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 06:22:32', '2025-11-30 06:22:32'),
(123, 28, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 06:28:31', '2025-11-30 06:28:31'),
(124, 28, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 06:28:31', '2025-11-30 06:28:31'),
(125, 28, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 06:28:31', '2025-11-30 06:28:31'),
(126, 28, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 06:28:31', '2025-11-30 06:28:31'),
(127, 29, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 06:32:31', '2025-11-30 06:32:31'),
(128, 29, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 06:32:31', '2025-11-30 06:32:31'),
(129, 29, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 06:32:31', '2025-11-30 06:32:31'),
(130, 29, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 06:32:31', '2025-11-30 06:32:31'),
(132, 20, 1, '%', NULL, 1, 40, 1, '2025-11-30 06:56:39', '2025-11-30 06:56:39'),
(146, 30, 1, '%', NULL, 1, 40, 1, '2025-11-30 07:13:12', '2025-11-30 07:13:12'),
(151, 35, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 07:23:47', '2025-11-30 07:23:47'),
(152, 35, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 07:23:47', '2025-11-30 07:23:47'),
(153, 35, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 07:23:47', '2025-11-30 07:23:47'),
(154, 35, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 07:23:47', '2025-11-30 07:23:47'),
(155, 36, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 07:27:06', '2025-11-30 07:27:06'),
(156, 36, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 07:27:06', '2025-11-30 07:27:06'),
(157, 36, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 07:27:06', '2025-11-30 07:27:06'),
(158, 36, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 07:27:06', '2025-11-30 07:27:06'),
(159, 37, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 07:31:36', '2025-11-30 07:31:36'),
(160, 37, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 07:31:36', '2025-11-30 07:31:36'),
(161, 37, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 07:31:36', '2025-11-30 07:31:36'),
(162, 37, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 07:31:36', '2025-11-30 07:31:36'),
(163, 38, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 07:35:49', '2025-11-30 07:35:49'),
(164, 38, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 07:35:49', '2025-11-30 07:35:49'),
(165, 38, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 07:35:49', '2025-11-30 07:35:49'),
(166, 38, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 07:35:49', '2025-11-30 07:35:49'),
(167, 39, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 07:38:47', '2025-11-30 07:38:47'),
(168, 39, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 07:38:47', '2025-11-30 07:38:47'),
(169, 39, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 07:38:47', '2025-11-30 07:38:47'),
(170, 39, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 07:38:47', '2025-11-30 07:38:47'),
(173, 18, 1, 'Level 1', NULL, 1, 5, 1, '2025-12-02 08:42:50', '2025-12-02 08:42:50'),
(197, 31, 1, '%', NULL, 1, 5, 1, '2025-12-04 07:41:54', '2025-12-04 07:41:54'),
(207, 21, 1, 'Level 1', NULL, 1, 5, 1, '2025-12-04 07:44:34', '2025-12-04 07:44:34'),
(221, 22, 1, 'Level 1', NULL, 1, 5, 1, '2025-12-04 09:04:22', '2025-12-04 09:04:22'),
(235, 34, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-12-04 19:45:34', '2025-12-04 19:45:34'),
(236, 34, 2, 'Acceptable', NULL, 3, 3, 0, '2025-12-04 19:45:34', '2025-12-04 19:45:34'),
(237, 34, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-12-04 19:45:34', '2025-12-04 19:45:34'),
(238, 34, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-12-04 19:45:34', '2025-12-04 19:45:34'),
(239, 24, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-12-04 19:46:03', '2025-12-04 19:46:03'),
(240, 24, 2, 'Acceptable', NULL, 3, 3, 0, '2025-12-04 19:46:03', '2025-12-04 19:46:03'),
(241, 24, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-12-04 19:46:03', '2025-12-04 19:46:03'),
(242, 24, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-12-04 19:46:03', '2025-12-04 19:46:03'),
(243, 32, 1, 'Level 1', NULL, 1, 1, 0, '2025-12-05 05:06:19', '2025-12-05 05:06:19'),
(244, 33, 1, 'Pass Option 1', 'without revision', NULL, NULL, 0, '2025-12-05 22:09:17', '2025-12-05 22:09:17'),
(245, 33, 2, 'Pass Option 2', 'with minor revisions: at least 80% acceptability (refer to evaluation\nsheet)', NULL, NULL, 0, '2025-12-05 22:09:17', '2025-12-05 22:09:17'),
(246, 33, 3, 'Pass Option 3', 'with major revisions: at least 70% acceptability (for re-defense)', NULL, NULL, 0, '2025-12-05 22:09:17', '2025-12-05 22:09:17'),
(247, 40, 1, 'Level 1', NULL, 1, 1, 0, '2026-04-12 22:02:16', '2026-04-12 22:02:16');

-- --------------------------------------------------------

--
-- Table structure for table `rubric_programs`
--

CREATE TABLE `rubric_programs` (
  `rubric_id` int(10) UNSIGNED NOT NULL,
  `program_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Associates rubrics with specific programs';

--
-- Dumping data for table `rubric_programs`
--

INSERT INTO `rubric_programs` (`rubric_id`, `program_name`) VALUES
(1, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(2, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(3, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(4, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(5, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(6, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(8, 'Bachelor of Library and Information Science'),
(8, 'Bachelor of Science in Computer Science - Data Science'),
(8, 'Bachelor of Science in Computer Science - Software Engineering'),
(8, 'Bachelor of Science in Information Technology - Network and Information Security'),
(8, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(9, 'Bachelor of Library and Information Science'),
(9, 'Bachelor of Science in Computer Science - Data Science'),
(9, 'Bachelor of Science in Computer Science - Software Engineering'),
(9, 'Bachelor of Science in Information Technology - Network and Information Security'),
(9, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(10, 'Bachelor of Library and Information Science'),
(10, 'Bachelor of Science in Computer Science - Data Science'),
(10, 'Bachelor of Science in Computer Science - Software Engineering'),
(10, 'Bachelor of Science in Information Technology - Network and Information Security'),
(10, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(11, 'Bachelor of Library and Information Science'),
(11, 'Bachelor of Science in Computer Science - Data Science'),
(11, 'Bachelor of Science in Computer Science - Software Engineering'),
(11, 'Bachelor of Science in Information Technology - Network and Information Security'),
(11, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(12, 'Bachelor of Library and Information Science'),
(12, 'Bachelor of Science in Computer Science - Data Science'),
(12, 'Bachelor of Science in Computer Science - Software Engineering'),
(12, 'Bachelor of Science in Information Technology - Network and Information Security'),
(12, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(13, 'Bachelor of Library and Information Science'),
(13, 'Bachelor of Science in Computer Science - Data Science'),
(13, 'Bachelor of Science in Computer Science - Software Engineering'),
(13, 'Bachelor of Science in Information Technology - Network and Information Security'),
(13, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(14, 'Bachelor of Library and Information Science'),
(14, 'Bachelor of Science in Computer Science - Data Science'),
(14, 'Bachelor of Science in Computer Science - Software Engineering'),
(14, 'Bachelor of Science in Information Technology - Network and Information Security'),
(14, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(15, 'Bachelor of Library and Information Science'),
(15, 'Bachelor of Science in Computer Science - Data Science'),
(15, 'Bachelor of Science in Computer Science - Software Engineering'),
(15, 'Bachelor of Science in Information Technology - Network and Information Security'),
(15, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(16, 'Bachelor of Library and Information Science'),
(16, 'Bachelor of Science in Computer Science - Data Science'),
(16, 'Bachelor of Science in Computer Science - Software Engineering'),
(16, 'Bachelor of Science in Information Technology - Network and Information Security'),
(16, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(17, 'Bachelor of Library and Information Science'),
(17, 'Bachelor of Science in Computer Science - Data Science'),
(17, 'Bachelor of Science in Computer Science - Software Engineering'),
(17, 'Bachelor of Science in Information Technology - Network and Information Security'),
(17, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(18, 'Bachelor of Library and Information Science'),
(18, 'Bachelor of Science in Computer Science - Data Science'),
(18, 'Bachelor of Science in Computer Science - Software Engineering'),
(18, 'Bachelor of Science in Information Technology - Network and Information Security'),
(18, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(19, 'Bachelor of Library and Information Science'),
(19, 'Bachelor of Science in Computer Science - Data Science'),
(19, 'Bachelor of Science in Computer Science - Software Engineering'),
(19, 'Bachelor of Science in Information Technology - Network and Information Security'),
(19, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(20, 'Bachelor of Library and Information Science'),
(20, 'Bachelor of Science in Computer Science - Data Science'),
(20, 'Bachelor of Science in Computer Science - Software Engineering'),
(20, 'Bachelor of Science in Information Technology - Network and Information Security'),
(20, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(21, 'Bachelor of Library and Information Science'),
(21, 'Bachelor of Science in Computer Science - Data Science'),
(21, 'Bachelor of Science in Computer Science - Software Engineering'),
(21, 'Bachelor of Science in Information Technology - Network and Information Security'),
(21, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(22, 'Bachelor of Library and Information Science'),
(22, 'Bachelor of Science in Computer Science - Data Science'),
(22, 'Bachelor of Science in Computer Science - Software Engineering'),
(22, 'Bachelor of Science in Information Technology - Network and Information Security'),
(22, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(23, 'Bachelor of Library and Information Science'),
(23, 'Bachelor of Science in Computer Science - Data Science'),
(23, 'Bachelor of Science in Computer Science - Software Engineering'),
(23, 'Bachelor of Science in Information Technology - Network and Information Security'),
(23, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(24, 'Bachelor of Library and Information Science'),
(24, 'Bachelor of Science in Computer Science - Data Science'),
(24, 'Bachelor of Science in Computer Science - Software Engineering'),
(24, 'Bachelor of Science in Information Technology - Network and Information Security'),
(24, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(25, 'Bachelor of Library and Information Science'),
(25, 'Bachelor of Science in Computer Science - Data Science'),
(25, 'Bachelor of Science in Computer Science - Software Engineering'),
(25, 'Bachelor of Science in Information Technology - Network and Information Security'),
(25, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(26, 'Bachelor of Library and Information Science'),
(26, 'Bachelor of Science in Computer Science - Data Science'),
(26, 'Bachelor of Science in Computer Science - Software Engineering'),
(26, 'Bachelor of Science in Information Technology - Network and Information Security'),
(26, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(27, 'Bachelor of Library and Information Science'),
(27, 'Bachelor of Science in Computer Science - Data Science'),
(27, 'Bachelor of Science in Computer Science - Software Engineering'),
(27, 'Bachelor of Science in Information Technology - Network and Information Security'),
(27, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(28, 'Bachelor of Library and Information Science'),
(28, 'Bachelor of Science in Computer Science - Data Science'),
(28, 'Bachelor of Science in Computer Science - Software Engineering'),
(28, 'Bachelor of Science in Information Technology - Network and Information Security'),
(28, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(29, 'Bachelor of Library and Information Science'),
(29, 'Bachelor of Science in Computer Science - Data Science'),
(29, 'Bachelor of Science in Computer Science - Software Engineering'),
(29, 'Bachelor of Science in Information Technology - Network and Information Security'),
(29, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(30, 'Bachelor of Library and Information Science'),
(30, 'Bachelor of Science in Computer Science - Data Science'),
(30, 'Bachelor of Science in Computer Science - Software Engineering'),
(30, 'Bachelor of Science in Information Technology - Network and Information Security'),
(30, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(31, 'Bachelor of Library and Information Science'),
(31, 'Bachelor of Science in Computer Science - Data Science'),
(31, 'Bachelor of Science in Computer Science - Software Engineering'),
(31, 'Bachelor of Science in Information Technology - Network and Information Security'),
(31, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(32, 'Bachelor of Library and Information Science'),
(32, 'Bachelor of Science in Computer Science - Data Science'),
(32, 'Bachelor of Science in Computer Science - Software Engineering'),
(32, 'Bachelor of Science in Information Technology - Network and Information Security'),
(32, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(33, 'Bachelor of Library and Information Science'),
(33, 'Bachelor of Science in Computer Science - Data Science'),
(33, 'Bachelor of Science in Computer Science - Software Engineering'),
(33, 'Bachelor of Science in Information Technology - Network and Information Security'),
(33, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(34, 'Bachelor of Library and Information Science'),
(34, 'Bachelor of Science in Computer Science - Data Science'),
(34, 'Bachelor of Science in Computer Science - Software Engineering'),
(34, 'Bachelor of Science in Information Technology - Network and Information Security'),
(34, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(35, 'Bachelor of Library and Information Science'),
(35, 'Bachelor of Science in Computer Science - Data Science'),
(35, 'Bachelor of Science in Computer Science - Software Engineering'),
(35, 'Bachelor of Science in Information Technology - Network and Information Security'),
(35, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(36, 'Bachelor of Library and Information Science'),
(36, 'Bachelor of Science in Computer Science - Data Science'),
(36, 'Bachelor of Science in Computer Science - Software Engineering'),
(36, 'Bachelor of Science in Information Technology - Network and Information Security'),
(36, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(37, 'Bachelor of Library and Information Science'),
(37, 'Bachelor of Science in Computer Science - Data Science'),
(37, 'Bachelor of Science in Computer Science - Software Engineering'),
(37, 'Bachelor of Science in Information Technology - Network and Information Security'),
(37, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(38, 'Bachelor of Library and Information Science'),
(38, 'Bachelor of Science in Computer Science - Data Science'),
(38, 'Bachelor of Science in Computer Science - Software Engineering'),
(38, 'Bachelor of Science in Information Technology - Network and Information Security'),
(38, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(39, 'Bachelor of Library and Information Science'),
(39, 'Bachelor of Science in Computer Science - Data Science'),
(39, 'Bachelor of Science in Computer Science - Software Engineering'),
(39, 'Bachelor of Science in Information Technology - Network and Information Security'),
(39, 'Bachelor of Science in Information Technology - Web and Mobile Technology'),
(40, 'Bachelor of Science in Computer Science - Software Engineering');

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
('repro_6a0e305613767', 'running', 'Making sure every team can actually be scheduled…', 26, '2026-05-20 22:06:14', '2026-05-20 22:06:14'),
('repro_6a0e313e2d94d', 'running', 'Making sure every team can actually be scheduled…', 26, '2026-05-20 22:10:06', '2026-05-20 22:10:06'),
('repro_6a0e3ca5d26d0', 'error', 'MediScan has 0 valid slots: a compliant panel cannot be formed — fewer than 2 available same-program panelists for the primary internal seats.', NULL, '2026-05-20 22:58:45', '2026-05-20 22:58:46'),
('sched_1779314397492_4fz9nf', 'completed', 'Your schedule preview is ready and has no conflicts.', 100, '2026-05-20 21:59:57', '2026-05-20 22:00:01'),
('sched_1779314411714_78p7bo', 'running', 'Making sure every team can actually be scheduled…', 26, '2026-05-20 22:00:11', '2026-05-20 22:00:11'),
('sched_1779314413145_buol59', 'running', 'Making sure every team can actually be scheduled…', 26, '2026-05-20 22:00:13', '2026-05-20 22:00:13'),
('sched_1779314572447_yrthzn', 'error', 'All generated layout options failed final class/faculty/venue checks. MediScan: the room is already booked by DATASPHERE on 2026-05-25', NULL, '2026-05-20 22:02:52', '2026-05-20 22:02:56'),
('sched_1779314589808_uji1nx', 'completed', 'Your schedule preview is ready and has no conflicts.', 100, '2026-05-20 22:03:09', '2026-05-20 22:03:17'),
('sched_1779317953761_sd22wi', 'completed', 'Your schedule preview is ready and has no conflicts. 3 layout option(s): use the dropdown to compare.', 100, '2026-05-20 22:59:13', '2026-05-20 22:59:19'),
('sched_1779317972243_l22vfh', 'error', 'MediScan has 0 valid slots: a compliant panel cannot be formed — fewer than 2 available same-program panelists for the primary internal seats.', NULL, '2026-05-20 22:59:32', '2026-05-20 22:59:32'),
('sched_1779317978558_kcz39w', 'error', 'MediScan has 0 valid slots: a compliant panel cannot be formed — fewer than 2 available same-program panelists for the primary internal seats.', NULL, '2026-05-20 22:59:38', '2026-05-20 22:59:38'),
('sched_1779317979631_c7y8v5', 'error', 'MediScan has 0 valid slots: a compliant panel cannot be formed — fewer than 2 available same-program panelists for the primary internal seats.', NULL, '2026-05-20 22:59:39', '2026-05-20 22:59:39'),
('test_strict_run_6a0e3044975c8', 'error', 'Confirmation required for overwriting upcoming defenses', NULL, '2026-05-20 22:05:56', '2026-05-20 22:05:56');

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
(2, 'CS401', 486, 'active', NULL, 0, '2026-05-05 17:44:21'),
(3, 'IT401', 481, 'active', '2025-2026, 1st Semester', 0, '2026-05-15 07:06:37'),
(4, 'IT402', 673, 'active', '2025-2026, 1st Semester', 486, '2026-05-19 06:00:42');

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

--
-- Dumping data for table `specialization_pool`
--

INSERT INTO `specialization_pool` (`id`, `name`, `description`, `department`, `college`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Software Engineering', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 04:06:57', '2026-05-15 04:06:57'),
(2, 'Android Development', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 04:07:19', '2026-05-15 04:07:19'),
(3, 'Cybersecurity', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 04:09:00', '2026-05-15 04:09:00'),
(5, 'Machine Learning', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 04:09:34', '2026-05-15 04:09:34'),
(6, 'Data Science', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 04:09:50', '2026-05-15 04:09:50'),
(8, 'Game Development', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 04:10:35', '2026-05-15 04:10:35'),
(9, 'Blockchain', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 04:10:55', '2026-05-15 04:10:55'),
(10, 'Algorithms & Theory', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 04:11:29', '2026-05-15 04:11:29'),
(11, 'Computer Network and Infrastructure', '', '', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 08:55:59', '2026-05-15 08:55:59'),
(12, 'Graphic Design', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 08:56:41', '2026-05-15 08:56:41'),
(13, 'Multimedia Tool', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 08:57:36', '2026-05-15 08:57:36'),
(14, 'Web Technologies', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 09:00:35', '2026-05-15 09:00:35'),
(15, 'Software and System Development', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 09:01:36', '2026-05-15 09:08:17'),
(16, 'Agentic AI & Intelligent System', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 09:02:40', '2026-05-15 09:02:40'),
(17, 'UI/UX', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 09:05:05', '2026-05-15 09:05:05'),
(18, 'Cloud Engineering and DevSecOps', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 09:07:50', '2026-05-15 09:07:50'),
(19, 'Database Management / Administration', '', 'Computer Studies', 'College of Information Technology and Computer Science', 1, 0, '2026-05-15 09:08:54', '2026-05-15 09:08:54');

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
(1, 'HERBASCAN', '2026-05-05 14:52:34', 'CS2526-1-001', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev, Algorithms & Theory, Machine Learning, Software Engineering, Agentic AI & Intelligent System, Software and System Development', 'title_proposal', 1, NULL, NULL, NULL),
(3, 'KNOWWHERE', '2026-05-05 14:55:20', 'CS2526-1-003', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev, Android Development, Software Engineering, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(4, 'RECOLOR', '2026-05-05 14:58:57', 'CS2526-1-004', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev, Algorithms & Theory, Machine Learning, Agentic AI & Intelligent System', 'title_proposal', 0, NULL, NULL, NULL),
(5, 'SOLARI', '2026-05-05 15:00:45', 'CS2526-1-005', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev, Agentic AI & Intelligent System, Algorithms & Theory, UI/UX, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(6, 'NAVICAV', '2026-05-05 15:05:00', 'CS2526-1-006', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev, Machine Learning, Algorithms & Theory, Web Technologies, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(7, 'POSTRA', '2026-05-05 15:06:04', 'CS2526-1-007', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev, Data Science, Machine Learning, Agentic AI & Intelligent System, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(8, 'DRIVEED', '2026-05-05 15:09:15', 'CS2526-1-008', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Mobile Dev, Machine Learning, Software Engineering, Agentic AI & Intelligent System, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(9, 'DENGEGUARD', '2026-05-05 15:11:21', 'CS2526-1-009', 'Bachelor of Science in Computer Science - Software Engineering', 'Algorithms & Theory, Machine Learning, Software Engineering, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(10, 'SalinDugo', '2026-05-05 15:12:10', 'CS2526-1-010', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev, Algorithms & Theory, Machine Learning, Software Engineering, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(11, 'GAIA', '2026-05-05 15:12:29', 'CS2526-1-011', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev, Data Science, Software Engineering, Agentic AI & Intelligent System, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(12, 'PRIVACYGUARD', '2026-05-05 15:13:32', 'CS2526-1-012', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev, Cybersecurity, Algorithms & Theory, Machine Learning, Software Engineering, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(13, 'Smart Retail', '2026-05-15 07:07:37', 'IT2526-1-001', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Cloud Engineering and DevSecOps, Software and System Development, Database Management / Administration, Data Science, Web Technologies, UI/UX, Multimedia Tool', 'title_proposal', 0, NULL, NULL, NULL),
(14, 'GuardTrack', '2026-05-15 07:09:30', 'IT2526-1-002', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(15, 'MediChain', '2026-05-15 07:11:02', 'IT2526-1-003', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(16, 'BYTEFORGE', '2026-05-15 07:11:03', 'IT2526-1-004', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Cybersecurity, Agentic AI & Intelligent System, Software and System Development, Database Management / Administration, Computer Network and Infrastructure', 'title_proposal', 0, NULL, NULL, NULL),
(17, 'EcoRoute', '2026-05-15 07:12:33', 'IT2526-1-005', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software Engineering, Software and System Development, Web Technologies, Database Management / Administration', 'title_proposal', 0, NULL, NULL, NULL),
(18, 'CODENOVA', '2026-05-15 07:12:34', 'IT2526-1-006', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software and System Development, Database Management / Administration, Web Technologies', 'title_proposal', 0, NULL, NULL, NULL),
(19, 'NEXUSLAB', '2026-05-15 07:13:43', 'IT2526-1-007', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Android Development, Software and System Development, UI/UX, Multimedia Tool, Algorithms & Theory', 'title_proposal', 0, NULL, NULL, NULL),
(20, 'SkillMatch', '2026-05-15 07:13:49', 'IT2526-1-008', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Algorithms & Theory, Graphic Design, Multimedia Tool, UI/UX, Web Technologies, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(21, 'DATASPHERE', '2026-05-15 07:16:02', 'IT2526-1-009', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Machine Learning, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(22, 'CYBERNOVA', '2026-05-15 07:17:41', 'IT2526-1-010', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Machine Learning, Algorithms & Theory, Software and System Development, Computer Network and Infrastructure', 'title_proposal', 0, NULL, NULL, NULL),
(23, 'DefendNet', '2026-05-19 06:01:53', 'IT2526-1-011', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev, Machine Learning, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL),
(24, 'SmartAgri', '2026-05-19 06:03:28', 'IT2526-1-012', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software and System Development, Android Development, Algorithms & Theory, Agentic AI & Intelligent System, UI/UX, Web Technologies, Graphic Design', 'title_proposal', 0, NULL, NULL, NULL),
(25, 'MediScan', '2026-05-19 06:05:30', 'IT2526-1-013', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software and System Development, Machine Learning, Android Development', 'title_proposal', 0, NULL, NULL, NULL),
(26, 'TransitBuddy', '2026-05-19 06:06:42', 'IT2526-1-014', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software and System Development, Algorithms & Theory, Agentic AI & Intelligent System, Machine Learning, Web Technologies, UI/UX, Database Management / Administration, Android Development', 'title_proposal', 0, NULL, NULL, NULL),
(27, 'EduVerify', '2026-05-19 06:09:36', 'IT2526-1-015', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Blockchain, Cloud Engineering and DevSecOps, Cybersecurity, Software and System Development, Data Science', 'title_proposal', 0, NULL, NULL, NULL),
(28, 'CloudScale', '2026-05-19 06:18:43', 'IT2526-1-016', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software and System Development, Cloud Engineering and DevSecOps, Algorithms & Theory, Computer Network and Infrastructure', 'title_proposal', 0, NULL, NULL, NULL),
(29, 'EcoMap', '2026-05-19 06:24:33', 'IT2526-1-017', 'Bachelor of Science in Information Technology - Network and Information Security', 'Algorithms & Theory, Software and System Development, Web Technologies, Multimedia Tool, Graphic Design', 'title_proposal', 0, NULL, NULL, NULL),
(30, 'B2B-Link', '2026-05-19 06:26:30', 'IT2526-1-018', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Technologies, Software and System Development, Software Engineering, Database Management / Administration', 'title_proposal', 0, NULL, NULL, NULL),
(31, 'CodeQuest', '2026-05-19 06:28:36', 'IT2526-1-019', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Game Development, Software and System Development, Software Engineering, UI/UX, Graphic Design, Multimedia Tool', 'title_proposal', 0, NULL, NULL, NULL),
(32, 'CareSync', '2026-05-19 06:31:21', 'IT2526-1-020', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Database Management / Administration, Software Engineering, Software and System Development', 'title_proposal', 0, NULL, NULL, NULL);

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
(2, 1, 506, 'leader'),
(3, 1, 507, 'member'),
(4, 1, 508, 'member'),
(5, 1, 510, 'member'),
(6, 2, 480, 'adviser'),
(7, 2, 494, 'leader'),
(8, 2, 495, 'member'),
(9, 2, 497, 'member'),
(10, 2, 498, 'member'),
(12, 3, 512, 'leader'),
(13, 3, 514, 'member'),
(14, 3, 518, 'member'),
(15, 3, 521, 'member'),
(17, 4, 513, 'leader'),
(18, 4, 515, 'member'),
(19, 4, 517, 'member'),
(20, 4, 520, 'member'),
(22, 5, 500, 'leader'),
(23, 5, 501, 'member'),
(24, 5, 503, 'member'),
(25, 5, 505, 'member'),
(27, 1, 481, 'adviser'),
(32, 6, 523, 'leader'),
(33, 6, 525, 'member'),
(34, 6, 527, 'member'),
(36, 7, 531, 'leader'),
(37, 7, 536, 'member'),
(38, 7, 537, 'member'),
(40, 8, 494, 'leader'),
(41, 8, 495, 'member'),
(42, 8, 497, 'member'),
(43, 8, 498, 'member'),
(48, 9, 481, 'adviser'),
(49, 9, 502, 'leader'),
(50, 9, 504, 'member'),
(51, 9, 509, 'member'),
(52, 9, 511, 'member'),
(54, 10, 490, 'leader'),
(55, 10, 491, 'member'),
(56, 10, 492, 'member'),
(57, 10, 493, 'member'),
(59, 11, 516, 'leader'),
(60, 11, 519, 'member'),
(61, 11, 522, 'member'),
(62, 11, 524, 'member'),
(64, 12, 528, 'leader'),
(65, 12, 530, 'member'),
(66, 12, 533, 'member'),
(67, 12, 535, 'member'),
(68, 12, 485, 'adviser'),
(69, 10, 482, 'adviser'),
(70, 8, 480, 'adviser'),
(71, 7, 499, 'adviser'),
(72, 5, 488, 'adviser'),
(73, 4, 485, 'adviser'),
(74, 3, 480, 'adviser'),
(75, 11, 486, 'adviser'),
(76, 6, 486, 'adviser'),
(77, 13, 499, 'adviser'),
(78, 13, 716, 'leader'),
(79, 13, 715, 'member'),
(80, 13, 714, 'member'),
(81, 13, 713, 'member'),
(82, 14, 488, 'adviser'),
(83, 14, 712, 'leader'),
(84, 14, 711, 'member'),
(85, 14, 710, 'member'),
(86, 14, 709, 'member'),
(87, 15, 485, 'adviser'),
(88, 15, 708, 'leader'),
(89, 15, 707, 'member'),
(90, 15, 706, 'member'),
(91, 15, 705, 'member'),
(92, 16, 488, 'adviser'),
(93, 16, 696, 'leader'),
(94, 16, 695, 'member'),
(95, 16, 694, 'member'),
(96, 16, 693, 'member'),
(97, 17, 482, 'adviser'),
(98, 17, 704, 'leader'),
(99, 17, 703, 'member'),
(100, 17, 702, 'member'),
(101, 17, 701, 'member'),
(102, 18, 484, 'adviser'),
(103, 18, 692, 'leader'),
(104, 18, 691, 'member'),
(105, 18, 690, 'member'),
(106, 18, 689, 'member'),
(107, 19, 480, 'adviser'),
(108, 19, 687, 'leader'),
(109, 19, 686, 'member'),
(110, 19, 685, 'member'),
(111, 19, 684, 'member'),
(112, 20, 482, 'adviser'),
(113, 20, 700, 'leader'),
(114, 20, 699, 'member'),
(115, 20, 698, 'member'),
(116, 20, 697, 'member'),
(117, 21, 487, 'adviser'),
(118, 21, 683, 'leader'),
(119, 21, 682, 'member'),
(120, 21, 681, 'member'),
(121, 21, 680, 'member'),
(122, 22, 481, 'adviser'),
(123, 22, 679, 'leader'),
(124, 22, 678, 'member'),
(125, 22, 677, 'member'),
(126, 22, 688, 'member'),
(127, 23, 781, 'leader'),
(128, 23, 780, 'member'),
(129, 23, 779, 'member'),
(130, 23, 778, 'member'),
(131, 24, 777, 'leader'),
(132, 24, 776, 'member'),
(133, 24, 775, 'member'),
(134, 24, 774, 'member'),
(135, 25, 773, 'leader'),
(136, 25, 772, 'member'),
(137, 25, 771, 'member'),
(138, 25, 770, 'member'),
(139, 26, 769, 'leader'),
(140, 26, 768, 'member'),
(141, 26, 767, 'member'),
(142, 26, 766, 'member'),
(145, 24, 486, 'adviser'),
(146, 23, 673, 'adviser'),
(147, 27, 717, 'adviser'),
(148, 27, 765, 'leader'),
(149, 27, 764, 'member'),
(150, 27, 763, 'member'),
(151, 27, 762, 'member'),
(153, 28, 761, 'leader'),
(154, 28, 760, 'member'),
(155, 28, 759, 'member'),
(156, 28, 758, 'member'),
(157, 28, 499, 'adviser'),
(158, 29, 484, 'adviser'),
(159, 29, 757, 'leader'),
(160, 29, 756, 'member'),
(161, 29, 755, 'member'),
(162, 29, 754, 'member'),
(163, 30, 673, 'adviser'),
(164, 30, 753, 'leader'),
(165, 30, 752, 'member'),
(166, 30, 751, 'member'),
(167, 30, 750, 'member'),
(168, 31, 484, 'adviser'),
(169, 31, 749, 'leader'),
(170, 31, 748, 'member'),
(171, 31, 747, 'member'),
(172, 31, 746, 'member'),
(176, 32, 744, 'member'),
(177, 32, 743, 'member'),
(178, 32, 672, 'member'),
(180, 32, 745, 'leader'),
(181, 32, 718, 'adviser'),
(182, 25, 718, 'adviser'),
(183, 26, 487, 'adviser');

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
  `section` varchar(10) DEFAULT NULL,
  `is_external_panelist` tinyint(1) GENERATED ALWAYS AS (`usertype` = 2 and `is_external` = 1) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `usertype`, `username`, `program`, `area_of_expertise`, `is_parttime`, `email`, `password`, `first_name`, `middle_name`, `last_name`, `gender`, `headline`, `bio`, `profile_image`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`, `is_program_chair`, `is_external`, `year`, `section`) VALUES
(0, 0, 'Admin', NULL, '', 0, 'ton.agustin09@gmail.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Calamyty', 'Chaos', 'Mytryx', 'm', 'SUPER ADMIN', 'Greetings I\'m Calamytryx. Omen of disaster. Trapped in the Mytryx.', 'profile_69f9cfc42573f9.00926720.png', '2024-10-05 05:55:38', '2024-10-05 05:55:38', '2026-05-20 21:45:43', '0000-00-00 00:00:00', '2026-05-20 21:45:43', 0, 0, NULL, NULL),
(480, 2, 'elizabeth.nsubuga', '', 'Software and System Development', 0, 'elizabeth.nsubuga@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Elizabeth', 'NA', 'Nsubuga', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-20 03:59:31', NULL, '2026-05-20 03:59:31', NULL, 0, NULL, NULL),
(481, 2, 'raymund.constante', 'Bachelor of Science in Computer Science - Software Engineering', 'Software and System Development', 0, 'raymund.constante@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Raymund', 'NA', 'Constante', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-15 10:18:11', NULL, '2026-05-15 10:18:11', NULL, 0, NULL, NULL),
(482, 2, 'elmer.matel', 'Bachelor of Science in Computer Science - Software Engineering', 'Data Science, Agentic AI & Intelligent System, Cybersecurity, Graphic Design, Multimedia Tool, Software and System Development', 1, 'elmer.matel@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Elmer', 'NA', 'Matel', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-15 09:24:39', NULL, '2026-05-15 09:23:51', 0, 0, NULL, NULL),
(484, 2, 'earl.saavedra', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Blockchain, Data Science, Game Development, Software and System Development, UI/UX, Web Technologies', 0, 'earl.saavedra@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Earl', 'NA', 'Saavedra', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-20 03:59:10', NULL, '2026-05-20 03:57:59', 0, 1, NULL, NULL),
(485, 2, 'arcell.hadlocon', 'Bachelor of Science in Computer Science - Software Engineering', 'Cybersecurity, Software and System Development', 0, 'arcell.hadlocon@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Arcell', 'NA', 'Hadlocon', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-15 09:23:28', NULL, '2026-05-15 09:22:48', NULL, 0, NULL, NULL),
(486, 0, 'jerian.peren', 'Bachelor of Science in Computer Science - Software Engineering', 'Data Science, Database Management / Administration, Software and System Development', 0, 'jerian.peren@lpu.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jerian', 'NA', 'Peren', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-20 04:21:35', NULL, '2026-05-20 04:21:35', 0, 0, NULL, NULL),
(487, 2, 'jeff.nebran', 'Bachelor of Science in Computer Science - Software Engineering', 'Computer Network and Infrastructure, Cloud Engineering and DevSecOps, Database Management / Administration, Software and System Development', 1, 'jeff.nebran@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jeff', 'NA', 'Nebran', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-20 03:54:37', NULL, '2026-05-20 03:53:56', 0, 0, NULL, NULL),
(488, 2, 'sean.gono', 'Bachelor of Science in Computer Science - Software Engineering', 'Cybersecurity, Agentic AI & Intelligent System, Data Science, Graphic Design, Multimedia Tool, Software and System Development, UI/UX', 0, 'sean.gono@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sean Charslton', 'NA', 'Gono', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-15 09:22:23', NULL, '2026-05-15 09:21:14', NULL, 0, NULL, NULL),
(489, 2, 'roger.doctor', 'Bachelor of Science in Computer Science - Software Engineering', 'Algorithms & Theory, Android Development, Data Science, Software Engineering', 0, 'roger.doctor@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Roger Wyne', 'NA', 'Doctor', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-20 03:50:44', NULL, '2026-05-20 03:50:44', 0, 1, NULL, NULL),
(490, 1, 'jerome.villaganas', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'jerome.villaganas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jerome Christian', 'NA', 'Villaganas', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(491, 1, 'warren.abdon', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'warren.abdon@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Warren Jacob', 'NA', 'Abdon', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(492, 1, 'john.lingad', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'john.lingad@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Vincent', 'NA', 'Lingad', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(493, 1, 'nicole.fernandez', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'nicole.fernandez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Nicole Wyne', 'NA', 'Fernadez', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(494, 1, 'john.diel', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'john.diel@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Christian', 'NA', 'Diel', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 18:34:24', NULL, NULL, 0, 0, 4, 'IT401'),
(495, 1, 'wendell.luzano', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'wendell.luzano@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Wendell', 'NA', 'Luzano', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 18:34:30', NULL, NULL, 0, 0, 4, 'IT401'),
(496, 2, 'kathlene.pedero', 'Bachelor of Science in Information Technology - Network and Information Security', 'Algorithms & Theory, Multimedia Tool, Software and System Development, Software Engineering, UI/UX, Web Technologies', 0, 'kathlene.pedero@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kathlene', 'NA', 'Pedero', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-20 03:55:34', NULL, '2026-05-20 03:54:53', 0, 1, NULL, NULL),
(497, 1, 'angelica.quieng', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'angelica.quieng@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Angelica', 'NA', 'Quieng', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 18:34:38', NULL, NULL, 0, 0, 4, 'IT401'),
(498, 1, 'tiara.sacala', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'tiara.sacala@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Tiara Tanihata', 'NA', 'Sacala', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 18:34:42', NULL, NULL, 0, 0, 4, 'IT401'),
(499, 2, 'klarence.baptista', 'Bachelor of Science in Computer Science - Software Engineering', 'Computer Network and Infrastructure, Cybersecurity, Graphic Design, Multimedia Tool, Software and System Development', 0, 'klarence.baptista@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Klarence', 'NA', 'Baptista', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-20 03:48:55', NULL, '2026-05-20 03:48:55', 0, 0, NULL, NULL),
(500, 1, 'cj.rojo', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'cj.rojo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'CJ Vhert', 'NA', 'Rojo', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(501, 1, 'leila.manalo', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'leila.manalo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Leila Aliyah', 'NA', 'Manalo', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(502, 1, 'jaira.tafalla', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'jaira.tafalla@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jaira Mae', 'NA', 'Tafalla', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(503, 1, 'john.delacruz', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'john.delacruz@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Lloyd', 'NA', 'Dela Cruz', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(504, 1, 'sir.laudato', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'sir.laudato@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sir Lawrence', 'NA', 'Laudato', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(505, 1, 'patricia.mendoza', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'patricia.mendoza@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Patricia Nicole', 'NA', 'Mendoza', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(506, 1, 'berna.alhambra', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'berna.alhambra@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Berna Marie', 'NA', 'Alhambra', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'CS401'),
(507, 1, 'hans.bertoso', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'hans.bertoso@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Hans Kyle', 'NA', 'Bertoso', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'CS401'),
(508, 1, 'vince.espera', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'vince.espera@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Vince Wackie', 'NA', 'Espera', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'CS401'),
(509, 1, 'brent.rull', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'brent.rull@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Brent Harvey', 'NA', 'Rull', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(510, 1, 'mark.salazar', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'mark.salazar@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Mark Judiel', 'NA', 'Salazar', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'CS401'),
(511, 1, 'leenel.santos', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'leenel.santos@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Leenel Ioan', 'NA', 'Santos', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(512, 1, 'alyssa.abac', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'alyssa.abac@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Alyssa Mae', 'NA', 'Abac', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'CS401'),
(513, 1, 'mark.caparas', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'mark.caparas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Mark Risen', 'NA', 'Caparas', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(514, 1, 'alexander.asinas', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'alexander.asinas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Alexander', 'NA', 'Asinas', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(515, 1, 'charles.rull', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'charles.rull@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Charles Justine', 'NA', 'Rull', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(516, 1, 'ian.lumanog', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'ian.lumanog@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ian', 'NA', 'Lumanog', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(517, 1, 'johann.cepeda', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'johann.cepeda@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Johann Nikkolai', 'NA', 'Cepeda', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(518, 1, 'angelo.fabian', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'angelo.fabian@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Angelo Mark Xyz', 'NA', 'Fabian', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'CS401'),
(519, 1, 'alexis.rellon', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'alexis.rellon@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Alexis John', 'NA', 'Rellon', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(520, 1, 'stephen.lacsa', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'stephen.lacsa@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Stephen', 'NA', 'Lacsa', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(521, 1, 'brandon.miranda', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'brandon.miranda@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Brandon', 'NA', 'Miranda', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(522, 1, 'aaron.roxas', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'aaron.roxas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Aaron Joshua', 'NA', 'Roxas', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(523, 1, 'lance.romero', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'lance.romero@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Lance Christian', 'NA', 'Romero', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'CS401'),
(524, 1, 'beo.salguero', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'beo.salguero@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Beo Alvaro', 'NA', 'Salguero', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 3, 'CS401'),
(525, 1, 'romuel.borja', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'romuel.borja@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Romuel', 'NA', 'Borja', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(526, 1, 'winston.agustin', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'winston.agustin@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Winston', 'NA', 'Agustin', 'm', 'GUSTO KO NALANG MAMATAY', 'AYOKO NA SA THESIS NA ITO', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-15 18:00:28', NULL, NULL, 0, 0, 4, 'CS401'),
(527, 1, 'dyan.mercado', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'dyan.mercado@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Dyan Paul', 'NA', 'Mercado', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'CS401'),
(528, 1, 'jaermaine.domingcil', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'jaermaine.domingcil@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jaermaine Lester', 'NA', 'Domingcil', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(529, 1, 'neil.vicedo', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'neil.vicedo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Neil', 'NA', 'Vicedo', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-15 16:50:23', NULL, NULL, 0, 0, 4, 'CS301'),
(530, 1, 'franco.nicanor', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'franco.nicanor@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Franco Luis', 'NA', 'Nicanor', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(531, 1, 'king.page', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'king.page@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'King Edward', 'NA', 'Page', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'CS401'),
(532, 1, 'jerald.gerona', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'jerald.gerona@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jerald Ryan', 'NA', 'Gerona', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-15 17:17:11', NULL, NULL, 0, 0, 4, 'CS302'),
(533, 1, 'lorenzo.canales', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'lorenzo.canales@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Lorenzo', 'NA', 'Canales', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(534, 1, 'ivan.ilano', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'ivan.ilano@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ivan Kerwin', 'NA', 'Ilano', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-15 17:36:27', NULL, NULL, 0, 0, 4, 'CS401'),
(535, 1, 'resty.cruz', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'resty.cruz@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Resty Jean', 'NA', 'Cruz', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(536, 1, 'micah.sereno', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'micah.sereno@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Micah', 'NA', 'Sereno', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(537, 1, 'mielle.dulce', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'mielle.dulce@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Mielle Angelie', 'NA', 'Dulce', NULL, NULL, NULL, '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, NULL, 0, 4, 'CS401'),
(538, 1, 'alliah.budol', 'Bachelor of Science in Architecture (Arch)', '', 0, 'alliah.budol@lpunetwork.edu.oh', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Alliah', 'NA', 'Budol', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'ARCH401'),
(540, 1, 'john.michael', 'Bachelor of Science in Architecture (Arch)', '', 0, 'john.michael@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Michael', 'NA', 'Dela Cruz', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'ARCH401'),
(541, 1, 'joshua.deala', 'Bachelor of Science in Architecture (Arch)', '', 0, 'joshua.deala@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joshua Aldrin', 'NA', 'Deala', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(542, 1, 'eisen.bacatan', 'Bachelor of Science in Architecture (Arch)', '', 0, 'eisen.bacatan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Eisen', 'NA', 'Bacatan', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'ARCH401'),
(543, 1, 'tyron.rojas', 'Bachelor of Science in Architecture (Arch)', '', 0, 'tyron.rojas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Tyron Kier', 'NA', 'Rojas', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'ARCH401'),
(544, 1, 'shan.raflores', 'Bachelor of Science in Architecture (Arch)', '', 0, 'shan.raflores@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Shan Archer', 'NA', 'Raflores', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(545, 1, 'aphreal.mojica', 'Bachelor of Science in Architecture (Arch)', '', 0, 'aphreal.mojica@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Aphreal Cathlene', 'NA', 'Mojica', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'ARCH401'),
(546, 1, 'angelica.argente', 'Bachelor of Science in Architecture (Arch)', '', 0, 'angelica.argente@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Angelica', 'P.', 'Argente', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'ARCH401'),
(547, 1, 'lorraine.celebre', 'Bachelor of Science in Architecture (Arch)', '', 0, 'lorraine.celebre@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Lorraine Joyce', 'NA', 'Celebre', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(548, 1, 'lee.rint', 'Bachelor of Science in Architecture (Arch)', '', 0, 'lee.rint@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Lee Helen', 'NA', 'Rint', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(549, 1, 'jose.orque', 'Bachelor of Science in Architecture (Arch)', '', 0, 'jose.orque@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jose Connel', 'NA', 'Orque', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(550, 1, 'laverne.videa', 'Bachelor of Science in Architecture (Arch)', '', 0, 'laverne.videna@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Laverne Dex', 'NA', 'Videna', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(551, 1, 'christine.jimenez', 'Bachelor of Science in Architecture (Arch)', '', 0, 'christine.jimenez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Christine', 'NA', 'Jimenez', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'ARCH401'),
(552, 1, 'eydrianne.guerrero', 'Bachelor of Science in Architecture (Arch)', '', 0, 'eydrianne.guerrero@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Eydrianne', 'NA', 'Guerrero', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(553, 1, 'andreianna.casaul', 'Bachelor of Science in Architecture (Arch)', '', 0, 'andreianna.casaul@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Andreianna', 'NA', 'Casaul', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(554, 1, 'samantha.centeno', 'Bachelor of Science in Architecture (Arch)', '', 0, 'samantha.centeno@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Samantha', 'NA', 'Centeno', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(555, 1, 'erika.cruz', 'Bachelor of Science in Architecture (Arch)', '', 0, 'erika.cruz@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Erika Roschel', 'NA', 'Cruz', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(556, 1, 'john.perillo', 'Bachelor of Science in Architecture (Arch)', '', 0, 'john.perillo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Denbert', 'NA', 'Perillo', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(557, 1, 'erika.entac', 'Bachelor of Science in Architecture (Arch)', '', 0, 'erika.entac@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Erika', 'NA', 'Entac', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(558, 1, 'jazmine.tillman', 'Bachelor of Science in Architecture (Arch)', '', 0, 'jazmine.tillman@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jazmine Lynette', 'NA', 'Tillman', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(559, 1, 'luisa.barcelon', 'Bachelor of Science in Architecture (Arch)', '', 0, 'luisa.barcelon@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Luisa', 'NA', 'Barcelon', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(560, 1, 'alyssa.kiamzon', 'Bachelor of Science in Architecture (Arch)', '', 0, 'alyssa.kiamzon@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Alyssa Margaret', 'NA', 'Kiamzon', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(561, 1, 'alliah.ala', 'Bachelor of Science in Architecture (Arch)', '', 0, 'alliah.ala@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Alliah Ghaile', 'NA', 'Ala', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(562, 1, 'demi.alegre', 'Bachelor of Science in Architecture (Arch)', '', 0, 'demi.alegre@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Demi Keith', 'NA', 'Alegre', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(563, 1, 'angelique.cueto', 'Bachelor of Science in Architecture (Arch)', '', 0, 'angelique.cueto@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Angelique', 'NA', 'Cueto', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(564, 1, 'hannah.esmani', 'Bachelor of Science in Architecture (Arch)', '', 0, 'hannah.esmani@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Hannah Gracelyn', 'NA', 'Esmani', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(565, 1, 'ronaline.llaguno', 'Bachelor of Science in Architecture (Arch)', '', 0, 'ronaline.llaguno@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ronaline', 'NA', 'Llaguno', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(566, 1, 'kim.diesta', 'Bachelor of Science in Architecture (Arch)', '', 0, 'kim.diesta@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kim Harvey', 'NA', 'Diesta', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(567, 1, 'rovic.delacruz', 'Bachelor of Science in Architecture (Arch)', '', 0, 'rovic.delacruz@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Rovic', 'NA', 'Dela Cruz', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(568, 1, 'kate.tirol', 'Bachelor of Science in Architecture (Arch)', '', 0, 'kate.tirol@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kate', 'NA', 'Tirol', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(569, 1, 'christine.madrona', 'Bachelor of Science in Architecture (Arch)', '', 0, 'christine.madrona@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Christine', 'NA', 'Madrona', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ARCH401'),
(570, 1, 'andrei.castro', 'Bachelor of Science in Computer Engineering', '', 0, 'andrei.castro@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Andrei', 'NA', 'Castro', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(571, 1, 'sheila.beler', 'Bachelor of Science in Computer Engineering', '', 0, 'sheila.beler@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sheila Mae', 'NA', 'Beler', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(572, 1, 'june.padrid', 'Bachelor of Science in Computer Engineering', '', 0, 'june.padrid@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'June Alfred', 'NA', 'Padrid', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(573, 1, 'ryzamae.saracho', 'Bachelor of Science in Computer Engineering', '', 0, 'ryzamae.saracho@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ryzamae Mariel', 'NA', 'Saracho', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(574, 1, 'miguel.aborque', 'Bachelor of Science in Computer Engineering', '', 0, 'miguel.aborque@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Miguel Angelo', 'NA', 'Aborque', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(575, 1, 'catherine.balane', 'Bachelor of Science in Computer Engineering', '', 0, 'catherine.balane@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Catherine Joy', 'NA', 'Balane', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(576, 1, 'rafael.medina', 'Bachelor of Science in Computer Engineering', '', 0, 'rafael.medina@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Rafael', 'NA', 'Medina', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(577, 1, 'heherson.aledia', 'Bachelor of Science in Computer Engineering', '', 0, 'heherson.aledia@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Heherson', 'NA', 'Aledia', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(578, 1, 'franco.desantos', 'Bachelor of Science in Computer Engineering', '', 0, 'franco.desantos@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Franco Yves', 'NA', 'De Santos', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(579, 1, 'earlvin.eustacio', 'Bachelor of Science in Computer Engineering', '', 0, 'earlvin.eustacio@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Earlvin', 'NA', 'Eustacio', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(580, 1, 'pauline.querido', 'Bachelor of Science in Computer Engineering', '', 0, 'pauline.querido@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Pauline', 'NA', 'Querido', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(581, 1, 'christian.nacionales', 'Bachelor of Science in Computer Engineering', '', 0, 'christian.nacionales@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Christian Joyce', 'NA', 'Nacionales', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(582, 1, 'ehldren.alag', 'Bachelor of Science in Computer Engineering', '', 0, 'ehldren.alag@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ehldren Franc', 'NA', 'Alag', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(583, 1, 'derick.barbon', 'Bachelor of Science in Computer Engineering', '', 0, 'derick.barbon@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Derick', 'NA', 'Barbon', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(584, 1, 'lee.bolo', 'Bachelor of Science in Computer Engineering', '', 0, 'lee.bolo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Lee Shauran', 'NA', 'Bolo', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(585, 1, 'juan.decastro', 'Bachelor of Science in Computer Engineering', '', 0, 'juan.decastro@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Juan Carlos', 'NA', 'De Castro', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(586, 1, 'john.luistro', 'Bachelor of Science in Computer Engineering', '', 0, 'john.luistro@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Mark', 'NA', 'Luistro', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(587, 1, 'gadwyn.ducut', 'Bachelor of Science in Computer Engineering', '', 0, 'gadwyn.ducut@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Gadwyn Howard', 'NA', 'Ducut', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(588, 1, 'cris.adap', 'Bachelor of Science in Computer Engineering', '', 0, 'cris.adap@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Cris Gilson', 'NA', 'Adap', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(589, 1, 'renz.sambillo', 'Bachelor of Science in Computer Engineering', '', 0, 'renz.sambillo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Renz Edward', 'NA', 'Sambillo', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(590, 1, 'adrux.casaul', 'Bachelor of Science in Computer Engineering', '', 0, 'adrux.casaul@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Adrux Lanford', 'NA', 'Casaul', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(591, 1, 'jeffrey.paderanga', 'Bachelor of Science in Computer Engineering', '', 0, 'jeffrey.paderanga@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jeffrey', 'NA', 'Paderanga', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(592, 1, 'jonas.tating', 'Bachelor of Science in Computer Engineering', '', 0, 'jonas.tating@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jonas Austin', 'NA', 'Tating', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(593, 1, 'kevin.garcia', 'Bachelor of Science in Computer Engineering', '', 0, 'kevin.garcia@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kevin Ryan Pante', 'NA', 'Garcia', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(594, 1, 'charlon.perey', 'Bachelor of Science in Computer Engineering', '', 0, 'charlon.perey@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Charlon Louis', 'NA', 'Perey', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(595, 1, 'jordan.cruz', 'Bachelor of Science in Computer Engineering', '', 0, 'jordan.cruz@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jordan Miguel', 'NA', 'Cruz', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(596, 1, 'seigbren.marquez', 'Bachelor of Science in Computer Engineering', '', 0, 'seigbren.marquez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Seigbren Jon', 'NA', 'Marquez', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(597, 1, 'fritz.fabellar', 'Bachelor of Science in Computer Engineering', '', 0, 'fritz.fabellar@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Fritz Fort', 'NA', 'Fabellar', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(598, 1, 'angelo.antimo', 'Bachelor of Science in Computer Engineering', '', 0, 'angelo.antimo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Angelo', 'NA', 'Antimo', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(599, 1, 'dave.cambarijan', 'Bachelor of Science in Computer Engineering', '', 0, 'dave.cambarijan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Dave', 'NA', 'Cambarijan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(600, 1, 'sherwin.penoliar', 'Bachelor of Science in Computer Engineering', '', 0, 'sherwin.penoliar@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sherwin Dilag', 'NA', 'Penoliar', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(601, 1, 'jet.masunsong', 'Bachelor of Science in Computer Engineering', '', 0, 'jet.masunsong@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jet Mark', 'NA', 'Masunsong', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(602, 1, 'daneil.catamio', 'Bachelor of Science in Computer Engineering', '', 0, 'daneil.catamio@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Daneil John', 'NA', 'Catamio', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(603, 1, 'john.chua', 'Bachelor of Science in Computer Engineering', '', 0, 'john.chua@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Andrei', 'NA', 'Chua', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(604, 1, 'carl.bartolome', 'Bachelor of Science in Computer Engineering', '', 0, 'carl.bartolome@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Carl Jeremiah', 'NA', 'Bartolome', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(605, 1, 'charles.chavez', 'Bachelor of Science in Computer Engineering', '', 0, 'charles.chavez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Charles Joseph Benoyo', 'NA', 'Chavez', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(606, 1, 'lex.reyes', 'Bachelor of Science in Computer Engineering', '', 0, 'lex.reyes@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Lex Danielle De Torres', 'NA', 'Reyes', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'CPE401'),
(607, 1, 'shon.dequito', 'Bachelor of Science in Electronics Engineering', '', 0, 'shon.dequito@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Shon Kelly', 'NA', 'Dequito', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ECE401'),
(608, 1, 'mary.aviso', 'Bachelor of Science in Electronics Engineering', '', 0, 'mary.aviso@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Mary Paulin', 'NA', 'Aviso', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ECE401'),
(609, 1, 'audrey.reyes', 'Bachelor of Science in Electronics Engineering', '', 0, 'audrey.reyes@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Audrey Nicole', 'NA', 'Reyes', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ECE401'),
(610, 1, 'revin.damot', 'Bachelor of Science in Electronics Engineering', '', 0, 'revin.damot@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Revin Ashley', 'NA', 'Damot', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ECE401'),
(611, 1, 'kirk.hernandez', 'Bachelor of Science in Electronics Engineering', '', 0, 'kirk.hernandez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kirk Ira', 'NA', 'Hernandez', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ECE401'),
(612, 1, 'hadji.montealegre', 'Bachelor of Science in Electronics Engineering', '', 0, 'hadji.montealegre@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Hadji Luis', 'NA', 'Montealegre', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ECE401'),
(613, 1, 'joshua.contreras', 'Bachelor of Science in Electronics Engineering', '', 0, 'joshua.contreras@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joshua Lawrence', 'NA', 'Contreras', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ECE401'),
(614, 1, 'francis.yaeso', 'Bachelor of Science in Electronics Engineering', '', 0, 'francis.yaeso@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Francis', 'NA', 'Yaeso', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ECE401'),
(615, 1, 'kenneth.aquino', 'Bachelor of Science in Electronics Engineering', '', 0, 'kenneth.aquino@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kenneth', 'NA', 'Aquino', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ECE401'),
(616, 1, 'trisha.baladad', 'Bachelor of Science in Electrical Engineering', '', 0, 'trisha.baladad@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Trisha Kieth', 'NA', 'Baladad', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(617, 1, 'mark.cayas', 'Bachelor of Science in Electrical Engineering', '', 0, 'mark.cayas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Mark Angelo', 'NA', 'Cayas', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(618, 1, 'joyceanne.rosales', 'Bachelor of Science in Electrical Engineering', '', 0, 'joyceanne.rosales@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joyceanne', 'NA', 'Rosales', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(619, 1, 'miguel.sierras', 'Bachelor of Science in Electrical Engineering', '', 0, 'miguel.sierras@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Miguel Alexander', 'NA', 'Sierras', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(620, 1, 'jim.astorga', 'Bachelor of Science in Electrical Engineering', '', 0, 'jim.astorga@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jim Nino', 'NA', 'Astorga', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(621, 1, 'elijah.gonzales', 'Bachelor of Science in Electrical Engineering', '', 0, 'elijah.gonzales@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Elijah Matthew', 'NA', 'Gonzales', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(622, 1, 'kurt.santos', 'Bachelor of Science in Electrical Engineering', '', 0, 'kurt.santos@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kurt Russel', 'NA', 'Santos', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(623, 1, 'ian.mabulac', 'Bachelor of Science in Electrical Engineering', '', 0, 'ian.mabulac@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ian Joshua', 'NA', 'Mabulac', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(624, 1, 'kenneth.peji', 'Bachelor of Science in Electrical Engineering', '', 0, 'kenneth.peji@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kenneth Brian', 'NA', 'Peji', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(625, 1, 'john.tarrago', 'Bachelor of Science in Electrical Engineering', '', 0, 'john.tarrago@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Raven', 'NA', 'Tarrago', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(626, 1, 'rence.apelado', 'Bachelor of Science in Electrical Engineering', '', 0, 'rence.apelado@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Rence Lian Kyle', 'NA', 'Apelado', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401'),
(627, 1, 'cristhoper.borromeo', 'Bachelor of Science in Electrical Engineering', '', 0, 'cristhoper.borromeo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Cristhoper Ray Biglang-Awa', 'NA', 'Borromeo', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'EE401');
INSERT INTO `users` (`id`, `usertype`, `username`, `program`, `area_of_expertise`, `is_parttime`, `email`, `password`, `first_name`, `middle_name`, `last_name`, `gender`, `headline`, `bio`, `profile_image`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`, `is_program_chair`, `is_external`, `year`, `section`) VALUES
(628, 1, 'sean.dizon', 'Bachelor of Science in Industrial Engineering', '', 0, 'sean.dizon@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sean Deniel', 'NA', 'Dizon', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(629, 1, 'martin.ili', 'Bachelor of Science in Industrial Engineering', '', 0, 'martin.ili@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Martin', 'NA', 'Ili', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(630, 1, 'james.manila', 'Bachelor of Science in Industrial Engineering', '', 0, 'james.manila@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'James Andrei', 'NA', 'Manila', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(631, 1, 'bianca.catalla', 'Bachelor of Science in Industrial Engineering', '', 0, 'bianca.catalla@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Bianca Mae', 'NA', 'Catalla', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(632, 1, 'charlie.puikera', 'Bachelor of Science in Industrial Engineering', '', 0, 'charlie.puikera@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Charlie', 'NA', 'Puikera', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(633, 1, 'marc.valladores', 'Bachelor of Science in Industrial Engineering', '', 0, 'marc.valladores@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Marc Daryl', 'NA', 'Valladores', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(634, 1, 'lyn.cupino', 'Bachelor of Science in Industrial Engineering', '', 0, 'lyn.cupino@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Lyn Mae', 'NA', 'Cupino', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(635, 1, 'april.ilagan', 'Bachelor of Science in Industrial Engineering', '', 0, 'april.ilagan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'April Joy', 'NA', 'Ilagan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(636, 1, 'fatima.penano', 'Bachelor of Science in Industrial Engineering', '', 0, 'fatima.penano@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Fatima Con', 'NA', 'Penano', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(637, 1, 'andrea.francisco', 'Bachelor of Science in Industrial Engineering', '', 0, 'andrea.francisco@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Andrea Jane', 'NA', 'Francisco', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(638, 1, 'malcolm.talau', 'Bachelor of Science in Industrial Engineering', '', 0, 'malcolm.talau@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Malcolm', 'NA', 'Talau', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(639, 1, 'earl.ambat', 'Bachelor of Science in Industrial Engineering', '', 0, 'earl.ambat@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Earl John', 'NA', 'Ambat', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(640, 1, 'charles.dilan', 'Bachelor of Science in Industrial Engineering', '', 0, 'charles.dilan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Charles Glenn', 'NA', 'Dilan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(641, 1, 'princess.bonostro', 'Bachelor of Science in Industrial Engineering', '', 0, 'princess.bonostro@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Princess', 'NA', 'Bonostro', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(642, 1, 'errol.cunanan', 'Bachelor of Science in Industrial Engineering', '', 0, 'errol.cunanan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Errol', 'NA', 'Cunanan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(643, 1, 'norielle.rellores', 'Bachelor of Science in Industrial Engineering', '', 0, 'norielle.rellores@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Norielle', 'NA', 'Rellores', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(644, 1, 'james.inocando', 'Bachelor of Science in Industrial Engineering', '', 0, 'james.inocando@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'James Andrei', 'NA', 'Inocando', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(645, 1, 'john.mariano', 'Bachelor of Science in Industrial Engineering', '', 0, 'john.mariano@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Paul', 'NA', 'Mariano', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(646, 1, 'ace.bayot', 'Bachelor of Science in Industrial Engineering', '', 0, 'ace.bayot@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ace Allen', 'NA', 'Bayot', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(647, 1, 'john.hernandez', 'Bachelor of Science in Industrial Engineering', '', 0, 'john.hernandez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Wayne', 'NA', 'Hernandez', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(648, 1, 'jules.gonzales', 'Bachelor of Science in Industrial Engineering', '', 0, 'jules.gonzales@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jules Rafael', 'NA', 'Gonzales', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(649, 1, 'paul.gaudiano', 'Bachelor of Science in Industrial Engineering', '', 0, 'paul.gaudiano@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Paul Kent', 'NA', 'Gaudiano', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(650, 1, 'marc.caranatan', 'Bachelor of Science in Industrial Engineering', '', 0, 'marc.caranatan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Marc Edrei', 'NA', 'Gaudiano', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(651, 1, 'giovani.sonbise', 'Bachelor of Science in Industrial Engineering', '', 0, 'giovani.sonbise@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Giovani', 'NA', 'Sonbise', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(652, 1, 'john.castillo', 'Bachelor of Science in Industrial Engineering', '', 0, 'john.castillo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Bernard', 'NA', 'Castillo', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(653, 1, 'jovan.perez', 'Bachelor of Science in Industrial Engineering', '', 0, 'jovan.perez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jovan Christian', 'NA', 'Perez', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'IE401'),
(655, 1, 'caulenne.bautista', 'Bachelor of Library and Information Science', '', 0, 'caulenne.bautista@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Caulenne', 'NA', 'Bautista', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'LIS401'),
(656, 1, 'raoul.casado', 'Bachelor of Science in Mechanical Engineering', '', 0, 'raoul.casado@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Raoul Martin', 'NA', 'Casado', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(657, 1, 'king.roldan', 'Bachelor of Science in Mechanical Engineering', '', 0, 'king.roldan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'King Christian', 'NA', 'Roldan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(658, 1, 'marionito.sereno', 'Bachelor of Science in Mechanical Engineering', '', 0, 'marionito.sereno@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Marionito', 'NA', 'Sereno Jr.', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(659, 1, 'kian.babadilla', 'Bachelor of Science in Mechanical Engineering', '', 0, 'kian.babadilla@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kian Andrei', 'NA', 'Babadilla', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(660, 1, 'rheden.calzado', 'Bachelor of Science in Mechanical Engineering', '', 0, 'rheden.calzado@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Rheden', 'NA', 'Calzado', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(661, 1, 'emmanuel.custan', 'Bachelor of Science in Mechanical Engineering', '', 0, 'emmanuel.custan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Emmanuel', 'NA', 'Custan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(662, 1, 'adam.delacruz', 'Bachelor of Science in Mechanical Engineering', '', 0, 'adam.delacruz@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Adam Christian', 'NA', 'Dela Cruz', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(663, 1, 'gener.extremadura', 'Bachelor of Science in Mechanical Engineering', '', 0, 'gener.extremadura@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Gener', 'NA', 'Extremadura', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(664, 1, 'arthur.regencia', 'Bachelor of Science in Mechanical Engineering', '', 0, 'arthur.regencia@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Arthur Manuel', 'NA', 'Regencia', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(665, 1, 'ryan.umayam', 'Bachelor of Science in Mechanical Engineering', '', 0, 'ryan.umayam@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ryan', 'NA', 'Umayam', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(666, 1, 'daryl.camacho', 'Bachelor of Science in Mechanical Engineering', '', 0, 'daryl.camacho@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Daryl John', 'NA', 'Camacho', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(667, 1, 'aaron.toledo', 'Bachelor of Science in Mechanical Engineering', '', 0, 'aaron.toledo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Aaron Jazper', 'NA', 'Toledo', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(668, 1, 'jio.tuliao', 'Bachelor of Science in Mechanical Engineering', '', 0, 'jio.tuliao@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jio', 'NA', 'Tuliao', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'ME401'),
(669, 1, 'jacob.lopez', 'Bachelor of Science in Mechanical Engineering', '', 0, 'jacob.lopez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jacob', 'NA', 'Lopez', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(670, 1, 'roman.tumamao', 'Bachelor of Science in Mechanical Engineering', '', 0, 'roman.tumamao@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Roman', 'NA', 'Tumamao', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(671, 1, 'neil.melgar', 'Bachelor of Science in Mechanical Engineering', '', 0, 'neil.melgar@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Neil Raizon', 'NA', 'Melgar', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, 4, 'ME401'),
(672, 1, 'tony.langkaan', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'tony.langkaan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Tony', 'NA', 'Langkaan', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(673, 0, 'alyssa.pocaan', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software and System Development', 0, 'alyssa.pocaan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Alyssa', 'NA', 'Pocaan', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-20 07:55:11', NULL, '2026-05-20 07:55:11', 0, 0, NULL, NULL),
(674, 2, 'amanda.menta', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Computer Network and Infrastructure, Cloud Engineering and DevSecOps, Graphic Design, Multimedia Tool, Software and System Development', NULL, 'amanda.menta@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Amanda', 'NA', 'Menta', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-20 18:04:39', NULL, '2026-05-15 09:28:51', 0, 0, NULL, NULL),
(677, 1, 'john.cruz', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'john.cruz@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John', 'Santos', 'Cruz', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-19 05:40:08', NULL, NULL, 0, 0, 4, 'IT401'),
(678, 1, 'mary.garcia', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'mary.garcia@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Mary', 'Reyes', 'Garcia', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(679, 1, 'mark.mendoza', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'mark.mendoza@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Mark', 'Cruz', 'Mendoza', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-19 05:40:28', NULL, NULL, 0, 0, 4, 'IT401'),
(680, 1, 'maria.reyes', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'maria.reyes@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Maria', 'Bautista', 'Reyes', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(681, 1, 'james.ramos', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'james.ramos@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'James', 'Ocampo', 'Ramos', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(682, 1, 'joseph.flores', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'joseph.flores@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joseph', 'Garcia', 'Flores', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(683, 1, 'michael.santos', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'michael.santos@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Michael', 'Mendoza', 'Santos', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(684, 1, 'sarah.villanueva', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'sarah.villanueva@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sarah', 'Aquino', 'Villanueva', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(685, 1, 'jessica.bautista', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'jessica.bautista@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jessica', 'Villanueva', 'Bautista', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(686, 1, 'david.aquino', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'david.aquino@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'David', 'Ramos', 'Aquino', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(687, 1, 'anna.cruz', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'anna.cruz@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Anna', 'Castro', 'Cruz', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(688, 1, 'paul.ocampo', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'paul.ocampo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Paul', 'Rivera', 'Ocampo', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(689, 1, 'christopher.rivera', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'christopher.rivera@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Christopher', 'Navarro', 'Rivera', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(690, 1, 'elizabeth.castro', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'elizabeth.castro@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Elizabeth', 'Dizon', 'Castro', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(691, 1, 'daniel.dizon', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'daniel.dizon@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Daniel', 'Tolentino', 'Dizon', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(692, 1, 'matthew.tolentino', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'matthew.tolentino@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Matthew', 'Mercado', 'Tolentino', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(693, 1, 'anthony.mercado', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'anthony.mercado@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Anthony', 'Pineda', 'Mercado', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(694, 1, 'joshua.pineda', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'joshua.pineda@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joshua', 'Aguilar', 'Pineda', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(695, 1, 'andrew.aguilar', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'andrew.aguilar@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Andrew', 'Suarez', 'Aguilar', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(696, 1, 'kevin.suarez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'kevin.suarez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kevin', 'Gonzales', 'Suarez', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(697, 1, 'ryan.gonzales', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'ryan.gonzales@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ryan', 'Soriano', 'Gonzales', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(698, 1, 'jason.soriano', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'jason.soriano@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jason', 'Roxas', 'Soriano', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(699, 1, 'eric.roxas', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'eric.roxas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Eric', 'Silva', 'Roxas', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(700, 1, 'jonathan.silva', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'jonathan.silva@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jonathan', 'Fernandez', 'Silva', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(701, 1, 'justin.fernandez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'justin.fernandez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Justin', 'Lopez', 'Fernandez', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(702, 1, 'brian.lopez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'brian.lopez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Brian', 'Perez', 'Lopez', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(703, 1, 'william.perez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'william.perez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'William', 'Gomez', 'Perez', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(704, 1, 'george.gomez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'george.gomez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'George', 'Sanchez', 'Gomez', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(705, 1, 'charles.sanchez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'charles.sanchez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Charles', 'Diaz', 'Sanchez', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(706, 1, 'thomas.diaz', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'thomas.diaz@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Thomas', 'Torres', 'Diaz', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(707, 1, 'christian.torres', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'christian.torres@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Christian', 'Ruiz', 'Torres', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(708, 1, 'arthur.ruiz', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'arthur.ruiz@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Arthur', 'Alvarez', 'Ruiz', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(709, 1, 'richard.alvarez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'richard.alvarez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Richard', 'Romero', 'Alvarez', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(710, 1, 'edward.romero', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'edward.romero@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Edward', 'Herrera', 'Romero', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(711, 1, 'grace.herrera', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'grace.herrera@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Grace', 'Medina', 'Herrera', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(712, 1, 'emily.medina', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'emily.medina@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Emily', 'Flores', 'Medina', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(713, 1, 'chloe.castillo', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'chloe.castillo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Chloe', 'Vargas', 'Castillo', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(714, 1, 'samantha.morales', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'samantha.morales@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Samantha', 'Castillo', 'Morales', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(715, 1, 'isabella.cortez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'isabella.cortez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Isabella', 'Morales', 'Cortez', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(716, 1, 'mia.trinidad', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'mia.trinidad@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Mia', 'Cortez', 'Trinidad', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-05 14:43:40', NULL, NULL, 0, 0, 4, 'IT401'),
(717, 2, 'joven.cajigas', 'Bachelor of Science in Computer Science - Software Engineering', 'Graphic Design, Multimedia Tool, Web Technologies, Software and System Development', 1, 'joven.cajigas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joven', 'NA', 'Cajigas', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-20 03:49:18', NULL, '2026-05-20 03:49:18', NULL, 0, NULL, NULL),
(718, 2, 'genson.mendoza', 'Bachelor of Science in Information Technology - Network and Information Security', 'Computer Network and Infrastructure, Graphic Design, Multimedia Tool, Software and System Development', 1, 'genson.mendoza@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Genson', 'NA', 'Mendoza', NULL, '', '', '_defaultUser.png', '2026-05-05 14:43:40', NULL, '2026-05-15 09:25:17', NULL, '2026-05-15 09:24:51', NULL, 0, NULL, NULL),
(721, 2, 'leanjoy.rebusa', 'Bachelor of Science in Architecture (Arch)', 'Software Engineering', 0, 'leanjoy.rebusa@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Leanjoy', 'NA', 'Rebusa', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(722, 2, 'juanito.sy', 'Bachelor of Science in Architecture (Arch)', 'Software Engineering', 0, 'juanito.sy@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Juanito', 'NA', 'Sy', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(723, 2, 'martin.malinawan', 'Bachelor of Science in Architecture (Arch)', 'Software Engineering', 0, 'martin.malinawan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Martin', 'NA', 'Malinawan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(724, 2, 'drexler.sibal', 'Bachelor of Science in Architecture (Arch)', 'Software Engineering', 0, 'drexler.sibal@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Drexler', 'NA', 'Sibal', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(725, 2, 'franz.navarro', 'Bachelor of Science in Architecture (Arch)', 'Software Engineering', 0, 'franz.navarro@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Franz Edrick', 'NA', 'Navarro', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(726, 2, 'aldrin.mamaril', 'Bachelor of Science in Architecture (Arch)', 'Software Engineering', 0, 'aldrin.mamaril@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Aldrin Ron', 'NA', 'Mamaril', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(727, 2, 'arjee.jimenez', 'Bachelor of Science in Architecture (Arch)', 'Software Engineering', 0, 'arjee.jimenez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Arjee Louie', 'NA', 'Jimenez', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(728, 2, 'jahnelle.dejose', 'Bachelor of Science in Architecture (Arch)', 'Software Engineering', 0, 'jahnelle.dejose@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jahnelle', 'NA', 'De Jose', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(729, 2, 'anjerick.topacio', 'Bachelor of Science in Architecture (Arch)', 'Software Engineering', 0, 'anjerick.topacio@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Anjerick', 'NA', 'Topacio', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(730, 2, 'toni.granado', 'Bachelor of Science in Computer Engineering', 'Software Engineering', 0, 'toni.granado@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Toni', 'NA', 'Granado', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(731, 2, 'rhia.espanto', 'Bachelor of Science in Computer Engineering', 'Software Engineering', 0, 'rhia.espanto@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Rhia', 'NA', 'Espanto', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(732, 2, 'leah.santos', 'Bachelor of Science in Electronics Engineering', 'Software Engineering', 0, 'leah.santos@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Leah', 'NA', 'Santos', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(733, 2, 'arnel.avelino', 'Bachelor of Science in Electronics Engineering', 'Software Engineering', 0, 'arnel.avelino@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Arnel', 'NA', 'Avelino', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(734, 2, 'joshua.ancheta', 'Bachelor of Science in Electronics Engineering', 'Software Engineering', 0, 'joshua.ancheta@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joshua', 'NA', 'Ancheta', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(735, 2, 'delia.fainsan', 'Bachelor of Science in Electronics Engineering', 'Software Engineering', 0, 'delia.fainsan@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Delia', 'NA', 'Fainsan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(736, 2, 'alex.pabayo', 'Bachelor of Science in Electronics Engineering', 'Software Engineering', 0, 'alex.pabayo@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Alex', 'NA', 'Pabayo', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(737, 2, 'denwhilrex.garcia', 'Bachelor of Science in Electronics Engineering', 'Software Engineering', 0, 'denwhilrex.garcia@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Den Whilrex', 'NA', 'Garcia', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(738, 2, 'rex.penas', 'Bachelor of Science in Electrical Engineering', 'Software Engineering', 0, 'rex.penas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Rex', 'NA', 'Penas', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(740, 2, 'analyn.romero', 'Bachelor of Science in Electrical Engineering', 'Software Engineering', 0, 'analyn.romero@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Analyn', 'NA', 'Romero', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, NULL, 0, NULL, NULL),
(741, 2, 'sherryann.rodenas', 'Bachelor of Library and Information Science', 'Agentic AI & Intelligent System, Android Development, Blockchain, Cloud Engineering and DevSecOps, Cybersecurity, Software and System Development, Web Technologies', 0, 'sherryann.rodenas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sherry Ann', 'NA', 'Rodenas', NULL, '', '', '_defaultUser.png', '2026-05-13 07:00:00', NULL, '2026-05-20 03:57:36', NULL, '2026-05-20 03:57:14', NULL, 0, NULL, NULL),
(743, 1, 'james.smith', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'james.smith@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'James', 'Michael', 'Smith', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-15 18:42:13', NULL, NULL, 0, 0, 4, 'IT402'),
(744, 1, 'olivia.johnson', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'olivia.johnson@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Olivia', 'Grace', 'Johnson', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(745, 1, 'robert.williams', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'robert.williams@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Robert', 'David', 'Williams', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(746, 1, 'emma.brown', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'emma.brown@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Emma', 'Marie', 'Brown', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(747, 1, 'john.jones', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'john.jones@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John', 'Christopher', 'Jones', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(748, 1, 'ava.garcia', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'ava.garcia@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ava', 'Elizabeth', 'Garcia', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(749, 1, 'william.miller', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'william.miller@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'William', 'Thomas', 'Miller', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(750, 1, 'sophia.davis', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'sophia.davis@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sophia', 'Rose', 'Davis', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(751, 1, 'david.rodriguez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'david.rodriguez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'David', 'Anthony', 'Rodriguez', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(752, 1, 'isabella.martinez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'isabella.martinez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Isabella', 'Louise', 'Martinez', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(753, 1, 'richard.hernandez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'richard.hernandez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Richard', 'James', 'Hernandez', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(754, 1, 'mia.lopez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'mia.lopez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Mia', 'Lynn', 'Lopez', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(755, 1, 'joseph.gonzalez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'joseph.gonzalez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joseph', 'Andrew', 'Gonzalez', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(756, 1, 'charlotte.wilson', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'charlotte.wilson@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Charlotte', 'Ann', 'Wilson', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(757, 1, 'thomas.anderson', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'thomas.anderson@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Thomas', 'Edward', 'Anderson', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(758, 1, 'amelia.thomas', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'amelia.thomas@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Amelia', 'Jane', 'Thomas', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(759, 1, 'charles.taylor', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'charles.taylor@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Charles', 'Robert', 'Taylor', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(760, 1, 'harper.moore', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'harper.moore@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Harper', 'Nicole', 'Moore', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(761, 1, 'christopher.jackson', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'christopher.jackson@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Christopher', 'Lee', 'Jackson', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(762, 1, 'evelyn.martin', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'evelyn.martin@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Evelyn', 'Rae', 'Martin', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(763, 1, 'matthew.lee', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'matthew.lee@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Matthew', 'Ryan', 'Lee', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(764, 1, 'abigail.thompson', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'abigail.thompson@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Abigail', 'May', 'Thompson', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(765, 1, 'daniel.white', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'daniel.white@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Daniel', 'Alan', 'White', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(766, 1, 'emily.harris', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'emily.harris@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Emily', 'Victoria', 'Harris', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(767, 1, 'mark.sanchez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'mark.sanchez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Mark', 'Patrick', 'Sanchez', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(768, 1, 'elizabeth.clark', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'elizabeth.clark@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Elizabeth', 'Anne', 'Clark', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(769, 1, 'donald.ramirez', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'donald.ramirez@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Donald', 'Scott', 'Ramirez', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(770, 1, 'sofia.lewis', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'sofia.lewis@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sofia', 'Dawn', 'Lewis', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(771, 1, 'steven.robinson', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'steven.robinson@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Steven', 'Paul', 'Robinson', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(772, 1, 'avery.walker', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'avery.walker@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Avery', 'Faith', 'Walker', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(773, 1, 'andrew.young', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'andrew.young@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Andrew', 'Gregory', 'Young', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(774, 1, 'scarlett.allen', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'scarlett.allen@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Scarlett', 'Joy', 'Allen', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(775, 1, 'joshua.king', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'joshua.king@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joshua', 'Aaron', 'King', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(776, 1, 'madison.wright', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'madison.wright@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Madison', 'Paige', 'Wright', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(777, 1, 'kevin.scott', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'kevin.scott@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kevin', 'Douglas', 'Scott', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(778, 1, 'chloe.green', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'chloe.green@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Chloe', 'Brooke', 'Green', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(779, 1, 'brayden.baker', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'brayden.baker@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Brayden', 'Tyler', 'Baker', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402'),
(780, 1, 'lily.adams', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'lily.adams@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Lily', 'Catherine', 'Adams', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402');
INSERT INTO `users` (`id`, `usertype`, `username`, `program`, `area_of_expertise`, `is_parttime`, `email`, `password`, `first_name`, `middle_name`, `last_name`, `gender`, `headline`, `bio`, `profile_image`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`, `is_program_chair`, `is_external`, `year`, `section`) VALUES
(781, 1, 'ryan.nelson', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'ryan.nelson@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ryan', 'Bradley', 'Nelson', NULL, '', '', '_defaultUser.png', NULL, NULL, '2026-05-19 05:48:51', NULL, NULL, 0, 0, 4, 'IT402');

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
(8, 482, '78', 'CS302', 'C607', 'Thursday', '16:30:00', '19:30:00', 'CSCN05C', 0, 0, 3),
(14, 488, '78', 'CS301', 'L203', 'Friday', '07:00:00', '10:00:00', 'CSNC01C', 0, 0, 3),
(15, 488, '78', 'CS301', 'C608', 'Friday', '10:30:00', '13:00:00', 'CSNC01C', 0, 0, 3),
(16, 488, '78', 'CS301', 'C204', 'Friday', '13:30:00', '16:30:00', 'DCSN06C', 0, 0, 3),
(17, 499, '78', 'CS301', 'C610', 'Saturday', '07:00:00', '09:00:00', 'CSCN07C', 0, 0, 3),
(18, 499, '78', 'CS301', 'C204', 'Saturday', '10:00:00', '13:00:00', 'CSCN07C', 0, 0, 3),
(19, 488, '78', 'CS301', 'C204', 'Saturday', '13:30:00', '16:30:00', 'CSCN08C', 0, 0, 3),
(20, 488, '78', 'CS301', 'C613', 'Saturday', '17:00:00', '19:30:00', 'CSCN08C', 0, 0, 3),
(22, 481, '78', 'CS302', 'C712', 'Thursday', '10:00:00', '11:30:00', 'ITEN04C', 0, 0, 3),
(23, 499, '80', 'IT301', 'C302', 'Monday', '07:00:00', '10:00:00', 'ICTN18C', 0, 0, 3),
(24, 481, '78', 'CS302', 'C712', 'Tuesday', '10:00:00', '11:30:00', 'ITEN04C', 0, 0, 3),
(25, 482, '78', 'CS301', 'C704', 'Tuesday', '16:30:00', '19:30:00', 'CSCN05C', 0, 0, 3),
(26, 673, '80', 'IT401', 'C608', 'Monday', '16:30:00', '18:00:00', 'TRPN01E ', 0, 0, 4),
(27, 673, '80', 'IT401', 'C608', 'Wednesday', '16:30:00', '18:00:00', 'TRPN01E', 0, 0, 4),
(28, 673, '80', 'IT401', 'C608', 'Tuesday', '08:30:00', '10:00:00', 'ITSN01C', 0, 0, 4),
(29, 673, '80', 'IT401', 'C608', 'Thursday', '08:30:00', '10:00:00', 'ITSN01C', 0, 0, 4),
(30, 487, '78', 'CS301', 'C707', 'Tuesday', '07:00:00', '09:00:00', 'ICTN04C', 0, 0, 3),
(31, 486, '80', 'IT401', 'C606', 'Friday', '10:00:00', '13:00:00', 'CPTN02C', 0, 0, 4),
(32, 487, '78', 'CS301', 'C201', 'Tuesday', '10:00:00', '12:30:00', 'ICTN04C', 0, 0, 3),
(33, 481, '78', 'CS301', 'C608', 'Tuesday', '13:00:00', '14:30:00', 'ITEN04C', 0, 0, 3),
(34, 481, '78', 'CS301', 'C608', 'Thursday', '13:00:00', '14:30:00', 'ITEN04C', 0, 0, 3),
(35, 488, '78', 'CS301', 'C608', 'Thursday', '16:30:00', '18:30:00', 'DCSN06C', 0, 0, 3),
(36, 674, '80', 'IT402', 'C602', 'Tuesday', '16:30:00', '19:30:00', 'CPTN02C', 0, 0, 4),
(37, 487, '78', 'CS302', 'L202', 'Tuesday', '14:30:00', '16:30:00', 'ICTN04C', 0, 0, 3),
(38, 487, '78', 'CS302', 'C711', 'Tuesday', '17:00:00', '19:00:00', 'ICTN04C', 0, 0, 3),
(39, 488, '78', 'CS302', 'C606', 'Thursday', '07:00:00', '09:00:00', 'DCSN06C', 0, 0, 3),
(40, 485, '78', 'CS302', 'C613', 'Friday', '07:00:00', '09:00:00', 'CSCN08C', 0, 0, 3),
(41, 485, '78', 'CS302', 'C204', 'Friday', '10:00:00', '13:00:00', 'CSCN08C', 0, 0, 3),
(42, 488, '78', 'CS302', 'C204', 'Friday', '16:30:00', '19:30:00', 'DCSN06C', 0, 0, 3),
(43, 488, '78', 'CS302', 'C613', 'Saturday', '07:00:00', '09:30:00', 'CSCN01C', 0, 0, 3),
(44, 488, '78', 'CS302', 'C610', 'Saturday', '10:00:00', '13:00:00', 'CSCN01C', 0, 0, 3),
(45, 486, '80', 'IT402', 'TBA', 'Tuesday', '10:00:00', '13:00:00', 'CPTN02C', 0, 0, 4),
(46, 499, '78', 'CS302', 'C610', 'Saturday', '13:30:00', '16:00:00', 'CSCN07C', 0, 0, 3),
(47, 486, '80', 'IT402', 'TBA', 'Friday', '14:00:00', '17:00:00', 'ITSN01C', 0, 0, 4),
(48, 499, '78', 'CS302', 'C204', 'Saturday', '16:30:00', '19:30:00', 'CSCN07C', 0, 0, 3),
(49, 673, '78', 'CS401', 'TBA', 'Thursday', '16:30:00', '19:30:00', 'THEL01C', 0, 0, 4),
(50, 484, '78', 'CS401', 'C606', 'Friday', '13:30:00', '15:30:00', 'ITEN01C', 0, 0, 3),
(51, 484, '78', 'CS401', 'C203', 'Friday', '16:00:00', '19:30:00', 'ITEN01C', 0, 0, 3),
(52, 480, '78', 'CS401', 'C608', 'Saturday', '07:00:00', '09:30:00', 'CSEL04C', 0, 0, 4),
(53, 496, '78', 'CS401', 'C302', 'Saturday', '10:00:00', '13:00:00', 'CSEL04C', 0, 0, 4),
(54, 486, '78', 'CS401', 'C608', 'Saturday', '13:30:00', '16:30:00', 'ITSN01C', 0, 0, 4);

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
  ADD KEY `idx_defense_team` (`team_id`),
  ADD KEY `idx_ds_status_date_room` (`status`,`schedule_date`,`room`,`start_time`,`end_time`);

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
  ADD UNIQUE KEY `id` (`id`,`username`,`email`),
  ADD KEY `idx_external_panelists` (`is_external_panelist`);

--
-- Indexes for table `user_schedules`
--
ALTER TABLE `user_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_us_user_day_time` (`user_id`,`day_of_week`,`start_time`,`end_time`),
  ADD KEY `idx_us_program_section_day` (`program`,`section`,`day_of_week`);

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `notification_actions`
--
ALTER TABLE `notification_actions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `panelist_approvals`
--
ALTER TABLE `panelist_approvals`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `panel_assignment_popup_state`
--
ALTER TABLE `panel_assignment_popup_state`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `re_defense_assessments`
--
ALTER TABLE `re_defense_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rubrics`
--
ALTER TABLE `rubrics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `rubric_criteria`
--
ALTER TABLE `rubric_criteria`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=559;

--
-- AUTO_INCREMENT for table `rubric_groups`
--
ALTER TABLE `rubric_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rubric_group_items`
--
ALTER TABLE `rubric_group_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- AUTO_INCREMENT for table `rubric_levels`
--
ALTER TABLE `rubric_levels`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT for table `section_professors`
--
ALTER TABLE `section_professors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `specialization_pool`
--
ALTER TABLE `specialization_pool`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=782;

--
-- AUTO_INCREMENT for table `user_schedules`
--
ALTER TABLE `user_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

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
