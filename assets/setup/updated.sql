-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2024 at 08:03 PM
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
(29, 1, 60, 62, 66, '2024-12-13', '11:00:00', '12:00:00', 'Defense Room A', 'scheduled', '2024-11-16 15:51:42'),
(30, 2, 60, 63, 64, '2024-12-14', '16:00:00', '17:00:00', 'Defense Room B', 'scheduled', '2024-11-16 15:51:42'),
(31, 3, 59, NULL, NULL, '2024-12-13', '12:00:00', '13:00:00', 'Defense Room A', 'scheduled', '2024-11-16 15:51:42'),
(32, 4, 58, 60, 67, '2024-12-13', '09:00:00', '10:00:00', 'Defense Room A', 'scheduled', '2024-11-16 15:51:42'),
(33, 5, 59, 60, 67, '2024-12-13', '11:00:00', '12:00:00', 'Defense Room B', 'scheduled', '2024-11-16 15:51:42'),
(34, 6, 58, 59, 67, '2024-12-11', '09:00:00', '10:00:00', 'Defense Room B', 'scheduled', '2024-11-16 15:51:42'),
(35, 7, 58, NULL, NULL, '2024-12-10', '10:00:00', '11:00:00', 'Defense Room B', 'scheduled', '2024-11-16 15:51:42');

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
(4, 'APP_DESCRIPTION', 'Advanced Thesis Logistics and AI System for LPU', 'Application description'),
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
(15, 'MAIL_PORT', '465', 'Mail port');

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
(5, 'Book bind Copy', '', '2024-12-04', '2024-11-11 10:52:22');

-- --------------------------------------------------------

--
-- Table structure for table `research_titles`
--

