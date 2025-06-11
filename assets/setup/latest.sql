-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql302.iceiy.com
-- Generation Time: Jun 11, 2025 at 10:34 AM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
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
(104, 'sean.gono@lpu.edu.ph', 'password_reset', 'ca92c4dad4d8cdbf', '$2y$10$3AkW3EFEvXovTXyIU31nT.GJ.RKd.RKQuAP5l7DjmcNTpZAiJvZHq', '2024-12-07 15:13:46', '2024-12-07 09:13:46'),
(107, 'neilvicedo.ih@gmail.com', 'remember_me', '93af16f780701c28', '$2y$10$YPAbKoqAgwAso7RD68TCLuHMCKTNmIl2d1jRhFpNC45ELxj15ysKq', '2025-02-18 04:54:06', '2025-02-27 21:54:06'),
(112, 'lito.maligro', 'account_verify', '6999ee0069886390', '$2y$10$HjvmyniBMjFJFm7Vbs6LguADjK/cNuKNiYCrStdkFiU68hrFiRx1m', '2025-04-02 03:47:03', '2025-04-01 20:47:03'),
(115, 'winston.agustin@lpunetwork.edu.ph', 'remember_me', '9c1f5a950065ca7a', '$2y$10$CG5TXI3.A2xvmfiq6fyUiuoSoibgB/5jYze0YCOPAAWEQUgKSj1NW', '2025-04-02 13:51:55', '2025-04-12 05:51:55'),
(118, 'b.b@lpunetwork.ude.ph', 'account_verify', '1d82f8c22f62a6c1', '$2y$10$ZEy3lU.tnPRJifLQmaGWseYU6p6vjIurQM1nCzB4fZGvdR2nrsRjC', '2025-04-07 17:33:13', '2025-04-07 10:33:13'),
(119, '2021-2-03212@lpunetwork.edu.ph', 'account_verify', '6bd301d37fc5abd5', '$2y$10$Fsg1ccJcc4q5NrZia0HkruH.xg/Hh3Tv5KVnPdwirhPZqn0UufBRe', '2025-04-07 18:13:53', '2025-04-07 11:13:53'),
(122, 'jetix55291@bauscn.com', 'password_reset', '227c447cfbe35563', '$2y$10$dX0qpsrTGMLFq65i7f5K8OjkmxWhQk5UHJ8BEAoOxPnETRj.ZanRS', '2025-04-23 14:25:38', '2025-04-24 06:25:38');

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
(2003, 74, 41, 223, NULL, NULL, '1', NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2004, 74, 41, 224, NULL, NULL, '1', NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2005, 74, 42, 212, NULL, 5, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2006, 74, 42, 213, NULL, 5, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2007, 74, 42, 214, NULL, 5, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2008, 74, 42, 215, NULL, 5, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2009, 74, 42, 216, NULL, 5, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2010, 74, 42, 217, NULL, 5, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2011, 74, 45, 124, 122, 10, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2012, 74, 45, 124, 141, 10, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2013, 74, 45, 124, 142, 10, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2014, 74, 45, 125, 122, 10, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2015, 74, 45, 125, 141, 10, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2016, 74, 45, 125, 142, 10, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2017, 74, 45, 126, 122, 10, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2018, 74, 45, 126, 141, 10, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2019, 74, 45, 126, 142, 10, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2020, 74, 47, NULL, NULL, NULL, '2', NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2021, 74, 49, 220, 122, 0, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2022, 74, 49, 220, 141, 0, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2023, 74, 49, 220, 142, 0, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2024, 74, 49, 221, 122, 0, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2025, 74, 49, 221, 141, 0, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2026, 74, 49, 221, 142, 0, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2027, 74, 49, 222, 122, 0, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2028, 74, 49, 222, 141, 0, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2029, 74, 49, 222, 142, 0, NULL, NULL, '2025-04-22 16:17:49', '2025-04-22 16:17:49'),
(2046, 77, 42, 258, NULL, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2047, 77, 42, 259, NULL, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2048, 77, 42, 260, NULL, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2049, 77, 42, 261, NULL, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2050, 77, 42, 262, NULL, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2051, 77, 42, 263, NULL, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2052, 77, 45, 255, 38, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2053, 78, 45, 255, 40, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2054, 79, 45, 255, 41, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2055, 77, 45, 256, 38, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2056, 78, 45, 256, 40, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2057, 79, 45, 256, 41, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2058, 77, 45, 257, 38, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2059, 78, 45, 257, 40, 10, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2060, 79, 45, 257, 41, 3, NULL, NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37'),
(2061, 77, 47, NULL, NULL, NULL, '1', NULL, '2025-04-27 23:07:37', '2025-04-27 23:07:37');

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
(13, 4, 59, 48, 60, 40, 100, 'nice', '2024-12-18 00:59:28', '0000-00-00 00:00:00'),
(14, 4, 59, 49, 60, 40, 100, 'nice', '2024-12-18 00:59:28', '0000-00-00 00:00:00'),
(15, 4, 59, 50, 60, 40, 100, 'nice', '2024-12-18 00:59:28', '0000-00-00 00:00:00'),
(49, 1, 59, 38, 51.3333, 40, 91.3333, 'panget mo ilano', '2025-03-08 02:37:46', '0000-00-00 00:00:00'),
(50, 1, 59, 39, 51.3333, 33, 84.3333, 'panget mo ilano', '2025-03-08 02:37:46', '0000-00-00 00:00:00'),
(51, 1, 59, 40, 51.3333, 12, 63.3333, 'panget mo ilano', '2025-03-08 02:37:46', '0000-00-00 00:00:00'),
(52, 1, 59, 41, 51.3333, 5, 56.3333, 'panget mo ilano', '2025-03-08 02:37:46', '0000-00-00 00:00:00'),
(74, 17, 58, 122, 30, 20, 50, 'qwe', '2025-04-22 03:25:44', '2025-04-22 16:17:49'),
(75, 17, 58, 142, 30, 20, 50, 'qwe', '2025-04-22 03:25:44', '2025-04-22 16:17:49'),
(76, 17, 58, 141, 30, 20, 50, 'qwe', '2025-04-22 03:25:44', '2025-04-22 16:17:49'),
(77, 45, 58, 38, 60, 40, 100, '', '2025-04-27 23:07:08', '2025-04-27 23:07:37'),
(78, 45, 58, 40, 60, 40, 100, '', '2025-04-27 23:07:08', '2025-04-27 23:07:37'),
(79, 45, 58, 41, 60, 30.6667, 90.6667, '', '2025-04-27 23:07:08', '2025-04-27 23:07:37');

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
(1, 'pls work', 'pls-work', '<p>If you\'re seeing this then celebrate, it\'s now <b>working.</b></p>', 'draft', '2025-04-18 19:24:53', '2025-04-24 21:08:28', 37, 0),
(2, 'still working and improved?', 'still-working-and-improved', '<h1>Greetings Lyceans,</h1><h3>We are venom.</h3><blockquote class=\"blockquote\"><p>I do not think, therefore I do not am. - Venom</p></blockquote><p><br></p><p>&nbsp;This is a normal paragraph being tested for the features such as, <b>bold,</b>&nbsp;<u>underlined,</u>&nbsp;<i>italic, </i><span style=\"background-color: rgb(0, 255, 0);\">with higlight,</span>&nbsp;&nbsp;<br></p><hr><ul><li>In a bullet<br></li></ul><hr><ol><li>In a number</li></ol><hr><p style=\"text-align: center; \">Centered</p><hr><p style=\"text-align: left;\">Left-aligned</p><hr><p style=\"text-align: right;\">Right-aligned</p><hr><p style=\"text-align: justify;\">Justified&nbsp;Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.<br></p><hr><p style=\"text-align: justify; margin-left: 25px;\">Indented</p><hr><p style=\"text-align: justify; margin-left: 25px;\">Table</p><table class=\"table table-bordered\"><tbody><tr><td>Col1</td><td>Col2</td><td>Col3</td></tr><tr><td>Row1 C1</td><td>Row1 C2</td><td>Row1 C3</td></tr></tbody></table><hr><p style=\"text-align: justify; margin-left: 25px;\">gfdgfd</p>', 'published', '2025-04-21 02:37:06', '2025-04-21 09:18:23', 37, 37);

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
(59, 'College of Allied Medical Sciences', 'wuvwuv', 'BS Pharmacy', '', '2025-04-29 13:34:25'),
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
(76, 'College of Engineering, Computer Studies and Architecture', 'Architecture', 'Bachelor of Science in Architecture (Arch)', NULL, '2025-04-29 09:02:55'),
(77, 'College of Engineering, Computer Studies and Architecture', 'Computer Studies', 'Bachelor of Science in Computer Science', 'Data Science', '2025-04-29 09:02:55'),
(78, 'College of Engineering, Computer Studies and Architecture', 'Computer Studies', 'Bachelor of Science in Computer Science', 'Software Engineering', '2025-04-29 09:02:55'),
(79, 'College of Engineering, Computer Studies and Architecture', 'Computer Studies', 'Bachelor of Science in Information Technology', 'Network and Information Security', '2025-04-29 09:02:55'),
(80, 'College of Engineering, Computer Studies and Architecture', 'Computer Studies', 'Bachelor of Science in Information Technology', 'Web and Mobile Technology', '2025-04-29 09:02:55'),
(81, 'College of Engineering, Computer Studies and Architecture', 'Computer Studies', 'Bachelor of Library and Information Science', NULL, '2025-04-29 09:02:55'),
(82, 'College of Engineering, Computer Studies and Architecture', 'Engineering', 'Bachelor of Science in Aeronautical Engineering', NULL, '2025-04-29 09:02:55'),
(83, 'College of Engineering, Computer Studies and Architecture', 'Engineering', 'Bachelor of Science in Civil Engineering', 'Construction Engineering & Management', '2025-04-29 09:02:55'),
(84, 'College of Engineering, Computer Studies and Architecture', 'Engineering', 'Bachelor of Science in Civil Engineering', 'Structural Engineering', '2025-04-29 09:02:55'),
(85, 'College of Engineering, Computer Studies and Architecture', 'Engineering', 'Bachelor of Science in Civil Engineering', 'Transportation Engineering', '2025-04-29 09:02:55'),
(86, 'College of Engineering, Computer Studies and Architecture', 'Engineering', 'Bachelor of Science in Computer Engineering', NULL, '2025-04-29 09:02:55'),
(87, 'College of Engineering, Computer Studies and Architecture', 'Engineering', 'Bachelor of Engineering Technology', 'Construction Technology and Management', '2025-04-29 09:02:55'),
(88, 'College of Engineering, Computer Studies and Architecture', 'Engineering', 'Bachelor of Science in Electrical Engineering', NULL, '2025-04-29 09:02:55'),
(89, 'College of Engineering, Computer Studies and Architecture', 'Engineering', 'Bachelor of Science in Electronics Engineering', NULL, '2025-04-29 09:02:55'),
(90, 'College of Engineering, Computer Studies and Architecture', 'Engineering', 'Bachelor of Science in Industrial Engineering', NULL, '2025-04-29 09:02:55'),
(91, 'College of Engineering, Computer Studies and Architecture', 'Engineering', 'Bachelor of Science in Mechanical Engineering', NULL, '2025-04-29 09:02:55'),
(92, 'College of Fine Arts and Design', NULL, 'Bachelor of Fine Arts', NULL, '2025-04-29 09:02:55'),
(93, 'College of Fine Arts and Design', NULL, 'Bachelor of Multimedia Arts', NULL, '2025-04-29 09:02:55'),
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
(5, 'Final Manuscript', 'Also used for Research Repository (DO NOT REMOVE)', '2024-12-04', '2024-11-11 10:52:22'),
(41, 'grading sheet', 'grades', '2024-12-13', '2024-12-11 04:12:01'),
(43, 'imrad', '', '0000-00-00', '2025-04-25 03:40:35');

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
(2, 2, ' Arcadia: A LIBRARY MANAGEMENTSYSTEMFORLPU  ACADEMICRESOURCECENTERUSINGMACHINE  LEARNINGFORTEXTCLASSIFICATIONAND  RECOMMENDATIONSYSTEMS', 'Master in Business Administration', '2024-11-16 02:30:00', NULL, '2024-10-13 07:15:44', '2025-04-27 08:41:47'),
(3, 3, 'SOLACE: SMART SYMPTOM MONITORING AND AI PREDICTIVE  INTERVENTION IN PALLIATIVE AND HOSPICE CARE', 'Juris Doctor', NULL, NULL, '2024-10-13 07:15:44', '2025-04-27 08:50:59'),
(4, 4, 'ADAPT: AI-DRIVEN CUSTOMIZABLE CHATBOT PLUGIN FOR  ENHANCED USER INTERACTION IN WEB-BASED PLATFORMS  ', NULL, '2024-11-18 06:00:00', NULL, '2024-10-13 07:15:44', '2024-12-10 22:48:08'),
(5, 5, 'QUIZSCAN: AUTOMATED HANDWRITTEN ACTIVITY ANSWERS  RECOGNITION FOR TEACHERS USING CNN ALGORITHM', NULL, '2024-11-19 07:30:00', NULL, '2024-10-13 07:15:44', '2025-04-23 14:33:52'),
(27, 26, 'GIG-A-FIND:', NULL, NULL, NULL, '2025-04-11 04:05:01', '2025-04-11 04:05:01'),
(28, 27, 'TherapEase', NULL, NULL, NULL, '2025-04-11 04:05:54', '2025-04-11 04:05:54'),
(29, 28, 'Blaze Rider', NULL, NULL, NULL, '2025-04-11 04:06:21', '2025-04-11 04:06:21'),
(30, 29, 'CYBEREUM', NULL, NULL, NULL, '2025-04-11 04:07:15', '2025-04-11 04:07:15'),
(31, 30, 'CoralIS', NULL, NULL, NULL, '2025-04-11 04:07:40', '2025-04-11 04:07:40'),
(32, 31, 'ReLuto', NULL, NULL, NULL, '2025-04-11 04:08:16', '2025-04-11 04:08:16'),
(33, 32, 'CRAMS', NULL, NULL, NULL, '2025-04-11 04:08:40', '2025-04-11 04:08:40'),
(34, 33, 'The Influence of Corporate Social Responsibility Programs of a Legacy Brand in Tagaytay City Towards Customer Loyalty', NULL, NULL, NULL, '2025-04-22 01:17:51', '2025-04-22 01:17:51'),
(35, 34, 'The Influence of Fear-of-Missing-Out (FOMO) Behavior to Purchase Decision on Technology-Related Products: Basis for Developing Marketing Strategies', NULL, NULL, NULL, '2025-04-22 01:18:47', '2025-04-22 01:18:47'),
(36, 35, 'The Influence of Social Media Content Strategies on College Students\' Perception of Brand Image in a Private University in General Trias, Cavite', NULL, NULL, NULL, '2025-04-22 01:18:47', '2025-04-22 01:18:47'),
(37, 36, 'Private Universities  Digital Advertising Practices on Senior High School Student Preference in Selected Cities in Cavite: A Basis for Improvement', NULL, NULL, NULL, '2025-04-22 01:18:47', '2025-04-22 01:18:47'),
(38, 37, 'The Effect of Hyper-Personalization on Privacy Concerns in Social Commerce Among Residents of Selected Barangay in Dasmari as, Cavite', NULL, NULL, NULL, '2025-04-22 01:18:47', '2025-04-22 01:18:47'),
(39, 38, 'Influence of Eco-conscious Branding on University Students  Cosmetics Purchasing Decisions in Selected Universities in Dasmari as, Cavite', NULL, NULL, NULL, '2025-04-22 01:18:47', '2025-04-22 01:18:47'),
(40, 39, 'Effect of Viral Video Meme Marketing on Consumer Engagement Among Generation Z in a Selected University in Cavite', NULL, NULL, NULL, '2025-04-22 01:18:47', '2025-04-22 01:18:47'),
(41, 40, 'The Correlation Between Display Advertisement and Consumer Purchase Intentions Towards Jollibee Among Students of Selected Universities in Cavite', NULL, NULL, NULL, '2025-04-22 01:18:47', '2025-04-22 01:18:47'),
(42, 42, 'T1', NULL, NULL, NULL, '2025-04-23 14:35:15', '2025-04-23 14:35:15'),
(43, 43, 'title', NULL, NULL, NULL, '2025-04-23 14:36:41', '2025-04-25 06:32:16'),
(44, 44, 'TESTTESTSETE', NULL, NULL, NULL, '2025-04-23 15:00:56', '2025-04-23 15:00:56'),
(45, 45, 'TESTTESTSETE', NULL, NULL, NULL, '2025-04-23 15:01:02', '2025-04-23 15:01:02'),
(46, 46, 'TESTTESTSETE', NULL, NULL, NULL, '2025-04-23 15:02:33', '2025-04-23 15:02:33'),
(47, 47, 'TESTTESTSETE', NULL, NULL, NULL, '2025-04-23 15:05:41', '2025-04-23 15:05:41'),
(48, 48, 'TESTSETESTSE', NULL, NULL, NULL, '2025-04-23 15:07:18', '2025-04-23 15:07:18'),
(49, 43, 'title', NULL, NULL, NULL, '2025-04-23 15:18:07', '2025-04-25 06:32:16'),
(50, 50, 'Mycelium', NULL, NULL, NULL, '2025-04-24 02:47:28', '2025-04-24 02:47:28'),
(53, 53, 'NannyHub', NULL, NULL, NULL, '2025-04-25 15:07:34', '2025-04-25 15:07:34'),
(54, 54, 'NannyHub', NULL, NULL, NULL, '2025-04-25 15:08:45', '2025-04-25 15:08:45'),
(55, 55, 'NannyHub', NULL, NULL, NULL, '2025-04-25 15:09:15', '2025-04-25 15:09:15'),
(56, 56, 'NannyHub', NULL, NULL, NULL, '2025-04-25 15:09:54', '2025-04-25 15:09:54'),
(57, 57, 'NannyHub', NULL, NULL, NULL, '2025-04-25 15:10:32', '2025-04-25 15:10:32'),
(58, 58, 'NannyHub', NULL, NULL, NULL, '2025-04-25 15:13:39', '2025-04-25 15:13:39'),
(59, 59, 'NannyHub', NULL, NULL, NULL, '2025-04-25 15:13:56', '2025-04-25 15:13:56'),
(60, 60, '', NULL, NULL, NULL, '2025-04-25 15:14:08', '2025-04-25 15:14:08'),
(61, 61, 'NannyHubdfh', NULL, NULL, NULL, '2025-04-25 15:15:46', '2025-04-25 15:21:49'),
(62, 1, 'aaaaaaaaaa', NULL, NULL, NULL, '2025-04-26 06:26:00', '2025-04-27 14:30:43'),
(63, 63, 'shamwow', NULL, NULL, NULL, '2025-04-26 06:29:25', '2025-04-26 06:31:42'),
(64, 64, 'Shamwow', NULL, NULL, NULL, '2025-04-26 06:32:10', '2025-04-26 06:32:10'),
(65, 65, 'Shamwow', NULL, NULL, NULL, '2025-04-26 06:33:41', '2025-04-26 06:33:41'),
(66, 66, 'Shamwow', NULL, NULL, NULL, '2025-04-26 06:34:14', '2025-04-26 06:34:14'),
(67, 67, 'a', NULL, NULL, NULL, '2025-04-27 00:30:22', '2025-04-27 00:30:22');

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
(41, 'rubric  name', 'desc', 'yesno', 0, NULL, 'desc', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-04-17 13:19:21', '2025-04-23 14:45:02', 100),
(42, 'Written Manuscript', 'Group Grade', 'numerical', 0, 'Final Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-04-18 11:28:12', '2025-04-27 23:31:58', 100),
(45, 'Oral Defense ', 'Individual Grade', 'numerical', 1, 'Final Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, 1, '2025-04-21 09:55:14', '2025-04-27 23:32:08', 100),
(47, 'FINAL RECOMMENDATION:', 'CBA ', 'passfail', 0, 'Final Defense', '', 'The manuscript is accepted: ', 'The manuscript is rejected: ', 'below 70% acceptability (refer to research adviser and for re-defense) ', '81.00', '80.00', '70.00', 0, NULL, 1, '2025-04-21 17:13:58', '2025-04-27 23:32:20', 100),
(48, 'Written Manuscript Proposal', 'Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-04-21 19:39:59', '2025-04-22 15:04:55', 100),
(49, 'Oral Defense Proposal', 'Individual Grade ', 'numerical', 1, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, 1, '2025-04-21 19:45:28', '2025-04-22 15:14:22', 100);

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
(1, 16, 'a', NULL, 0, 0, '2025-04-13 15:35:33', '2025-04-13 15:35:33'),
(15, 40, 'a', NULL, 0, 0, '2025-04-17 13:13:37', '2025-04-17 13:13:37'),
(16, 40, 'b', NULL, 1, 0, '2025-04-17 13:13:37', '2025-04-17 13:13:37'),
(218, 48, '', '', 0, 0, '2025-04-22 15:04:55', '2025-04-22 15:04:55'),
(220, 49, 'Clarity and mastery in the presentation', NULL, 0, 1, '2025-04-22 15:14:22', '2025-04-22 15:14:22'),
(221, 49, 'Articulate response to the inquiries', NULL, 1, 1, '2025-04-22 15:14:22', '2025-04-22 15:14:22'),
(222, 49, 'Proper demeanor and dress code', NULL, 2, 1, '2025-04-22 15:14:22', '2025-04-22 15:14:22'),
(225, 41, 'hello', 'hello hello', 0, 0, '2025-04-23 14:45:02', '2025-04-23 14:45:02'),
(226, 41, 'hi', '', 1, 0, '2025-04-23 14:45:02', '2025-04-23 14:45:02'),
(230, 50, '', '', 0, 0, '2025-04-25 12:07:31', '2025-04-25 12:07:31'),
(232, 51, '', '', 0, 0, '2025-04-25 16:27:20', '2025-04-25 16:27:20'),
(233, 52, 'a', 'a', 0, 0, '2025-04-27 08:51:47', '2025-04-27 08:51:47'),
(264, 42, 'Clarity of Research Problem and Objectives', '[\"10%\"]', 0, 0, '2025-04-27 23:31:58', '2025-04-27 23:31:58'),
(265, 42, 'Extent of Review of Related Literature', '[\"10%\"]', 1, 0, '2025-04-27 23:31:58', '2025-04-27 23:31:58'),
(266, 42, 'Appropriateness of Methodology', '[\"10%\"]', 2, 0, '2025-04-27 23:31:58', '2025-04-27 23:31:58'),
(267, 42, 'Data Presentation and Depth of Analysis', '[\"10% \"]', 3, 0, '2025-04-27 23:31:58', '2025-04-27 23:31:58'),
(268, 42, 'Logic of Conclusion and Recommendations', '[\"10%\"]', 4, 0, '2025-04-27 23:31:58', '2025-04-27 23:31:58'),
(269, 42, 'Order and neatness of the manuscript', '[\"10%\"]', 5, 0, '2025-04-27 23:31:58', '2025-04-27 23:31:58'),
(270, 45, 'Clarity and mastery in the presentation', NULL, 0, 1, '2025-04-27 23:32:08', '2025-04-27 23:32:08'),
(271, 45, 'Articulate response to the inquiries', NULL, 1, 1, '2025-04-27 23:32:08', '2025-04-27 23:32:08'),
(272, 45, 'Proper demeanor and dress code', NULL, 2, 1, '2025-04-27 23:32:08', '2025-04-27 23:32:08');

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
(3, 'Final Defense Score Sheet', 'CBA A.Y. 24-25', '2025-04-17 13:33:37', '2025-04-25 03:37:48'),
(5, 'Proposal Defense Score Sheet', 'CBA A.Y. 24-25', '2025-04-21 19:43:54', '2025-04-21 19:46:23');

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
(31, 5, 48, 0, '60.00', '2025-04-21 19:46:23', '2025-04-21 19:46:23'),
(32, 5, 49, 1, '40.00', '2025-04-21 19:46:23', '2025-04-21 19:46:23'),
(33, 5, 47, 2, NULL, '2025-04-21 19:46:23', '2025-04-21 19:46:23'),
(50, 3, 42, 0, '60.00', '2025-04-25 03:37:48', '2025-04-25 03:37:48'),
(51, 3, 45, 1, '40.00', '2025-04-25 03:37:48', '2025-04-25 03:37:48'),
(52, 3, 47, 2, NULL, '2025-04-25 03:37:48', '2025-04-25 03:37:48');

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
(64, 40, 1, 'Level 1', 'a', 5, 5, 0, '2025-04-17 13:13:37', '2025-04-17 13:13:37'),
(65, 40, 2, 'Level 2', 'b', 4, 4, 0, '2025-04-17 13:13:37', '2025-04-17 13:13:37'),
(66, 40, 3, 'Level 3', 'c', 3, 3, 0, '2025-04-17 13:13:37', '2025-04-17 13:13:37'),
(123, 46, 1, 'Modifier 1', 'Modifier 1 Description', NULL, NULL, 0, '2025-04-21 13:44:35', '2025-04-21 13:44:35'),
(124, 46, 2, 'Modifier 2', 'Modifier 2 Description', NULL, NULL, 0, '2025-04-21 13:44:35', '2025-04-21 13:44:35'),
(125, 46, 3, 'Modifier 3', 'Modifier 3 Description', NULL, NULL, 0, '2025-04-21 13:44:35', '2025-04-21 13:44:35'),
(174, 48, 1, 'Written Manuscript Proposal', '', 10, 10, 0, '2025-04-22 15:04:55', '2025-04-22 15:04:55'),
(176, 49, 1, 'Level 1', '', 10, 10, 0, '2025-04-22 15:14:22', '2025-04-22 15:14:22'),
(178, 50, 1, 'Level 1', '', 5, 5, 0, '2025-04-25 12:07:31', '2025-04-25 12:07:31'),
(183, 51, 1, 'Level 1', '', 5, 5, 0, '2025-04-25 16:27:20', '2025-04-25 16:27:20'),
(184, 51, 2, 'Level 2', '', 4, 4, 0, '2025-04-25 16:27:20', '2025-04-25 16:27:20'),
(185, 51, 3, 'Level 3', '', 3, 3, 0, '2025-04-25 16:27:20', '2025-04-25 16:27:20'),
(186, 51, 4, 'Level 4', '', 2, 2, 0, '2025-04-25 16:27:20', '2025-04-25 16:27:20'),
(187, 51, 5, 'Level 5', '', 1, 1, 0, '2025-04-25 16:27:20', '2025-04-25 16:27:20'),
(188, 52, 1, 'a', 'a', 5, 5, 0, '2025-04-27 08:51:47', '2025-04-27 08:51:47'),
(207, 42, 1, 'Written manuscript', '', 0, 10, 1, '2025-04-27 23:31:58', '2025-04-27 23:31:58'),
(208, 45, 1, 'Level 1', '', 0, 100, 1, '2025-04-27 23:32:08', '2025-04-27 23:32:08'),
(209, 47, 1, 'Pass Option 1', 'without revision ', NULL, NULL, 0, '2025-04-27 23:32:20', '2025-04-27 23:32:20'),
(210, 47, 2, 'Pass Option 2', 'with minor revisions: at least 80% acceptability (refer to evaluation sheet)', NULL, NULL, 0, '2025-04-27 23:32:20', '2025-04-27 23:32:20'),
(211, 47, 3, 'Pass Option 3', 'with  major revisions: at least 70% acceptability (for re-defense) ', NULL, NULL, 0, '2025-04-27 23:32:20', '2025-04-27 23:32:20');

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
(42, 'Bachelor of Science in Computer Science - Software Engineering'),
(45, 'Bachelor of Science in Computer Science - Software Engineering'),
(47, 'Bachelor of Science in Computer Science - Software Engineering');

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
(1, '120ms', '2024-10-13 06:58:29', 'Bachelor of Science in Computer Science - Software Engineering', 'webdev'),
(2, 'Arcadia', '2024-10-13 06:58:29', 'Bachelor of Science in Computer Science', NULL),
(3, 'Solace', '2024-10-13 06:58:29', 'Bachelor of Science in Computer Science', NULL),
(4, 'Adapt', '2024-10-13 06:58:29', 'Bachelor of Science in Computer Science', NULL),
(26, 'GIG-A-FIND', '2025-04-11 04:05:01', 'Bachelor of Science in Information Technology with specialization in Web and Mobile Technology', 'Web Dev'),
(27, 'TherapEase', '2025-04-11 04:05:54', 'Bachelor of Science in Information Technology with specialization in Web and Mobile Technology', 'Hybrid Dev'),
(28, 'Blaze Rider', '2025-04-11 04:06:21', 'Bachelor of Science in Information Technology with specialization in Web and Mobile Technology', 'Mobile Dev'),
(29, 'CYBEREUM', '2025-04-11 04:07:15', 'Bachelor of Science in Information Technology with specialization in Web and Mobile Technology', 'Mobile Dev'),
(30, 'CoralIS', '2025-04-11 04:07:40', 'Bachelor of Science in Information Technology with specialization in Web and Mobile Technology', 'Hybrid Dev'),
(31, 'ReLuto', '2025-04-11 04:08:16', 'Bachelor of Science in Information Technology with specialization in Web and Mobile Technology', 'Mobile Dev'),
(32, 'CRAMS', '2025-04-11 04:08:40', 'Bachelor of Science in Information Technology with specialization in Web and Mobile Technology', 'Web Dev'),
(33, 'team 1 MM302', '2025-04-22 01:17:51', 'BS Marketing Management', 'Qualitative'),
(34, 'team 2 MM302', '2025-04-22 01:18:47', 'BS Marketing Management', 'Qualitative'),
(35, 'team 1 MM303', '2025-04-22 01:18:47', 'BS Marketing Management', 'Qualitative'),
(36, 'team 2 MM303', '2025-04-22 01:18:47', 'BS Marketing Management', 'Qualitative'),
(37, 'team 1 MM304', '2025-04-22 01:18:47', 'BS Marketing Management', 'Qualitative'),
(38, 'team 2 MM304', '2025-04-22 01:18:47', 'BS Marketing Management', 'Qualitative'),
(39, 'team 1 MM305', '2025-04-22 01:18:47', 'BS Marketing Management', 'Qualitative'),
(40, 'team 2 MM305', '2025-04-22 01:18:47', 'BS Marketing Management', 'Qualitative'),
(44, 'T2', '2025-04-23 15:00:56', 'Bachelor of Science in Computer Science with specialization in Data Science', 'Hybrid Dev'),
(45, 'T2', '2025-04-23 15:01:02', 'Bachelor of Science in Computer Science with specialization in Data Science', 'Hybrid Dev'),
(46, 'T2', '2025-04-23 15:02:33', 'Bachelor of Science in Computer Science with specialization in Data Science', 'Hybrid Dev'),
(47, 'T2', '2025-04-23 15:05:41', 'Bachelor of Science in Computer Science with specialization in Data Science', 'Hybrid Dev'),
(48, 'T2', '2025-04-23 15:07:18', 'BSCS', 'Webdev'),
(50, 'Mycelium', '2025-04-24 02:47:28', 'Master in Business Administration', 'Biology'),
(51, '', '2025-04-25 10:07:53', '', ''),
(52, '', '2025-04-25 10:20:35', '', ''),
(53, 'NannyHub', '2025-04-25 15:07:34', 'Bachelor of Science in Computer Science with specialization in Software Engineering', 'Web Dev'),
(55, 'NannyHub', '2025-04-25 15:09:15', 'Bachelor of Science in Computer Science with specialization in Software Engineering', 'Software Engineering'),
(56, 'NannyHub', '2025-04-25 15:09:54', 'Bachelor of Science in Computer Science with specialization in Software Engineering', 'Software Engineering'),
(58, 'NannyHub', '2025-04-25 15:13:39', 'Bachelor of Science in Computer Science with specialization in Software Engineering', 'Software Engineering'),
(59, 'NannyHub', '2025-04-25 15:13:56', '', ''),
(60, '', '2025-04-25 15:14:08', '', ''),
(65, 'Shamwow', '2025-04-26 06:33:41', 'a', 'a'),
(66, 'Shamwow', '2025-04-26 06:34:14', 'Bachelor of Science in Architecture', 'Mobile Dev'),
(67, 'a', '2025-04-27 00:30:22', 'Unspecified', 'a');

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
(91, 3, 47, 'member'),
(92, 11, 58, 'adviser'),
(93, 11, 53, 'leader'),
(94, 11, 51, 'member'),
(95, 11, 55, 'member'),
(96, 12, 65, 'adviser'),
(98, 13, 65, 'adviser'),
(99, 13, 76, 'member'),
(101, 13, 76, 'leader'),
(102, 12, 75, 'leader'),
(103, 15, 58, 'adviser'),
(104, 15, 55, 'leader'),
(105, 16, 59, 'adviser'),
(106, 16, 53, 'leader'),
(107, 17, 59, 'adviser'),
(108, 17, 52, 'leader'),
(109, 18, 58, 'adviser'),
(110, 18, 39, 'leader'),
(111, 18, 40, 'memeber'),
(112, 19, 93, 'leader'),
(113, 20, 106, 'member'),
(114, 21, 124, 'leader'),
(115, 22, 86, 'leader'),
(116, 23, 129, 'leader'),
(118, 25, 126, 'leader'),
(119, 26, 93, 'leader'),
(120, 26, 66, 'adviser'),
(121, 27, 106, 'leader'),
(122, 27, 58, 'adviser'),
(123, 28, 110, 'adviser'),
(124, 28, 83, 'leader'),
(125, 29, 144, 'adviser'),
(126, 29, 124, 'leader'),
(127, 30, 86, 'member'),
(128, 30, 143, 'adviser'),
(131, 32, 59, 'adviser'),
(132, 32, 122, 'leader'),
(133, 29, 125, 'member'),
(134, 29, 126, 'member'),
(135, 29, 115, 'member'),
(136, 30, 121, 'member'),
(137, 30, 128, 'member'),
(138, 30, 114, 'member'),
(142, 30, 146, 'member'),
(143, 27, 108, 'member'),
(144, 27, 88, 'member'),
(145, 27, 105, 'member'),
(146, 28, 99, 'member'),
(149, 31, 144, 'adviser'),
(151, 31, 129, 'leader'),
(152, 31, 120, 'member'),
(153, 31, 117, 'member'),
(154, 31, 148, 'member'),
(155, 31, 135, 'member'),
(156, 32, 142, 'member'),
(157, 32, 141, 'member'),
(158, 33, 172, 'adviser'),
(159, 33, 149, 'leader'),
(160, 33, 150, 'member'),
(161, 33, 151, 'member'),
(162, 33, 152, 'member'),
(163, 33, 153, 'member'),
(164, 34, 172, 'adviser'),
(165, 34, 154, 'leader'),
(166, 34, 155, 'member'),
(167, 34, 156, 'member'),
(168, 34, 157, 'member'),
(169, 34, 158, 'member'),
(170, 35, 172, 'adviser'),
(171, 35, 159, 'leader'),
(172, 35, 160, 'member'),
(173, 35, 161, 'member'),
(174, 35, 162, 'member'),
(175, 36, 172, 'adviser'),
(176, 36, 163, 'leader'),
(177, 36, 164, 'member'),
(178, 36, 165, 'member'),
(179, 36, 166, 'member'),
(180, 36, 167, 'member'),
(181, 37, 175, 'adviser'),
(182, 37, 106, 'leader'),
(183, 38, 175, 'adviser'),
(184, 39, 175, 'adviser'),
(185, 40, 175, 'adviser'),
(186, 35, 178, 'member'),
(187, 40, 154, 'leader'),
(188, 40, 196, 'member'),
(189, 40, 197, 'member'),
(190, 40, 198, 'member'),
(191, 40, 199, 'member'),
(192, 37, 179, 'member'),
(193, 37, 180, 'member'),
(194, 37, 181, 'member'),
(195, 37, 182, 'member'),
(196, 37, 183, 'member'),
(197, 38, 187, 'leader'),
(198, 38, 188, 'member'),
(199, 38, 189, 'member'),
(200, 38, 190, 'member'),
(201, 38, 191, 'member'),
(202, 39, 192, 'leader'),
(203, 39, 200, 'member'),
(204, 39, 193, 'member'),
(205, 39, 194, 'member'),
(206, 39, 195, 'member'),
(207, 42, 66, 'adviser'),
(208, 42, 201, 'leader'),
(209, 42, 209, 'member'),
(210, 42, 210, 'member'),
(211, 43, 66, 'adviser'),
(212, 43, 201, 'leader'),
(213, 43, 209, 'member'),
(214, 43, 210, 'member'),
(215, 43, 213, 'member'),
(216, 48, NULL, 'leader'),
(217, 50, 215, 'member'),
(218, 50, 66, 'adviser'),
(219, 54, 51, 'leader'),
(221, 61, 53, 'member'),
(222, 61, 52, 'member'),
(223, 61, 54, 'member'),
(224, 63, 56, 'adviser'),
(225, 64, 37, 'adviser'),
(238, 57, 62, 'adviser'),
(242, 57, 50, 'leader'),
(243, 57, 54, 'member');

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
(13, 32, 1, 'submitted', '2025-04-21 18:43:33', NULL, '32_1_1745261013_120ms-BookbindCopy-20241208.pdf', NULL),
(14, 32, 5, 'submitted', '2025-04-21 18:43:48', NULL, '32_5_1745261028_120ms-BookbindCopy-20241208.pdf', NULL),
(15, 32, 2, 'submitted', '2025-04-22 06:50:40', NULL, '32_2_1745304640_32_1_1745261013_120ms-BookbindCopy-20241208.pdf', NULL),
(16, 1, 5, 'approved', '2025-05-13 10:04:22', 'ok na', '1_5_1747130662_NEW_FORMAT.pdf', '');

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
(1, 'International Market Entry Strategies', 'Analysis of strategies used by hospitality firms when entering new international markets. Better understanding of market entry risks and opportunities for expansion.', 'Master in International Hospitality Management', '2025-05-13 09:16:49'),
(2, 'Production engineering', ' product design and development', 'Bachelor of Science in Industrial Engineering', '2025-05-13 09:28:19');

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
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `usertype`, `username`, `program`, `area_of_expertise`, `is_parttime`, `email`, `password`, `first_name`, `last_name`, `gender`, `headline`, `bio`, `profile_image`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`) VALUES
(0, 0, 'Admin', NULL, NULL, NULL, 'ton.agustin09@gmail.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Winston', 'Agustin', 'm', 'SUPER ADMIN', '', '67fccf5d724c92.92568803.png', '2024-10-05 05:55:38', '2024-10-05 05:55:38', '2025-05-13 08:58:54', '0000-00-00 00:00:00', '2025-05-13 08:58:54'),
(37, 0, 'neilv', 'Bachelor of Science in Industrial Engineering', NULL, NULL, 'neilvicedo.ih@gmail.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Niall', 'V', 'o', 'Basta programmer ako', '?', '_defaultUser.png', '2024-10-08 05:14:14', '2024-10-08 05:13:14', '2025-06-11 14:21:28', NULL, '2025-06-11 14:21:28'),
(38, 1, '2021-2-02134', 'Bachelor of Science in Computer Science', NULL, NULL, 'winston.agustin@lpunetwork.edu.ph', '$2y$10$FwRMim1ZTijICfN7cNJ/f.1G6pLLXZIV3/fBfXerZGyplkP4gtCae', 'Winstonini', 'Paganini', 'm', 'Student Headline', 'This is a student bio.', '67fc6b9c8052d9.27463503.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-05-13 09:01:06', NULL, '2025-05-13 09:01:06'),
(39, 1, 'student', 'Bachelor of Science in Computer Science', '', 0, 'student@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Juan', 'Delacruz', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-11 14:47:33', NULL, '2025-04-11 14:47:33'),
(40, 1, 'student3', 'Bachelor of Science in Computer Science', NULL, NULL, 'student3@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jerald Ryan', 'Gerona', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-05-01 06:07:17', NULL, '2025-05-01 06:07:17'),
(41, 1, 'student4', 'Bachelor of Science in Computer Science', NULL, NULL, 'student4@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Ivan Kerwin', 'Ilano', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-18 00:55:49', NULL, '2025-04-18 00:55:49'),
(42, 1, 'student5', 'Bachelor of Science in Computer Science', NULL, NULL, 'student5@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Linus Karl', 'Sambile', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-12 02:14:10', NULL, '2025-04-12 02:14:10'),
(43, 1, 'student6', 'Bachelor of Science in Computer Science', NULL, NULL, 'student6@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Yusuf', 'Mirasol', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-11-12 17:18:56'),
(44, 1, 'student7', 'Bachelor of Science in Computer Science', NULL, NULL, 'student7@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Keith Andrei', 'Marpuri', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-11-12 17:24:45'),
(45, 1, 'student8', 'Bachelor of Science in Computer Science', '', 0, 'student8@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Renzo', 'ViÃ±as', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-23 14:32:11', NULL, '2024-10-09 22:07:06'),
(46, 1, 'student9', 'Bachelor of Science in Computer Science', NULL, NULL, 'student9@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Earl Stephen', 'Tacda', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(47, 1, 'student10', 'Bachelor of Science in Computer Science', NULL, NULL, 'student10@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Cassandra', 'Roxas', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(48, 1, 'student11', 'Bachelor of Science in Computer Science', NULL, NULL, 'student11@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kedd Cyrus', 'Alegre', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-18 12:04:30', NULL, '2025-04-18 12:04:30'),
(49, 1, 'student12', 'Bachelor of Science in Computer Science', NULL, NULL, 'student12@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Gian David ', 'Marasigan', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(50, 1, 'student13', 'Bachelor of Science in Computer Science', NULL, NULL, 'student13@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Kenneth Joshua', 'Pedero', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-12-11 01:51:05'),
(51, 1, 'student14', 'Bachelor of Science in Computer Science', NULL, NULL, 'student14@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Christann', 'Nabablit', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(52, 1, 'student15', 'Bachelor of Science in Computer Science', NULL, NULL, 'student15@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Gerche Jay', 'Balaan', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(53, 1, 'student16', 'Bachelor of Science in Computer Science', NULL, NULL, 'student16@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'John Lyrick', 'Jonson', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-12-11 01:56:08'),
(54, 1, 'student17', 'Bachelor of Science in Computer Science', NULL, NULL, 'student17@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Von Zachary Benedict', 'Fadri', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(55, 1, 'student18', 'Bachelor of Science in Computer Science', NULL, NULL, 'student18@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Joshua', 'Catampongan', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(56, 1, 'student19', 'Bachelor of Science in Computer Science', NULL, NULL, 'student19@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Nineteen', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(57, 1, 'student20', 'Bachelor of Science in Computer Science', NULL, NULL, 'student20@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Twenty', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(58, 2, 'staff1', 'Bachelor of Science in Computer Science', '', 0, 'sean.gono@lpu.edu.ph', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Sean Charlston', 'Gono', 'm', 'BOI', 'This is a BOI..,.', '67545c47388503.11452602.jpg', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-06-02 14:27:56', NULL, '2025-06-02 14:27:56'),
(59, 2, 'staff2', 'Bachelor of Science in Computer Science', NULL, NULL, 'staff2@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Toni', 'Granado', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-18 00:54:16', NULL, '2025-04-18 00:54:16'),
(60, 2, 'staff3', 'Bachelor of Science in Computer Science', NULL, NULL, 'staff3@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Jerian', 'Peren', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-12 03:53:44', NULL, '2025-04-12 03:53:44'),
(61, 2, 'staff4', 'Bachelor of Science in Computer Science', NULL, NULL, 'staff4@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Raymund', 'Constante', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-03-26 06:56:06', NULL, '2025-03-26 06:56:06'),
(62, 2, 'staff5', 'Bachelor of Science in Information Technology', NULL, NULL, 'staff5@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Laarnie', 'Carlos', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-03-15 00:14:36', NULL, '2025-03-15 00:14:36'),
(63, 2, 'staff6', 'Bachelor of Science in Information Technology', NULL, NULL, 'staff6@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Delia', 'Fainsan', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:10:45', NULL, '2024-10-09 22:07:06'),
(64, 2, 'staff7', 'Bachelor of Science in Information Technology', NULL, NULL, 'staff7@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Elmer', 'Matel', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:11:34', NULL, '2024-10-09 22:07:06'),
(65, 2, 'staff8', 'Bachelor of Science in Computer Engineering', NULL, NULL, 'staff8@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Alyssa Paola', 'Pocaan', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2024-12-16 19:11:13', NULL, '2024-10-09 22:07:06'),
(66, 2, 'staff9', 'Bachelor of Science in Computer Engineering', 'Software Engineering', 0, 'staff9@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Elizabeth', 'Nsubuga', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-11 03:59:11', NULL, '2024-10-09 22:07:06'),
(67, 2, 'staff10', 'Bachelor of Science in Computer Engineering', 'Software Engineering', 0, 'staff10@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Anabella ', 'Doctor', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-12 02:06:02', NULL, '2025-04-12 02:06:02'),
(71, 1, 'ilano', NULL, NULL, NULL, 'ilano@ilano.ilano', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', '', '', NULL, '', '', '_defaultUser.png', '2024-10-27 18:12:34', '2024-10-27 18:10:53', '2024-11-11 06:39:12', NULL, '2024-10-27 18:12:43'),
(78, 1, '2021-2-02135', 'BSIT', NULL, NULL, 'a.a@lpunetwork.ude.ph', '$2y$10$tEHacn4uVW8p3nOKcU5Dkuhwb82lFNPhLMj7dx5jqn8JseDUBi3fi', 'a a', 'a', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(79, 1, '2021-2-03212', 'b b', NULL, NULL, 'asdada@asdadsa.gg', '$2y$10$F1A0hMd4/17jslsY1A1DdeKCVAUgDMqRAtuBtClyHhKr3g0qEr/gi', 'b', 'b', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, '2025-04-07 17:40:21'),
(80, 0, 'DCS-0002', NULL, '', 0, 'alyssa.pocaan@lpu.edu.ph', '$2y$10$39Dwj.Fb8dxWFKnxrCLyIeCmNeHCsSxNZ6jzoZ2cr9jG/2eBOZecq', 'Alyssa', 'Pocaan', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, '2025-04-11 03:25:23'),
(82, 1, '2021-2-01217', 'BSIT', NULL, NULL, '2021-2-01217@lpunetwork.edu.ph', '$2y$10$nU2n2I45gowP8AarIfhfYuTKRhHZgBuvAacZiGO8GXS0MMzAHCVUG', 'REGIL KENT* CASTANEDA', 'ANTONIO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(83, 1, '2021-2-00501', 'BSIT', NULL, NULL, '2021-2-00501@lpunetwork.edu.ph', '$2y$10$HWkr7kBidetWNlW74CgjcuXTYN5EaeS3/z2L.f3m4IE9koYvvrdE6', 'LEIHNARD CHRISTIAN LASACA', 'ARAGOZA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(84, 1, '2021-2-00091', 'BSIT', NULL, NULL, '2021-2-00091@lpunetwork.edu.ph', '$2y$10$E9QGb/AzAz93/VmTkh4Q8.A9juY9CyrO.mrkropUey2IwGHkQI3hO', 'JOHN LOUISSE PINGAD', 'ARNAN', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(85, 1, '2021-2-00944', 'BSIT', NULL, NULL, '2021-2-00944@lpunetwork.edu.ph', '$2y$10$PTzc1zaKHe5R4sjXvRPgfewY2vWVRvrJJ0HGdeg7gjz9.0jPnDVje', 'JOSHUA LUMACAD', 'BARGO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(86, 1, '2019-2-03872', 'BSIT', NULL, NULL, '2019-2-03872@lpunetwork.edu.ph', '$2y$10$eBw0a4JQTL7eSUB1cgxyDOijF0UPIUHrdFMy2AB78M.d5kP8I5JSy', 'JASON JEB BERNAL', 'BARIZO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(87, 1, '2021-2-01122', 'BSIT', NULL, NULL, '2021-2-01122@lpunetwork.edu.ph', '$2y$10$fXAao8tMmyTIjZbFMASyvetVaA29rSBqKXdWRXpBOALsBz.ihqcDq', 'ASHLEY NICOLE MANALIGOD', 'BODEGON', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(88, 1, '2021-2-00095', 'BSIT', NULL, NULL, '2021-2-00095@lpunetwork.edu.ph', '$2y$10$q9fEpOPmvobkmwqc62gEhe.RGJr7HsDcs4F4ccTJG6fDDd0.oI.tm', 'CHRISTIAN GABRIEL PAREDES', 'CASTRO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(89, 1, '2021-2-00755', 'BSIT', NULL, NULL, '2021-2-00755@lpunetwork.edu.ph', '$2y$10$7wqL3NMNmDX9SyM12uEAIeXLXVhBKLmTNnGxfj8IELdYTHoa.kxtq', 'ROBBIE JULE APOLONA', 'DE LOS SANTOS', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(90, 1, '2019-2-03343', 'BSIT', NULL, NULL, '2019-2-03343@lpunetwork.edu.ph', '$2y$10$KWmsqIbRO/Vz9iT7KELrJO/zqAt/99K5.OeFzGOPSSFjgV3amFUE6', 'ZHONIEL ALSOL', 'DE OCERA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(91, 1, '2021-2-00743', 'BSIT', NULL, NULL, '2021-2-00743@lpunetwork.edu.ph', '$2y$10$hCl7G3gMREWC/J69Xyl87.sPezni1p.hBOQrINyxAFxKEAdSinOi2', 'JOHN ANDREI SIMPELO', 'JOCO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(92, 1, '2021-2-00958', 'BSIT', NULL, NULL, '2021-2-00958@lpunetwork.edu.ph', '$2y$10$SjkWBUgtenIJjiAV3h0oIewDXypC4ReITX454XeFKNllmzIMlACbG', 'ANDREA DIANA MATEL', 'LINO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(93, 1, '2021-2-01044', 'BSIT', NULL, NULL, '2021-2-01044@lpunetwork.edu.ph', '$2y$10$icEFNyBFZYb1QsemI7Xqjebxt2a1OsRsCKhxdZ1Z5ByOb3c5uK0au', 'PATRICK GABRIEL PASCUAL', 'MALATE', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(94, 1, '2021-2-00834', 'BSIT', NULL, NULL, '2021-2-00834@lpunetwork.edu.ph', '$2y$10$vEy2rWNWnQrR2CdKO79jOOAanMA0Q2Ri.1YI7NKSbTrzQIvh43Owu', 'LITO JARDIN', 'MALIGRO JR.', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(95, 1, '2021-2-00087', 'BSIT', NULL, NULL, '2021-2-00087@lpunetwork.edu.ph', '$2y$10$aoPCriFwEfm8Idz1xlRfCug2R/hWAZwhcJJwK61ZO.izfWK/1rBp.', 'MICHELLE* BAES', 'MAYOL', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(96, 1, '2021-2-00362', 'BSIT', NULL, NULL, '2021-2-00362@lpunetwork.edu.ph', '$2y$10$XcXHYToibl6usePQl42Euu7LH4VtJGGJf3x2u8uHMEBmgIOX4YCKK', 'ART GELO CASIDSID', 'NECIA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(97, 1, '2020-2-02013', 'BSIT', NULL, NULL, '2020-2-02013@lpunetwork.edu.ph', '$2y$10$cxXMY2tCOdgdrBG3g0.rVeBqBk.2leGqcUaEg0R/U6QGzQN6MFNyi', 'CHRISTOPHER SOME .', 'OPLE', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(98, 1, '2021-2-00420', 'BSIT', NULL, NULL, '2021-2-00420@lpunetwork.edu.ph', '$2y$10$pCZKIa0qopkyT0r47zkHee7q7BfVtdB7ABen0an/0z9ZDDKoA.24K', 'CHARLES ADRIAN CENIZA', 'PULA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(99, 1, '2021-2-02010', 'BSIT', NULL, NULL, '2021-2-02010@lpunetwork.edu.ph', '$2y$10$8GIjHIsYzU509Q6opxpRKuXCrUWuJb72nIIKItQ3e6276npnINJnG', 'JOWEN MATTHEW ENG', 'RABAGO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(100, 1, '2021-2-01997', 'BSIT', NULL, NULL, '2021-2-01997@lpunetwork.edu.ph', '$2y$10$UhXrGzAaWTzDub4twZgj7uZ9gOQD6Qx35N6dVbgEBQIue3zOrLgZK', 'JUSTINE* ALLARCES', 'RAZON', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(102, 1, '2021-2-00415', 'BSIT', NULL, NULL, '2021-2-00415@lpunetwork.edu.ph', '$2y$10$U1KXf4vg5uDYl/xzXJY8keqdgGBU1q0JAfYUAAWW291jDd8NN69fS', 'IVANN CEDRIC ALMODOVAR', 'SALDIVAR', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(103, 1, '2021-2-02166', 'BSIT', NULL, NULL, '2021-2-02166@lpunetwork.edu.ph', '$2y$10$xBtABuxCahnoD35QyTl7iO5.UruQmLNHUWC8i6iBZ18xzwSH15Oui', 'JERZE STEPHANIE MORTEGA', 'SAMONTE', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(104, 1, '2021-2-01304', 'BSIT', NULL, NULL, '2021-2-01304@lpunetwork.edu.ph', '$2y$10$SgMEWBkZDHK4M5PIvETcX.aSJbIBB64DtaPOpjSw7Bwaux0.pH.rq', 'MOREEN SANTIAGO', 'SAMPANG', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(105, 1, '2021-2-01019', 'BSIT', NULL, NULL, '2021-2-01019@lpunetwork.edu.ph', '$2y$10$JogZPA2QbhJ907crhe895eTDaec9R6UFj8WFvNPFLHLOcMpj.JHJu', 'JEREMY JUB ABAQUITA', 'SANDOVAL', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(106, 1, '2021-2-00021', 'BSIT', NULL, NULL, '2021-2-00021@lpunetwork.edu.ph', '$2y$10$Nqh41VoThd6VqR2EO6hQXekRZZz5d8fKHFvHBy0Z7vnnJrhrUX1ga', 'THOMAS JACOB BAUTISTA', 'TABARANZA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(107, 1, '2021-2-01191', 'BSIT', NULL, NULL, '2021-2-01191@lpunetwork.edu.ph', '$2y$10$hucdws2gWittbkjwgIVDPuibrlAACTSU4CxqRt/A/5yJrjjtgakD.', 'ALLYSA KATE SANTIAGO', 'TROMPETA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(108, 1, '2021-2-00827', 'BSIT', NULL, NULL, '2021-2-00827@lpunetwork.edu.ph', '$2y$10$w5zC6Z.Mtifb2AJLUHjx7OqcwWcU6onC..aleWrapPCFe.VtxCl0a', 'SEAN MANUEL PALACOL', 'YATER', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(109, 1, '2020-2-01334', 'BSIT', NULL, NULL, '2020-2-01334@lpunetwork.edu.ph', '$2y$10$Lg/vxhytPgVNZPhtN1XjjOYskQhVoHVbEPze6owzwyvnlS1YzJc4m', 'HANELY MORALES', 'YOO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(110, 2, '2019-0119F', NULL, 'Software Engineering', 0, 'amanda.menta@lpu.edu.ph', '$2y$10$SNiOswuR7UIVpUmscexWqu45HHsK4lYwhG3ueRgCCGVDjzNXxSh8G', 'Amanda', 'Menta', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(111, 1, '2021-2-02590', 'BSIT', NULL, NULL, '2021-2-02590@lpunetwork.edu.ph', '$2y$10$JIwSCmgx.5IadIGwkkICKudOjory86saKUoGzBkjk/d.ISgPbhFIa', 'VAUGHN JACOB* CAMARCE', 'BASCUGUIN', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(112, 1, '2019-2-02303', 'BSIT', NULL, NULL, '2019-2-02303@lpunetwork.edu.ph', '$2y$10$zYK8.q9lSSBMEndhboXZi.wiV.SQL/S5L/2LBobi62R1FtFYRh2Zu', 'SHANLEY LOUISSE AMON', 'BAUZON', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(113, 1, '2021-2-00326', 'BSIT', NULL, NULL, '2021-2-00326@lpunetwork.edu.ph', '$2y$10$dvvaIUx.OIus46olybdX9uUuqFpMcWVfmOwZ.UY1Z2zE6tpkCE4U6', 'MILES ANDREI LUPISAN', 'CALAZAN', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(114, 1, '2017-2-02314', 'BSIT', NULL, NULL, '2017-2-02314@lpunetwork.edu.ph', '$2y$10$npkneJJd28368fZCY9ikY.vXrgCBFmZq76A34bgB4i3QfZecpvjO2', 'RAFAEL CARLO SUGUI', 'CAMPO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(115, 1, '2021-2-02577', 'BSIT', NULL, NULL, '2021-2-02577@lpunetwork.edu.ph', '$2y$10$1ZPQrj3szxdfI471QAh2h.jYniL1kEBzDyvd58cDGmx/d6xrU6ylK', 'ROLANDO III  MACATANGAY', 'CORONADO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(116, 1, '2021-2-01472', 'BSIT', NULL, NULL, '2021-2-01472@lpunetwork.edu.ph', '$2y$10$4tpfgcF9Z/8Fg1qLFvNHeOMjLmRsRUYkHcKyxo9WhAsUPrfr5ZBRi', 'CEDRIC JAMES ROXAS', 'DELOS REYES', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(117, 1, '2021-2-01946', 'BSIT', NULL, NULL, '2021-2-01946@lpunetwork.edu.ph', '$2y$10$J20PC8LP7ZvnzzTw6okOX.Je1tEr/.uq4SJDyXBosYswdbzgudONC', 'MICHAEL ONEAL TABLANTE', 'DESPI', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(118, 1, '2022-2-02842', 'BSIT', NULL, NULL, '2022-2-02842@lpunetwork.edu.ph', '$2y$10$YOiWLL9u8IbO992XFzocZOLKA.mLv/uqpAQOHeSAViOOXmKGYL40.', 'SHANE ANGELIC ARA?AS', 'DIAZ', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(119, 1, '2022-2-02621', 'BSIT', NULL, NULL, '2022-2-02621@lpunetwork.edu.ph', '$2y$10$dmnk1T.FQ7TVflA1BxPxu.Bj/rSRMNojbjGqv.7NzCPttcXRhcwUe', 'LINDSAY BORRES', 'ESCALA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(120, 1, '2020-2-02151', 'BSIT', NULL, NULL, '2020-2-02151@lpunetwork.edu.ph', '$2y$10$zTeLvwahm8UM6vYG8dv5O.dELgjeYvNIB0JnlYabzRK73IiC7fo/W', 'DANE ISAIAH NOVA', 'EYAYA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(121, 1, '2021-2-01881', 'BSIT', NULL, NULL, '2021-2-01881@lpunetwork.edu.ph', '$2y$10$U/NTUFUMhDG2jwOTUlvQauOmksB3RKEa486wyozbNiXAE9elmVbPm', 'VINCE LAWRENCE N/A', 'GARGALLO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(122, 1, '2021-2-01990', 'BSIT', NULL, NULL, '2021-2-01990@lpunetwork.edu.ph', '$2y$10$XW0wQBAYgTtRSRiRMQv/9eVI.2KaBX/DVCnznrm5wJ0jf4gHcC7/O', 'MATTHEW GABRIEL ESPINEDA', 'ISABELO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-22 06:44:23', NULL, '2025-04-22 06:44:23'),
(123, 1, '2021-2-00390', 'BSIT', NULL, NULL, '2021-2-00390@lpunetwork.edu.ph', '$2y$10$IyXXAef/bk/3rtn1llqPl.9hGKEEWf1JcIN9ORqDWH1TkLUmDKkHC', 'JOB DONES', 'JUNTURA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(124, 1, '2021-2-01795', 'BSIT', NULL, NULL, '2021-2-01795@lpunetwork.edu.ph', '$2y$10$OeHujFoRGao8q.4tzhkrA.i7FMT4kAoX9thRUbAK98C7JWkQURH6S', 'JIBSON PAUL ANDAL', 'LAMBINICIO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(125, 1, '2021-2-01317', 'BSIT', NULL, NULL, '2021-2-01317@lpunetwork.edu.ph', '$2y$10$CNuw2Ay5gbi1rE4Lts6bWeLylb7zfCByTAbf6LGLKZovWy.YEIHPy', 'JOHANH STEVENSON ALVAREZ', 'LEONARDO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(126, 1, '2021-2-01578', 'BSIT', NULL, NULL, '2021-2-01578@lpunetwork.edu.ph', '$2y$10$/zF918qbmW9CIILglzs2sOOsuBzFPUrVQz7RDNDuTpzDD..HJ5.1y', 'RAFHAEL IANNY SUMADIA', 'LIM', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(127, 1, '2021-2-01916', 'BSIT', NULL, NULL, '2021-2-01916@lpunetwork.edu.ph', '$2y$10$8a7EMX3o7Mm8fqVY1HJQ7OhaKFjI2WGHo2ghlELil2NVDKmy7p3iK', 'RYAN PAUL BASAN', 'LOMONGO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(128, 1, '2022-2-02550', 'BSIT', NULL, NULL, '2022-2-02550@lpunetwork.edu.ph', '$2y$10$hj96Ae3X0gDl1e9/4MpEm.LVjjrvH7u9EHIcQw30Ykgg62gVi6V5W', 'RALPH PAULO BERNADAS', 'LORENZANA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(129, 1, '2019-2-02524', 'BSIT', NULL, NULL, '2019-2-02524@lpunetwork.edu.ph', '$2y$10$g0NyRx2Nl7Zozg1nfaTUl.bs3dFkATfUwxdG.REv.rxP3buVKqJ7S', 'MICHAEL LEARNS GONZALES', 'MALLARI', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(130, 1, '2021-2-02003', 'BSIT', NULL, NULL, '2021-2-02003@lpunetwork.edu.ph', '$2y$10$LmR/wMMIrMW4FTR69mGDROyERIhtHPGZFGX9SCKn9NZJ9yASOWqUy', 'NEIL PATRICK T.', 'MARCOS', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(131, 1, '2020-2-01137', 'BSIT', NULL, NULL, '2020-2-01137@lpunetwork.edu.ph', '$2y$10$93L86N1dEkc5eVVdaS3iPOsaFeYGG85oqL8sX.GON.ZLAbPMBrvIe', 'LEENARD* LALIMOS', 'MEDRANO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(132, 1, '2018-2-01489', 'BSIT', NULL, NULL, '2018-2-01489@lpunetwork.edu.ph', '$2y$10$cbCnrPPFPJ1YGZB3a7OWEOyCyNapMungxEQlLSgDgleyG9lwK1QCS', 'CARL BRYAN DE PADUA', 'MONTECILLO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(133, 1, '2020-2-02020', 'BSIT', NULL, NULL, '2020-2-02020@lpunetwork.edu.ph', '$2y$10$rRJ9Uiog64Mgoz6VUh6.RusJ77b6xrZqcO5.fgUVcmTn/PbFTfKkW', 'MONICA CUNANAN', 'MORONES', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(134, 1, '2021-2-01081', 'BSIT', NULL, NULL, '2021-2-01081@lpunetwork.edu.ph', '$2y$10$au3JHRrJuKBWB6cIq1qRzuqaQSdFIR5DGVRSwUfKJX4zE6.JR8X5W', 'JULIEN RAPHAEL BALLECER', 'PALMA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(135, 1, '2021-2-02071', 'BSIT', NULL, NULL, '2021-2-02071@lpunetwork.edu.ph', '$2y$10$am9ygQEWD/nTbs8jAfccK./tOJu5NxqYJJAJH5L63vESRBeXW1Gzy', 'JEROME* BOQUIREN', 'PEREZ', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(136, 1, '2021-2-02241', 'BSIT', NULL, NULL, '2021-2-02241@lpunetwork.edu.ph', '$2y$10$lf5FZlYX1El2dfXtK.6i6eyMYJhxKXEj/cjJki0zCXRfI31ZS7r2W', 'SETH JOREL* PADAL', 'PUNZALAN', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(137, 1, '2019-2-02774', 'BSIT', NULL, NULL, '2019-2-02774@lpunetwork.edu.ph', '$2y$10$fvriV.z0uaDNP09uQaJqWu.BGYoDBUzw8hTrvBEa9QX1ond4EcebC', 'CHLOIE EMILY LOURDES ABUTAL', 'REVUELTA', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(138, 1, '2020-2-01798', 'BSIT', NULL, NULL, '2020-2-01798@lpunetwork.edu.ph', '$2y$10$9d3hOXQdTqwctzLupVrTNO.x4sPZOt7h/xYHdhQgmPjDFssMzvsea', 'KYLE PATRICK DADIVAS', 'ROBLES', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(139, 1, '2021-2-01812', 'BSIT', NULL, NULL, '2021-2-01812@lpunetwork.edu.ph', '$2y$10$gxTE.zYWW2akXCRvTf8dh.lxueO/CHJka6KOqpzAIVMylsA15zXOG', 'CARL ALCAZAR', 'ROSALES', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(140, 1, '2021-2-01552', 'BSIT', NULL, NULL, '2021-2-01552@lpunetwork.edu.ph', '$2y$10$zI/LybMEFf0.ubixpKE3h.J24eG0dAClnIVkCFnXM/jRJ6E6nshsC', 'DAVID BRYAN BALOFENOS', 'SALINO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(141, 1, '2019-2-03124', 'BSIT', NULL, NULL, '2019-2-03124@lpunetwork.edu.ph', '$2y$10$rYgpW/YLMzNhTsdNudw7Ge34ynotHBRKdj7VpF3oOGZpRx.Va9XNW', 'LLEAN FRANCIS RODRIGO GANZAN', 'SUMAGUE', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(142, 1, '2019-2-03777', 'BSIT', NULL, NULL, '2019-2-03777@lpunetwork.edu.ph', '$2y$10$/jJBpe0YAh3h2RCp.lbaXOmuwFDPARjh8LLbtcqeO14ip6BhmtvSS', 'KHEN RYU  FELIAS', 'WOO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:28:33', NULL, NULL),
(143, 2, 'staff11', NULL, '', 0, 'staff11@gmail.com', '$2y$10$TyDGEa2K1c5V/Lnq1va1.Oo/azmyqldlHqeQXYsV0U.DkC2Mlfqnq', 'Joven ', 'Cajigas', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-18 00:50:14', NULL, '2025-04-18 00:50:14'),
(144, 2, 'staff12', NULL, '', 0, 'staff12@gmail.com', '$2y$10$r2zgmUnVANVKua.dneR2bOF/cIUUtqcORuP8A8ysNSz8AjybpFbmy', 'Genson', 'Mendoza', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 00:11:40', NULL, NULL),
(145, 2, 'staff13', NULL, 'Software Engineering', 0, 'staff13@gmail.com', '$2y$10$5Ho5cqj8U4WaItK/4RsflOoUONeO49MEWIBboUuR/zgRzP6GBOrr.', 'Leah', 'Santos', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-14 23:41:23', NULL, '2025-04-14 23:41:23'),
(146, 1, '202020202020', 'Bachelor of Science in Information Technology with specialization in Network and Information Security', '', 0, 'test@test.test', '$2y$10$1vODdL13Goo4Yk18.IOHGe4oDA7x7oTtvm.OhrWfkOfBa.EZ9lVoq', 'Lorence', 'Olaes', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 01:58:40', NULL, NULL),
(147, 1, 'villalon', 'Bachelor of Science in Information Technology with specialization in Network and Information Security', '', 0, 'test2@test.test', '$2y$10$Jb..OZsY7dqYYnJp4Ia3fuI2jbce666SN2SzuE.xBIiMimIwsxU62', 'ALEXANDER', 'VILLALON', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 02:48:07', NULL, NULL),
(148, 1, 'test4', 'Bachelor of Science in Information Technology with specialization in Network and Information Security', '', 0, 'test4@test.com', '$2y$10$mxbowc71b4Msu0E2Oxwjq.hbmWMQK1FFn0q46b5PP3NH1/mOgvFJu', 'RYAN PAUL', 'LOMONGO', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2025-04-11 03:24:31', '2025-04-12 02:48:07', NULL, NULL),
(149, 1, '2021-2-00001', 'Marketing Management', NULL, NULL, 'genesis.atienza@lpunetwork.ude.ph', '$2y$10$hAkClg1HNimgxdSGOdiTquYL.ziCUx7aWF74ij1slZSJcgYVzcoLG', 'Genesis G.', 'Atienza', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(150, 1, '2021-2-00002', 'Marketing Management', NULL, NULL, 'janna.aranda@lpunetwork.ude.ph', '$2y$10$zrXcEi1VEwGSwPUFA61U.uI9Kvfi3tUD0Fy7e0jil7ZSbxBeElE9q', 'Janna Louise F.', 'Aranda', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(151, 1, '2021-2-00003', 'Marketing Management', NULL, NULL, 'mary.alcantara@lpunetwork.ude.ph', '$2y$10$XjyV6PIyw2vzasDLpTrbxukndvCUVDcumNTTMdY5bZ.dqjDroBqM6', 'Mary Stephanie A.', 'Alcantara', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(152, 1, '2021-2-00004', 'Marketing Management', NULL, NULL, 'alphonso.ilustrisimo@lpunetwork.ude.ph', '$2y$10$h5nQgzy3qEIAFT77jxszq.S7P1hzV80vBmGBUlETCelhXl1Zm./yy', 'Alphonso Uriel R.', 'Ilustrisimo', NULL, NULL, NULL, '_defaultUser.png', '2025-04-11 03:24:31', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(153, 1, '2021-2-00005', 'Marketing Management', NULL, NULL, 'kenneth.marapao@lpunetwork.ude.ph', '$2y$10$8mOq.Cy2txJxzuBQwc5pju//tm9CcuLzg6jtzxVzazcGV3llnfrjy', 'Kenneth Lejan', 'Marapao', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(154, 1, '2021-2-00006', 'Marketing Management', NULL, NULL, 'leane.abellar@lpunetwork.ude.ph', '$2y$10$pMxBrB5DsFhVnnRZnFt2DOJt84fBGF1jwZsJ2chhymSA8R/v/dr8O', 'Leane Marie S.', 'Abellar', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(155, 1, '2021-2-00007', 'Marketing Management', NULL, NULL, 'shania.basa@lpunetwork.ude.ph', '$2y$10$2SCzpQjk9UpLHucP0E5ccuxK6.tO6H.GgfJI2eM4hvotqlkVruBum', 'Shania Shannen', 'Basa', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(156, 1, '2021-2-00008', 'Marketing Management', NULL, NULL, 'jdrrl.guevarra@lpunetwork.ude.ph', '$2y$10$lOtavbePPgkGDrrZG9GF5uA8Z5LVOTwzsL23lHzyGajxoIYbLMCFa', 'Jdrrl M.', 'Guevarra', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(157, 1, '2021-2-00009', 'Marketing Management', NULL, NULL, 'ivan.herrera@lpunetwork.ude.ph', '$2y$10$QZfK53Dc7b3FT/gTmr7AY.RKECzOLYdG6ZghQ/VRaGYWAvLc2VsF2', 'Ivan Joshua R.', 'Herrera', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(158, 1, '2021-2-00010', 'Marketing Management', NULL, NULL, 'karl.mencias@lpunetwork.ude.ph', '$2y$10$5WDgWOjaHh0TOj8r5ucMMuMHIiWF6JRwqA8L9GdwnGnNF7u0/das.', 'Karl Rafael B.', 'Mencias', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(159, 1, '2021-2-00011', 'Marketing Management', NULL, NULL, 'ma.belir@lpunetwork.ude.ph', '$2y$10$QSJyQi8b5j5CeREutK0GPOyVJivuAFh0QO3/Fai6tPHqgbrx6cXl6', 'Ma Nica', 'Belir', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(160, 1, '2021-2-00012', 'Marketing Management', NULL, NULL, 'levy.delmonte@lpunetwork.ude.ph', '$2y$10$Tdckdn.bHn3SXsLu1H9MP.nzoUmokmuo0MB15F6uxd7LeGhG0ta3m', 'Levy John', 'Delmonte', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(161, 1, '2021-2-00013', 'Marketing Management', NULL, NULL, 'louis.hayag@lpunetwork.ude.ph', '$2y$10$DpJr3ZAWE/d/1rOERN7kVu9ye2uS75Nqvl4oAyrbLgs9bxRQ7AsF6', 'Louis Ailamei', 'Hayag', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(162, 1, '2021-2-00015', 'Marketing Management', NULL, NULL, 'shaneiah.torcelero@lpunetwork.ude.ph', '$2y$10$q98PQjseLjXnTy5eNy9c2.GH2J3Kkm32D1an.pOTslmapWfmSc0Xu', 'Shaneiah', 'Torcelero', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(163, 1, '2021-2-00016', 'Marketing Management', NULL, NULL, 'denielle.dimaculangan@lpunetwork.ude.ph', '$2y$10$D87f9E/iw9T3ynn.D1QyrOOquTxehO7UBnOfjsMouJjvQeAQ80Kjm', 'Denielle', 'Dimaculangan', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(164, 1, '2021-2-00017', 'Marketing Management', NULL, NULL, 'erwin.gile@lpunetwork.ude.ph', '$2y$10$ko8ofFOWpejZjTZBIZPgB.X1wi7zQ6Jhn//6QqL5ei/3LrIuqqkIK', 'Erwin', 'Gile', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(165, 1, '2021-2-00018', 'Marketing Management', NULL, NULL, 'colline.reformoso@lpunetwork.ude.ph', '$2y$10$uCDvzEUpYjXQXgQkaUx1POnf0f08DY8yhOPRM9hMRfvJdM5RbywAK', 'Colline Quisha', 'Reformoso', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(166, 1, '2021-2-00019', 'Marketing Management', NULL, NULL, 'alliah.tucbo@lpunetwork.ude.ph', '$2y$10$1HEoCYtcfmeD87zf8OjmPuG0k1NTdwFkqdWz/yT9lclguRiVX5z7m', 'Alliah Nicole', 'Tucbo', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(167, 1, '2021-2-00020', 'Marketing Management', NULL, NULL, 'kurt.ugalingan@lpunetwork.ude.ph', '$2y$10$I2PXytUJIcsT2mGpHS9BIewJmL2vn5xPDxQCZnw6vgmTdE310.222', 'Kurt', 'Ugalingan', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(172, 2, 'Vincent', 'BSMM', 'Qualitative', 0, 'email1@ewan.com', '$2y$10$OAhq5dAI4aSuOrH/gv/wseOg6OjO0I6xMjlptdJFaChHggacOIdbu', 'Vincent', 'Cortiñas', '', '', '', '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(173, 2, 'Lucky', 'BSMM', 'Qualitative', 0, 'email2@ewan.com', '$2y$10$fbywify5DeylcrcfpMQuYOGI/hsNVlRrwO5OROcPLC5sAJhry/eam', 'Lucky Cedric', 'Guyamin', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-25 04:00:06', NULL, '2025-04-25 04:00:06'),
(174, 2, 'Christian', 'BSMM', 'Qualitative', 0, 'email3@ewan.com', '$2y$10$3qPMS0Z2OfQ28.pbN0VMGOnNhii6SGMQ4gI52KXIZPSVIppioTaUy', 'Christian', 'Matriano', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(175, 2, 'Jaysrr', 'BSMM', 'Qualitative', 0, 'email4@ewan.com', '$2y$10$SUdWWsDiuP9CN02ChoTjKe.RTeTkQ5TD2i5ybke5Wo598BkoJupne', 'Jaysrr', 'Maranan', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(176, 2, 'Reynaldo', 'BSMM', 'Qualitative', 0, 'email5@ewan.com', '$2y$10$qAHVMVvrS4Z8n4JYGgmkUOOlJZWLrK2RgnXDt.MEvRpM2Bz2LpC2y', 'Reynaldo', 'San Mateo', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(177, 2, 'Divine', 'BSMM', 'Qualitative', 0, 'email6@ewan.com', '$2y$10$6Q7uKtxQAcp5u39TJOmsb.TDZxDDOCLbuVLHYoLXO/JzCjuPz29kW', 'Divine', 'Dela Cruz', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:34:05', NULL, '2025-04-22 02:34:05'),
(178, 1, 'Irish', 'BSMM', '', 0, 'irish@email.com', '$2y$10$IsFq0ADlK5VPcqKyHaZiKekIS.a.k1aHsy4ltJprbh6t9tkzWlnYa', 'Irish Jade', 'Peñaflorida', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(179, 1, 'Crystal', 'BSMM', '', 0, 'crystal@email.com', '$2y$10$kzPFB5HcWVQKO2F1o.SIMuOGi6eVQCxUMHGkUPfjc8ig0X8yu4Lce', 'Crystal Mizzy', 'Abeabe', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(180, 1, 'Lovely', 'BSMM', '', 0, 'lovely@email.com', '$2y$10$ial19Me.jA/IZihKqW4tI.vfT9K4ZYkEiCwYgfQlksJQizuqSYWTK', 'Lovely', 'Castillo', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(181, 1, 'Harrianne', 'BSMM', '', 0, 'harrianne@email.com', '$2y$10$4md6/7GkkGyb/hlQcMrvBOof6CLMoHnwFnGpk4vjK7.O9PgwJwu6u', 'Harrianne', 'Hormillosa', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(182, 1, 'Julienne', 'BSMM', '', 0, 'julienne@email.com', '$2y$10$K6XPejcbf6vvIIOb4b6F2eonkDMIvFQgQrhAMQIWHxP78CDFO4EkO', 'Julienne', 'Marilag', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(183, 1, 'Iman', 'BSMM', '', 0, 'iman@email.com', '$2y$10$KLYUFCqQrd99hIYmEIvCWeJ3sKzaQrEtrQB0CrBzFzwtVI4gTwf8.', 'Iman', 'Martinez', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(187, 1, 'Irish2', 'BSMM', '', 0, 'Irish2@email.com', '$2y$10$TPKGwPiOt5zmWnV1QWGPbuxZZopVMCqVdZWhS.PM52xU8mxja3ZR.', 'Irish', 'Cayapado', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(188, 1, 'Ailene', 'BSMM', '', 0, 'ailene@email.com', '$2y$10$TJuXXFoMeIbVWp3tU2uIZeaGWNU8DtExMnoSytsjo2nkMCvE09kFu', 'Ailene', 'Cerdeña', NULL, NULL, NULL, '_defaultUser.png', '2024-10-09 22:07:06', '2024-10-09 22:07:06', '2025-04-22 02:14:00', NULL, NULL),
(189, 1, 'Kyla', 'BSMM', '', 0, 'kyla@email.com', '$2y$10$Xqgsl923z0n1XC5WrCGbruJ/c7aclANX9/F6AMhhkIYITtYgrDDs6', 'Kyla Jules', 'Chua', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:39:20', NULL, NULL),
(190, 1, 'Yannyzha', 'BSMM', '', 0, 'yannyzha@email.com', '$2y$10$/FTmaTcWEWTEn0DCcDADM.LUH0e5MDHJZyOcKDj5dUMnhfyS8NaBS', 'Yannyzha Mae', 'Estor', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:40:23', NULL, NULL),
(191, 1, 'Leanne', 'BSMM', '', 0, 'leanne@email.com', '$2y$10$6tY.QtA6aa4Z8QMH3SeBdu/V5OY5Kc3OTrkU.MY1FgHdD/8icnr7K', 'Leanne Chesly', 'Ulan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:49:00', NULL, NULL),
(192, 1, 'Mark', 'BSMM', '', 0, 'mark@email.com', '$2y$10$awKaz5HO0/FTelA9WMk9i.rVfiQBwmrYfh/LzMq0ncFOJfXsgb.lO', 'Mark Kelly', 'Comcom', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:50:12', NULL, NULL),
(193, 1, 'Angel', 'BSMM', '', 0, 'angel@email.com', '$2y$10$4h/.qBC39oMlp4c8PmB7euJb.6J5APc.0ItMfzvjeFXA2zexkSPjS', 'Angel', 'Garraez', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:50:52', NULL, NULL),
(194, 1, 'Kim', 'BSMM', '', 0, 'kim@email.com', '$2y$10$GpZwq04QRBSrt0sNU9rTIu2EjvWsgA0vMRGFETt1gcAPdWzie8bA6', 'Kim Louise', 'Pagkaliwangan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:51:42', NULL, NULL),
(195, 1, 'Anmol', 'BSMM', '', 0, 'anmol@email.com', '$2y$10$Yi4QGyLfRgPhylwnrCRLBOhjzNTS/xkHILaryktuoq.bnpkOFZsLW', 'Anmol Deep', 'Singh', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:52:26', NULL, NULL),
(196, 1, 'Anne', 'BSMM', '', 0, 'anne@email.com', '$2y$10$6EwoY2lyDXESC2D8GNq8SuQevz6zajkGcLb0ydekO9U6tUHv4A/nW', 'Anne Ritsel', 'Arica', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:53:43', NULL, NULL),
(197, 1, 'Al ', 'BSMM', '', 0, 'al@email.com', '$2y$10$ODcE2ZEE1L7EnHLYcX7oqeVZvz.oABjHyAYNBr4GLO6DgFKQLASwa', 'Al Hassan', 'Hatem Jasser', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:54:50', NULL, NULL),
(198, 1, 'Sean', 'BSMM', '', 0, 'sean@email.com', '$2y$10$q/bEwt5GYO8LR3QX7zj3FOsy4NP3ge44G0.OTg9PFiiiYz24tjreq', 'Sean Hernan', 'Solis', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:55:39', NULL, NULL),
(199, 1, 'Mabel', 'BSMM', '', 0, 'mabel@email.com', '$2y$10$Wac2gd6Iv8YMKUlphwhmT.NbbzD728d12bBkrCQlZRz/GHknbUP2q', 'Mabel Anne', 'Tigley', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 02:56:26', NULL, NULL),
(200, 1, 'Missiella', 'BSMM', '', 0, 'missiella@email.com', '$2y$10$FmfE9BVzp/zmfFE33xMBHO0gsWqPRYwhRgQ36XuD0Ti.MACjkCjba', 'Missiella Nicole', 'Esguerra', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-22 03:01:18', NULL, NULL),
(201, 0, 'Renzy', 'ComSci', '', 0, 'renzv@gmail.com', '$2y$10$J2jcI3dMJ1iyPkPZhWYSTuTVV1vIU440moDr2IODZbALmZhWUW5z6', 'Renz', 'Dimagiba', '', '', '', '_defaultUser.png', NULL, NULL, '2025-04-23 14:12:11', NULL, NULL),
(211, 1, '123', 'IT', NULL, NULL, '123@lpunetwork.edu.ph', '$2y$10$CWom67tSl3wT8uv5rDdRjeDfojEbNrIGCYZgAGpESTy2f/jKyh6zK', 'Lompot', 'Lompot', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-23 14:52:16', NULL, NULL),
(212, 1, '456', 'Comsci', NULL, NULL, '456@lpunetwork.edu.ph', '$2y$10$0MtGfu2y.g/JBIK1U76tsej4FtF0uu8sDwssMdbgKCSyKbgKO8E..', 'Elijah', 'Elijah', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-23 14:52:16', NULL, NULL),
(213, 1, '789', 'IT', NULL, NULL, '789@lpunetwork.edu.ph', '$2y$10$NmAY4W0tEPOyIGBHz.EVNeVwwm4rFpgeiTxMeuzBleuuYjx2tpEaO', 'Ryan', 'Jepard', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-23 14:52:17', NULL, NULL),
(215, 1, '2017-2-02440', 'Biology', '', 0, 'elisa.lorenzana@lpunetwork.edu.ph', '$2y$10$NWGBvk/AgNniFjsxjm2MnuxPQo4xUthSPnAK7WF/cfvb/9DbfavR.', 'Elisa', 'Lorenzana', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-24 02:41:46', NULL, NULL),
(235, 1, 'tester1tester1', 'BS Architecture', NULL, NULL, 'tester1tester1@lpunetwork.edu.ph', '$2y$10$ZG8unX8yVYD6NFYJ7eUVWuf5oTphYZYCkku747kOWDL/aRQXbzO.u', 'Tester1', 'Tester1', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-25 14:51:22', NULL, NULL),
(238, 1, '2019-2-022234', 'BS Arch', NULL, NULL, '2019-2-022234@lpunetwork.com', '$2y$10$7bD3jaevz5xZWdjCFo94AO8JY.jwBl3hfqLfIEMJeKbbTJwhUkt1m', 'Testing', 'Tester', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-04-25 14:53:29', NULL, NULL);

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
-- Indexes for table `page_content`
--
ALTER TABLE `page_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `defense_panelists`
--
ALTER TABLE `defense_panelists`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2062;

--
-- AUTO_INCREMENT for table `evaluation_per_panel`
--
ALTER TABLE `evaluation_per_panel`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

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
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `requirements`
--
ALTER TABLE `requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `research_titles`
--
ALTER TABLE `research_titles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `rubrics`
--
ALTER TABLE `rubrics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `rubric_criteria`
--
ALTER TABLE `rubric_criteria`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=273;

--
-- AUTO_INCREMENT for table `rubric_groups`
--
ALTER TABLE `rubric_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rubric_group_items`
--
ALTER TABLE `rubric_group_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `rubric_levels`
--
ALTER TABLE `rubric_levels`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `team_requirements`
--
ALTER TABLE `team_requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `thesis_topics`
--
ALTER TABLE `thesis_topics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
