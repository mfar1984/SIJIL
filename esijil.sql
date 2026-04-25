-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 25, 2026 at 01:26 PM
-- Server version: 8.0.45
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `esijil`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint UNSIGNED NOT NULL,
  `log_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint UNSIGNED DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `attribute_changes` json DEFAULT NULL,
  `batch_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `attribute_changes`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(1, 'auth', 'User logged in successfully', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"email\": \"admin@e-certificate.com.my\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', NULL, NULL, '2026-04-16 07:14:03', '2026-04-16 07:14:03'),
(2, 'auth', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-16 10:25:50', '2026-04-16 10:25:50'),
(3, 'auth', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-16 10:26:26', '2026-04-16 10:26:26'),
(4, 'auth', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-16 10:28:23', '2026-04-16 10:28:23'),
(5, 'auth', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-16 10:28:36', '2026-04-16 10:28:36'),
(6, 'auth', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-16 10:29:50', '2026-04-16 10:29:50'),
(7, 'default', 'User updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-16 10:29:53', '2026-04-16 10:29:53'),
(8, 'auth', 'User logged in successfully', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"email\": \"admin@e-certificate.com.my\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-16 10:29:53', '2026-04-16 10:29:53'),
(9, 'default', 'Event created', 'App\\Models\\Event', 'created', 7, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"name\": \"Powerboat RACE 2026\", \"status\": \"active\", \"end_date\": \"2026-04-26 00:00:00\", \"location\": \"Pulau Li Hua Sibu\", \"start_date\": \"2026-04-24 00:00:00\", \"description\": null}}', NULL, '2026-04-16 10:40:43', '2026-04-16 10:40:43'),
(10, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-16 10:40:43', '2026-04-16 10:40:43'),
(11, 'default', 'Participant created', 'App\\Models\\Participant', 'created', 36, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"name\": \"MOHAMAD FAIZAN BIN ABDUL RAHMAN\", \"email\": \"faizanrahman84@gmail.com\", \"phone\": \"60178591411\", \"status\": \"active\", \"event_id\": 7}}', NULL, '2026-04-16 13:21:22', '2026-04-16 13:21:22'),
(12, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": {\"end_date\": \"2026-04-26 00:00:00\", \"start_date\": \"2026-04-24 00:00:00\"}, \"attributes\": {\"end_date\": \"2026-04-17 00:00:00\", \"start_date\": \"2026-04-16 00:00:00\"}}', NULL, '2026-04-16 13:46:07', '2026-04-16 13:46:07'),
(13, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": {\"start_date\": \"2026-04-16 00:00:00\"}, \"attributes\": {\"start_date\": \"2026-04-17 00:00:00\"}}', NULL, '2026-04-16 13:46:27', '2026-04-16 13:46:27'),
(14, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": {\"start_date\": \"2026-04-17 00:00:00\"}, \"attributes\": {\"start_date\": \"2026-04-16 00:00:00\"}}', NULL, '2026-04-16 13:46:40', '2026-04-16 13:46:40'),
(15, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": \"active\"}, \"attributes\": {\"status\": \"completed\"}}', NULL, '2026-04-16 13:46:54', '2026-04-16 13:46:54'),
(16, 'default', 'Certificate created', 'App\\Models\\Certificate', 'created', 20, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416220728-4DD10C\"}}', NULL, '2026-04-16 14:07:28', '2026-04-16 14:07:28'),
(17, 'default', 'Certificate deleted', 'App\\Models\\Certificate', 'deleted', 20, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416220728-4DD10C\"}}', NULL, '2026-04-16 14:14:38', '2026-04-16 14:14:38'),
(18, 'default', 'Certificate created', 'App\\Models\\Certificate', 'created', 21, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416221601-5B4F1F\"}}', NULL, '2026-04-16 14:16:01', '2026-04-16 14:16:01'),
(19, 'default', 'Certificate deleted', 'App\\Models\\Certificate', 'deleted', 21, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416221601-5B4F1F\"}}', NULL, '2026-04-16 14:19:07', '2026-04-16 14:19:07'),
(20, 'default', 'Certificate created', 'App\\Models\\Certificate', 'created', 22, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416221921-BE8B9C\"}}', NULL, '2026-04-16 14:19:21', '2026-04-16 14:19:21'),
(21, 'default', 'Certificate deleted', 'App\\Models\\Certificate', 'deleted', 22, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416221921-BE8B9C\"}}', NULL, '2026-04-16 14:24:47', '2026-04-16 14:24:47'),
(22, 'default', 'Certificate created', 'App\\Models\\Certificate', 'created', 23, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416222458-F3E88E\"}}', NULL, '2026-04-16 14:24:58', '2026-04-16 14:24:58'),
(23, 'default', 'Certificate deleted', 'App\\Models\\Certificate', 'deleted', 23, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416222458-F3E88E\"}}', NULL, '2026-04-16 14:33:53', '2026-04-16 14:33:53'),
(24, 'default', 'Certificate created', 'App\\Models\\Certificate', 'created', 24, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416223405-9BC7A1\"}}', NULL, '2026-04-16 14:34:05', '2026-04-16 14:34:05'),
(25, 'default', 'Certificate deleted', 'App\\Models\\Certificate', 'deleted', 24, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416223405-9BC7A1\"}}', NULL, '2026-04-16 14:37:37', '2026-04-16 14:37:37'),
(26, 'default', 'Certificate created', 'App\\Models\\Certificate', 'created', 25, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416223748-3B1D0A\"}}', NULL, '2026-04-16 14:37:48', '2026-04-16 14:37:48'),
(27, 'default', 'Certificate deleted', 'App\\Models\\Certificate', 'deleted', 25, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416223748-3B1D0A\"}}', NULL, '2026-04-16 15:52:21', '2026-04-16 15:52:21'),
(28, 'default', 'Certificate created', 'App\\Models\\Certificate', 'created', 26, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416235249-D73203\"}}', NULL, '2026-04-16 15:52:49', '2026-04-16 15:52:49'),
(29, 'default', 'Certificate deleted', 'App\\Models\\Certificate', 'deleted', 26, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260416235249-D73203\"}}', NULL, '2026-04-16 16:00:40', '2026-04-16 16:00:40'),
(30, 'default', 'Certificate created', 'App\\Models\\Certificate', 'created', 27, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260417001236-52FB9D\"}}', NULL, '2026-04-16 16:12:36', '2026-04-16 16:12:36'),
(31, 'default', 'Certificate deleted', 'App\\Models\\Certificate', 'deleted', 27, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260417001236-52FB9D\"}}', NULL, '2026-04-16 16:14:32', '2026-04-16 16:14:32'),
(32, 'default', 'Certificate created', 'App\\Models\\Certificate', 'created', 28, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260417001459-5EE020\"}}', NULL, '2026-04-16 16:14:59', '2026-04-16 16:14:59'),
(33, 'default', 'Certificate deleted', 'App\\Models\\Certificate', 'deleted', 28, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260417001459-5EE020\"}}', NULL, '2026-04-16 16:25:27', '2026-04-16 16:25:27'),
(34, 'default', 'Certificate created', 'App\\Models\\Certificate', 'created', 29, 'App\\Models\\User', 1, '[]', '{\"attributes\": {\"status\": null, \"event_id\": 7, \"template_id\": 14, \"participant_id\": 36, \"certificate_number\": \"CERT-20260417002656-C45D58\"}}', NULL, '2026-04-16 16:26:56', '2026-04-16 16:26:56'),
(35, 'default', 'Certificate updated', 'App\\Models\\Certificate', 'updated', 29, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-16 16:26:56', '2026-04-16 16:26:56'),
(36, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": {\"end_date\": \"2026-04-17 00:00:00\", \"start_date\": \"2026-04-16 00:00:00\"}, \"attributes\": {\"end_date\": \"2026-04-18 00:00:00\", \"start_date\": \"2026-04-17 00:00:00\"}}', NULL, '2026-04-17 03:11:36', '2026-04-17 03:11:36'),
(37, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-17 03:11:50', '2026-04-17 03:11:50'),
(38, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-17 03:12:02', '2026-04-17 03:12:02'),
(39, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": {\"start_date\": \"2026-04-17 00:00:00\"}, \"attributes\": {\"start_date\": \"2026-04-16 00:00:00\"}}', NULL, '2026-04-17 05:13:23', '2026-04-17 05:13:23'),
(40, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": {\"start_date\": \"2026-04-16 00:00:00\"}, \"attributes\": {\"start_date\": \"2026-04-18 00:00:00\"}}', NULL, '2026-04-17 05:13:45', '2026-04-17 05:13:45'),
(41, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": {\"start_date\": \"2026-04-18 00:00:00\"}, \"attributes\": {\"start_date\": \"2026-04-17 00:00:00\"}}', NULL, '2026-04-17 05:24:47', '2026-04-17 05:24:47'),
(42, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": {\"status\": \"completed\"}, \"attributes\": {\"status\": \"active\"}}', NULL, '2026-04-17 05:26:58', '2026-04-17 05:26:58'),
(43, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 05:53:35', '2026-04-17 05:53:35'),
(44, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 05:53:37', '2026-04-17 05:53:37'),
(45, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 05:53:42', '2026-04-17 05:53:42'),
(46, 'default', 'User updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-17 05:53:47', '2026-04-17 05:53:47'),
(47, 'auth', 'User logged in successfully', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"email\": \"admin@e-certificate.com.my\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 05:53:47', '2026-04-17 05:53:47'),
(48, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 7, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-17 10:26:50', '2026-04-17 10:26:50'),
(49, 'auth', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 16:27:01', '2026-04-17 16:27:01'),
(50, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 23:37:47', '2026-04-17 23:37:47'),
(51, 'default', 'User updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-17 23:37:55', '2026-04-17 23:37:55'),
(52, 'auth', 'User logged in successfully', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"email\": \"admin@e-certificate.com.my\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 23:37:55', '2026-04-17 23:37:55'),
(53, 'auth', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 23:38:03', '2026-04-17 23:38:03'),
(54, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 23:43:44', '2026-04-17 23:43:44'),
(55, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 23:43:47', '2026-04-17 23:43:47'),
(56, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 23:43:50', '2026-04-17 23:43:50'),
(57, 'default', 'User updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-17 23:43:55', '2026-04-17 23:43:55'),
(58, 'auth', 'User logged in successfully', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"email\": \"admin@e-certificate.com.my\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-17 23:43:55', '2026-04-17 23:43:55'),
(59, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-18 08:37:15', '2026-04-18 08:37:15'),
(60, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-18 08:37:18', '2026-04-18 08:37:18'),
(61, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-18 08:37:20', '2026-04-18 08:37:20'),
(62, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-18 08:37:26', '2026-04-18 08:37:26'),
(63, 'default', 'User updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-18 08:37:30', '2026-04-18 08:37:30'),
(64, 'auth', 'User logged in successfully', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"email\": \"admin@e-certificate.com.my\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-18 08:37:30', '2026-04-18 08:37:30'),
(65, 'auth', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-18 09:55:47', '2026-04-18 09:55:47'),
(66, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-18 10:08:53', '2026-04-18 10:08:53'),
(67, 'default', 'User updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-18 10:09:01', '2026-04-18 10:09:01'),
(68, 'auth', 'User logged in successfully', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"email\": \"admin@e-certificate.com.my\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-18 10:09:01', '2026-04-18 10:09:01'),
(69, 'auth', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-18 10:10:35', '2026-04-18 10:10:35'),
(70, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-18 10:10:40', '2026-04-18 10:10:40'),
(71, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-21 19:56:55', '2026-04-21 19:56:55'),
(72, 'default', 'User updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-21 19:57:00', '2026-04-21 19:57:00'),
(73, 'auth', 'User logged in successfully', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"email\": \"admin@e-certificate.com.my\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-21 19:57:00', '2026-04-21 19:57:00'),
(74, 'security', 'Failed login attempt', NULL, NULL, NULL, NULL, NULL, '{\"email\": \"admin@e-certificate.com.my\", \"reason\": \"Invalid credentials\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-22 14:12:59', '2026-04-22 14:12:59'),
(75, 'default', 'User updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-22 14:13:02', '2026-04-22 14:13:02'),
(76, 'auth', 'User logged in successfully', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"email\": \"admin@e-certificate.com.my\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-22 14:13:02', '2026-04-22 14:13:02'),
(77, 'auth', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '{\"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-22 14:13:24', '2026-04-22 14:13:24'),
(78, 'default', 'User updated', 'App\\Models\\User', 'updated', 2, 'App\\Models\\User', 2, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-22 14:13:29', '2026-04-22 14:13:29'),
(79, 'auth', 'User logged in successfully', NULL, NULL, NULL, 'App\\Models\\User', 2, '{\"email\": \"organizer@e-certificate.com.my\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\"}', '[]', NULL, '2026-04-22 14:13:29', '2026-04-22 14:13:29'),
(80, 'default', 'Event created', 'App\\Models\\Event', 'created', 8, 'App\\Models\\User', 2, '[]', '{\"attributes\": {\"name\": \"a\", \"status\": \"active\", \"end_date\": \"2026-04-25 00:00:00\", \"location\": \"aa\", \"start_date\": \"2026-04-24 00:00:00\", \"description\": \"a\"}}', NULL, '2026-04-22 16:32:04', '2026-04-22 16:32:04'),
(81, 'default', 'Event updated', 'App\\Models\\Event', 'updated', 8, 'App\\Models\\User', 2, '[]', '{\"old\": [], \"attributes\": []}', NULL, '2026-04-22 16:32:04', '2026-04-22 16:32:04'),
(82, 'default', 'Participant created', 'App\\Models\\Participant', 'created', 37, 'App\\Models\\User', 2, '[]', '{\"attributes\": {\"name\": \"MOHAMAD FAIZAN BIN ABDUL RAHMAN\", \"email\": \"faizanrahman84@gmail.com\", \"phone\": \"60178591411\", \"status\": \"active\", \"event_id\": 8}}', NULL, '2026-04-22 16:32:33', '2026-04-22 16:32:33'),
(83, 'default', 'Participant updated', 'App\\Models\\Participant', 'updated', 37, 'App\\Models\\User', 2, '[]', '{\"old\": {\"name\": \"MOHAMAD FAIZAN BIN ABDUL RAHMAN\"}, \"attributes\": {\"name\": \"Mohamad Faizan Bin Abdul Rahman\"}}', NULL, '2026-04-22 16:46:32', '2026-04-22 16:46:32'),
(84, 'default', 'Participant updated', 'App\\Models\\Participant', 'updated', 37, 'App\\Models\\User', 2, '[]', '{\"old\": {\"name\": \"Mohamad Faizan Bin Abdul Rahman\"}, \"attributes\": {\"name\": \"MOHAMAD FAIZAN BIN ABDUL RAHMAN\"}}', NULL, '2026-04-22 16:52:41', '2026-04-22 16:52:41');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `unique_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','expired','completed','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `event_id`, `name`, `date`, `start_time`, `end_time`, `unique_code`, `created_by`, `created_at`, `updated_at`, `status`) VALUES
(8, 7, NULL, '2026-04-18', '08:00:00', '17:00:00', 'HxOChz2Ehx0BNx2OkQ0KYLNopjE7qDZk', 1, '2026-04-17 09:23:23', '2026-04-17 09:23:23', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` bigint UNSIGNED NOT NULL,
  `attendance_id` bigint UNSIGNED NOT NULL,
  `attendance_session_id` bigint UNSIGNED DEFAULT NULL,
  `participant_id` bigint UNSIGNED NOT NULL,
  `checkin_lat` decimal(10,7) DEFAULT NULL,
  `checkin_lng` decimal(10,7) DEFAULT NULL,
  `checkout_lat` decimal(10,7) DEFAULT NULL,
  `checkout_lng` decimal(10,7) DEFAULT NULL,
  `checkin_time` timestamp NULL DEFAULT NULL,
  `checkout_time` timestamp NULL DEFAULT NULL,
  `timestamp` timestamp NOT NULL,
  `status` enum('present','absent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `scanned_by_device` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_sessions`
--

CREATE TABLE `attendance_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `attendance_id` bigint UNSIGNED NOT NULL,
  `unique_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'checkin',
  `date` date NOT NULL,
  `checkin_start_time` time DEFAULT NULL,
  `checkin_end_time` time DEFAULT NULL,
  `checkout_start_time` time DEFAULT NULL,
  `checkout_end_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance_sessions`
--

INSERT INTO `attendance_sessions` (`id`, `attendance_id`, `unique_code`, `session_type`, `date`, `checkin_start_time`, `checkin_end_time`, `checkout_start_time`, `checkout_end_time`, `created_at`, `updated_at`) VALUES
(21, 8, 'v0gYO9bAUG0YUtEm5lzLWNVSeTtiDTOa', 'checkin', '2026-04-18', '08:00:00', '17:00:00', NULL, NULL, '2026-04-17 09:23:23', '2026-04-17 09:23:23');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `campaign_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `audience_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `filter_criteria` json DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `content` json NOT NULL,
  `schedule_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'now',
  `scheduled_at` datetime DEFAULT NULL,
  `recipients_count` int NOT NULL DEFAULT '0',
  `delivered_count` int NOT NULL DEFAULT '0',
  `opened_count` int NOT NULL DEFAULT '0',
  `clicked_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `participant_id` bigint UNSIGNED NOT NULL,
  `template_id` bigint UNSIGNED NOT NULL,
  `certificate_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pdf_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generated_at` timestamp NOT NULL,
  `generated_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `event_id`, `participant_id`, `template_id`, `certificate_number`, `pdf_file`, `generated_at`, `generated_by`, `created_at`, `updated_at`) VALUES
(29, 7, 36, 14, 'CERT-20260417002656-C45D58', 'certificates/certificate_1776356816_36.pdf', '2026-04-16 16:26:56', 1, '2026-04-16 16:26:56', '2026-04-16 16:26:56');

-- --------------------------------------------------------

--
-- Table structure for table `certificate_templates`
--

CREATE TABLE `certificate_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `preview_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orientation` enum('portrait','landscape') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'portrait',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `placeholders` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `template_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificate_templates`
--

INSERT INTO `certificate_templates` (`id`, `user_id`, `name`, `description`, `preview_image`, `pdf_file`, `background_pdf`, `orientation`, `created_by`, `placeholders`, `is_active`, `template_data`, `created_at`, `updated_at`) VALUES
(14, 1, 'Powerboat RACE 2026', NULL, NULL, 'http://localhost:8000certificate-templates/Q6Uo9COHgdNfdN2gcoaYD82P7WOz2nLPwg07Kxwo.pdf', 'http://localhost:8000/storage/certificate-templates/Q6Uo9COHgdNfdN2gcoaYD82P7WOz2nLPwg07Kxwo.pdf', 'portrait', 1, '[]', 1, '{\"width\": 210, \"height\": 297, \"elements\": [{\"x\": 103.5, \"y\": 144, \"id\": 1776347490305, \"type\": \"text\", \"color\": \"#000000\", \"content\": \"{{participant_name}}\", \"fontSize\": 24, \"fontStyle\": \"normal\", \"textAlign\": \"center\", \"fontFamily\": \"Times New Roman\", \"fontWeight\": \"bold\", \"textDecoration\": \"none\"}, {\"x\": 169, \"y\": 12.000000000000028, \"id\": 1776355262688, \"type\": \"qrcode\", \"width\": 30, \"height\": 30}, {\"x\": 103.5, \"y\": 45, \"id\": 1776355772586, \"type\": \"text\", \"color\": \"#000000\", \"content\": \"{{CERT-GEN}}\", \"fontSize\": 14, \"fontStyle\": \"normal\", \"textAlign\": \"center\", \"fontFamily\": \"Times New Roman\", \"fontWeight\": \"bold\", \"textDecoration\": \"none\"}]}', '2026-04-16 13:51:24', '2026-04-16 16:10:29'),
(15, 2, 'a', NULL, NULL, 'http://localhost:8000certificate-templates/dtSekVVxsQaVw5uRcstL4yzRTAb3vM6v7dJB3KU4.pdf', 'http://localhost:8000/storage/certificate-templates/dtSekVVxsQaVw5uRcstL4yzRTAb3vM6v7dJB3KU4.pdf', 'portrait', 2, '[]', 1, '{\"width\": 210, \"height\": 297, \"elements\": [{\"x\": 101.5, \"y\": 131, \"id\": 1776875608451, \"type\": \"text\", \"color\": \"#000000\", \"content\": \"{{participant_name}}\", \"fontSize\": 20, \"fontStyle\": \"normal\", \"textAlign\": \"center\", \"fontFamily\": \"Amsterdam\", \"fontWeight\": \"normal\", \"textDecoration\": \"none\"}, {\"x\": 158.5, \"y\": 199.5, \"id\": 1776875618880, \"type\": \"qrcode\", \"width\": 35, \"height\": 35}]}', '2026-04-22 16:33:16', '2026-04-22 16:48:16');

-- --------------------------------------------------------

--
-- Table structure for table `database_participants`
--

CREATE TABLE `database_participants` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_passport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organization` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'website',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_configs`
--

CREATE TABLE `delivery_configs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `config_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `settings` json DEFAULT NULL,
  `default_template` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery_configs`
--

INSERT INTO `delivery_configs` (`id`, `user_id`, `config_type`, `provider`, `is_active`, `settings`, `default_template`, `created_at`, `updated_at`) VALUES
(1, 1, 'email', 'smtp', 1, '{\"host\": \"indigo.herosite.pro\", \"port\": \"587\", \"password\": \"F@iz@n!984\", \"username\": \"admin@e-certificate.com.my\", \"from_name\": \"Sijil System\", \"encryption\": \"ssl\", \"from_address\": \"admin@e-certificate.com.my\"}', NULL, '2025-07-25 02:02:01', '2026-04-16 14:03:46'),
(2, 1, 'sms', 'twilio', 0, '{\"sid\": \"test_sid\", \"from\": \"+1234567890\", \"token\": \"test_token\"}', 'Hello {name}, your event {event_name} is starting soon.', '2025-07-25 02:02:19', '2026-04-16 14:33:35'),
(4, 2, 'email', 'smtp', 1, '{\"host\": \"indigo.herosite.pro\", \"port\": \"587\", \"password\": \"F@iz@n!984\", \"username\": \"enquiry@kflegacyresources.com\", \"from_name\": \"SIJIL System\", \"encryption\": \"tls\", \"from_address\": \"enquiry@kflegacyresources.com\"}', NULL, '2025-10-15 00:16:21', '2025-10-16 01:28:50'),
(6, 1, 'sms', 'infobip', 1, '{\"key\": \"9732c72b45cd8c2cc82386c07f22adcf-c59d083f-6d28-49ac-8615-ecddda4e66a9\", \"from\": \"62033\", \"base_url\": \"https://r4eee.api.infobip.com\"}', 'Hello {name}, your event {event_name} is starting soon.', '2026-04-16 13:58:48', '2026-04-16 14:33:35');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `organizer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `condition` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `start_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_date` date NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `max_participants` int NOT NULL,
  `status` enum('active','pending','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `disable_auto_expiry` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` bigint UNSIGNED NOT NULL,
  `contact_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_expires_at` timestamp NULL DEFAULT NULL,
  `poster` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `organizer`, `description`, `condition`, `start_date`, `start_time`, `end_date`, `end_time`, `location`, `address`, `max_participants`, `status`, `disable_auto_expiry`, `user_id`, `contact_person`, `contact_email`, `contact_phone`, `registration_link`, `registration_expires_at`, `poster`, `created_at`, `updated_at`) VALUES
(7, 'Powerboat RACE 2026', 'Sibu Resident Office', NULL, '<h1 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 18pt;\">TERMA &amp; SYARAT ACARA: INTERNATIONAL RIVER CHALLENGE POWERBOAT RACE 2026</span></h1>\r\n<h3 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 14pt;\">1. PENDAHULUAN</span></h3>\r\n<p class=\"ng-star-inserted\"><span style=\"font-size: 12pt;\"><span class=\"ng-star-inserted\">1.1 Acara ini dianjurkan oleh </span><strong class=\"ng-star-inserted\"><span class=\"ng-star-inserted\">[Nama Organisasi/Jawatankuasa]</span></strong><span class=\"ng-star-inserted\"> (selepas ini dirujuk sebagai \"Penganjur\").</span></span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">1.2 Dengan mendaftar untuk acara ini, semua peserta (pemandu, krew, dan pemilik pasukan) dianggap telah membaca, memahami, dan bersetuju untuk terikat dengan Terma &amp; Syarat ini.</span></p>\r\n<h3 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 14pt;\">2. KELAYAKAN PESERTA</span></h3>\r\n<p class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">2.1 Peserta mestilah berumur sekurang-kurangnya 18 tahun pada tarikh acara (kecuali kategori remaja tertentu dengan kebenaran bertulis penjaga).</span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">2.2 Setiap pemandu wajib memiliki lesen perlumbaan antarabangsa yang sah (cth: UIM - Union Internationale Motonautique) atau lesen yang diiktiraf oleh badan sukan bermotor kebangsaan.</span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">2.3 Peserta mestilah berada dalam keadaan kesihatan yang baik. Pemeriksaan kesihatan rasmi mungkin diperlukan sebelum pelepasan.</span></p>\r\n<h3 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 14pt;\">3. PENDAFTARAN DAN YURAN</span></h3>\r\n<p class=\"ng-star-inserted\"><span style=\"font-size: 12pt;\"><span class=\"ng-star-inserted\">3.1 Pendaftaran hanya dianggap sah setelah bayaran penuh diterima sebelum tarikh tutup </span><strong class=\"ng-star-inserted\"><span class=\"ng-star-inserted\">[Nyatakan Tarikh]</span></strong><span class=\"ng-star-inserted\">.</span></span><br class=\"ng-star-inserted\"><span style=\"font-size: 12pt;\"><span class=\"ng-star-inserted\">3.2 Yuran pendaftaran tidak boleh dikembalikan (</span><span class=\"ng-star-inserted\">non-refundable</span><span class=\"ng-star-inserted\">) kecuali jika acara dibatalkan sepenuhnya oleh Penganjur atas sebab-sebab selain daripada </span><span class=\"ng-star-inserted\">Force Majeure</span><span class=\"ng-star-inserted\">.</span></span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">3.3 Penganjur berhak menolak penyertaan mana-mana pihak tanpa perlu memberikan alasan.</span></p>\r\n<h3 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 14pt;\">4. PERATURAN TEKNIKAL DAN KESELAMATAN</span></h3>\r\n<p class=\"ng-star-inserted\"><span style=\"font-size: 12pt;\"><span class=\"ng-star-inserted\">4.1 Semua bot mestilah mematuhi spesifikasi teknikal kategori masing-masing yang ditetapkan oleh </span><strong class=\"ng-star-inserted\"><span class=\"ng-star-inserted\">[Badan Kawal Selia, cth: UIM]</span></strong><span class=\"ng-star-inserted\">.</span></span><br class=\"ng-star-inserted\"><span style=\"font-size: 12pt;\"><span class=\"ng-star-inserted\">4.2 Pemeriksaan teknikal (</span><span class=\"ng-star-inserted\">Scrutineering</span><span class=\"ng-star-inserted\">) adalah wajib sebelum perlumbaan. Bot yang gagal memenuhi piawaian keselamatan akan dilarang berlumba.</span></span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">4.3 Peralatan Keselamatan Peribadi (PPE) seperti jaket keselamatan (life jacket) gred perlumbaan, topi keledar, dan pakaian kalis api (jika berkaitan) adalah wajib dipakai sepanjang masa berada di dalam air.</span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">4.4 Penggunaan alkohol dan dadah adalah dilarang sama sekali. Ujian saringan boleh dijalankan secara rawak.</span></p>\r\n<h3 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 14pt;\">5. LIABILITI DAN INDEMNITI</span></h3>\r\n<p class=\"ng-star-inserted\"><span style=\"font-size: 12pt;\"><span class=\"ng-star-inserted\">5.1 </span><strong class=\"ng-star-inserted\"><span class=\"ng-star-inserted\">Pelepasan Liabiliti:</span></strong><span class=\"ng-star-inserted\"> Peserta memahami bahawa perlumbaan bot laju adalah sukan berbahaya yang melibatkan risiko kecederaan parah atau kematian. Peserta menyertai acara ini atas risiko sendiri.</span></span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">5.2 Penganjur, penaja, dan pihak berkuasa tempatan tidak akan bertanggungjawab atas sebarang kehilangan, kerosakan harta benda, kecederaan, atau kematian yang berlaku semasa acara.</span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">5.3 Peserta wajib memiliki insurans perlindungan diri dan liabiliti pihak ketiga yang merangkumi aktiviti perlumbaan antarabangsa.</span></p>\r\n<h3 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 14pt;\">6. PERLINDUNGAN ALAM SEKITAR</span></h3>\r\n<p class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">6.1 Memandangkan ini adalah acara sungai, semua pasukan wajib memastikan tiada tumpahan minyak atau bahan kimia ke dalam sungai.</span><br class=\"ng-star-inserted\"><span style=\"font-size: 12pt;\"><span class=\"ng-star-inserted\">6.2 Penggunaan </span><span class=\"ng-star-inserted\">oil spill kit</span><span class=\"ng-star-inserted\"> dan alas kalis minyak di kawasan </span><span class=\"ng-star-inserted\">paddock</span><span class=\"ng-star-inserted\"> adalah wajib.</span></span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">6.3 Mana-mana peserta yang didapati sengaja mencemarkan sungai akan hilang kelayakan serta-merta dan boleh dikenakan denda oleh pihak berkuasa.</span></p>\r\n<h3 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 14pt;\">7. HAK MEDIA DAN PENGGAMBARAN</span></h3>\r\n<p class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">7.1 Penganjur mempunyai hak eksklusif untuk menggunakan nama, suara, gambar, dan video peserta untuk tujuan promosi, siaran langsung, dan dokumentasi tanpa sebarang bayaran royalti kepada peserta.</span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">7.2 Penggunaan dron peribadi oleh peserta untuk penggambaran adalah dilarang tanpa permit bertulis daripada Penganjur dan pihak berkuasa penerbangan.</span></p>\r\n<h3 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 14pt;\">8. PEMBATALAN DAN FORCE MAJEURE</span></h3>\r\n<p class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">8.1 Penganjur berhak meminda jadual, memendekkan perlumbaan, atau membatalkan acara atas faktor keselamatan, cuaca buruk (cth: banjir besar, arus terlalu deras), atau arahan pihak berkuasa.</span><br class=\"ng-star-inserted\"><span style=\"font-size: 12pt;\"><span class=\"ng-star-inserted\">8.2 Dalam situasi </span><span class=\"ng-star-inserted\">Force Majeure</span><span class=\"ng-star-inserted\"> (bencana alam, peperangan, pandemik), Penganjur tidak bertanggungjawab ke atas sebarang kerugian kos perjalanan atau penginapan peserta.</span></span></p>\r\n<h3 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 14pt;\">9. TATATERTIB DAN BANTAHAN</span></h3>\r\n<p class=\"ng-star-inserted\"><span style=\"font-size: 12pt;\"><span class=\"ng-star-inserted\">9.1 Sebarang bantahan (</span><span class=\"ng-star-inserted\">protest</span><span class=\"ng-star-inserted\">) terhadap keputusan perlumbaan mestilah dikemukakan secara bertulis kepada Urusetia dalam tempoh 30 minit selepas keputusan rasmi dikeluarkan, bersama yuran bantahan sebanyak </span><strong class=\"ng-star-inserted\"><span class=\"ng-star-inserted\">[Jumlah RM/USD]</span></strong><span class=\"ng-star-inserted\">.</span></span><br class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">9.2 Kelakuan tidak beretika atau provokasi melampau terhadap pegawai atau peserta lain boleh mengakibatkan hilang kelayakan serta-merta.</span></p>\r\n<h3 class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 14pt;\">10. UNDANG-UNDANG KAWALAN</span></h3>\r\n<p class=\"ng-star-inserted\"><span class=\"ng-star-inserted\" style=\"font-size: 12pt;\">10.1 Terma &amp; Syarat ini tertakluk kepada undang-undang Malaysia. Sebarang pertikaian yang tidak dapat diselesaikan secara harmoni akan dirujuk kepada mahkamah di Malaysia.</span></p>', '2026-04-17', '08:00:00', '2026-04-18', '17:00:00', 'Pulau Li Hua Sibu', 'Pulau Li Hua Sibu', 1999, 'active', 1, 1, 'Khairunnisa Binti Sabawi', 'khairuni90@sarawak.gov.my', '+60128834665', 'MzIwNTQ3YjItMzgzMy00MmI3LWJkZTYtNmQ5MGMyOTU1NDRiMTc3NjMzNjA0Mzc', NULL, 'events/posters/Y5ts4NyhMa1QkoKKP1S6ebBByHb6KowU1ibFRIBT.png', '2026-04-16 10:40:43', '2026-04-17 10:26:50'),
(8, 'a', 'a', 'a', NULL, '2026-04-24', '00:31:00', '2026-04-25', '03:31:00', 'aa', NULL, 10000, 'active', 1, 2, NULL, NULL, NULL, 'NGQyNDMyNjUtZTgzYS00YjhlLTk1YjctMmM4MmI5M2M0ODIyMTc3Njg3NTUyNDg', NULL, NULL, '2026-04-22 16:32:04', '2026-04-22 16:32:04');

-- --------------------------------------------------------

--
-- Table structure for table `event_pwa_participant`
--

CREATE TABLE `event_pwa_participant` (
  `id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `pwa_participant_id` bigint UNSIGNED NOT NULL,
  `is_registered` tinyint(1) NOT NULL DEFAULT '1',
  `registered_at` timestamp NULL DEFAULT NULL,
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `checked_out_at` timestamp NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attendance_record_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` bigint UNSIGNED NOT NULL,
  `pwa_participant_id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED NOT NULL,
  `registration_date` timestamp NULL DEFAULT NULL,
  `attendance_date` timestamp NULL DEFAULT NULL,
  `status` enum('registered','attended','cancelled','no_show') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fcm_tokens`
--

CREATE TABLE `fcm_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `device` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fcm_tokens`
--

INSERT INTO `fcm_tokens` (`id`, `user_id`, `token`, `device`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'foE3_sIb0ucGaFa3BcxXF1:APA91bF-CpfMgMKF1zMosSoZszqNOEC1EpIeUBW9pUlT8ledgR5ITOuuOUuAdmXVb_x0XXds4pZp7tWN3YtRHmnes8NKeXwtDZDVSOq5SwTL3dtMDaBAVzs', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 05:56:02', '2025-10-16 05:51:58', '2025-10-16 05:56:02'),
(2, 1, 'foE3_sIb0ucGaFa3BcxXF1:APA91bHtt9-d205C50LbC117mzvHjtmIglyHfg5bd7gacJa9UzCtFAR4-UfqWmYR5p1F5Qg6OmHJ-Spefj3znZ3eDqYrEH6NSI91fapWqxHWhRpf1neBGF0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 05:58:55', '2025-10-16 05:58:40', '2025-10-16 05:58:55'),
(3, 1, 'ev3XW4u7_TXB0L73o1ShsD:APA91bGzNUUdSQ5Qf6KLobI2jCsxqS_STSx4taNW3iglRpPa4cVkQh1e_EgnMRjoueC2Ah606fbCV56VQE0Ik9i5egSmA73vZr8J5djo5iGCmL9aoaJCI4k', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 06:12:22', '2025-10-16 06:01:32', '2025-10-16 06:12:22'),
(4, 1, 'e12S_kZsBnFhQbD5FXbBS7:APA91bGUetuZrAnTYI8nswhS1RxtFzKvmv9FXoeze2bdipZ_ghbnJN871ZiMPc649TtJ3WagKLl_6Nhr8ScMmqNKqoetSoafttWVXXehLa5WgetccXKO6jc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 06:26:17', '2025-10-16 06:13:14', '2025-10-16 06:26:17'),
(5, 2, 'e12S_kZsBnFhQbD5FXbBS7:APA91bEHbjjwMXy4MV3hgjsL77bAUNs0bs6dpRi0cBmiKCe_SQg1SZxjzJvzmtCqjMBwMvBNoEAIEryTOjpUNPj5OjqtUEXCJOvY-muFxWa847U0GhLZKO0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 09:13:53', '2025-10-16 06:27:40', '2025-10-16 09:13:53'),
(6, 1, 'd2z7cF6o6o1QMjIocnHfrY:APA91bEyVT0nKHcYZjBAchcyWN-9-wdHnyZuPQ1qpygc-0uqa0E4gc-dsHxs_HGjZ9ZsS5mIlkFoOGLj9cOv8oRM2p8a-DoDrfPY9SsFOz3lwomxDEzp77w', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Safari/605.1.15', '2025-10-16 07:34:17', '2025-10-16 07:34:17', '2025-10-16 07:34:17'),
(7, 2, 'cvy5JsmZ21fCt4EgLRj8xI:APA91bHSCTsLYF-vmHScX3K0Q4Bz-8E1puj6G7JFKrDHXztm8dH1I5_srzp4CkiJBv_LeYTtg8lShavWrHw4uSnkAjYsVx0_NJntm2wWeceRoZ_m_yFIuYM', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 09:30:35', '2025-10-16 09:24:07', '2025-10-16 09:30:35'),
(8, 2, 'cvy5JsmZ21fCt4EgLRj8xI:APA91bEtcI447ezvhiEpPCfKyK-v9MjSkUgTXt8cQYwQMPNCJHLJDVpKhZ3f-IQVBmbUjeyyGUIW3ZBhyg9WW_9Bzvv_LQG8hdOJ5T49RVD4mSlW7U62Zpc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-19 15:18:48', '2025-10-16 09:32:06', '2025-10-19 15:18:48'),
(9, 1, 'dZeGiV1d_8zxuOx0gYZMuh:APA91bHBgcxFO9jJIdGLI5cC_lZ7Kx7AbsiLt0Q52Sgmn3ET3r2tQY4O4rynaoqsojiCGrPJUmO4aC8KoHipacxs8g6HTA57rLZTgadkCf0kQuxMQ7R-fBY', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-27 05:13:49', '2025-12-27 04:52:14', '2025-12-27 05:13:49'),
(10, 2, 'dFqNgoOrqZYYaI8XK8ayra:APA91bFGCJoevG8WQL0ZhwO8dhhEfVV0GtKtubBCXhy2exlcP2HF-P1tneIXxs7QjiJH7zMQJlRmwYcgYd1y02pQH1egFnLN2N2qS7PAVRS-Z27AnAK1Zog', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-22 17:22:51', '2026-04-16 01:14:37', '2026-04-22 17:22:51');

-- --------------------------------------------------------

--
-- Table structure for table `global_configs`
--

CREATE TABLE `global_configs` (
  `id` bigint UNSIGNED NOT NULL,
  `org_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sijil Event Management',
  `org_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contact@sijilevents.com',
  `timezone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Kuala_Lumpur',
  `date_format` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'd/m/Y',
  `org_logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT '0',
  `debug_mode` tinyint(1) NOT NULL DEFAULT '0',
  `cache_lifetime` int NOT NULL DEFAULT '60',
  `pagination` int NOT NULL DEFAULT '25',
  `error_reporting` tinyint(1) NOT NULL DEFAULT '1',
  `activity_logging` tinyint(1) NOT NULL DEFAULT '1',
  `event_expiry` int NOT NULL DEFAULT '48',
  `default_event_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `registration_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `allow_multiple_registrations` tinyint(1) NOT NULL DEFAULT '1',
  `auto_confirmation_emails` tinyint(1) NOT NULL DEFAULT '1',
  `min_password_length` int NOT NULL DEFAULT '8',
  `password_expiry` int NOT NULL DEFAULT '90',
  `require_special_chars` tinyint(1) NOT NULL DEFAULT '1',
  `require_numbers` tinyint(1) NOT NULL DEFAULT '1',
  `require_uppercase` tinyint(1) NOT NULL DEFAULT '1',
  `max_login_attempts` int NOT NULL DEFAULT '5',
  `lockout_duration` int NOT NULL DEFAULT '15',
  `session_timeout` int NOT NULL DEFAULT '120',
  `enable_2fa` tinyint(1) NOT NULL DEFAULT '1',
  `force_ssl` tinyint(1) NOT NULL DEFAULT '1',
  `log_failed_logins` tinyint(1) NOT NULL DEFAULT '1',
  `log_password_changes` tinyint(1) NOT NULL DEFAULT '1',
  `log_permission_changes` tinyint(1) NOT NULL DEFAULT '1',
  `enable_security_alerts` tinyint(1) NOT NULL DEFAULT '1',
  `primary_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#004aad',
  `secondary_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#38bdf8',
  `default_theme` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `font_family` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inter',
  `allow_user_theme_choice` tinyint(1) NOT NULL DEFAULT '1',
  `favicon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_background` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_css` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sidebar_default` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'expanded',
  `table_density` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `show_welcome_message` tinyint(1) NOT NULL DEFAULT '1',
  `show_help_icons` tinyint(1) NOT NULL DEFAULT '1',
  `email_new_user_registration` tinyint(1) NOT NULL DEFAULT '1',
  `email_event_registration` tinyint(1) NOT NULL DEFAULT '1',
  `email_event_reminder` tinyint(1) NOT NULL DEFAULT '1',
  `email_certificate_generated` tinyint(1) NOT NULL DEFAULT '1',
  `sms_certificate_generated` tinyint(1) NOT NULL DEFAULT '0',
  `telegram_certificate_generated` tinyint(1) NOT NULL DEFAULT '0',
  `email_password_reset` tinyint(1) NOT NULL DEFAULT '1',
  `sms_event_registration` tinyint(1) NOT NULL DEFAULT '0',
  `sms_event_reminder` tinyint(1) NOT NULL DEFAULT '0',
  `sms_reminder_hours` int NOT NULL DEFAULT '24',
  `admin_system_errors` tinyint(1) NOT NULL DEFAULT '1',
  `admin_new_registrations` tinyint(1) NOT NULL DEFAULT '0',
  `admin_security_alerts` tinyint(1) NOT NULL DEFAULT '1',
  `telegram_event_registration` tinyint(1) NOT NULL DEFAULT '0',
  `admin_notification_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin@sijilevents.com',
  `api_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `api_rate_limit` int NOT NULL DEFAULT '60',
  `enable_api_keys` tinyint(1) NOT NULL DEFAULT '1',
  `enable_oauth` tinyint(1) NOT NULL DEFAULT '1',
  `api_cors_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `cors_domains` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `google_calendar_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `microsoft_teams_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `stripe_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `zoom_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `enable_webhooks` tinyint(1) NOT NULL DEFAULT '1',
  `webhook_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_events` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `telegram_bot_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_bot_username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_channel_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_owner_user_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_owner_username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `global_configs`
--

INSERT INTO `global_configs` (`id`, `org_name`, `org_email`, `timezone`, `date_format`, `org_logo`, `maintenance_mode`, `debug_mode`, `cache_lifetime`, `pagination`, `error_reporting`, `activity_logging`, `event_expiry`, `default_event_status`, `registration_message`, `allow_multiple_registrations`, `auto_confirmation_emails`, `min_password_length`, `password_expiry`, `require_special_chars`, `require_numbers`, `require_uppercase`, `max_login_attempts`, `lockout_duration`, `session_timeout`, `enable_2fa`, `force_ssl`, `log_failed_logins`, `log_password_changes`, `log_permission_changes`, `enable_security_alerts`, `primary_color`, `secondary_color`, `default_theme`, `font_family`, `allow_user_theme_choice`, `favicon`, `login_background`, `custom_css`, `sidebar_default`, `table_density`, `show_welcome_message`, `show_help_icons`, `email_new_user_registration`, `email_event_registration`, `email_event_reminder`, `email_certificate_generated`, `sms_certificate_generated`, `telegram_certificate_generated`, `email_password_reset`, `sms_event_registration`, `sms_event_reminder`, `sms_reminder_hours`, `admin_system_errors`, `admin_new_registrations`, `admin_security_alerts`, `telegram_event_registration`, `admin_notification_email`, `api_enabled`, `api_rate_limit`, `enable_api_keys`, `enable_oauth`, `api_cors_enabled`, `cors_domains`, `google_calendar_enabled`, `microsoft_teams_enabled`, `stripe_enabled`, `zoom_enabled`, `enable_webhooks`, `webhook_secret`, `webhook_events`, `telegram_bot_token`, `telegram_bot_username`, `telegram_channel_id`, `telegram_owner_user_id`, `telegram_owner_username`, `created_at`, `updated_at`) VALUES
(1, 'Sijil Event Management TEST', 'admin@e-certificate.com.my', 'Asia/Kuala_Lumpur', 'd/m/Y', NULL, 0, 0, 60, 25, 1, 1, 48, 'published', 'Thank you for registering for this event. Please check your email for confirmation details.', 1, 1, 8, 90, 1, 1, 1, 5, 15, 120, 1, 1, 1, 1, 1, 1, '#004aad', '#38bdf8', 'light', 'inter', 1, NULL, NULL, '/* Custom CSS code */\r\n.custom-header {\r\n  background: linear-gradient(to right, var(--primary-color), var(--secondary-color));\r\n}', 'expanded', 'default', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 24, 1, 1, 1, 1, 'admin@sijilevents.com', 1, 60, 1, 1, 1, 'https://example.com, https://*.sijilevents.com', 0, 0, 0, 0, 1, 'wh_sec_fk9U8VEUNCCaA5NDEIL0SdL1XAFn41Fc', 'event.created, event.updated, registration.completed, certificate.generated, attendance.recorded', '8434391628:AAF_smkdIy1HQcZQqkHA_UiupihoHkISMqc', 'ecertificatebot', '-1003805636973', '473855787', 'devilguardian', '2025-07-26 08:09:57', '2026-04-17 06:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `helpdesk_messages`
--

CREATE TABLE `helpdesk_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT '0',
  `attachments` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `helpdesk_tickets`
--

CREATE TABLE `helpdesk_tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Format: HD-XXXX for tickets up to 9999, then HD-DDMMYY-XXXX where XXXX is a unique alphanumeric code',
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `category` enum('technical','billing','event','account','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','medium','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','in_progress','resolved','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(24, '0001_01_01_000000_create_users_table', 1),
(25, '0001_01_01_000001_create_cache_table', 1),
(26, '0001_01_01_000002_create_jobs_table', 1),
(27, '2025_07_21_190058_add_role_id_to_users_table', 1),
(28, '2025_07_22_000003_add_user_management_fields_to_users_table', 1),
(29, '2025_07_22_002525_create_permission_tables', 1),
(30, '2025_07_22_100000_create_events_table', 1),
(31, '2025_07_22_100001_create_participants_table', 1),
(32, '2025_07_22_135436_rename_organization_to_id_passport_in_participants_table', 1),
(33, '2025_07_22_141307_add_related_participant_id_to_participants_table', 1),
(34, '2025_07_22_155941_create_attendances_table', 1),
(35, '2025_07_22_155946_create_attendance_records_table', 1),
(36, '2025_07_24_143147_create_participants_table', 2),
(37, '2025_07_22_230025_create_certificate_templates_table', 3),
(38, '2025_07_22_231059_add_pdf_file_to_certificate_templates_table', 4),
(39, '2025_07_23_010000_create_certificates_table', 5),
(40, '2025_07_23_221836_add_template_data_and_background_pdf_to_certificate_templates', 6),
(42, '2025_07_24_150554_add_address_to_participants_table', 7),
(43, '2025_07_24_163051_add_id_passport_gender_date_of_birth_job_title_to_participants_table', 8),
(44, '2025_07_24_170659_add_identity_card_and_passport_no_to_participants_table', 9),
(46, '2025_07_24_173028_migrate_id_passport_data_to_new_columns', 10),
(47, '2025_07_24_180102_update_participants_table_structure', 11),
(48, '2025_07_24_181825_add_condition_to_events_table', 12),
(49, '2025_07_25_000000_add_attendance_session_and_checkin_checkout_to_attendance_records_table', 13),
(50, '2025_07_25_100000_create_delivery_configs_table', 14),
(51, '2025_07_26_000000_create_campaigns_table', 15),
(52, '2025_07_25_165035_create_helpdesk_tickets_table', 16),
(53, '2025_07_25_165042_create_helpdesk_messages_table', 17),
(56, '2025_07_25_205101_update_helpdesk_ticket_id_format', 18),
(57, '2025_07_26_093251_create_surveys_table', 19),
(58, '2025_07_26_093320_create_survey_questions_table', 20),
(59, '2025_07_26_093305_create_survey_responses_table', 21),
(60, '2025_07_26_141027_create_global_configs_table', 22),
(61, '2025_07_28_103324_create_activity_log_table', 23),
(62, '2025_07_28_103325_add_event_column_to_activity_log_table', 24),
(63, '2025_07_28_103326_add_batch_uuid_column_to_activity_log_table', 25),
(64, '2025_07_28_135654_add_archived_to_attendance_status_enum', 26),
(65, '2025_07_28_135710_add_archived_status_to_attendances_table', 27),
(66, '2025_07_29_090419_create_pwa_participants_table', 28),
(67, '2025_07_29_090422_create_event_registrations_table', 29),
(68, '2019_12_14_000001_create_personal_access_tokens_table', 30),
(69, '2025_07_29_103411_create_event_pwa_participant_table', 31),
(70, '2025_07_29_103415_create_pwa_settings_table', 32),
(71, '2025_07_29_103418_create_pwa_email_templates_table', 33),
(73, '2025_07_29_150000_add_bulk_import_fields_to_pwa_participants_table', 34),
(74, '2025_07_29_150001_add_address_to_pwa_participants_table', 35),
(75, '2025_07_29_150002_add_is_active_to_pwa_participants_table', 36),
(76, '2025_07_29_150003_add_password_changed_at_to_pwa_participants_table', 37),
(77, '2025_07_29_150004_add_created_by_updated_by_to_pwa_participants_table', 38),
(78, '2025_07_22_185319_create_attendance_sessions_table', 39),
(79, '2025_07_22_221335_add_checkin_and_checkout_time_to_attendance_records_table', 40),
(80, '2025_07_22_221837_add_attendance_session_id_to_attendance_records_table', 41),
(81, '2025_07_23_000000_add_placeholders_to_certificate_templates_table', 42),
(82, '2025_07_23_125056_create_certificate_templates_table', 43),
(83, '2025_07_23_131805_create_certificate_templates_table', 44),
(84, '2025_07_23_224815_add_is_active_to_certificate_templates', 44),
(85, '2025_07_25_101301_create_delivery_configs_table', 45),
(86, '2025_07_25_102245_create_delivery_configs_table', 46),
(87, '2025_07_26_140919_create_global_configs_table', 47),
(88, '2025_07_29_092216_create_personal_access_tokens_table', 48),
(89, '2025_07_29_103404_create_pwa_participants_table', 49),
(90, '2025_07_29_105247_rename_organizer_id_to_user_id_in_pwa_tables', 49),
(91, '2025_07_29_114547_add_username_to_pwa_participants_table', 50),
(92, '2025_07_29_200000_add_attendance_record_id_to_event_pwa_participant_table', 50),
(93, '2025_07_29_210000_add_related_participant_id_to_pwa_participants_table', 50),
(94, '2025_07_30_011245_remove_related_participant_id_from_participants_table', 51),
(95, '2025_10_15_000100_create_pwa_email_logs_table', 52),
(96, '2025_10_16_120000_create_fcm_tokens_table', 53),
(97, '2025_10_16_224057_add_race_to_participants_table', 54),
(98, '2025_10_16_232104_add_poster_to_events_table', 55),
(99, '2025_10_17_090846_add_gps_coordinates_to_attendance_records_table', 56),
(100, '2025_10_17_164555_add_unique_code_to_attendance_sessions_table', 57),
(101, '2025_10_17_165828_make_attendance_session_times_nullable', 58),
(102, '2026_04_16_151844_create_notifications_table', 59),
(103, '2026_04_16_153049_add_telegram_fields_to_global_configs_table', 59),
(104, '2026_04_16_155842_add_telegram_notification_to_global_configs_table', 60),
(105, '2026_04_16_182526_add_attribute_changes_to_activity_log_table', 61),
(106, '2026_04_16_213644_add_disable_auto_expiry_to_events_table', 62),
(107, '2026_04_16_215642_add_certificate_notification_flags_to_global_configs_table', 63),
(109, '2026_04_22_042648_add_user_id_to_certificate_templates_table', 64);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 3),
(2, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 6);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'notifications',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `icon`, `url`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'event_registration', 'New Event Registration', 'MOHAMAD FAIZAN BIN ABDUL RAHMAN has registered for Powerboat RACE 2026', 'person_add', 'http://localhost:8000/participants?event_id=7', '{\"event_id\": 7, \"event_name\": \"Powerboat RACE 2026\", \"participant_id\": 36, \"participant_name\": \"MOHAMAD FAIZAN BIN ABDUL RAHMAN\", \"participant_email\": \"faizanrahman84@gmail.com\"}', '2026-04-16 14:15:48', '2026-04-16 13:21:22', '2026-04-16 14:15:48'),
(2, 2, 'event_registration', 'New Event Registration', 'MOHAMAD FAIZAN BIN ABDUL RAHMAN has registered for a', 'person_add', 'http://localhost:8000/participants?event_id=8', '{\"event_id\": 8, \"event_name\": \"a\", \"participant_id\": 37, \"participant_name\": \"MOHAMAD FAIZAN BIN ABDUL RAHMAN\", \"participant_email\": \"faizanrahman84@gmail.com\"}', '2026-04-22 16:41:52', '2026-04-22 16:32:33', '2026-04-22 16:41:52');

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identity_card` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organization` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Malaysia',
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `race` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `event_id` bigint UNSIGNED NOT NULL,
  `registration_date` timestamp NULL DEFAULT NULL,
  `attendance_date` timestamp NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`id`, `name`, `email`, `phone`, `identity_card`, `passport_no`, `organization`, `job_title`, `address1`, `address2`, `city`, `state`, `postcode`, `country`, `gender`, `date_of_birth`, `race`, `status`, `event_id`, `registration_date`, `attendance_date`, `notes`, `created_at`, `updated_at`) VALUES
(36, 'MOHAMAD FAIZAN BIN ABDUL RAHMAN', 'faizanrahman84@gmail.com', '60178591411', '841205-13-6419', NULL, 'KF Legacy Resources', 'IT Director', 'No.44,', 'Jalan Kampung Nangka,', 'Sibu', 'Sarawak', '96000', 'Malaysia', 'male', '1984-05-12', 'Melayu (Sarawak)', 'active', 7, '2026-04-16 13:21:22', NULL, NULL, '2026-04-16 13:21:22', '2026-04-16 13:21:22'),
(37, 'MOHAMAD FAIZAN BIN ABDUL RAHMAN', 'faizanrahman84@gmail.com', '60178591411', '841205-13-6419', NULL, 'KF Legacy Resources', 'IT Director', 'No.44,', 'Jalan Kampung Nangka,', 'Sibu', 'Sarawak', '96000', 'Malaysia', 'male', '1984-05-12', 'Melayu (Sarawak)', 'active', 8, '2026-04-22 16:32:33', NULL, NULL, '2026-04-22 16:32:33', '2026-04-22 16:52:41');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `display_name`, `group`, `description`, `created_at`, `updated_at`) VALUES
(1, 'dashboard.read', 'web', 'View Dashboard', 'dashboard', 'Access dashboard', '2025-07-26 05:51:52', '2025-10-15 07:35:16'),
(2, 'event-management.create', 'web', 'Create Event Management', 'event', 'Permission to create Event Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(3, 'event-management.read', 'web', 'Read Event Management', 'event', 'Permission to read Event Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(4, 'event-management.update', 'web', 'Update Event Management', 'event', 'Permission to update Event Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(5, 'event-management.delete', 'web', 'Delete Event Management', 'event', 'Permission to delete Event Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(6, 'survey.create', 'web', 'Create Survey', 'event', 'Permission to create Survey', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(7, 'survey.read', 'web', 'Read Survey', 'event', 'Permission to read Survey', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(8, 'survey.update', 'web', 'Update Survey', 'event', 'Permission to update Survey', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(9, 'survey.delete', 'web', 'Delete Survey', 'event', 'Permission to delete Survey', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(10, 'participants.create', 'web', 'Create Participants', 'participants', 'Add new participants', '2025-07-26 05:51:52', '2025-10-15 07:35:16'),
(11, 'participants.read', 'web', 'View Participants', 'participants', 'View participant list', '2025-07-26 05:51:52', '2025-10-15 07:35:16'),
(12, 'participants.update', 'web', 'Edit Participants', 'participants', 'Edit participant information', '2025-07-26 05:51:52', '2025-10-15 07:35:16'),
(13, 'participants.delete', 'web', 'Delete Participants', 'participants', 'Delete participants', '2025-07-26 05:51:52', '2025-10-15 07:35:16'),
(17, 'template-designer.create', 'web', 'Create Template Designer', 'certificate', 'Permission to create Template Designer', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(18, 'template-designer.read', 'web', 'Read Template Designer', 'certificate', 'Permission to read Template Designer', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(19, 'template-designer.update', 'web', 'Update Template Designer', 'certificate', 'Permission to update Template Designer', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(20, 'template-designer.delete', 'web', 'Delete Template Designer', 'certificate', 'Permission to delete Template Designer', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(21, 'manage-attendance.create', 'web', 'Create Manage Attendance', 'attendance', 'Permission to create Manage Attendance', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(22, 'manage-attendance.read', 'web', 'Read Manage Attendance', 'attendance', 'Permission to read Manage Attendance', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(23, 'manage-attendance.update', 'web', 'Update Manage Attendance', 'attendance', 'Permission to update Manage Attendance', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(24, 'manage-attendance.delete', 'web', 'Delete Manage Attendance', 'attendance', 'Permission to delete Manage Attendance', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(26, 'attendance-reports.read', 'web', 'Read Attendance Reports', 'reports', 'Permission to read Attendance Reports', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(27, 'event-statistics.read', 'web', 'Read Event Statistics', 'reports', 'Permission to read Event Statistics', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(28, 'certificate-reports.read', 'web', 'Read Certificate Reports', 'reports', 'Permission to read Certificate Reports', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(29, 'campaign.create', 'web', 'Create Campaign', 'campaign', 'Permission to create Campaign', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(30, 'campaign.read', 'web', 'Read Campaign', 'campaign', 'Permission to read Campaign', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(31, 'campaign.update', 'web', 'Update Campaign', 'campaign', 'Permission to update Campaign', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(32, 'campaign.delete', 'web', 'Delete Campaign', 'campaign', 'Permission to delete Campaign', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(33, 'config-delivery.read', 'web', 'Read Config Delivery', 'campaign', 'Permission to read Config Delivery', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(34, 'config-delivery.update', 'web', 'Update Config Delivery', 'campaign', 'Permission to update Config Delivery', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(35, 'helpdesk.read', 'web', 'View Helpdesk', 'helpdesk', 'View helpdesk tickets', '2025-07-26 05:51:52', '2025-10-15 07:35:16'),
(36, 'helpdesk.update', 'web', 'Manage Helpdesk', 'helpdesk', 'Reply and manage tickets', '2025-07-26 05:51:52', '2025-10-15 07:35:16'),
(37, 'global-config.read', 'web', 'Read Global Config', 'settings', 'Permission to read Global Config', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(38, 'global-config.update', 'web', 'Update Global Config', 'settings', 'Permission to update Global Config', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(39, 'role-management.create', 'web', 'Create Role Management', 'settings', 'Permission to create Role Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(40, 'role-management.read', 'web', 'Read Role Management', 'settings', 'Permission to read Role Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(41, 'role-management.update', 'web', 'Update Role Management', 'settings', 'Permission to update Role Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(42, 'role-management.delete', 'web', 'Delete Role Management', 'settings', 'Permission to delete Role Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(43, 'user-management.create', 'web', 'Create User Management', 'settings', 'Permission to create User Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(44, 'user-management.read', 'web', 'Read User Management', 'settings', 'Permission to read User Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(45, 'user-management.update', 'web', 'Update User Management', 'settings', 'Permission to update User Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(46, 'user-management.delete', 'web', 'Delete User Management', 'settings', 'Permission to delete User Management', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(47, 'log-activity.read', 'web', 'Read Log Activity', 'settings', 'Permission to read Log Activity', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(48, 'security-audit.read', 'web', 'Read Security & Audit', 'settings', 'Permission to read Security & Audit', '2025-07-26 05:51:52', '2025-07-26 05:51:52'),
(107, 'events.create', 'web', 'Create Events', 'event', 'Create new events', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(108, 'events.read', 'web', 'View Events', 'event', 'View event details', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(109, 'events.update', 'web', 'Edit Events', 'event', 'Edit event information', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(110, 'events.delete', 'web', 'Delete Events', 'event', 'Delete events', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(111, 'surveys.create', 'web', 'Create Surveys', 'event', 'Create new surveys', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(112, 'surveys.read', 'web', 'View Surveys', 'event', 'View surveys', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(113, 'surveys.update', 'web', 'Edit Surveys', 'event', 'Edit surveys', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(114, 'surveys.delete', 'web', 'Delete Surveys', 'event', 'Delete surveys', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(115, 'survey_questions.manage', 'web', 'Manage Survey Questions', 'event', 'Add/edit/delete survey questions', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(116, 'survey_responses.read', 'web', 'View Survey Responses', 'event', 'View survey responses', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(117, 'survey_responses.export', 'web', 'Export Survey Responses', 'event', 'Export survey data', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(118, 'surveys.publish', 'web', 'Publish Surveys', 'event', 'Publish/unpublish surveys', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(119, 'attendance.create', 'web', 'Create Attendance', 'attendance', 'Create attendance sessions', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(120, 'attendance.read', 'web', 'View Attendance', 'attendance', 'View attendance records', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(121, 'attendance.update', 'web', 'Edit Attendance', 'attendance', 'Edit attendance records', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(122, 'attendance.delete', 'web', 'Delete Attendance', 'attendance', 'Delete attendance records', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(123, 'archives.read', 'web', 'View Archives', 'attendance', 'View archived attendance', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(124, 'certificates.read', 'web', 'View Certificates', 'certificate', 'View certificate list', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(125, 'certificates.create', 'web', 'Generate Certificates', 'certificate', 'Generate new certificates', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(127, 'certificates.delete', 'web', 'Delete Certificates', 'certificate', 'Delete certificates', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(128, 'templates.create', 'web', 'Create Templates', 'certificate', 'Create certificate templates', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(129, 'templates.read', 'web', 'View Templates', 'certificate', 'View templates', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(130, 'templates.update', 'web', 'Edit Templates', 'certificate', 'Edit certificate templates', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(131, 'templates.delete', 'web', 'Delete Templates', 'certificate', 'Delete templates', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(132, 'pwa_participants.create', 'web', 'Create PWA Participants', 'pwa', 'Add PWA participants', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(133, 'pwa_participants.read', 'web', 'View PWA Participants', 'pwa', 'View PWA participants', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(134, 'pwa_participants.update', 'web', 'Edit PWA Participants', 'pwa', 'Edit PWA participants', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(135, 'pwa_participants.delete', 'web', 'Delete PWA Participants', 'pwa', 'Delete PWA participants', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(136, 'pwa_analytics.read', 'web', 'View PWA Analytics', 'pwa', 'View PWA analytics', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(137, 'pwa_analytics.export', 'web', 'Export PWA Analytics', 'pwa', 'Export PWA analytics data', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(138, 'pwa_templates.create', 'web', 'Create Email Templates', 'pwa', 'Create PWA email templates', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(139, 'pwa_templates.read', 'web', 'View Email Templates', 'pwa', 'View email templates', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(140, 'pwa_templates.update', 'web', 'Edit Email Templates', 'pwa', 'Edit email templates', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(141, 'pwa_templates.delete', 'web', 'Delete Email Templates', 'pwa', 'Delete email templates', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(142, 'pwa_settings.read', 'web', 'View PWA Settings', 'pwa', 'View PWA settings', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(143, 'pwa_settings.update', 'web', 'Manage PWA Settings', 'pwa', 'Configure PWA settings', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(144, 'attendance_reports.read', 'web', 'View Attendance Reports', 'reports', 'View attendance reports', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(145, 'attendance_reports.export', 'web', 'Export Attendance Reports', 'reports', 'Export attendance data', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(146, 'event_statistics.read', 'web', 'View Event Statistics', 'reports', 'View event statistics', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(147, 'event_statistics.export', 'web', 'Export Event Statistics', 'reports', 'Export statistics data', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(148, 'certificate_reports.read', 'web', 'View Certificate Reports', 'reports', 'View certificate reports', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(149, 'certificate_reports.export', 'web', 'Export Certificate Reports', 'reports', 'Export certificate data', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(150, 'campaigns.create', 'web', 'Create Campaigns', 'campaign', 'Create email/SMS campaigns', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(151, 'campaigns.read', 'web', 'View Campaigns', 'campaign', 'View campaigns', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(152, 'campaigns.update', 'web', 'Edit Campaigns', 'campaign', 'Edit campaigns', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(153, 'campaigns.delete', 'web', 'Delete Campaigns', 'campaign', 'Delete campaigns', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(154, 'delivery.read', 'web', 'View Delivery Config', 'campaign', 'View delivery settings', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(155, 'delivery.update', 'web', 'Manage Delivery', 'campaign', 'Configure delivery settings', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(156, 'global_config.read', 'web', 'View Global Config', 'settings', 'View global configuration', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(157, 'global_config.update', 'web', 'Manage Global Config', 'settings', 'Edit global configuration', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(158, 'roles.create', 'web', 'Create Roles', 'settings', 'Create new roles', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(159, 'roles.read', 'web', 'View Roles', 'settings', 'View roles', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(160, 'roles.update', 'web', 'Edit Roles', 'settings', 'Edit roles', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(161, 'roles.delete', 'web', 'Delete Roles', 'settings', 'Delete roles', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(162, 'users.create', 'web', 'Create Users', 'settings', 'Create new users', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(163, 'users.read', 'web', 'View Users', 'settings', 'View users', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(164, 'users.update', 'web', 'Edit Users', 'settings', 'Edit users', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(165, 'users.delete', 'web', 'Delete Users', 'settings', 'Delete users', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(166, 'log_activity.read', 'web', 'View Log Activity', 'settings', 'View activity logs', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(167, 'security_audit.read', 'web', 'View Security Audit', 'settings', 'View security audit logs', '2025-10-15 07:35:16', '2025-10-15 07:35:16'),
(168, 'attendance_management.read', 'web', 'View Attendance Management', 'attendance', 'Access attendance management pages', '2025-10-15 12:50:53', '2025-10-15 12:50:53'),
(170, 'archives.delete', 'web', 'Delete Archives', 'attendance', 'Delete archived attendance sessions', '2025-10-15 12:50:53', '2025-10-15 12:50:53'),
(171, 'attendance.archive', 'web', 'Archive/Unarchive Attendance', 'attendance', 'Archive or unarchive attendance sessions', '2025-10-15 13:27:54', '2025-10-15 13:27:54'),
(172, 'archives.archive', 'web', 'Unarchive from Archive Page', 'attendance', 'Unarchive from archive page', '2025-10-15 13:51:55', '2025-10-15 13:51:55'),
(173, 'pwa_templates.export', 'web', 'Export Email Templates', 'pwa', 'Export email templates as CSV', '2025-10-15 15:19:48', '2025-10-15 15:19:48'),
(174, 'attendance_reports.delete', 'web', 'Delete Attendance Reports', 'reports', 'Delete attendance report entries', '2025-10-16 00:08:44', '2025-10-16 00:08:44'),
(175, 'helpdesk.create', 'web', 'Create Helpdesk Ticket', 'helpdesk', 'Create new helpdesk tickets', '2025-10-16 01:33:34', '2025-10-16 01:33:34'),
(176, 'helpdesk.delete', 'web', 'Delete Helpdesk Ticket', 'helpdesk', 'Delete helpdesk tickets', '2025-10-16 01:33:34', '2025-10-16 01:33:34'),
(177, 'log_activity.delete', 'web', 'Delete Log Activity', 'settings', 'Clear activity logs', '2025-10-16 01:52:42', '2025-10-16 01:52:42'),
(178, 'log_activity.export', 'web', 'Export Log Activity', 'settings', 'Export activity logs', '2025-10-16 01:52:42', '2025-10-16 01:52:42'),
(179, 'security_audit.delete', 'web', 'Delete Security Audit', 'settings', 'Clear security audit logs', '2025-10-16 01:52:42', '2025-10-16 01:52:42'),
(180, 'security_audit.export', 'web', 'Export Security Audit', 'settings', 'Export security audit logs', '2025-10-16 01:52:42', '2025-10-16 01:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\PwaParticipant', 1, 'pwa-token', '1bc040898667de103df637aa1b01bc141bf340ade7a33eca400d13bcfd0413cf', '[\"*\"]', NULL, NULL, '2025-07-29 01:22:26', '2025-07-29 01:22:26'),
(2, 'App\\Models\\PwaParticipant', 1, 'pwa-token', 'f4c31d20c754b63610e2f50f885494dbcf1a650b518a7fb412ad0f1b9797ba98', '[\"*\"]', NULL, NULL, '2025-07-29 01:22:37', '2025-07-29 01:22:37'),
(3, 'App\\Models\\PwaParticipant', 1, 'pwa-token', '28d6f3fc2a445dddd21f97b503cd78763e2a3847650c31b1de7d3b986d51a444', '[\"*\"]', '2025-07-29 01:25:04', NULL, '2025-07-29 01:25:04', '2025-07-29 01:25:04'),
(4, 'App\\Models\\PwaParticipant', 1, 'pwa-token', '274381ad151f9a73299128102903080dbe783f3f53d2c09ba0cbdc5e1c904ffe', '[\"*\"]', NULL, NULL, '2025-07-29 01:27:01', '2025-07-29 01:27:01'),
(5, 'App\\Models\\PwaParticipant', 1, 'pwa-token', '2cecf1b08a997515c784bbe09d48212df19cc8ba2c39c79ee345d68a3a5c8859', '[\"*\"]', '2025-07-29 01:28:07', NULL, '2025-07-29 01:28:07', '2025-07-29 01:28:07'),
(6, 'App\\Models\\PwaParticipant', 1, 'pwa-token', 'dabb58867e1c0c491e0c9e34a3f95dfff2eb8b4f454a5a229dfcb453f6e66374', '[\"*\"]', NULL, NULL, '2025-07-29 01:30:33', '2025-07-29 01:30:33'),
(7, 'App\\Models\\PwaParticipant', 1, 'pwa-token', '56a80aea48bd5c61941e9c41f740e881052dc4b2acfc3c773150cdb9f7488e10', '[\"*\"]', '2025-07-29 01:31:44', NULL, '2025-07-29 01:31:44', '2025-07-29 01:31:44'),
(17, 'App\\Models\\PwaParticipant', 102, 'test', 'e6c6ea61a69cb4b734b1048fee2b91148e6073e52b6207c0445c965c3b8aef79', '[\"*\"]', NULL, NULL, '2025-10-17 04:48:32', '2025-10-17 04:48:32'),
(37, 'App\\Models\\PwaParticipant', 102, 'pwa-token', 'e56ee477d316c35d5d5384bcd643c348a630095c197055c7afe2eee502574a6a', '[\"*\"]', '2025-10-17 17:15:26', NULL, '2025-10-17 17:10:34', '2025-10-17 17:15:26'),
(41, 'App\\Models\\PwaParticipant', 513, 'pwa-token', 'f7bff4fe02e8a85043eb6000cbe131da6be385f27abb0b0b1945a0ae60e7cf93', '[\"*\"]', NULL, NULL, '2025-10-18 00:26:07', '2025-10-18 00:26:07'),
(42, 'App\\Models\\PwaParticipant', 514, 'pwa-token', 'ca791a963e514705ad24ecf8c05fb7a64b2920bbdafbfe893569be812e4192aa', '[\"*\"]', NULL, NULL, '2025-10-18 00:26:58', '2025-10-18 00:26:58'),
(43, 'App\\Models\\PwaParticipant', 515, 'pwa-token', '2422720ed2e74a85be8b0f0ad845ace55df52697ef03bf8230b5cb567511ff40', '[\"*\"]', NULL, NULL, '2025-10-18 00:38:23', '2025-10-18 00:38:23'),
(44, 'App\\Models\\PwaParticipant', 516, 'pwa-token', '5d5b4453e2da43d82fc1feec33c5a8d80067f885c637d84f0d631669f8b9e7a4', '[\"*\"]', NULL, NULL, '2025-10-18 01:10:23', '2025-10-18 01:10:23'),
(45, 'App\\Models\\PwaParticipant', 517, 'pwa-token', 'd373a4b63c9341323151aef4eed2d8120d9b8f9b6bc3f537952ac0678e17dfce', '[\"*\"]', NULL, NULL, '2025-10-18 01:24:16', '2025-10-18 01:24:16'),
(48, 'App\\Models\\PwaParticipant', 102, 'pwa-token', '892affc0f17a76f26caa03f08e86ae2cf043fdd50293a18b424ea1abae7800bd', '[\"*\"]', '2025-10-18 07:35:36', NULL, '2025-10-18 06:24:02', '2025-10-18 07:35:36'),
(49, 'App\\Models\\PwaParticipant', 102, 'pwa-token', '793c1dc6785f50944f8136be2ab07d993ffd0a9c0cb11e35d87e7fe88671a38a', '[\"*\"]', NULL, NULL, '2025-10-18 07:35:54', '2025-10-18 07:35:54'),
(50, 'App\\Models\\PwaParticipant', 102, 'pwa-token', 'def27310e630c86fccc0300b07c9fe05da94789bfcb5b5ef4d7f737f8095f72d', '[\"*\"]', '2025-10-18 08:14:22', NULL, '2025-10-18 07:47:48', '2025-10-18 08:14:22'),
(51, 'App\\Models\\PwaParticipant', 102, 'pwa-token', 'fd30d622c116a49e98015cc9638e57e573e23122731f29223caad1d100eacb86', '[\"*\"]', '2025-10-18 08:02:50', NULL, '2025-10-18 08:02:50', '2025-10-18 08:02:50'),
(52, 'App\\Models\\PwaParticipant', 102, 'pwa-token', 'daa6e058ea4ab9d0572672521e2380f0ff1b52bbd9f9989bf0dcce397cc4231d', '[\"*\"]', '2025-10-18 14:44:49', NULL, '2025-10-18 08:09:32', '2025-10-18 14:44:49'),
(53, 'App\\Models\\PwaParticipant', 102, 'pwa-token', 'c408f435c6f3913089735211cf287dba69097cb360357ef59dcda4ceebe92270', '[\"*\"]', '2025-10-18 14:52:41', NULL, '2025-10-18 14:52:40', '2025-10-18 14:52:41'),
(58, 'App\\Models\\PwaParticipant', 518, 'pwa-token', '4165ab23896f422e811b80ee286c2b5d372bbcdfa0d86f1250de445915fe34b6', '[\"*\"]', NULL, NULL, '2026-04-16 13:20:51', '2026-04-16 13:20:51'),
(59, 'App\\Models\\PwaParticipant', 518, 'pwa-token', 'e2c4cc61c8dab0fed7218a56efe9035acd95644282345ac9eefa754db3d24650', '[\"*\"]', '2026-04-16 14:41:13', NULL, '2026-04-16 14:39:45', '2026-04-16 14:41:13'),
(60, 'App\\Models\\PwaParticipant', 518, 'pwa-token', '245df435461e97df29fd6bf040d062b539fbab02e5d3bd4ef16b163de9223952', '[\"*\"]', NULL, NULL, '2026-04-22 16:32:30', '2026-04-22 16:32:30');

-- --------------------------------------------------------

--
-- Table structure for table `pwa_email_logs`
--

CREATE TABLE `pwa_email_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `template_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pwa_email_logs`
--

INSERT INTO `pwa_email_logs` (`id`, `template_id`, `action`, `quantity`, `meta`, `created_at`, `updated_at`) VALUES
(1, NULL, 'sent', 10, NULL, '2025-10-15 05:15:15', '2025-10-15 05:15:15'),
(2, NULL, 'open', 7, NULL, '2025-10-15 05:15:15', '2025-10-15 05:15:15'),
(3, NULL, 'click', 3, NULL, '2025-10-15 05:15:15', '2025-10-15 05:15:15'),
(4, NULL, 'bounce', 1, NULL, '2025-10-15 05:15:15', '2025-10-15 05:15:15'),
(5, 4, 'sent', 1, '{\"test\": true}', '2025-10-15 05:42:55', '2025-10-15 05:42:55'),
(6, 4, 'sent', 1, '{\"test\": true}', '2025-10-15 06:03:38', '2025-10-15 06:03:38'),
(7, 1, 'sent', 0, '{\"bulk\": true}', '2025-10-15 06:03:42', '2025-10-15 06:03:42'),
(8, 4, 'sent', 1, '{\"to\": \"faizanrahman84@gmail.com\", \"test\": true}', '2025-10-15 06:07:32', '2025-10-15 06:07:32'),
(9, 4, 'sent', 1, '{\"to\": \"faizanrahman84@gmail.com\", \"test\": true}', '2025-10-15 06:08:32', '2025-10-15 06:08:32'),
(10, 4, 'sent', 1, '{\"to\": \"faizanrahman84@gmail.com\", \"test\": true}', '2025-10-15 06:09:17', '2025-10-15 06:09:17'),
(11, 4, 'sent', 1, '{\"to\": \"faizanrahman84@gmail.com\", \"test\": true}', '2025-10-15 06:09:29', '2025-10-15 06:09:29'),
(12, 4, 'sent', 1, '{\"to\": \"faizanrahman84@gmail.com\", \"test\": true}', '2025-10-15 06:13:02', '2025-10-15 06:13:02'),
(13, 5, 'sent', 1, '{\"to\": \"faizanrahman84@gmail.com\", \"context\": \"password_reset\"}', '2025-10-16 17:40:12', '2025-10-16 17:40:12'),
(14, 5, 'sent', 1, '{\"to\": \"faizanrahman84@gmail.com\", \"context\": \"password_reset\"}', '2025-10-16 17:52:12', '2025-10-16 17:52:12'),
(15, 5, 'sent', 1, '{\"to\": \"khairuni90@gmail.com\", \"context\": \"password_reset\"}', '2025-10-18 04:47:55', '2025-10-18 04:47:55'),
(16, 5, 'sent', 1, '{\"to\": \"khairuni90@sarawak.gov.my\", \"context\": \"password_reset\"}', '2026-04-16 01:44:47', '2026-04-16 01:44:47');

-- --------------------------------------------------------

--
-- Table structure for table `pwa_email_templates`
--

CREATE TABLE `pwa_email_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('welcome','password_reset','event_reminder','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scope` enum('global','organizer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'global',
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `times_used` int NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pwa_email_templates`
--

INSERT INTO `pwa_email_templates` (`id`, `name`, `type`, `subject`, `content`, `scope`, `user_id`, `is_active`, `times_used`, `last_used_at`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Welcome Email', 'welcome', 'Welcome to E-Certificate Online - Your PWA Access', '<p><strong>Dear @{{name}},</strong></p>\n<p>Welcome to <strong>E-Certificate Online</strong>! Your account has been successfully created and you now have access to our mobile application.</p>\n\n<div class=\"bg-gray-50 p-3 rounded my-4\">\n    <p class=\"text-sm font-medium mb-2\">Your Login Credentials:</p>\n    <p class=\"text-sm\"><strong>Email:</strong> @{{email}}</p>\n    <p class=\"text-sm\"><strong>Temporary Password:</strong> @{{password}}</p>\n</div>\n\n<p><strong>Important:</strong> For security reasons, you will be required to change your password on your first login.</p>\n\n<div class=\"bg-blue-50 p-3 rounded my-4\">\n    <p class=\"text-sm font-medium mb-2\">Getting Started:</p>\n    <ol class=\"text-sm list-decimal list-inside space-y-1\">\n        <li>Download our mobile app or visit: @{{pwa_link}}</li>\n        <li>Login with your email and temporary password</li>\n        <li>Change your password when prompted</li>\n        <li>Start exploring your events and certificates!</li>\n    </ol>\n</div>\n\n<p>If you have any questions or need assistance, please contact us at @{{support_email}}.</p>\n\n<p>Best regards,<br>\n<strong>E-Certificate Online Team</strong></p>', 'organizer', 2, 1, 2, '2025-10-15 06:03:42', 2, NULL, '2025-07-29 02:47:51', '2025-10-15 06:03:42'),
(2, 'Password Reset', 'password_reset', 'Password Reset Request - E-Certificate Online', '<p><strong>Dear @{{name}},</strong></p>\n<p>We received a request to reset your password for your E-Certificate Online account.</p>\n\n<div class=\"bg-gray-50 p-3 rounded my-4\">\n    <p class=\"text-sm font-medium mb-2\">Your New Password:</p>\n    <p class=\"text-sm\"><strong>@{{password}}</strong></p>\n</div>\n\n<p><strong>Important:</strong> For security reasons, you will be required to change this password on your next login.</p>\n\n<p>If you did not request this password reset, please contact us immediately at @{{support_email}}.</p>\n\n<p>Best regards,<br>\n<strong>E-Certificate Online Team</strong></p>', 'organizer', 2, 1, 0, NULL, 2, NULL, '2025-07-29 02:47:51', '2025-07-29 02:47:51'),
(3, 'Event Reminder', 'event_reminder', 'Event Reminder - @{{event_name}}', '<p><strong>Dear @{{name}},</strong></p>\n<p>This is a friendly reminder about your upcoming event.</p>\n\n<div class=\"bg-blue-50 p-3 rounded my-4\">\n    <p class=\"text-sm font-medium mb-2\">Event Details:</p>\n    <p class=\"text-sm\"><strong>Event:</strong> @{{event_name}}</p>\n    <p class=\"text-sm\"><strong>Organization:</strong> @{{organization}}</p>\n</div>\n\n<p>Please make sure to:</p>\n<ul class=\"text-sm list-disc list-inside space-y-1\">\n    <li>Arrive on time for check-in</li>\n    <li>Bring your mobile device for QR code scanning</li>\n    <li>Have your login credentials ready</li>\n</ul>\n\n<p>If you have any questions, please contact us at @{{support_email}}.</p>\n\n<p>Best regards,<br>\n<strong>E-Certificate Online Team</strong></p>', 'organizer', 2, 0, 0, NULL, 2, NULL, '2025-07-29 02:47:51', '2025-07-29 02:47:51'),
(4, 'Welcome Email', 'welcome', 'Welcome to E-Certificate Online - Your PWA Access', '<p><strong>Dear @{{name}},</strong></p>\r\n<p>Welcome to <strong>E-Certificate Online</strong>! Your account has been successfully created and you now have access to our mobile application.</p>\r\n\r\n<div class=\"bg-gray-50 p-3 rounded my-4\">\r\n    <p class=\"text-sm font-medium mb-2\">Your Login Credentials:</p>\r\n    <p class=\"text-sm\"><strong>Email:</strong> @{{email}}</p>\r\n    <p class=\"text-sm\"><strong>Temporary Password:</strong> @{{password}}</p>\r\n</div>\r\n\r\n<p><strong>Important:</strong> For security reasons, you will be required to change your password on your first login.</p>\r\n\r\n<div class=\"bg-blue-50 p-3 rounded my-4\">\r\n    <p class=\"text-sm font-medium mb-2\">Getting Started:</p>\r\n    <ol class=\"text-sm list-decimal list-inside space-y-1\">\r\n        <li>Download our mobile app or visit: @{{pwa_link}}</li>\r\n        <li>Login with your email and temporary password</li>\r\n        <li>Change your password when prompted</li>\r\n        <li>Start exploring your events and certificates!</li>\r\n    </ol>\r\n</div>\r\n\r\n<p>If you have any questions or need assistance, please contact us at @{{support_email}}.</p>\r\n\r\n<p>Best regards,<br>\r\n<strong>E-Certificate Online Team</strong></p>', 'global', NULL, 1, 9, '2025-10-15 06:13:02', 1, 1, '2025-07-29 02:47:57', '2025-10-15 06:13:02'),
(5, 'Password Reset', 'password_reset', 'Password Reset Request - E-Certificate Online', '<p><strong>Dear @{{name}},</strong></p>\r\n<p>We received a request to reset your password for your E-Certificate Online account.</p>\r\n\r\n<div class=\"bg-gray-50 p-3 rounded my-4\">\r\n    <p class=\"text-sm font-medium mb-2\">Your New Password:</p>\r\n    <p class=\"text-sm\"><strong>@{{password}}</strong></p>\r\n</div>\r\n\r\n<p><strong>Important:</strong> For security reasons, you will be required to change this password on your next login.</p>\r\n\r\n<p>If you did not request this password reset, please contact us immediately at @{{support_email}}.</p>\r\n\r\n<p>Best regards,<br>\r\n<strong>E-Certificate Online Team</strong></p>', 'global', NULL, 1, 4, '2026-04-16 01:44:47', 1, 1, '2025-07-29 02:47:57', '2026-04-16 01:44:47'),
(6, 'Event Reminder', 'event_reminder', 'Event Reminder - @{{event_name}}', '<p><strong>Dear @{{name}},</strong></p>\r\n<p>This is a friendly reminder about your upcoming event.</p>\r\n\r\n<div class=\"bg-blue-50 p-3 rounded my-4\">\r\n    <p class=\"text-sm font-medium mb-2\">Event Details:</p>\r\n    <p class=\"text-sm\"><strong>Event:</strong> @{{event_name}}</p>\r\n    <p class=\"text-sm\"><strong>Organization:</strong> @{{organization}}</p>\r\n</div>\r\n\r\n<p>Please make sure to:</p>\r\n<ul class=\"text-sm list-disc list-inside space-y-1\">\r\n    <li>Arrive on time for check-in</li>\r\n    <li>Bring your mobile device for QR code scanning</li>\r\n    <li>Have your login credentials ready</li>\r\n</ul>\r\n\r\n<p>If you have any questions, please contact us at @{{support_email}}.</p>\r\n\r\n<p>Best regards,<br>\r\n<strong>E-Certificate Online Team</strong></p>', 'global', NULL, 1, 0, NULL, 1, 1, '2025-07-29 02:47:57', '2025-10-15 06:08:15');

-- --------------------------------------------------------

--
-- Table structure for table `pwa_participants`
--

CREATE TABLE `pwa_participants` (
  `id` bigint UNSIGNED NOT NULL,
  `related_participant_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `job_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `identity_card` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Malaysia',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pwa_participants`
--

INSERT INTO `pwa_participants` (`id`, `related_participant_id`, `name`, `email`, `username`, `phone`, `password`, `organization`, `address`, `is_active`, `password_changed_at`, `created_by`, `updated_by`, `job_title`, `gender`, `date_of_birth`, `identity_card`, `passport_no`, `address1`, `address2`, `city`, `state`, `postcode`, `country`, `notes`, `status`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(518, NULL, 'Mohamad Faizan Bin Abdul Rahman', 'faizanrahman84@gmail.com', 'faizanrahman84', NULL, '$argon2id$v=19$m=65536,t=4,p=1$UnJ6d0cubHY2Sy5hUWJTbA$xM9Y8TXglW/t68vmWnBK3PyVIUeNAirK1NfP1KFhrVQ', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Malaysia', NULL, 'active', NULL, NULL, '2026-04-16 13:20:51', '2026-04-16 13:20:51');

-- --------------------------------------------------------

--
-- Table structure for table `pwa_settings`
--

CREATE TABLE `pwa_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `scope` enum('global','organizer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'global',
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `settings` json NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pwa_settings`
--

INSERT INTO `pwa_settings` (`id`, `scope`, `user_id`, `settings`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'organizer', 2, '{\"sync_name\": true, \"sync_email\": true, \"sync_phone\": true, \"pwa_app_link\": \"https://apps.e-certificate.com.my\", \"sync_address\": false, \"support_email\": \"support@e-certificate.com.my\", \"checkbox_label\": \"Enable E-Certificate Online mobile access\", \"real_time_sync\": true, \"include_numbers\": true, \"password_expiry\": \"never\", \"password_length\": 8, \"session_timeout\": \"60\", \"include_app_link\": true, \"lockout_duration\": \"30\", \"enable_pwa_access\": true, \"include_lowercase\": true, \"include_uppercase\": true, \"sync_organization\": true, \"default_pwa_access\": \"enabled\", \"max_login_attempts\": 5, \"send_welcome_email\": true, \"auto_create_accounts\": true, \"force_password_change\": true, \"include_special_chars\": false, \"checkbox_default_state\": \"checked\"}', 2, NULL, '2025-07-29 02:48:05', '2025-07-29 02:48:05'),
(2, 'global', NULL, '{\"sync_name\": true, \"sync_email\": true, \"sync_phone\": true, \"pwa_app_link\": \"https://apps.e-certificate.com.my\", \"sync_address\": false, \"support_email\": \"support@e-certificate.com.my\", \"checkbox_label\": \"Enable E-Certificate Online mobile access\", \"real_time_sync\": true, \"include_numbers\": true, \"password_expiry\": \"never\", \"password_length\": 8, \"session_timeout\": \"60\", \"include_app_link\": true, \"lockout_duration\": \"30\", \"enable_pwa_access\": true, \"include_lowercase\": true, \"include_uppercase\": true, \"sync_organization\": true, \"default_pwa_access\": \"enabled\", \"max_login_attempts\": 5, \"send_welcome_email\": true, \"auto_create_accounts\": true, \"force_password_change\": true, \"include_special_chars\": false, \"checkbox_default_state\": \"checked\"}', 1, NULL, '2025-07-29 02:48:07', '2025-07-29 02:48:07');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modified_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `description`, `status`, `created_by`, `modified_by`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'web', 'System Administrator with full access to all features', 'active', 'System', 'Admin User', '2025-07-24 06:38:05', '2025-10-15 07:55:17'),
(2, 'Organizer', 'web', 'Event Organizer with limited permissions', 'active', 'System', 'Admin User', '2025-07-24 06:38:05', '2025-10-15 02:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(35, 1),
(36, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(112, 1),
(113, 1),
(114, 1),
(115, 1),
(116, 1),
(117, 1),
(118, 1),
(119, 1),
(120, 1),
(121, 1),
(122, 1),
(123, 1),
(124, 1),
(125, 1),
(127, 1),
(128, 1),
(129, 1),
(130, 1),
(131, 1),
(132, 1),
(133, 1),
(134, 1),
(135, 1),
(136, 1),
(137, 1),
(138, 1),
(139, 1),
(140, 1),
(141, 1),
(142, 1),
(143, 1),
(144, 1),
(145, 1),
(146, 1),
(147, 1),
(148, 1),
(149, 1),
(150, 1),
(151, 1),
(152, 1),
(153, 1),
(154, 1),
(155, 1),
(156, 1),
(157, 1),
(158, 1),
(159, 1),
(160, 1),
(161, 1),
(162, 1),
(163, 1),
(164, 1),
(165, 1),
(166, 1),
(167, 1),
(168, 1),
(170, 1),
(171, 1),
(172, 1),
(174, 1),
(175, 1),
(176, 1),
(177, 1),
(178, 1),
(179, 1),
(180, 1),
(1, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(35, 2),
(36, 2),
(107, 2),
(108, 2),
(109, 2),
(110, 2),
(111, 2),
(112, 2),
(113, 2),
(114, 2),
(115, 2),
(116, 2),
(117, 2),
(118, 2),
(119, 2),
(120, 2),
(121, 2),
(122, 2),
(123, 2),
(124, 2),
(125, 2),
(127, 2),
(128, 2),
(129, 2),
(130, 2),
(131, 2),
(132, 2),
(133, 2),
(134, 2),
(135, 2),
(136, 2),
(137, 2),
(138, 2),
(139, 2),
(140, 2),
(141, 2),
(142, 2),
(143, 2),
(144, 2),
(145, 2),
(146, 2),
(147, 2),
(148, 2),
(149, 2),
(150, 2),
(151, 2),
(152, 2),
(153, 2),
(154, 2),
(155, 2),
(168, 2),
(170, 2),
(171, 2),
(172, 2),
(173, 2),
(174, 2),
(175, 2),
(176, 2);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `surveys`
--

CREATE TABLE `surveys` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `access_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `allow_anonymous` tinyint(1) NOT NULL DEFAULT '1',
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surveys`
--

INSERT INTO `surveys` (`id`, `title`, `description`, `event_id`, `user_id`, `status`, `access_type`, `allow_anonymous`, `slug`, `published_at`, `expires_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 'A', 'A', 7, 1, 'published', 'public', 0, 'a-2c9o3ut2', '2026-04-16 23:15:42', NULL, '2026-04-16 23:14:12', '2026-04-16 23:15:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `survey_questions`
--

CREATE TABLE `survey_questions` (
  `id` bigint UNSIGNED NOT NULL,
  `survey_id` bigint UNSIGNED NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `question_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `options` json DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `validation_rules` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `survey_questions`
--

INSERT INTO `survey_questions` (`id`, `survey_id`, `order`, `question_type`, `question_text`, `description`, `options`, `required`, `validation_rules`, `created_at`, `updated_at`) VALUES
(10, 3, 1, 'text', 'a', NULL, NULL, 0, NULL, '2026-04-16 23:14:33', '2026-04-16 23:14:33'),
(11, 3, 2, 'text', 'b', NULL, NULL, 0, NULL, '2026-04-16 23:14:44', '2026-04-16 23:14:44');

-- --------------------------------------------------------

--
-- Table structure for table `survey_responses`
--

CREATE TABLE `survey_responses` (
  `id` bigint UNSIGNED NOT NULL,
  `survey_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `participant_id` bigint UNSIGNED DEFAULT NULL,
  `respondent_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'anonymous',
  `respondent_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `respondent_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `respondent_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_data` json NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `survey_responses`
--

INSERT INTO `survey_responses` (`id`, `survey_id`, `user_id`, `participant_id`, `respondent_type`, `respondent_email`, `respondent_name`, `respondent_phone`, `session_id`, `response_data`, `ip_address`, `user_agent`, `completed`, `started_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(4, 3, 1, NULL, 'user', NULL, NULL, NULL, 'nMMvLX1BsXkdUcwqNaHggdl7czg8HjpQIUzrzePt', '[]', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 0, '2026-04-16 23:15:48', NULL, '2026-04-16 23:15:48', '2026-04-16 23:15:48'),
(5, 3, 1, NULL, 'user', NULL, NULL, NULL, 'd0zrbzJKbV48Bo3LhgujiY3aCtOApPUSUKCVA0Ef', '[]', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 0, '2026-04-18 00:08:48', NULL, '2026-04-18 00:08:48', '2026-04-18 00:08:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organization` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive','banned') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `address_line1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_type` enum('company','government','ngo','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_address_line1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_address_line2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_postcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_telephone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_fax` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `org_website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `organization`, `role_id`, `status`, `last_login_at`, `address_line1`, `address_line2`, `city`, `state`, `postcode`, `country`, `org_type`, `org_name`, `org_address_line1`, `org_address_line2`, `org_city`, `org_state`, `org_postcode`, `org_country`, `org_telephone`, `org_fax`, `org_email`, `org_website`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@e-certificate.com.my', '+60123456789', NULL, 1, 'active', '2026-04-22 14:13:02', '123 Main Street', 'Suite 100', NULL, NULL, NULL, 'Malaysia', 'government', 'Ministry of Technology', '456 Govt Street', '8th Floor', NULL, NULL, NULL, 'Malaysia', '+60323456789', '+60323456780', 'info@mtech.gov.my', 'https://mtech.gov.my', '2025-07-24 06:38:04', '$argon2id$v=19$m=65536,t=4,p=1$N25PVy5ELlYvZUVaUXZQQg$dAXm9+kOVD7vXJa4u2vkcDJwzorh2/nALe5L1QxHjqk', NULL, '2025-07-24 06:38:04', '2026-04-22 14:13:02'),
(2, 'Organizer User', 'organizer@e-certificate.com.my', '+60129876543', NULL, 2, 'active', '2026-04-22 14:13:29', '789 Oak Avenue', 'Apt 45', 'Petaling Jaya', 'Selangor', '46000', 'Malaysia', 'company', 'Event Solutions Sdn Bhd', '101 Business Park', 'Unit 302', 'Petaling Jaya', 'Selangor', NULL, 'Malaysia', '+60378901234', '+60378901235', 'info@eventsolutions.com.my', 'https://eventsolutions.com.my', '2025-07-24 06:38:04', '$argon2id$v=19$m=65536,t=4,p=1$T3BKYUl6OGJwOVEvMzlYSQ$a4XGjy3/dVUtqM9ndsVfO+DYjVUKRwJWJ7R+9XT3Crs', NULL, '2025-07-24 06:38:04', '2026-04-22 14:13:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_unique_code_unique` (`unique_code`),
  ADD KEY `attendances_event_id_foreign` (`event_id`),
  ADD KEY `attendances_created_by_foreign` (`created_by`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_records_attendance_id_foreign` (`attendance_id`),
  ADD KEY `attendance_records_participant_id_foreign` (`participant_id`);

--
-- Indexes for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_sessions_attendance_id_foreign` (`attendance_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaigns_user_id_foreign` (`user_id`),
  ADD KEY `campaigns_event_id_foreign` (`event_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificates_certificate_number_unique` (`certificate_number`),
  ADD KEY `certificates_event_id_foreign` (`event_id`),
  ADD KEY `certificates_participant_id_foreign` (`participant_id`),
  ADD KEY `certificates_template_id_foreign` (`template_id`),
  ADD KEY `certificates_generated_by_foreign` (`generated_by`);

--
-- Indexes for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `certificate_templates_user_id_foreign` (`user_id`);

--
-- Indexes for table `database_participants`
--
ALTER TABLE `database_participants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_configs`
--
ALTER TABLE `delivery_configs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_configs_user_id_config_type_provider_unique` (`user_id`,`config_type`,`provider`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `events_registration_link_unique` (`registration_link`),
  ADD KEY `events_user_id_foreign` (`user_id`);

--
-- Indexes for table `event_pwa_participant`
--
ALTER TABLE `event_pwa_participant`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_pwa_participant_event_id_pwa_participant_id_unique` (`event_id`,`pwa_participant_id`),
  ADD KEY `event_pwa_participant_pwa_participant_id_foreign` (`pwa_participant_id`),
  ADD KEY `event_pwa_participant_attendance_record_id_foreign` (`attendance_record_id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_registrations_pwa_participant_id_event_id_unique` (`pwa_participant_id`,`event_id`),
  ADD KEY `event_registrations_event_id_foreign` (`event_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fcm_tokens`
--
ALTER TABLE `fcm_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fcm_tokens_token_unique` (`token`),
  ADD KEY `fcm_tokens_user_id_foreign` (`user_id`);

--
-- Indexes for table `global_configs`
--
ALTER TABLE `global_configs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `helpdesk_messages`
--
ALTER TABLE `helpdesk_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `helpdesk_messages_ticket_id_foreign` (`ticket_id`),
  ADD KEY `helpdesk_messages_user_id_foreign` (`user_id`);

--
-- Indexes for table `helpdesk_tickets`
--
ALTER TABLE `helpdesk_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `helpdesk_tickets_ticket_id_unique` (`ticket_id`),
  ADD KEY `helpdesk_tickets_user_id_foreign` (`user_id`),
  ADD KEY `helpdesk_tickets_assigned_to_foreign` (`assigned_to`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`),
  ADD KEY `notifications_created_at_index` (`created_at`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `participants_event_id_foreign` (`event_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `pwa_email_logs`
--
ALTER TABLE `pwa_email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pwa_email_logs_template_id_foreign` (`template_id`);

--
-- Indexes for table `pwa_email_templates`
--
ALTER TABLE `pwa_email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pwa_email_templates_organizer_id_foreign` (`user_id`),
  ADD KEY `pwa_email_templates_created_by_foreign` (`created_by`),
  ADD KEY `pwa_email_templates_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `pwa_participants`
--
ALTER TABLE `pwa_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pwa_participants_email_unique` (`email`),
  ADD KEY `pwa_participants_related_participant_id_foreign` (`related_participant_id`);

--
-- Indexes for table `pwa_settings`
--
ALTER TABLE `pwa_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pwa_settings_scope_organizer_id_unique` (`scope`,`user_id`),
  ADD KEY `pwa_settings_organizer_id_foreign` (`user_id`),
  ADD KEY `pwa_settings_created_by_foreign` (`created_by`),
  ADD KEY `pwa_settings_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `surveys`
--
ALTER TABLE `surveys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `surveys_slug_unique` (`slug`),
  ADD KEY `surveys_event_id_foreign` (`event_id`),
  ADD KEY `surveys_user_id_foreign` (`user_id`);

--
-- Indexes for table `survey_questions`
--
ALTER TABLE `survey_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `survey_questions_survey_id_foreign` (`survey_id`);

--
-- Indexes for table `survey_responses`
--
ALTER TABLE `survey_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `survey_responses_survey_id_foreign` (`survey_id`),
  ADD KEY `survey_responses_user_id_foreign` (`user_id`),
  ADD KEY `survey_responses_participant_id_foreign` (`participant_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `database_participants`
--
ALTER TABLE `database_participants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `delivery_configs`
--
ALTER TABLE `delivery_configs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `event_pwa_participant`
--
ALTER TABLE `event_pwa_participant`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fcm_tokens`
--
ALTER TABLE `fcm_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `global_configs`
--
ALTER TABLE `global_configs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `helpdesk_messages`
--
ALTER TABLE `helpdesk_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `helpdesk_tickets`
--
ALTER TABLE `helpdesk_tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `pwa_email_logs`
--
ALTER TABLE `pwa_email_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pwa_email_templates`
--
ALTER TABLE `pwa_email_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pwa_participants`
--
ALTER TABLE `pwa_participants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=519;

--
-- AUTO_INCREMENT for table `pwa_settings`
--
ALTER TABLE `pwa_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `surveys`
--
ALTER TABLE `surveys`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `survey_questions`
--
ALTER TABLE `survey_questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `survey_responses`
--
ALTER TABLE `survey_responses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_records_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD CONSTRAINT `attendance_sessions_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `campaigns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `certificate_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  ADD CONSTRAINT `certificate_templates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_configs`
--
ALTER TABLE `delivery_configs`
  ADD CONSTRAINT `delivery_configs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_pwa_participant`
--
ALTER TABLE `event_pwa_participant`
  ADD CONSTRAINT `event_pwa_participant_attendance_record_id_foreign` FOREIGN KEY (`attendance_record_id`) REFERENCES `attendance_records` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `event_pwa_participant_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_pwa_participant_pwa_participant_id_foreign` FOREIGN KEY (`pwa_participant_id`) REFERENCES `pwa_participants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_pwa_participant_id_foreign` FOREIGN KEY (`pwa_participant_id`) REFERENCES `pwa_participants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fcm_tokens`
--
ALTER TABLE `fcm_tokens`
  ADD CONSTRAINT `fcm_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `helpdesk_messages`
--
ALTER TABLE `helpdesk_messages`
  ADD CONSTRAINT `helpdesk_messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `helpdesk_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `helpdesk_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `helpdesk_tickets`
--
ALTER TABLE `helpdesk_tickets`
  ADD CONSTRAINT `helpdesk_tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `helpdesk_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pwa_email_logs`
--
ALTER TABLE `pwa_email_logs`
  ADD CONSTRAINT `pwa_email_logs_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `pwa_email_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pwa_email_templates`
--
ALTER TABLE `pwa_email_templates`
  ADD CONSTRAINT `pwa_email_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pwa_email_templates_organizer_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pwa_email_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pwa_participants`
--
ALTER TABLE `pwa_participants`
  ADD CONSTRAINT `pwa_participants_related_participant_id_foreign` FOREIGN KEY (`related_participant_id`) REFERENCES `participants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pwa_settings`
--
ALTER TABLE `pwa_settings`
  ADD CONSTRAINT `pwa_settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pwa_settings_organizer_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pwa_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `surveys`
--
ALTER TABLE `surveys`
  ADD CONSTRAINT `surveys_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `surveys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `survey_questions`
--
ALTER TABLE `survey_questions`
  ADD CONSTRAINT `survey_questions_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `survey_responses`
--
ALTER TABLE `survey_responses`
  ADD CONSTRAINT `survey_responses_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `survey_responses_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `survey_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
