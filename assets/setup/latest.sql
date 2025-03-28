-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2025 at 06:15 AM
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
(104, 'sean.gono@lpu.edu.ph', 'password_reset', 'ca92c4dad4d8cdbf', '$2y$10$3AkW3EFEvXovTXyIU31nT.GJ.RKd.RKQuAP5l7DjmcNTpZAiJvZHq', '2024-12-07 15:13:46', '2024-12-07 09:13:46'),
(105, 'ton.agustin09@gmail.com', 'remember_me', '0c70140ff6a97d0c', '$2y$10$YaPaGBlc2EzyJgR6vaTC7e0gPMiS/8O4cQpCK5jT2ugAcRHZjti4m', '2024-12-08 01:33:21', '2024-12-17 18:33:21'),
(107, 'neilvicedo.ih@gmail.com', 'remember_me', '93af16f780701c28', '$2y$10$YPAbKoqAgwAso7RD68TCLuHMCKTNmIl2d1jRhFpNC45ELxj15ysKq', '2025-02-18 04:54:06', '2025-02-27 21:54:06');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `defense_schedules`
--

INSERT INTO `defense_schedules` (`id`, `team_id`, `panelist_id`, `panelist_id2`, `panelist_id3`, `schedule_date`, `start_time`, `end_time`, `room`, `status`, `created_at`) VALUES
(1, 1, 61, 65, 62, '2025-03-11', '17:00:00', '19:00:00', 'b', 'scheduled', '2025-03-10 03:34:49'),
(2, 2, 60, 64, 62, '2025-03-11', '19:00:00', '21:00:00', 'b', 'scheduled', '2025-03-10 03:34:49'),
(3, 3, 58, 64, 63, '2025-03-11', '11:00:00', '13:00:00', 'a', 'scheduled', '2025-03-10 03:34:49'),
(4, 4, 61, 62, 65, '2025-03-10', '19:00:00', '21:00:00', 'b', 'scheduled', '2025-03-10 03:34:49'),
(5, 5, 59, 64, 65, '2025-03-10', '13:00:00', '15:00:00', 'b', 'scheduled', '2025-03-10 03:34:49');

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
(1, 'APP_NAME', 'ATLAS', 'Application name'),
(2, 'APP_ORGANIZATION', 'LPU-C CoECSA', 'Organization name'),
(3, 'APP_OWNER', '120ms', 'Application owner'),
(4, 'APP_DESCRIPTION', 'taga schedule', 'Application description'),
(5, 'ALLOWED_INACTIVITY_TIME', '3600', 'Allowed inactivity time in seconds'),
(6, 'DB_DATABASE', 'coecsa_thesis', 'Database name'),
(7, 'DB_HOST', '127.0.0.1', 'Database host'),
(8, 'DB_USERNAME', 'root', 'Database username'),
(9, 'DB_PASSWORD', '', 'Database password'),
(10, 'DB_PORT', '3306', 'Database port'),
(11, 'MAIL_HOST', 'smtp.gmail.com', 'Mail host'),
(12, 'MAIL_USERNAME', 'ton.agustin09@gmail.com', 'Mail username'),
(13, 'MAIL_PASSWORD', 'rdrc cinf leli xdms', 'Mail password'),
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
  `comments` text DEFAULT NULL,
  `recommendation` enum('pass','fail','revise') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_details`
--

CREATE TABLE `evaluation_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `evaluation_id` int(11) UNSIGNED DEFAULT NULL,
  `criterion_id` int(11) UNSIGNED DEFAULT NULL,
  `score` float DEFAULT NULL,
  `comment` text DEFAULT NULL
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluation_per_panel`
--

INSERT INTO `evaluation_per_panel` (`id`, `defense_schedule_id`, `evaluator_id`, `student_id`, `group_score`, `solo_score`, `total_score`, `comments`, `created_at`) VALUES
(13, 4, 59, 48, 60, 40, 100, 'nice', '2024-12-18 00:59:28'),
(14, 4, 59, 49, 60, 40, 100, 'nice', '2024-12-18 00:59:28'),
(15, 4, 59, 50, 60, 40, 100, 'nice', '2024-12-18 00:59:28'),
(49, 1, 59, 38, 51.3333, 40, 91.3333, 'panget mo ilano', '2025-03-08 02:37:46'),
(50, 1, 59, 39, 51.3333, 33, 84.3333, 'panget mo ilano', '2025-03-08 02:37:46'),
(51, 1, 59, 40, 51.3333, 12, 63.3333, 'panget mo ilano', '2025-03-08 02:37:46'),
(52, 1, 59, 41, 51.3333, 5, 56.3333, 'panget mo ilano', '2025-03-08 02:37:46');

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

--
-- Dumping data for table `form_assignments`
--

INSERT INTO `form_assignments` (`id`, `defense_schedule_id`, `embed_link`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, '<iframe src=\"https://docs.google.com/forms/d/e/1FAIpQLSdQ4FkofF2p-7IYqiBviGmhEifLmagOzA3mL7mZP06tNyqjYw/viewform?embedded=true\" width=\"640\" height=\"1000\" frameborder=\"0\" marginheight=\"0\" marginwidth=\"0\">Loading…</iframe>', 1, '2025-03-09 05:22:50', '2025-03-09 05:36:19'),
(2, 5, '<iframe src=\"https://docs.google.com/forms/d/e/1FAIpQLSdGJjmT0gKrHNXFoTwQCQn8CUZ7TCU-xE5vR-j0PUXJx_EUmQ/viewform?embedded=true\" width=\"640\" height=\"1000\" frameborder=\"0\" marginheight=\"0\" marginwidth=\"0\">Loading…</iframe>', 1, '2025-03-09 05:23:14', '2025-03-09 15:54:57'),
(3, 1, '<iframe src=\"https://docs.google.com/forms/d/e/1FAIpQLSdQ4FkofF2p-7IYqiBviGmhEifLmagOzA3mL7mZP06tNyqjYw/viewform?embedded=true\" width=\"640\" height=\"1000\" frameborder=\"0\" marginheight=\"0\" marginwidth=\"0\">Loading…</iframe>', 1, '2025-03-09 05:36:19', '2025-03-09 05:36:19');

-- --------------------------------------------------------

--
-- Table structure for table `requirements`
--

CREATE TABLE `requirements` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requirements`
--