CREATE TABLE `research_titles` (
  `id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `research_titles`
--

INSERT INTO `research_titles` (`id`, `team_id`, `title`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Analysis of Machine Learning Algorithms in Predictive Maintenance', NULL, '2024-10-13 07:15:44', '2024-10-28 02:30:17'),
(2, 2, 'Sustainable Urban Planning: A Case Study of Green Cities', '2024-11-16 02:30:00', '2024-10-13 07:15:44', '2024-10-13 07:15:44'),
(3, 3, 'The Impact of Social Media on Mental Health in Adolescents', '2024-11-17 03:45:00', '2024-10-13 07:15:44', '2024-10-13 07:15:44'),
(4, 4, 'Renewable Energy Integration in Smart Grids', '2024-11-18 06:00:00', '2024-10-13 07:15:44', '2024-10-13 07:15:44'),
(5, 5, 'Cybersecurity Challenges in Internet of Things (IoT) Devices', '2024-11-19 07:30:00', '2024-10-13 07:15:44', '2024-10-13 07:15:44'),
(6, 6, 'The Role of Artificial Intelligence in Healthcare Diagnostics', NULL, '2024-10-13 07:15:44', '2024-10-13 07:15:44'),
(7, 7, 'Blockchain Technology in Supply Chain Management', '2024-11-20 05:15:00', '2024-10-13 07:16:51', '2024-10-13 07:16:51');

-- --------------------------------------------------------

--
-- Table structure for table `rubrics`
--

CREATE TABLE `rubrics` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`structure`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubrics`
--

INSERT INTO `rubrics` (`id`, `name`, `description`, `created_by`, `created_at`, `structure`) VALUES
(2, 'a', 'a', NULL, '2024-11-18 08:40:55', ''),
(9, 'test', 'desc', 37, '2024-12-04 19:02:49', '{\"levels\":[\"Level 1\",\"Level 2\",\"Level 3\",\"Level 4\"],\"criteria\":[{\"criterion\":\"cri\",\"levels\":[{\"content\":\"1\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"2\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"3\",\"rowSpan\":1,\"colSpan\":1},{\"content\":\"4\",\"rowSpan\":1,\"colSpan\":1}]},{\"criterion\":\"cri2\",\"levels\":[{\"content\":\"1 2\",\"rowSpan\":1,\"colSpan\":2},{\"content\":\"3 4\",\"rowSpan\":1,\"colSpan\":2}]}]}');

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
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `created_at`, `title`) VALUES
(1, 'Team1', '2024-10-13 06:58:29', 'Analysis of Machine Learning Algorithms in Predictive Maintenance'),
(2, 'Team2', '2024-10-13 06:58:29', 'Sustainable Urban Planning: A Case Study of Green Cities'),
(3, 'Team3', '2024-10-13 06:58:29', 'The Impact of Social Media on Mental Health in Adolescents'),
(4, 'Team4', '2024-10-13 06:58:29', 'Renewable Energy Integration in Smart Grids'),
(5, 'Team5', '2024-10-13 06:58:29', 'Cybersecurity Challenges in Internet of Things (IoT) Devices'),
(6, 'Team6', '2024-10-13 06:58:29', 'The Role of Artificial Intelligence in Healthcare Diagnostics'),
(7, 'Team7', '2024-10-13 06:58:29', 'Blockchain Technology in Supply Chain Management');

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
(5, 2, 60, 'adviser'),
(6, 2, 41, 'leader'),
(7, 2, 42, 'member'),
(8, 2, 43, 'member'),
(9, 3, 60, 'adviser'),
(10, 3, 44, 'leader'),
(11, 3, 45, 'member'),
(12, 3, 46, 'member'),
(13, 4, 61, 'adviser'),
(14, 4, 47, 'leader'),
(15, 4, 48, 'member'),
(16, 4, 49, 'member'),
(17, 5, 62, 'adviser'),
(18, 5, 50, 'leader'),
(19, 5, 51, 'member'),
(20, 5, 52, 'member'),
(21, 6, 63, 'adviser'),
(22, 6, 53, 'leader'),
(23, 6, 54, 'member'),
(24, 6, 55, 'member'),
(25, 7, 64, 'adviser'),
(26, 7, 56, 'leader'),
(27, 7, 57, 'member'),
(61, 1, 58, 'adviser'),
(62, 1, 38, 'leader'),
(63, 1, 39, 'member'),
(64, 1, 40, 'member');

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
(1, 2, 1, 'submitted', '2024-11-19 14:19:41', '', 'Team2-Chapter1-20241119.pdf', NULL);

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
(19, 'Theoretical Computer Science', 'Exploring the foundations of computation and algorithms. Development of novel computational approaches and algorithms.', 'Computer Science', '2024-11-18 09:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `usertype` int(1) NOT NULL DEFAULT 1,
  `username` varchar(255) NOT NULL,
  `program` varchar(10) DEFAULT NULL,
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

INSERT INTO `users` (`id`, `usertype`, `username`, `program`, `email`, `password`, `first_name`, `last_name`, `gender`, `headline`, `bio`, `profile_image`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`) VALUES
(1, 0, 'winstonadmin', NULL, 'ton.agustin09@gmail.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Winston', 'Agustin', 'm', 'SUPER ADMIN', '', '6703b15c765f80.83029727.png', '2024-10-05 13:55:38', '2024-10-05 13:55:38', '2024-11-19 13:59:24', '0000-00-00 00:00:00', '2024-11-19 13:59:24'),
(35, 0, 'supahot', NULL, 'supa@hot.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Supahot', 'Soverysupahot', 'm', 'Headline of a supa hot user', 'This is the bio of a supa hot user. Now i will say needless stuff to make this longer so this looks like a bio and not anything other than a bio.', '_defaultUser.png', NULL, '2024-10-08 05:25:07', '2024-11-11 14:39:12', NULL, NULL),
(37, 0, 'neilv', NULL, 'neilvicedo.ih@gmail.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Niall', 'V', 'o', 'Basta programmer ako', '?', '_defaultUser.png', '2024-10-08 13:14:14', '2024-10-08 13:13:14', '2024-12-04 18:43:58', NULL, '2024-12-04 18:43:58'),
(38, 1, 'student1', 'BSCS', 'student1@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'One', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-19 14:36:46', NULL, '2024-11-19 14:36:46'),
(39, 1, 'student2', 'BSCS', 'student2@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Two', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-19 11:44:04', NULL, '2024-11-19 11:44:04'),
(40, 1, 'student3', 'BSCS', 'student3@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Three', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-12 04:44:23'),
(41, 1, 'student4', 'BSCS', 'student4@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Four', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-19 14:47:12', NULL, '2024-11-19 14:47:12'),
(42, 1, 'student5', 'BSCS', 'student5@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Five', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-11-13 03:21:37'),
(43, 1, 'student6', 'BSCS', 'student6@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Six', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-11-13 01:18:56'),
(44, 1, 'student7', 'BSCS', 'student7@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Seven', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-11-13 01:24:45'),
(45, 1, 'student8', 'BSCS', 'student8@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Eight', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(46, 1, 'student9', 'BLIS', 'student9@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Nine', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(47, 1, 'student10', 'BSIT', 'student10@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Ten', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(48, 1, 'student11', 'BSIT', 'student11@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Eleven', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(49, 1, 'student12', 'BSIT', 'student12@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Twelve', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(50, 1, 'student13', 'BSIT', 'student13@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Thirteen', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(51, 1, 'student14', 'BSIT', 'student14@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Fourteen', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(52, 1, 'student15', 'BSIT', 'student15@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Fifteen', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(53, 1, 'student16', 'BSCS', 'student16@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Sixteen', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(54, 1, 'student17', 'BSCS', 'student17@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Seventeen', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(55, 1, 'student18', 'BSCS', 'student18@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Eighteen', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(56, 1, 'student19', 'BLIS', 'student19@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Nineteen', 'm', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(57, 1, 'student20', 'BLIS', 'student20@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Student', 'Twenty', 'f', 'Student Headline', 'This is a student bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-16 12:41:51', NULL, '2024-10-10 06:07:06'),
(58, 2, 'staff1', NULL, 'staff1@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'One', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-19 23:17:44', NULL, '2024-11-19 23:17:44'),
(59, 2, 'staff2', NULL, 'staff2@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Two', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-20 00:24:23', NULL, '2024-11-20 00:24:23'),
(60, 2, 'staff3', NULL, 'staff3@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Three', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-20 12:48:49', NULL, '2024-11-20 12:48:49'),
(61, 2, 'staff4', NULL, 'staff4@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Four', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-11 14:39:12', NULL, '2024-10-10 06:07:06'),
(62, 2, 'staff5', NULL, 'staff5@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Five', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-11 14:39:12', NULL, '2024-10-10 06:07:06'),
(63, 2, 'staff6', NULL, 'staff6@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Six', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-11 14:39:12', NULL, '2024-10-10 06:07:06'),
(64, 2, 'staff7', NULL, 'staff7@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Seven', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-11 14:39:12', NULL, '2024-10-10 06:07:06'),
(65, 2, 'staff8', NULL, 'staff8@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Eight', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-11 14:39:12', NULL, '2024-10-10 06:07:06'),
(66, 2, 'staff9', NULL, 'staff9@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Nine', 'm', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-11 14:39:12', NULL, '2024-10-10 06:07:06'),
(67, 2, 'staff10', NULL, 'staff10@example.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Staff', 'Ten', 'f', 'Staff Headline', 'This is a staff bio.', '_defaultUser.png', '2024-10-10 06:07:06', '2024-10-10 06:07:06', '2024-11-11 14:39:12', NULL, '2024-10-10 06:07:06'),
(70, 0, 'a', NULL, 'a@a.a', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'a', 'a', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2024-11-11 14:39:12', NULL, '2024-10-25 03:31:28'),
(71, 1, 'ilano', NULL, 'ilano@ilano.ilano', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', '', '', NULL, '', '', '_defaultUser.png', '2024-10-28 02:12:34', '2024-10-28 02:10:53', '2024-11-11 14:39:12', NULL, '2024-10-28 02:12:43');

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
-- Dumping data for table `user_schedules`
--

INSERT INTO `user_schedules` (`id`, `user_id`, `day_of_week`, `start_time`, `end_time`, `class_name`) VALUES
(6, 38, 'Tuesday', '13:00:00', '16:00:00', 'English'),
(7, 38, 'Wednesday', '08:00:00', '11:00:00', 'History'),
(8, 38, 'Monday', '10:00:00', '14:00:00', 'Literature'),
(9, 38, 'Friday', '15:00:00', '18:00:00', 'Math'),
(10, 38, 'Wednesday', '16:00:00', '20:00:00', 'Economics'),
(11, 39, 'Wednesday', '12:00:00', '15:00:00', 'English'),
(12, 39, 'Friday', '18:00:00', '23:00:00', 'Economics'),
(13, 39, 'Monday', '15:00:00', '19:00:00', 'History'),
(14, 40, 'Wednesday', '17:00:00', '21:00:00', 'English'),
(15, 40, 'Tuesday', '13:00:00', '18:00:00', 'Programming'),
(16, 40, 'Monday', '07:00:00', '11:00:00', 'History'),
(17, 41, 'Monday', '14:00:00', '17:00:00', 'English'),
(18, 41, 'Thursday', '08:00:00', '11:00:00', 'Chemistry'),
(19, 41, 'Wednesday', '14:00:00', '18:00:00', 'English'),
(20, 41, 'Tuesday', '11:00:00', '15:00:00', 'Literature'),
(21, 41, 'Friday', '11:00:00', '14:00:00', 'Physics'),
(22, 42, 'Friday', '17:00:00', '20:00:00', 'Chemistry'),
(23, 42, 'Monday', '14:00:00', '17:00:00', 'Physics'),
(24, 42, 'Thursday', '14:00:00', '19:00:00', 'Math'),
(25, 42, 'Tuesday', '13:00:00', '18:00:00', 'Programming'),
(26, 43, 'Wednesday', '19:00:00', '22:00:00', 'Programming'),
(27, 43, 'Monday', '07:00:00', '10:00:00', 'Literature'),
(28, 43, 'Thursday', '18:00:00', '22:00:00', 'Science'),
(29, 44, 'Friday', '10:00:00', '13:00:00', 'Programming'),
(30, 44, 'Saturday', '11:00:00', '14:00:00', 'Chemistry'),
(31, 44, 'Saturday', '15:00:00', '20:00:00', 'English'),
(32, 44, 'Thursday', '08:00:00', '12:00:00', 'Economics'),
(33, 44, 'Friday', '10:00:00', '14:00:00', 'Programming'),
(34, 45, 'Wednesday', '16:00:00', '20:00:00', 'English'),
(35, 45, 'Tuesday', '15:00:00', '18:00:00', 'Chemistry'),
(36, 45, 'Thursday', '19:00:00', '24:00:00', 'History'),
(37, 45, 'Monday', '16:00:00', '21:00:00', 'Literature'),
(38, 46, 'Thursday', '12:00:00', '15:00:00', 'Literature'),
(39, 46, 'Friday', '09:00:00', '14:00:00', 'Programming'),
(40, 46, 'Wednesday', '17:00:00', '22:00:00', 'Physics'),
(41, 46, 'Wednesday', '17:00:00', '20:00:00', 'History'),
(42, 47, 'Friday', '10:00:00', '13:00:00', 'Economics'),
(43, 47, 'Saturday', '12:00:00', '16:00:00', 'Literature'),
(44, 47, 'Wednesday', '11:00:00', '14:00:00', 'History'),
(45, 47, 'Saturday', '09:00:00', '12:00:00', 'English'),
(46, 47, 'Friday', '10:00:00', '14:00:00', 'Economics'),
(47, 48, 'Monday', '08:00:00', '11:00:00', 'Chemistry'),
(48, 48, 'Thursday', '07:00:00', '12:00:00', 'English'),
(49, 48, 'Saturday', '09:00:00', '12:00:00', 'Economics'),
(50, 49, 'Saturday', '07:00:00', '12:00:00', 'Economics'),
(51, 49, 'Monday', '09:00:00', '14:00:00', 'Economics'),
(52, 49, 'Friday', '12:00:00', '17:00:00', 'Chemistry'),
(53, 50, 'Thursday', '13:00:00', '16:00:00', 'Literature'),
(54, 50, 'Thursday', '10:00:00', '13:00:00', 'Chemistry'),
(55, 50, 'Wednesday', '10:00:00', '14:00:00', 'English'),
(56, 51, 'Thursday', '19:00:00', '23:00:00', 'English'),
(57, 51, 'Saturday', '19:00:00', '23:00:00', 'History'),
(58, 51, 'Saturday', '19:00:00', '22:00:00', 'Literature'),
(59, 51, 'Friday', '11:00:00', '15:00:00', 'Science'),
(60, 51, 'Friday', '07:00:00', '10:00:00', 'Science'),
(61, 52, 'Saturday', '13:00:00', '18:00:00', 'Literature'),
(62, 52, 'Thursday', '17:00:00', '22:00:00', 'Literature'),
(63, 52, 'Wednesday', '08:00:00', '12:00:00', 'English'),
(64, 52, 'Tuesday', '16:00:00', '19:00:00', 'Chemistry'),
(65, 52, 'Monday', '13:00:00', '18:00:00', 'Physics'),
(66, 53, 'Wednesday', '13:00:00', '18:00:00', 'Chemistry'),
(67, 53, 'Tuesday', '12:00:00', '16:00:00', 'Programming'),
(68, 53, 'Saturday', '13:00:00', '18:00:00', 'Programming'),
(69, 53, 'Monday', '08:00:00', '13:00:00', 'Literature'),
(70, 54, 'Saturday', '14:00:00', '19:00:00', 'Science'),
(71, 54, 'Friday', '19:00:00', '22:00:00', 'Economics'),
(72, 54, 'Wednesday', '15:00:00', '19:00:00', 'History'),
(73, 54, 'Thursday', '10:00:00', '14:00:00', 'Literature'),
(74, 54, 'Tuesday', '15:00:00', '18:00:00', 'Chemistry'),
(75, 55, 'Friday', '13:00:00', '17:00:00', 'Programming'),
(76, 55, 'Saturday', '16:00:00', '20:00:00', 'Economics'),
(77, 55, 'Friday', '16:00:00', '21:00:00', 'Literature'),
(78, 56, 'Friday', '19:00:00', '24:00:00', 'History'),
(79, 56, 'Saturday', '13:00:00', '18:00:00', 'History'),
(80, 56, 'Thursday', '13:00:00', '18:00:00', 'Physics'),
(81, 57, 'Saturday', '17:00:00', '20:00:00', 'Chemistry'),
(82, 57, 'Wednesday', '12:00:00', '16:00:00', 'Math'),
(83, 57, 'Saturday', '16:00:00', '21:00:00', 'Chemistry'),
(84, 57, 'Tuesday', '14:00:00', '18:00:00', 'History'),
(85, 57, 'Wednesday', '07:00:00', '12:00:00', 'Chemistry'),
(86, 58, 'Thursday', '13:00:00', '17:00:00', 'Programming'),
(87, 58, 'Monday', '10:00:00', '14:00:00', 'Literature'),
(88, 58, 'Monday', '19:00:00', '24:00:00', 'Math'),
(89, 58, 'Tuesday', '14:00:00', '18:00:00', 'Chemistry'),
(90, 58, 'Saturday', '11:00:00', '14:00:00', 'Economics'),
(91, 59, 'Friday', '19:00:00', '22:00:00', 'Economics'),
(92, 59, 'Friday', '17:00:00', '22:00:00', 'Physics'),
(93, 59, 'Thursday', '16:00:00', '21:00:00', 'Literature'),
(94, 59, 'Tuesday', '10:00:00', '13:00:00', 'Chemistry'),
(95, 60, 'Friday', '07:00:00', '10:00:00', 'Science'),
(96, 60, 'Tuesday', '16:00:00', '19:00:00', 'Science'),
(97, 60, 'Monday', '10:00:00', '14:00:00', 'Chemistry'),
(98, 60, 'Thursday', '19:00:00', '22:00:00', 'Programming'),
(99, 61, 'Wednesday', '09:00:00', '12:00:00', 'Science'),
(100, 61, 'Saturday', '08:00:00', '11:00:00', 'Chemistry'),
(101, 61, 'Monday', '08:00:00', '13:00:00', 'Economics'),
(102, 61, 'Monday', '16:00:00', '20:00:00', 'Programming'),
(103, 62, 'Monday', '09:00:00', '14:00:00', 'Math'),
(104, 62, 'Tuesday', '18:00:00', '22:00:00', 'Programming'),
(105, 62, 'Wednesday', '12:00:00', '15:00:00', 'Literature'),
(106, 62, 'Thursday', '08:00:00', '12:00:00', 'Programming'),
(107, 62, 'Tuesday', '12:00:00', '17:00:00', 'History'),
(108, 63, 'Saturday', '18:00:00', '22:00:00', 'Economics'),
(109, 63, 'Wednesday', '15:00:00', '20:00:00', 'English'),
(110, 63, 'Tuesday', '18:00:00', '22:00:00', 'Literature'),
(111, 63, 'Tuesday', '08:00:00', '11:00:00', 'Chemistry'),
(112, 64, 'Wednesday', '13:00:00', '17:00:00', 'History'),
(113, 64, 'Saturday', '16:00:00', '20:00:00', 'Programming'),
(114, 64, 'Wednesday', '13:00:00', '18:00:00', 'Literature'),
(115, 65, 'Wednesday', '12:00:00', '17:00:00', 'Literature'),
(116, 65, 'Tuesday', '13:00:00', '17:00:00', 'Literature'),
(117, 65, 'Saturday', '12:00:00', '16:00:00', 'Chemistry'),
(118, 65, 'Friday', '10:00:00', '13:00:00', 'Math'),
(119, 66, 'Saturday', '11:00:00', '14:00:00', 'Math'),
(120, 66, 'Monday', '16:00:00', '21:00:00', 'Economics'),
(121, 66, 'Thursday', '14:00:00', '17:00:00', 'Programming'),
(122, 67, 'Tuesday', '08:00:00', '11:00:00', 'Physics'),
(123, 67, 'Tuesday', '08:00:00', '11:00:00', 'Science'),
(124, 67, 'Tuesday', '18:00:00', '23:00:00', 'Science'),
(125, 67, 'Thursday', '16:00:00', '21:00:00', 'History');

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `defense_panelists`
--
ALTER TABLE `defense_panelists`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `env_variables`
--
ALTER TABLE `env_variables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `evaluation_details`
--
ALTER TABLE `evaluation_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requirements`
--
ALTER TABLE `requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `research_titles`
--
ALTER TABLE `research_titles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rubrics`
--
ALTER TABLE `rubrics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rubric_criteria`
--
ALTER TABLE `rubric_criteria`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `team_requirements`
--
ALTER TABLE `team_requirements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `thesis_topics`
--
ALTER TABLE `thesis_topics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

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
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_requirements`
--
ALTER TABLE `team_requirements`
  ADD CONSTRAINT `team_requirements_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_requirements_ibfk_2` FOREIGN KEY (`requirement_id`) REFERENCES `requirements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_schedules`
--
ALTER TABLE `user_schedules`
  ADD CONSTRAINT `user_schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
