-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 24, 2025 at 02:02 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coecsa_thesis`
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
(1, 'winstonagustin.ih@gmail.com', 'account_verify', '613f4c35ee6dac46', '$2y$10$fGDz8SdTBADhULRbmpcauORjPUc1tD.JsKCldb72Z.uQFaej5PdG.', '2025-07-22 05:07:15', '2025-07-22 21:07:15');

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
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval_status` enum('approved','rejected','pending') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(13, 'MAIL_PASSWORD', 'oflo arms thzh jlss', 'Mail password'),
(14, 'MAIL_ENCRYPTION', 'ssl', 'Mail encryption'),
(15, 'MAIL_PORT', '465', 'Mail port'),
(16, 'APP_LOGO_NAVBAR', 'logo_full_lightbg.png', NULL),
(17, 'APP_LOGO_FOOTER', 'logowhite.png', NULL);

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
(115, 8, 5, 43, 268, 1, NULL, NULL, '2025-08-23 06:08:46', '2025-08-23 06:08:46');

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
(8, 6, 270, 268, 65, 2, 67, '123asd', '2025-08-23 06:08:46', '2025-08-23 06:08:46');

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
(26, 268, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for August 23, 2025 at 7:00 AM - 8:00 AM in 1. Waiting for panelist approval.', 6, NULL, 0, '2025-08-23 06:07:25', '2025-08-23 06:07:25');

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
(12, 23, 'reject_defense', '{\"schedule_id\":\"6\"}', 0, NULL, '2025-08-23 06:07:25');

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
(15, 6, 272, 'pending', NULL, NULL, '2025-08-23 06:07:25');

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
(58, 'College of Allied Medical Sciences', NULL, 'BS Medical Technology', NULL, '2025-04-29 09:02:55'),
(59, 'College of Allied Medical Sciences', '', 'BS Pharmacy', '', '2025-07-21 13:12:37'),
(60, 'College of Allied Medical Sciences', NULL, 'BS Radiologic Technology', NULL, '2025-04-29 09:02:55'),
(61, 'College of Allied Medical Sciences', NULL, 'BS Biology', NULL, '2025-04-29 09:02:55'),
(62, 'College of Liberal Arts and Education', NULL, 'Bachelor of Arts in Communication', NULL, '2025-04-29 09:02:55'),
(63, 'College of Liberal Arts and Education', NULL, 'AB Foreign Service', NULL, '2025-04-29 09:02:55'),
(64, 'College of Liberal Arts and Education', NULL, 'AB Legal Studies', NULL, '2025-04-29 09:02:55'),
(65, 'College of Liberal Arts and Education', NULL, 'Bachelor of Early Childhood Education', NULL, '2025-04-29 09:02:55'),
(66, 'College of Liberal Arts and Education', NULL, 'Bachelor in Secondary Education', NULL, '2025-04-29 09:02:55'),
(67, 'College of Liberal Arts and Education', NULL, 'BS Psychology', NULL, '2025-04-29 09:02:55'),
(68, 'College of Business Administration', NULL, 'BS Accountancy', NULL, '2025-04-29 09:02:55'),
(69, 'College of Business Administration', NULL, 'BS Business Administration', 'Human Resource Development Management', '2025-04-29 09:02:55'),
(70, 'College of Business Administration', NULL, 'BS Business Administration', 'Management Accounting', '2025-04-29 09:02:55'),
(71, 'College of Business Administration', NULL, 'BS Business Administration', 'Marketing Management', '2025-04-29 09:02:55'),
(72, 'College of Business Administration', NULL, 'BS Business Administration', 'Operations Management', '2025-04-29 09:02:55'),
(73, 'College of Business Administration', NULL, 'BS Customs Administration', NULL, '2025-04-29 09:02:55'),
(74, 'College of Business Administration', NULL, 'BS Entrepreneurship', 'Aesthetics Industry Management', '2025-04-29 09:02:55'),
(75, 'College of Business Administration', NULL, 'BS Real Estate Management', NULL, '2025-04-29 09:02:55'),
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
(95, 'College of International Tourism and Hospitality Management', NULL, 'BS International Travel and Tourism Management', NULL, '2025-04-29 09:02:55'),
(96, 'College of International Tourism and Hospitality Management', NULL, 'BS International Travel and Tourism Management', 'Health and Wellness', '2025-04-29 09:02:55'),
(97, 'College of International Tourism and Hospitality Management', NULL, 'BS International Hospitality Management', 'Cruise Line Operations in Culinary Arts', '2025-04-29 09:02:55'),
(98, 'College of International Tourism and Hospitality Management', NULL, 'BS International Hospitality Management', 'Cruise Line Operations in Hotel Services', '2025-04-29 09:02:55'),
(99, 'College of International Tourism and Hospitality Management', NULL, 'BS International Hospitality Management', 'Culinary Arts and Kitchen Operations', '2025-04-29 09:02:55'),
(100, 'College of International Tourism and Hospitality Management', NULL, 'BS International Hospitality Management', 'Hotel and Restaurant Administration', '2025-04-29 09:02:55'),
(101, 'College of International Tourism and Hospitality Management', NULL, 'BS Nutrition and Dietetics', NULL, '2025-04-29 09:02:55'),
(102, 'College of Nursing', NULL, 'BS Nursing', NULL, '2025-04-29 09:02:55'),
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
-- Table structure for table `requirements`
--

CREATE TABLE `requirements` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `template_file` varchar(255) DEFAULT NULL,
  `template_original_name` varchar(255) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requirements`
--

INSERT INTO `requirements` (`id`, `name`, `description`, `template_file`, `template_original_name`, `due_date`, `created_at`) VALUES
(2, 'Capstone 2', 'This includes the template', '687d025450e81_1753023060.docx', 'CAPSTONE 1-2 TEMPLATES.docx', '2024-10-31', '2024-11-11 10:52:54'),
(3, 'Capstone 1', 'This includes the template\r\n(IT ONLY)', '687d0244896eb_1753023044.docx', 'CAPSTONE 1-2 TEMPLATES.docx', '2024-11-30', '2024-11-11 10:52:41'),
(5, 'Final Manuscript', 'Also used for Research Repository (DO NOT REMOVE)', '687d0263dbbb8_1753023075.docx', 'FULL MANUSCRIPT_template_crd2025.docx', '2024-12-04', '2024-11-11 10:52:22');

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
(1, 1, 'Title of Team 1', '', '2025-07-22 19:48:15', NULL, '2025-07-21 09:05:57', '2025-07-22 19:48:15');

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
(1, 'Team 1', '2025-07-21 09:05:57', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev');

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
(1, 1, 269, 'adviser'),
(2, 1, 267, 'leader'),
(3, 1, 268, 'member');

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
(1, 1, 3, 'pending', '2025-07-21 09:09:20', '', '1_3_1753088960_687d0244896eb_1753023044_2_.docx', ''),
(3, 1, 5, 'pending', '2025-07-21 09:19:02', '', '1_5_1753089542_FULL_MANUSCRIPT_template_crd2025.pdf', ''),
(6, 1, 4, 'submitted', '2025-07-21 13:07:31', NULL, '1_4_1753103251_1_3_1753088960_687d0244896eb_1753023044_2_.docx', NULL),
(7, 1, 41, 'submitted', '2025-07-21 13:07:39', NULL, '1_41_1753103259_1_3_1753088960_687d0244896eb_1753023044_2_.docx', NULL),
(8, 1, 2, 'approved', '2025-07-22 16:25:58', 'nice', '1_2_1753201558_system-flow.pdf', '');

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
  `is_program_chair` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `usertype`, `username`, `program`, `area_of_expertise`, `is_parttime`, `email`, `password`, `first_name`, `last_name`, `gender`, `headline`, `bio`, `profile_image`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`, `is_program_chair`) VALUES
