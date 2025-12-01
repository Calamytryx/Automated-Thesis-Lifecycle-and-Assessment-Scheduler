-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql302.iceiy.com
-- Generation Time: Dec 01, 2025 at 12:03 PM
-- Server version: 11.4.7-MariaDB
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
CREATE DATABASE IF NOT EXISTS `icei_38697196_coecsathesis` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `icei_38697196_coecsathesis`;

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

DROP TABLE IF EXISTS `auth_tokens`;
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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `user_email`, `auth_type`, `selector`, `token`, `created_at`, `expires_at`) VALUES
(1, 'winstonagustin.ih@gmail.com', 'account_verify', '613f4c35ee6dac46', '$2y$10$fGDz8SdTBADhULRbmpcauORjPUc1tD.JsKCldb72Z.uQFaej5PdG.', '2025-07-22 05:07:15', '2025-07-22 21:07:15'),
(2, 'ton.agustin09@gmail.com', 'password_reset', '5cb4a55ab4006dc4', '$2y$10$o.wvCBt7WYWugpZNvwQaX.k130D9lRxeAcNrwaZPQYTYY9UjVf3gu', '2025-09-16 09:00:17', '2025-09-16 10:00:16'),
(3, 'kahit.ano@lpu.edu.ph', 'account_verify', '450073c3d1df9f3d', '$2y$10$FOKJO285TtKgRBS6gpclTuS4kUnb8Wo2wXMyT3t7PDx3JXKa4Bq6a', '2025-11-06 06:45:12', '2025-11-06 07:45:12'),
(4, 'jaira.mae@lpunetwork.edu.ph', 'account_verify', '88732ef365b4d1dd', '$2y$10$Ln2UYWgTscxjUCMCw8JX7Oytd4Q2L/XI678KEXHqrQW0wx3bi73V.', '2025-11-30 05:31:10', '2025-11-30 22:31:10'),
(5, 'leennel.ioan@lpunetwork.edu.ph', 'account_verify', '1e6def8352df576d', '$2y$10$8khCsuyFYjad0yEIy9U82ezXWsEdLGc7m3nhrOBVuIBHA4pQ9mwV2', '2025-11-30 05:35:56', '2025-11-30 22:35:56'),
(6, 'brent.harvey@lpunetwork.edu.ph', 'account_verify', 'e8ad23f1ca77a50a', '$2y$10$wSRFtMq1KJgrV30LQSklDu9CRDqLoH.p9L10GQw2WEx2FeV0XBohK', '2025-11-30 05:36:43', '2025-11-30 22:36:43'),
(7, 'sir.lawrence@lpunetwork.edu.ph', 'account_verify', '340b588afa25f249', '$2y$10$7ldQlyqoriu2NsQuuy94V.w.yNoNwbBHKpO2hcdF11ev7oauesADi', '2025-11-30 05:37:28', '2025-11-30 22:37:28'),
(8, 'Jeff.Nebran@lpu.edu.ph', 'account_verify', 'a2f609f292acdeaf', '$2y$10$c7URFeWitk00s3LTiRgU7O7pODaTnRyNvDxl8aFerglhIKWYmMJyG', '2025-11-30 05:46:07', '2025-11-30 22:46:07'),
(9, 'ian.lumanog@lpunetwork.edu.ph', 'account_verify', '7bc35550e463b71c', '$2y$10$MiFoHij5noqBZ3bS89/m2emQD.KunDnB5/CkO.TPbgXizwqKCptn6', '2025-11-30 05:50:39', '2025-11-30 22:50:39'),
(10, 'aaron.joshua@lpunetwork.edu.ph', 'account_verify', 'bb9f2afdf622369c', '$2y$10$gaIKO46QF8DI/KuB2ojOyulJY6UQmUwuCr/l4cmIuA.L3qqRG1DnG', '2025-11-30 05:51:16', '2025-11-30 22:51:16'),
(11, 'alexis.john@lpunetwork.edu.ph', 'account_verify', '82147cc1a26fe945', '$2y$10$Z6p8JPPGWpqZwaZB9UGfR.cXcVzBFqSVQCXvDVmFdYAd40ZWwheJm', '2025-11-30 05:51:48', '2025-11-30 22:51:48'),
(12, 'beo.alvaro@lpunetwork.edu.ph', 'account_verify', '745657a37087b1a3', '$2y$10$ctvCYfU75LOBNQpMyqkwFO4C78zmktsd3aTNUfwibTLduuUdl4.GG', '2025-11-30 05:52:23', '2025-11-30 22:52:23'),
(13, 'Roger.Wyne@lpu.edu.ph', 'account_verify', '59dfcf4d3f1ed51a', '$2y$10$B5RQgN7R7xgjrrLNNOET9OPkHBcEdPOAsAxVf0ajGWwPe934J.aji', '2025-11-30 05:53:23', '2025-11-30 22:53:23'),
(15, 'king.edward@lpunetwork.edu.ph', 'account_verify', '1ace183b291209ce', '$2y$10$2oyY8OyWxGPJddaXSDhMqO0ZHbxDYt6AY1OUlU8e2E1GojI00aJYa', '2025-11-30 06:01:17', '2025-11-30 23:01:17'),
(16, 'micah.sereno@lpunetwork.edu.ph', 'account_verify', 'ba43707acfb2f76c', '$2y$10$o4LslW66jT4fbWPFTBUgouk1ZuIKO7tfiKIPJamccfJQUG/J1soKq', '2025-11-30 06:02:06', '2025-11-30 23:02:06'),
(17, 'mielle.angelie@lpunetwork.edu.ph', 'account_verify', '0f92fd00ff6438ce', '$2y$10$b0btPFqJJhpqx17bs/ZbH.hW6kVYkxeMRKZt6t3MdmZDntc998gEi', '2025-11-30 06:02:40', '2025-11-30 23:02:40'),
(18, 'barbuco.barbuco@lpunetwork.edu.ph', 'account_verify', '3bc6b51fce6492fc', '$2y$10$XGKa6cipK7imXcqcH/yd0OLVL35/hrm5ggWDFSFghvPfH5BiGsS3e', '2025-11-30 06:03:29', '2025-11-30 23:03:29'),
(19, 'alyssa.mae@lpunetwork.edu.ph', 'account_verify', 'b20bbd78efa5fc9c', '$2y$10$cUeKmXceds4V/p45d3oswOmZp8Tudn3WAHbtTj8GVhKKEEOfiusWC', '2025-11-30 06:08:02', '2025-11-30 23:08:02'),
(20, 'alexander.asinas@lpunetwork.edu.ph', 'account_verify', '829e05b0b72178eb', '$2y$10$HJjdkWJ10M.795YFmybThOjKCynBPHxhrgZ7xgAWMXn9Me8KSJ0W6', '2025-11-30 06:08:50', '2025-11-30 23:08:50'),
(21, 'angelo.mark@lpunetwork.edu.ph', 'account_verify', '4b6cda4061edfca5', '$2y$10$yL.oZoFH4BZhSvaZJPVLkuXmIr4pHhfVpgIymzaCvg7cECLVBkEMG', '2025-11-30 06:10:03', '2025-11-30 23:10:03'),
(22, 'brandon.miranda@lpunetwork.edu.ph', 'account_verify', '91297091969f5034', '$2y$10$ewapdsnCyVdEFT2Mxo/7uuT5BI81gm8.2Ioogp.v4Aj5/pxdCSimG', '2025-11-30 06:10:41', '2025-11-30 23:10:41'),
(23, 'Earl.Saavedra@lpu.edu.ph', 'account_verify', '19d6163826c0d5d2', '$2y$10$fKhH/v/wNIl8oxDr8tUVk.8Za5W4ru8hxJmAF2v2Xk9j.Ez6GGGGS', '2025-11-30 06:11:26', '2025-11-30 23:11:26'),
(24, 'cj.vhert@lpunetwork.edu.ph', 'account_verify', '82d56b40eb8c8b9d', '$2y$10$B.mE27fTeptVBVehN7599upeIktwVJBpsmqV.eezQ7ggLt4pZ7PPK', '2025-11-30 06:15:33', '2025-11-30 23:15:33'),
(25, 'john.lloyd@lpunetwork.edu.ph', 'account_verify', '961d569a9c03c404', '$2y$10$yKtrZb/1SNSYkRpPeTt.gO9Xp4BDQqg7eqvdFTJHpqcfWmHhxVz6O', '2025-11-30 06:16:17', '2025-11-30 23:16:16'),
(26, 'patricia.nicole@lpunetwork.edu.ph', 'account_verify', '7b3581729346d403', '$2y$10$YL6BXsdEq2JMXvzO7NUnTeFD0sTAB8yj277PHdX28gAqLqSgGl1Ui', '2025-11-30 06:16:44', '2025-11-30 23:16:44'),
(27, 'leila.aliyah@lpunetwork.edu.ph', 'account_verify', '4387d0ad0ab68325', '$2y$10$zo.Qgjwk/222uYV/jHdP5eviU2cvFncgoeLDnCDtS/NQvBT8lR692', '2025-11-30 06:17:21', '2025-11-30 23:17:21'),
(28, 'john.vincent@lpunetwork.edu.ph', 'account_verify', '031ca4c883986ce7', '$2y$10$f9BA833uFHZLhd91yoW/ouFfbRFqQuzsvFbUgmsQk/iu9I/lIqpA.', '2025-11-30 06:21:21', '2025-11-30 23:21:21'),
(29, 'warren.jacob@lpunetwork.edu.ph', 'account_verify', 'a9b63307caae53ef', '$2y$10$2j3AGhrnEt0q5LBSlZZay.YsguzjPxIJi/sgU8Z4FMjm8pGyZLAte', '2025-11-30 06:21:57', '2025-11-30 23:21:57'),
(30, 'jc.villaganas@lpunetwork.edu.ph', 'account_verify', '06cfa85dff7200c4', '$2y$10$2xIaVAwK9kZQ2KMPs9IfmOSpa7W9Yn8TfU68FrehWSAZdfi0ACwA2', '2025-11-30 06:22:31', '2025-11-30 23:22:30'),
(31, 'nicole.wyne@lpunetwork.edu.ph', 'account_verify', 'b7886ed7925c92b5', '$2y$10$lb5xb7bTSRHhubfw8HZf.u0uR49DxcFPD.7xRLjskpwaPvBt2wJni', '2025-11-30 06:23:01', '2025-11-30 23:23:01'),
(32, 'jaermaine.lester@lpunetwork.edu.ph', 'account_verify', '3d4878492217666e', '$2y$10$41L92jXv9V6cQG82JxP6r.qV65rsE626GPWgG6BS/LdiRAeq/v/Um', '2025-12-01 15:19:54', '2025-12-02 08:19:54'),
(33, 'lorenzo.canales@lpunetwork.edu.ph', 'account_verify', '6a1fcc27f852ed56', '$2y$10$MUaDULgcB3WVUivo.sQUseFK8AVd5.ywJwL69ce5eTaGdC0JRQVA.', '2025-12-01 15:20:33', '2025-12-02 08:20:33'),
(34, 'franco.luis@lpunetwork.edu.ph', 'account_verify', '90ed4bb8ba24397b', '$2y$10$QQJVlmROQD7YnFE8zAa4lejMTvTmm2NZdpuzIhdajK/iQxBv1ZLXu', '2025-12-01 15:21:01', '2025-12-02 08:21:01'),
(35, 'resty.jean@lpunetwork.edu.ph', 'account_verify', 'cb4599022612c966', '$2y$10$70TMTcNV/KQKxtLmODccaOrlo0jjKnl0CqGRXtV8MQeKYX4N6alSa', '2025-12-01 15:21:36', '2025-12-02 08:21:36');

-- --------------------------------------------------------

--
-- Table structure for table `default_schedules`
--

DROP TABLE IF EXISTS `default_schedules`;
CREATE TABLE IF NOT EXISTS `default_schedules` (
  `id` int(11) NOT NULL,
  `program` varchar(255) NOT NULL,
  `year` enum('1','2','3','4','5') NOT NULL,
  `section` int(2) NOT NULL,
  `building` varchar(45) NOT NULL,
  `room` varchar(45) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `class_name` varchar(45) NOT NULL,
  `start_time` varchar(45) NOT NULL,
  `end_time` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_panelists`
--

DROP TABLE IF EXISTS `defense_panelists`;
CREATE TABLE IF NOT EXISTS `defense_panelists` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `defense_id` int(11) UNSIGNED NOT NULL,
  `panelist_id` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `defense_id` (`defense_id`),
  KEY `panelist_id` (`panelist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `defense_schedules`
--

DROP TABLE IF EXISTS `defense_schedules`;
CREATE TABLE IF NOT EXISTS `defense_schedules` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` int(11) UNSIGNED DEFAULT NULL,
  `panelist_id` int(11) UNSIGNED DEFAULT NULL,
  `panelist_id2` int(11) UNSIGNED DEFAULT NULL,
  `panelist_id3` int(11) UNSIGNED DEFAULT NULL,
  `schedule_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re-defense') DEFAULT 'title_proposal',
  `related_requirement_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array of team_requirement_files IDs for multi-submission requirements',
  `admin_override_defense_type` tinyint(1) DEFAULT 0 COMMENT 'Whether defense type was manually overridden by admin',
`status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
`approval_status` enum('approved','rejected','pending') DEFAULT 'pending',
  PRIMARY KEY (`id`)
);


--
-- Dumping data for table `defense_schedules`
--

