-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 21, 2025 at 09:52 PM
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

--
-- Dumping data for table `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `user_email`, `auth_type`, `selector`, `token`, `created_at`, `expires_at`) VALUES
(1, 'winstonagustin.ih@gmail.com', 'account_verify', '613f4c35ee6dac46', '$2y$10$fGDz8SdTBADhULRbmpcauORjPUc1tD.JsKCldb72Z.uQFaej5PdG.', '2025-07-22 05:07:15', '2025-07-22 21:07:15'),
(2, 'ton.agustin09@gmail.com', 'password_reset', '5cb4a55ab4006dc4', '$2y$10$o.wvCBt7WYWugpZNvwQaX.k130D9lRxeAcNrwaZPQYTYY9UjVf3gu', '2025-09-16 09:00:17', '2025-09-16 10:00:16'),
(3, 'kahit.ano@lpu.edu.ph', 'account_verify', '450073c3d1df9f3d', '$2y$10$FOKJO285TtKgRBS6gpclTuS4kUnb8Wo2wXMyT3t7PDx3JXKa4Bq6a', '2025-11-06 06:45:12', '2025-11-06 07:45:12');

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
  `defense_type` enum('title_proposal','title_defense','final_defense','re-defense') DEFAULT 'title_proposal',
  `related_requirement_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array of team_requirement_files IDs for multi-submission requirements' CHECK (json_valid(`related_requirement_files`)),
  `admin_override_defense_type` tinyint(1) DEFAULT 0 COMMENT 'Whether defense type was manually overridden by admin',
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval_status` enum('approved','rejected','pending') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `defense_schedules`
--

INSERT INTO `defense_schedules` (`id`, `team_id`, `panelist_id`, `panelist_id2`, `panelist_id3`, `schedule_date`, `start_time`, `end_time`, `room`, `defense_type`, `related_requirement_files`, `admin_override_defense_type`, `status`, `created_at`, `approval_status`) VALUES
(7, 1, 270, 271, 272, '2025-08-24', '09:30:00', '10:00:00', 'Defense Room 1', NULL, NULL, 0, 'scheduled', '2025-08-23 16:03:35', 'pending'),
(9, 4, 271, 270, 272, '2025-11-21', '10:00:00', '12:00:00', 'defense room 1', NULL, NULL, 0, 'scheduled', '2025-11-21 14:46:29', 'pending'),
(10, 5, 271, 285, 277, '2025-11-21', '12:00:00', '14:00:00', 'defense room 1', NULL, NULL, 0, 'scheduled', '2025-11-21 14:46:29', 'pending');

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

--
-- Dumping data for table `defense_type_overrides`
--

INSERT INTO `defense_type_overrides` (`id`, `team_id`, `override_type`, `reason`, `active`, `created_by`, `created_at`, `updated_at`, `expires_at`) VALUES
(1, 4, 'final_defense', '', 0, 0, '2025-11-21 16:34:41', '2025-11-21 16:35:04', NULL);

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
(5, 'ALLOWED_INACTIVITY_TIME', '86400', 'Allowed inactivity time in seconds'),
(11, 'MAIL_HOST', 'smtp.gmail.com', 'Mail host'),
(12, 'MAIL_USERNAME', 'ton.agustin09@gmail.com', 'Mail username'),
(13, 'MAIL_PASSWORD', 'kkjh zktq xuhn mhml ', 'Mail password'),
(14, 'MAIL_ENCRYPTION', 'ssl', 'Mail encryption'),
(15, 'MAIL_PORT', '465', 'Mail port'),
(16, 'APP_LOGO_NAVBAR', 'logo_full_lightbg.png', NULL),
(17, 'APP_LOGO_FOOTER', 'logowhite.png', NULL),
(18, 'APP_GEMINI_API', 'AIzaSyBSE1RdMjnZA7w83hBJW9EwF4fpuRdgp_c', NULL);

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

--
-- Dumping data for table `evaluation_details`
--

INSERT INTO `evaluation_details` (`id`, `evaluation_id`, `rubric_id`, `criterion_id`, `student_id`, `score`, `selected_option`, `comment`, `created_at`, `updated_at`) VALUES
(114, 7, 5, 43, 267, 1, NULL, NULL, '2025-08-23 06:08:46', '2025-08-23 06:08:46'),
(115, 8, 5, 43, 268, 1, NULL, NULL, '2025-08-23 06:08:46', '2025-08-23 06:08:46'),
(117, 9, 1, 8, NULL, 1, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(118, 9, 1, 9, NULL, 1, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(119, 9, 1, 10, NULL, 1, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(120, 9, 2, 45, NULL, 1, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(121, 9, 3, 26, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(122, 9, 3, 27, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(123, 9, 3, 28, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(124, 9, 3, 29, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(125, 9, 3, 30, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(126, 9, 3, 31, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(127, 9, 3, 32, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(128, 9, 3, 33, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(129, 9, 3, 34, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(130, 9, 3, 35, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(131, 9, 4, 20, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(132, 9, 4, 21, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(133, 9, 4, 22, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(134, 9, 4, 23, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(135, 9, 4, 24, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(136, 9, 4, 25, NULL, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(137, 9, 5, 40, 267, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(138, 10, 5, 40, 268, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(139, 9, 5, 41, 267, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(140, 10, 5, 41, 268, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(141, 9, 5, 42, 267, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(142, 10, 5, 42, 268, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(143, 9, 5, 43, 267, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(144, 10, 5, 43, 268, 0, NULL, NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(145, 9, 6, NULL, NULL, NULL, '0', NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39');

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

--
-- Dumping data for table `evaluation_per_panel`
--

INSERT INTO `evaluation_per_panel` (`id`, `defense_schedule_id`, `evaluator_id`, `student_id`, `group_score`, `solo_score`, `total_score`, `comments`, `created_at`, `updated_at`) VALUES
(7, 6, 270, 267, 65, 2, 67, '123asd', '2025-08-23 06:08:46', '2025-08-23 06:08:46'),
(8, 6, 270, 268, 65, 2, 67, '123asd', '2025-08-23 06:08:46', '2025-08-23 06:08:46'),
(9, 7, 272, 267, 5, 0, 5, '', '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(10, 7, 272, 268, 5, 0, 5, '', '2025-08-23 16:37:39', '2025-08-23 16:37:39');

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
(2, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team 1\'s defense:\n\n📅 Date: July 25, 2025\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: a\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Title of Team 1\n\nPlease approve or decline this assignment.', 4, NULL, 1, '2025-07-22 17:45:02', '2025-08-23 06:03:59'),
(3, 269, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for July 25, 2025 at 8:00 AM - 10:00 AM in a. Waiting for panelist approval.', 4, NULL, 1, '2025-07-22 17:45:02', '2025-07-22 19:45:00'),
(4, 267, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for July 25, 2025 at 8:00 AM - 10:00 AM in a. Waiting for panelist approval.', 4, NULL, 1, '2025-07-22 17:45:02', '2025-07-22 17:47:51'),
(5, 268, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for July 25, 2025 at 8:00 AM - 10:00 AM in a. Waiting for panelist approval.', 4, NULL, 0, '2025-07-22 17:45:02', '2025-07-22 17:45:02'),
(6, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team 1\'s defense:\n\n📅 Date: July 25, 2025\n🕒 Time: 11:00 AM - 1:00 PM\n🏢 Room: a\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Title of Team 1\n\nPlease approve or decline this assignment.', 5, NULL, 1, '2025-07-22 19:40:15', '2025-08-23 05:51:46'),
(7, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team 1\'s defense:\n\n📅 Date: July 25, 2025\n🕒 Time: 11:00 AM - 1:00 PM\n🏢 Room: a\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Title of Team 1\n\nPlease approve or decline this assignment.', 5, NULL, 0, '2025-07-22 19:40:15', '2025-07-22 19:40:15'),
(8, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team 1\'s defense:\n\n📅 Date: July 25, 2025\n🕒 Time: 11:00 AM - 1:00 PM\n🏢 Room: a\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Title of Team 1\n\nPlease approve or decline this assignment.', 5, NULL, 0, '2025-07-22 19:40:15', '2025-07-22 19:40:15'),
(9, 269, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for July 25, 2025 at 11:00 AM - 1:00 PM in a. Waiting for panelist approval.', 5, NULL, 1, '2025-07-22 19:40:15', '2025-07-22 19:45:00'),
(10, 267, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for July 25, 2025 at 11:00 AM - 1:00 PM in a. Waiting for panelist approval.', 5, NULL, 0, '2025-07-22 19:40:15', '2025-07-22 19:40:15'),
(11, 268, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for July 25, 2025 at 11:00 AM - 1:00 PM in a. Waiting for panelist approval.', 5, NULL, 0, '2025-07-22 19:40:15', '2025-07-22 19:40:15'),
(12, 269, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'Title of Team 1\' has been approved and you can now proceed with your research.', 1, NULL, 0, '2025-07-22 19:48:15', '2025-07-22 19:48:15'),
(13, 267, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'Title of Team 1\' has been approved and you can now proceed with your research.', 1, NULL, 0, '2025-07-22 19:48:15', '2025-07-22 19:48:15'),
(14, 268, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'Title of Team 1\' has been approved and you can now proceed with your research.', 1, NULL, 0, '2025-07-22 19:48:15', '2025-07-22 19:48:15'),
(15, 267, '', 'New Feedback Available', 'Your adviser has provided feedback for your \'Capstone 2\' submission. Please check your requirements section to view the feedback.', 1, NULL, 0, '2025-07-22 19:50:20', '2025-07-22 19:50:20'),
(16, 268, '', 'New Feedback Available', 'Your adviser has provided feedback for your \'Capstone 2\' submission. Please check your requirements section to view the feedback.', 1, NULL, 0, '2025-07-22 19:50:20', '2025-07-22 19:50:20'),
(17, 267, '', 'New Feedback Available', 'Your adviser has provided feedback for your \'Capstone 2\' submission. Please check your requirements section to view the feedback.', 1, NULL, 1, '2025-07-22 19:50:20', '2025-07-22 19:50:44'),
(18, 268, '', 'New Feedback Available', 'Your adviser has provided feedback for your \'Capstone 2\' submission. Please check your requirements section to view the feedback.', 1, NULL, 0, '2025-07-22 19:50:20', '2025-07-22 19:50:20'),
(19, 267, '', 'New Feedback Available', 'Your adviser has provided feedback for your \'Capstone 2\' submission. Please check your requirements section to view the feedback.', 1, NULL, 0, '2025-08-15 15:46:54', '2025-08-15 15:46:54'),
(20, 268, '', 'New Feedback Available', 'Your adviser has provided feedback for your \'Capstone 2\' submission. Please check your requirements section to view the feedback.', 1, NULL, 0, '2025-08-15 15:46:54', '2025-08-15 15:46:54'),
(21, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team 1\'s defense:\n\n📅 Date: August 23, 2025\n🕒 Time: 7:00 AM - 8:00 AM\n🏢 Room: 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Title of Team 1\n\nPlease approve or decline this assignment.', 6, NULL, 1, '2025-08-23 06:07:25', '2025-08-23 06:07:50'),
(22, 275, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team 1\'s defense:\n\n📅 Date: August 23, 2025\n🕒 Time: 7:00 AM - 8:00 AM\n🏢 Room: 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Title of Team 1\n\nPlease approve or decline this assignment.', 6, NULL, 0, '2025-08-23 06:07:25', '2025-08-23 06:07:25'),
(23, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team 1\'s defense:\n\n📅 Date: August 23, 2025\n🕒 Time: 7:00 AM - 8:00 AM\n🏢 Room: 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Title of Team 1\n\nPlease approve or decline this assignment.', 6, NULL, 0, '2025-08-23 06:07:25', '2025-08-23 06:07:25'),
(24, 269, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for August 23, 2025 at 7:00 AM - 8:00 AM in 1. Waiting for panelist approval.', 6, NULL, 0, '2025-08-23 06:07:25', '2025-08-23 06:07:25'),
(25, 267, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for August 23, 2025 at 7:00 AM - 8:00 AM in 1. Waiting for panelist approval.', 6, NULL, 0, '2025-08-23 06:07:25', '2025-08-23 06:07:25'),
(26, 268, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for August 23, 2025 at 7:00 AM - 8:00 AM in 1. Waiting for panelist approval.', 6, NULL, 0, '2025-08-23 06:07:25', '2025-08-23 06:07:25'),
(27, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team 1\'s defense:\n\n📅 Date: August 24, 2025\n🕒 Time: 9:30 AM - 10:00 AM\n🏢 Room: asda\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Title of Team 1\n\nPlease approve or decline this assignment.', 7, NULL, 1, '2025-08-23 16:03:35', '2025-08-23 16:37:13'),
(28, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team 1\'s defense:\n\n📅 Date: August 24, 2025\n🕒 Time: 9:30 AM - 10:00 AM\n🏢 Room: asda\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Title of Team 1\n\nPlease approve or decline this assignment.', 7, NULL, 0, '2025-08-23 16:03:35', '2025-08-23 16:03:35'),
(29, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team 1\'s defense:\n\n📅 Date: August 24, 2025\n🕒 Time: 9:30 AM - 10:00 AM\n🏢 Room: asda\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Title of Team 1\n\nPlease approve or decline this assignment.', 7, NULL, 1, '2025-08-23 16:03:35', '2025-08-23 16:34:59'),
(30, 269, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for August 24, 2025 at 9:30 AM - 10:00 AM in asda. Waiting for panelist approval.', 7, NULL, 0, '2025-08-23 16:03:35', '2025-08-23 16:03:35'),
(31, 267, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for August 24, 2025 at 9:30 AM - 10:00 AM in asda. Waiting for panelist approval.', 7, NULL, 0, '2025-08-23 16:03:35', '2025-08-23 16:03:35'),
(32, 268, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for August 24, 2025 at 9:30 AM - 10:00 AM in asda. Waiting for panelist approval.', 7, NULL, 0, '2025-08-23 16:03:35', '2025-08-23 16:03:35'),
(33, 269, 'requirement_submitted', 'New Requirement Submission', 'Team \'Team 1\' has submitted the requirement \'Capstone 1\'. File: 1_3_1762433207_5-Drugs-for-Asthma.pdf', 1, NULL, 0, '2025-11-06 12:46:47', '2025-11-06 12:46:47'),
(34, 299, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'ML Prediction Model\' has been approved and you can now proceed with your research.', 7, NULL, 0, '2025-11-20 23:19:59', '2025-11-20 23:19:59'),
(35, 300, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'ML Prediction Model\' has been approved and you can now proceed with your research.', 7, NULL, 0, '2025-11-20 23:19:59', '2025-11-20 23:19:59'),
(36, 301, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'ML Prediction Model\' has been approved and you can now proceed with your research.', 7, NULL, 0, '2025-11-20 23:19:59', '2025-11-20 23:19:59'),
(37, 302, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'ML Prediction Model\' has been approved and you can now proceed with your research.', 7, NULL, 0, '2025-11-20 23:19:59', '2025-11-20 23:19:59'),
(38, 283, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'ML Prediction Model\' has been approved and you can now proceed with your research.', 7, NULL, 0, '2025-11-20 23:19:59', '2025-11-20 23:19:59'),
(39, 272, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'AI Optimization Project\' has been approved and you can now proceed with your research.', 6, NULL, 0, '2025-11-20 23:20:12', '2025-11-20 23:20:12'),
(40, 295, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'AI Optimization Project\' has been approved and you can now proceed with your research.', 6, NULL, 0, '2025-11-20 23:20:12', '2025-11-20 23:20:12'),
(41, 296, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'AI Optimization Project\' has been approved and you can now proceed with your research.', 6, NULL, 0, '2025-11-20 23:20:12', '2025-11-20 23:20:12'),
(42, 297, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'AI Optimization Project\' has been approved and you can now proceed with your research.', 6, NULL, 0, '2025-11-20 23:20:12', '2025-11-20 23:20:12'),
(43, 298, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'AI Optimization Project\' has been approved and you can now proceed with your research.', 6, NULL, 0, '2025-11-20 23:20:12', '2025-11-20 23:20:12'),
(44, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 21, 2025\n🕒 Time: 7:00 AM - 9:00 AM\n🏢 Room: d201\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 8, NULL, 0, '2025-11-21 05:39:08', '2025-11-21 05:39:08'),
(45, 277, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 21, 2025\n🕒 Time: 7:00 AM - 9:00 AM\n🏢 Room: d201\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 8, NULL, 0, '2025-11-21 05:39:08', '2025-11-21 05:39:08'),
(46, 279, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 21, 2025\n🕒 Time: 7:00 AM - 9:00 AM\n🏢 Room: d201\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 8, NULL, 0, '2025-11-21 05:39:08', '2025-11-21 05:39:08'),
(47, 291, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 21, 2025 at 7:00 AM - 9:00 AM in d201. Waiting for panelist approval.', 8, NULL, 0, '2025-11-21 05:39:08', '2025-11-21 05:39:08'),
(48, 292, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 21, 2025 at 7:00 AM - 9:00 AM in d201. Waiting for panelist approval.', 8, NULL, 0, '2025-11-21 05:39:08', '2025-11-21 05:39:08'),
(49, 293, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 21, 2025 at 7:00 AM - 9:00 AM in d201. Waiting for panelist approval.', 8, NULL, 0, '2025-11-21 05:39:08', '2025-11-21 05:39:08'),
(50, 294, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 21, 2025 at 7:00 AM - 9:00 AM in d201. Waiting for panelist approval.', 8, NULL, 0, '2025-11-21 05:39:08', '2025-11-21 05:39:08'),
(51, 283, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 21, 2025 at 7:00 AM - 9:00 AM in d201. Waiting for panelist approval.', 8, NULL, 0, '2025-11-21 05:39:08', '2025-11-21 05:39:08'),
(52, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 21, 2025\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 9, NULL, 0, '2025-11-21 14:46:29', '2025-11-21 14:46:29'),
(53, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 21, 2025\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 9, NULL, 1, '2025-11-21 14:46:29', '2025-11-21 16:28:43'),
(54, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 21, 2025\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 9, NULL, 0, '2025-11-21 14:46:29', '2025-11-21 14:46:29'),
(55, 280, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 21, 2025 at 10:00 AM - 12:00 PM in defense room 1. Waiting for panelist approval.', 9, NULL, 0, '2025-11-21 14:46:29', '2025-11-21 14:46:29'),
(56, 287, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 21, 2025 at 10:00 AM - 12:00 PM in defense room 1. Waiting for panelist approval.', 9, NULL, 0, '2025-11-21 14:46:29', '2025-11-21 14:46:29'),
(57, 288, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 21, 2025 at 10:00 AM - 12:00 PM in defense room 1. Waiting for panelist approval.', 9, NULL, 0, '2025-11-21 14:46:29', '2025-11-21 14:46:29'),
(58, 289, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 21, 2025 at 10:00 AM - 12:00 PM in defense room 1. Waiting for panelist approval.', 9, NULL, 0, '2025-11-21 14:46:29', '2025-11-21 14:46:29'),
(59, 290, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 21, 2025 at 10:00 AM - 12:00 PM in defense room 1. Waiting for panelist approval.', 9, NULL, 0, '2025-11-21 14:46:29', '2025-11-21 14:46:29'),
(60, 280, 'requirement_submitted', 'New Requirement Submission', 'Team \'Team Nexus\' has submitted the requirement \'Research methods Template A\'. File: 7_46_1763742782_Document1.pdf', 7, NULL, 0, '2025-11-21 16:33:02', '2025-11-21 16:33:02'),
(61, 280, 'requirement_submitted', 'New Requirement Submission', 'Team \'Team Nexus\' has submitted the requirement \'Research methods Template A\'. File: 7_46_1763748840_Document1.pdf', 7, NULL, 0, '2025-11-21 18:14:00', '2025-11-21 18:14:00'),
(62, 280, 'requirement_submitted', 'New Requirement Submission', 'Team \'Team Innovate\' has submitted the requirement \'Research methods Template A\'. File: 4_46_1763749192_Document1.pdf', 4, NULL, 0, '2025-11-21 18:19:52', '2025-11-21 18:19:52'),
(63, 280, 'requirement_submitted', 'New Requirement Submission', 'Team \'Team Innovate\' has submitted the requirement \'Research methods Template A\'. File: 4_46_1763749500_document.pdf', 4, NULL, 0, '2025-11-21 18:25:00', '2025-11-21 18:25:00'),
(64, 280, 'requirement_submitted', 'New Requirement Submission', 'Team \'Team Innovate\' has submitted the requirement \'Final Manuscript\'. File: 4_5_1763749535_THESIS-7.pdf', 4, NULL, 0, '2025-11-21 18:25:35', '2025-11-21 18:25:35'),
(65, 280, 'requirement_submitted', 'New Requirement Submission', 'Team \'Team Innovate\' has submitted the requirement \'Research methods Template A\'. File: 4_46_1763750136_THESIS-9.pdf', 4, NULL, 0, '2025-11-21 18:35:36', '2025-11-21 18:35:36'),
(66, 280, 'requirement_submitted', 'New Requirement Submission', 'Team \'Team Innovate\' has submitted the requirement \'Research methods Template A\'. File: 4_46_1763752944_Document1.pdf', 4, NULL, 0, '2025-11-21 19:22:24', '2025-11-21 19:22:24'),
(67, 280, 'requirement_submitted', 'New Requirement Submission', 'Team \'Team Innovate\' has submitted the requirement \'Final Manuscript\'. File: 4_5_1763753811_1_3_1762412995_5-Drugs-for-Asthma.pdf', 4, NULL, 0, '2025-11-21 19:36:51', '2025-11-21 19:36:51');

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
(1, 6, 'approve_defense', '{\"schedule_id\":\"5\"}', 1, '2025-08-23 05:51:46', '2025-07-22 19:40:15'),
(2, 6, 'reject_defense', '{\"schedule_id\":\"5\"}', 1, '2025-08-23 05:51:46', '2025-07-22 19:40:15'),
(3, 7, 'approve_defense', '{\"schedule_id\":\"5\"}', 0, NULL, '2025-07-22 19:40:15'),
(4, 7, 'reject_defense', '{\"schedule_id\":\"5\"}', 0, NULL, '2025-07-22 19:40:15'),
(5, 8, 'approve_defense', '{\"schedule_id\":\"5\"}', 0, NULL, '2025-07-22 19:40:15'),
(6, 8, 'reject_defense', '{\"schedule_id\":\"5\"}', 0, NULL, '2025-07-22 19:40:15'),
(7, 21, 'approve_defense', '{\"schedule_id\":\"6\"}', 1, '2025-08-23 06:07:50', '2025-08-23 06:07:25'),
(8, 21, 'reject_defense', '{\"schedule_id\":\"6\"}', 1, '2025-08-23 06:07:50', '2025-08-23 06:07:25'),
(9, 22, 'approve_defense', '{\"schedule_id\":\"6\"}', 0, NULL, '2025-08-23 06:07:25'),
(10, 22, 'reject_defense', '{\"schedule_id\":\"6\"}', 0, NULL, '2025-08-23 06:07:25'),
(11, 23, 'approve_defense', '{\"schedule_id\":\"6\"}', 0, NULL, '2025-08-23 06:07:25'),
(12, 23, 'reject_defense', '{\"schedule_id\":\"6\"}', 0, NULL, '2025-08-23 06:07:25'),
(13, 27, 'approve_defense', '{\"schedule_id\":\"7\"}', 1, '2025-08-23 16:37:13', '2025-08-23 16:03:35'),
(14, 27, 'reject_defense', '{\"schedule_id\":\"7\"}', 1, '2025-08-23 16:37:13', '2025-08-23 16:03:35'),
(15, 28, 'approve_defense', '{\"schedule_id\":\"7\"}', 0, NULL, '2025-08-23 16:03:35'),
(16, 28, 'reject_defense', '{\"schedule_id\":\"7\"}', 0, NULL, '2025-08-23 16:03:35'),
(17, 29, 'approve_defense', '{\"schedule_id\":\"7\"}', 1, '2025-08-23 16:34:59', '2025-08-23 16:03:35'),
(18, 29, 'reject_defense', '{\"schedule_id\":\"7\"}', 1, '2025-08-23 16:34:59', '2025-08-23 16:03:35'),
(19, 44, 'approve_defense', '{\"schedule_id\":\"8\"}', 0, NULL, '2025-11-21 05:39:08'),
(20, 44, 'reject_defense', '{\"schedule_id\":\"8\"}', 0, NULL, '2025-11-21 05:39:08'),
(21, 45, 'approve_defense', '{\"schedule_id\":\"8\"}', 0, NULL, '2025-11-21 05:39:08'),
(22, 45, 'reject_defense', '{\"schedule_id\":\"8\"}', 0, NULL, '2025-11-21 05:39:08'),
(23, 46, 'approve_defense', '{\"schedule_id\":\"8\"}', 0, NULL, '2025-11-21 05:39:08'),
(24, 46, 'reject_defense', '{\"schedule_id\":\"8\"}', 0, NULL, '2025-11-21 05:39:08'),
(25, 52, 'approve_defense', '{\"schedule_id\":\"9\"}', 0, NULL, '2025-11-21 14:46:29'),
(26, 52, 'reject_defense', '{\"schedule_id\":\"9\"}', 0, NULL, '2025-11-21 14:46:29'),
(27, 53, 'approve_defense', '{\"schedule_id\":\"9\"}', 1, '2025-11-21 16:28:43', '2025-11-21 14:46:29'),
(28, 53, 'reject_defense', '{\"schedule_id\":\"9\"}', 1, '2025-11-21 16:28:43', '2025-11-21 14:46:29'),
(29, 54, 'approve_defense', '{\"schedule_id\":\"9\"}', 0, NULL, '2025-11-21 14:46:29'),
(30, 54, 'reject_defense', '{\"schedule_id\":\"9\"}', 0, NULL, '2025-11-21 14:46:29');

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

--
-- Dumping data for table `page_content`
--

INSERT INTO `page_content` (`id`, `title`, `slug`, `content`, `status`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'pls work', 'pls-work', '<p>If you\'re seeing this then celebrate, it\'s now <b>working.</b></p>', 'published', '2025-04-18 19:24:53', '2025-07-21 07:01:08', 37, 0),
(2, 'still working and improved?', 'still-working-and-improved', '<h1>Greetings Lyceans,</h1><h3>We are venom.</h3><blockquote class=\"blockquote\"><p>I do not think, therefore I do not am. - Venom</p></blockquote><p><br></p><p>&nbsp;This is a normal paragraph being tested for the features such as, <b>bold,</b>&nbsp;<u>underlined,</u>&nbsp;<i>italic, </i><span style=\"background-color: rgb(0, 255, 0);\">with higlight,</span>&nbsp;&nbsp;<br></p><hr><ul><li>In a bullet<br></li></ul><hr><ol><li>In a number</li></ol><hr><p style=\"text-align: center; \">Centered</p><hr><p style=\"text-align: left;\">Left-aligned</p><hr><p style=\"text-align: right;\">Right-aligned</p><hr><p style=\"text-align: justify;\">Justified&nbsp;Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.<br></p><hr><p style=\"text-align: justify; margin-left: 25px;\">Indented</p><hr><p style=\"text-align: justify; margin-left: 25px;\">Table</p><table class=\"table table-bordered\"><tbody><tr><td>Col1</td><td>Col2</td><td>Col3</td></tr><tr><td>Row1 C1</td><td>Row1 C2</td><td>Row1 C3</td></tr></tbody></table><hr><p style=\"text-align: justify; margin-left: 25px;\">gfdgfd</p>', 'published', '2025-04-21 02:37:06', '2025-04-21 09:18:23', 37, 37);

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

--
-- Dumping data for table `panelist_approvals`
--

INSERT INTO `panelist_approvals` (`id`, `defense_schedule_id`, `panelist_id`, `approval_status`, `response_date`, `rejection_reason`, `created_at`) VALUES
(1, 2, 271, 'approved', '2025-07-21 06:17:19', '', '2025-07-21 09:14:41'),
(2, 2, 270, 'approved', '2025-07-21 05:51:57', '', '2025-07-21 09:14:41'),
(3, 2, 272, 'approved', '2025-07-21 06:32:35', '', '2025-07-21 09:14:41'),
(4, 3, 270, 'pending', NULL, NULL, '2025-07-22 16:19:50'),
(5, 3, 275, 'pending', NULL, NULL, '2025-07-22 16:19:50'),
(6, 3, 272, 'pending', NULL, NULL, '2025-07-22 16:19:50'),
(7, 4, 270, 'pending', NULL, NULL, '2025-07-22 17:45:02'),
(8, 4, 275, 'pending', NULL, NULL, '2025-07-22 17:45:02'),
(9, 4, 272, 'pending', NULL, NULL, '2025-07-22 17:45:02'),
(10, 5, 270, 'approved', '2025-08-23 13:51:46', '', '2025-07-22 19:40:15'),
(11, 5, 271, 'pending', NULL, NULL, '2025-07-22 19:40:15'),
(12, 5, 272, 'pending', NULL, NULL, '2025-07-22 19:40:15'),
(13, 6, 270, 'approved', '2025-08-23 14:07:50', '', '2025-08-23 06:07:25'),
(14, 6, 275, 'pending', NULL, NULL, '2025-08-23 06:07:25'),
(15, 6, 272, 'pending', NULL, NULL, '2025-08-23 06:07:25'),
(16, 7, 272, 'approved', '2025-08-24 00:37:13', '', '2025-08-23 16:03:35'),
(17, 7, 271, 'pending', NULL, NULL, '2025-08-23 16:03:35'),
(18, 7, 270, 'approved', '2025-08-24 00:34:59', '', '2025-08-23 16:03:35'),
(19, 8, 272, 'pending', NULL, NULL, '2025-11-21 05:39:08'),
(20, 8, 277, 'pending', NULL, NULL, '2025-11-21 05:39:08'),
(21, 8, 279, 'pending', NULL, NULL, '2025-11-21 05:39:08'),
(22, 9, 271, 'pending', NULL, NULL, '2025-11-21 14:46:29'),
(23, 9, 270, 'approved', '2025-11-22 00:28:43', '', '2025-11-21 14:46:29'),
(24, 9, 272, 'pending', NULL, NULL, '2025-11-21 14:46:29');

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
(77, 'College of Computer Studies', 'Computer Studies', 'Bachelor of Science in Computer Science', 'Data Science', '2025-07-20 11:16:27'),
(78, 'College of Computer Studies', 'Computer Studies', 'Bachelor of Science in Computer Science', 'Software Engineering', '2025-07-20 11:16:19'),
(79, 'College of Computer Studies', 'Computer Studies', 'Bachelor of Science in Information Technology', 'Network and Information Security', '2025-07-20 11:15:35'),
(80, 'College of Computer Studies', 'Computer Studies', 'Bachelor of Science in Information Technology', 'Web and Mobile Technology', '2025-07-20 11:15:22'),
(81, 'College of Computer Studies', 'Computer Studies', 'Bachelor of Library and Information Science', '', '2025-07-20 11:15:11'),
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

--
-- Dumping data for table `program_manuscript_requirements`
--

INSERT INTO `program_manuscript_requirements` (`id`, `requirement_id`, `program_id`, `defense_type`, `is_required`, `submission_stage`, `can_revise_after`, `visibility_to_panelist`, `created_at`, `updated_at`) VALUES
(3, 46, 79, 'title_proposal', 1, 'before_defense', 0, 1, '2025-11-21 18:22:37', '2025-11-21 18:22:37'),
(4, 46, 80, 'title_proposal', 1, 'before_defense', 0, 1, '2025-11-21 18:22:37', '2025-11-21 18:22:37');

-- --------------------------------------------------------

--
-- Stand-in structure for view `program_manuscript_view`
-- (See below for the actual view)
--
CREATE TABLE `program_manuscript_view` (
`mapping_id` int(11)
,`requirement_id` int(11) unsigned
,`requirement_name` varchar(255)
,`is_defense_manuscript` tinyint(1)
,`program_id` int(11)
,`program_name` varchar(255)
,`defense_type` enum('title_proposal','title_defense','final_defense','re-defense','general')
,`is_required` tinyint(1)
,`submission_stage` enum('before_defense','at_defense','optional')
,`can_revise_after` tinyint(1)
,`visibility_to_panelist` tinyint(1)
);

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
(2, 'Capstone 2', 'title_defense', 0, 0, 1, 'This includes the template', '687d025450e81_1753023060.docx', 'CAPSTONE 1-2 TEMPLATES.docx', '2024-10-31', '2024-11-11 10:52:54'),
(3, 'Capstone 1', 'title_defense', 0, 0, 1, 'This includes the template\r\n(IT ONLY)', '687d0244896eb_1753023044.docx', 'CAPSTONE 1-2 TEMPLATES.docx', '2024-11-30', '2024-11-11 10:52:41'),
(5, 'Final Manuscript', 'final_defense', 1, 0, 1, 'Also used for Research Repository (DO NOT REMOVE)', '687d0263dbbb8_1753023075.docx', 'FULL MANUSCRIPT_template_crd2025.docx', '2026-12-04', '2024-11-11 10:52:22'),
(46, 'Research methods Template A', 'title_proposal', 1, 1, 3, 'Template of title proposal template', NULL, NULL, '2025-11-28', '2025-11-21 14:47:43');

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
(4, 4, 'Next Gen Web App', NULL, NULL, NULL, '2025-11-20 23:16:36', '2025-11-20 23:18:30'),
(5, 5, 'Secure Network Project', NULL, NULL, NULL, '2025-11-20 23:16:36', '2025-11-21 01:04:39'),
(6, 6, 'AI Optimization Project', 'Bachelor of Science in Computer Science - Software Engineering', '2025-11-21 15:20:12', NULL, '2025-11-20 23:16:36', '2025-11-21 12:51:48'),
(7, 7, 'ML Prediction Model', 'Bachelor of Science in Computer Science - Software Engineering', '2025-11-21 15:19:59', NULL, '2025-11-20 23:16:36', '2025-11-21 15:19:59');

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
(1, 'A. Degree of Design / Level of Technical Complexity (30%)', '(RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 09:32:37', '2025-07-21 09:36:37', 100),
(2, 'B. Safety, Functionality, & Workmanship (20%)', '(RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 09:36:20', '2025-07-21 13:25:50', 100),
(3, 'Content (20%)', 'Final Manuscript Rubric (RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 09:41:50', '2025-07-21 09:45:42', 100),
(4, 'Organization (10%)', 'Final Manuscript Rubric (RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 09:45:09', '2025-07-21 09:45:09', 100),
(5, 'Presentation and Defense', 'Final Manuscript Rubric (RE-PRESENTATION)', 'numerical', 1, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, 1, '2025-07-21 09:48:07', '2025-07-21 09:48:20', 100),
(6, 'FINAL RECOMMENDATION:', '(RE-PRESENTATION)', 'passfail', 0, 'Proposal Defense', '', 'System is accepted:', 'System is rejected:', 'below 65% acceptability; refer to thesis adviser', 100.00, 75.00, 65.00, 0, NULL, 1, '2025-07-21 09:52:11', '2025-07-21 09:53:02', 100);

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores criteria rows for Numerical and Yes/No rubrics';

--
-- Dumping data for table `rubric_criteria`
--

INSERT INTO `rubric_criteria` (`id`, `rubric_id`, `criterion_text`, `criterion_detail`, `order_index`, `is_individual`, `created_at`, `updated_at`) VALUES
(8, 1, 'Modules and Features', '[\"Modules and features are missing, non-functional, or incomplete.\",\"Core modules present and functional but may lack advanced or complete features.\",\"All modules and features are fully functional and demonstrate advanced or extended capabilities.\"]', 0, 0, '2025-07-21 09:36:37', '2025-07-21 09:36:37'),
(9, 1, 'User Interface (UI) Design', '[\"UI is hard to use, lacks structure, and does not follow any design principles.\",\"Functional and moderately user-friendly but lacks visual polish and consistency.\",\"Intuitive, professional, visually appealing, responsive, and adheres to usability and design principles.\"]', 1, 0, '2025-07-21 09:36:37', '2025-07-21 09:36:37'),
(10, 1, 'Innovation and Creativity', '[\"\",\"\",\"\"]', 2, 0, '2025-07-21 09:36:37', '2025-07-21 09:36:37'),
(20, 4, 'Table of Contents', '[\"Missing or disorganized.\",\"Mostly consistent.\",\"Complete, consistent, easy to navigate.\"]', 0, 0, '2025-07-21 09:45:09', '2025-07-21 09:45:09'),
(21, 4, 'Acknowledgment', '[\"Informal or irrelevant.\",\"Somewhat formal and relevant.\",\"Formal, well-written, and appropriate.\"]', 1, 0, '2025-07-21 09:45:09', '2025-07-21 09:45:09'),
(22, 4, 'References', '[\"Missing or not in proper format.\",\"APA followed but inconsistently.\",\"APA fully followed and well-organized.\"]', 2, 0, '2025-07-21 09:45:09', '2025-07-21 09:45:09'),
(23, 4, 'Appendices', '[\"Missing or not supportive.\",\"Present and somewhat relevant.\",\"Highly relevant and supportive.\"]', 3, 0, '2025-07-21 09:45:09', '2025-07-21 09:45:09'),
(24, 4, 'Manuscript Layout', '[\"Poor formatting and structure.\",\"Mostly follows academic standards.\",\"Professionally formatted and consistent.\"]', 4, 0, '2025-07-21 09:45:09', '2025-07-21 09:45:09'),
(25, 4, 'Grammar and Fluidity', '[\"Many errors and weak coherence.\",\"Minor issues; decent flow.\",\"Grammatically strong with excellent flow.\"]', 5, 0, '2025-07-21 09:45:09', '2025-07-21 09:45:09'),
(26, 3, 'Relevance of Introduction', '[\"Lacks relevance or is disconnected.\",\"Relevant and provides sufficient background.\",\"Highly relevant, compelling, and comprehensive.\"]', 0, 0, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(27, 3, 'Clarity of Objectives', '[\"Objectives unclear or poorly stated.\",\"Clear and defined.\",\"Exceptionally clear, specific, and integrated.\"]', 1, 0, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(28, 3, 'Relevance of Literature', '[\"Outdated or irrelevant.\",\"Mostly relevant and updated.\",\"Comprehensive, current, and well-integrated.\"]', 2, 0, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(29, 3, 'Critical Analysis of Literature', '[\"Lacks critical evaluation.\",\"Demonstrates basic synthesis.\",\"Deep analysis with meaningful integration.\"]', 3, 0, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(30, 3, 'Appropriateness of Methodology', '[\"Poorly described or irrelevant.\",\"Appropriate and sufficiently described.\",\"Clearly justified, highly appropriate.\"]', 4, 0, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(31, 3, 'Alignment with Objectives', '[\"Methodology does not align.\",\"Some alignment with objectives.\",\"Strong, justified alignment.\"]', 5, 0, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(32, 3, 'Clarity of Results', '[\"Results unclear or incomplete.\",\"Adequately clear and complete.\",\"Clearly presented and comprehensive.\"]', 6, 0, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(33, 3, 'Depth of Discussion', '[\"Superficial with limited insight.\",\"Moderately insightful.\",\"Thorough, insightful, and critically evaluates findings.\"]', 7, 0, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(34, 3, 'Relevance of Conclusions', '[\"Vague or unsupported conclusions.\",\"Supported by results.\",\"Clear, relevant, and strongly supported.\"]', 8, 0, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(35, 3, 'Practicality of Recommendations', '[\"Impractical or irrelevant.\",\"Feasible and related to findings.\",\"Highly practical and forward-looking.\"]', 9, 0, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(40, 5, '1.	The student has passed due to demonstrating mastery in presenting the research findings with clarity in conveying the findings, conclusions, and recommendations; providing ', NULL, 0, 1, '2025-07-21 09:48:20', '2025-07-21 09:48:20'),
(41, 5, 'The student has failed due to lack of mastery in presenting the research findings, unclear delivery of the findings, conclusions, and recommendations; inability to respond effectively to the examiners’ inquiries;', NULL, 1, 1, '2025-07-21 09:48:20', '2025-07-21 09:48:20'),
(42, 5, 'empty', NULL, 2, 1, '2025-07-21 09:48:20', '2025-07-21 09:48:20'),
(43, 5, 'empty again', NULL, 3, 1, '2025-07-21 09:48:20', '2025-07-21 09:48:20'),
(45, 2, '', '[\"\",\"\",\"\"]', 0, 0, '2025-07-21 13:25:50', '2025-07-21 13:25:50'),
(48, 7, 'a', '[\"a\",\"a\",\"a\"]', 0, 0, '2025-07-21 13:27:25', '2025-07-21 13:27:25'),
(49, 7, 'b', '[\"b\",\"b\",\"b\"]', 1, 0, '2025-07-21 13:27:25', '2025-07-21 13:27:25');

-- --------------------------------------------------------

--
-- Table structure for table `rubric_groups`
--

CREATE TABLE `rubric_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubric_groups`
--

INSERT INTO `rubric_groups` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Information Technology and Computer Science', '(RE-PRESENTATION)', '2025-07-21 09:50:27', '2025-07-21 13:30:27');

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
(30, 1, 1, 0, 30.00, '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(31, 1, 2, 1, 20.00, '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(32, 1, 3, 2, 20.00, '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(33, 1, 4, 3, 10.00, '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(34, 1, 5, 4, 20.00, '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(35, 1, 6, 5, NULL, '2025-07-21 13:30:27', '2025-07-21 13:30:27');

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
(7, 1, 1, 'Unacceptable', '', 1, 3, 1, '2025-07-21 09:36:37', '2025-07-21 09:36:37'),
(8, 1, 2, 'Acceptable ', '', 4, 7, 1, '2025-07-21 09:36:37', '2025-07-21 09:36:37'),
(9, 1, 3, 'Excellent ', '', 8, 10, 1, '2025-07-21 09:36:37', '2025-07-21 09:36:37'),
(13, 4, 1, 'Unacceptable ', '', 0, 1, 1, '2025-07-21 09:45:09', '2025-07-21 09:45:09'),
(14, 4, 2, 'Acceptable ', '', 2, 3, 1, '2025-07-21 09:45:09', '2025-07-21 09:45:09'),
(15, 4, 3, 'Exemplary ', '', 4, 5, 1, '2025-07-21 09:45:09', '2025-07-21 09:45:09'),
(16, 3, 1, 'Unacceptable', '', 0, 1, 1, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(17, 3, 2, 'Acceptable ', '', 2, 3, 1, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(18, 3, 3, 'Exemplary ', '', 4, 5, 1, '2025-07-21 09:45:42', '2025-07-21 09:45:42'),
(20, 5, 1, 'Level 1', '', 1, 1, 0, '2025-07-21 09:48:20', '2025-07-21 09:48:20'),
(24, 6, 1, 'Pass Option 1', 'without revision; 100% acceptability  ', NULL, NULL, 0, '2025-07-21 09:53:02', '2025-07-21 09:53:02'),
(25, 6, 2, 'Pass Option 2', 'with minor revision(s); 75-99.99% acceptability; refer to evaluation sheet', NULL, NULL, 0, '2025-07-21 09:53:02', '2025-07-21 09:53:02'),
(26, 6, 3, 'Pass Option 3', 'with major revisions; 65-74.99% acceptability; for re -presentation', NULL, NULL, 0, '2025-07-21 09:53:02', '2025-07-21 09:53:02'),
(30, 2, 1, 'Unacceptable ', '', 1, 3, 1, '2025-07-21 13:25:50', '2025-07-21 13:25:50'),
(31, 2, 2, 'Acceptable ', '', 4, 7, 1, '2025-07-21 13:25:50', '2025-07-21 13:25:50'),
(32, 2, 3, 'Excellent ', '', 8, 10, 1, '2025-07-21 13:25:50', '2025-07-21 13:25:50'),
(37, 7, 1, 'Level 1', '', 5, 6, 1, '2025-07-21 13:27:25', '2025-07-21 13:27:25'),
(38, 7, 2, 'Level 2', '', 3, 4, 1, '2025-07-21 13:27:25', '2025-07-21 13:27:25'),
(39, 7, 3, 'Level 3', '', 1, 2, 1, '2025-07-21 13:27:25', '2025-07-21 13:27:25');

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
(6, 'Bachelor of Science in Information Technology - Web and Mobile Technology');

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
('sched_69207adce3bcc0.47366559', 'completed', 'Schedule generated and saved successfully!', 100, '2025-11-21 14:44:44', '2025-11-21 14:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `program` varchar(255) NOT NULL,
  `area_of_expertise` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `created_at`, `program`, `area_of_expertise`) VALUES
(4, 'Team Innovate', '2025-11-20 23:16:36', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev'),
(5, 'Team Horizon', '2025-11-20 23:16:36', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Cybersecurity'),
(6, 'Team Quantum', '2025-11-20 23:16:36', 'Bachelor of Science in Computer Science - Software Engineering', 'AI & Data Science'),
(7, 'Team Nexus', '2025-11-20 23:16:36', 'Bachelor of Science in Computer Science - Software Engineering', 'Machine Learning');

-- --------------------------------------------------------

--
-- Stand-in structure for view `team_defense_status`
-- (See below for the actual view)
--
CREATE TABLE `team_defense_status` (
`team_id` int(11) unsigned
,`team_name` varchar(100)
,`current_defense_type` varchar(14)
,`approved_titles` bigint(21)
,`completed_evaluations` bigint(21)
,`override_defense_type` enum('title_proposal','title_defense','final_defense','re-defense')
,`override_active` tinyint(1)
);

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
(14, 4, 280, 'adviser'),
(15, 4, 287, 'leader'),
(16, 4, 288, 'member'),
(17, 4, 289, 'member'),
(18, 4, 290, 'member'),
(20, 5, 291, 'leader'),
(21, 5, 292, 'member'),
(22, 5, 293, 'member'),
(23, 5, 294, 'member'),
(25, 6, 295, 'leader'),
(26, 6, 296, 'member'),
(27, 6, 297, 'member'),
(28, 6, 298, 'member'),
(30, 7, 299, 'leader'),
(31, 7, 300, 'member'),
(32, 7, 301, 'member'),
(33, 7, 302, 'member'),
(34, 7, 280, 'adviser'),
(35, 5, 283, 'adviser'),
(37, 8, 280, 'adviser'),
(38, 6, 285, 'adviser'),
(39, 9, 282, 'adviser');

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

--
-- Dumping data for table `team_requirements`
--

INSERT INTO `team_requirements` (`id`, `team_id`, `requirement_id`, `status`, `submitted_at`, `feedback`, `file_name`, `feedback_file`) VALUES
(1, 1, 3, 'submitted', '2025-11-06 12:46:47', NULL, '1_3_1762433207_5-Drugs-for-Asthma.pdf', NULL),
(3, 1, 5, 'pending', '2025-07-21 09:19:02', '', '1_5_1753089542_FULL_MANUSCRIPT_template_crd2025.pdf', ''),
(6, 1, 4, 'submitted', '2025-07-21 13:07:31', NULL, '1_4_1753103251_1_3_1753088960_687d0244896eb_1753023044_2_.docx', NULL),
(7, 1, 41, 'submitted', '2025-07-21 13:07:39', NULL, '1_41_1753103259_1_3_1753088960_687d0244896eb_1753023044_2_.docx', NULL),
(8, 1, 2, 'submitted', '2025-07-22 16:25:58', 'nice', '1_2_1753201558_system-flow.pdf', 'feedback-Team 1-Capstone 2-20251106.pdf'),
(14, 4, 46, 'submitted', '2025-11-21 19:22:24', NULL, '4_46_1763752944_Document1.pdf', NULL);

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

--
-- Dumping data for table `team_requirement_files`
--

INSERT INTO `team_requirement_files` (`id`, `team_id`, `requirement_id`, `file_name`, `original_file_name`, `file_path`, `file_size`, `submission_number`, `status`, `feedback`, `feedback_file`, `submitted_by`, `submitted_at`, `updated_at`, `deleted_at`) VALUES
(6, 4, 46, '4_46_1763752944_Document1.pdf', 'Document1.pdf', '/opt/lampp/htdocs/home/includes/../../assets/uploads/submission/4_46_1763752944_Document1.pdf', 4548364, 1, 'submitted', NULL, NULL, 287, '2025-11-21 19:22:24', '2025-11-21 19:22:24', NULL);

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

--
-- Dumping data for table `thesis_topics`
--

INSERT INTO `thesis_topics` (`id`, `topic`, `description`, `category`, `created_at`) VALUES
(1, 'a', ' a', 'a', '2025-07-21 13:03:36');

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
  `year` int(1) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `usertype`, `username`, `program`, `area_of_expertise`, `is_parttime`, `email`, `password`, `first_name`, `last_name`, `gender`, `headline`, `bio`, `profile_image`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`, `is_program_chair`, `year`, `section`) VALUES