(0, 0, 'Admin', NULL, NULL, NULL, 'winston.agustin@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Winston', 'Agustin', 'm', 'SUPER ADMIN', '', '67fccf5d724c92.92568803.png', '2024-10-05 05:55:38', '2024-10-05 05:55:38', '2025-08-23 05:54:23', '0000-00-00 00:00:00', '2025-08-23 05:54:23', NULL),
(267, 1, 'student1', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student1@lpunetwork.edu.ph', '$2y$10$j13zgjmiWnaN3Vw5HjKjm.iqZoBH8fuHGx1MxDBZqWUsChi9koKSW', 'Example', 'One', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-08-23 06:01:35', NULL, '2025-08-23 06:01:35', NULL),
(268, 1, 'student2', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student2@lpunetwork.edu.ph', '$2y$10$Ggm2Jo3kYZpazx29LW/Fdea52tRW3cgRCrY3AV2j6nDThbUmqLSIe', 'Example', 'Two', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-07-22 19:48:49', NULL, '2025-07-22 19:48:49', NULL),
(269, 2, 'CCS-IT-01', 'Bachelor of Science in Information Technology', 'Web Dev', 0, 'teacher1@lpu.edu.ph', '$2y$10$dDLdwhy2MzpJKXfp98CeE.TV3ChOHpHIvTWZy1Ffkc7xsJhj0o0hK', 'Adviser', 'One', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-08-23 08:26:12', NULL, '2025-08-21 03:34:53', NULL),
(270, 2, 'CCS-IT-02', 'Bachelor of Science in Information Technology', 'Web Dev', 0, 'teacher2@lpu.edu.ph', '$2y$10$0ZKGSjL2n/TDJJjWDlNQ4euoT/Ej7sqjjifsd7fTP7IQpgWGBNvR2', 'Teacher', 'Two', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-08-23 08:26:08', NULL, '2025-08-23 06:04:28', NULL),
(271, 2, 'CCS-IT-03', 'Bachelor of Science in Information Technology', 'Web Dev', 0, 'teacher3@lpu.edu.ph', '$2y$10$7gglTWLQSErKoILKfiCj3uC6GoMs28PyMwcnKYyI1JYq.gSGwNnCm', 'Teacher', 'Three', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-08-23 08:26:06', NULL, '2025-07-22 19:46:01', NULL),
(272, 2, 'CCS-CS-01', 'Bachelor of Science in Computer Science', 'Web Dev', 0, 'teacher4@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Teacher', 'Four', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-08-23 08:26:26', NULL, '2025-07-22 19:46:18', NULL),
(273, 0, 'CCS-IT', 'Bachelor of Science in Information Technology', '', 0, 'it.programchair@lpu.edu.ph', '$2y$10$s.h4./g96wR0jfV1L3qbqOkiaQY8uu0dTaFVJgZoLeKIlR1PF7.qS', 'Program Chair', 'IT', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-08-23 08:07:13', NULL, '2025-08-23 07:58:20', 1),
(274, 1, '2021-2-01217', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'winstonagustin.ih@gmail.com', '$2y$10$JMQY5E6kK5YjXU8ffa5jPOTMxUFV7U9t7D62pJR5M2FpduRLJq6iO', 'REGIL KENT', 'ANTONIO', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-07-22 05:07:11', NULL, '2025-07-22 05:07:11', NULL),
(275, 2, 'winstonadmina', 'Unspecified', 'Web Dev', 1, 'jk2o4gq65@mozmail.com', '$2y$10$520iKpeTou75C60zH6aQFOUFg4FEGA4tJCNFimuyHAnQ6XG5MLQXq', 'a', 'a', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-07-22 18:54:09', NULL, '2025-07-22 18:54:09', NULL);

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
  `class_name` varchar(255) NOT NULL
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
  ADD KEY `panelist_id` (`panelist_id`),
  ADD KEY `panelist_id2` (`panelist_id2`),
  ADD KEY `panelist_id3` (`panelist_id3`),
  ADD KEY `fk_defense_schedules_team` (`team_id`),
  ADD KEY `idx_defense_schedules_approval` (`approval_status`);

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
-- Indexes for table `team_requirements`
--
ALTER TABLE `team_requirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requirement_id` (`requirement_id`),
  ADD KEY `team_id` (`team_id`);

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `defense_panelists`
--
ALTER TABLE `defense_panelists`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `env_variables`
--
ALTER TABLE `env_variables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluation_details`
--
ALTER TABLE `evaluation_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `evaluation_per_panel`
--
ALTER TABLE `evaluation_per_panel`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `notification_actions`
--
ALTER TABLE `notification_actions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `panelist_approvals`
--
ALTER TABLE `panelist_approvals`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `requirements`
--
ALTER TABLE `requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `research_titles`
--
ALTER TABLE `research_titles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `team_requirements`
--
ALTER TABLE `team_requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=276;

--
-- AUTO_INCREMENT for table `user_schedules`
--
ALTER TABLE `user_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