INSERT INTO `requirements` (`id`, `name`, `description`, `due_date`, `created_at`) VALUES
(1, 'Chapter 1', '', '2024-09-30', '2024-11-11 10:53:07'),
(2, 'Chapter 2', '', '2024-10-31', '2024-11-11 10:52:54'),
(3, 'Chapter 3', '', '2024-11-30', '2024-11-11 10:52:41'),
(4, 'Endorsement Letter', '', '2024-11-30', '2024-11-11 10:51:55'),
(5, 'Book bind Copy', '', '2024-12-04', '2024-11-11 10:52:22'),
(41, 'grading sheet', 'grades', '2024-12-13', '2024-12-11 04:12:01');

-- --------------------------------------------------------

--
-- Table structure for table `research_titles`
--

CREATE TABLE `research_titles` (
  `id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `defended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `research_titles`
--

INSERT INTO `research_titles` (`id`, `team_id`, `title`, `approved_at`, `defended_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'ATLAS: ADVANCED THESIS LOGISTICS AND AI SYSTEM FOR THE COLLEGE OF ENGINEERING, COMPUTER STUDIES AND ARCHITECTURE AT LYCEUM OF THE PHILIPPINES UNIVERSITY CAVITE', NULL, '2024-12-16 08:20:54', '2024-10-13 07:15:44', '2025-03-09 11:24:50'),
(2, 2, ' Arcadia: A LIBRARY MANAGEMENTSYSTEMFORLPU  ACADEMICRESOURCECENTERUSINGMACHINE  LEARNINGFORTEXTCLASSIFICATIONAND  RECOMMENDATIONSYSTEMS', '2024-11-16 02:30:00', NULL, '2024-10-13 07:15:44', '2024-12-10 22:46:52'),
(3, 3, 'SOLACE: SMART SYMPTOM MONITORING AND AI PREDICTIVE  INTERVENTION IN PALLIATIVE AND HOSPICE CARE', NULL, NULL, '2024-10-13 07:15:44', '2024-12-17 08:11:15'),
(4, 4, 'ADAPT: AI-DRIVEN CUSTOMIZABLE CHATBOT PLUGIN FOR  ENHANCED USER INTERACTION IN WEB-BASED PLATFORMS  ', '2024-11-18 06:00:00', NULL, '2024-10-13 07:15:44', '2024-12-10 22:48:08'),
(5, 5, 'QUIZSCAN: AUTOMATED HANDWRITTEN ACTIVITY ANSWERS  RECOGNITION FOR TEACHERS USING CNN ALGORITHM', '2024-11-19 07:30:00', NULL, '2024-10-13 07:15:44', '2024-12-10 22:48:45');

-- --------------------------------------------------------

--
-- Table structure for table `rubrics`
--

CREATE TABLE `rubrics` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `max_total_score` int(11) DEFAULT NULL,
  `quality_criteria_count` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`structure`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubrics`
--

INSERT INTO `rubrics` (`id`, `name`, `description`, `max_total_score`, `quality_criteria_count`, `is_active`, `created_by`, `created_at`, `structure`) VALUES
(9, 'test', 'desc', NULL, NULL, 0, NULL, '2024-12-04 19:02:49', '{\"levels\":[\"Level 1\",\"Level 2\",\"Level 3\",\"Level 4\"],\"criteria\":[{\"criterion\":\"cri\",\"levels\":[{\"content\":\"1\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"2\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"3\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"4\",\"rowSpan\":1,\"colSpan\":1}]},{\"criterion\":\"cri2\",\"levels\":[{\"content\":\"1 2\",\"rowSpan\":1,\"colSpan\":2},{\"content\":\"3 4\",\"rowSpan\":1,\"colSpan\":2}]}]}'),
(10, 'Atlas', 'desctop', 11, 3, 0, NULL, '2025-03-15 00:13:47', '{\"levels\":[\"just some\",\"some more\",\"and even more\"],\"criteria\":[{\"criterion\":\"row 1\",\"levels\":[{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1}]},{\"criterion\":\"row 2\",\"levels\":[{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1}]},{\"criterion\":\"row 3\",\"levels\":[{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1}]}]}'),
(11, 'dsadsada', 'fsfddsfs', 8, 2, 0, NULL, '2025-03-15 00:24:32', '{\"levels\":[\"fdsr5gd\",\"fgd46\"],\"criteria\":[{\"criterion\":\"5fg\",\"levels\":[{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1}]},{\"criterion\":\"sfsfs\",\"levels\":[{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"\",\"rowSpan\":1,\"colSpan\":1}]}]}');

-- --------------------------------------------------------

--
-- Table structure for table `rubric_criteria`
--

CREATE TABLE `rubric_criteria` (
  `id` int(11) UNSIGNED NOT NULL,
  `rubric_id` int(11) UNSIGNED DEFAULT NULL,
  `criterion` varchar(255) NOT NULL,
  `max_score` int(11) DEFAULT NULL,
  `weight` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rubric_quality_criteria`
--

CREATE TABLE `rubric_quality_criteria` (
  `id` int(11) UNSIGNED NOT NULL,
  `rubric_id` int(11) UNSIGNED NOT NULL,
  `quality_level` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubric_quality_criteria`
--

INSERT INTO `rubric_quality_criteria` (`id`, `rubric_id`, `quality_level`, `points`, `description`, `created_at`) VALUES
(4, 10, 1, 3, 'just some', '2025-03-15 00:23:51'),
(5, 10, 2, 4, 'some more', '2025-03-15 00:23:51'),
(6, 10, 3, 5, 'and even more', '2025-03-15 00:23:51'),
(11, 11, 1, 5, 'fdsr5gd', '2025-03-15 02:44:07'),
(12, 11, 2, 4, 'fgd468', '2025-03-15 02:44:07');

-- --------------------------------------------------------

--
-- Table structure for table `rubric_rows`
--

CREATE TABLE `rubric_rows` (
  `id` int(11) UNSIGNED NOT NULL,
  `rubric_id` int(11) UNSIGNED NOT NULL,
  `description` text NOT NULL,
  `order_index` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubric_rows`
--

INSERT INTO `rubric_rows` (`id`, `rubric_id`, `description`, `order_index`, `created_at`) VALUES
(3, 10, 'row 1', 0, '2025-03-15 00:23:51'),
(4, 10, 'row 2', 1, '2025-03-15 00:23:51'),
(5, 10, 'row 3', 2, '2025-03-15 00:23:51'),
(10, 11, '5fg', 0, '2025-03-15 02:44:07'),
(11, 11, 'sfsfs', 1, '2025-03-15 02:44:07');

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
(1, '120ms', '2024-10-13 06:58:29', 'Bachelor of Science in Computer Science', NULL),
(2, 'Arcadia', '2024-10-13 06:58:29', 'Bachelor of Science in Computer Science', NULL),
(3, 'Solace', '2024-10-13 06:58:29', 'Bachelor of Science in Computer Science', NULL),
(4, 'Adapt', '2024-10-13 06:58:29', 'Bachelor of Science in Computer Science', NULL),
(5, 'QuizScan', '2024-10-13 06:58:29', 'Bachelor of Science in Computer Science', NULL),
(10, 'test', '2025-03-08 02:42:32', 'a', NULL);

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
(8, 2, 43, 'member'),
(9, 3, 60, 'adviser'),
(12, 3, 46, 'member'),
(15, 4, 48, 'member'),
(16, 4, 49, 'member'),
(19, 5, 51, 'member'),
(20, 5, 52, 'member'),
(21, 6, 63, 'adviser'),
(22, 6, 53, 'leader'),
(23, 6, 54, 'member'),
(24, 6, 55, 'member'),
(25, 7, 64, 'adviser'),
(26, 7, 56, 'leader'),
(27, 7, 57, 'member'),
(69, 1, 58, 'adviser'),
(70, 1, 38, 'leader'),
(71, 1, 39, 'member'),
(72, 1, 40, 'member'),
(74, 1, 41, 'member'),
(76, 2, 42, 'leader'),
(80, 4, 50, 'leader'),
(81, 5, 53, 'leader'),
(82, 2, 59, 'adviser'),
(83, 3, 45, 'leader'),
(85, 2, 44, 'member'),
(86, 2, 54, 'member'),
(87, 5, 55, 'member'),
(88, 4, 60, 'adviser'),
(89, 5, 61, 'adviser'),
(91, 3, 47, 'member');

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
(6, 1, 5, 'submitted', '2024-12-08 10:48:34', '', '120ms-BookbindCopy-20241208.pdf', NULL),
(7, 1, 1, 'submitted', '2024-12-10 08:24:13', '', '120ms-Chapter1-20241210.pdf', NULL),
(8, 4, 5, 'submitted', '2024-12-11 01:52:20', '', 'Adapt-BookbindCopy-20241211.pdf', NULL);

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
(2, 'Sustainable Design and Construction', 'Exploring environmentally friendly materials, energy-efficient building technologies, and sustainable building practices. Reduced environmental impact, improved resource efficiency, and healthier built environments.', 'Architecture', '2024-11-18 07:26:43'),
(3, 'Digital Fabrication and Parametric Design', 'Investigating the use of computational design tools and advanced manufacturing technologies for architectural production. Enhanced design efficiency, increased design complexity, and the exploration of novel architectural forms and materials.', 'Architecture', '2024-11-18 08:57:37'),
(4, 'Adaptive Reuse and Urban Regeneration', 'Researching strategies for revitalizing existing structures and neighborhoods, focusing on sustainability and community engagement. Preservation of historical assets, economic growth in urban areas, and improved quality of life for residents.', 'Architecture', '2024-11-18 08:57:40'),
(5, 'Disaster-Resilient Design and Construction', 'Developing building designs and construction methods to withstand extreme weather events and natural hazards. Protection of human life and property, reduced economic losses due to natural disasters, and increased societal resilience.', 'Architecture', '2024-11-18 08:57:44'),
(6, 'Affordable and Inclusive Housing', 'Exploring innovative design and construction approaches to create affordable and accessible housing for diverse populations. Improved living conditions for marginalized communities and the advancement of social equity.', 'Architecture', '2024-11-18 08:57:52'),
(7, 'Accessibility and Universal Design', 'Researching principles of universal design to create buildings and spaces that are usable and accessible to everyone, regardless of ability. Enhanced inclusivity and equity, increased accessibility for people with disabilities, and improved usability for all.', 'Architecture', '2024-11-18 08:57:56'),
(8, 'Architectural History and Theory', 'Analyzing and interpreting the historical evolution of architecture and its theoretical underpinnings. A deeper understanding of architectural history, enhanced design knowledge, and informed decision-making in design practice.', 'Architecture', '2024-11-18 08:58:00'),
(9, 'Community-Based Design and Participation', 'Investigating the process of engaging local communities in architectural design and decision-making. Enhanced community ownership and engagement, creation of buildings and spaces that meet local needs, and promotion of social inclusiveness.', 'Architecture', '2024-11-18 08:58:04'),
(11, 'Smart and Interactive Buildings', 'Exploring the integration of technology to create intelligent buildings that respond to user needs and optimize performance. Enhanced energy efficiency, improved comfort, and increased security.', 'Architecture', '2024-11-18 08:58:49'),
(12, 'Architectural Tectonics & Materiality', 'Exploring the structural and material aspects of building design, emphasizing the relationship between form and material. Improves structural integrity, enables innovative design, advances material science.', 'Architecture', '2024-11-18 08:59:22'),
(13, 'Artificial Intelligence and Machine Learning', 'Developing and applying AI algorithms to solve complex problems. Advancements in automation, decision-making, and problem-solving across various industries.', 'Computer Science', '2024-11-18 08:59:39'),
(14, 'Data Science and Big Data Analytics', 'Extracting knowledge and insights from large and complex datasets. Improved decision-making in business, healthcare, and scientific research.', 'Computer Science', '2024-11-18 08:59:42'),
(15, 'Cybersecurity and Network Security', 'Protecting computer systems and networks from unauthorized access and cyber threats. Enhanced security and protection of sensitive data and infrastructure.', 'Computer Science', '2024-11-18 08:59:45'),
(16, 'Cloud Computing and Distributed Systems', 'Developing and managing scalable and reliable cloud-based systems. Enabling efficient resource utilization and accessibility of computing resources.', 'Computer Science', '2024-11-18 08:59:48'),
(17, 'Software Engineering and Development', 'Improving software development methodologies and processes. Increased efficiency and quality of software development.', 'Computer Science', '2024-11-18 08:59:51'),
(18, 'Human-Computer Interaction (HCI)', 'Designing and evaluating user interfaces and interactions. Improved user experience and accessibility of technology.', 'Computer Science', '2024-11-18 08:59:56'),
(19, 'Theoretical Computer Science', 'Exploring the foundations of computation and algorithms. Development of novel computational approaches and algorithms.', 'Computer Science', '2024-11-18 09:00:06'),
(20, 'Cybersecurity and Privacy', 'Exploring methods for enhancing data security, privacy protection, and mitigating cyber threats in various IT systems and applications. Improved data protection, reduced risks from cyberattacks, enhanced user trust in online systems.', 'Information Technology', '2024-12-11 03:52:59'),
(21, 'Ethical and Societal Implications of IT', 'Focuses on the moral, social, and cultural impacts of technology advancement. ', 'Information Technology', '2024-12-18 08:59:46');

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
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `usertype`, `username`, `program`, `area_of_expertise`, `is_parttime`, `email`, `password`, `first_name`, `last_name`, `gender`, `headline`, `bio`, `profile_image`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`) VALUES
(0, 0, 'winstonadmin', NULL, NULL, NULL, 'ton.agustin09@gmail.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Winston', 'Agustin', 'm', 'SUPER ADMIN', '', '6703b15c765f80.83029727.png', '2024-10-05 05:55:38', '2024-10-05 05:55:38', '2025-03-11 11:49:31', '0000-00-00 00:00:00', '2025-03-11 11:49:31'),
(37, 0, 'neilv', NULL, NULL, NULL, 'neilvicedo.ih@gmail.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Niall', 'V', 'o', 'Basta programmer ako', '?', '_defaultUser.png', '2024-10-08 05:14:14', '2024-10-08 05:13:14', '2025-03-15 02:43:04', NULL, '2025-03-15 02:43:04'),
(38, 1, '2021-2-02134', 'Bachelor of Science in Computer Science', NULL, NULL, 'winston.agustin@lpunetwork.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Winston', 'Agustin', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-03-08 02:38:04', NULL, '2025-03-08 02:38:04'),
(39, 1, 'student2', 'Bachelor of Science in Computer Science', NULL, NULL, 'student2@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Neil', 'Vicedo', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-03-10 03:46:33', NULL, '2025-03-10 03:46:33'),
(40, 1, 'student3', 'Bachelor of Science in Computer Science', NULL, NULL, 'student3@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jerald Ryan', 'Gerona', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-11 20:44:23'),
(41, 1, 'student4', 'Bachelor of Science in Computer Science', NULL, NULL, 'student4@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ivan Kerwin', 'Ilano', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-03-08 02:28:33', NULL, '2025-03-08 02:28:33'),
(42, 1, 'student5', 'Bachelor of Science in Computer Science', NULL, NULL, 'student5@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Linus Karl', 'Sambile', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-11-12 19:21:37'),
(43, 1, 'student6', 'Bachelor of Science in Computer Science', NULL, NULL, 'student6@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Yusuf', 'Mirasol', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-11-12 17:18:56'),
(44, 1, 'student7', 'Bachelor of Science in Computer Science', NULL, NULL, 'student7@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Keith Andrei', 'Marpuri', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-11-12 17:24:45'),
(45, 1, 'student8', 'Bachelor of Science in Computer Science', NULL, NULL, 'student8@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Renzo', 'Viñas', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(46, 1, 'student9', 'Bachelor of Science in Computer Science', NULL, NULL, 'student9@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Earl Stephen', 'Tacda', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(47, 1, 'student10', 'Bachelor of Science in Computer Science', NULL, NULL, 'student10@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Cassandra', 'Roxas', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(48, 1, 'student11', 'Bachelor of Science in Computer Science', NULL, NULL, 'student11@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kedd Cyrus', 'Alegre', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-02-20 16:38:36', NULL, '2025-02-20 16:38:36'),
(49, 1, 'student12', 'Bachelor of Science in Computer Science', NULL, NULL, 'student12@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Gian David ', 'Marasigan', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(50, 1, 'student13', 'Bachelor of Science in Computer Science', NULL, NULL, 'student13@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kenneth Joshua', 'Pedero', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-12-11 01:51:05'),
(51, 1, 'student14', 'Bachelor of Science in Computer Science', NULL, NULL, 'student14@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Christann', 'Nabablit', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(52, 1, 'student15', 'Bachelor of Science in Computer Science', NULL, NULL, 'student15@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Gerche Jay', 'Balaan', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(53, 1, 'student16', 'Bachelor of Science in Computer Science', NULL, NULL, 'student16@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Lyrick', 'Jonson', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-12-11 01:56:08'),
(54, 1, 'student17', 'Bachelor of Science in Computer Science', NULL, NULL, 'student17@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Von Zachary Benedict', 'Fadri', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(55, 1, 'student18', 'Bachelor of Science in Computer Science', NULL, NULL, 'student18@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joshua', 'Catampongan', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(56, 1, 'student19', 'Bachelor of Science in Computer Science', NULL, NULL, 'student19@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Nineteen', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(57, 1, 'student20', 'Bachelor of Science in Computer Science', NULL, NULL, 'student20@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Twenty', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(58, 2, 'staff1', 'Bachelor of Science in Computer Science', '', 0, 'sean.gono@lpu.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sean Charlston', 'Gono', 'm', 'BOI', 'This is a BOI.', '67545c47388503.11452602.jpg', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-03-12 08:46:30', NULL, '2025-03-10 03:38:04'),
(59, 2, 'staff2', 'Bachelor of Science in Computer Science', NULL, NULL, 'staff2@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Toni', 'Granado', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-03-10 03:37:10', NULL, '2025-03-10 03:37:10'),
(60, 2, 'staff3', 'Bachelor of Science in Computer Science', NULL, NULL, 'staff3@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jerian', 'Peren', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-03-10 03:37:41', NULL, '2025-03-10 03:37:41'),
(61, 2, 'staff4', 'Bachelor of Science in Computer Science', NULL, NULL, 'staff4@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Raymund', 'Constante', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-03-10 03:47:27', NULL, '2025-03-10 03:47:27'),
(62, 2, 'staff5', 'Bachelor of Science in Information Technology', NULL, NULL, 'staff5@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Laarnie', 'Carlos', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-03-15 00:14:36', NULL, '2025-03-15 00:14:36'),
(63, 2, 'staff6', 'Bachelor of Science in Information Technology', NULL, NULL, 'staff6@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Delia', 'Fainsan', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(64, 2, 'staff7', 'Bachelor of Science in Information Technology', NULL, NULL, 'staff7@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Elmer', 'Matel', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:11:34', NULL, '2024-10-09 22:07:06'),
(65, 2, 'staff8', 'Bachelor of Science in Computer Engineering', NULL, NULL, 'staff8@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Alyssa Paola', 'Pocaan', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:11:13', NULL, '2024-10-09 22:07:06'),
(66, 2, 'staff9', 'Bachelor of Science in Computer Engineering', NULL, NULL, 'staff9@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Nine', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(67, 2, 'staff10', 'Bachelor of Science in Computer Engineering', NULL, NULL, 'staff10@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Ten', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-02-20 16:37:25', NULL, '2025-02-20 16:37:25'),
(70, 0, 'a', NULL, NULL, NULL, 'a@a.a', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'a', 'a', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2024-11-11 06:39:12', NULL, '2024-10-24 19:31:28'),
(71, 1, 'ilano', NULL, NULL, NULL, 'ilano@ilano.ilano', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', '', '', NULL, '', '', '_defaultUser.png', '2024-10-27 18:12:34', '2024-10-27 18:10:53', '2024-11-11 06:39:12', NULL, '2024-10-27 18:12:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_schedules`
--

CREATE TABLE `user_schedules` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
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
  ADD KEY `fk_defense_schedules_team` (`team_id`);

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
  ADD KEY `criterion_id` (`criterion_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `rubric_criteria`
--
ALTER TABLE `rubric_criteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rubric_id` (`rubric_id`);

--
-- Indexes for table `rubric_quality_criteria`
--
ALTER TABLE `rubric_quality_criteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rubric_id` (`rubric_id`);

--
-- Indexes for table `rubric_rows`
--
ALTER TABLE `rubric_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rubric_id` (`rubric_id`);

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `defense_panelists`
--
ALTER TABLE `defense_panelists`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `env_variables`
--
ALTER TABLE `env_variables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `evaluation_details`
--
ALTER TABLE `evaluation_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluation_per_panel`
--
ALTER TABLE `evaluation_per_panel`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `form_assignments`
--
ALTER TABLE `form_assignments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `requirements`
--
ALTER TABLE `requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `research_titles`
--
ALTER TABLE `research_titles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rubrics`
--
ALTER TABLE `rubrics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `rubric_criteria`
--
ALTER TABLE `rubric_criteria`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rubric_quality_criteria`
--
ALTER TABLE `rubric_quality_criteria`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rubric_rows`
--
ALTER TABLE `rubric_rows`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `team_requirements`
--
ALTER TABLE `team_requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `thesis_topics`
--
ALTER TABLE `thesis_topics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

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
-- Constraints for table `evaluation_details`
--
ALTER TABLE `evaluation_details`
  ADD CONSTRAINT `evaluation_details_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluation_details_ibfk_2` FOREIGN KEY (`criterion_id`) REFERENCES `rubric_criteria` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `evaluation_per_panel`
--
ALTER TABLE `evaluation_per_panel`
  ADD CONSTRAINT `evalusations_per_panel_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `form_assignments`
--
ALTER TABLE `form_assignments`
  ADD CONSTRAINT `form_assignments_ibfk_1` FOREIGN KEY (`defense_schedule_id`) REFERENCES `defense_schedules` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `research_titles`
--
ALTER TABLE `research_titles`
  ADD CONSTRAINT `research_titles_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `rubrics`
--
ALTER TABLE `rubrics`
  ADD CONSTRAINT `rubrics_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rubric_criteria`
--
ALTER TABLE `rubric_criteria`
  ADD CONSTRAINT `rubric_criteria_ibfk_1` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rubric_quality_criteria`
--
ALTER TABLE `rubric_quality_criteria`
  ADD CONSTRAINT `rubric_quality_criteria_ibfk_1` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rubric_rows`
--
ALTER TABLE `rubric_rows`
  ADD CONSTRAINT `rubric_rows_ibfk_1` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_requirements`
--
ALTER TABLE `team_requirements`
  ADD CONSTRAINT `team_requirements_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_requirements_ibfk_2` FOREIGN KEY (`requirement_id`) REFERENCES `requirements` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