INSERT INTO `defense_schedules` (`id`, `team_id`, `panelist_id`, `panelist_id2`, `panelist_id3`, `schedule_date`, `start_time`, `end_time`, `room`, `defense_type`, `related_requirement_files`, `admin_override_defense_type`, `status`, `created_at`, `approval_status`) VALUES
(7, 1, 270, 271, 272, '2025-08-24', '09:30:00', '10:00:00', 'Defense Room 1', NULL, NULL, 0, 'scheduled', '2025-08-23 16:03:35', 'pending'),
(18, 6, 285, 272, 286, '2025-11-27', '09:00:00', '10:00:00', 'defense room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-11-24 13:04:05', 'pending'),
(23, 4, 277, 272, 269, '2025-11-27', '17:00:00', '18:00:00', '11', 'title_proposal', NULL, 0, 'scheduled', '2025-11-25 13:59:27', 'pending'),
(24, 5, 270, 281, 278, '2025-11-27', '12:00:00', '13:00:00', '11', 'title_proposal', NULL, 0, 'scheduled', '2025-11-25 13:59:27', 'pending'),
(25, 7, 278, 279, 285, '2025-11-27', '19:00:00', '20:00:00', '11', 'title_proposal', NULL, 0, 'scheduled', '2025-11-25 13:59:27', 'pending'),
(34, 22, 281, 271, 272, '2025-11-28', '10:00:00', '11:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-11-26 10:35:48', 'pending'),
(35, 23, 281, 270, 272, '2025-11-28', '09:00:00', '10:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-11-26 10:35:48', 'pending'),
(36, 27, 286, 270, 278, '2025-11-28', '14:00:00', '15:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-11-26 10:35:48', 'pending'),
(37, 24, 271, 270, 286, '2025-11-28', '20:00:00', '21:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-11-26 10:35:48', 'pending'),
(38, 21, 281, 272, 269, '2025-11-28', '18:00:00', '19:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-11-26 10:35:49', 'pending'),
(39, 25, 269, 278, 277, '2025-11-28', '09:00:00', '10:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-11-26 10:35:49', 'pending'),
(40, 26, 278, 280, 283, '2025-11-28', '08:00:00', '09:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-11-26 10:35:49', 'pending'),
(41, 28, 279, 270, 271, '2025-11-28', '20:00:00', '21:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-11-26 10:35:49', 'pending'),
(43, 52, 422, 427, 436, '2025-12-05', '16:00:00', '18:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-12-02 07:14:19', 'pending'),
(44, 58, 427, 430, 441, '2025-12-06', '08:00:00', '10:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-12-02 07:15:50', 'pending'),
(45, 59, 422, 429, 441, '2025-12-06', '10:00:00', '12:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-12-02 07:24:07', 'pending'),
(46, 50, 431, 418, 441, '2025-12-06', '12:00:00', '14:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-12-02 07:25:09', 'pending'),
(47, 51, 422, 430, 441, '2025-12-06', '14:00:00', '16:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-12-02 07:26:22', 'pending'),
(48, 53, 427, 430, 441, '2025-12-06', '16:00:00', '18:00:00', 'Defense Room 1', 'title_proposal', NULL, 0, 'scheduled', '2025-12-02 07:26:54', 'pending'),
(49, 54, 431, 418, 451, '2025-12-06', '08:00:00', '10:00:00', 'Defense Room 2', 'title_proposal', NULL, 0, 'scheduled', '2025-12-02 07:32:12', 'pending'),
(50, 55, 431, 428, 451, '2025-12-06', '10:00:00', '12:00:00', 'Defense Room 2', 'title_proposal', NULL, 0, 'scheduled', '2025-12-02 07:32:47', 'pending'),
(51, 49, 431, 429, 451, '2025-12-06', '14:00:00', '16:00:00', 'Defense Room 2', 'title_proposal', NULL, 0, 'scheduled', '2025-12-02 07:33:27', 'pending'),
(52, 56, 422, 418, 451, '2025-12-06', '16:00:00', '18:00:00', 'Defense Room 2', 'title_proposal', NULL, 0, 'scheduled', '2025-12-02 07:33:55', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `defense_type_overrides`
--

DROP TABLE IF EXISTS `defense_type_overrides`;
CREATE TABLE IF NOT EXISTS `defense_type_overrides` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` int(11) UNSIGNED NOT NULL,
  `override_type` enum('title_proposal','title_defense','final_defense','re-defense') NOT NULL,
  `reason` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1 COMMENT 'Whether this override is currently active',
  `created_by` int(11) UNSIGNED NOT NULL COMMENT 'Admin user ID who created this override',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Optional expiry date for temporary overrides',
  PRIMARY KEY (`id`),
  KEY `idx_team_active` (`team_id`,`active`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Admin overrides for defense type mapping';

--
-- Dumping data for table `defense_type_overrides`
--

INSERT INTO `defense_type_overrides` (`id`, `team_id`, `override_type`, `reason`, `active`, `created_by`, `created_at`, `updated_at`, `expires_at`) VALUES
(1, 4, 'final_defense', '', 0, 0, '2025-11-21 16:34:41', '2025-11-21 16:35:04', NULL),
(2, 50, 'title_defense', '', 1, 0, '2025-11-29 08:58:22', '2025-11-29 08:58:22', NULL),
(3, 51, 'title_defense', '', 1, 0, '2025-11-29 08:58:33', '2025-11-29 08:58:33', NULL),
(4, 49, 'title_defense', '', 1, 0, '2025-11-29 08:58:44', '2025-11-29 08:58:44', NULL),
(5, 58, 'title_defense', '', 0, 0, '2025-12-01 03:23:39', '2025-12-01 05:53:28', NULL),
(6, 58, 'final_defense', '', 0, 0, '2025-12-01 05:50:33', '2025-12-01 05:53:28', NULL),
(7, 58, 'final_defense', '', 0, 0, '2025-12-01 05:51:19', '2025-12-01 05:53:28', NULL),
(8, 58, 'final_defense', '', 1, 0, '2025-12-01 05:53:36', '2025-12-01 05:53:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `env_variables`
--

DROP TABLE IF EXISTS `env_variables`;
CREATE TABLE IF NOT EXISTS `env_variables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE IF NOT EXISTS `evaluations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `defense_schedule_id` int(11) UNSIGNED DEFAULT NULL,
  `evaluator_id` int(11) UNSIGNED DEFAULT NULL,
  `total_score` float DEFAULT NULL,
  `pass_fail_status` enum('pass','fail') DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `recommendation` enum('pass','fail','revise minor','revise major') DEFAULT NULL,
  `yes_no` enum('yes','no') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `defense_schedule_id` (`defense_schedule_id`),
  KEY `evaluator_id` (`evaluator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_details`
--

DROP TABLE IF EXISTS `evaluation_details`;
CREATE TABLE IF NOT EXISTS `evaluation_details` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(11) UNSIGNED DEFAULT NULL,
  `rubric_id` int(11) UNSIGNED DEFAULT NULL,
  `criterion_id` int(11) UNSIGNED DEFAULT NULL,
  `student_id` int(11) UNSIGNED DEFAULT NULL,
  `score` float DEFAULT NULL,
  `selected_option` varchar(50) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `evaluation_id` (`evaluation_id`),
  KEY `criterion_id` (`criterion_id`),
  KEY `fk_evaluation_details_rubric` (`rubric_id`),
  KEY `fk_evaluation_details_student` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(145, 9, 6, NULL, NULL, NULL, '0', NULL, '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(146, 11, 8, 69, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(147, 11, 8, 70, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(148, 11, 8, 71, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(149, 11, 8, 72, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(150, 11, 8, 73, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(151, 11, 8, 74, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(152, 11, 9, 75, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(153, 11, 9, 76, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(154, 11, 9, 77, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(155, 11, 9, 78, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(156, 11, 9, 79, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(157, 11, 9, 80, NULL, 5, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(158, 11, 10, 81, 437, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(159, 12, 10, 81, 438, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(160, 13, 10, 81, 439, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(161, 14, 10, 81, 440, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(162, 11, 10, 82, 437, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(163, 12, 10, 82, 438, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(164, 13, 10, 82, 439, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(165, 14, 10, 82, 440, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(166, 11, 10, 83, 437, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(167, 12, 10, 83, 438, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(168, 13, 10, 83, 439, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(169, 14, 10, 83, 440, 1, NULL, NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(170, 11, 11, NULL, NULL, NULL, '1', NULL, '2025-11-30 11:41:45', '2025-11-30 11:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_per_panel`
--

DROP TABLE IF EXISTS `evaluation_per_panel`;
CREATE TABLE IF NOT EXISTS `evaluation_per_panel` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `defense_schedule_id` int(11) UNSIGNED NOT NULL,
  `evaluator_id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `group_score` float DEFAULT NULL,
  `solo_score` float DEFAULT NULL,
  `total_score` float DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `defense_schedule_id` (`defense_schedule_id`),
  KEY `evaluator_id` (`evaluator_id`),
  KEY `evalusations_per_panel_ibfk_3_idx` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluation_per_panel`
--

INSERT INTO `evaluation_per_panel` (`id`, `defense_schedule_id`, `evaluator_id`, `student_id`, `group_score`, `solo_score`, `total_score`, `comments`, `created_at`, `updated_at`) VALUES
(7, 6, 270, 267, 65, 2, 67, '123asd', '2025-08-23 06:08:46', '2025-08-23 06:08:46'),
(8, 6, 270, 268, 65, 2, 67, '123asd', '2025-08-23 06:08:46', '2025-08-23 06:08:46'),
(9, 7, 272, 267, 5, 0, 5, '', '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(10, 7, 272, 268, 5, 0, 5, '', '2025-08-23 16:37:39', '2025-08-23 16:37:39'),
(11, 42, 286, 437, 30, 4, 34, '', '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(12, 42, 286, 438, 30, 4, 34, '', '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(13, 42, 286, 439, 30, 4, 34, '', '2025-11-30 11:41:45', '2025-11-30 11:41:45'),
(14, 42, 286, 440, 30, 4, 34, '', '2025-11-30 11:41:45', '2025-11-30 11:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `form_assignments`
--

DROP TABLE IF EXISTS `form_assignments`;
CREATE TABLE IF NOT EXISTS `form_assignments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `defense_schedule_id` int(11) UNSIGNED NOT NULL,
  `embed_link` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `defense_schedule_id` (`defense_schedule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merged_evaluations`
--

DROP TABLE IF EXISTS `merged_evaluations`;
CREATE TABLE IF NOT EXISTS `merged_evaluations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `defense_schedule_id` (`defense_schedule_id`),
  KEY `evaluator_id` (`evaluator_id`),
  KEY `student_id` (`student_id`),
  KEY `rubric_id` (`rubric_id`),
  KEY `criterion_id` (`criterion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `type` enum('defense_scheduled','title_approved','requirement_created','requirement_submitted','requirement_approved','requirement_rejected','defense_approval') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID of related entity (team_id, requirement_id, etc.)',
  `related_type` enum('team','requirement','defense_schedule','research_title') DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`),
  KEY `idx_notifications_type` (`type`),
  KEY `idx_notifications_related` (`related_id`,`related_type`)
) ENGINE=InnoDB AUTO_INCREMENT=392 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(67, 280, 'requirement_submitted', 'New Requirement Submission', 'Team \'Team Innovate\' has submitted the requirement \'Final Manuscript\'. File: 4_5_1763753811_1_3_1762412995_5-Drugs-for-Asthma.pdf', 4, NULL, 0, '2025-11-21 19:36:51', '2025-11-21 19:36:51'),
(68, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 3:00 PM - 4:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 11, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(69, 282, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 3:00 PM - 4:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 11, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(70, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 3:00 PM - 4:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 11, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(71, 280, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in defense room 1. Waiting for panelist approval.', 11, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(72, 287, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in defense room 1. Waiting for panelist approval.', 11, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(73, 288, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in defense room 1. Waiting for panelist approval.', 11, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(74, 289, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in defense room 1. Waiting for panelist approval.', 11, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(75, 290, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in defense room 1. Waiting for panelist approval.', 11, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(76, 286, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 7:00 AM - 8:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 12, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(77, 278, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 7:00 AM - 8:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 12, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(78, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 7:00 AM - 8:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 12, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(79, 291, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 7:00 AM - 8:00 AM in defense room 1. Waiting for panelist approval.', 12, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(80, 292, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 7:00 AM - 8:00 AM in defense room 1. Waiting for panelist approval.', 12, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(81, 293, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 7:00 AM - 8:00 AM in defense room 1. Waiting for panelist approval.', 12, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(82, 294, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 7:00 AM - 8:00 AM in defense room 1. Waiting for panelist approval.', 12, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(83, 283, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 7:00 AM - 8:00 AM in defense room 1. Waiting for panelist approval.', 12, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(84, 285, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Quantum\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 6:00 PM - 7:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: AI Optimization Project\n\nPlease approve or decline this assignment.', 13, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(85, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Quantum\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 6:00 PM - 7:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: AI Optimization Project\n\nPlease approve or decline this assignment.', 13, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(86, 283, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Quantum\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 6:00 PM - 7:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: AI Optimization Project\n\nPlease approve or decline this assignment.', 13, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(87, 295, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in defense room 1. Waiting for panelist approval.', 13, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(88, 296, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in defense room 1. Waiting for panelist approval.', 13, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(89, 297, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in defense room 1. Waiting for panelist approval.', 13, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(90, 298, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in defense room 1. Waiting for panelist approval.', 13, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(91, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in defense room 1. Waiting for panelist approval.', 13, NULL, 0, '2025-11-24 13:03:44', '2025-11-24 13:03:44'),
(92, 277, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Nexus\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 12:00 PM - 1:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: ML Prediction Model\n\nPlease approve or decline this assignment.', 15, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(93, 282, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Nexus\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 12:00 PM - 1:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: ML Prediction Model\n\nPlease approve or decline this assignment.', 15, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(94, 281, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Nexus\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 12:00 PM - 1:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: ML Prediction Model\n\nPlease approve or decline this assignment.', 15, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(95, 299, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in defense room 1. Waiting for panelist approval.', 15, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(96, 300, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in defense room 1. Waiting for panelist approval.', 15, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(97, 301, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in defense room 1. Waiting for panelist approval.', 15, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(98, 302, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in defense room 1. Waiting for panelist approval.', 15, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(99, 280, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in defense room 1. Waiting for panelist approval.', 15, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(100, 278, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 8:00 AM - 9:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 16, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(101, 280, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 8:00 AM - 9:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 16, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(102, 281, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 8:00 AM - 9:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 16, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(103, 291, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 8:00 AM - 9:00 AM in defense room 1. Waiting for panelist approval.', 16, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(104, 292, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 8:00 AM - 9:00 AM in defense room 1. Waiting for panelist approval.', 16, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(105, 293, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 8:00 AM - 9:00 AM in defense room 1. Waiting for panelist approval.', 16, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(106, 294, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 8:00 AM - 9:00 AM in defense room 1. Waiting for panelist approval.', 16, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(107, 283, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 8:00 AM - 9:00 AM in defense room 1. Waiting for panelist approval.', 16, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(108, 285, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 17, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(109, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 17, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(110, 283, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 17, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(111, 280, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in defense room 1. Waiting for panelist approval.', 17, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(112, 287, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in defense room 1. Waiting for panelist approval.', 17, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(113, 288, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in defense room 1. Waiting for panelist approval.', 17, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(114, 289, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in defense room 1. Waiting for panelist approval.', 17, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(115, 290, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in defense room 1. Waiting for panelist approval.', 17, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(116, 285, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Quantum\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 9:00 AM - 10:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: AI Optimization Project\n\nPlease approve or decline this assignment.', 18, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(117, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Quantum\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 9:00 AM - 10:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: AI Optimization Project\n\nPlease approve or decline this assignment.', 18, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(118, 286, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Quantum\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 9:00 AM - 10:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: AI Optimization Project\n\nPlease approve or decline this assignment.', 18, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(119, 295, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 9:00 AM - 10:00 AM in defense room 1. Waiting for panelist approval.', 18, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(120, 296, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 9:00 AM - 10:00 AM in defense room 1. Waiting for panelist approval.', 18, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(121, 297, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 9:00 AM - 10:00 AM in defense room 1. Waiting for panelist approval.', 18, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(122, 298, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 9:00 AM - 10:00 AM in defense room 1. Waiting for panelist approval.', 18, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(123, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 9:00 AM - 10:00 AM in defense room 1. Waiting for panelist approval.', 18, NULL, 0, '2025-11-24 13:04:05', '2025-11-24 13:04:05'),
(124, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 7:00 AM - 8:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 19, NULL, 0, '2025-11-25 06:38:12', '2025-11-25 06:38:12'),
(125, 269, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 7:00 AM - 8:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 19, NULL, 0, '2025-11-25 06:38:12', '2025-11-25 06:38:12'),
(126, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 7:00 AM - 8:00 AM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 19, NULL, 0, '2025-11-25 06:38:12', '2025-11-25 06:38:12'),
(127, 280, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 7:00 AM - 8:00 AM in defense room 1. Waiting for panelist approval.', 19, NULL, 0, '2025-11-25 06:38:12', '2025-11-25 06:38:12'),
(128, 287, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 7:00 AM - 8:00 AM in defense room 1. Waiting for panelist approval.', 19, NULL, 0, '2025-11-25 06:38:12', '2025-11-25 06:38:12'),
(129, 288, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 7:00 AM - 8:00 AM in defense room 1. Waiting for panelist approval.', 19, NULL, 0, '2025-11-25 06:38:12', '2025-11-25 06:38:12'),
(130, 289, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 7:00 AM - 8:00 AM in defense room 1. Waiting for panelist approval.', 19, NULL, 0, '2025-11-25 06:38:12', '2025-11-25 06:38:12'),
(131, 290, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 7:00 AM - 8:00 AM in defense room 1. Waiting for panelist approval.', 19, NULL, 0, '2025-11-25 06:38:12', '2025-11-25 06:38:12'),
(132, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 5:00 PM - 6:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 21, NULL, 0, '2025-11-25 06:39:44', '2025-11-25 06:39:44'),
(133, 269, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 5:00 PM - 6:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 21, NULL, 0, '2025-11-25 06:39:44', '2025-11-25 06:39:44'),
(134, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 5:00 PM - 6:00 PM\n🏢 Room: defense room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 21, NULL, 0, '2025-11-25 06:39:44', '2025-11-25 06:39:44'),
(135, 280, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 5:00 PM - 6:00 PM in defense room 1. Waiting for panelist approval.', 21, NULL, 0, '2025-11-25 06:39:44', '2025-11-25 06:39:44'),
(136, 287, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 5:00 PM - 6:00 PM in defense room 1. Waiting for panelist approval.', 21, NULL, 0, '2025-11-25 06:39:44', '2025-11-25 06:39:44'),
(137, 288, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 5:00 PM - 6:00 PM in defense room 1. Waiting for panelist approval.', 21, NULL, 0, '2025-11-25 06:39:44', '2025-11-25 06:39:44'),
(138, 289, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 5:00 PM - 6:00 PM in defense room 1. Waiting for panelist approval.', 21, NULL, 0, '2025-11-25 06:39:44', '2025-11-25 06:39:44'),
(139, 290, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 5:00 PM - 6:00 PM in defense room 1. Waiting for panelist approval.', 21, NULL, 0, '2025-11-25 06:39:44', '2025-11-25 06:39:44'),
(140, 277, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 5:00 PM - 6:00 PM\n🏢 Room: 11\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 23, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(141, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 5:00 PM - 6:00 PM\n🏢 Room: 11\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 23, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(142, 269, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Innovate\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 5:00 PM - 6:00 PM\n🏢 Room: 11\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Next Gen Web App\n\nPlease approve or decline this assignment.', 23, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(143, 280, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 5:00 PM - 6:00 PM in 11. Waiting for panelist approval.', 23, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(144, 287, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 5:00 PM - 6:00 PM in 11. Waiting for panelist approval.', 23, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(145, 288, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 5:00 PM - 6:00 PM in 11. Waiting for panelist approval.', 23, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(146, 289, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 5:00 PM - 6:00 PM in 11. Waiting for panelist approval.', 23, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(147, 290, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 5:00 PM - 6:00 PM in 11. Waiting for panelist approval.', 23, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(148, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 12:00 PM - 1:00 PM\n🏢 Room: 11\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 24, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(149, 281, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 12:00 PM - 1:00 PM\n🏢 Room: 11\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 24, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(150, 278, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Team Horizon\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 12:00 PM - 1:00 PM\n🏢 Room: 11\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: Secure Network Project\n\nPlease approve or decline this assignment.', 24, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(151, 291, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in 11. Waiting for panelist approval.', 24, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(152, 292, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in 11. Waiting for panelist approval.', 24, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(153, 293, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in 11. Waiting for panelist approval.', 24, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(154, 294, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in 11. Waiting for panelist approval.', 24, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(155, 283, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in 11. Waiting for panelist approval.', 24, NULL, 0, '2025-11-25 13:59:27', '2025-11-25 13:59:27'),
(156, 277, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 3 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web and psychology\n\nPlease approve or decline this assignment.', 26, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(157, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 3 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web and psychology\n\nPlease approve or decline this assignment.', 26, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(158, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 3 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web and psychology\n\nPlease approve or decline this assignment.', 26, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(159, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 26, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(160, 325, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 26, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(161, 311, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 26, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(162, 320, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 26, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(163, 304, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 26, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(164, 295, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 26, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(165, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 2 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web apps\n\nPlease approve or decline this assignment.', 27, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(166, 269, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 2 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web apps\n\nPlease approve or decline this assignment.', 27, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(167, 282, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 2 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web apps\n\nPlease approve or decline this assignment.', 27, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(168, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 27, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(169, 323, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 27, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(170, 330, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 27, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(171, 291, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 27, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(172, 317, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 27, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(173, 331, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 27, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(174, 277, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 8 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about social media and psychology\n\nPlease approve or decline this assignment.', 28, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(175, 278, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 8 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about social media and psychology\n\nPlease approve or decline this assignment.', 28, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(176, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 8 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about social media and psychology\n\nPlease approve or decline this assignment.', 28, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(177, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 28, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(178, 316, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 28, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(179, 324, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 28, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(180, 318, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 28, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(181, 310, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 28, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(182, 322, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 28, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(183, 269, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 4 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about conducting research\n\nPlease approve or decline this assignment.', 29, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(184, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 4 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about conducting research\n\nPlease approve or decline this assignment.', 29, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(185, 278, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 4 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about conducting research\n\nPlease approve or decline this assignment.', 29, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(186, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 29, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(187, 288, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 29, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(188, 312, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 29, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(189, 308, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 29, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(190, 315, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 29, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(191, 313, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 29, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(192, 269, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 1 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 6:00 PM - 7:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about mobile apps\n\nPlease approve or decline this assignment.', 30, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(193, 281, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 1 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 6:00 PM - 7:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about mobile apps\n\nPlease approve or decline this assignment.', 30, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(194, 286, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 1 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 6:00 PM - 7:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about mobile apps\n\nPlease approve or decline this assignment.', 30, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(195, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 30, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(196, 292, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 30, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(197, 287, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 30, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(198, 300, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 30, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(199, 306, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 30, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(200, 333, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 30, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(201, 286, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 6 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 12:00 PM - 1:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about plant growth using tech\n\nPlease approve or decline this assignment.', 31, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(202, 281, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 6 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 12:00 PM - 1:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about plant growth using tech\n\nPlease approve or decline this assignment.', 31, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(203, 283, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 6 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 12:00 PM - 1:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about plant growth using tech\n\nPlease approve or decline this assignment.', 31, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(204, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in Defense Room 1. Waiting for panelist approval.', 31, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(205, 309, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in Defense Room 1. Waiting for panelist approval.', 31, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(206, 321, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in Defense Room 1. Waiting for panelist approval.', 31, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(207, 329, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in Defense Room 1. Waiting for panelist approval.', 31, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(208, 314, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in Defense Room 1. Waiting for panelist approval.', 31, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(209, 332, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 12:00 PM - 1:00 PM in Defense Room 1. Waiting for panelist approval.', 31, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(210, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 5 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about point of sales\n\nPlease approve or decline this assignment.', 32, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(211, 283, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 5 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about point of sales\n\nPlease approve or decline this assignment.', 32, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(212, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 5 (IT401)\'s defense:\n\n📅 Date: November 27, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about point of sales\n\nPlease approve or decline this assignment.', 32, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(213, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 32, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(214, 267, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 32, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(215, 268, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 32, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(216, 296, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 32, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(217, 299, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 32, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(218, 303, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 32, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(219, 285, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in Defense Room 1.', 33, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(220, 327, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in Defense Room 1.', 33, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(221, 319, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in Defense Room 1.', 33, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(222, 294, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in Defense Room 1.', 33, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(223, 326, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in Defense Room 1.', 33, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(224, 328, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 27, 2025 at 3:00 PM - 4:00 PM in Defense Room 1.', 33, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(225, 271, '', 'New Defense Assignment', 'You have been assigned as a panelist for team 9 (IT401)\'s defense on November 27, 2025 at 3:00 PM - 4:00 PM in Defense Room 1.', 33, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(226, 277, '', 'New Defense Assignment', 'You have been assigned as a panelist for team 9 (IT401)\'s defense on November 27, 2025 at 3:00 PM - 4:00 PM in Defense Room 1.', 33, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(227, 286, '', 'New Defense Assignment', 'You have been assigned as a panelist for team 9 (IT401)\'s defense on November 27, 2025 at 3:00 PM - 4:00 PM in Defense Room 1.', 33, NULL, 0, '2025-11-25 18:25:31', '2025-11-25 18:25:31'),
(228, 281, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 2 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web apps\n\nPlease approve or decline this assignment.', 34, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(229, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 2 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web apps\n\nPlease approve or decline this assignment.', 34, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(230, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 2 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 10:00 AM - 11:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web apps\n\nPlease approve or decline this assignment.', 34, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(231, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 34, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(232, 323, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 34, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(233, 330, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 34, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(234, 291, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 34, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(235, 317, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 34, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(236, 331, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 10:00 AM - 11:00 AM in Defense Room 1. Waiting for panelist approval.', 34, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(237, 281, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 3 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 9:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web and psychology\n\nPlease approve or decline this assignment.', 35, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(238, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 3 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 9:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web and psychology\n\nPlease approve or decline this assignment.', 35, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(239, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 3 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 9:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about web and psychology\n\nPlease approve or decline this assignment.', 35, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(240, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 35, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(241, 325, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 35, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(242, 311, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 35, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(243, 320, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 35, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(244, 304, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 35, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(245, 295, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 35, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(246, 286, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 8 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 2:00 PM - 3:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about social media and psychology\n\nPlease approve or decline this assignment.', 36, NULL, 1, '2025-11-26 10:35:48', '2025-11-26 14:16:30'),
(247, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 8 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 2:00 PM - 3:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about social media and psychology\n\nPlease approve or decline this assignment.', 36, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(248, 278, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 8 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 2:00 PM - 3:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about social media and psychology\n\nPlease approve or decline this assignment.', 36, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(249, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 2:00 PM - 3:00 PM in Defense Room 1. Waiting for panelist approval.', 36, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(250, 316, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 2:00 PM - 3:00 PM in Defense Room 1. Waiting for panelist approval.', 36, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(251, 324, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 2:00 PM - 3:00 PM in Defense Room 1. Waiting for panelist approval.', 36, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(252, 318, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 2:00 PM - 3:00 PM in Defense Room 1. Waiting for panelist approval.', 36, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(253, 310, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 2:00 PM - 3:00 PM in Defense Room 1. Waiting for panelist approval.', 36, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(254, 322, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 2:00 PM - 3:00 PM in Defense Room 1. Waiting for panelist approval.', 36, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(255, 271, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 4 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 8:00 PM - 9:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about conducting research\n\nPlease approve or decline this assignment.', 37, NULL, 0, '2025-11-26 10:35:48', '2025-11-26 10:35:48'),
(256, 270, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 4 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 8:00 PM - 9:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about conducting research\n\nPlease approve or decline this assignment.', 37, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(257, 286, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 4 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 8:00 PM - 9:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about conducting research\n\nPlease approve or decline this assignment.', 37, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(258, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1. Waiting for panelist approval.', 37, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(259, 288, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1. Waiting for panelist approval.', 37, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(260, 312, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1. Waiting for panelist approval.', 37, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(261, 308, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1. Waiting for panelist approval.', 37, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(262, 315, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1. Waiting for panelist approval.', 37, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(263, 313, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1. Waiting for panelist approval.', 37, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(264, 281, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 1 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 6:00 PM - 7:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about mobile apps\n\nPlease approve or decline this assignment.', 38, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(265, 272, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 1 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 6:00 PM - 7:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about mobile apps\n\nPlease approve or decline this assignment.', 38, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(266, 269, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 1 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 6:00 PM - 7:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about mobile apps\n\nPlease approve or decline this assignment.', 38, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(267, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 38, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(268, 292, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 38, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(269, 287, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 38, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(270, 300, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 38, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(271, 306, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 38, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(272, 333, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 6:00 PM - 7:00 PM in Defense Room 1. Waiting for panelist approval.', 38, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(273, 269, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 5 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 9:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about point of sales\n\nPlease approve or decline this assignment.', 39, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(274, 278, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 5 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 9:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about point of sales\n\nPlease approve or decline this assignment.', 39, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(275, 277, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 5 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 9:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about point of sales\n\nPlease approve or decline this assignment.', 39, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(276, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 39, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(277, 267, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 39, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(278, 268, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 39, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(279, 296, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 39, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(280, 299, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 39, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(281, 303, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 9:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 39, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(282, 278, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 6 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 8:00 AM - 9:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about plant growth using tech\n\nPlease approve or decline this assignment.', 40, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(283, 280, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 6 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 8:00 AM - 9:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about plant growth using tech\n\nPlease approve or decline this assignment.', 40, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(284, 283, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for team 6 (IT401)\'s defense:\n\n📅 Date: November 28, 2025\n🕒 Time: 8:00 AM - 9:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Information Technology - Web and Mobile Technology\n📝 Research: a research about plant growth using tech\n\nPlease approve or decline this assignment.', 40, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(285, 285, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 AM - 9:00 AM in Defense Room 1. Waiting for panelist approval.', 40, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(286, 309, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 AM - 9:00 AM in Defense Room 1. Waiting for panelist approval.', 40, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(287, 321, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 AM - 9:00 AM in Defense Room 1. Waiting for panelist approval.', 40, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(288, 329, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 AM - 9:00 AM in Defense Room 1. Waiting for panelist approval.', 40, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(289, 314, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 AM - 9:00 AM in Defense Room 1. Waiting for panelist approval.', 40, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(290, 332, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 AM - 9:00 AM in Defense Room 1. Waiting for panelist approval.', 40, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(291, 285, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1.', 41, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(292, 327, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1.', 41, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(293, 319, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1.', 41, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(294, 294, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1.', 41, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(295, 326, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1.', 41, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(296, 328, '', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1.', 41, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(297, 279, '', 'New Defense Assignment', 'You have been assigned as a panelist for team 9 (IT401)\'s defense on November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1.', 41, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(298, 270, '', 'New Defense Assignment', 'You have been assigned as a panelist for team 9 (IT401)\'s defense on November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1.', 41, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(299, 271, '', 'New Defense Assignment', 'You have been assigned as a panelist for team 9 (IT401)\'s defense on November 28, 2025 at 8:00 PM - 9:00 PM in Defense Room 1.', 41, NULL, 0, '2025-11-26 10:35:49', '2025-11-26 10:35:49'),
(300, 422, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\' has been approved and you can now proceed with your research.', 58, NULL, 0, '2025-11-30 11:30:11', '2025-11-30 11:30:11'),
(301, 437, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\' has been approved and you can now proceed with your research.', 58, NULL, 0, '2025-11-30 11:30:11', '2025-11-30 11:30:11'),
(302, 438, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\' has been approved and you can now proceed with your research.', 58, NULL, 0, '2025-11-30 11:30:11', '2025-11-30 11:30:11'),
(303, 439, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\' has been approved and you can now proceed with your research.', 58, NULL, 0, '2025-11-30 11:30:11', '2025-11-30 11:30:11'),
(304, 440, 'title_approved', 'Research Title Approved', 'Great news! Your research title \'GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\' has been approved and you can now proceed with your research.', 58, NULL, 0, '2025-11-30 11:30:11', '2025-11-30 11:30:11'),
(305, 269, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for GAIA\'s defense:\n\n📅 Date: December 2, 2025\n🕒 Time: 9:00 AM - 11:00 AM\n🏢 Room: CS\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\n\nPlease approve or decline this assignment.', 42, NULL, 1, '2025-11-30 11:32:37', '2025-11-30 11:35:17'),
(306, 427, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for GAIA\'s defense:\n\n📅 Date: December 2, 2025\n🕒 Time: 9:00 AM - 11:00 AM\n🏢 Room: CS\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\n\nPlease approve or decline this assignment.', 42, NULL, 0, '2025-11-30 11:32:37', '2025-11-30 11:32:37'),
(307, 451, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for GAIA\'s defense:\n\n📅 Date: December 2, 2025\n🕒 Time: 9:00 AM - 11:00 AM\n🏢 Room: CS\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\n\nPlease approve or decline this assignment.', 42, NULL, 0, '2025-11-30 11:32:37', '2025-11-30 11:32:37'),
(308, 422, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 2, 2025 at 9:00 AM - 11:00 AM in CS. Waiting for panelist approval.', 42, NULL, 0, '2025-11-30 11:32:37', '2025-11-30 11:32:37'),
(309, 437, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 2, 2025 at 9:00 AM - 11:00 AM in CS. Waiting for panelist approval.', 42, NULL, 0, '2025-11-30 11:32:37', '2025-11-30 11:32:37'),
(310, 438, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 2, 2025 at 9:00 AM - 11:00 AM in CS. Waiting for panelist approval.', 42, NULL, 0, '2025-11-30 11:32:37', '2025-11-30 11:32:37'),
(311, 439, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 2, 2025 at 9:00 AM - 11:00 AM in CS. Waiting for panelist approval.', 42, NULL, 0, '2025-11-30 11:32:37', '2025-11-30 11:32:37'),
(312, 440, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 2, 2025 at 9:00 AM - 11:00 AM in CS. Waiting for panelist approval.', 42, NULL, 0, '2025-11-30 11:32:37', '2025-11-30 11:32:37'),
(313, 422, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for DENGUEGUARD\'s defense:\n\n📅 Date: December 5, 2025\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING\n\nPlease approve or decline this assignment.', 43, NULL, 0, '2025-12-01 15:14:19', '2025-12-01 15:14:19'),
(314, 427, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for DENGUEGUARD\'s defense:\n\n📅 Date: December 5, 2025\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING\n\nPlease approve or decline this assignment.', 43, NULL, 0, '2025-12-01 15:14:19', '2025-12-01 15:14:19'),
(315, 436, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for DENGUEGUARD\'s defense:\n\n📅 Date: December 5, 2025\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING\n\nPlease approve or decline this assignment.', 43, NULL, 0, '2025-12-01 15:14:19', '2025-12-01 15:14:19'),
(316, 418, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 5, 2025 at 4:00 PM - 6:00 PM in Defense Room 1. Waiting for panelist approval.', 43, NULL, 0, '2025-12-01 15:14:19', '2025-12-01 15:14:19'),
(317, 432, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 5, 2025 at 4:00 PM - 6:00 PM in Defense Room 1. Waiting for panelist approval.', 43, NULL, 0, '2025-12-01 15:14:19', '2025-12-01 15:14:19'),
(318, 433, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 5, 2025 at 4:00 PM - 6:00 PM in Defense Room 1. Waiting for panelist approval.', 43, NULL, 0, '2025-12-01 15:14:19', '2025-12-01 15:14:19'),
(319, 434, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 5, 2025 at 4:00 PM - 6:00 PM in Defense Room 1. Waiting for panelist approval.', 43, NULL, 0, '2025-12-01 15:14:19', '2025-12-01 15:14:19'),
(320, 435, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 5, 2025 at 4:00 PM - 6:00 PM in Defense Room 1. Waiting for panelist approval.', 43, NULL, 0, '2025-12-01 15:14:19', '2025-12-01 15:14:19'),
(321, 427, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for GAIA\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\n\nPlease approve or decline this assignment.', 44, NULL, 0, '2025-12-01 15:15:50', '2025-12-01 15:15:50'),
(322, 430, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for GAIA\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\n\nPlease approve or decline this assignment.', 44, NULL, 0, '2025-12-01 15:15:50', '2025-12-01 15:15:50'),
(323, 441, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for GAIA\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.\n\nPlease approve or decline this assignment.', 44, NULL, 0, '2025-12-01 15:15:50', '2025-12-01 15:15:50'),
(324, 422, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 8:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 44, NULL, 0, '2025-12-01 15:15:50', '2025-12-01 15:15:50'),
(325, 437, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 8:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 44, NULL, 0, '2025-12-01 15:15:50', '2025-12-01 15:15:50'),
(326, 438, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 8:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 44, NULL, 0, '2025-12-01 15:15:50', '2025-12-01 15:15:50'),
(327, 439, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 8:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 44, NULL, 0, '2025-12-01 15:15:50', '2025-12-01 15:15:50'),
(328, 440, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 8:00 AM - 10:00 AM in Defense Room 1. Waiting for panelist approval.', 44, NULL, 0, '2025-12-01 15:15:50', '2025-12-01 15:15:50'),
(329, 422, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for PrivacyGuard\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: PrivacyGuard: A browser extension for PII protection in LPU-Cavite using Hybrid NER and Random Forest\n\nPlease approve or decline this assignment.', 45, NULL, 0, '2025-12-01 15:24:07', '2025-12-01 15:24:07'),
(330, 429, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for PrivacyGuard\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: PrivacyGuard: A browser extension for PII protection in LPU-Cavite using Hybrid NER and Random Forest\n\nPlease approve or decline this assignment.', 45, NULL, 0, '2025-12-01 15:24:07', '2025-12-01 15:24:07'),
(331, 441, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for PrivacyGuard\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: PrivacyGuard: A browser extension for PII protection in LPU-Cavite using Hybrid NER and Random Forest\n\nPlease approve or decline this assignment.', 45, NULL, 0, '2025-12-01 15:24:07', '2025-12-01 15:24:07'),
(332, 427, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 10:00 AM - 12:00 PM in Defense Room 1. Waiting for panelist approval.', 45, NULL, 0, '2025-12-01 15:24:07', '2025-12-01 15:24:07'),
(333, 461, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 10:00 AM - 12:00 PM in Defense Room 1. Waiting for panelist approval.', 45, NULL, 0, '2025-12-01 15:24:07', '2025-12-01 15:24:07'),
(334, 462, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 10:00 AM - 12:00 PM in Defense Room 1. Waiting for panelist approval.', 45, NULL, 0, '2025-12-01 15:24:07', '2025-12-01 15:24:07'),
(335, 463, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 10:00 AM - 12:00 PM in Defense Room 1. Waiting for panelist approval.', 45, NULL, 0, '2025-12-01 15:24:07', '2025-12-01 15:24:07'),
(336, 464, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 10:00 AM - 12:00 PM in Defense Room 1. Waiting for panelist approval.', 45, NULL, 0, '2025-12-01 15:24:07', '2025-12-01 15:24:07'),
(337, 431, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for NaviCav\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 12:00 PM - 2:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: NaviCav: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.\n\nPlease approve or decline this assignment.', 46, NULL, 0, '2025-12-01 15:25:09', '2025-12-01 15:25:09'),
(338, 418, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for NaviCav\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 12:00 PM - 2:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: NaviCav: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.\n\nPlease approve or decline this assignment.', 46, NULL, 0, '2025-12-01 15:25:09', '2025-12-01 15:25:09'),
(339, 441, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for NaviCav\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 12:00 PM - 2:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: NaviCav: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.\n\nPlease approve or decline this assignment.', 46, NULL, 0, '2025-12-01 15:25:09', '2025-12-01 15:25:09'),
(340, 419, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 12:00 PM - 2:00 PM in Defense Room 1. Waiting for panelist approval.', 46, NULL, 0, '2025-12-01 15:25:09', '2025-12-01 15:25:09'),
(341, 420, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 12:00 PM - 2:00 PM in Defense Room 1. Waiting for panelist approval.', 46, NULL, 0, '2025-12-01 15:25:09', '2025-12-01 15:25:09'),
(342, 421, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 12:00 PM - 2:00 PM in Defense Room 1. Waiting for panelist approval.', 46, NULL, 0, '2025-12-01 15:25:09', '2025-12-01 15:25:09'),
(343, 422, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 12:00 PM - 2:00 PM in Defense Room 1. Waiting for panelist approval.', 46, NULL, 0, '2025-12-01 15:25:09', '2025-12-01 15:25:09'),
(344, 422, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for RECOLOR\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: RECOLOR: A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION AND CLUSTERING ALGORITHMS\n\nPlease approve or decline this assignment.', 47, NULL, 0, '2025-12-01 15:26:22', '2025-12-01 15:26:22'),
(345, 430, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for RECOLOR\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: RECOLOR: A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION AND CLUSTERING ALGORITHMS\n\nPlease approve or decline this assignment.', 47, NULL, 0, '2025-12-01 15:26:22', '2025-12-01 15:26:22'),
(346, 441, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for RECOLOR\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: RECOLOR: A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION AND CLUSTERING ALGORITHMS\n\nPlease approve or decline this assignment.', 47, NULL, 0, '2025-12-01 15:26:22', '2025-12-01 15:26:22'),
(347, 423, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 2:00 PM - 4:00 PM in Defense Room 1. Waiting for panelist approval.', 47, NULL, 0, '2025-12-01 15:26:22', '2025-12-01 15:26:22'),
(348, 424, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 2:00 PM - 4:00 PM in Defense Room 1. Waiting for panelist approval.', 47, NULL, 0, '2025-12-01 15:26:22', '2025-12-01 15:26:22'),
(349, 425, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 2:00 PM - 4:00 PM in Defense Room 1. Waiting for panelist approval.', 47, NULL, 0, '2025-12-01 15:26:22', '2025-12-01 15:26:22'),
(350, 426, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 2:00 PM - 4:00 PM in Defense Room 1. Waiting for panelist approval.', 47, NULL, 0, '2025-12-01 15:26:22', '2025-12-01 15:26:22'),
(351, 427, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 2:00 PM - 4:00 PM in Defense Room 1. Waiting for panelist approval.', 47, NULL, 0, '2025-12-01 15:26:22', '2025-12-01 15:26:22'),
(352, 427, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Postra\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: Postra: A City-Based Missing Person Poster Detector with a Facial Recognition System for Community-Level Identification and Response\n\nPlease approve or decline this assignment.', 48, NULL, 0, '2025-12-01 15:26:54', '2025-12-01 15:26:54'),
(353, 430, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Postra\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: Postra: A City-Based Missing Person Poster Detector with a Facial Recognition System for Community-Level Identification and Response\n\nPlease approve or decline this assignment.', 48, NULL, 0, '2025-12-01 15:26:54', '2025-12-01 15:26:54'),
(354, 441, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Postra\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 1\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: Postra: A City-Based Missing Person Poster Detector with a Facial Recognition System for Community-Level Identification and Response\n\nPlease approve or decline this assignment.', 48, NULL, 0, '2025-12-01 15:26:54', '2025-12-01 15:26:54'),
(355, 428, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 4:00 PM - 6:00 PM in Defense Room 1. Waiting for panelist approval.', 48, NULL, 0, '2025-12-01 15:26:54', '2025-12-01 15:26:54'),
(356, 443, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 4:00 PM - 6:00 PM in Defense Room 1. Waiting for panelist approval.', 48, NULL, 0, '2025-12-01 15:26:54', '2025-12-01 15:26:54'),
(357, 444, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 4:00 PM - 6:00 PM in Defense Room 1. Waiting for panelist approval.', 48, NULL, 0, '2025-12-01 15:26:54', '2025-12-01 15:26:54'),
(358, 445, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 4:00 PM - 6:00 PM in Defense Room 1. Waiting for panelist approval.', 48, NULL, 0, '2025-12-01 15:26:54', '2025-12-01 15:26:54'),
(359, 446, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 4:00 PM - 6:00 PM in Defense Room 1. Waiting for panelist approval.', 48, NULL, 0, '2025-12-01 15:26:54', '2025-12-01 15:26:54'),
(360, 431, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for KNOWWHERE\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: KNOWWHERE: AN SLM-POWERED SEMANTIC SEARCH SYSTEM FOR ACADEMIC RESEARCH DISCOVERY\n\nPlease approve or decline this assignment.', 49, NULL, 0, '2025-12-01 15:32:12', '2025-12-01 15:32:12'),
(361, 418, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for KNOWWHERE\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: KNOWWHERE: AN SLM-POWERED SEMANTIC SEARCH SYSTEM FOR ACADEMIC RESEARCH DISCOVERY\n\nPlease approve or decline this assignment.', 49, NULL, 0, '2025-12-01 15:32:12', '2025-12-01 15:32:12'),
(362, 451, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for KNOWWHERE\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 8:00 AM - 10:00 AM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: KNOWWHERE: AN SLM-POWERED SEMANTIC SEARCH SYSTEM FOR ACADEMIC RESEARCH DISCOVERY\n\nPlease approve or decline this assignment.', 49, NULL, 0, '2025-12-01 15:32:12', '2025-12-01 15:32:12'),
(363, 429, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 8:00 AM - 10:00 AM in Defense Room 2. Waiting for panelist approval.', 49, NULL, 0, '2025-12-01 15:32:12', '2025-12-01 15:32:12'),
(364, 447, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 8:00 AM - 10:00 AM in Defense Room 2. Waiting for panelist approval.', 49, NULL, 0, '2025-12-01 15:32:12', '2025-12-01 15:32:12'),
(365, 448, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 8:00 AM - 10:00 AM in Defense Room 2. Waiting for panelist approval.', 49, NULL, 0, '2025-12-01 15:32:12', '2025-12-01 15:32:12'),
(366, 449, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 8:00 AM - 10:00 AM in Defense Room 2. Waiting for panelist approval.', 49, NULL, 0, '2025-12-01 15:32:12', '2025-12-01 15:32:12'),
(367, 450, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 8:00 AM - 10:00 AM in Defense Room 2. Waiting for panelist approval.', 49, NULL, 0, '2025-12-01 15:32:12', '2025-12-01 15:32:12'),
(368, 431, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Solari\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR REAL-TIME VISUAL AND SCENE DESCRIPTION FOR VISUALLY IMPAIRED\n\nPlease approve or decline this assignment.', 50, NULL, 0, '2025-12-01 15:32:47', '2025-12-01 15:32:47'),
(369, 428, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Solari\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR REAL-TIME VISUAL AND SCENE DESCRIPTION FOR VISUALLY IMPAIRED\n\nPlease approve or decline this assignment.', 50, NULL, 0, '2025-12-01 15:32:47', '2025-12-01 15:32:47'),
(370, 451, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for Solari\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 10:00 AM - 12:00 PM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR REAL-TIME VISUAL AND SCENE DESCRIPTION FOR VISUALLY IMPAIRED\n\nPlease approve or decline this assignment.', 50, NULL, 0, '2025-12-01 15:32:47', '2025-12-01 15:32:47'),
(371, 430, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 10:00 AM - 12:00 PM in Defense Room 2. Waiting for panelist approval.', 50, NULL, 0, '2025-12-01 15:32:47', '2025-12-01 15:32:47'),
(372, 452, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 10:00 AM - 12:00 PM in Defense Room 2. Waiting for panelist approval.', 50, NULL, 0, '2025-12-01 15:32:47', '2025-12-01 15:32:47'),
(373, 453, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 10:00 AM - 12:00 PM in Defense Room 2. Waiting for panelist approval.', 50, NULL, 0, '2025-12-01 15:32:47', '2025-12-01 15:32:47'),
(374, 454, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 10:00 AM - 12:00 PM in Defense Room 2. Waiting for panelist approval.', 50, NULL, 0, '2025-12-01 15:32:47', '2025-12-01 15:32:47'),
(375, 455, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 10:00 AM - 12:00 PM in Defense Room 2. Waiting for panelist approval.', 50, NULL, 0, '2025-12-01 15:32:47', '2025-12-01 15:32:47'),
(376, 431, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for HerbaScan\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: HerbaScan: A Convolutional Neural Network-Based Mobile Application for Plant Identification and Herbal Medicine Information\n\nPlease approve or decline this assignment.', 51, NULL, 0, '2025-12-01 15:33:27', '2025-12-01 15:33:27'),
(377, 429, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for HerbaScan\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: HerbaScan: A Convolutional Neural Network-Based Mobile Application for Plant Identification and Herbal Medicine Information\n\nPlease approve or decline this assignment.', 51, NULL, 0, '2025-12-01 15:33:27', '2025-12-01 15:33:27'),
(378, 451, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for HerbaScan\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 2:00 PM - 4:00 PM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: HerbaScan: A Convolutional Neural Network-Based Mobile Application for Plant Identification and Herbal Medicine Information\n\nPlease approve or decline this assignment.', 51, NULL, 0, '2025-12-01 15:33:27', '2025-12-01 15:33:27'),
(379, 414, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 2:00 PM - 4:00 PM in Defense Room 2. Waiting for panelist approval.', 51, NULL, 0, '2025-12-01 15:33:27', '2025-12-01 15:33:27'),
(380, 415, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 2:00 PM - 4:00 PM in Defense Room 2. Waiting for panelist approval.', 51, NULL, 0, '2025-12-01 15:33:27', '2025-12-01 15:33:27'),
(381, 416, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 2:00 PM - 4:00 PM in Defense Room 2. Waiting for panelist approval.', 51, NULL, 0, '2025-12-01 15:33:27', '2025-12-01 15:33:27'),
(382, 417, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 2:00 PM - 4:00 PM in Defense Room 2. Waiting for panelist approval.', 51, NULL, 0, '2025-12-01 15:33:27', '2025-12-01 15:33:27'),
(383, 418, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 2:00 PM - 4:00 PM in Defense Room 2. Waiting for panelist approval.', 51, NULL, 0, '2025-12-01 15:33:27', '2025-12-01 15:33:27'),
(384, 422, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for SalinDugo\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: SalinDugo: An AI-Enhanced Blood Donor Matching and Demand Forecasting Web Application with Regional Blood Type Insights and Location-Based Service Finder\n\nPlease approve or decline this assignment.', 52, NULL, 0, '2025-12-01 15:33:55', '2025-12-01 15:33:55'),
(385, 418, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for SalinDugo\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: SalinDugo: An AI-Enhanced Blood Donor Matching and Demand Forecasting Web Application with Regional Blood Type Insights and Location-Based Service Finder\n\nPlease approve or decline this assignment.', 52, NULL, 0, '2025-12-01 15:33:55', '2025-12-01 15:33:55'),
(386, 451, 'defense_approval', 'Defense Schedule Approval Required', 'You have been assigned as a panelist for SalinDugo\'s defense:\n\n📅 Date: December 6, 2025\n🕒 Time: 4:00 PM - 6:00 PM\n🏢 Room: Defense Room 2\n🎓 Program: Bachelor of Science in Computer Science - Software Engineering\n📝 Research: SalinDugo: An AI-Enhanced Blood Donor Matching and Demand Forecasting Web Application with Regional Blood Type Insights and Location-Based Service Finder\n\nPlease approve or decline this assignment.', 52, NULL, 0, '2025-12-01 15:33:55', '2025-12-01 15:33:55'),
(387, 431, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 4:00 PM - 6:00 PM in Defense Room 2. Waiting for panelist approval.', 52, NULL, 0, '2025-12-01 15:33:55', '2025-12-01 15:33:55'),
(388, 456, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 4:00 PM - 6:00 PM in Defense Room 2. Waiting for panelist approval.', 52, NULL, 0, '2025-12-01 15:33:55', '2025-12-01 15:33:55'),
(389, 457, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 4:00 PM - 6:00 PM in Defense Room 2. Waiting for panelist approval.', 52, NULL, 0, '2025-12-01 15:33:55', '2025-12-01 15:33:55'),
(390, 458, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 4:00 PM - 6:00 PM in Defense Room 2. Waiting for panelist approval.', 52, NULL, 0, '2025-12-01 15:33:55', '2025-12-01 15:33:55'),
(391, 459, 'defense_scheduled', 'Defense Schedule Created', 'Your team\'s defense has been scheduled for December 6, 2025 at 4:00 PM - 6:00 PM in Defense Room 2. Waiting for panelist approval.', 52, NULL, 0, '2025-12-01 15:33:55', '2025-12-01 15:33:55');

-- --------------------------------------------------------

--
-- Table structure for table `notification_actions`
--

DROP TABLE IF EXISTS `notification_actions`;
CREATE TABLE IF NOT EXISTS `notification_actions` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) UNSIGNED NOT NULL,
  `action_type` enum('approve_defense','reject_defense') NOT NULL,
  `action_data` longtext DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `notification_id` (`notification_id`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(30, 54, 'reject_defense', '{\"schedule_id\":\"9\"}', 0, NULL, '2025-11-21 14:46:29'),
(31, 68, 'approve_defense', '{\"schedule_id\":\"11\"}', 0, NULL, '2025-11-24 13:03:44'),
(32, 68, 'reject_defense', '{\"schedule_id\":\"11\"}', 0, NULL, '2025-11-24 13:03:44'),
(33, 69, 'approve_defense', '{\"schedule_id\":\"11\"}', 0, NULL, '2025-11-24 13:03:44'),
(34, 69, 'reject_defense', '{\"schedule_id\":\"11\"}', 0, NULL, '2025-11-24 13:03:44'),
(35, 70, 'approve_defense', '{\"schedule_id\":\"11\"}', 0, NULL, '2025-11-24 13:03:44'),
(36, 70, 'reject_defense', '{\"schedule_id\":\"11\"}', 0, NULL, '2025-11-24 13:03:44'),
(37, 76, 'approve_defense', '{\"schedule_id\":\"12\"}', 0, NULL, '2025-11-24 13:03:44'),
(38, 76, 'reject_defense', '{\"schedule_id\":\"12\"}', 0, NULL, '2025-11-24 13:03:44'),
(39, 77, 'approve_defense', '{\"schedule_id\":\"12\"}', 0, NULL, '2025-11-24 13:03:44'),
(40, 77, 'reject_defense', '{\"schedule_id\":\"12\"}', 0, NULL, '2025-11-24 13:03:44'),
(41, 78, 'approve_defense', '{\"schedule_id\":\"12\"}', 0, NULL, '2025-11-24 13:03:44'),
(42, 78, 'reject_defense', '{\"schedule_id\":\"12\"}', 0, NULL, '2025-11-24 13:03:44'),
(43, 84, 'approve_defense', '{\"schedule_id\":\"13\"}', 0, NULL, '2025-11-24 13:03:44'),
(44, 84, 'reject_defense', '{\"schedule_id\":\"13\"}', 0, NULL, '2025-11-24 13:03:44'),
(45, 85, 'approve_defense', '{\"schedule_id\":\"13\"}', 0, NULL, '2025-11-24 13:03:44'),
(46, 85, 'reject_defense', '{\"schedule_id\":\"13\"}', 0, NULL, '2025-11-24 13:03:44'),
(47, 86, 'approve_defense', '{\"schedule_id\":\"13\"}', 0, NULL, '2025-11-24 13:03:44'),
(48, 86, 'reject_defense', '{\"schedule_id\":\"13\"}', 0, NULL, '2025-11-24 13:03:44'),
(49, 92, 'approve_defense', '{\"schedule_id\":\"15\"}', 0, NULL, '2025-11-24 13:04:05'),
(50, 92, 'reject_defense', '{\"schedule_id\":\"15\"}', 0, NULL, '2025-11-24 13:04:05'),
(51, 93, 'approve_defense', '{\"schedule_id\":\"15\"}', 0, NULL, '2025-11-24 13:04:05'),
(52, 93, 'reject_defense', '{\"schedule_id\":\"15\"}', 0, NULL, '2025-11-24 13:04:05'),
(53, 94, 'approve_defense', '{\"schedule_id\":\"15\"}', 0, NULL, '2025-11-24 13:04:05'),
(54, 94, 'reject_defense', '{\"schedule_id\":\"15\"}', 0, NULL, '2025-11-24 13:04:05'),
(55, 100, 'approve_defense', '{\"schedule_id\":\"16\"}', 0, NULL, '2025-11-24 13:04:05'),
(56, 100, 'reject_defense', '{\"schedule_id\":\"16\"}', 0, NULL, '2025-11-24 13:04:05'),
(57, 101, 'approve_defense', '{\"schedule_id\":\"16\"}', 0, NULL, '2025-11-24 13:04:05'),
(58, 101, 'reject_defense', '{\"schedule_id\":\"16\"}', 0, NULL, '2025-11-24 13:04:05'),
(59, 102, 'approve_defense', '{\"schedule_id\":\"16\"}', 0, NULL, '2025-11-24 13:04:05'),
(60, 102, 'reject_defense', '{\"schedule_id\":\"16\"}', 0, NULL, '2025-11-24 13:04:05'),
(61, 108, 'approve_defense', '{\"schedule_id\":\"17\"}', 0, NULL, '2025-11-24 13:04:05'),
(62, 108, 'reject_defense', '{\"schedule_id\":\"17\"}', 0, NULL, '2025-11-24 13:04:05'),
(63, 109, 'approve_defense', '{\"schedule_id\":\"17\"}', 0, NULL, '2025-11-24 13:04:05'),
(64, 109, 'reject_defense', '{\"schedule_id\":\"17\"}', 0, NULL, '2025-11-24 13:04:05'),
(65, 110, 'approve_defense', '{\"schedule_id\":\"17\"}', 0, NULL, '2025-11-24 13:04:05'),
(66, 110, 'reject_defense', '{\"schedule_id\":\"17\"}', 0, NULL, '2025-11-24 13:04:05'),
(67, 116, 'approve_defense', '{\"schedule_id\":\"18\"}', 0, NULL, '2025-11-24 13:04:05'),
(68, 116, 'reject_defense', '{\"schedule_id\":\"18\"}', 0, NULL, '2025-11-24 13:04:05'),
(69, 117, 'approve_defense', '{\"schedule_id\":\"18\"}', 0, NULL, '2025-11-24 13:04:05'),
(70, 117, 'reject_defense', '{\"schedule_id\":\"18\"}', 0, NULL, '2025-11-24 13:04:05'),
(71, 118, 'approve_defense', '{\"schedule_id\":\"18\"}', 0, NULL, '2025-11-24 13:04:05'),
(72, 118, 'reject_defense', '{\"schedule_id\":\"18\"}', 0, NULL, '2025-11-24 13:04:05'),
(73, 124, 'approve_defense', '{\"schedule_id\":\"19\"}', 0, NULL, '2025-11-25 06:38:12'),
(74, 124, 'reject_defense', '{\"schedule_id\":\"19\"}', 0, NULL, '2025-11-25 06:38:12'),
(75, 125, 'approve_defense', '{\"schedule_id\":\"19\"}', 0, NULL, '2025-11-25 06:38:12'),
(76, 125, 'reject_defense', '{\"schedule_id\":\"19\"}', 0, NULL, '2025-11-25 06:38:12'),
(77, 126, 'approve_defense', '{\"schedule_id\":\"19\"}', 0, NULL, '2025-11-25 06:38:12'),
(78, 126, 'reject_defense', '{\"schedule_id\":\"19\"}', 0, NULL, '2025-11-25 06:38:12'),
(79, 132, 'approve_defense', '{\"schedule_id\":\"21\"}', 0, NULL, '2025-11-25 06:39:44'),
(80, 132, 'reject_defense', '{\"schedule_id\":\"21\"}', 0, NULL, '2025-11-25 06:39:44'),
(81, 133, 'approve_defense', '{\"schedule_id\":\"21\"}', 0, NULL, '2025-11-25 06:39:44'),
(82, 133, 'reject_defense', '{\"schedule_id\":\"21\"}', 0, NULL, '2025-11-25 06:39:44'),
(83, 134, 'approve_defense', '{\"schedule_id\":\"21\"}', 0, NULL, '2025-11-25 06:39:44'),
(84, 134, 'reject_defense', '{\"schedule_id\":\"21\"}', 0, NULL, '2025-11-25 06:39:44'),
(85, 140, 'approve_defense', '{\"schedule_id\":\"23\"}', 0, NULL, '2025-11-25 13:59:27'),
(86, 140, 'reject_defense', '{\"schedule_id\":\"23\"}', 0, NULL, '2025-11-25 13:59:27'),
(87, 141, 'approve_defense', '{\"schedule_id\":\"23\"}', 0, NULL, '2025-11-25 13:59:27'),
(88, 141, 'reject_defense', '{\"schedule_id\":\"23\"}', 0, NULL, '2025-11-25 13:59:27'),
(89, 142, 'approve_defense', '{\"schedule_id\":\"23\"}', 0, NULL, '2025-11-25 13:59:27'),
(90, 142, 'reject_defense', '{\"schedule_id\":\"23\"}', 0, NULL, '2025-11-25 13:59:27'),
(91, 148, 'approve_defense', '{\"schedule_id\":\"24\"}', 0, NULL, '2025-11-25 13:59:27'),
(92, 148, 'reject_defense', '{\"schedule_id\":\"24\"}', 0, NULL, '2025-11-25 13:59:27'),
(93, 149, 'approve_defense', '{\"schedule_id\":\"24\"}', 0, NULL, '2025-11-25 13:59:27'),
(94, 149, 'reject_defense', '{\"schedule_id\":\"24\"}', 0, NULL, '2025-11-25 13:59:27'),
(95, 150, 'approve_defense', '{\"schedule_id\":\"24\"}', 0, NULL, '2025-11-25 13:59:27'),
(96, 150, 'reject_defense', '{\"schedule_id\":\"24\"}', 0, NULL, '2025-11-25 13:59:27'),
(97, 156, 'approve_defense', '{\"schedule_id\":\"26\"}', 0, NULL, '2025-11-25 18:25:31'),
(98, 156, 'reject_defense', '{\"schedule_id\":\"26\"}', 0, NULL, '2025-11-25 18:25:31'),
(99, 157, 'approve_defense', '{\"schedule_id\":\"26\"}', 0, NULL, '2025-11-25 18:25:31'),
(100, 157, 'reject_defense', '{\"schedule_id\":\"26\"}', 0, NULL, '2025-11-25 18:25:31'),
(101, 158, 'approve_defense', '{\"schedule_id\":\"26\"}', 0, NULL, '2025-11-25 18:25:31'),
(102, 158, 'reject_defense', '{\"schedule_id\":\"26\"}', 0, NULL, '2025-11-25 18:25:31'),
(103, 165, 'approve_defense', '{\"schedule_id\":\"27\"}', 0, NULL, '2025-11-25 18:25:31'),
(104, 165, 'reject_defense', '{\"schedule_id\":\"27\"}', 0, NULL, '2025-11-25 18:25:31'),
(105, 166, 'approve_defense', '{\"schedule_id\":\"27\"}', 0, NULL, '2025-11-25 18:25:31'),
(106, 166, 'reject_defense', '{\"schedule_id\":\"27\"}', 0, NULL, '2025-11-25 18:25:31'),
(107, 167, 'approve_defense', '{\"schedule_id\":\"27\"}', 0, NULL, '2025-11-25 18:25:31'),
(108, 167, 'reject_defense', '{\"schedule_id\":\"27\"}', 0, NULL, '2025-11-25 18:25:31'),
(109, 174, 'approve_defense', '{\"schedule_id\":\"28\"}', 0, NULL, '2025-11-25 18:25:31'),
(110, 174, 'reject_defense', '{\"schedule_id\":\"28\"}', 0, NULL, '2025-11-25 18:25:31'),
(111, 175, 'approve_defense', '{\"schedule_id\":\"28\"}', 0, NULL, '2025-11-25 18:25:31'),
(112, 175, 'reject_defense', '{\"schedule_id\":\"28\"}', 0, NULL, '2025-11-25 18:25:31'),
(113, 176, 'approve_defense', '{\"schedule_id\":\"28\"}', 0, NULL, '2025-11-25 18:25:31'),
(114, 176, 'reject_defense', '{\"schedule_id\":\"28\"}', 0, NULL, '2025-11-25 18:25:31'),
(115, 183, 'approve_defense', '{\"schedule_id\":\"29\"}', 0, NULL, '2025-11-25 18:25:31'),
(116, 183, 'reject_defense', '{\"schedule_id\":\"29\"}', 0, NULL, '2025-11-25 18:25:31'),
(117, 184, 'approve_defense', '{\"schedule_id\":\"29\"}', 0, NULL, '2025-11-25 18:25:31'),
(118, 184, 'reject_defense', '{\"schedule_id\":\"29\"}', 0, NULL, '2025-11-25 18:25:31'),
(119, 185, 'approve_defense', '{\"schedule_id\":\"29\"}', 0, NULL, '2025-11-25 18:25:31'),
(120, 185, 'reject_defense', '{\"schedule_id\":\"29\"}', 0, NULL, '2025-11-25 18:25:31'),
(121, 192, 'approve_defense', '{\"schedule_id\":\"30\"}', 0, NULL, '2025-11-25 18:25:31'),
(122, 192, 'reject_defense', '{\"schedule_id\":\"30\"}', 0, NULL, '2025-11-25 18:25:31'),
(123, 193, 'approve_defense', '{\"schedule_id\":\"30\"}', 0, NULL, '2025-11-25 18:25:31'),
(124, 193, 'reject_defense', '{\"schedule_id\":\"30\"}', 0, NULL, '2025-11-25 18:25:31'),
(125, 194, 'approve_defense', '{\"schedule_id\":\"30\"}', 0, NULL, '2025-11-25 18:25:31'),
(126, 194, 'reject_defense', '{\"schedule_id\":\"30\"}', 0, NULL, '2025-11-25 18:25:31'),
(127, 201, 'approve_defense', '{\"schedule_id\":\"31\"}', 0, NULL, '2025-11-25 18:25:31'),
(128, 201, 'reject_defense', '{\"schedule_id\":\"31\"}', 0, NULL, '2025-11-25 18:25:31'),
(129, 202, 'approve_defense', '{\"schedule_id\":\"31\"}', 0, NULL, '2025-11-25 18:25:31'),
(130, 202, 'reject_defense', '{\"schedule_id\":\"31\"}', 0, NULL, '2025-11-25 18:25:31'),
(131, 203, 'approve_defense', '{\"schedule_id\":\"31\"}', 0, NULL, '2025-11-25 18:25:31'),
(132, 203, 'reject_defense', '{\"schedule_id\":\"31\"}', 0, NULL, '2025-11-25 18:25:31'),
(133, 210, 'approve_defense', '{\"schedule_id\":\"32\"}', 0, NULL, '2025-11-25 18:25:31'),
(134, 210, 'reject_defense', '{\"schedule_id\":\"32\"}', 0, NULL, '2025-11-25 18:25:31'),
(135, 211, 'approve_defense', '{\"schedule_id\":\"32\"}', 0, NULL, '2025-11-25 18:25:31'),
(136, 211, 'reject_defense', '{\"schedule_id\":\"32\"}', 0, NULL, '2025-11-25 18:25:31'),
(137, 212, 'approve_defense', '{\"schedule_id\":\"32\"}', 0, NULL, '2025-11-25 18:25:31'),
(138, 212, 'reject_defense', '{\"schedule_id\":\"32\"}', 0, NULL, '2025-11-25 18:25:31'),
(139, 228, 'approve_defense', '{\"schedule_id\":\"34\"}', 0, NULL, '2025-11-26 10:35:48'),
(140, 228, 'reject_defense', '{\"schedule_id\":\"34\"}', 0, NULL, '2025-11-26 10:35:48'),
(141, 229, 'approve_defense', '{\"schedule_id\":\"34\"}', 0, NULL, '2025-11-26 10:35:48'),
(142, 229, 'reject_defense', '{\"schedule_id\":\"34\"}', 0, NULL, '2025-11-26 10:35:48'),
(143, 230, 'approve_defense', '{\"schedule_id\":\"34\"}', 0, NULL, '2025-11-26 10:35:48'),
(144, 230, 'reject_defense', '{\"schedule_id\":\"34\"}', 0, NULL, '2025-11-26 10:35:48'),
(145, 237, 'approve_defense', '{\"schedule_id\":\"35\"}', 0, NULL, '2025-11-26 10:35:48'),
(146, 237, 'reject_defense', '{\"schedule_id\":\"35\"}', 0, NULL, '2025-11-26 10:35:48'),
(147, 238, 'approve_defense', '{\"schedule_id\":\"35\"}', 0, NULL, '2025-11-26 10:35:48'),
(148, 238, 'reject_defense', '{\"schedule_id\":\"35\"}', 0, NULL, '2025-11-26 10:35:48'),
(149, 239, 'approve_defense', '{\"schedule_id\":\"35\"}', 0, NULL, '2025-11-26 10:35:48'),
(150, 239, 'reject_defense', '{\"schedule_id\":\"35\"}', 0, NULL, '2025-11-26 10:35:48'),
(151, 246, 'approve_defense', '{\"schedule_id\":\"36\"}', 1, '2025-11-26 14:16:30', '2025-11-26 10:35:48'),
(152, 246, 'reject_defense', '{\"schedule_id\":\"36\"}', 1, '2025-11-26 14:16:30', '2025-11-26 10:35:48'),
(153, 247, 'approve_defense', '{\"schedule_id\":\"36\"}', 0, NULL, '2025-11-26 10:35:48'),
(154, 247, 'reject_defense', '{\"schedule_id\":\"36\"}', 0, NULL, '2025-11-26 10:35:48'),
(155, 248, 'approve_defense', '{\"schedule_id\":\"36\"}', 0, NULL, '2025-11-26 10:35:48'),
(156, 248, 'reject_defense', '{\"schedule_id\":\"36\"}', 0, NULL, '2025-11-26 10:35:48'),
(157, 255, 'approve_defense', '{\"schedule_id\":\"37\"}', 0, NULL, '2025-11-26 10:35:48'),
(158, 255, 'reject_defense', '{\"schedule_id\":\"37\"}', 0, NULL, '2025-11-26 10:35:48'),
(159, 256, 'approve_defense', '{\"schedule_id\":\"37\"}', 0, NULL, '2025-11-26 10:35:49'),
(160, 256, 'reject_defense', '{\"schedule_id\":\"37\"}', 0, NULL, '2025-11-26 10:35:49'),
(161, 257, 'approve_defense', '{\"schedule_id\":\"37\"}', 0, NULL, '2025-11-26 10:35:49'),
(162, 257, 'reject_defense', '{\"schedule_id\":\"37\"}', 0, NULL, '2025-11-26 10:35:49'),
(163, 264, 'approve_defense', '{\"schedule_id\":\"38\"}', 0, NULL, '2025-11-26 10:35:49'),
(164, 264, 'reject_defense', '{\"schedule_id\":\"38\"}', 0, NULL, '2025-11-26 10:35:49'),
(165, 265, 'approve_defense', '{\"schedule_id\":\"38\"}', 0, NULL, '2025-11-26 10:35:49'),
(166, 265, 'reject_defense', '{\"schedule_id\":\"38\"}', 0, NULL, '2025-11-26 10:35:49'),
(167, 266, 'approve_defense', '{\"schedule_id\":\"38\"}', 0, NULL, '2025-11-26 10:35:49'),
(168, 266, 'reject_defense', '{\"schedule_id\":\"38\"}', 0, NULL, '2025-11-26 10:35:49'),
(169, 273, 'approve_defense', '{\"schedule_id\":\"39\"}', 0, NULL, '2025-11-26 10:35:49'),
(170, 273, 'reject_defense', '{\"schedule_id\":\"39\"}', 0, NULL, '2025-11-26 10:35:49'),
(171, 274, 'approve_defense', '{\"schedule_id\":\"39\"}', 0, NULL, '2025-11-26 10:35:49'),
(172, 274, 'reject_defense', '{\"schedule_id\":\"39\"}', 0, NULL, '2025-11-26 10:35:49'),
(173, 275, 'approve_defense', '{\"schedule_id\":\"39\"}', 0, NULL, '2025-11-26 10:35:49'),
(174, 275, 'reject_defense', '{\"schedule_id\":\"39\"}', 0, NULL, '2025-11-26 10:35:49'),
(175, 282, 'approve_defense', '{\"schedule_id\":\"40\"}', 0, NULL, '2025-11-26 10:35:49'),
(176, 282, 'reject_defense', '{\"schedule_id\":\"40\"}', 0, NULL, '2025-11-26 10:35:49'),
(177, 283, 'approve_defense', '{\"schedule_id\":\"40\"}', 0, NULL, '2025-11-26 10:35:49'),
(178, 283, 'reject_defense', '{\"schedule_id\":\"40\"}', 0, NULL, '2025-11-26 10:35:49'),
(179, 284, 'approve_defense', '{\"schedule_id\":\"40\"}', 0, NULL, '2025-11-26 10:35:49'),
(180, 284, 'reject_defense', '{\"schedule_id\":\"40\"}', 0, NULL, '2025-11-26 10:35:49'),
(181, 305, 'approve_defense', '{\"schedule_id\":\"42\"}', 1, '2025-11-30 11:35:30', '2025-11-30 11:32:37'),
(182, 305, 'reject_defense', '{\"schedule_id\":\"42\"}', 1, '2025-11-30 11:35:30', '2025-11-30 11:32:37'),
(183, 306, 'approve_defense', '{\"schedule_id\":\"42\"}', 0, NULL, '2025-11-30 11:32:37'),
(184, 306, 'reject_defense', '{\"schedule_id\":\"42\"}', 0, NULL, '2025-11-30 11:32:37'),
(185, 307, 'approve_defense', '{\"schedule_id\":\"42\"}', 0, NULL, '2025-11-30 11:32:37'),
(186, 307, 'reject_defense', '{\"schedule_id\":\"42\"}', 0, NULL, '2025-11-30 11:32:37'),
(187, 313, 'approve_defense', '{\"schedule_id\":\"43\"}', 0, NULL, '2025-12-01 15:14:19'),
(188, 313, 'reject_defense', '{\"schedule_id\":\"43\"}', 0, NULL, '2025-12-01 15:14:19'),
(189, 314, 'approve_defense', '{\"schedule_id\":\"43\"}', 0, NULL, '2025-12-01 15:14:19'),
(190, 314, 'reject_defense', '{\"schedule_id\":\"43\"}', 0, NULL, '2025-12-01 15:14:19'),
(191, 315, 'approve_defense', '{\"schedule_id\":\"43\"}', 0, NULL, '2025-12-01 15:14:19'),
(192, 315, 'reject_defense', '{\"schedule_id\":\"43\"}', 0, NULL, '2025-12-01 15:14:19'),
(193, 321, 'approve_defense', '{\"schedule_id\":\"44\"}', 0, NULL, '2025-12-01 15:15:50'),
(194, 321, 'reject_defense', '{\"schedule_id\":\"44\"}', 0, NULL, '2025-12-01 15:15:50'),
(195, 322, 'approve_defense', '{\"schedule_id\":\"44\"}', 0, NULL, '2025-12-01 15:15:50'),
(196, 322, 'reject_defense', '{\"schedule_id\":\"44\"}', 0, NULL, '2025-12-01 15:15:50'),
(197, 323, 'approve_defense', '{\"schedule_id\":\"44\"}', 0, NULL, '2025-12-01 15:15:50'),
(198, 323, 'reject_defense', '{\"schedule_id\":\"44\"}', 0, NULL, '2025-12-01 15:15:50'),
(199, 329, 'approve_defense', '{\"schedule_id\":\"45\"}', 0, NULL, '2025-12-01 15:24:07'),
(200, 329, 'reject_defense', '{\"schedule_id\":\"45\"}', 0, NULL, '2025-12-01 15:24:07'),
(201, 330, 'approve_defense', '{\"schedule_id\":\"45\"}', 0, NULL, '2025-12-01 15:24:07'),
(202, 330, 'reject_defense', '{\"schedule_id\":\"45\"}', 0, NULL, '2025-12-01 15:24:07'),
(203, 331, 'approve_defense', '{\"schedule_id\":\"45\"}', 0, NULL, '2025-12-01 15:24:07'),
(204, 331, 'reject_defense', '{\"schedule_id\":\"45\"}', 0, NULL, '2025-12-01 15:24:07'),
(205, 337, 'approve_defense', '{\"schedule_id\":\"46\"}', 0, NULL, '2025-12-01 15:25:09'),
(206, 337, 'reject_defense', '{\"schedule_id\":\"46\"}', 0, NULL, '2025-12-01 15:25:09'),
(207, 338, 'approve_defense', '{\"schedule_id\":\"46\"}', 0, NULL, '2025-12-01 15:25:09'),
(208, 338, 'reject_defense', '{\"schedule_id\":\"46\"}', 0, NULL, '2025-12-01 15:25:09'),
(209, 339, 'approve_defense', '{\"schedule_id\":\"46\"}', 0, NULL, '2025-12-01 15:25:09'),
(210, 339, 'reject_defense', '{\"schedule_id\":\"46\"}', 0, NULL, '2025-12-01 15:25:09'),
(211, 344, 'approve_defense', '{\"schedule_id\":\"47\"}', 0, NULL, '2025-12-01 15:26:22'),
(212, 344, 'reject_defense', '{\"schedule_id\":\"47\"}', 0, NULL, '2025-12-01 15:26:22'),
(213, 345, 'approve_defense', '{\"schedule_id\":\"47\"}', 0, NULL, '2025-12-01 15:26:22'),
(214, 345, 'reject_defense', '{\"schedule_id\":\"47\"}', 0, NULL, '2025-12-01 15:26:22'),
(215, 346, 'approve_defense', '{\"schedule_id\":\"47\"}', 0, NULL, '2025-12-01 15:26:22'),
(216, 346, 'reject_defense', '{\"schedule_id\":\"47\"}', 0, NULL, '2025-12-01 15:26:22'),
(217, 352, 'approve_defense', '{\"schedule_id\":\"48\"}', 0, NULL, '2025-12-01 15:26:54'),
(218, 352, 'reject_defense', '{\"schedule_id\":\"48\"}', 0, NULL, '2025-12-01 15:26:54'),
(219, 353, 'approve_defense', '{\"schedule_id\":\"48\"}', 0, NULL, '2025-12-01 15:26:54'),
(220, 353, 'reject_defense', '{\"schedule_id\":\"48\"}', 0, NULL, '2025-12-01 15:26:54'),
(221, 354, 'approve_defense', '{\"schedule_id\":\"48\"}', 0, NULL, '2025-12-01 15:26:54'),
(222, 354, 'reject_defense', '{\"schedule_id\":\"48\"}', 0, NULL, '2025-12-01 15:26:54'),
(223, 360, 'approve_defense', '{\"schedule_id\":\"49\"}', 0, NULL, '2025-12-01 15:32:12'),
(224, 360, 'reject_defense', '{\"schedule_id\":\"49\"}', 0, NULL, '2025-12-01 15:32:12'),
(225, 361, 'approve_defense', '{\"schedule_id\":\"49\"}', 0, NULL, '2025-12-01 15:32:12'),
(226, 361, 'reject_defense', '{\"schedule_id\":\"49\"}', 0, NULL, '2025-12-01 15:32:12'),
(227, 362, 'approve_defense', '{\"schedule_id\":\"49\"}', 0, NULL, '2025-12-01 15:32:12'),
(228, 362, 'reject_defense', '{\"schedule_id\":\"49\"}', 0, NULL, '2025-12-01 15:32:12'),
(229, 368, 'approve_defense', '{\"schedule_id\":\"50\"}', 0, NULL, '2025-12-01 15:32:47'),
(230, 368, 'reject_defense', '{\"schedule_id\":\"50\"}', 0, NULL, '2025-12-01 15:32:47'),
(231, 369, 'approve_defense', '{\"schedule_id\":\"50\"}', 0, NULL, '2025-12-01 15:32:47'),
(232, 369, 'reject_defense', '{\"schedule_id\":\"50\"}', 0, NULL, '2025-12-01 15:32:47'),
(233, 370, 'approve_defense', '{\"schedule_id\":\"50\"}', 0, NULL, '2025-12-01 15:32:47'),
(234, 370, 'reject_defense', '{\"schedule_id\":\"50\"}', 0, NULL, '2025-12-01 15:32:47'),
(235, 376, 'approve_defense', '{\"schedule_id\":\"51\"}', 0, NULL, '2025-12-01 15:33:27'),
(236, 376, 'reject_defense', '{\"schedule_id\":\"51\"}', 0, NULL, '2025-12-01 15:33:27'),
(237, 377, 'approve_defense', '{\"schedule_id\":\"51\"}', 0, NULL, '2025-12-01 15:33:27'),
(238, 377, 'reject_defense', '{\"schedule_id\":\"51\"}', 0, NULL, '2025-12-01 15:33:27'),
(239, 378, 'approve_defense', '{\"schedule_id\":\"51\"}', 0, NULL, '2025-12-01 15:33:27'),
(240, 378, 'reject_defense', '{\"schedule_id\":\"51\"}', 0, NULL, '2025-12-01 15:33:27'),
(241, 384, 'approve_defense', '{\"schedule_id\":\"52\"}', 0, NULL, '2025-12-01 15:33:55'),
(242, 384, 'reject_defense', '{\"schedule_id\":\"52\"}', 0, NULL, '2025-12-01 15:33:55'),
(243, 385, 'approve_defense', '{\"schedule_id\":\"52\"}', 0, NULL, '2025-12-01 15:33:55'),
(244, 385, 'reject_defense', '{\"schedule_id\":\"52\"}', 0, NULL, '2025-12-01 15:33:55'),
(245, 386, 'approve_defense', '{\"schedule_id\":\"52\"}', 0, NULL, '2025-12-01 15:33:55'),
(246, 386, 'reject_defense', '{\"schedule_id\":\"52\"}', 0, NULL, '2025-12-01 15:33:55');

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

DROP TABLE IF EXISTS `notification_preferences`;
CREATE TABLE IF NOT EXISTS `notification_preferences` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `defense_scheduled` tinyint(1) NOT NULL DEFAULT 1,
  `title_approved` tinyint(1) NOT NULL DEFAULT 1,
  `requirement_created` tinyint(1) NOT NULL DEFAULT 1,
  `requirement_submitted` tinyint(1) NOT NULL DEFAULT 1,
  `requirement_approved` tinyint(1) NOT NULL DEFAULT 1,
  `requirement_rejected` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_content`
--

DROP TABLE IF EXISTS `page_content`;
CREATE TABLE IF NOT EXISTS `page_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `status` enum('published','draft') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `panelist_approvals`;
CREATE TABLE IF NOT EXISTS `panelist_approvals` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `defense_schedule_id` int(11) UNSIGNED NOT NULL,
  `panelist_id` int(11) UNSIGNED NOT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `response_date` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_panelist_defense` (`defense_schedule_id`,`panelist_id`),
  KEY `panelist_id` (`panelist_id`),
  KEY `idx_panelist_approvals_status` (`approval_status`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(24, 9, 272, 'pending', NULL, NULL, '2025-11-21 14:46:29'),
(25, 11, 271, 'pending', NULL, NULL, '2025-11-24 13:03:44'),
(26, 11, 282, 'pending', NULL, NULL, '2025-11-24 13:03:44'),
(27, 11, 270, 'pending', NULL, NULL, '2025-11-24 13:03:44'),
(28, 12, 286, 'pending', NULL, NULL, '2025-11-24 13:03:44'),
(29, 12, 278, 'pending', NULL, NULL, '2025-11-24 13:03:44'),
(30, 12, 270, 'pending', NULL, NULL, '2025-11-24 13:03:44'),
(31, 13, 285, 'pending', NULL, NULL, '2025-11-24 13:03:44'),
(32, 13, 270, 'pending', NULL, NULL, '2025-11-24 13:03:44'),
(33, 13, 283, 'pending', NULL, NULL, '2025-11-24 13:03:44'),
(34, 15, 277, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(35, 15, 282, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(36, 15, 281, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(37, 16, 278, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(38, 16, 280, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(39, 16, 281, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(40, 17, 285, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(41, 17, 272, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(42, 17, 283, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(43, 18, 285, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(44, 18, 272, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(45, 18, 286, 'pending', NULL, NULL, '2025-11-24 13:04:05'),
(46, 19, 271, 'pending', NULL, NULL, '2025-11-25 06:38:12'),
(47, 19, 269, 'pending', NULL, NULL, '2025-11-25 06:38:12'),
(48, 19, 270, 'pending', NULL, NULL, '2025-11-25 06:38:12'),
(49, 21, 270, 'pending', NULL, NULL, '2025-11-25 06:39:44'),
(50, 21, 269, 'pending', NULL, NULL, '2025-11-25 06:39:44'),
(51, 21, 271, 'pending', NULL, NULL, '2025-11-25 06:39:44'),
(52, 23, 277, 'pending', NULL, NULL, '2025-11-25 13:59:27'),
(53, 23, 272, 'pending', NULL, NULL, '2025-11-25 13:59:27'),
(54, 23, 269, 'pending', NULL, NULL, '2025-11-25 13:59:27'),
(55, 24, 270, 'pending', NULL, NULL, '2025-11-25 13:59:27'),
(56, 24, 281, 'pending', NULL, NULL, '2025-11-25 13:59:27'),
(57, 24, 278, 'pending', NULL, NULL, '2025-11-25 13:59:27'),
(58, 26, 277, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(59, 26, 272, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(60, 26, 270, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(61, 27, 271, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(62, 27, 269, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(63, 27, 282, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(64, 28, 277, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(65, 28, 278, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(66, 28, 271, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(67, 29, 269, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(68, 29, 272, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(69, 29, 278, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(70, 30, 269, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(71, 30, 281, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(72, 30, 286, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(73, 31, 286, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(74, 31, 281, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(75, 31, 283, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(76, 32, 270, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(77, 32, 283, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(78, 32, 271, 'pending', NULL, NULL, '2025-11-25 18:25:31'),
(79, 34, 281, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(80, 34, 271, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(81, 34, 272, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(82, 35, 281, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(83, 35, 270, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(84, 35, 272, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(85, 36, 286, 'approved', '2025-11-26 06:16:30', '', '2025-11-26 10:35:48'),
(86, 36, 270, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(87, 36, 278, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(88, 37, 271, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(89, 37, 270, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(90, 37, 286, 'pending', NULL, NULL, '2025-11-26 10:35:48'),
(91, 38, 281, 'pending', NULL, NULL, '2025-11-26 10:35:49'),
(92, 38, 272, 'pending', NULL, NULL, '2025-11-26 10:35:49'),
(93, 38, 269, 'pending', NULL, NULL, '2025-11-26 10:35:49'),
(94, 39, 269, 'pending', NULL, NULL, '2025-11-26 10:35:49'),
(95, 39, 278, 'pending', NULL, NULL, '2025-11-26 10:35:49'),
(96, 39, 277, 'pending', NULL, NULL, '2025-11-26 10:35:49'),
(97, 40, 278, 'pending', NULL, NULL, '2025-11-26 10:35:49'),
(98, 40, 280, 'pending', NULL, NULL, '2025-11-26 10:35:49'),
(99, 40, 283, 'pending', NULL, NULL, '2025-11-26 10:35:49'),
(100, 42, 269, 'approved', '2025-11-30 03:35:30', '', '2025-11-30 11:32:37'),
(101, 42, 427, 'pending', NULL, NULL, '2025-11-30 11:32:37'),
(102, 42, 451, 'pending', NULL, NULL, '2025-11-30 11:32:37'),
(103, 43, 422, 'pending', NULL, NULL, '2025-12-01 15:14:19'),
(104, 43, 427, 'pending', NULL, NULL, '2025-12-01 15:14:19'),
(105, 43, 436, 'pending', NULL, NULL, '2025-12-01 15:14:19'),
(106, 44, 427, 'pending', NULL, NULL, '2025-12-01 15:15:50'),
(107, 44, 430, 'pending', NULL, NULL, '2025-12-01 15:15:50'),
(108, 44, 441, 'pending', NULL, NULL, '2025-12-01 15:15:50'),
(109, 45, 422, 'pending', NULL, NULL, '2025-12-01 15:24:07'),
(110, 45, 429, 'pending', NULL, NULL, '2025-12-01 15:24:07'),
(111, 45, 441, 'pending', NULL, NULL, '2025-12-01 15:24:07'),
(112, 46, 431, 'pending', NULL, NULL, '2025-12-01 15:25:09'),
(113, 46, 418, 'pending', NULL, NULL, '2025-12-01 15:25:09'),
(114, 46, 441, 'pending', NULL, NULL, '2025-12-01 15:25:09'),
(115, 47, 422, 'pending', NULL, NULL, '2025-12-01 15:26:22'),
(116, 47, 430, 'pending', NULL, NULL, '2025-12-01 15:26:22'),
(117, 47, 441, 'pending', NULL, NULL, '2025-12-01 15:26:22'),
(118, 48, 427, 'pending', NULL, NULL, '2025-12-01 15:26:54'),
(119, 48, 430, 'pending', NULL, NULL, '2025-12-01 15:26:54'),
(120, 48, 441, 'pending', NULL, NULL, '2025-12-01 15:26:54'),
(121, 49, 431, 'pending', NULL, NULL, '2025-12-01 15:32:12'),
(122, 49, 418, 'pending', NULL, NULL, '2025-12-01 15:32:12'),
(123, 49, 451, 'pending', NULL, NULL, '2025-12-01 15:32:12'),
(124, 50, 431, 'pending', NULL, NULL, '2025-12-01 15:32:47'),
(125, 50, 428, 'pending', NULL, NULL, '2025-12-01 15:32:47'),
(126, 50, 451, 'pending', NULL, NULL, '2025-12-01 15:32:47'),
(127, 51, 431, 'pending', NULL, NULL, '2025-12-01 15:33:27'),
(128, 51, 429, 'pending', NULL, NULL, '2025-12-01 15:33:27'),
(129, 51, 451, 'pending', NULL, NULL, '2025-12-01 15:33:27'),
(130, 52, 422, 'pending', NULL, NULL, '2025-12-01 15:33:55'),
(131, 52, 418, 'pending', NULL, NULL, '2025-12-01 15:33:55'),
(132, 52, 451, 'pending', NULL, NULL, '2025-12-01 15:33:55');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
CREATE TABLE IF NOT EXISTS `programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `college` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `program_manuscript_requirements`;
CREATE TABLE IF NOT EXISTS `program_manuscript_requirements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requirement_id` int(10) UNSIGNED NOT NULL,
  `program_id` int(11) NOT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re-defense','general') NOT NULL,
  `is_required` tinyint(1) DEFAULT 1 COMMENT 'Is this manuscript required for this combination?',
  `submission_stage` enum('before_defense','at_defense','optional') DEFAULT 'before_defense' COMMENT 'When should it be submitted?',
  `can_revise_after` tinyint(1) DEFAULT 0 COMMENT 'Can team revise after this stage?',
  `visibility_to_panelist` tinyint(1) DEFAULT 1 COMMENT 'Should panelist see this at defense?',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_req_program_defense` (`requirement_id`,`program_id`,`defense_type`),
  KEY `idx_program_defense_type` (`program_id`,`defense_type`),
  KEY `idx_requirement_program` (`requirement_id`,`program_id`),
  KEY `idx_is_required` (`is_required`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Maps manuscript requirements to specific program + defense type combinations';

--
-- Dumping data for table `program_manuscript_requirements`
--

INSERT INTO `program_manuscript_requirements` (`id`, `requirement_id`, `program_id`, `defense_type`, `is_required`, `submission_stage`, `can_revise_after`, `visibility_to_panelist`, `created_at`, `updated_at`) VALUES
(3, 46, 79, 'title_proposal', 1, 'before_defense', 0, 1, '2025-11-21 18:22:37', '2025-11-21 18:22:37'),
(4, 46, 80, 'title_proposal', 1, 'before_defense', 0, 1, '2025-11-21 18:22:37', '2025-11-21 18:22:37');

-- --------------------------------------------------------

--
-- Table structure for table `program_manuscript_table`
--

DROP TABLE IF EXISTS `program_manuscript_table`;
CREATE TABLE IF NOT EXISTS `program_manuscript_table` (
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

--
-- Dumping data for table `program_manuscript_table`
--

INSERT INTO `program_manuscript_table` (`mapping_id`, `requirement_id`, `requirement_name`, `is_defense_manuscript`, `program_id`, `program_name`, `defense_type`, `is_required`, `submission_stage`, `can_revise_after`, `visibility_to_panelist`) VALUES
(3, 46, 'Research methods Template A', 1, 79, 'Bachelor of Science in Information Technology', 'title_proposal', 1, 'before_defense', 0, 1),
(4, 46, 'Research methods Template A', 1, 80, 'Bachelor of Science in Information Technology', 'title_proposal', 1, 'before_defense', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `program_manuscript_view`
--

DROP TABLE IF EXISTS `program_manuscript_view`;
CREATE TABLE IF NOT EXISTS `program_manuscript_view` (
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

DROP TABLE IF EXISTS `program_requirements_mapping`;
CREATE TABLE IF NOT EXISTS `program_requirements_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `program_id` int(11) NOT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re-defense','general') NOT NULL,
  `requirement_id` int(11) UNSIGNED NOT NULL,
  `is_mandatory` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_program_defense_requirement` (`program_id`,`defense_type`,`requirement_id`),
  KEY `idx_program_defense` (`program_id`,`defense_type`),
  KEY `idx_program_requirements` (`program_id`,`requirement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requirements`
--

DROP TABLE IF EXISTS `requirements`;
CREATE TABLE IF NOT EXISTS `requirements` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `requirement_type` enum('title_proposal','title_defense','final_defense','re-defense','general') DEFAULT 'general',
  `is_defense_manuscript` tinyint(1) DEFAULT 0,
  `allow_multiple_submissions` tinyint(1) DEFAULT 0 COMMENT 'Whether teams can submit multiple files (max 3) for this requirement',
  `max_submissions` int(11) DEFAULT 1 COMMENT 'Maximum number of submissions allowed (default 1, max 3)',
  `description` text DEFAULT NULL,
  `template_file` varchar(255) DEFAULT NULL,
  `template_original_name` varchar(255) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `research_titles`;
CREATE TABLE IF NOT EXISTS `research_titles` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `program` varchar(255) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `defended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `research_titles`
--

INSERT INTO `research_titles` (`id`, `team_id`, `title`, `program`, `approved_at`, `defended_at`, `created_at`, `updated_at`) VALUES
(19, 19, 'aa', NULL, NULL, NULL, '2025-11-25 00:21:20', '2025-11-25 00:21:20'),
(21, 21, 'a research about mobile apps', NULL, NULL, NULL, '2025-11-25 16:17:54', '2025-11-25 16:18:22'),
(22, 22, 'a research about web apps', NULL, NULL, NULL, '2025-11-25 16:19:49', '2025-11-25 16:19:49'),
(23, 23, 'a research about web and psychology', NULL, NULL, NULL, '2025-11-25 16:21:00', '2025-11-25 16:21:00'),
(24, 24, 'a research about conducting research', NULL, NULL, NULL, '2025-11-25 16:21:52', '2025-11-25 16:21:52'),
(25, 25, 'a research about point of sales', NULL, NULL, NULL, '2025-11-25 16:23:34', '2025-11-25 16:23:34'),
(26, 26, 'a research about plant growth using tech', NULL, NULL, NULL, '2025-11-25 16:24:27', '2025-11-25 16:24:27'),
(27, 27, 'a research about social media and psychology', NULL, NULL, NULL, '2025-11-25 16:25:45', '2025-11-25 16:25:45'),
(28, 28, 'a research about corelation of passing thesis and school environment', NULL, NULL, NULL, '2025-11-25 16:27:52', '2025-11-25 16:27:52'),
(29, 29, 'a research about another point of sale', NULL, NULL, NULL, '2025-11-25 16:32:09', '2025-11-25 16:32:09'),
(30, 30, 'a research about ai chatbot', NULL, NULL, NULL, '2025-11-25 16:33:29', '2025-11-25 16:33:29'),
(32, 32, 'a research about the effect of social media', NULL, NULL, NULL, '2025-11-25 17:03:36', '2025-11-25 17:03:36'),
(33, 33, 'a research about games', NULL, NULL, NULL, '2025-11-25 17:04:40', '2025-11-25 17:04:40'),
(34, 34, 'a research about ai', NULL, NULL, NULL, '2025-11-25 17:06:29', '2025-11-25 17:18:18'),
(35, 35, 'a research about another ai', NULL, NULL, NULL, '2025-11-25 17:08:54', '2025-11-25 17:08:54'),
(36, 36, 'a research about addiction of games', NULL, NULL, NULL, '2025-11-25 17:10:04', '2025-11-25 17:10:04'),
(37, 37, 'a research about cloud storage', NULL, NULL, NULL, '2025-11-25 17:11:12', '2025-11-25 17:11:12'),
(38, 38, 'a research about effect of chatbot', NULL, NULL, NULL, '2025-11-25 17:13:11', '2025-11-25 17:13:11'),
(39, 39, 'a research about cybersecurity', NULL, NULL, NULL, '2025-11-25 17:14:09', '2025-11-25 17:14:09'),
(40, 40, 'a research about cloud storage', NULL, NULL, NULL, '2025-11-25 17:15:22', '2025-11-25 17:15:22'),
(42, 42, 'a research about mobile', NULL, NULL, NULL, '2025-11-25 17:21:12', '2025-11-25 17:21:12'),
(43, 43, 'a research about mobile games', NULL, NULL, NULL, '2025-11-25 17:22:00', '2025-11-25 17:22:00'),
(44, 44, 'a research about software bugs', NULL, NULL, NULL, '2025-11-25 17:23:15', '2025-11-25 17:23:15'),
(45, 45, 'a research about another web app', NULL, NULL, NULL, '2025-11-25 17:24:30', '2025-11-25 17:24:30'),
(46, 46, 'a research about software bugs', NULL, NULL, NULL, '2025-11-25 17:26:23', '2025-11-25 17:26:23'),
(47, 47, 'a research about hybrid app', NULL, NULL, NULL, '2025-11-25 17:27:37', '2025-11-25 17:33:35'),
(48, 48, 'a research about hybrid mobile app', NULL, NULL, NULL, '2025-11-25 17:35:04', '2025-11-25 17:36:17'),
(49, 49, 'HerbaScan: A Convolutional Neural Network-Based Mobile Application for Plant Identification and Herbal Medicine Information', NULL, NULL, NULL, '2025-11-29 08:36:18', '2025-11-29 09:01:21'),
(50, 50, 'NaviCav: A WEB–MOBILE ROUTE PLANNER FOR MULTI-MODAL COMMUTING IN CAVITE USING MONTE CARLO–GUIDED GENETIC OPTIMIZATION.', NULL, NULL, NULL, '2025-11-29 08:43:37', '2025-11-29 09:00:57'),
(51, 51, 'RECOLOR: A MOBILE VISION ASSISTIVE TOOL FOR COLOR VISION  DEFICIENCY USING ADAPTIVE DALTONIZATION AND CLUSTERING ALGORITHMS', NULL, NULL, NULL, '2025-11-29 08:50:02', '2025-11-29 09:00:35'),
(52, 52, 'DENGUEGUARD: ARIMA-LSTM ENSEMBLE AND K-MEANS CLUSTERING FOR DENGUE OUTBREAK EARLY WARNING', NULL, NULL, NULL, '2025-11-30 05:48:02', '2025-11-30 05:48:02'),
(53, 53, 'Postra: A City-Based Missing Person Poster Detector with a Facial Recognition System for Community-Level Identification and Response', NULL, NULL, NULL, '2025-11-30 06:04:49', '2025-11-30 06:04:49'),
(54, 54, 'KNOWWHERE: AN SLM-POWERED SEMANTIC SEARCH SYSTEM FOR ACADEMIC RESEARCH DISCOVERY', NULL, NULL, NULL, '2025-11-30 06:13:08', '2025-11-30 06:13:24'),
(55, 55, 'Solari: AN AI-POWERED SMART GLASSES SYSTEM FOR REAL-TIME VISUAL AND SCENE DESCRIPTION FOR VISUALLY IMPAIRED', NULL, NULL, NULL, '2025-11-30 06:20:03', '2025-11-30 06:20:03'),
(56, 56, 'SalinDugo: An AI-Enhanced Blood Donor Matching and Demand Forecasting Web Application with Regional Blood Type Insights and Location-Based Service Finder', NULL, NULL, NULL, '2025-11-30 06:24:31', '2025-11-30 06:24:31'),
(58, 58, 'GAIA: An AI-Driven Framework Integrating Zero-Shot Classification and Geo-NER for Real-Time Environmental Hazard Detection.', '', '2025-12-01 03:30:11', NULL, '2025-11-30 08:06:36', '2025-12-01 03:30:11'),
(59, 59, 'PrivacyGuard: A browser extension for PII protection in LPU-Cavite using Hybrid NER and Random Forest', NULL, NULL, NULL, '2025-12-01 15:23:07', '2025-12-01 15:23:07');

-- --------------------------------------------------------

--
-- Table structure for table `re_defense_assessments`
--

DROP TABLE IF EXISTS `re_defense_assessments`;
CREATE TABLE IF NOT EXISTS `re_defense_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `defense_schedule_id` int(11) NOT NULL,
  `reason_for_redefense` text DEFAULT NULL,
  `initial_defense_schedule_id` int(11) DEFAULT NULL,
  `status` enum('pending','completed','passed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `initiated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_redefense` (`team_id`,`defense_schedule_id`),
  KEY `idx_team_redefense` (`team_id`,`status`),
  KEY `idx_defense_schedule` (`defense_schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rubrics`
--

DROP TABLE IF EXISTS `rubrics`;
CREATE TABLE IF NOT EXISTS `rubrics` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `max_score_per_criterion` int(11) DEFAULT 100 COMMENT 'Maximum score allowed per criterion for individual scoring mode',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubrics`
--

INSERT INTO `rubrics` (`id`, `name`, `description`, `rubric_type`, `is_individual_enabled`, `defense_type`, `rubric_description`, `pass_recommendation_text`, `fail_recommendation_text`, `fail_option_text`, `pass_threshold_1`, `pass_threshold_2`, `pass_threshold_3`, `max_total_score`, `max_members`, `is_active`, `created_at`, `updated_at`, `max_score_per_criterion`) VALUES
(1, 'A. Degree of Design / Level of Technical Complexity (30%)', '(RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 09:32:37', '2025-07-21 09:36:37', 100),
(2, 'B. Safety, Functionality, & Workmanship (20%)', '(RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 09:36:20', '2025-07-21 13:25:50', 100),
(3, 'Content (20%)', 'Final Manuscript Rubric (RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 09:41:50', '2025-07-21 09:45:42', 100),
(4, 'Organization (10%)', 'Final Manuscript Rubric (RE-PRESENTATION)', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-07-21 09:45:09', '2025-07-21 09:45:09', 100),
(5, 'Presentation and Defense', 'Final Manuscript Rubric (RE-PRESENTATION)', 'numerical', 1, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, 1, '2025-07-21 09:48:07', '2025-07-21 09:48:20', 100),
(6, 'FINAL RECOMMENDATION:', '(RE-PRESENTATION)', 'passfail', 0, 'Proposal Defense', '', 'System is accepted:', 'System is rejected:', 'below 65% acceptability; refer to thesis adviser', '100.00', '75.00', '65.00', 0, NULL, 1, '2025-07-21 09:52:11', '2025-07-21 09:53:02', 100),
(14, 'FDR - Written Manuscript', 'Final Defense Rubric – Group Grade', 'numerical', 0, 'Final Defense', 'Written Manuscript', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 12:12:43', '2025-11-30 12:26:09', 100),
(15, 'FDR - Developed System', 'Final Defense Rubric – Group Grade', 'numerical', 0, 'Final Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 12:23:47', '2025-11-30 12:25:56', 100),
(16, 'FDS - Written Manuscript', 'Final Defense Score Sheet - Group Grade', 'numerical', 0, 'Final Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 12:30:29', '2025-11-30 12:34:29', 100),
(17, 'FDS - Developed System', 'Final Defense Score Sheet - Group Grade', 'numerical', 0, 'Final Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 12:32:58', '2025-11-30 12:36:14', 100),
(18, 'FDS - Oral Defense', 'Final Defense Score Sheet - Individual Grade', 'numerical', 1, 'Final Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, 1, '2025-11-30 12:45:58', '2025-11-30 15:03:32', 100),
(19, 'FDS - Final Recommendation', 'Final Defense Score Sheet - Final Recommendation', 'passfail', 0, 'Final Defense', '', 'Manuscript is accepted:', 'The Manuscript is rejected:', 'below 70% acceptability (refer to research adviser and for re-defense)', '100.00', '75.00', '65.00', 0, NULL, 1, '2025-11-30 12:47:53', '2025-11-30 12:48:15', 100),
(20, 'PRS - Written Manuscript and Quality of the Developed System', 'Proposal Re-Presentation Score Sheet - Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 13:47:51', '2025-11-30 14:56:39', 100),
(21, 'PRS - Oral Defense Presentation', 'Proposal Re-Presentation Score Sheet - Individual Grade', 'numerical', 1, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, 1, '2025-11-30 13:50:26', '2025-11-30 15:05:16', 100),
(22, 'PRS - Oral Defense Delivery', 'Proposal Re-Presentation Score Sheet - Individual Grade', 'numerical', 1, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, 1, '2025-11-30 13:51:55', '2025-11-30 15:01:25', 100),
(23, 'PRS - Final Recommendation', 'Proposal Re-Presentation Score Sheet', 'passfail', 0, 'Re-Defense', '', 'The research proposal is accepted:', 'The research proposal is rejected:', '(below 70% acceptability) – subject for re-enrollment', '100.00', '75.00', '65.00', 0, NULL, 1, '2025-11-30 13:56:45', '2025-11-30 13:59:08', 100),
(24, 'PRR - A1 - Content – Manuscript', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 14:07:10', '2025-11-30 14:07:10', 100),
(25, 'PRR - A2 - Content – Manuscript', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 14:13:35', '2025-11-30 14:13:35', 100),
(26, 'PRR - A3 - Content – Manuscript', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 14:17:36', '2025-11-30 14:17:36', 100),
(27, 'PRR - B - Content – Manuscript', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 14:22:32', '2025-11-30 14:22:32', 100),
(28, 'PRR - Organization - Manuscript', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 14:28:31', '2025-11-30 14:28:31', 100),
(29, 'PRR - Novelty and Impact', 'Research Proposal Re-Presentation Rubric – Group Grade', 'numerical', 0, 'Re-Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 14:32:31', '2025-11-30 14:32:31', 100),
(30, 'PDS - Written Manuscript and Quality of the Developed System', 'Proposal Defense Score Sheet - Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 14:55:01', '2025-11-30 15:13:12', 100),
(31, 'PDS - Oral Defense Presentation', 'Proposal Defense Score Sheet - Individual Grade', 'numerical', 1, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, 1, '2025-11-30 14:59:54', '2025-11-30 15:09:22', 100),
(32, 'PDS - Oral Defense Delivery', 'Proposal Defense Score Sheet - Individual Grade', 'numerical', 1, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, 1, '2025-11-30 15:08:02', '2025-11-30 15:12:59', 100),
(33, 'PDS - Final Recommendation', 'Proposal Defense Score Sheet', 'passfail', 0, 'Proposal Defense', '', 'The research proposal is accepted:', 'The research proposal is rejected:', 'below 70% acceptability (refer to research adviser and for redefense)', '100.00', '75.00', '65.00', 0, NULL, 1, '2025-11-30 15:12:43', '2025-11-30 15:12:43', 100),
(34, 'PDR - A1 - Content - Manuscript', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 15:20:54', '2025-11-30 15:20:54', 100),
(35, 'PDR - A2 - Content - Manuscript', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 15:23:47', '2025-11-30 15:23:47', 100),
(36, 'PDR - A3 - Content - Manuscript', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 15:27:06', '2025-11-30 15:27:06', 100),
(37, 'PDR - B - Content - Quality of the Developed System', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 15:31:36', '2025-11-30 15:31:36', 100),
(38, 'PDR - Organization - Manuscript', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 15:35:49', '2025-11-30 15:35:49', 100),
(39, 'PDR - Novelty and Impact', 'Research Proposal Defense Rubric – Group Grade', 'numerical', 0, 'Proposal Defense', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2025-11-30 15:38:47', '2025-11-30 15:38:47', 100);

-- --------------------------------------------------------

--
-- Table structure for table `rubric_criteria`
--

DROP TABLE IF EXISTS `rubric_criteria`;
CREATE TABLE IF NOT EXISTS `rubric_criteria` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` int(11) UNSIGNED NOT NULL COMMENT 'FK to rubrics table',
  `criterion_text` text NOT NULL COMMENT 'The main text for the criterion row',
  `criterion_detail` text DEFAULT NULL COMMENT 'Optional secondary description (e.g., for Yes/No)',
  `order_index` int(11) NOT NULL COMMENT 'Order of this criterion row within the rubric',
  `is_individual` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rubric_criterion_order` (`rubric_id`,`order_index`),
  KEY `rubric_id` (`rubric_id`)
) ENGINE=InnoDB AUTO_INCREMENT=237 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores criteria rows for Numerical and Yes/No rubrics';


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
(49, 7, 'b', '[\"b\",\"b\",\"b\"]', 1, 0, '2025-07-21 13:27:25', '2025-07-21 13:27:25'),
(69, 8, '1. Clarity of Research Problem and Objectives', '[\"5%\"]', 0, 0, '2025-11-30 09:09:24', '2025-11-30 09:09:24'),
(70, 8, '2. Extent of Review of Related Literature', '[\"5%\"]', 1, 0, '2025-11-30 09:09:24', '2025-11-30 09:09:24'),
(71, 8, '3. Appropriateness of Methodology', '[\"5%\"]', 2, 0, '2025-11-30 09:09:24', '2025-11-30 09:09:24'),
(72, 8, '4. Data Presentation and Depth of Analysis', '[\"5%\"]', 3, 0, '2025-11-30 09:09:24', '2025-11-30 09:09:24'),
(73, 8, '5. Logic of Conclusion and Recommendations', '[\"5%\"]', 4, 0, '2025-11-30 09:09:24', '2025-11-30 09:09:24'),
(74, 8, '6. Order and Neatness of the Manuscript', '[\"5%\"]', 5, 0, '2025-11-30 09:09:24', '2025-11-30 09:09:24'),
(75, 9, '1. Completeness of Features', '[\"5%\"]', 0, 0, '2025-11-30 09:10:26', '2025-11-30 09:10:26'),
(76, 9, '2. Accuracy and Reliability', '[\"5%\"]', 1, 0, '2025-11-30 09:10:26', '2025-11-30 09:10:26'),
(77, 9, '3. Error Handling and Validation', '[\"5%\"]', 2, 0, '2025-11-30 09:10:26', '2025-11-30 09:10:26'),
(78, 9, '4. Design and Layout', '[\"5%\"]', 3, 0, '2025-11-30 09:10:26', '2025-11-30 09:10:26'),
(79, 9, '5. Security and Data Integrity', '[\"5%\"]', 4, 0, '2025-11-30 09:10:26', '2025-11-30 09:10:26'),
(80, 9, '6. Innovation and Scalability', '[\"5%\"]', 5, 0, '2025-11-30 09:10:26', '2025-11-30 09:10:26'),
(81, 10, '1. Clarity and mastery in the presentation', NULL, 0, 1, '2025-11-30 09:17:01', '2025-11-30 09:17:01'),
(82, 10, '2. Articulate response to the inquiries', NULL, 1, 1, '2025-11-30 09:17:01', '2025-11-30 09:17:01'),
(83, 10, '3. Proper demeanor and dress code', NULL, 2, 1, '2025-11-30 09:17:01', '2025-11-30 09:17:01'),
(84, 12, 'Clarity of Research Problem and Objectives', '[\"Problem \\/ objectives are unclear, unfocused, or missing\",\"Problem \\/ objectives are stated but vague or loosely connected\",\"Problem \\/ objectives are clear but may lack depth or refinement\",\"Problem \\/ objectives are clearly presented and logically aligned\",\"Problem \\/ objectives are exceptionally clear, well-defined, and strongly aligned to the study\"]', 0, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(85, 12, 'Extent of Review of Related Literature', '[\"Lacks relevant literature; lacks synthesis; sources are outdated\",\"Limited sources or weak connection to the topic; minimal synthesis\",\"Adequate sources with acceptable synthesis; some gaps\",\"Well-selected and current sources; clear synthesis and connection to the topic\",\"Comprehensive, updated, and well-synthesized literature showing strong theoretical grounding\"]', 1, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(86, 12, 'Appropriateness of Methodology', '[\"Methodology is inappropriate, incomplete, or not described\",\"Method is partly appropriate but lacks clarity or justification\",\"Method is appropriate with sufficient explanation; minor gaps\",\"Clearly appropriate method with good justification and detail\",\"Highly appropriate, well-justified, and thoroughly detailed methodology\"]', 2, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(87, 12, 'Data Presentation and Depth of Analysis', '[\"Data is unclear, disorganized, or confusing; tables\\/charts missing or irrelevant\",\"Data somewhat understandable but lacks organization or proper labeling\",\"Data is clear with adequate tables\\/charts; minor organizational issues\",\"Data is clear, well-organized; visuals enhance understanding\",\"Data is professionally presented, highly organized, and strongly enhances analysis\"]', 3, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(88, 12, 'Logic of Conclusion and Recommendations', '[\"Conclusions are unsupported or irrelevant; recommendations missing\",\"Conclusions somewhat related but weakly supported\",\"Conclusions are aligned with findings; recommendations present\",\"Conclusions are strong, logical, and well-supported\",\"Conclusions are compelling, insightful, and strongly supported with actionable recommendations\"]', 4, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(89, 12, 'Order and Neatness of the Manuscript', '[\"Manuscript is messy, disorganized, and difficult to follow; formatting inconsistent\",\"Some organization present but contains clutter, inconsistencies, or poor formatting\",\"Manuscript is generally organized and readable; minor formatting issues\",\"Well-organized, neat, and easy to follow with consistent formatting\",\"Exceptionally neat, professionally formatted, and highly organized throughout\"]', 5, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(96, 13, 'Completeness of Features', '[\"Key features missing; system incomplete\",\"Some features implemented but lacks core functionality\",\"Most features implemented; minor missing elements\",\"All required features complete and functional\",\"Features fully complete, exceeding expectations with enhancements\"]', 0, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(97, 13, 'Accuracy and Reliability', '[\"System frequently fails or produces incorrect results\",\"Occasional errors; limited reliability\",\"Generally accurate; minor inconsistencies\",\"Accurate and reliable under most conditions\",\"Highly accurate, stable, and consistently reliable\"]', 1, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(98, 13, 'Error Handling and Validation', '[\"No validation; frequent errors and crashes\",\"Minimal validation; some unhandled errors\",\"Adequate validation; occasional issues\",\"Strong validation and consistent error handling\",\"Robust validation with comprehensive error handling mechanisms\"]', 2, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(99, 13, 'Design and Layout', '[\"Interface is cluttered, inconsistent, or difficult to navigate\",\"Basic layout; lacks visual structure or usability\",\"Clean and acceptable design with some usability issues\",\"Visually appealing, consistent, and user-friendly\",\"Professional, intuitive, and polished interface with excellent user experience\"]', 3, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(100, 13, 'Security and Data Integrity', '[\"Security and Data Integrity\",\"Minimal security; vulnerable to breaches\",\"Basic security implemented; some risks remain\",\"Strong security measures and good data protection\",\"Highly secure system with strong integrity, proper encryption, and safeguards\"]', 4, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(101, 13, 'Innovation and Scalability', '[\"No innovative features; limited to basic functions\",\"No innovative features; limited to basic functions\",\"Some innovative elements; moderate scalability\",\"Innovative design with good potential for growth\",\"Highly innovative and fully scalable system with future-ready architecture\"]', 5, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(115, 15, 'Completeness of Features', '[\"Features fully complete, exceeding expectations with enhancements\",\"All required features complete and functional\",\"Most features implemented; minor missing elements\",\"Some features implemented but lacks core functionality\",\"Key features missing; system incomplete\"]', 0, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(116, 15, 'Accuracy and Reliability', '[\"Highly accurate, stable, and consistently reliable\",\"Accurate and reliable under most conditions\",\"Generally accurate; minor inconsistencies\",\"Occasional errors; limited reliability\",\"System frequently fails or produces incorrect results\"]', 1, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(117, 15, 'Error Handling and Validation', '[\"Robust validation with comprehensive error handling mechanisms\",\"Strong validation and consistent error handling\",\"Adequate validation; occasional issues\",\"Minimal validation; some unhandled errors\",\"No validation; frequent errors and crashes\"]', 2, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(118, 15, 'Design and Layout', '[\"Professional, intuitive, and polished interface with excellent user experience\",\"Visually appealing, consistent, and user-friendly\",\"Clean and acceptable design with some usability issues\",\"Basic layout; lacks visual structure or usability\",\"Interface is cluttered, inconsistent, or difficult to navigate\"]', 3, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(119, 15, 'Security and Data Integrity', '[\"Highly secure system with strong integrity, proper encryption, and safeguards\",\"Strong security measures and good data protection\",\"Basic security implemented; some risks remain\",\"Minimal security; vulnerable to breaches\",\"No security measures; high risk of data exposure\"]', 4, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(120, 15, 'Innovation and Scalability', '[\"Highly innovative and fully scalable system with future-ready architecture\",\"Innovative design with good potential for growth\",\"Some innovative elements; moderate scalability\",\"Minimal innovation; system not scalable\",\"No innovative features; limited to basic functions\"]', 5, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(121, 14, 'Clarity of Research Problem and Objectives', '[\"Problem \\/ objectives are exceptionally clear, well-defined, and strongly aligned to the study\",\"Problem \\/ objectives are clearly presented and logically aligned\",\"Problem \\/ objectives are clear but may lack depth or refinement\",\"Problem \\/ objectives are stated but vague or loosely connected\",\"Problem \\/ objectives are unclear, unfocused, or missing\"]', 0, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(122, 14, 'Extent of Review of Related Literature', '[\"Comprehensive, updated, and well-synthesized literature showing strong theoretical grounding\",\"Well-selected and current sources; clear synthesis and connection to the topic\",\"Adequate sources with acceptable synthesis; some gaps\",\"Limited sources or weak connection to the topic; minimal synthesis\",\"Lacks relevant literature; lacks synthesis; sources are outdated\"]', 1, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(123, 14, 'Appropriateness of Methodology', '[\"Highly appropriate, well-justified, and thoroughly detailed methodology\",\"Clearly appropriate method with good justification and detail\",\"Method is appropriate with sufficient explanation; minor gaps\",\"Method is partly appropriate but lacks clarity or justification\",\"Methodology is inappropriate, incomplete, or not described\"]', 2, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(124, 14, 'Data Presentation and Depth of Analysis', '[\"Data is professionally presented, highly organized, and strongly enhances analysis\",\"Data is clear, well-organized; visuals enhance understanding\",\"Data is clear with adequate tables\\/charts; minor organizational issues\",\"Data somewhat understandable but lacks organization or proper labeling\",\"Data is unclear, disorganized, or confusing; tables\\/charts missing or irrelevant\"]', 3, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(125, 14, 'Logic of Conclusion and Recommendations', '[\"Conclusions are compelling, insightful, and strongly supported with actionable recommendations\",\"Conclusions are strong, logical, and well-supported\",\"Conclusions are aligned with findings; recommendations present\",\"Conclusions somewhat related but weakly supported\",\"Conclusions are unsupported or irrelevant; recommendations missing\"]', 4, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(126, 14, 'Order and Neatness of the Manuscript', '[\"Exceptionally neat, professionally formatted, and highly organized throughout\",\"Well-organized, neat, and easy to follow with consistent formatting\",\"Manuscript is generally organized and readable; minor formatting issues\",\"Some organization present but contains clutter, inconsistencies, or poor formatting\",\"Manuscript is messy, disorganized, and difficult to follow; formatting inconsistent\"]', 5, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(134, 16, '1. Clarity of Research Problem and Objectives', '[\"\"]', 0, 0, '2025-11-30 12:34:29', '2025-11-30 12:34:29'),
(135, 16, '2. Extent of Review of Related Literature', '[\"\"]', 1, 0, '2025-11-30 12:34:29', '2025-11-30 12:34:29'),
(136, 16, '3. Appropriateness of Methodology', '[\"\"]', 2, 0, '2025-11-30 12:34:29', '2025-11-30 12:34:29'),
(137, 16, '4. Data Presentation and Depth of Analysis', '[\"\"]', 3, 0, '2025-11-30 12:34:29', '2025-11-30 12:34:29'),
(138, 16, '5. Logic of Conclusion and Recommendations', '[\"\"]', 4, 0, '2025-11-30 12:34:29', '2025-11-30 12:34:29'),
(139, 16, '6. Order and Neatness of the Manuscript', '[\"\"]', 5, 0, '2025-11-30 12:34:29', '2025-11-30 12:34:29'),
(140, 17, '1. Completeness of Features', '[\"5%\"]', 0, 0, '2025-11-30 12:36:14', '2025-11-30 12:36:14'),
(141, 17, '2. Accuracy and Reliability', '[\"5%\"]', 1, 0, '2025-11-30 12:36:14', '2025-11-30 12:36:14'),
(142, 17, '3. Error Handling and Validation', '[\"5%\"]', 2, 0, '2025-11-30 12:36:14', '2025-11-30 12:36:14'),
(143, 17, '4. Design and Layout', '[\"5%\"]', 3, 0, '2025-11-30 12:36:14', '2025-11-30 12:36:14'),
(144, 17, '5. Security and Data Integrity', '[\"5%\"]', 4, 0, '2025-11-30 12:36:14', '2025-11-30 12:36:14'),
(145, 17, '6. Innovation and Scalability', '[\"5%\"]', 5, 0, '2025-11-30 12:36:14', '2025-11-30 12:36:14'),
(160, 24, 'Chapter 1', '[\"Thorough, wellarticulated context; strongly justifies the study with relevant support\",\"Provides clear and relevant background; rationale is present but may lack depth\",\"Provides minimal context with limited connection to the problem; lacks depth\",\"Lacks clarity, relevance, or justification; context is missing or inappropriate\"]', 0, 0, '2025-11-30 14:07:10', '2025-11-30 14:07:10'),
(161, 24, 'Chapter 1', '[\"Objectives are precise, measurable, and clearly aligned with the research problem\",\"Objectives are clear and aligned with the problem statement\",\"Objectives are stated but unclear or not aligned with the problem\",\"Objectives are vague, irrelevant, or missing\"]', 1, 0, '2025-11-30 14:07:10', '2025-11-30 14:07:10'),
(162, 24, 'Chapter 1', '[\"Strong justification of the study\\u2019s importance; clearly identifies impact and beneficiaries\",\"Adequately explains importance and identifies key beneficiaries\",\"Minimally explains importance; beneficiaries are unclear\",\"No clear value to stakeholders; lacks justification or impact\"]', 2, 0, '2025-11-30 14:07:10', '2025-11-30 14:07:10'),
(163, 24, 'Chapter 1', '[\"Scope and limitations are specific, realistic, and critically analyzed in the context of the study\",\"Scope and limitations are clearly defined and relevant\",\"Scope and limitations are present but lack detail or clarity\",\"Scope is vague or too broad; limitations not identified or irrelevant\"]', 3, 0, '2025-11-30 14:07:10', '2025-11-30 14:07:10'),
(164, 25, 'Chapter II', '[\"Well-curated, up-todate, and highly relevant sources; shows clear synthesis and strong critical insight connecting to the study\",\"Adequate selection of relevant sources; demonstrates synthesis and some critical thinking\",\"Limited and weakly connected sources; minimal synthesis of ideas\",\"Lacks relevant sources or contains outdated\\/irrelevant literature; no synthesis or connection to study\"]', 0, 0, '2025-11-30 14:13:35', '2025-11-30 14:13:35'),
(165, 25, 'Chapter II', '[\"Strongly developed, insightful framework; clearly demonstrates the study\'s direction and theoretical foundation\",\"Clearly presented with visual and narrative explanation; ogically aligned with objectives\",\"Present but vague; weak linkage to study or confusing flow\",\"Absent, unclear, or inappropriate; lacks coherence and logical structure\"]', 1, 0, '2025-11-30 14:13:35', '2025-11-30 14:13:35'),
(166, 26, 'Chapter III', '[\"Strongly articulated design; logically aligned with study goals and appropriate for the project type\",\"Clearly described and generally fits the nature of the study\",\"Design is stated but lacks detail or partial relevance to the objectives\",\"No clear design or methodology; lacks direction or coherence\"]', 0, 0, '2025-11-30 14:17:36', '2025-11-30 14:17:36'),
(167, 26, 'Chapter III', '[\"Well-justified and systematically selected; shows thoughtful planning and alignment with the study\\u2019s purpose\",\"Sampling method is described with clear rationale and participant relevance\",\"Described briefly with weak rationale or fit\",\"Sampling approach is missing, unjustified, or inappropriate\"]', 1, 0, '2025-11-30 14:17:36', '2025-11-30 14:17:36'),
(168, 26, 'Chapter III', '[\"Clearly follows a structured model (e.g., Agile, SDLC) with well-explained development phases, roles, and iterations\",\"Identifies an organized process or model; explains phases of development\",\"Process is mentioned but lacks structure or coherence (e.g., unclear use of models like Agile\\/Waterfall)\",\"Development process is missing, disorganized, or inappropriate (e.g., no methodology used)\"]', 2, 0, '2025-11-30 14:17:36', '2025-11-30 14:17:36'),
(169, 26, 'Chapter III', '[\"Well-structured and technically sound system architecture; clearly shows components, flow, and integration logic\",\"Provides basic structure and understandable architecture diagram\",\"Architecture is present but unclear or lacks technical coherence\",\"Absent or contains irrelevant\\/confusing diagrams or explanations\"]', 3, 0, '2025-11-30 14:17:36', '2025-11-30 14:17:36'),
(170, 27, 'Modules and Features Implementation', '[\"All core modules are complete, integrated, and align well with project objectives\",\"Most modules are implemented and function as intended\",\"Some modules are working; lacks completeness or integration\",\"Very few features\\/modules are implemented; most are missing or do not work\"]', 0, 0, '2025-11-30 14:22:32', '2025-11-30 14:22:32'),
(171, 27, 'User Interface & Navigation', '[\"Highly intuitive, visually consistent, and user-friendly interface\",\"UI is clean and usable with minor inconsistencies\",\"Basic layout and working navigation, but lacks consistency or usability\",\"Interface is confusing or unattractive; navigation is broken or unclear\"]', 1, 0, '2025-11-30 14:22:32', '2025-11-30 14:22:32'),
(172, 27, 'Performance', '[\"Smooth, fast, and stable system; handles load, input, and errors efficiently\",\"Acceptable performance; generally stable and responsive\",\"Occasionally slow or error-prone; inconsistent handling of input\",\"Very poor performance; system is slow, unresponsive, or crashes frequently\"]', 2, 0, '2025-11-30 14:22:32', '2025-11-30 14:22:32'),
(173, 27, 'System Reliability & Error Handling', '[\"Highly robust; handles exceptions, validates input, and avoids crashes gracefully\",\"Works reliably with some input validation and error alerts\",\"Basic reliability but lacks input checks or error feedback\",\"System frequently fails; no validation or feedback\"]', 3, 0, '2025-11-30 14:22:32', '2025-11-30 14:22:32'),
(174, 27, 'Technical Complexity', '[\"Very basic; no technical challenge or creativity involved\",\"Some effort shown, but implementation is mostly standard\",\"Demonstrates thoughtful use of tools, logic, or external services\",\"Impressive technical depth or creative integration of advanced tools and logic\"]', 4, 0, '2025-11-30 14:22:32', '2025-11-30 14:22:32'),
(175, 28, 'Logical Flow and Structure', '[\"Exceptionally clear, coherent, and logically structured from start to end\",\"Generally wellorganized with logical progression between sections\",\"Inconsistent structure; some sections unclear or out of order\",\"Manuscript lacks structure; ideas are disorganized; transitions are poor\"]', 0, 0, '2025-11-30 14:28:31', '2025-11-30 14:28:31'),
(176, 28, 'Adherence to Format Guidelines', '[\"Fully compliant with all formatting standards\",\"Follows most formatting rules with minor errors\",\"Some formatting inconsistencies; limited adherence to guidelines\",\"Does not follow prescribed formatting; major issues with margins, spacing, fonts, etc.\"]', 1, 0, '2025-11-30 14:28:31', '2025-11-30 14:28:31'),
(177, 28, 'Clarity and Readability', '[\"Highly readable, well-written, free of grammar and spelling errors\",\"Clear writing with minor grammar\\/wording concerns\",\"Some sentences are difficult to understand; frequent grammar issues\",\"Writing is unclear, with poor grammar, awkward phrasing, or jargon\"]', 2, 0, '2025-11-30 14:28:31', '2025-11-30 14:28:31'),
(178, 28, 'Use of Figures, Tables, and Appendices', '[\"Visuals enhance understanding; consistently formatted and appropriately referenced in text\",\"Relevant visuals used and labeled properly\",\"Some figures\\/tables used but lack consistency or proper formatting\",\"Missing or irrelevant visuals; poorly labeled or placed\"]', 3, 0, '2025-11-30 14:28:31', '2025-11-30 14:28:31'),
(179, 29, 'Originality and Innovation', '[\"Proposes a highly original system and algorithm with unique, creative insights.\",\"Builds upon existing work with moderate innovation or new approaches.\",\"Slight improvements to existing ideas; limited originality\",\"Replicates existing solutions with no new insights or innovation.\"]', 0, 0, '2025-11-30 14:32:31', '2025-11-30 14:32:31'),
(180, 29, 'Contribution and Relevance', '[\"Clearly addresses a significant gap and contributes meaningfully to the body of knowledge.\",\"Addresses a known problem and adds reasonable value to the field.\",\"Limited relevance; contribution is unclear or minimal.\",\"Fails to address a meaningful problem or contribute to existing knowledge.\"]', 1, 0, '2025-11-30 14:32:31', '2025-11-30 14:32:31'),
(181, 29, 'Societal Impact and Inclusivity', '[\"Demonstrates strong real-world application and inclusive design for diverse users.\",\"Shows potential for societal use with some inclusivity.\",\"Limited application or accessibility considerations.\",\"Lacks practical application or excludes major user groups.\"]', 2, 0, '2025-11-30 14:32:31', '2025-11-30 14:32:31'),
(183, 20, '1. Content - System and Manuscript', '[\"40%\"]', 0, 0, '2025-11-30 14:56:39', '2025-11-30 14:56:39'),
(184, 20, '2. Organization - Manuscript', '[\"10%\"]', 1, 0, '2025-11-30 14:56:39', '2025-11-30 14:56:39'),
(185, 20, '3. Novelty and Impact - System and Manuscript', '[\"10%\"]', 2, 0, '2025-11-30 14:56:39', '2025-11-30 14:56:39'),
(191, 22, '1. The presenter is confident and well-prepared.', NULL, 0, 1, '2025-11-30 15:01:25', '2025-11-30 15:01:25'),
(192, 22, '2. The presenter successfully conveyed the concepts.', NULL, 1, 1, '2025-11-30 15:01:25', '2025-11-30 15:01:25'),
(193, 22, '3. The presenter demonstrated mastery and logical thinking in defending the proposal.', NULL, 2, 1, '2025-11-30 15:01:25', '2025-11-30 15:01:25'),
(197, 18, '1. Clarity and mastery in the presentation', NULL, 0, 1, '2025-11-30 15:03:32', '2025-11-30 15:03:32'),
(198, 18, '2. Articulate response to the inquiries', NULL, 1, 1, '2025-11-30 15:03:32', '2025-11-30 15:03:32'),
(199, 18, '3. Proper demeanor and dress code', NULL, 2, 1, '2025-11-30 15:03:32', '2025-11-30 15:03:32'),
(202, 21, '1. The presentation is completed in the allowed time.', NULL, 0, 1, '2025-11-30 15:05:16', '2025-11-30 15:05:16'),
(203, 21, '2. The visual presentation comprehensively illustrated the concepts.', NULL, 1, 1, '2025-11-30 15:05:16', '2025-11-30 15:05:16'),
(207, 31, '1. The presentation is completed in the allowed time', NULL, 0, 1, '2025-11-30 15:09:22', '2025-11-30 15:09:22'),
(208, 31, '2. The visual presentation comprehensively illustrated the concepts.', NULL, 1, 1, '2025-11-30 15:09:22', '2025-11-30 15:09:22'),
(209, 32, '1. The presenter is confident and well-prepared.', NULL, 0, 1, '2025-11-30 15:12:59', '2025-11-30 15:12:59'),
(210, 32, '2. The presenter successfully conveyed the concepts', NULL, 1, 1, '2025-11-30 15:12:59', '2025-11-30 15:12:59'),
(211, 32, '3. The presenter demonstrated mastery and logical thinking in defending the proposal.', NULL, 2, 1, '2025-11-30 15:12:59', '2025-11-30 15:12:59'),
(212, 30, '1. Content - System and Manuscript', '[\"40%\"]', 0, 0, '2025-11-30 15:13:12', '2025-11-30 15:13:12'),
(213, 30, '2. Organization - Manuscript', '[\"10%\"]', 1, 0, '2025-11-30 15:13:12', '2025-11-30 15:13:12'),
(214, 30, '3. Novelty and Impact - System and Manuscript', '[\"10%\"]', 2, 0, '2025-11-30 15:13:12', '2025-11-30 15:13:12'),
(215, 34, 'Chapter I', '[\"Thorough, wellarticulated context; strongly justifies the study with relevant support\",\"Provides clear and relevant background; rationale is present but may lack depth\",\"Provides minimal context with limited connection to the problem; lacks depth\",\"Lacks clarity, relevance, or justification; context is missing or inappropriate\"]', 0, 0, '2025-11-30 15:20:54', '2025-11-30 15:20:54'),
(216, 34, 'Chapter I', '[\"Objectives are precise, measurable, and clearly aligned with the research problem\",\"Objectives are clear and aligned with the problem statement\",\"Objectives are stated but unclear or not aligned with the problem\",\"Objectives are vague, irrelevant, or missing\"]', 1, 0, '2025-11-30 15:20:54', '2025-11-30 15:20:54'),
(217, 34, 'Chapter I', '[\"Strong justification of the study\\u2019s importance; clearly identifies impact and beneficiaries\",\"Adequately explains importance and identifies key beneficiaries\",\"Minimally explains importance; beneficiaries are unclear\",\"No clear value to stakeholders; lacks justification or impact\"]', 2, 0, '2025-11-30 15:20:54', '2025-11-30 15:20:54'),
(218, 34, 'Chapter I', '[\"Scope and limitations are specific, realistic, and critically analyzed in the context of the study\",\"Scope and limitations are clearly defined and relevant\",\"Scope and limitations are present but lack detail or clarity\",\"Scope is vague or too broad; limitations not identified or irrelevant\"]', 3, 0, '2025-11-30 15:20:54', '2025-11-30 15:20:54'),
(219, 35, 'Chapter II', '[\"Well-curated, up-todate, and highly relevant sources; shows clear synthesis and strong critical insight connecting to the study\",\"Adequate selection of relevant sources; demonstrates synthesis and some critical thinking\",\"Limited and weakly connected sources; minimal synthesis of ideas\",\"Lacks relevant sources or contains outdated\\/irrelevant literature; no synthesis or connection to study\"]', 0, 0, '2025-11-30 15:23:47', '2025-11-30 15:23:47'),
(220, 35, 'Chapter II', '[\"Strongly developed, insightful framework; clearly demonstrates the study\'s direction and theoretical foundation\",\"Clearly presented with visual and narrative explanation; logically aligned with objectives\",\"Present but vague; weak linkage to study or confusing flow\",\"Absent, unclear, or inappropriate; lacks coherence and logical structure\"]', 1, 0, '2025-11-30 15:23:47', '2025-11-30 15:23:47'),
(221, 36, 'Chapter III', '[\"Strongly articulated design; logically aligned with study goals and appropriate for the project type\",\"Clearly described and generally fits the nature of the study\",\"Design is stated but lacks detail or partial relevance to the objectives\",\"No clear design or methodology; lacks direction or coherence\"]', 0, 0, '2025-11-30 15:27:06', '2025-11-30 15:27:06'),
(222, 36, 'Chapter III', '[\"Well-justified and systematically selected; shows thoughtful planning and alignment with the study\\u2019s purpose\",\"Sampling method is described with clear rationale and participant relevance\",\"Described briefly with weak rationale or fit\",\"Sampling approach is missing, unjustified, or inappropriate\"]', 1, 0, '2025-11-30 15:27:06', '2025-11-30 15:27:06'),
(223, 36, 'Chapter III', '[\"Clearly follows a structured model (e.g., Agile, SDLC) with well-explained development phases, roles, and iterations\",\"Identifies an organized process or model; explains phases of development\",\"Process is mentioned but lacks structure or coherence (e.g., unclear use of models like Agile\\/Waterfall)\",\"Development process is missing, disorganized, or inappropriate (e.g., no methodology used)\"]', 2, 0, '2025-11-30 15:27:06', '2025-11-30 15:27:06'),
(224, 36, 'Chapter III', '[\"Well-structured and technically sound system architecture; clearly shows components, flow, and integration logic\",\"Provides basic structure and understandable architecture diagram\",\"Architecture is present but unclear or lacks technical coherence\",\"Absent or contains irrelevant\\/confusing diagrams or explanations\"]', 3, 0, '2025-11-30 15:27:06', '2025-11-30 15:27:06'),
(225, 37, 'Modules and Features Implementation', '[\"All core modules are complete, integrated, and align well with project objectives\",\"Most modules are implemented and function as intended\",\"Some modules are working; lacks completeness or integration\",\"Very few features\\/modules are implemented; most are missing or do not work\"]', 0, 0, '2025-11-30 15:31:36', '2025-11-30 15:31:36'),
(226, 37, 'User Interface & Navigation', '[\"Highly intuitive, visually consistent, and user-friendly interface\",\"UI is clean and usable with minor inconsistencies\",\"Basic layout and working navigation, but lacks consistency or usability\",\"Interface is confusing or unattractive; navigation is broken or unclear\"]', 1, 0, '2025-11-30 15:31:36', '2025-11-30 15:31:36'),
(227, 37, 'Performance', '[\"Smooth, fast, and stable system; handles load, input, and errors efficiently\",\"Acceptable performance; generally stable and responsive\",\"Occasionally slow or error-prone; inconsistent handling of input\",\"Very poor performance; system is slow, unresponsive, or crashes frequently\"]', 2, 0, '2025-11-30 15:31:36', '2025-11-30 15:31:36'),
(228, 37, 'System Reliability & Error Handling', '[\"Highly robust; handles exceptions, validates input, and avoids crashes gracefully\",\"Works reliably with some input validation and error alerts\",\"Basic reliability but lacks input checks or error feedback\",\"System frequently fails; no validation or feedback\"]', 3, 0, '2025-11-30 15:31:36', '2025-11-30 15:31:36'),
(229, 37, 'Technical Complexity', '[\"Impressive technical depth or creative integration of advanced tools and logic\",\"Demonstrates thoughtful use of tools, logic, or external services\",\"Some effort shown, but implementation is mostly standard\",\"Very basic; no technical challenge or creativity involved\"]', 4, 0, '2025-11-30 15:31:36', '2025-11-30 15:31:36'),
(230, 38, 'Logical Flow and Structure', '[\"Exceptionally clear, coherent, and logically structured from start to end\",\"Generally wellorganized with logical progression between sections\",\"Inconsistent structure; some sections unclear or out of order\",\"Manuscript lacks structure; ideas are disorganized; transitions are poor\"]', 0, 0, '2025-11-30 15:35:49', '2025-11-30 15:35:49'),
(231, 38, 'Adherence to Format Guidelines', '[\"Fully compliant with all formatting standards\",\"Follows most formatting rules with minor errors\",\"Some formatting inconsistencies; limited adherence to guidelines\",\"Does not follow prescribed formatting; major issues with margins, spacing, fonts, etc.\"]', 1, 0, '2025-11-30 15:35:49', '2025-11-30 15:35:49'),
(232, 38, 'Clarity and Readability', '[\"Highly readable, well-written, free of grammar and spelling errors\",\"Clear writing with minor grammar\\/wording concerns\",\"Some sentences are difficult to understand; frequent grammar issues\",\"Writing is unclear, with poor grammar, awkward phrasing, or jargon\"]', 2, 0, '2025-11-30 15:35:49', '2025-11-30 15:35:49'),
(233, 38, 'Use of Figures, Tables, and Appendices', '[\"Visuals enhance understanding; consistently formatted and appropriately referenced in text\",\"Relevant visuals used and labeled properly\",\"Some figures\\/tables used but lack consistency or proper formatting\",\"Missing or irrelevant visuals; poorly labeled or placed\"]', 3, 0, '2025-11-30 15:35:49', '2025-11-30 15:35:49'),
(234, 39, 'Originality and Innovation', '[\"Proposes a highly original system and algorithm with unique, creative insights\",\"Builds upon existing work with moderate innovation or new approaches\",\"Slight improvements to existing ideas; limited originality\",\"Replicates existing solutions with no new insights or innovation.\"]', 0, 0, '2025-11-30 15:38:47', '2025-11-30 15:38:47'),
(235, 39, 'Contribution and Relevance', '[\"Clearly addresses a significant gap and contributes meaningfully to the body of knowledge.\",\"Addresses a known problem and adds reasonable value to the field.\",\"Limited relevance; contribution is unclear or minimal.\",\"Fails to address a meaningful problem or contribute to existing knowledge.\"]', 1, 0, '2025-11-30 15:38:47', '2025-11-30 15:38:47'),
(236, 39, 'Societal Impact and Inclusivity', '[\"Demonstrates strong real-world application and inclusive design for diverse users.\",\"Shows potential for societal use with some inclusivity.\",\"Limited application or accessibility considerations.\",\"Lacks practical application or excludes major user groups.\"]', 2, 0, '2025-11-30 15:38:47', '2025-11-30 15:38:47');

-- --------------------------------------------------------

--
-- Table structure for table `rubric_groups`
--

DROP TABLE IF EXISTS `rubric_groups`;
CREATE TABLE IF NOT EXISTS `rubric_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubric_groups`
--

INSERT INTO `rubric_groups` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Information Technology and Computer Science', '(RE-PRESENTATION)', '2025-07-21 09:50:27', '2025-07-21 13:30:27'),
(5, 'CCS', 'Final Defense', '2025-11-30 12:50:06', '2025-11-30 14:34:17'),
(6, 'CCS', 'Proposal Re-Presentation', '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(7, 'CCS', 'Proposal Defense', '2025-11-30 15:42:50', '2025-11-30 15:42:50');

-- --------------------------------------------------------

--
-- Table structure for table `rubric_group_items`
--

DROP TABLE IF EXISTS `rubric_group_items`;
CREATE TABLE IF NOT EXISTS `rubric_group_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `rubric_id` int(11) UNSIGNED NOT NULL,
  `order_index` int(11) NOT NULL DEFAULT 0,
  `weight` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `group_id_idx` (`group_id`),
  KEY `rubric_id_idx` (`rubric_id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubric_group_items`
--

INSERT INTO `rubric_group_items` (`id`, `group_id`, `rubric_id`, `order_index`, `weight`, `created_at`, `updated_at`) VALUES
(30, 1, 1, 0, '30.00', '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(31, 1, 2, 1, '20.00', '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(32, 1, 3, 2, '20.00', '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(33, 1, 4, 3, '10.00', '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(34, 1, 5, 4, '20.00', '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(35, 1, 6, 5, NULL, '2025-07-21 13:30:27', '2025-07-21 13:30:27'),
(36, 2, 8, 0, '30.00', '2025-11-30 09:24:46', '2025-11-30 09:24:46'),
(37, 2, 9, 1, '30.00', '2025-11-30 09:24:46', '2025-11-30 09:24:46'),
(38, 2, 10, 2, '40.00', '2025-11-30 09:24:46', '2025-11-30 09:24:46'),
(39, 2, 11, 3, NULL, '2025-11-30 09:24:46', '2025-11-30 09:24:46'),
(40, 3, 12, 0, '50.00', '2025-11-30 10:05:33', '2025-11-30 10:05:33'),
(41, 3, 13, 1, '50.00', '2025-11-30 10:05:33', '2025-11-30 10:05:33'),
(42, 4, 12, 0, '50.00', '2025-11-30 12:24:32', '2025-11-30 12:24:32'),
(43, 4, 13, 1, '50.00', '2025-11-30 12:24:32', '2025-11-30 12:24:32'),
(48, 5, 16, 0, '20.00', '2025-11-30 14:34:17', '2025-11-30 14:34:17'),
(49, 5, 17, 1, '20.00', '2025-11-30 14:34:17', '2025-11-30 14:34:17'),
(50, 5, 18, 2, '20.00', '2025-11-30 14:34:17', '2025-11-30 14:34:17'),
(51, 5, 14, 3, '20.00', '2025-11-30 14:34:17', '2025-11-30 14:34:17'),
(52, 5, 15, 4, '20.00', '2025-11-30 14:34:17', '2025-11-30 14:34:17'),
(53, 5, 19, 5, NULL, '2025-11-30 14:34:17', '2025-11-30 14:34:17'),
(54, 6, 20, 0, '11.10', '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(55, 6, 21, 1, '11.10', '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(56, 6, 22, 2, '11.10', '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(57, 6, 23, 3, NULL, '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(58, 6, 24, 4, '11.10', '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(59, 6, 25, 5, '11.10', '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(60, 6, 26, 6, '11.10', '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(61, 6, 27, 7, '11.10', '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(62, 6, 28, 8, '11.10', '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(63, 6, 29, 9, '11.20', '2025-11-30 14:37:52', '2025-11-30 14:37:52'),
(64, 7, 30, 0, '11.10', '2025-11-30 15:42:50', '2025-11-30 15:42:50'),
(65, 7, 31, 1, '11.10', '2025-11-30 15:42:50', '2025-11-30 15:42:50'),
(66, 7, 32, 2, '11.10', '2025-11-30 15:42:50', '2025-11-30 15:42:50'),
(67, 7, 33, 3, NULL, '2025-11-30 15:42:50', '2025-11-30 15:42:50'),
(68, 7, 34, 4, '11.10', '2025-11-30 15:42:50', '2025-11-30 15:42:50'),
(69, 7, 35, 5, '11.10', '2025-11-30 15:42:50', '2025-11-30 15:42:50'),
(70, 7, 36, 6, '11.10', '2025-11-30 15:42:50', '2025-11-30 15:42:50'),
(71, 7, 37, 7, '11.10', '2025-11-30 15:42:50', '2025-11-30 15:42:50'),
(72, 7, 38, 8, '11.10', '2025-11-30 15:42:50', '2025-11-30 15:42:50'),
(73, 7, 39, 9, '11.20', '2025-11-30 15:42:50', '2025-11-30 15:42:50');

-- --------------------------------------------------------

--
-- Table structure for table `rubric_levels`
--

DROP TABLE IF EXISTS `rubric_levels`;
CREATE TABLE IF NOT EXISTS `rubric_levels` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rubric_id` int(11) UNSIGNED NOT NULL COMMENT 'FK to rubrics table',
  `level_index` tinyint(3) UNSIGNED NOT NULL COMMENT 'Order of the level/modifier (1, 2, 3...)',
  `name` varchar(100) NOT NULL COMMENT 'Name of the level (e.g., Excellent) or Pass Modifier',
  `description` text DEFAULT NULL COMMENT 'Description of the level or Pass Modifier',
  `points_min` int(11) DEFAULT NULL COMMENT 'Numerical Only: Min points for this level',
  `points_max` int(11) DEFAULT NULL COMMENT 'Numerical Only: Max points (same as min if not range)',
  `is_range` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Numerical Only: 1 if points_min/max define a range, 0 otherwise',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rubric_level_order` (`rubric_id`,`level_index`),
  KEY `rubric_id` (`rubric_id`)
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores Numerical quality levels or Pass/Fail modifier definitions';


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
(39, 7, 3, 'Level 3', '', 1, 2, 1, '2025-07-21 13:27:25', '2025-07-21 13:27:25'),
(44, 8, 1, '%', NULL, 5, 5, 0, '2025-11-30 09:09:24', '2025-11-30 09:09:24'),
(45, 9, 1, '%', NULL, 5, 5, 0, '2025-11-30 09:10:26', '2025-11-30 09:10:26'),
(46, 10, 1, 'Level 1', NULL, 1, 1, 0, '2025-11-30 09:17:01', '2025-11-30 09:17:01'),
(47, 11, 1, 'Pass Option 1', 'without revision', NULL, NULL, 0, '2025-11-30 09:21:35', '2025-11-30 09:21:35'),
(48, 11, 2, 'Pass Option 2', 'with minor revisions: at least 80% acceptability (refer to evaluation sheet)', NULL, NULL, 0, '2025-11-30 09:21:35', '2025-11-30 09:21:35'),
(49, 11, 3, 'Pass Option 3', 'with major revisions: at least 70% acceptability (for re-defense)', NULL, NULL, 0, '2025-11-30 09:21:35', '2025-11-30 09:21:35'),
(50, 12, 1, '1', NULL, 1, 1, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(51, 12, 2, '2', NULL, 2, 2, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(52, 12, 3, '3', NULL, 3, 3, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(53, 12, 4, '4', NULL, 4, 4, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(54, 12, 5, '5', NULL, 5, 5, 0, '2025-11-30 09:54:15', '2025-11-30 09:54:15'),
(60, 13, 1, '1', NULL, 1, 1, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(61, 13, 2, '2', NULL, 2, 2, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(62, 13, 3, '3', NULL, 3, 3, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(63, 13, 4, '4', NULL, 4, 4, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(64, 13, 5, '5', NULL, 5, 5, 0, '2025-11-30 10:00:40', '2025-11-30 10:00:40'),
(80, 15, 1, '5', NULL, 5, 5, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(81, 15, 2, '4', NULL, 4, 4, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(82, 15, 3, '3', NULL, 3, 3, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(83, 15, 4, '2', NULL, 2, 2, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(84, 15, 5, '1', NULL, 1, 1, 0, '2025-11-30 12:25:56', '2025-11-30 12:25:56'),
(85, 14, 1, '5', NULL, 5, 5, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(86, 14, 2, '4', NULL, 4, 4, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(87, 14, 3, '3', NULL, 3, 3, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(88, 14, 4, '2', NULL, 2, 2, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(89, 14, 5, '1', NULL, 1, 1, 0, '2025-11-30 12:26:09', '2025-11-30 12:26:09'),
(92, 16, 1, '%', NULL, 1, 5, 1, '2025-11-30 12:34:29', '2025-11-30 12:34:29'),
(93, 17, 1, '%', NULL, 1, 5, 1, '2025-11-30 12:36:14', '2025-11-30 12:36:14'),
(98, 19, 1, 'Pass Option 1', 'without revision', NULL, NULL, 0, '2025-11-30 12:48:15', '2025-11-30 12:48:15'),
(99, 19, 2, 'Pass Option 2', 'with minor revisions: at least 80% acceptability (refer to evaluation sheet)', NULL, NULL, 0, '2025-11-30 12:48:15', '2025-11-30 12:48:15'),
(100, 19, 3, 'Pass Option 3', 'with major revisions: at least 70% acceptability (for re-defense)', NULL, NULL, 0, '2025-11-30 12:48:15', '2025-11-30 12:48:15'),
(106, 23, 1, 'Pass Option 1', '(at least 70% acceptability)', NULL, NULL, 0, '2025-11-30 13:59:08', '2025-11-30 13:59:08'),
(107, 24, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 14:07:10', '2025-11-30 14:07:10'),
(108, 24, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 14:07:10', '2025-11-30 14:07:10'),
(109, 24, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 14:07:10', '2025-11-30 14:07:10'),
(110, 24, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 14:07:10', '2025-11-30 14:07:10'),
(111, 25, 1, 'Highly Accpetable', NULL, 4, 4, 0, '2025-11-30 14:13:35', '2025-11-30 14:13:35'),
(112, 25, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 14:13:35', '2025-11-30 14:13:35'),
(113, 25, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 14:13:35', '2025-11-30 14:13:35'),
(114, 25, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 14:13:35', '2025-11-30 14:13:35'),
(115, 26, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 14:17:36', '2025-11-30 14:17:36'),
(116, 26, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 14:17:36', '2025-11-30 14:17:36'),
(117, 26, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 14:17:36', '2025-11-30 14:17:36'),
(118, 26, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 14:17:36', '2025-11-30 14:17:36'),
(119, 27, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 14:22:32', '2025-11-30 14:22:32'),
(120, 27, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 14:22:32', '2025-11-30 14:22:32'),
(121, 27, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 14:22:32', '2025-11-30 14:22:32'),
(122, 27, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 14:22:32', '2025-11-30 14:22:32'),
(123, 28, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 14:28:31', '2025-11-30 14:28:31'),
(124, 28, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 14:28:31', '2025-11-30 14:28:31'),
(125, 28, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 14:28:31', '2025-11-30 14:28:31'),
(126, 28, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 14:28:31', '2025-11-30 14:28:31'),
(127, 29, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 14:32:31', '2025-11-30 14:32:31'),
(128, 29, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 14:32:31', '2025-11-30 14:32:31'),
(129, 29, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 14:32:31', '2025-11-30 14:32:31'),
(130, 29, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 14:32:31', '2025-11-30 14:32:31'),
(132, 20, 1, '%', NULL, 1, 40, 1, '2025-11-30 14:56:39', '2025-11-30 14:56:39'),
(135, 22, 1, 'Level 1', NULL, 1, 5, 1, '2025-11-30 15:01:25', '2025-11-30 15:01:25'),
(137, 18, 1, 'Level 1', NULL, 1, 5, 1, '2025-11-30 15:03:32', '2025-11-30 15:03:32'),
(139, 21, 1, 'Level 1', NULL, 1, 5, 1, '2025-11-30 15:05:16', '2025-11-30 15:05:16'),
(141, 31, 1, '%', NULL, 1, 5, 1, '2025-11-30 15:09:22', '2025-11-30 15:09:22'),
(142, 33, 1, 'Pass Option 1', 'without revision', NULL, NULL, 0, '2025-11-30 15:12:43', '2025-11-30 15:12:43'),
(143, 33, 2, 'Pass Option 2', 'with minor revisions: at least 80% acceptability (refer to evaluation\nsheet)', NULL, NULL, 0, '2025-11-30 15:12:43', '2025-11-30 15:12:43'),
(144, 33, 3, 'Pass Option 3', 'with major revisions: at least 70% acceptability (for re-defense)', NULL, NULL, 0, '2025-11-30 15:12:43', '2025-11-30 15:12:43'),
(145, 32, 1, 'Level 1', NULL, 1, 1, 0, '2025-11-30 15:12:59', '2025-11-30 15:12:59'),
(146, 30, 1, '%', NULL, 1, 40, 1, '2025-11-30 15:13:12', '2025-11-30 15:13:12'),
(147, 34, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 15:20:54', '2025-11-30 15:20:54'),
(148, 34, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 15:20:54', '2025-11-30 15:20:54'),
(149, 34, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 15:20:54', '2025-11-30 15:20:54'),
(150, 34, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 15:20:54', '2025-11-30 15:20:54'),
(151, 35, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 15:23:47', '2025-11-30 15:23:47'),
(152, 35, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 15:23:47', '2025-11-30 15:23:47'),
(153, 35, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 15:23:47', '2025-11-30 15:23:47'),
(154, 35, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 15:23:47', '2025-11-30 15:23:47'),
(155, 36, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 15:27:06', '2025-11-30 15:27:06'),
(156, 36, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 15:27:06', '2025-11-30 15:27:06'),
(157, 36, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 15:27:06', '2025-11-30 15:27:06'),
(158, 36, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 15:27:06', '2025-11-30 15:27:06'),
(159, 37, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 15:31:36', '2025-11-30 15:31:36'),
(160, 37, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 15:31:36', '2025-11-30 15:31:36'),
(161, 37, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 15:31:36', '2025-11-30 15:31:36'),
(162, 37, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 15:31:36', '2025-11-30 15:31:36'),
(163, 38, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 15:35:49', '2025-11-30 15:35:49'),
(164, 38, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 15:35:49', '2025-11-30 15:35:49'),
(165, 38, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 15:35:49', '2025-11-30 15:35:49'),
(166, 38, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 15:35:49', '2025-11-30 15:35:49'),
(167, 39, 1, 'Highly Acceptable', NULL, 4, 4, 0, '2025-11-30 15:38:47', '2025-11-30 15:38:47'),
(168, 39, 2, 'Acceptable', NULL, 3, 3, 0, '2025-11-30 15:38:47', '2025-11-30 15:38:47'),
(169, 39, 3, 'Fairly Acceptable', NULL, 2, 2, 0, '2025-11-30 15:38:47', '2025-11-30 15:38:47'),
(170, 39, 4, 'Unacceptable', NULL, 1, 1, 0, '2025-11-30 15:38:47', '2025-11-30 15:38:47');

-- --------------------------------------------------------

--
-- Table structure for table `rubric_programs`
--

DROP TABLE IF EXISTS `rubric_programs`;
CREATE TABLE IF NOT EXISTS `rubric_programs` (
  `rubric_id` int(10) UNSIGNED NOT NULL,
  `program_name` varchar(255) NOT NULL,
  PRIMARY KEY (`rubric_id`,`program_name`),
  KEY `fk_rubric_programs_rubric_id_idx` (`rubric_id`)
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
(39, 'Bachelor of Science in Information Technology - Web and Mobile Technology');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_progress`
--

DROP TABLE IF EXISTS `schedule_progress`;
CREATE TABLE IF NOT EXISTS `schedule_progress` (
  `id` varchar(50) NOT NULL,
  `status` enum('running','completed','error') DEFAULT 'running',
  `message` text DEFAULT NULL,
  `percentage` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_progress`
--

INSERT INTO `schedule_progress` (`id`, `status`, `message`, `percentage`, `created_at`, `updated_at`) VALUES
('sched_69207adce3bcc0.47366559', 'completed', 'Schedule generated and saved successfully!', 100, '2025-11-21 14:44:44', '2025-11-21 14:46:29'),
('sched_692457ab43a2e4.61443310', 'error', 'Confirmation required for overwriting existing schedules', NULL, '2025-11-24 13:03:39', '2025-11-24 13:03:39'),
('sched_692457ad890bc3.45635683', 'completed', 'Schedule generated and saved successfully!', 100, '2025-11-24 13:03:41', '2025-11-24 13:03:44'),
('sched_692457c1a4dce3.43172187', 'completed', 'Schedule generated and saved successfully!', 100, '2025-11-24 13:04:01', '2025-11-24 13:04:05'),
('sched_69254ec6145bb1.83940223', 'error', 'Confirmation required for overwriting existing schedules', NULL, '2025-11-25 06:37:58', '2025-11-25 06:37:58'),
('sched_69254ed2eda039.29505016', 'completed', 'Schedule generated and saved successfully!', 100, '2025-11-25 06:38:10', '2025-11-25 06:38:12'),
('sched_69254f2eb42185.87368600', 'completed', 'Schedule generated and saved successfully!', 100, '2025-11-25 06:39:42', '2025-11-25 06:39:44'),
('sched_69254f49184f66.58945696', 'error', 'Confirmation required for overwriting existing schedules', NULL, '2025-11-25 06:40:09', '2025-11-25 06:40:09'),
('sched_6925b070d90928.20780287', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:34:40', '2025-11-25 13:34:40'),
('sched_6925b071ee7e84.95962901', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:34:41', '2025-11-25 13:34:41'),
('sched_6925b072794949.04319095', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:34:42', '2025-11-25 13:34:42'),
('sched_6925b0b437cca7.50344906', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:35:48', '2025-11-25 13:35:48'),
('sched_6925b2965a8b47.48777392', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:43:50', '2025-11-25 13:43:50'),
('sched_6925b2977333e5.00355495', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:43:51', '2025-11-25 13:43:51'),
('sched_6925b297bb4cc2.35362083', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:43:51', '2025-11-25 13:43:51'),
('sched_6925b4557b39f1.45977476', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:51:17', '2025-11-25 13:51:17'),
('sched_6925b49d4dee96.64961301', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:52:29', '2025-11-25 13:52:29'),
('sched_6925b5aa794762.71265729', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:56:58', '2025-11-25 13:56:58'),
('sched_6925b5ab2d3d20.29632614', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:56:59', '2025-11-25 13:56:59'),
('sched_6925b5ab9c45d1.31548813', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:56:59', '2025-11-25 13:56:59'),
('sched_6925b5abea4517.23462582', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:56:59', '2025-11-25 13:56:59'),
('sched_6925b5ac20fb01.01776827', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:57:00', '2025-11-25 13:57:00'),
('sched_6925b5ac4e1235.56163892', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:57:00', '2025-11-25 13:57:00'),
('sched_6925b5cb1cffb7.16438675', 'error', 'You do not have access to one or more selected sections', NULL, '2025-11-25 13:57:31', '2025-11-25 13:57:31'),
('sched_6925b63d945ab4.21519176', 'completed', 'Schedule generated and saved successfully!', 100, '2025-11-25 13:59:25', '2025-11-25 13:59:27'),
('sched_6925edf0d72641.75427101', 'running', 'Processing generation 61 of 100', 65, '2025-11-25 17:57:04', '2025-11-25 17:58:54'),
('sched_6925f31897b431.41057257', 'running', 'Saving schedule to database...', 90, '2025-11-25 18:19:04', '2025-11-25 18:20:01'),
('sched_6925f48180d888.73047154', 'completed', 'Schedule generated and saved successfully!', 100, '2025-11-25 18:25:05', '2025-11-25 18:25:31'),
('sched_6926d7edd52d20.76687705', 'error', 'Confirmation required for overwriting existing schedules', NULL, '2025-11-26 10:35:25', '2025-11-26 10:35:25'),
('sched_6926d7ef9f0ac4.74781747', 'completed', 'Schedule generated and saved successfully!', 100, '2025-11-26 10:35:27', '2025-11-26 10:35:49');

-- --------------------------------------------------------

--
-- Table structure for table `section_professors`
--

DROP TABLE IF EXISTS `section_professors`;
CREATE TABLE IF NOT EXISTS `section_professors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(255) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'active',
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_section_professor` (`section`,`professor_id`),
  KEY `idx_professor_id` (`professor_id`),
  KEY `idx_section` (`section`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_professors`
--

INSERT INTO `section_professors` (`id`, `section`, `professor_id`, `status`, `assigned_by`, `assigned_at`) VALUES
(8, 'IT401', 285, 'active', 273, '2025-11-25 06:18:01'),
(11, 'IT402', 278, 'active', 273, '2025-11-25 16:15:38'),
(12, 'IT403', 284, 'active', 273, '2025-11-25 16:15:43'),
(13, 'CS401', 418, 'active', 0, '2025-11-29 08:36:43');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
CREATE TABLE IF NOT EXISTS `teams` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `program` varchar(255) NOT NULL,
  `area_of_expertise` varchar(255) DEFAULT NULL,
  `title_proposal` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark team as title proposal - professor role will be automatic',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `created_at`, `program`, `area_of_expertise`, `title_proposal`) VALUES
(21, 'team 1 (IT401)', '2025-11-25 16:17:54', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Mobile Dev', 1),
(22, 'team 2 (IT401)', '2025-11-25 16:19:49', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 1),
(23, 'team 3 (IT401)', '2025-11-25 16:21:00', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 1),
(24, 'team 4 (IT401)', '2025-11-25 16:21:52', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Hybrid Dev', 1),
(25, 'team 5 (IT401)', '2025-11-25 16:23:34', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software Engineering', 1),
(26, 'team 6 (IT401)', '2025-11-25 16:24:27', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Hybrid Dev', 1),
(27, 'team 8 (IT401)', '2025-11-25 16:25:45', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 1),
(28, 'team 9 (IT401)', '2025-11-25 16:27:52', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Hybrid Dev', 1),
(29, 'team 1 (IT402)', '2025-11-25 16:32:09', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software Engineering', 1),
(30, 'team 2 (IT402)', '2025-11-25 16:33:29', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 1),
(32, 'team 3  (IT402)', '2025-11-25 17:03:36', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 1),
(33, 'team 4 (IT402)', '2025-11-25 17:04:40', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 1),
(34, 'team 5 (IT402)', '2025-11-25 17:06:29', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 1),
(35, 'team 6 (IT402)', '2025-11-25 17:08:54', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Hybrid Dev', 1),
(36, 'team 7 (IT402)', '2025-11-25 17:10:04', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 1),
(37, 'team 8 (IT402)', '2025-11-25 17:11:12', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software Engineering', 1),
(38, 'team 1 (IT403)', '2025-11-25 17:13:11', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Mobile Dev', 1),
(39, 'team 2 (IT403)', '2025-11-25 17:14:09', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 1),
(40, 'team 3 (IT403)', '2025-11-25 17:15:22', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Hybrid Dev', 1),
(42, 'team 4 (IT403)', '2025-11-25 17:21:12', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Mobile Dev', 1),
(43, 'team 5 (IT403)', '2025-11-25 17:22:00', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Mobile Dev', 1),
(44, 'team 6 (IT403)', '2025-11-25 17:23:15', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software Engineering', 1),
(45, 'team 7 (IT403)', '2025-11-25 17:24:30', 'Unspecified', 'Web Dev', 1),
(46, 'team 7 (IT403)', '2025-11-25 17:26:23', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Software Engineering', 1),
(47, 'team 8 (IT403)', '2025-11-25 17:27:37', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Hybrid Dev', 1),
(48, 'team 9 (IT403)', '2025-11-25 17:35:04', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Mobile Dev', 1),
(49, 'HerbaScan', '2025-11-29 08:36:18', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev', 0),
(50, 'NaviCav', '2025-11-29 08:43:37', 'Bachelor of Science in Computer Science - Software Engineering', 'Web - Mobile Dev', 0),
(51, 'RECOLOR', '2025-11-29 08:50:02', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev', 0),
(52, 'DENGUEGUARD', '2025-11-30 05:48:02', 'Bachelor of Science in Computer Science - Software Engineering', 'Web Dev', 0),
(53, 'Postra', '2025-11-30 06:04:49', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev', 0),
(54, 'KNOWWHERE', '2025-11-30 06:13:08', 'Bachelor of Science in Computer Science - Software Engineering', 'Web Dev', 0),
(55, 'Solari', '2025-11-30 06:20:03', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev', 0),
(56, 'SalinDugo', '2025-11-30 06:24:31', 'Bachelor of Science in Computer Science - Software Engineering', 'Web Dev', 0),
(58, 'GAIA', '2025-11-30 08:06:36', 'Bachelor of Science in Computer Science - Software Engineering', 'Web Dev', 0),
(59, 'PrivacyGuard', '2025-12-01 15:23:07', 'Bachelor of Science in Computer Science - Software Engineering', 'Web Dev', 0);

-- --------------------------------------------------------

--
-- Table structure for table `team_defense_status`
--

DROP TABLE IF EXISTS `team_defense_status`;
CREATE TABLE IF NOT EXISTS `team_defense_status` (
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

DROP TABLE IF EXISTS `team_defense_status_table`;
CREATE TABLE IF NOT EXISTS `team_defense_status_table` (
  `team_id` int(11) UNSIGNED NOT NULL,
  `team_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `current_defense_type` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approved_titles` bigint(21) NOT NULL,
  `completed_evaluations` bigint(21) NOT NULL,
  `override_defense_type` enum('title_proposal','title_defense','final_defense','re-defense') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `override_active` tinyint(1) DEFAULT NULL COMMENT 'Whether this override is currently active'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_defense_status_table`
--

INSERT INTO `team_defense_status_table` (`team_id`, `team_name`, `current_defense_type`, `approved_titles`, `completed_evaluations`, `override_defense_type`, `override_active`) VALUES
(19, 'aa', 'title_proposal', 0, 0, NULL, NULL),
(21, 'team 1 (IT401)', 'title_proposal', 0, 0, NULL, NULL),
(22, 'team 2 (IT401)', 'title_proposal', 0, 0, NULL, NULL),
(23, 'team 3 (IT401)', 'title_proposal', 0, 0, NULL, NULL),
(24, 'team 4 (IT401)', 'title_proposal', 0, 0, NULL, NULL),
(25, 'team 5 (IT401)', 'title_proposal', 0, 0, NULL, NULL),
(26, 'team 6 (IT401)', 'title_proposal', 0, 0, NULL, NULL),
(27, 'team 8 (IT401)', 'title_proposal', 0, 0, NULL, NULL),
(28, 'team 9 (IT401)', 'title_proposal', 0, 0, NULL, NULL),
(29, 'team 1 (IT402)', 'title_proposal', 0, 0, NULL, NULL),
(30, 'team 2 (IT402)', 'title_proposal', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

DROP TABLE IF EXISTS `team_members`;
CREATE TABLE IF NOT EXISTS `team_members` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` int(11) UNSIGNED DEFAULT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=280 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(39, 9, 282, 'adviser'),
(40, 10, 305, 'leader'),
(41, 10, 267, 'member'),
(42, 10, 285, 'adviser'),
(44, 11, 303, 'leader'),
(45, 11, 304, 'member'),
(46, 11, 306, 'member'),
(47, 11, 285, 'adviser'),
(48, 12, 0, 'adviser'),
(49, 12, 268, 'leader'),
(50, 13, 0, 'adviser'),
(51, 14, 0, 'adviser'),
(52, 15, 0, 'adviser'),
(53, 16, 0, 'adviser'),
(54, 17, 278, 'adviser'),
(55, 18, 285, 'adviser'),
(56, 20, 285, 'adviser'),
(57, 20, 306, 'leader'),
(58, 20, 295, 'member'),
(59, 21, 285, 'adviser'),
(60, 21, 292, 'leader'),
(61, 21, 287, 'member'),
(62, 21, 300, 'member'),
(63, 21, 306, 'member'),
(64, 21, 333, 'member'),
(65, 22, 285, 'adviser'),
(66, 22, 323, 'leader'),
(67, 22, 330, 'member'),
(68, 22, 291, 'member'),
(69, 22, 317, 'member'),
(70, 22, 331, 'member'),
(71, 23, 285, 'adviser'),
(72, 23, 325, 'leader'),
(73, 23, 311, 'member'),
(74, 23, 320, 'member'),
(75, 23, 304, 'member'),
(76, 23, 295, 'member'),
(77, 24, 285, 'adviser'),
(78, 24, 288, 'leader'),
(79, 24, 312, 'member'),
(80, 24, 308, 'member'),
(81, 24, 315, 'member'),
(82, 24, 313, 'member'),
(83, 25, 285, 'adviser'),
(84, 25, 267, 'leader'),
(85, 25, 268, 'member'),
(86, 25, 296, 'member'),
(87, 25, 299, 'member'),
(88, 25, 303, 'member'),
(89, 26, 285, 'adviser'),
(90, 26, 309, 'leader'),
(91, 26, 321, 'member'),
(92, 26, 329, 'member'),
(93, 26, 314, 'member'),
(94, 26, 332, 'member'),
(95, 27, 285, 'adviser'),
(96, 27, 316, 'leader'),
(97, 27, 324, 'member'),
(98, 27, 318, 'member'),
(99, 27, 310, 'member'),
(100, 27, 322, 'member'),
(101, 28, 285, 'adviser'),
(102, 28, 327, 'leader'),
(103, 28, 319, 'member'),
(104, 28, 294, 'member'),
(105, 28, 326, 'member'),
(106, 28, 328, 'member'),
(107, 29, 278, 'adviser'),
(108, 29, 345, 'leader'),
(109, 29, 353, 'member'),
(110, 29, 348, 'member'),
(111, 29, 339, 'member'),
(112, 29, 338, 'member'),
(113, 30, 278, 'adviser'),
(114, 30, 347, 'leader'),
(115, 30, 368, 'member'),
(116, 30, 369, 'member'),
(117, 30, 350, 'member'),
(118, 30, 355, 'member'),
(119, 31, 278, 'adviser'),
(120, 31, 346, 'leader'),
(121, 31, 335, 'member'),
(122, 31, 364, 'member'),
(123, 31, 336, 'member'),
(124, 31, 342, 'member'),
(125, 32, 278, 'adviser'),
(126, 32, 346, 'leader'),
(127, 32, 335, 'member'),
(128, 32, 364, 'member'),
(129, 32, 336, 'member'),
(130, 32, 342, 'member'),
(131, 33, 278, 'adviser'),
(132, 33, 361, 'leader'),
(133, 33, 360, 'member'),
(134, 33, 356, 'member'),
(135, 33, 337, 'member'),
(136, 33, 372, 'member'),
(137, 34, 278, 'adviser'),
(138, 34, 341, 'leader'),
(139, 34, 367, 'member'),
(140, 34, 340, 'member'),
(141, 34, 373, 'member'),
(142, 34, 352, 'member'),
(143, 35, 278, 'adviser'),
(144, 35, 363, 'leader'),
(145, 35, 354, 'member'),
(146, 35, 343, 'member'),
(147, 35, 351, 'member'),
(148, 35, 371, 'member'),
(149, 36, 278, 'adviser'),
(150, 36, 358, 'leader'),
(151, 36, 349, 'member'),
(152, 36, 334, 'member'),
(153, 36, 365, 'member'),
(154, 36, 366, 'member'),
(155, 37, 278, 'adviser'),
(156, 37, 370, 'leader'),
(157, 37, 362, 'member'),
(158, 37, 357, 'member'),
(159, 37, 344, 'member'),
(160, 37, 359, 'member'),
(161, 38, 280, 'adviser'),
(162, 38, 394, 'leader'),
(163, 38, 400, 'member'),
(164, 38, 376, 'member'),
(165, 38, 395, 'member'),
(166, 38, 375, 'member'),
(167, 39, 280, 'adviser'),
(168, 39, 392, 'leader'),
(169, 39, 413, 'member'),
(170, 39, 378, 'member'),
(171, 39, 379, 'member'),
(172, 39, 412, 'member'),
(173, 40, 280, 'adviser'),
(174, 40, 405, 'leader'),
(175, 40, 396, 'member'),
(176, 40, 407, 'member'),
(177, 40, 380, 'member'),
(178, 40, 410, 'member'),
(179, 41, 280, 'adviser'),
(180, 41, 391, 'leader'),
(181, 41, 297, 'member'),
(182, 41, 374, 'member'),
(183, 41, 383, 'member'),
(184, 41, 382, 'member'),
(186, 42, 280, 'adviser'),
(187, 42, 411, 'leader'),
(188, 42, 377, 'member'),
(189, 42, 409, 'member'),
(190, 42, 387, 'member'),
(191, 42, 301, 'member'),
(192, 43, 280, 'adviser'),
(193, 43, 406, 'leader'),
(194, 43, 408, 'member'),
(195, 43, 381, 'member'),
(196, 43, 384, 'member'),
(197, 43, 289, 'member'),
(198, 44, 280, 'adviser'),
(199, 44, 298, 'leader'),
(200, 44, 386, 'member'),
(201, 44, 404, 'member'),
(202, 44, 389, 'member'),
(203, 44, 307, 'member'),
(204, 45, 280, 'adviser'),
(205, 45, 293, 'leader'),
(206, 45, 390, 'member'),
(207, 45, 399, 'member'),
(208, 45, 385, 'member'),
(209, 45, 401, 'member'),
(210, 46, 280, 'adviser'),
(211, 46, 305, 'leader'),
(212, 46, 302, 'member'),
(213, 46, 398, 'member'),
(214, 46, 393, 'member'),
(215, 46, 397, 'member'),
(216, 47, 280, 'adviser'),
(217, 47, 388, 'leader'),
(218, 47, 403, 'member'),
(219, 47, 290, 'member'),
(220, 47, 402, 'member'),
(221, 47, 391, 'member'),
(222, 48, 280, 'adviser'),
(223, 48, 297, 'leader'),
(224, 48, 374, 'member'),
(225, 48, 383, 'member'),
(226, 48, 382, 'member'),
(227, 49, 414, 'leader'),
(228, 49, 415, 'member'),
(229, 49, 416, 'member'),
(230, 49, 417, 'member'),
(231, 50, 419, 'leader'),
(232, 50, 420, 'member'),
(233, 50, 421, 'member'),
(234, 51, 423, 'leader'),
(235, 51, 424, 'member'),
(236, 51, 425, 'member'),
(237, 51, 426, 'member'),
(238, 51, 427, 'adviser'),
(239, 50, 422, 'adviser'),
(240, 49, 418, 'adviser'),
(241, 52, 418, 'adviser'),
(242, 52, 432, 'leader'),
(243, 52, 433, 'member'),
(244, 52, 434, 'member'),
(245, 52, 435, 'member'),
(246, 53, 428, 'adviser'),
(247, 53, 443, 'leader'),
(248, 53, 444, 'member'),
(249, 53, 445, 'member'),
(250, 53, 446, 'member'),
(251, 54, 429, 'adviser'),
(252, 54, 447, 'leader'),
(253, 54, 448, 'member'),
(254, 54, 449, 'member'),
(255, 54, 450, 'member'),
(256, 55, 430, 'adviser'),
(257, 55, 452, 'leader'),
(258, 55, 453, 'member'),
(259, 55, 454, 'member'),
(260, 55, 455, 'member'),
(261, 56, 431, 'adviser'),
(262, 56, 456, 'leader'),
(263, 56, 457, 'member'),
(264, 56, 458, 'member'),
(265, 56, 459, 'member'),
(266, 57, 437, 'leader'),
(267, 57, 438, 'member'),
(268, 57, 439, 'member'),
(269, 57, 440, 'member'),
(270, 58, 422, 'adviser'),
(271, 58, 437, 'leader'),
(272, 58, 438, 'member'),
(273, 58, 439, 'member'),
(274, 58, 440, 'member'),
(275, 59, 427, 'adviser'),
(276, 59, 461, 'leader'),
(277, 59, 462, 'member'),
(278, 59, 463, 'member'),
(279, 59, 464, 'member');

-- --------------------------------------------------------

--
-- Table structure for table `team_panelists`
--

DROP TABLE IF EXISTS `team_panelists`;
CREATE TABLE IF NOT EXISTS `team_panelists` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` int(11) UNSIGNED NOT NULL,
  `defense_type` enum('title_proposal','title_defense','final_defense','re-defense') NOT NULL,
  `panelist_id` int(11) UNSIGNED NOT NULL,
  `panelist_position` int(1) DEFAULT 1 COMMENT 'Position: 1=primary, 2=secondary, 3=tertiary',
  `locked` tinyint(1) DEFAULT 0 COMMENT 'Whether this assignment is locked and cannot be changed',
  `admin_override` tinyint(1) DEFAULT 0 COMMENT 'Whether this assignment was manually set by admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'User ID who created/locked this assignment',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_defense_panelist` (`team_id`,`defense_type`,`panelist_id`),
  KEY `idx_team_defense` (`team_id`,`defense_type`),
  KEY `idx_panelist` (`panelist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks persistent panelist assignments across defense stages';

-- --------------------------------------------------------

--
-- Table structure for table `team_requirements`
--

DROP TABLE IF EXISTS `team_requirements`;
CREATE TABLE IF NOT EXISTS `team_requirements` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` int(11) UNSIGNED DEFAULT NULL,
  `requirement_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('pending','submitted','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `feedback_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `requirement_id` (`requirement_id`),
  KEY `team_id` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `team_requirement_files`;
CREATE TABLE IF NOT EXISTS `team_requirement_files` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_team_requirement` (`team_id`,`requirement_id`),
  KEY `idx_team` (`team_id`),
  KEY `idx_requirement` (`requirement_id`),
  KEY `idx_submission_number` (`submission_number`),
  KEY `submitted_by` (`submitted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Individual file submissions for requirements with multi-submission support';

--
-- Dumping data for table `team_requirement_files`
--

INSERT INTO `team_requirement_files` (`id`, `team_id`, `requirement_id`, `file_name`, `original_file_name`, `file_path`, `file_size`, `submission_number`, `status`, `feedback`, `feedback_file`, `submitted_by`, `submitted_at`, `updated_at`, `deleted_at`) VALUES
(6, 4, 46, '4_46_1763752944_Document1.pdf', 'Document1.pdf', '/opt/lampp/htdocs/home/includes/../../assets/uploads/submission/4_46_1763752944_Document1.pdf', 4548364, 1, 'submitted', NULL, NULL, 287, '2025-11-21 19:22:24', '2025-11-21 19:22:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `thesis_topics`
--

DROP TABLE IF EXISTS `thesis_topics`;
CREATE TABLE IF NOT EXISTS `thesis_topics` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thesis_topics`
--

INSERT INTO `thesis_topics` (`id`, `topic`, `description`, `category`, `created_at`) VALUES
(1, 'a', ' a', 'a', '2025-07-21 13:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

DROP TABLE IF EXISTS `uploaded_files`;
CREATE TABLE IF NOT EXISTS `uploaded_files` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(512) NOT NULL,
  `filesize` int(11) NOT NULL,
  `filetype` varchar(50) NOT NULL,
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `college_name` varchar(255) DEFAULT NULL,
  `program_id` int(11) DEFAULT NULL,
  `team_id` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_college` (`college_name`),
  KEY `idx_program` (`program_id`),
  KEY `idx_team` (`team_id`),
  KEY `uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `section` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `id` (`id`,`username`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=465 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `usertype`, `username`, `program`, `area_of_expertise`, `is_parttime`, `email`, `password`, `first_name`, `last_name`, `gender`, `headline`, `bio`, `profile_image`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`, `is_program_chair`, `year`, `section`) VALUES
(0, 0, 'Admin', 'Master in Business Administration', '', 0, 'ton.agustin09@gmail.com', '$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a', 'Winston', 'Agustin', 'm', 'SUPER ADMIN', '', '67fccf5d724c92.92568803.png', '2024-10-05 05:55:38', '2024-10-05 05:55:38', '2025-12-01 15:08:01', '0000-00-00 00:00:00', '2025-12-01 15:08:01', 0, NULL, NULL),
(267, 1, '2022-2-00999', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student1@lpunetwork.edu.ph', '$2y$10$j13zgjmiWnaN3Vw5HjKjm.iqZoBH8fuHGx1MxDBZqWUsChi9koKSW', 'Jose', 'Manalo', NULL, 'a', 'a', 'profile_690c9a896fffe9.39118562.gif', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-26 14:36:25', NULL, '2025-11-26 14:36:25', NULL, 4, 'IT401'),
(268, 1, '2022-2-01000', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student2@lpunetwork.edu.ph', '$2y$10$Ggm2Jo3kYZpazx29LW/Fdea52tRW3cgRCrY3AV2j6nDThbUmqLSIe', 'Jose', 'Marie', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-25 11:40:44', NULL, '2025-07-22 19:48:49', NULL, 4, 'IT401'),
(269, 2, 'CCS-IT-01', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 0, 'teacher1@lpu.edu.ph', '$2y$10$dDLdwhy2MzpJKXfp98CeE.TV3ChOHpHIvTWZy1Ffkc7xsJhj0o0hK', 'Adviser', 'One', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-12-01 05:49:35', NULL, '2025-12-01 05:49:35', NULL, NULL, NULL),
(270, 2, 'CCS-IT-02', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 0, 'teacher2@lpu.edu.ph', '$2y$10$0ZKGSjL2n/TDJJjWDlNQ4euoT/Ej7sqjjifsd7fTP7IQpgWGBNvR2', 'Teacher', 'Two', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-21 18:20:40', NULL, '2025-11-21 18:20:40', NULL, NULL, NULL),
(271, 2, 'CCS-IT-03', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Web Dev', 0, 'teacher3@lpu.edu.ph', '$2y$10$7gglTWLQSErKoILKfiCj3uC6GoMs28PyMwcnKYyI1JYq.gSGwNnCm', 'Teacher', 'Three', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-21 00:12:11', NULL, '2025-11-20 17:53:34', NULL, NULL, NULL),
(272, 2, 'CCS-CS-01', 'Bachelor of Science in Computer Science', 'Web Dev', 0, 'teacher4@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Teacher', 'Four', NULL, NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-24 23:44:34', NULL, '2025-11-24 23:44:34', NULL, NULL, NULL),
(273, 0, 'alyssa.pocaan', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'alyssa.pocaan@lpu.edu.ph', '$2y$10$s.h4./g96wR0jfV1L3qbqOkiaQY8uu0dTaFVJgZoLeKIlR1PF7.qS', 'Alyssa Paola', 'Pocaan', 'f', '', '', '_defaultUser.png', '2025-07-21 08:25:32', '2025-07-21 08:25:32', '2025-11-30 11:45:36', NULL, '2025-11-30 11:45:36', 0, NULL, NULL),
(277, 2, 'CCS-IT-04', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Systems Dev', 0, 'marc.santiago@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Marc', 'Santiago', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:12:11', NULL, '2025-11-20 22:48:52', NULL, NULL, NULL),
(278, 2, 'CCS-IT-05', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Cybersecurity', 0, 'louise.torres@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Louise', 'Torres', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-25 18:17:18', NULL, '2025-11-25 18:17:18', NULL, NULL, NULL),
(279, 2, 'CCS-IT-06', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Networking', 1, 'jared.cruz@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Jared', 'Cruz', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-25 04:06:49', NULL, '2025-11-25 04:06:49', NULL, NULL, NULL),
(280, 2, 'CCS-IT-07', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'UI/UX', 0, 'kimberly.reyes@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Kimberly', 'Reyes', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-25 18:01:12', NULL, '2025-11-25 18:01:12', NULL, NULL, NULL),
(281, 2, 'CCS-IT-08', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Database Systems', 0, 'francis.lopez@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Francis', 'Lopez', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-24 23:29:55', NULL, '2025-11-24 23:29:55', NULL, NULL, NULL),
(282, 2, 'CCS-CS-02', 'Bachelor of Science in Computer Science', 'Machine Learning', 0, 'harold.espinosa@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Harold', 'Espinosa', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 00:59:43', NULL, NULL, NULL, NULL, NULL),
(283, 2, 'CCS-CS-03', 'Bachelor of Science in Computer Science', 'Algorithms', 0, 'ivy.marquez@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Ivy', 'Marquez', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 01:01:46', NULL, NULL, NULL, NULL, NULL),
(284, 2, 'CCS-CS-04', 'Bachelor of Science in Computer Science', 'AI Research', 1, 'renzo.castillo@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Renzo', 'Castillo', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-21 01:01:42', NULL, NULL, NULL, NULL, NULL),
(285, 2, 'CCS-CS-05', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Data Science', 0, 'mika.soriano@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Mika', 'Soriano', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-25 18:24:12', NULL, '2025-11-25 18:24:12', NULL, NULL, NULL),
(286, 2, 'CCS-IT-09', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'DevOps', 0, 'patrick.valdez@lpu.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Patrick', 'Valdez', 'm', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-30 11:39:25', NULL, '2025-11-30 11:39:25', NULL, NULL, NULL),
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
(306, 1, '2022-2-01020', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student01020@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT5dIl4YXBbG', 'Bianca', 'Serrano', 'f', NULL, NULL, '_defaultUser.png', '2025-07-21 08:25:32', NULL, '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(307, 1, '2021-2-01935', 'Bachelor of Library and Information Science', '', 0, 'student28282@lpunetwork.edu.ph', '$2y$10$ezZJqPPYJvkhHRAmv55ureOZijNHfxgJo/1NUSe9tAbHUdCJzYfzO', 'Micheal', 'Delizo', NULL, '', '', '_defaultUser.png', NULL, NULL, '2025-11-26 08:41:37', NULL, NULL, 0, NULL, NULL),
(308, 1, '2022-2-00308', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00308@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Jamie', 'Williams', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(309, 1, '2022-2-00309', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00309@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Morgan', 'Johnson', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(310, 1, '2022-2-00310', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00310@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Skyler', 'Green', 'm', '', '', '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 16:26:37', NULL, NULL, 0, 4, 'IT401'),
(311, 1, '2022-2-00311', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00311@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Drew', 'Davis', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(312, 1, '2022-2-00312', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00312@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Jamie', 'Jones', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(313, 1, '2022-2-00313', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00313@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Jordan', 'Martinez', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(314, 1, '2022-2-00314', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00314@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Riley', 'Brown', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(315, 1, '2022-2-00315', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00315@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Jordan', 'Lopez', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(316, 1, '2022-2-00316', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00316@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Riley', 'Smith', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(317, 1, '2022-2-00317', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00317@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Casey', 'Lopez', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(318, 1, '2022-2-00318', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00318@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Skyler', 'Miller', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(319, 1, '2022-2-00319', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00319@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Skyler', 'Johnson', 'f', '', '', '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 16:26:27', NULL, NULL, 0, 4, 'IT401'),
(320, 1, '2022-2-00320', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00320@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Drew', 'Garcia', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(321, 1, '2022-2-00321', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00321@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Morgan', 'Miller', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(322, 1, '2022-2-00322', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00322@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Skyler', 'Smith', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(323, 1, '2022-2-00323', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00323@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Cameron', 'Jones', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(324, 1, '2022-2-00324', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00324@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Riley', 'Ryder', 'f', '', '', '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-26 08:51:54', NULL, NULL, 0, 4, 'IT401'),
(325, 1, '2022-2-00325', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00325@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Drew', 'Brown', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(326, 1, '2022-2-00326', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00326@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Taylor', 'Swift', 'm', '', '', '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 16:28:07', NULL, NULL, 0, 4, 'IT401'),
(327, 1, '2022-2-00327', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00327@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Skyler', 'Adams', 'm', '', '', '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 16:26:16', NULL, NULL, 0, 4, 'IT401'),
(328, 1, '2022-2-00328', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00328@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Taylor', 'Lopez', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(329, 1, '2022-2-00329', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00329@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Morgan', 'Smith', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(330, 1, '2022-2-00330', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00330@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Cameron', 'Lopez', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(331, 1, '2022-2-00331', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00331@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Casey', 'Smith', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(332, 1, '2022-2-00332', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00332@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Riley', 'Martinez', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(333, 1, '2022-2-00333', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00333@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Cameron', 'Brown', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:45:11', '2025-11-25 11:45:11', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT401'),
(334, 1, '2022-2-00334', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00334@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mia', 'Garcia', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(335, 1, '2022-2-00335', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00335@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ella', 'Harris', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(336, 1, '2022-2-00336', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00336@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ella', 'Martinez', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(337, 1, '2022-2-00337', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00337@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ethan', 'Martinez', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(338, 1, '2022-2-00338', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00338@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ava', 'Robinson', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(339, 1, '2022-2-00339', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00339@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ava', 'Martin', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(340, 1, '2022-2-00340', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00340@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ethan', 'White', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(341, 1, '2022-2-00341', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00341@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ethan', 'Robinson', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(342, 1, '2022-2-00342', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00342@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ella', 'Thomas', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(343, 1, '2022-2-00343', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00343@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mason', 'Jackson', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(344, 1, '2022-2-00344', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00344@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Zoe', 'Jackson', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(345, 1, '2022-2-00345', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00345@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ava', 'Anderson', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(346, 1, '2022-2-00346', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00346@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ella', 'Cruz', 'm', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:46:23', NULL, NULL, 0, 4, 'IT402'),
(347, 1, '2022-2-00347', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00347@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ella', 'Anderson', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(348, 1, '2022-2-00348', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00348@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ava', 'Harris', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(349, 1, '2022-2-00349', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00349@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mason', 'Thompson', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(350, 1, '2022-2-00350', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00350@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Zoe', 'Martin', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(351, 1, '2022-2-00351', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00351@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mason', 'Martinez', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(352, 1, '2022-2-00352', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00352@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Logan', 'Harris', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(353, 1, '2022-2-00353', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00353@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ava', 'Kit Ganern', 'f', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:42:56', NULL, NULL, 0, 4, 'IT402'),
(354, 1, '2022-2-00354', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00354@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mason', 'Garcia', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(355, 1, '2022-2-00355', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00355@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Noah', 'Harris', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(356, 1, '2022-2-00356', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00356@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ethan', 'Jackson', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(357, 1, '2022-2-00357', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00357@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Zoe', 'Garcia', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(358, 1, '2022-2-00358', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00358@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mason', 'Ry', 'm', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:50:05', NULL, NULL, 0, 4, 'IT402'),
(359, 1, '2022-2-00359', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00359@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Zoe', 'White', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(360, 1, '2022-2-00360', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00360@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ethan', 'Garcia', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(361, 1, '2022-2-00361', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00361@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ella', 'Thompson', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(362, 1, '2022-2-00362', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00362@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Noah', 'Thompson', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(363, 1, '2022-2-00363', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00363@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mason', 'Anderson', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(364, 1, '2022-2-00364', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00364@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ella', 'Jackson', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(365, 1, '2022-2-00365', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00365@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mia', 'Martin', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(366, 1, '2022-2-00366', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00366@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mia', 'Martinez', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(367, 1, '2022-2-00367', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00367@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ethan', 'Sy', 'm', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:47:43', NULL, NULL, 0, 4, 'IT402'),
(368, 1, '2022-2-00368', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00368@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mason', 'Ines', 'f', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:50:55', NULL, NULL, 0, 4, 'IT402'),
(369, 1, '2022-2-00369', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00369@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mia', 'Jackson', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(370, 1, '2022-2-00370', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00370@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Noah', 'Martinez', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(371, 1, '2022-2-00371', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00371@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Mason', 'Olivarez', 'f', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:51:12', NULL, NULL, 0, 4, 'IT402'),
(372, 1, '2022-2-00372', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00372@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Ethan', 'Pronopio', 'f', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:48:04', NULL, NULL, 0, 4, 'IT402'),
(373, 1, '2022-2-00373', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00373@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Lily', 'Garcia', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT402'),
(374, 1, '2022-2-00374', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00374@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Elijah', 'Adams', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(375, 1, '2022-2-00375', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00375@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Aria', 'Green', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(376, 1, '2022-2-00376', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00376@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Aria', 'Carter', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(377, 1, '2022-2-00377', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00377@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Elijah', 'Hill', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(378, 1, '2022-2-00378', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00378@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Benjamin', 'King', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(379, 1, '2022-2-00379', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00379@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Benjamin', 'Nelson', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(380, 1, '2022-2-00380', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00380@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Chloe', 'Lopez', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(381, 1, '2022-2-00381', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00381@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Harper', 'King', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(382, 1, '2022-2-00382', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00382@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Elijah', 'Duterte', 'f', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:43:54', NULL, NULL, 0, 4, 'IT403'),
(383, 1, '2022-2-00383', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00383@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Elijah', 'Carter', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(384, 1, '2022-2-00384', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00384@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Harper', 'Scott', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(385, 1, '2022-2-00385', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00385@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Oliver', 'Nelson', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(386, 1, '2022-2-00386', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00386@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Lucas', 'Adams', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(387, 1, '2022-2-00387', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00387@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Elijah', 'Mountain', 'm', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:44:13', NULL, NULL, 0, 4, 'IT403'),
(388, 1, '2022-2-00388', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00388@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Sophia', 'Baker', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(389, 1, '2022-2-00389', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00389@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Manuela', 'King', 'f', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:44:51', NULL, NULL, 0, 4, 'IT403'),
(390, 1, '2022-2-00390', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00390@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Oliver', 'Green', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(391, 1, '2022-2-00391', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00391@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Chloe', 'Wright', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(392, 1, '2022-2-00392', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00392@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Aria', 'Scott', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(393, 1, '2022-2-00393', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00393@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Scarlett', 'Carter', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(394, 1, '2022-2-00394', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00394@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Alfredo', 'Carter', 'm', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:45:17', NULL, NULL, 0, 4, 'IT403'),
(395, 1, '2022-2-00395', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00395@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Aria', 'Dimaculangan', 'm', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:42:08', NULL, NULL, 0, 4, 'IT403'),
(396, 1, '2022-2-00396', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00396@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Chloe', 'Green', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(397, 1, '2022-2-00397', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00397@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Scarlett', 'Green', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(398, 1, '2022-2-00398', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00398@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Sabrina', 'Carpenter', 'f', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:52:16', NULL, NULL, 0, 4, 'IT403'),
(399, 1, '2022-2-00399', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00399@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Oliver', 'King', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(400, 1, '2022-2-00400', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00400@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Aria', 'Batongbakal', 'f', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:42:24', NULL, NULL, 0, 4, 'IT403'),
(401, 1, '2022-2-00401', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00401@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Oliver', 'Wright', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(402, 1, '2022-2-00402', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00402@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Thor', 'Hill', 'm', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:46:07', NULL, NULL, 0, 4, 'IT403'),
(403, 1, '2022-2-00403', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00403@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Sophia', 'Carter', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(404, 1, '2022-2-00404', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00404@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Lucas', 'Scott', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(405, 1, '2022-2-00405', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00405@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Chloe', 'Adams', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(406, 1, '2022-2-00406', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00406@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Harper', 'Adams', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(407, 1, '2022-2-00407', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00407@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Chloe', 'Carter', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(408, 1, '2022-2-00408', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00408@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Harper', 'Dimaguiba', 'f', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:49:28', NULL, NULL, 0, 4, 'IT403'),
(409, 1, '2022-2-00409', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00409@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Elijah', 'King', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(410, 1, '2022-2-00410', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00410@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Chloe', 'Scott', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 14:37:35', NULL, '2025-11-26 14:37:35', NULL, 4, 'IT403'),
(411, 1, '2022-2-00411', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00411@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Elijah', 'Green', 'm', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-25 11:53:27', NULL, NULL, NULL, 4, 'IT403'),
(412, 1, '2022-2-00412', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00412@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Benjamin', 'Quiboloy', 'm', '', '', '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 08:43:15', NULL, NULL, 0, 4, 'IT403'),
(413, 1, '2022-2-00413', 'Bachelor of Science in Information Technology - Web and Mobile Technology', '', 0, 'student00413@lpunetwork.edu.ph', '$2y$10$dEmz96jH8Ej2CvOldOWtO.rb0pWOEEqKp4s9DGjaRWT...', 'Aria', 'Wright', 'f', NULL, NULL, '_defaultUser.png', '2025-11-25 11:50:34', '2025-11-25 11:50:34', '2025-11-26 14:17:27', NULL, '2025-11-26 14:17:27', NULL, 4, 'IT403'),
(414, 1, 'Berna.Marie', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'bernamarie@lpunetwork.edu.ph', '$2y$10$ZsvgvTBHpBOxFU10Vdqkk.I4RBJlXc0vQj3CeO9iJq0hdr/WLygNW', 'Berna Marie', 'Alhambra', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:25:56', NULL, NULL, NULL, NULL, NULL),
(415, 1, 'Hans.Kyle', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'hans.kyle@lpunetwork.edu.ph', '$2y$10$pfmWo7hH1Ti0er58PoFx0uxek9wFwWNCcxqgo.4GFwsWwiq9wRUeK', 'Hans Kyle', 'Bertoso', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:26:37', NULL, NULL, NULL, NULL, NULL),
(416, 1, 'Vince.Wackie', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'vince.wackie@lpunetwork.edu.ph', '$2y$10$6sugUBQFs9dVgg/IldQBV.qC9sdKNKcJ1nbcaDJWaIhegBEdQa6uK', 'Vince Wackie', 'Espera', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:27:30', NULL, NULL, NULL, NULL, NULL),
(417, 1, 'Mark.Judiel', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'mark.salazar@lpunetwork.edu.ph', '$2y$10$ueH43Wh8yFPLdlexiYcW8OZPoEx5W0PGfAos0vLQR00BenXC.lDVO', 'Mark Judiel', 'Salazar', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:28:02', NULL, NULL, NULL, NULL, NULL),
(418, 2, 'Raymund.Constante', '', 'Mobile Dev', 0, 'raymund.constante@lpunetwork.edu.ph', '$2y$10$Uqww9X/H0VY2w1VRVPn/5.12mem55JELt3P.ElfE7ORez8D34r/Gy', 'Raymund', 'Constante', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:31:19', NULL, NULL, NULL, NULL, NULL),
(419, 1, 'Lance.Romero', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'lance.romero@lpunetwork.edu.ph', '$2y$10$F6ADkT5mZD.yfMOfhr0oy./6e4uCyIl452EewryBzRhmoaaqNTKZ2', 'Lance Christian', 'Romero', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:40:18', NULL, NULL, NULL, NULL, NULL),
(420, 1, 'Romuel.Borja', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'romuel.borja@lpunetwork.edu.ph', '$2y$10$vzAw9GKWMt./GWOQ6eWrLekGUb8WAhQwxAl2XRe3npzGAmczMS6Am', 'Romuel', 'Borja', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:40:56', NULL, NULL, NULL, NULL, NULL),
(421, 1, 'dyan.mercado', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'dyan.mercado@lpunetwork.edu.ph', '$2y$10$oSiwK.onke5O4Qld8IDwbOAVcK51LnEejhGtY2f9m3Gt4AvVN42TW', 'Dyan Paula', 'Mercado', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:41:19', NULL, NULL, NULL, NULL, NULL),
(422, 2, 'jerian.peren', 'Bachelor of Science in Computer Science - Software Engineering', 'Software Engineering', 0, 'jerian.peren@lpu.edu.ph', '$2y$10$KEg3VSXNVT7exMcEvuShsermUAAjX2XG2Tz36xW0ka5SulnztQ82q', 'Jerian', 'Peren', 'f', '', '', '_defaultUser.png', NULL, NULL, '2025-12-02 07:12:09', NULL, '2025-11-30 08:47:45', 0, NULL, NULL),
(423, 1, 'Mark.Caparas', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'mark.caparas@lpunetwork.edu.ph', '$2y$10$b8dW.fKwDQk9un0FvoDl2etM3A6U6dYIWTUlZE8eCq8L5fO2/8zfi', 'Mark Risen', 'Caparas', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:44:56', NULL, NULL, NULL, NULL, NULL),
(424, 1, 'Charles.Rull', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'charles.rull@lpunetwork.edu.ph', '$2y$10$gyLxUTDCY/B2Zv3oK0vlnuspxZhcQA1BLRHUrEUUdw7Ruwzx9J82O', 'Charles Justine', 'Rull', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:45:24', NULL, NULL, NULL, NULL, NULL),
(425, 1, 'Johann.Cepeda', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'johann.cepeda@lpunetwork.edu.ph', '$2y$10$RcMgyr5oSDKiGDZ6WLAAhOBW.92N7bn3dRBiev95vRxOgLPhanbF6', 'Johann Nikkolai', 'Cepeda', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:47:33', NULL, NULL, NULL, NULL, NULL),
(426, 1, 'Stephen.Lacsa', 'Bachelor of Science in Computer Science - Software Engineering', '', 0, 'stephen.lacsa@lpunetwork.edu.ph', '$2y$10$9JnluQdq12YYouVkCHxlNO5EA0PoXVVEIto2/CFEpwI5AQy1o1Pr6', 'Stephen', 'Lacsan', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 00:47:58', NULL, NULL, NULL, NULL, NULL),
(427, 2, 'Arcell.Hadlocon', 'Bachelor of Science in Computer Science - Software Engineering', 'Mobile Dev', 0, 'arcell.hadlocon@lpunetwork.edu.ph', '$2y$10$eLf6dMw98t5akLdttz9KPuPsHgpMpu05JlYSFkT0zcvKsuiaki8Be', 'Arcell', 'Hadlocon', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 11:38:02', NULL, '2025-11-30 11:38:02', NULL, NULL, NULL),
(428, 2, 'Klarence.Baptista', 'Bachelor of Science in Information Technology - Web and Mobile Technology', 'Mobile Dev', 0, 'klarence.baptista@lpunetwork.edu.ph', '$2y$10$EVkvQvWqOVIO3HgcSgI4r.Whq03HzopC1Z3QRyoII10wm9Uf4nXWG', 'Klarence', 'Baptista', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 21:23:46', NULL, NULL, NULL, NULL, NULL),
(429, 2, 'Elizabeth.Nsubuga', 'Bachelor of Science in Computer Science - Software Engineering', 'Technical Writer', 0, 'elizabeth.nsubuga@lpunetwork.edu.ph', '$2y$10$YXfzDQtybFbj1xNQcH0jfeZ2GptRAP9ZZGfC9Rfi9/bR/h7dAsDkW', 'Elizabeth', 'Nsubuga', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 21:24:41', NULL, NULL, NULL, NULL, NULL),
(430, 2, 'Sean.Gono', '', 'Hybrid Dev', 0, 'sean.gono@lpunetwork.edu.ph', '$2y$10$b9vt3QHAshIt78F5j.qbbuERFDbtsTiFUvyFaHXLDn7lm5SSFt876', 'Sean Charlston', 'Gono', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 21:25:27', NULL, NULL, NULL, NULL, NULL),
(431, 2, 'Elmer.Matel', '', 'Web Dev', 0, 'elmer.matel@lpunetwork.edu.ph', '$2y$10$NVj1iu9gIauZNDmPhM1MCu1ZiBZXJgLLxP08soK5wu6xC3J9TgHiy', 'Elmer', 'Matel', NULL, NULL, NULL, '_defaultUser.png', NULL, NULL, '2025-11-30 21:29:43', NULL, NULL, NULL, NULL, NULL),
(432, 1, 'jaira.mae', '', '', 0, 'jaira.mae@lpunetwork.edu.ph', '$2y$10$ukKmmtQR.ALzvBzczumn/eIzslvRRfccc0nrEcurxee.jrijqqhSm', 'Jaira Mae', 'Tafalla', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 05:31:10', '2025-11-30 21:33:07', NULL, '2025-11-30 05:31:47', 0, 4, 'CS401'),
(433, 1, 'leennel.ioan', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'leennel.ioan@lpunetwork.edu.ph', '$2y$10$Qj4VHH2ZfbHBnGThw6iN7OQAHh4e1vZAgdVF5SKpOImIKlXwL2ZO6', 'Leennel Ioan', 'Santos', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 05:35:56', '2025-11-30 05:35:56', NULL, NULL, NULL, 4, 'CS401'),
(434, 1, 'brent.harvey', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'brent.harvey@lpunetwork.edu.ph', '$2y$10$fnmTDhzGbWgd4ekd4PoaHulozqHLJJw6aB9sV6wJ2kARaxC/04n2y', 'Brent Harvey', 'Rull', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 05:36:43', '2025-11-30 05:36:43', NULL, NULL, NULL, 4, 'CS401'),
(435, 1, 'sir.lawrence', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'sir.lawrence@lpunetwork.edu.ph', '$2y$10$WDgkACCjjs2FVDUipf23e.oE/FJy1gFDW6GBOxTyjP.STmpHqk0Ge', 'Sir Lawrence', 'Laudato', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 05:37:28', '2025-11-30 05:37:28', NULL, NULL, NULL, 4, 'CS401'),
(436, 2, 'Jeff.Nebran', 'Bachelor of Science in Computer Engineering', NULL, NULL, 'Jeff.Nebran@lpu.edu.ph', '$2y$10$8Z2Af02NH9ypAEiPq5Po3ufjP5ZCbkgXrY3lqJOSsfloA6qhzg5mG', 'Jeff', 'Nebran', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 05:46:07', '2025-11-30 05:46:07', NULL, NULL, NULL, NULL, NULL),
(437, 1, 'ian.lumanog', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'ian.lumanog@lpunetwork.edu.ph', '$2y$10$j13zgjmiWnaN3Vw5HjKjm.iqZoBH8fuHGx1MxDBZqWUsChi9koKSW', 'Ian', 'Lumanog', NULL, '', '', '_defaultUser.png', '2025-11-30 05:50:39', '2025-11-30 05:50:39', '2025-12-01 05:54:21', NULL, '2025-12-01 05:54:21', NULL, 4, 'CS401'),
(438, 1, 'aaron.joshua', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'aaron.joshua@lpunetwork.edu.ph', '$2y$10$gXHNsPp/MFpwlORezp0mDuI/wgv8D4oKrTDp1YBvFP1lhsnaiP3DC', 'Aaron Joshua', 'Roxas', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 05:51:16', '2025-11-30 05:51:16', NULL, NULL, NULL, 4, 'CS401'),
(439, 1, 'alexis.john', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'alexis.john@lpunetwork.edu.ph', '$2y$10$yT5ue/p77s2m2R2US6g10OQJVXz0QVrAAgkgWtHdFKyzlaLSfsSC2', 'Alexis John', 'Rellon', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 05:51:48', '2025-11-30 05:51:48', NULL, NULL, NULL, 4, 'CS401'),
(440, 1, 'beo.alvaro', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'beo.alvaro@lpunetwork.edu.ph', '$2y$10$YZlqOPEUV3nJpgI68y2aueDODDg0oY0hwsrTWqlD0mVNsjWl5BAg.', 'Beo Alvaro', 'Salguero', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 05:52:23', '2025-11-30 05:52:23', NULL, NULL, NULL, 4, 'CS401'),
(441, 2, 'Roger.Wyne', 'Bachelor of Science in Computer Engineering', NULL, NULL, 'Roger.Wyne@lpu.edu.ph', '$2y$10$MuSpvhg0t8mWCfFYeJabgOFXthPjK/7gbIc/v9CYYe.zUtIKksOC2', 'Roger Wyne', 'Doctor', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 05:53:23', '2025-11-30 05:53:23', NULL, NULL, NULL, NULL, NULL),
(443, 1, 'king.edward', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'king.edward@lpunetwork.edu.ph', '$2y$10$akWNJ.AKxDp0t0arfs78.OM9869q6QkAcWpkqXj8C09WhE9ei3RDC', 'King Edward', 'Page', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:01:17', '2025-11-30 06:01:17', NULL, NULL, NULL, 4, 'CS401'),
(444, 1, 'micah.sereno', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'micah.sereno@lpunetwork.edu.ph', '$2y$10$5nhQ3ytE2afl2tbWMW788OGetz7U.82l2XK7cpSdR9zDEIeE/Y3dG', 'Micah', 'Sereno', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:02:06', '2025-11-30 06:02:06', NULL, NULL, NULL, 4, 'CS401'),
(445, 1, 'mielle.angelie', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'mielle.angelie@lpunetwork.edu.ph', '$2y$10$RfI5uYh5cHmdTjmGM49Gze10dy/5eGK6ZPvkBi/PuaY2duUatY97a', 'Mielle Angelie', 'Dulce', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:02:40', '2025-11-30 06:02:40', NULL, NULL, NULL, 4, 'CS401'),
(446, 1, 'barbuco.barbuco', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'barbuco.barbuco@lpunetwork.edu.ph', '$2y$10$19U7FmOF3I7wti/OzLNO.OlbwyD8CTrDulIR0lA4bsrzfUpb4o5IK', 'Barbuco', 'Barbuco', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:03:29', '2025-11-30 06:03:29', NULL, NULL, NULL, 4, 'CS401'),
(447, 1, 'alyssa.mae', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'alyssa.mae@lpunetwork.edu.ph', '$2y$10$JWWTlW1jPQ58em6Tjf/Gke0cT7TNck2A5tTdQvGVVr7p.JRdH/6sS', 'Alyssa Mae', 'Abac', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:08:02', '2025-11-30 06:08:02', NULL, NULL, NULL, 4, 'CS401'),
(448, 1, 'alexander.asinas', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'alexander.asinas@lpunetwork.edu.ph', '$2y$10$thsjQmh3NINvyY3DetlbpuURqTi5MzjjvldP74kzdNqxHFsK0jxpy', 'Alexander', 'Asinas', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:08:50', '2025-11-30 06:08:50', NULL, NULL, NULL, 4, 'CS401'),
(449, 1, 'angelo.mark', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'angelo.mark@lpunetwork.edu.ph', '$2y$10$hYZIQB8lrLV4YB99s/mW1ul7GnrPJR8ZPsYnMdrA4vjcYJD8j4bB6', 'Angelo Mark Xyz', 'Fabian', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:10:03', '2025-11-30 06:10:03', NULL, NULL, NULL, 4, 'CS401'),
(450, 1, 'brandon.miranda', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'brandon.miranda@lpunetwork.edu.ph', '$2y$10$YFfMSY/p4ltJ/GOCkQr/QOBr1Dvy5K5q4WhORf1ufO46B6JIsB.0m', 'Brandon', 'Miranda', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:10:41', '2025-11-30 06:10:41', NULL, NULL, NULL, 4, 'CS401'),
(451, 2, 'Earl.Saavedra', 'Bachelor of Science in Computer Engineering', NULL, NULL, 'Earl.Saavedra@lpu.edu.ph', '$2y$10$0zsZioOd/rXjfya0WG2fBOz4GkRdyxoJ1uk5QAiijON8nyBTjkFgW', 'Earl', 'Saavedra', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:11:26', '2025-11-30 06:11:26', NULL, NULL, NULL, NULL, NULL),
(452, 1, 'cj.vhert', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'cj.vhert@lpunetwork.edu.ph', '$2y$10$Su1eXEvdR..fM/CeVWto.uWXcdBUj8VlVFGoGGNhl3ZcSQfQMGBxm', 'Cj Vhert', 'Rojo', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:15:33', '2025-11-30 06:15:33', NULL, NULL, NULL, 4, 'CS401'),
(453, 1, 'john.lloyd', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'john.lloyd@lpunetwork.edu.ph', '$2y$10$eCX.ZKz2kJ2ulXZavHm4B.S79lmnzsqCOXdCfu3pRK37y7Yod3b4a', 'John Lloyd', 'Dela Cruz', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:16:16', '2025-11-30 06:16:16', NULL, NULL, NULL, 4, 'CS401'),
(454, 1, 'patricia.nicole', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'patricia.nicole@lpunetwork.edu.ph', '$2y$10$I8BnEaYVlpiMfhN3V91v2eDaOyYHbODqxjNAJNSal4A4dMtU99CaC', 'Patricia Nicole', 'Mendoza', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:16:44', '2025-11-30 06:16:44', NULL, NULL, NULL, 4, 'CS401'),
(455, 1, 'leila.aliyah', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'leila.aliyah@lpunetwork.edu.ph', '$2y$10$OyLaJuHSO7n7nZrSVbZJ1eh3UUCjhVucFYve6sLzT9F9xYuCGE5Y2', 'Leila Aliyah', 'Manalo', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:17:21', '2025-11-30 06:17:21', NULL, NULL, NULL, 4, 'CS401'),
(456, 1, 'john.vincent', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'john.vincent@lpunetwork.edu.ph', '$2y$10$EOBKNUYvnNA4E2eR7/g8LOV/D4Q2JEmt5mOuLuoykRRuLoOMiVHAG', 'John Vincent', 'Lingad', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:21:21', '2025-11-30 06:21:21', NULL, NULL, NULL, 4, 'CS401'),
(457, 1, 'warren.jacob', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'warren.jacob@lpunetwork.edu.ph', '$2y$10$lcrcap3ekJTaJm5zOJEeF.PTuM74DPkG6hORQNLA8dxMjJzWRVZBm', 'Warren Jacob', 'Abdon', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:21:57', '2025-11-30 06:21:57', NULL, NULL, NULL, 4, 'CS401'),
(458, 1, 'jc.villaganas', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'jc.villaganas@lpunetwork.edu.ph', '$2y$10$5WyYpgny7eZyoza1a2l9zeZpOZ26g1/Wmvyd4rzqBFQkQp/Kp20ey', 'JC', 'Villaganas', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:22:30', '2025-11-30 06:22:30', NULL, NULL, NULL, 4, 'CS401'),
(459, 1, 'nicole.wyne', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'nicole.wyne@lpunetwork.edu.ph', '$2y$10$6P0rFOfxGZyQmWHroA1/W.niFQWFC/TIk.Ji2ZKemnQfA5X8SuhTS', 'Nicole Wyne', 'Fernandez', NULL, '', '', '_defaultUser.png', NULL, '2025-11-30 06:23:01', '2025-11-30 06:23:01', NULL, NULL, NULL, 4, 'CS401'),
(461, 1, 'jaermaine.lester', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'jaermaine.lester@lpunetwork.edu.ph', '$2y$10$ehYpUqRvq5P/KlVf5znuBeLWg9OhimOXeCaYr.ycCyT8xQ5.bxU5u', 'Jaermaine Lester', 'Domingcil', NULL, '', '', '_defaultUser.png', NULL, '2025-12-01 15:19:54', '2025-12-01 15:19:54', NULL, NULL, NULL, 4, 'CS401'),
(462, 1, 'lorenzo.canales', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'lorenzo.canales@lpunetwork.edu.ph', '$2y$10$/Yav1eRsbQSC10k5WthLm.c4ZuxVlLv8FXDUtDY/Bf4WYFp2mE3Oa', 'Lorenzo', 'Canales', NULL, '', '', '_defaultUser.png', NULL, '2025-12-01 15:20:33', '2025-12-01 15:20:33', NULL, NULL, NULL, 4, 'CS401'),
(463, 1, 'franco.luis', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'franco.luis@lpunetwork.edu.ph', '$2y$10$8gAlXEsAaP8OTa1N0DaD6OzaUNuyJJQPc5tDb7LbQTLbs0EtkcrSa', 'Franco Luis', 'Nicanor', NULL, '', '', '_defaultUser.png', NULL, '2025-12-01 15:21:01', '2025-12-01 15:21:01', NULL, NULL, NULL, 4, 'CS401'),
(464, 1, 'resty.jean', 'Bachelor of Science in Computer Science Software Engineering', NULL, NULL, 'resty.jean@lpunetwork.edu.ph', '$2y$10$tEPFv/x6M0MrgcbFamoSDuFFsy6eV/kCGJ58ws9y3x0kcf/yrdWsu', 'Resty Jean', 'Cruz', NULL, '', '', '_defaultUser.png', NULL, '2025-12-01 15:21:36', '2025-12-01 15:21:36', NULL, NULL, NULL, 4, 'CS401');

-- --------------------------------------------------------

--
-- Table structure for table `user_schedules`
--

DROP TABLE IF EXISTS `user_schedules`;
CREATE TABLE IF NOT EXISTS `user_schedules` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `program` varchar(255) DEFAULT NULL,
  `section` varchar(255) DEFAULT NULL,
  `room` varchar(255) DEFAULT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `year` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1413 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_schedules`
--

INSERT INTO `user_schedules` (`id`, `user_id`, `program`, `section`, `room`, `day_of_week`, `start_time`, `end_time`, `class_name`, `year`) VALUES
(1226, 281, '80', 'IT401', 'L102', 'Friday', '08:30:00', '11:30:00', 'Mobile Development (3-hour class)', 4),
(1227, 279, '80', 'IT401', 'L103', 'Monday', '17:00:00', '19:00:00', 'Cloud Computing (2-hour lecture)', 4),
(1228, 277, '80', 'IT401', 'L203', 'Tuesday', '09:00:00', '11:00:00', 'Database Systems (2-hour lecture)', 4),
(1229, 271, '80', 'IT401', 'L102', 'Monday', '19:00:00', '21:00:00', 'Software Testing (2-hour lecture)', 4),
(1230, 269, '80', 'IT401', 'L201', 'Monday', '09:30:00', '11:30:00', 'UI/UX Design (2-hour lecture)', 4),
(1231, 278, '80', 'IT401', 'C602', 'Saturday', '07:00:00', '09:00:00', 'Cloud Computing (2-hour lecture)', 4),
(1232, 279, '80', 'IT401', 'L103', 'Friday', '19:00:00', '21:00:00', 'Mobile Development (2-hour lecture)', 4),
(1233, 278, '80', 'IT401', 'C601', 'Tuesday', '14:00:00', '17:00:00', 'Software Testing (3-hour class)', 4),
(1234, 277, '80', 'IT401', 'L202', 'Wednesday', '14:30:00', '17:30:00', 'Web Technologies (3-hour laboratory)', 4),
(1235, 279, '80', 'IT401', 'C603', 'Friday', '13:30:00', '15:30:00', 'Cybersecurity (2-hour lecture)', 4),
(1236, 281, '80', 'IT401', 'L101', 'Saturday', '17:30:00', '19:30:00', 'Database Systems (2-hour lecture)', 4),
(1237, 269, '80', 'IT402', 'L101', 'Saturday', '08:00:00', '11:00:00', 'Mobile Development (3-hour class)', 4),
(1238, 269, '80', 'IT402', 'C609', 'Saturday', '14:30:00', '17:30:00', 'Systems Development (3-hour laboratory)', 4),
(1239, 271, '80', 'IT402', 'C601', 'Monday', '13:00:00', '16:00:00', 'Enterprise Systems (3-hour laboratory)', 4),
(1240, 280, '80', 'IT402', 'L201', 'Wednesday', '13:30:00', '15:30:00', 'UI/UX Design (2-hour lecture)', 4),
(1241, 280, '80', 'IT402', 'L103', 'Saturday', '18:00:00', '21:00:00', 'Systems Development (3-hour class)', 4),
(1242, 280, '80', 'IT402', 'L201', 'Tuesday', '13:00:00', '15:00:00', 'Cloud Computing (2-hour lecture)', 4),
(1243, 280, '80', 'IT402', 'C602', 'Wednesday', '09:00:00', '12:00:00', 'Cloud Computing (3-hour class)', 4),
(1244, 271, '80', 'IT402', 'L101', 'Wednesday', '17:00:00', '20:00:00', 'Mobile Development (3-hour laboratory)', 4),
(1245, 278, '80', 'IT402', 'L103', 'Wednesday', '07:00:00', '09:00:00', 'IT Capstone (2-hour lecture)', 4),
(1246, 270, '80', 'IT403', 'L101', 'Monday', '13:00:00', '16:00:00', 'Mobile Development (3-hour class)', 4),
(1247, 278, '80', 'IT403', 'L202', 'Monday', '07:00:00', '10:00:00', 'UI/UX Design (3-hour class)', 4),
(1248, 269, '80', 'IT403', 'C603', 'Tuesday', '13:00:00', '16:00:00', 'Enterprise Systems (3-hour laboratory)', 4),
(1249, 277, '80', 'IT403', 'L101', 'Wednesday', '08:00:00', '11:00:00', 'Enterprise Systems (3-hour class)', 4),
(1250, 279, '80', 'IT403', 'L102', 'Friday', '16:00:00', '19:00:00', 'Web Technologies (3-hour laboratory)', 4),
(1251, 277, '80', 'IT403', 'L101', 'Friday', '09:00:00', '12:00:00', 'Systems Development (3-hour class)', 4),
(1252, 271, '80', 'IT403', 'C609', 'Friday', '13:00:00', '16:00:00', 'Software Testing (3-hour laboratory)', 4),
(1253, 271, '80', 'IT403', 'L203', 'Saturday', '16:00:00', '19:00:00', 'Cybersecurity (3-hour laboratory)', 4),
(1254, 283, '78', 'CS401', 'C601', 'Friday', '17:30:00', '20:30:00', 'Algorithms (3-hour class)', 4),
(1255, 272, '78', 'CS401', 'C609', 'Thursday', '07:00:00', '10:00:00', 'Machine Learning (3-hour class)', 4),
(1256, 283, '78', 'CS401', 'C602', 'Friday', '08:00:00', '11:00:00', 'Compiler Design (3-hour class)', 4),
(1257, 284, '78', 'CS401', 'C601', 'Tuesday', '09:00:00', '12:00:00', 'AI Research (3-hour class)', 4),
(1258, 282, '78', 'CS401', 'L202', 'Thursday', '16:00:00', '19:00:00', 'Software Design (3-hour laboratory)', 4),
(1259, 282, '78', 'CS401', 'C609', 'Tuesday', '14:30:00', '17:30:00', 'AI Research (3-hour laboratory)', 4),
(1260, 284, '78', 'CS401', 'C603', 'Friday', '15:30:00', '17:30:00', 'Machine Learning (2-hour lecture)', 4),
(1261, 284, '78', 'CS401', 'C609', 'Thursday', '13:00:00', '16:00:00', 'CS Thesis (3-hour laboratory)', 4),
(1262, 272, '78', 'CS401', 'L203', 'Monday', '17:00:00', '20:00:00', 'CS Thesis (3-hour class)', 4),
(1263, 285, '86', 'CpE401', 'L101', 'Thursday', '16:30:00', '18:30:00', 'Embedded Systems (2-hour lecture)', 4),
(1264, 285, '86', 'CpE401', 'L101', 'Monday', '18:00:00', '21:00:00', 'Microprocessors (3-hour laboratory)', 4),
(1265, 281, '86', 'CpE401', 'L101', 'Wednesday', '13:00:00', '16:00:00', 'Hardware Design (3-hour laboratory)', 4),
(1266, 286, '86', 'CpE401', 'L101', 'Tuesday', '16:00:00', '19:00:00', 'Hardware Design (3-hour laboratory)', 4),
(1267, 285, '86', 'CpE401', 'L101', 'Tuesday', '09:30:00', '11:30:00', 'Computer Architecture (2-hour lecture)', 4),
(1268, 282, '86', 'CpE401', 'L102', 'Wednesday', '18:00:00', '21:00:00', 'VLSI Design (3-hour laboratory)', 4),
(1269, 272, '86', 'CpE401', 'L101', 'Friday', '16:00:00', '18:00:00', 'Embedded Systems (2-hour lecture)', 4),
(1270, 283, '86', 'CpE401', 'L102', 'Wednesday', '07:30:00', '10:30:00', 'Digital Signal Processing (3-hour class)', 4),
(1271, 270, '86', 'CpE401', 'L101', 'Monday', '08:30:00', '11:30:00', 'Computer Architecture (3-hour class)', 4),
(1272, 270, '86', 'CpE401', 'L101', 'Saturday', '14:00:00', '16:00:00', 'Digital Signal Processing (2-hour lecture)', 4),
(1273, 285, '86', 'CpE401', 'L102', 'Saturday', '07:00:00', '10:00:00', 'Hardware Design (3-hour laboratory)', 4),
(1274, 284, '86', 'CpE402', 'L101', 'Friday', '18:00:00', '20:00:00', 'Embedded Systems (2-hour lecture)', 4),
(1275, 286, '86', 'CpE402', 'L102', 'Saturday', '10:00:00', '12:00:00', 'IoT Systems (2-hour lecture)', 4),
(1276, 281, '86', 'CpE402', 'L102', 'Tuesday', '16:00:00', '19:00:00', 'VLSI Design (3-hour laboratory)', 4),
(1277, 272, '86', 'CpE402', 'L101', 'Tuesday', '13:30:00', '15:30:00', 'Digital Signal Processing (2-hour lecture)', 4),
(1278, 282, '86', 'CpE402', 'L102', 'Monday', '15:00:00', '18:00:00', 'Robotics (3-hour class)', 4),
(1279, 283, '86', 'CpE402', 'L102', 'Wednesday', '13:00:00', '15:00:00', 'Hardware Design (2-hour lecture)', 4),
(1280, 270, '86', 'CpE402', 'L102', 'Wednesday', '15:00:00', '18:00:00', 'Hardware Design (3-hour class)', 4),
(1281, 284, '86', 'CpE402', 'L101', 'Friday', '07:00:00', '09:00:00', 'Control Systems (2-hour lecture)', 4),
(1282, 286, '86', 'CpE402', 'L101', 'Tuesday', '07:30:00', '09:30:00', 'Hardware Design (2-hour lecture)', 4),
(1283, 286, '86', 'CpE402', 'L101', 'Friday', '13:30:00', '15:30:00', 'Control Systems (2-hour lecture)', 4),
(1284, 279, '86', 'CpE402', 'L102', 'Saturday', '14:30:00', '17:30:00', 'IoT Systems (3-hour class)', 4),
(1285, 280, '86', 'CpE402', 'L103', 'Wednesday', '18:00:00', '21:00:00', 'Robotics (3-hour class)', 4),
(1286, 285, '86', 'CpE403', 'L102', 'Tuesday', '13:00:00', '16:00:00', 'Computer Architecture (3-hour class)', 4),
(1287, 278, '86', 'CpE403', 'L102', 'Saturday', '18:30:00', '20:30:00', 'Digital Signal Processing (2-hour lecture)', 4),
(1288, 286, '86', 'CpE403', 'L103', 'Saturday', '15:00:00', '18:00:00', 'Computer Networks (3-hour laboratory)', 4),
(1289, 281, '86', 'CpE403', 'L102', 'Friday', '13:00:00', '16:00:00', 'IoT Systems (3-hour class)', 4),
(1290, 269, '86', 'CpE403', 'L102', 'Friday', '19:00:00', '21:00:00', 'IoT Systems (2-hour lecture)', 4),
(1291, 277, '86', 'CpE403', 'L103', 'Tuesday', '16:30:00', '19:30:00', 'Robotics (3-hour class)', 4),
(1292, 272, '86', 'CpE403', 'L103', 'Saturday', '08:00:00', '10:00:00', 'VLSI Design (2-hour lecture)', 4),
(1293, 270, '86', 'CpE403', 'L103', 'Wednesday', '09:00:00', '12:00:00', 'Microprocessors (3-hour laboratory)', 4),
(1294, 272, '86', 'CpE403', 'L102', 'Monday', '07:30:00', '10:30:00', 'Control Systems (3-hour class)', 4),
(1295, 282, '86', 'CpE403', 'L102', 'Tuesday', '08:00:00', '11:00:00', 'Digital Signal Processing (3-hour laboratory)', 4),
(1296, 286, '78', 'CS402', 'L101', 'Monday', '16:00:00', '18:00:00', 'Machine Learning (2-hour lecture)', 4),
(1297, 269, '78', 'CS402', 'L201', 'Saturday', '17:30:00', '20:30:00', 'Algorithms (3-hour laboratory)', 4),
(1298, 283, '78', 'CS402', 'L103', 'Friday', '15:30:00', '17:30:00', 'CS Thesis (2-hour lecture)', 4),
(1299, 283, '78', 'CS402', 'L103', 'Friday', '13:00:00', '15:00:00', 'Machine Learning (2-hour lecture)', 4),
(1300, 278, '78', 'CS402', 'L101', 'Thursday', '08:00:00', '10:00:00', 'Machine Learning (2-hour lecture)', 4),
(1301, 280, '78', 'CS402', 'L102', 'Monday', '13:00:00', '15:00:00', 'Compiler Design (2-hour lecture)', 4),
(1302, 281, '78', 'CS402', 'L103', 'Saturday', '10:00:00', '12:00:00', 'Algorithms (2-hour lecture)', 4),
(1303, 271, '78', 'CS402', 'L103', 'Wednesday', '13:00:00', '16:00:00', 'Algorithms (3-hour laboratory)', 4),
(1304, 285, '78', 'CS402', 'L103', 'Monday', '09:00:00', '11:00:00', 'CS Thesis (2-hour lecture)', 4),
(1305, 270, '78', 'CS402', 'L201', 'Saturday', '07:00:00', '10:00:00', 'Algorithms (3-hour laboratory)', 4),
(1306, 277, '78', 'CS402', 'L103', 'Monday', '19:00:00', '21:00:00', 'Deep Learning (2-hour lecture)', 4),
(1307, 278, '78', 'CS402', 'L103', 'Friday', '07:00:00', '10:00:00', 'Operating Systems (3-hour class)', 4),
(1308, 272, '78', 'CS403', 'L103', 'Tuesday', '07:30:00', '10:30:00', 'Algorithms (3-hour class)', 4),
(1309, 283, '78', 'CS403', 'L101', 'Tuesday', '19:00:00', '21:00:00', 'Computer Vision (2-hour lecture)', 4),
(1310, 282, '78', 'CS403', 'L103', 'Monday', '13:00:00', '15:00:00', 'Software Design (2-hour lecture)', 4),
(1311, 285, '78', 'CS403', 'L201', 'Saturday', '14:30:00', '17:30:00', 'Machine Learning (3-hour class)', 4),
(1312, 281, '78', 'CS403', 'L101', 'Thursday', '13:30:00', '16:30:00', 'Data Science (3-hour class)', 4),
(1313, 269, '78', 'CS403', 'L103', 'Monday', '07:00:00', '09:00:00', 'Operating Systems (2-hour lecture)', 4),
(1314, 286, '78', 'CS403', 'L201', 'Friday', '07:30:00', '10:30:00', 'Data Science (3-hour laboratory)', 4),
(1315, 280, '78', 'CS403', 'L201', 'Tuesday', '16:00:00', '19:00:00', 'Theory of Computation (3-hour class)', 4),
(1316, 282, '78', 'CS403', 'L201', 'Wednesday', '09:00:00', '12:00:00', 'AI Research (3-hour laboratory)', 4),
(1317, 271, '78', 'CS403', 'L201', 'Friday', '16:30:00', '19:30:00', 'Data Science (3-hour laboratory)', 4),
(1318, 278, '78', 'CS301', 'L201', 'Friday', '13:00:00', '16:00:00', 'Software Design (3-hour laboratory)', 3),
(1319, 277, '78', 'CS301', 'L202', 'Saturday', '14:00:00', '17:00:00', 'Theory of Computation (3-hour class)', 3),
(1320, 283, '78', 'CS301', 'L202', 'Saturday', '18:00:00', '21:00:00', 'Computer Vision (3-hour class)', 3),
(1321, 272, '78', 'CS301', 'L103', 'Wednesday', '16:00:00', '18:00:00', 'Software Design (2-hour lecture)', 3),
(1322, 286, '78', 'CS301', 'L102', 'Thursday', '13:30:00', '16:30:00', 'CS Thesis (3-hour class)', 3),
(1323, 280, '78', 'CS301', 'L201', 'Tuesday', '08:00:00', '11:00:00', 'Operating Systems (3-hour class)', 3),
(1324, 285, '78', 'CS301', 'L202', 'Friday', '09:00:00', '12:00:00', 'AI Research (3-hour class)', 3),
(1325, 269, '78', 'CS301', 'L102', 'Tuesday', '19:00:00', '21:00:00', 'Data Science (2-hour lecture)', 3),
(1326, 281, '78', 'CS301', 'L202', 'Friday', '16:00:00', '19:00:00', 'CS Thesis (3-hour class)', 3),
(1327, 270, '78', 'CS301', 'L201', 'Wednesday', '18:00:00', '21:00:00', 'Algorithms (3-hour class)', 3),
(1328, 272, '78', 'CS302', 'L202', 'Friday', '13:00:00', '16:00:00', 'Algorithms (3-hour class)', 3),
(1329, 277, '78', 'CS302', 'L103', 'Tuesday', '13:30:00', '16:30:00', 'AI Research (3-hour laboratory)', 3),
(1330, 283, '78', 'CS302', 'L103', 'Saturday', '13:00:00', '15:00:00', 'Natural Language Processing (2-hour lecture)', 3),
(1331, 282, '78', 'CS302', 'L201', 'Monday', '18:00:00', '21:00:00', 'Computer Vision (3-hour class)', 3),
(1332, 278, '78', 'CS302', 'L203', 'Friday', '17:30:00', '20:30:00', 'Compiler Design (3-hour class)', 3),
(1333, 269, '78', 'CS302', 'L103', 'Friday', '10:00:00', '12:00:00', 'Deep Learning (2-hour lecture)', 3),
(1334, 270, '78', 'CS302', 'L203', 'Friday', '07:00:00', '10:00:00', 'Software Design (3-hour class)', 3),
(1335, 280, '78', 'CS302', 'L102', 'Thursday', '08:30:00', '11:30:00', 'Data Science (3-hour laboratory)', 3),
(1336, 286, '78', 'CS302', 'L202', 'Wednesday', '18:00:00', '21:00:00', 'CS Thesis (3-hour class)', 3),
(1337, 285, '78', 'CS302', 'C601', 'Saturday', '17:30:00', '20:30:00', 'Theory of Computation (3-hour laboratory)', 3),
(1338, 281, '78', 'CS303', 'L201', 'Monday', '13:30:00', '16:30:00', 'CS Thesis (3-hour class)', 3),
(1339, 283, '78', 'CS303', 'L102', 'Thursday', '18:00:00', '21:00:00', 'Natural Language Processing (3-hour class)', 3),
(1340, 271, '78', 'CS303', 'L101', 'Thursday', '10:00:00', '12:00:00', 'Data Science (2-hour lecture)', 3),
(1341, 277, '78', 'CS303', 'L202', 'Saturday', '08:00:00', '11:00:00', 'Software Design (3-hour laboratory)', 3),
(1342, 271, '78', 'CS303', 'L201', 'Monday', '07:30:00', '09:30:00', 'Computer Vision (2-hour lecture)', 3),
(1343, 282, '78', 'CS303', 'L202', 'Friday', '07:00:00', '09:00:00', 'CS Thesis (2-hour lecture)', 3),
(1344, 269, '78', 'CS303', 'L202', 'Tuesday', '08:00:00', '11:00:00', 'Operating Systems (3-hour class)', 3),
(1345, 278, '78', 'CS303', 'L202', 'Wednesday', '09:00:00', '12:00:00', 'Algorithms (3-hour laboratory)', 3),
(1346, 270, '78', 'CS303', 'L202', 'Monday', '17:00:00', '20:00:00', 'Theory of Computation (3-hour laboratory)', 3),
(1347, 286, '78', 'CS303', 'L203', 'Saturday', '19:00:00', '21:00:00', 'AI Research (2-hour lecture)', 3),
(1348, 285, '78', 'CS303', 'L203', 'Friday', '13:00:00', '16:00:00', 'Theory of Computation (3-hour laboratory)', 3),
(1349, 280, '80', 'IT301', 'C601', 'Friday', '13:30:00', '16:30:00', 'Database Systems (3-hour laboratory)', 3),
(1350, 272, '80', 'IT301', 'L202', 'Tuesday', '15:30:00', '18:30:00', 'Networking (3-hour class)', 3),
(1351, 271, '80', 'IT301', 'L103', 'Thursday', '13:30:00', '16:30:00', 'Database Systems (3-hour class)', 3),
(1352, 281, '80', 'IT301', 'L203', 'Wednesday', '16:30:00', '19:30:00', 'Database Systems (3-hour laboratory)', 3),
(1353, 282, '80', 'IT301', 'L201', 'Wednesday', '07:00:00', '09:00:00', 'UI/UX Design (2-hour lecture)', 3),
(1354, 269, '80', 'IT301', 'C601', 'Friday', '08:00:00', '10:00:00', 'Cloud Computing (2-hour lecture)', 3),
(1355, 277, '80', 'IT301', 'L202', 'Monday', '13:30:00', '16:30:00', 'Database Systems (3-hour laboratory)', 3),
(1356, 272, '80', 'IT301', 'L203', 'Saturday', '13:00:00', '16:00:00', 'Cloud Computing (3-hour laboratory)', 3),
(1357, 283, '80', 'IT301', 'L203', 'Monday', '07:30:00', '10:30:00', 'IT Capstone (3-hour laboratory)', 3),
(1358, 286, '80', 'IT301', 'L203', 'Saturday', '07:00:00', '10:00:00', 'Database Systems (3-hour laboratory)', 3),
(1359, 270, '80', 'IT302', 'C602', 'Saturday', '18:00:00', '21:00:00', 'IT Capstone (3-hour class)', 3),
(1360, 282, '80', 'IT302', 'L103', 'Thursday', '07:00:00', '10:00:00', 'Mobile Development (3-hour class)', 3),
(1361, 269, '80', 'IT302', 'C602', 'Friday', '13:00:00', '16:00:00', 'UI/UX Design (3-hour laboratory)', 3),
(1362, 278, '80', 'IT302', 'L103', 'Thursday', '18:00:00', '21:00:00', 'Systems Development (3-hour class)', 3),
(1363, 285, '80', 'IT302', 'L203', 'Wednesday', '09:00:00', '12:00:00', 'Database Systems (3-hour class)', 3),
(1364, 271, '80', 'IT302', 'L201', 'Saturday', '10:00:00', '12:00:00', 'Project Management (2-hour lecture)', 3),
(1365, 283, '80', 'IT302', 'C601', 'Saturday', '08:00:00', '10:00:00', 'Database Systems (2-hour lecture)', 3),
(1366, 280, '80', 'IT302', 'C602', 'Friday', '18:00:00', '21:00:00', 'UI/UX Design (3-hour class)', 3),
(1367, 281, '80', 'IT302', 'C601', 'Monday', '07:30:00', '10:30:00', 'Database Systems (3-hour laboratory)', 3),
(1368, 272, '80', 'IT302', 'C603', 'Friday', '07:00:00', '10:00:00', 'Web Development (3-hour laboratory)', 3),
(1369, 286, '80', 'IT303', 'C603', 'Friday', '17:30:00', '20:30:00', 'Database Systems (3-hour class)', 3),
(1370, 277, '80', 'IT303', 'L203', 'Tuesday', '07:00:00', '09:00:00', 'Systems Development (2-hour lecture)', 3),
(1371, 271, '80', 'IT303', 'L201', 'Tuesday', '19:00:00', '21:00:00', 'Project Management (2-hour lecture)', 3),
(1372, 278, '80', 'IT303', 'L201', 'Thursday', '15:00:00', '18:00:00', 'Networking (3-hour class)', 3),
(1373, 283, '80', 'IT303', 'L103', 'Thursday', '10:00:00', '12:00:00', 'Cloud Computing (2-hour lecture)', 3),
(1374, 269, '80', 'IT303', 'L201', 'Thursday', '07:00:00', '10:00:00', 'Cloud Computing (3-hour laboratory)', 3),
(1375, 280, '80', 'IT303', 'L103', 'Monday', '15:00:00', '17:00:00', 'IT Capstone (2-hour lecture)', 3),
(1376, 270, '80', 'IT303', 'L201', 'Thursday', '18:00:00', '21:00:00', 'Cybersecurity (3-hour class)', 3),
(1377, 277, '80', 'IT303', 'C603', 'Saturday', '17:00:00', '20:00:00', 'Software Testing (3-hour class)', 3),
(1378, 285, '80', 'IT303', 'L203', 'Wednesday', '13:30:00', '16:30:00', 'Project Management (3-hour laboratory)', 3),
(1379, 281, '80', 'IT303', 'C601', 'Monday', '18:00:00', '21:00:00', 'DevOps (3-hour laboratory)', 3),
(1380, 286, '78', 'CS201', 'L202', 'Monday', '10:00:00', '12:00:00', 'Operating Systems (2-hour lecture)', 2),
(1381, 271, '78', 'CS201', 'C601', 'Saturday', '13:00:00', '16:00:00', 'Data Science (3-hour class)', 2),
(1382, 282, '78', 'CS201', 'L101', 'Thursday', '19:00:00', '21:00:00', 'Operating Systems (2-hour lecture)', 2),
(1383, 272, '78', 'CS201', 'C609', 'Saturday', '18:00:00', '21:00:00', 'Natural Language Processing (3-hour class)', 2),
(1384, 280, '78', 'CS201', 'L202', 'Tuesday', '19:00:00', '21:00:00', 'AI Research (2-hour lecture)', 2),
(1385, 278, '78', 'CS201', 'L203', 'Monday', '15:00:00', '17:00:00', 'CS Thesis (2-hour lecture)', 2),
(1386, 283, '78', 'CS201', 'L202', 'Thursday', '07:00:00', '10:00:00', 'Software Design (3-hour laboratory)', 2),
(1387, 282, '78', 'CS201', 'L203', 'Saturday', '10:00:00', '12:00:00', 'AI Research (2-hour lecture)', 2),
(1388, 277, '78', 'CS201', 'C601', 'Wednesday', '17:30:00', '20:30:00', 'Data Science (3-hour laboratory)', 2),
(1389, 285, '78', 'CS201', 'C609', 'Friday', '18:00:00', '21:00:00', 'Operating Systems (3-hour laboratory)', 2),
(1390, 286, '78', 'CS201', 'C602', 'Monday', '18:00:00', '21:00:00', 'Natural Language Processing (3-hour class)', 2),
(1391, 282, '78', 'CS202', 'L202', 'Thursday', '13:00:00', '16:00:00', 'CS Thesis (3-hour laboratory)', 2),
(1392, 270, '78', 'CS202', 'L202', 'Tuesday', '13:00:00', '15:00:00', 'Theory of Computation (2-hour lecture)', 2),
(1393, 269, '78', 'CS202', 'L203', 'Tuesday', '16:00:00', '19:00:00', 'Computer Vision (3-hour class)', 2),
(1394, 271, '78', 'CS202', 'L203', 'Friday', '10:00:00', '12:00:00', 'Natural Language Processing (2-hour lecture)', 2),
(1395, 281, '78', 'CS202', 'C602', 'Saturday', '14:30:00', '16:30:00', 'Machine Learning (2-hour lecture)', 2),
(1396, 270, '78', 'CS202', 'C602', 'Tuesday', '08:30:00', '11:30:00', 'Computer Vision (3-hour class)', 2),
(1397, 280, '78', 'CS202', 'C602', 'Saturday', '09:00:00', '12:00:00', 'CS Thesis (3-hour class)', 2),
(1398, 278, '78', 'CS202', 'C603', 'Monday', '17:30:00', '20:30:00', 'Computer Vision (3-hour class)', 2),
(1399, 283, '78', 'CS202', 'C602', 'Monday', '14:30:00', '17:30:00', 'Theory of Computation (3-hour laboratory)', 2),
(1400, 272, '78', 'CS202', 'L203', 'Thursday', '18:00:00', '21:00:00', 'Software Design (3-hour laboratory)', 2),
(1401, 277, '78', 'CS203', 'C602', 'Monday', '08:00:00', '11:00:00', 'Deep Learning (3-hour laboratory)', 2),
(1402, 285, '78', 'CS203', 'C601', 'Tuesday', '17:30:00', '20:30:00', 'Machine Learning (3-hour class)', 2),
(1403, 271, '78', 'CS203', 'C603', 'Saturday', '07:30:00', '09:30:00', 'Software Design (2-hour lecture)', 2),
(1404, 286, '78', 'CS203', 'L203', 'Tuesday', '13:00:00', '16:00:00', 'Computer Vision (3-hour class)', 2),
(1405, 269, '78', 'CS203', 'C601', 'Wednesday', '14:00:00', '17:00:00', 'Data Science (3-hour laboratory)', 2),
(1406, 280, '78', 'CS203', 'L203', 'Thursday', '13:00:00', '16:00:00', 'Natural Language Processing (3-hour laboratory)', 2),
(1407, 282, '78', 'CS203', 'L202', 'Friday', '19:00:00', '21:00:00', 'Theory of Computation (2-hour lecture)', 2),
(1408, 281, '78', 'CS203', 'C601', 'Wednesday', '08:00:00', '11:00:00', 'Operating Systems (3-hour laboratory)', 2),
(1409, 270, '78', 'CS203', 'L203', 'Thursday', '09:00:00', '12:00:00', 'Algorithms (3-hour laboratory)', 2),
(1410, 278, '78', 'CS203', 'C603', 'Saturday', '13:30:00', '16:30:00', 'Deep Learning (3-hour class)', 2),
(1411, 272, '80', 'IT201', 'L201', 'Thursday', '10:00:00', '12:00:00', 'Web Development (2-hour lecture)', 2),
(1412, 283, '80', 'IT202', 'C601', 'Tuesday', '07:00:00', '09:00:00', 'Mobile Development (2-hour lecture)', 2);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
