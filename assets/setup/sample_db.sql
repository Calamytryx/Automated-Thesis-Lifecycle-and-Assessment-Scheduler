CREATE DATABASE  IF NOT EXISTS `coecsa_thesis` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `coecsa_thesis`;
-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: coecsa_thesis
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auth_tokens`
--

DROP TABLE IF EXISTS `auth_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `auth_type` varchar(255) NOT NULL,
  `selector` text NOT NULL,
  `token` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_tokens`
--

LOCK TABLES `auth_tokens` WRITE;
/*!40000 ALTER TABLE `auth_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `defense_panelists`
--

DROP TABLE IF EXISTS `defense_panelists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `defense_panelists` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `defense_id` int(11) unsigned NOT NULL,
  `panelist_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `defense_id` (`defense_id`),
  KEY `panelist_id` (`panelist_id`),
  CONSTRAINT `defense_panelists_ibfk_1` FOREIGN KEY (`defense_id`) REFERENCES `defense_schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `defense_panelists_ibfk_2` FOREIGN KEY (`panelist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `defense_panelists`
--

LOCK TABLES `defense_panelists` WRITE;
/*!40000 ALTER TABLE `defense_panelists` DISABLE KEYS */;
/*!40000 ALTER TABLE `defense_panelists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `defense_schedules`
--

DROP TABLE IF EXISTS `defense_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `defense_schedules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(11) unsigned DEFAULT NULL,
  `panelist_id` int(11) unsigned DEFAULT NULL,
  `panelist_id2` int(11) unsigned DEFAULT NULL,
  `panelist_id3` int(11) unsigned DEFAULT NULL,
  `schedule_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `panelist_id` (`panelist_id`),
  KEY `panelist_id2` (`panelist_id2`),
  KEY `panelist_id3` (`panelist_id3`),
  KEY `fk_defense_schedules_team` (`team_id`),
  CONSTRAINT `defense_schedules_ibfk_2` FOREIGN KEY (`panelist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `defense_schedules_ibfk_3` FOREIGN KEY (`panelist_id2`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `defense_schedules_ibfk_4` FOREIGN KEY (`panelist_id3`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_defense_schedules_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `defense_schedules`
--

LOCK TABLES `defense_schedules` WRITE;
/*!40000 ALTER TABLE `defense_schedules` DISABLE KEYS */;
INSERT INTO `defense_schedules` VALUES (1,1,59,62,65,'2024-12-10','10:00:00','11:00:00','Defense Room B','scheduled','2024-10-13 08:02:26'),(2,2,60,64,67,'2024-12-10','14:00:00','15:00:00','Defense Room A','scheduled','2024-10-13 08:02:26'),(3,3,64,65,66,'2024-12-10','13:00:00','14:00:00','Defense Room A','scheduled','2024-10-13 08:02:26'),(4,4,60,62,67,'2024-12-10','14:00:00','15:00:00','Defense Room A','scheduled','2024-10-13 08:02:26'),(5,5,61,65,66,'2024-12-14','09:00:00','10:00:00','Defense Room A','scheduled','2024-10-13 08:02:26'),(6,6,60,65,66,'2024-12-09','11:00:00','12:00:00','Defense Room B','scheduled','2024-10-13 08:02:26'),(7,7,58,59,63,'2024-12-14','14:00:00','15:00:00','Defense Room B','scheduled','2024-10-13 08:02:26');
/*!40000 ALTER TABLE `defense_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `env_variables`
--

DROP TABLE IF EXISTS `env_variables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `env_variables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `env_variables`
--

LOCK TABLES `env_variables` WRITE;
/*!40000 ALTER TABLE `env_variables` DISABLE KEYS */;
INSERT INTO `env_variables` VALUES (1,'APP_NAME','ATLAS','Application name'),(2,'APP_ORGANIZATION','LPU-C CoECSA','Organization name'),(3,'APP_OWNER','120ms','Application owner'),(4,'APP_DESCRIPTION','Advanced Thesis Logistics and AI System for LPU','Application description'),(5,'ALLOWED_INACTIVITY_TIME','3600','Allowed inactivity time in seconds'),(6,'DB_DATABASE','coecsa_thesis','Database name'),(7,'DB_HOST','127.0.0.1','Database host'),(8,'DB_USERNAME','root','Database username'),(9,'DB_PASSWORD','','Database password'),(10,'DB_PORT','3306','Database port'),(11,'MAIL_HOST','smtp.gmail.com','Mail host'),(12,'MAIL_USERNAME','ton.agustin09@gmail.com','Mail username'),(13,'MAIL_PASSWORD','rdrc cinf leli xdms','Mail password'),(14,'MAIL_ENCRYPTION','ssl','Mail encryption'),(15,'MAIL_PORT','465','Mail port');
/*!40000 ALTER TABLE `env_variables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluation_details`
--

DROP TABLE IF EXISTS `evaluation_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluation_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `evaluation_id` int(11) unsigned DEFAULT NULL,
  `criterion_id` int(11) unsigned DEFAULT NULL,
  `score` float DEFAULT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluation_id` (`evaluation_id`),
  KEY `criterion_id` (`criterion_id`),
  CONSTRAINT `evaluation_details_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluation_details_ibfk_2` FOREIGN KEY (`criterion_id`) REFERENCES `rubric_criteria` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluation_details`
--

LOCK TABLES `evaluation_details` WRITE;
/*!40000 ALTER TABLE `evaluation_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `evaluation_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `defense_schedule_id` int(11) unsigned DEFAULT NULL,
  `evaluator_id` int(11) unsigned DEFAULT NULL,
  `total_score` float DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `recommendation` enum('pass','fail','revise') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `defense_schedule_id` (`defense_schedule_id`),
  KEY `evaluator_id` (`evaluator_id`),
  CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`defense_schedule_id`) REFERENCES `defense_schedules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluations`
--

LOCK TABLES `evaluations` WRITE;
/*!40000 ALTER TABLE `evaluations` DISABLE KEYS */;
/*!40000 ALTER TABLE `evaluations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requirements`
--

DROP TABLE IF EXISTS `requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `requirements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requirements`
--

LOCK TABLES `requirements` WRITE;
/*!40000 ALTER TABLE `requirements` DISABLE KEYS */;
/*!40000 ALTER TABLE `requirements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `research_titles`
--

DROP TABLE IF EXISTS `research_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `research_titles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  CONSTRAINT `research_titles_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `research_titles`
--

LOCK TABLES `research_titles` WRITE;
/*!40000 ALTER TABLE `research_titles` DISABLE KEYS */;
INSERT INTO `research_titles` VALUES (1,1,'Analysis of Machine Learning Algorithms in Predictive Maintenance','2024-11-15 01:00:00','2024-10-13 07:15:44','2024-10-13 07:15:44'),(2,2,'Sustainable Urban Planning: A Case Study of Green Cities','2024-11-16 02:30:00','2024-10-13 07:15:44','2024-10-13 07:15:44'),(3,3,'The Impact of Social Media on Mental Health in Adolescents','2024-11-17 03:45:00','2024-10-13 07:15:44','2024-10-13 07:15:44'),(4,4,'Renewable Energy Integration in Smart Grids','2024-11-18 06:00:00','2024-10-13 07:15:44','2024-10-13 07:15:44'),(5,5,'Cybersecurity Challenges in Internet of Things (IoT) Devices','2024-11-19 07:30:00','2024-10-13 07:15:44','2024-10-13 07:15:44'),(6,6,'The Role of Artificial Intelligence in Healthcare Diagnostics',NULL,'2024-10-13 07:15:44','2024-10-13 07:15:44'),(7,7,'Blockchain Technology in Supply Chain Management','2024-11-20 05:15:00','2024-10-13 07:16:51','2024-10-13 07:16:51');
/*!40000 ALTER TABLE `research_titles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rubric_criteria`
--

DROP TABLE IF EXISTS `rubric_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rubric_criteria` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rubric_id` int(11) unsigned DEFAULT NULL,
  `criterion` varchar(255) NOT NULL,
  `max_score` int(11) DEFAULT NULL,
  `weight` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rubric_id` (`rubric_id`),
  CONSTRAINT `rubric_criteria_ibfk_1` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rubric_criteria`
--

LOCK TABLES `rubric_criteria` WRITE;
/*!40000 ALTER TABLE `rubric_criteria` DISABLE KEYS */;
/*!40000 ALTER TABLE `rubric_criteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rubrics`
--

DROP TABLE IF EXISTS `rubrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rubrics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `rubrics_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rubrics`
--

LOCK TABLES `rubrics` WRITE;
/*!40000 ALTER TABLE `rubrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `rubrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_members`
--

DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_members` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_members`
--

LOCK TABLES `team_members` WRITE;
/*!40000 ALTER TABLE `team_members` DISABLE KEYS */;
INSERT INTO `team_members` VALUES (1,1,58,'adviser'),(2,1,38,'leader'),(3,1,39,'member'),(4,1,40,'member'),(5,2,59,'adviser'),(6,2,41,'leader'),(7,2,42,'member'),(8,2,43,'member'),(9,3,60,'adviser'),(10,3,44,'leader'),(11,3,45,'member'),(12,3,46,'member'),(13,4,61,'adviser'),(14,4,47,'leader'),(15,4,48,'member'),(16,4,49,'member'),(17,5,62,'adviser'),(18,5,50,'leader'),(19,5,51,'member'),(20,5,52,'member'),(21,6,63,'adviser'),(22,6,53,'leader'),(23,6,54,'member'),(24,6,55,'member'),(25,7,64,'adviser'),(26,7,56,'leader'),(27,7,57,'member');
/*!40000 ALTER TABLE `team_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'Team1','2024-10-13 06:58:29','Analysis of Machine Learning Algorithms in Predictive Maintenance'),(2,'Team2','2024-10-13 06:58:29','Sustainable Urban Planning: A Case Study of Green Cities'),(3,'Team3','2024-10-13 06:58:29','The Impact of Social Media on Mental Health in Adolescents'),(4,'Team4','2024-10-13 06:58:29','Renewable Energy Integration in Smart Grids'),(5,'Team5','2024-10-13 06:58:29','Cybersecurity Challenges in Internet of Things (IoT) Devices'),(6,'Team6','2024-10-13 06:58:29','The Role of Artificial Intelligence in Healthcare Diagnostics'),(7,'Team7','2024-10-13 06:58:29','Blockchain Technology in Supply Chain Management');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thesis_topics`
--

DROP TABLE IF EXISTS `thesis_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thesis_topics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `topic` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `suggested_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thesis_topics`
--

LOCK TABLES `thesis_topics` WRITE;
/*!40000 ALTER TABLE `thesis_topics` DISABLE KEYS */;
/*!40000 ALTER TABLE `thesis_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_requirements`
--

DROP TABLE IF EXISTS `user_requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_requirements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `requirement_id` int(11) unsigned DEFAULT NULL,
  `status` enum('pending','submitted','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `requirement_id` (`requirement_id`),
  CONSTRAINT `user_requirements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_requirements_ibfk_2` FOREIGN KEY (`requirement_id`) REFERENCES `requirements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_requirements`
--

LOCK TABLES `user_requirements` WRITE;
/*!40000 ALTER TABLE `user_requirements` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_requirements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_schedules`
--

DROP TABLE IF EXISTS `user_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_schedules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `class_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_schedules`
--

LOCK TABLES `user_schedules` WRITE;
/*!40000 ALTER TABLE `user_schedules` DISABLE KEYS */;
INSERT INTO `user_schedules` VALUES (6,38,'Tuesday','13:00:00','16:00:00','English'),(7,38,'Wednesday','08:00:00','11:00:00','History'),(8,38,'Monday','10:00:00','14:00:00','Literature'),(9,38,'Friday','15:00:00','18:00:00','Math'),(10,38,'Wednesday','16:00:00','20:00:00','Economics'),(11,39,'Wednesday','12:00:00','15:00:00','English'),(12,39,'Friday','18:00:00','23:00:00','Economics'),(13,39,'Monday','15:00:00','19:00:00','History'),(14,40,'Wednesday','17:00:00','21:00:00','English'),(15,40,'Tuesday','13:00:00','18:00:00','Programming'),(16,40,'Monday','07:00:00','11:00:00','History'),(17,41,'Monday','14:00:00','17:00:00','English'),(18,41,'Thursday','08:00:00','11:00:00','Chemistry'),(19,41,'Wednesday','14:00:00','18:00:00','English'),(20,41,'Tuesday','11:00:00','15:00:00','Literature'),(21,41,'Friday','11:00:00','14:00:00','Physics'),(22,42,'Friday','17:00:00','20:00:00','Chemistry'),(23,42,'Monday','14:00:00','17:00:00','Physics'),(24,42,'Thursday','14:00:00','19:00:00','Math'),(25,42,'Tuesday','13:00:00','18:00:00','Programming'),(26,43,'Wednesday','19:00:00','22:00:00','Programming'),(27,43,'Monday','07:00:00','10:00:00','Literature'),(28,43,'Thursday','18:00:00','22:00:00','Science'),(29,44,'Friday','10:00:00','13:00:00','Programming'),(30,44,'Saturday','11:00:00','14:00:00','Chemistry'),(31,44,'Saturday','15:00:00','20:00:00','English'),(32,44,'Thursday','08:00:00','12:00:00','Economics'),(33,44,'Friday','10:00:00','14:00:00','Programming'),(34,45,'Wednesday','16:00:00','20:00:00','English'),(35,45,'Tuesday','15:00:00','18:00:00','Chemistry'),(36,45,'Thursday','19:00:00','24:00:00','History'),(37,45,'Monday','16:00:00','21:00:00','Literature'),(38,46,'Thursday','12:00:00','15:00:00','Literature'),(39,46,'Friday','09:00:00','14:00:00','Programming'),(40,46,'Wednesday','17:00:00','22:00:00','Physics'),(41,46,'Wednesday','17:00:00','20:00:00','History'),(42,47,'Friday','10:00:00','13:00:00','Economics'),(43,47,'Saturday','12:00:00','16:00:00','Literature'),(44,47,'Wednesday','11:00:00','14:00:00','History'),(45,47,'Saturday','09:00:00','12:00:00','English'),(46,47,'Friday','10:00:00','14:00:00','Economics'),(47,48,'Monday','08:00:00','11:00:00','Chemistry'),(48,48,'Thursday','07:00:00','12:00:00','English'),(49,48,'Saturday','09:00:00','12:00:00','Economics'),(50,49,'Saturday','07:00:00','12:00:00','Economics'),(51,49,'Monday','09:00:00','14:00:00','Economics'),(52,49,'Friday','12:00:00','17:00:00','Chemistry'),(53,50,'Thursday','13:00:00','16:00:00','Literature'),(54,50,'Thursday','10:00:00','13:00:00','Chemistry'),(55,50,'Wednesday','10:00:00','14:00:00','English'),(56,51,'Thursday','19:00:00','23:00:00','English'),(57,51,'Saturday','19:00:00','23:00:00','History'),(58,51,'Saturday','19:00:00','22:00:00','Literature'),(59,51,'Friday','11:00:00','15:00:00','Science'),(60,51,'Friday','07:00:00','10:00:00','Science'),(61,52,'Saturday','13:00:00','18:00:00','Literature'),(62,52,'Thursday','17:00:00','22:00:00','Literature'),(63,52,'Wednesday','08:00:00','12:00:00','English'),(64,52,'Tuesday','16:00:00','19:00:00','Chemistry'),(65,52,'Monday','13:00:00','18:00:00','Physics'),(66,53,'Wednesday','13:00:00','18:00:00','Chemistry'),(67,53,'Tuesday','12:00:00','16:00:00','Programming'),(68,53,'Saturday','13:00:00','18:00:00','Programming'),(69,53,'Monday','08:00:00','13:00:00','Literature'),(70,54,'Saturday','14:00:00','19:00:00','Science'),(71,54,'Friday','19:00:00','22:00:00','Economics'),(72,54,'Wednesday','15:00:00','19:00:00','History'),(73,54,'Thursday','10:00:00','14:00:00','Literature'),(74,54,'Tuesday','15:00:00','18:00:00','Chemistry'),(75,55,'Friday','13:00:00','17:00:00','Programming'),(76,55,'Saturday','16:00:00','20:00:00','Economics'),(77,55,'Friday','16:00:00','21:00:00','Literature'),(78,56,'Friday','19:00:00','24:00:00','History'),(79,56,'Saturday','13:00:00','18:00:00','History'),(80,56,'Thursday','13:00:00','18:00:00','Physics'),(81,57,'Saturday','17:00:00','20:00:00','Chemistry'),(82,57,'Wednesday','12:00:00','16:00:00','Math'),(83,57,'Saturday','16:00:00','21:00:00','Chemistry'),(84,57,'Tuesday','14:00:00','18:00:00','History'),(85,57,'Wednesday','07:00:00','12:00:00','Chemistry'),(86,58,'Thursday','13:00:00','17:00:00','Programming'),(87,58,'Monday','10:00:00','14:00:00','Literature'),(88,58,'Monday','19:00:00','24:00:00','Math'),(89,58,'Tuesday','14:00:00','18:00:00','Chemistry'),(90,58,'Saturday','11:00:00','14:00:00','Economics'),(91,59,'Friday','19:00:00','22:00:00','Economics'),(92,59,'Friday','17:00:00','22:00:00','Physics'),(93,59,'Thursday','16:00:00','21:00:00','Literature'),(94,59,'Tuesday','10:00:00','13:00:00','Chemistry'),(95,60,'Friday','07:00:00','10:00:00','Science'),(96,60,'Tuesday','16:00:00','19:00:00','Science'),(97,60,'Monday','10:00:00','14:00:00','Chemistry'),(98,60,'Thursday','19:00:00','22:00:00','Programming'),(99,61,'Wednesday','09:00:00','12:00:00','Science'),(100,61,'Saturday','08:00:00','11:00:00','Chemistry'),(101,61,'Monday','08:00:00','13:00:00','Economics'),(102,61,'Monday','16:00:00','20:00:00','Programming'),(103,62,'Monday','09:00:00','14:00:00','Math'),(104,62,'Tuesday','18:00:00','22:00:00','Programming'),(105,62,'Wednesday','12:00:00','15:00:00','Literature'),(106,62,'Thursday','08:00:00','12:00:00','Programming'),(107,62,'Tuesday','12:00:00','17:00:00','History'),(108,63,'Saturday','18:00:00','22:00:00','Economics'),(109,63,'Wednesday','15:00:00','20:00:00','English'),(110,63,'Tuesday','18:00:00','22:00:00','Literature'),(111,63,'Tuesday','08:00:00','11:00:00','Chemistry'),(112,64,'Wednesday','13:00:00','17:00:00','History'),(113,64,'Saturday','16:00:00','20:00:00','Programming'),(114,64,'Wednesday','13:00:00','18:00:00','Literature'),(115,65,'Wednesday','12:00:00','17:00:00','Literature'),(116,65,'Tuesday','13:00:00','17:00:00','Literature'),(117,65,'Saturday','12:00:00','16:00:00','Chemistry'),(118,65,'Friday','10:00:00','13:00:00','Math'),(119,66,'Saturday','11:00:00','14:00:00','Math'),(120,66,'Monday','16:00:00','21:00:00','Economics'),(121,66,'Thursday','14:00:00','17:00:00','Programming'),(122,67,'Tuesday','08:00:00','11:00:00','Physics'),(123,67,'Tuesday','08:00:00','11:00:00','Science'),(124,67,'Tuesday','18:00:00','23:00:00','Science'),(125,67,'Thursday','16:00:00','21:00:00','History');
/*!40000 ALTER TABLE `user_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
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
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,0,'winstonadmin','ton.agustin09@gmail.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Winston','Agustin','m','SUPER ADMIN','This is the bio of a supa hot user. Now i will say needless stuff to make this longer so this looks like a bio and not anything other than a bio.','6703b15c765f80.83029727.png','2024-10-05 13:55:38','2024-10-05 13:55:38','2024-10-13 11:28:54',NULL,'2024-10-13 11:28:54'),(35,0,'supahot','supa@hot.com','$2y$10$jhIOk4NVdBile/NwhAU9We/f0aoohx.cG9CizmIALRz0aCKJa5s6a','Supahot','Soverysupahot','m','Headline of a supa hot user','This is the bio of a supa hot user. Now i will say needless stuff to make this longer so this looks like a bio and not anything other than a bio.','_defaultUser.png',NULL,'2024-10-08 05:25:07','2024-10-08 05:25:07',NULL,NULL),(37,0,'neilv','neilvicedo.ih@gmail.com','$2y$10$3NRnm/wbLVuSxzSPDE92LObQmTkp.n3A4Ztk6eBEW7zXYVnNhNOaq','Niall','V','o','Basta programmer ako','?','_defaultUser.png','2024-10-08 13:14:14','2024-10-08 13:13:14','2024-10-08 13:15:31',NULL,'2024-10-08 13:14:37'),(38,1,'student1','student1@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','One','m','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 10:11:36',NULL,'2024-10-10 10:11:36'),(39,1,'student2','student2@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Two','f','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(40,1,'student3','student3@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Three','m','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-12 04:44:23',NULL,'2024-10-12 04:44:23'),(41,1,'student4','student4@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Four','f','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(42,1,'student5','student5@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Five','m','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(43,1,'student6','student6@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Six','f','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(44,1,'student7','student7@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Seven','m','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(45,1,'student8','student8@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Eight','f','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(46,1,'student9','student9@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Nine','m','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(47,1,'student10','student10@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Ten','f','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(48,1,'student11','student11@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Eleven','m','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(49,1,'student12','student12@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Twelve','f','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(50,1,'student13','student13@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Thirteen','m','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(51,1,'student14','student14@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Fourteen','f','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(52,1,'student15','student15@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Fifteen','m','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(53,1,'student16','student16@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Sixteen','f','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(54,1,'student17','student17@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Seventeen','m','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(55,1,'student18','student18@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Eighteen','f','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(56,1,'student19','student19@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Nineteen','m','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(57,1,'student20','student20@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Student','Twenty','f','Student Headline','This is a student bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(58,2,'staff1','staff1@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Staff','One','m','Staff Headline','This is a staff bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-12 04:42:12',NULL,'2024-10-12 04:42:12'),(59,2,'staff2','staff2@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Staff','Two','f','Staff Headline','This is a staff bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(60,2,'staff3','staff3@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Staff','Three','m','Staff Headline','This is a staff bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(61,2,'staff4','staff4@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Staff','Four','f','Staff Headline','This is a staff bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(62,2,'staff5','staff5@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Staff','Five','m','Staff Headline','This is a staff bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(63,2,'staff6','staff6@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Staff','Six','f','Staff Headline','This is a staff bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(64,2,'staff7','staff7@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Staff','Seven','m','Staff Headline','This is a staff bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(65,2,'staff8','staff8@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Staff','Eight','f','Staff Headline','This is a staff bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(66,2,'staff9','staff9@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Staff','Nine','m','Staff Headline','This is a staff bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06'),(67,2,'staff10','staff10@example.com','$2y$10$hyL9m74UHeQQJU9TtWMIW.NvwcLInFk2xhTQMK9f7Tkdi4591gy6K','Staff','Ten','f','Staff Headline','This is a staff bio.','_defaultUser.png','2024-10-10 06:07:06','2024-10-10 06:07:06','2024-10-10 06:07:06',NULL,'2024-10-10 06:07:06');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-13 19:48:53