(0, 0, 'Admin', 'Master in Business Administration', '', 0, 'ton.agustin09@gmail.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Winston', 'Agustin', 'm', 'SUPER ADMIN', '', '67fccf5d724c92.92568803.png', '2024-10-05 05:55:38', '2024-10-05 05:55:38', '2025-11-21 15:01:21', '0000-00-00 00:00:00', '2025-11-21 15:01:21', 0, NULL, NULL),
(267, 1, 'student1', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student1@lpunetwork.edu.ph', '$2y$10$j13zgjmiWnaN3Vw5HjKjm.iqZoBH8fuHGx1MxDBZqWUsChi9koKSW', 'Example', 'One', NULL, 'a', 'a', 'profile_690c9a896fffe9.39118562.gif', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-06 12:55:11', NULL, '2025-11-06 12:37:41', NULL, 4, 'IT401'),
(268, 1, 'student2', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student2@lpunetwork.edu.ph', '$2y$10$Ggm2Jo3kYZpazx29LW/Fdea52tRW3cgRCrY3AV2j6nDThbUmqLSIe', 'Example', 'Two', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-09-01 15:31:55', NULL, '2025-07-22 19:48:49', NULL, 4, 'IT401'),
(269, 2, 'CCS-IT-01', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 0, 'teacher1@lpu.edu.ph', '$2y$10$dDLdwhy2MzpJKXfp98CeE.TV3ChOHpHIvTWZy1Ffkc7xsJhj0o0hK', 'Adviser', 'One', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-21 00:12:11', NULL, '2025-11-06 06:58:50', NULL, NULL, NULL),
(270, 2, 'CCS-IT-02', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 0, 'teacher2@lpu.edu.ph', '$2y$10$0ZKGSjL2n/TDJJjWDlNQ4euoT/Ej7sqjjifsd7fTP7IQpgWGBNvR2', 'Teacher', 'Two', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-21 18:20:40', NULL, '2025-11-21 18:20:40', NULL, NULL, NULL),
(271, 2, 'CCS-IT-03', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 0, 'teacher3@lpu.edu.ph', '$2y$10$7gglTWLQSErKoILKfiCj3uC6GoMs28PyMwcnKYyI1JYq.gSGwNnCm', 'Teacher', 'Three', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-21 00:12:11', NULL, '2025-11-20 17:53:34', NULL, NULL, NULL),
(272, 2, 'CCS-CS-01', 'Bachelor of Science in Computer Science', 'Web Dev', 0, 'teacher4@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Teacher', 'Four', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-21 00:59:24', NULL, '2025-11-21 00:22:49', NULL, NULL, NULL),
(273, 0, 'CCS-IT', 'Bachelor of Science in Information Technology', '', 0, 'it.programchair@lpu.edu.ph', '$2y$10$s.h4./g96wR0jfV1L3qbqOkiaQY8uu0dTaFVJgZoLeKIlR1PF7.qS', 'Program Chair', 'IT', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-21 15:09:58', NULL, '2025-11-21 15:09:58', 1, NULL, NULL),
(277, 2, 'CCS-IT-04', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Systems Dev', 0, 'marc.santiago@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Marc', 'Santiago', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:12:11', NULL, '2025-11-20 22:48:52', NULL, NULL, NULL),
(278, 2, 'CCS-IT-05', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Cybersecurity', 0, 'louise.torres@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Louise', 'Torres', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:12:11', NULL, NULL, NULL, NULL, NULL),
(279, 2, 'CCS-IT-06', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Networking', 1, 'jared.cruz@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Jared', 'Cruz', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:12:11', NULL, NULL, NULL, NULL, NULL),
(280, 2, 'CCS-IT-07', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'UI/UX', 0, 'kimberly.reyes@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Kimberly', 'Reyes', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 19:13:44', NULL, '2025-11-21 19:13:44', NULL, NULL, NULL),
(281, 2, 'CCS-IT-08', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Database Systems', 0, 'francis.lopez@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Francis', 'Lopez', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:12:11', NULL, NULL, NULL, NULL, NULL),
(282, 2, 'CCS-CS-02', 'Bachelor of Science in Computer Science', 'Machine Learning', 0, 'harold.espinosa@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Harold', 'Espinosa', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:59:43', NULL, NULL, NULL, NULL, NULL),
(283, 2, 'CCS-CS-03', 'Bachelor of Science in Computer Science', 'Algorithms', 0, 'ivy.marquez@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Ivy', 'Marquez', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 01:01:46', NULL, NULL, NULL, NULL, NULL),
(284, 2, 'CCS-CS-04', 'Bachelor of Science in Computer Science', 'AI Research', 1, 'renzo.castillo@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Renzo', 'Castillo', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 01:01:42', NULL, NULL, NULL, NULL, NULL),
(285, 2, 'CCS-CS-05', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Data Science', 0, 'mika.soriano@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Mika', 'Soriano', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 01:00:57', NULL, NULL, NULL, NULL, NULL),
(286, 2, 'CCS-IT-09', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'DevOps', 0, 'patrick.valdez@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Patrick', 'Valdez', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:12:11', NULL, NULL, NULL, NULL, NULL),
(287, 1, '2022-2-01001', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01001@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Allen', 'Rivera', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 20:32:22', NULL, '2025-11-21 20:32:22', NULL, 4, 'IT401'),
(288, 1, '2022-2-01002', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01002@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Hannah', 'Flores', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:13:59', NULL, NULL, NULL, 4, 'IT401'),
(289, 1, '2022-2-01003', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'student01003@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Jake', 'Manalo', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:58:59', NULL, NULL, NULL, 4, 'CS401'),
(290, 1, '2022-2-01004', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'student01004@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Sophia', 'Quinto', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:59:02', NULL, NULL, NULL, 4, 'CS401'),
(291, 1, '2022-2-01005', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01005@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Carl', 'Domingo', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:14:06', NULL, NULL, NULL, 4, 'IT401'),
(292, 1, '2022-2-01006', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01006@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Alexa', 'Marin', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 01:00:53', NULL, NULL, NULL, 4, 'IT401'),
(293, 1, '2022-2-01007', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'student01007@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Noel', 'Bartolome', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:59:05', NULL, NULL, NULL, 4, 'CS401'),
(294, 1, '2022-2-01008', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01008@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Steph', 'Navarro', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-20 22:50:44', NULL, NULL, NULL, 4, 'IT401'),
(295, 1, '2022-2-01009', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01009@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Gian', 'Dela Cruz', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 01:00:51', NULL, NULL, NULL, 4, 'IT401'),
(296, 1, '2022-2-01010', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01010@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Karen', 'Santos', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 01:00:49', NULL, NULL, NULL, 4, 'IT401'),
(297, 1, '2022-2-01011', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'student01011@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Cyrill', 'Amante', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:59:08', NULL, NULL, NULL, 4, 'CS401'),
(298, 1, '2022-2-01012', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'student01012@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Lara', 'Ignacio', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:59:10', NULL, NULL, NULL, 4, 'CS401'),
(299, 1, '2022-2-01013', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01013@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Joshua', 'Villena', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 16:29:40', NULL, '2025-11-21 16:29:40', NULL, 4, 'IT401'),
(300, 1, '2022-2-01014', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01014@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Ariana', 'Lim', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 01:00:44', NULL, NULL, NULL, 4, 'IT401'),
(301, 1, '2022-2-01015', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'student01015@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Felix', 'Ordoña', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:59:12', NULL, NULL, NULL, 4, 'CS401'),
(302, 1, '2022-2-01016', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'student01016@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Ruby', 'Mendoza', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:59:16', NULL, NULL, NULL, 4, 'CS401'),
(303, 1, '2022-2-01017', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01017@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Kenji', 'Salazar', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 01:00:41', NULL, NULL, NULL, 4, 'IT401'),
(304, 1, '2022-2-01018', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01018@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Elaine', 'Castro', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-20 22:50:44', NULL, NULL, NULL, 4, 'IT401'),
(305, 1, '2022-2-01019', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'student01019@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Ralph', 'Gomez', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:59:19', NULL, NULL, NULL, 4, 'CS401'),
(306, 1, '2022-2-01020', 'Bachelor of Science in Information Technology', '', 0, 'student01020@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Bianca', 'Serrano', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:14:26', NULL, NULL, NULL, 4, 'IT401');

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
  `year` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_schedules`
--

INSERT INTO `user_schedules` (`id`, `user_id`, `program`, `section`, `room`, `day_of_week`, `start_time`, `end_time`, `class_name`, `year`) VALUES
(127, 269, '80', 'IT401', 'R101', 'Monday', '08:00:00', '11:00:00', 'Web Development', 4),
(128, 270, '80', 'IT401', 'R102', 'Monday', '11:30:00', '14:30:00', 'Database Systems', 4),
(129, 271, '80', 'IT401', 'R103', 'Monday', '15:00:00', '18:00:00', 'Systems Development', 4),
(130, 277, '80', 'IT401', 'R101', 'Tuesday', '08:00:00', '11:00:00', 'Cybersecurity', 4),
(131, 278, '80', 'IT401', 'R102', 'Tuesday', '11:30:00', '14:30:00', 'Networking', 4),
(132, 279, '80', 'IT401', 'R103', 'Tuesday', '15:00:00', '18:00:00', 'UI/UX Design', 4),
(133, 280, '80', 'IT401', 'R101', 'Wednesday', '08:00:00', '11:00:00', 'DevOps', 4),
(134, 281, '80', 'IT401', 'R102', 'Wednesday', '11:30:00', '14:30:00', 'Project Management', 4),
(135, 286, '80', 'IT401', 'R103', 'Wednesday', '15:00:00', '18:00:00', 'Cloud Computing', 4),
(136, 282, '78', 'CS401', 'R201', 'Monday', '08:00:00', '11:00:00', 'Machine Learning', 4),
(137, 283, '78', 'CS401', 'R202', 'Monday', '11:30:00', '14:30:00', 'Algorithms', 4),
(138, 284, '78', 'CS401', 'R203', 'Monday', '15:00:00', '18:00:00', 'AI Research', 4),
(139, 285, '78', 'CS401', 'R201', 'Tuesday', '08:00:00', '11:00:00', 'Data Science', 4),
(140, 272, '78', 'CS401', 'R202', 'Tuesday', '11:30:00', '14:30:00', 'Software Design', 4);

-- --------------------------------------------------------

--
-- Structure for view `program_manuscript_view`
--
DROP TABLE IF EXISTS `program_manuscript_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `program_manuscript_view`  AS SELECT `pmr`.`id` AS `mapping_id`, `r`.`id` AS `requirement_id`, `r`.`name` AS `requirement_name`, `r`.`is_defense_manuscript` AS `is_defense_manuscript`, `p`.`id` AS `program_id`, `p`.`name` AS `program_name`, `pmr`.`defense_type` AS `defense_type`, `pmr`.`is_required` AS `is_required`, `pmr`.`submission_stage` AS `submission_stage`, `pmr`.`can_revise_after` AS `can_revise_after`, `pmr`.`visibility_to_panelist` AS `visibility_to_panelist` FROM ((`program_manuscript_requirements` `pmr` join `requirements` `r` on(`pmr`.`requirement_id` = `r`.`id`)) join `programs` `p` on(`pmr`.`program_id` = `p`.`id`)) WHERE `r`.`is_defense_manuscript` = 1 ORDER BY `p`.`name` ASC, field(`pmr`.`defense_type`,'title_proposal','title_defense','final_defense','re-defense','general') ASC, `r`.`name` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `team_defense_status`
--
DROP TABLE IF EXISTS `team_defense_status`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `team_defense_status`  AS SELECT `t`.`id` AS `team_id`, `t`.`name` AS `team_name`, CASE WHEN `dto`.`active` = 1 THEN `dto`.`override_type` WHEN count(distinct `ep`.`id`) >= 2 THEN 'final_defense' WHEN count(distinct `rt`.`id`) > 0 THEN 'title_defense' ELSE 'title_proposal' END AS `current_defense_type`, count(distinct `rt`.`id`) AS `approved_titles`, count(distinct `ep`.`id`) AS `completed_evaluations`, max(`dto`.`override_type`) AS `override_defense_type`, max(`dto`.`active`) AS `override_active` FROM ((((`teams` `t` left join `research_titles` `rt` on(`t`.`id` = `rt`.`team_id` and `rt`.`approved_at` is not null)) left join `defense_schedules` `ds` on(`t`.`id` = `ds`.`team_id`)) left join `evaluation_per_panel` `ep` on(`ds`.`id` = `ep`.`defense_schedule_id` and `ep`.`created_at` is not null)) left join `defense_type_overrides` `dto` on(`t`.`id` = `dto`.`team_id` and `dto`.`active` = 1)) GROUP BY `t`.`id` ;

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
  ADD KEY `panelist_id` (`panelist_id`),
  ADD KEY `panelist_id2` (`panelist_id2`),
  ADD KEY `panelist_id3` (`panelist_id3`),
  ADD KEY `fk_defense_schedules_team` (`team_id`),
  ADD KEY `idx_defense_schedules_approval` (`approval_status`),
  ADD KEY `idx_defense_type` (`defense_type`);

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
  ADD UNIQUE KEY `rubric_criterion_order` (`rubric_id`,`order_index`) COMMENT 'Ensure unique order per rubric',
  ADD KEY `rubric_id` (`rubric_id`);

--
-- Indexes for table `rubric_groups`
--
ALTER TABLE `rubric_groups`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `rubric_level_order` (`rubric_id`,`level_index`) COMMENT 'Ensure unique order per rubric',
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
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `defense_panelists`
--
ALTER TABLE `defense_panelists`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `defense_type_overrides`
--
ALTER TABLE `defense_type_overrides`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `env_variables`
--
ALTER TABLE `env_variables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluation_details`
--
ALTER TABLE `evaluation_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `evaluation_per_panel`
--
ALTER TABLE `evaluation_per_panel`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `form_assignments`
--
ALTER TABLE `form_assignments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `merged_evaluations`
--
ALTER TABLE `merged_evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `notification_actions`
--
ALTER TABLE `notification_actions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `panelist_approvals`
--
ALTER TABLE `panelist_approvals`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `program_manuscript_requirements`
--
ALTER TABLE `program_manuscript_requirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `program_requirements_mapping`
--
ALTER TABLE `program_requirements_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requirements`
--
ALTER TABLE `requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `research_titles`
--
ALTER TABLE `research_titles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `re_defense_assessments`
--
ALTER TABLE `re_defense_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rubrics`
--
ALTER TABLE `rubrics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `rubric_criteria`
--
ALTER TABLE `rubric_criteria`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `rubric_groups`
--
ALTER TABLE `rubric_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rubric_group_items`
--
ALTER TABLE `rubric_group_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `rubric_levels`
--
ALTER TABLE `rubric_levels`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `team_panelists`
--
ALTER TABLE `team_panelists`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_requirements`
--
ALTER TABLE `team_requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `team_requirement_files`
--
ALTER TABLE `team_requirement_files`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `thesis_topics`
--
ALTER TABLE `thesis_topics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- AUTO_INCREMENT for table `user_schedules`
--
ALTER TABLE `user_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

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
-- Constraints for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  ADD CONSTRAINT `defense_schedules_ibfk_2` FOREIGN KEY (`panelist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `defense_schedules_ibfk_3` FOREIGN KEY (`panelist_id2`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `defense_schedules_ibfk_4` FOREIGN KEY (`panelist_id3`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_defense_schedules_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
