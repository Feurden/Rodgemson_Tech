-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2026 at 08:12 AM
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
-- Database: `rodgemson_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `customer_info` text DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `phone_model` varchar(100) DEFAULT NULL,
  `phone_issue` text DEFAULT NULL,
  `diagnostic` text DEFAULT NULL,
  `suggested_part_replacement` text NOT NULL,
  `notes` text DEFAULT NULL,
  `created` datetime DEFAULT current_timestamp(),
  `modified` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `customer_info`, `contact_no`, `phone_model`, `phone_issue`, `diagnostic`, `suggested_part_replacement`, `notes`, `created`, `modified`) VALUES
(1, 'test 1', NULL, '1', 'Test 1', 'ghost touch', 'Touch Controller Issue', 'Touch Controller IC, Digitizer, Touch Flex Cable, LCD Screen Assembly, Power Management IC', '', '2026-03-26 06:35:10', '2026-03-26 06:35:10'),
(2, 'Test 2', NULL, '2', 'Test 2', 'not charging', 'Charging Port Issue', 'Charging Port, USB Connector, Charging Flex Cable, Charging IC, Power IC', '', '2026-03-26 06:39:56', '2026-03-26 06:39:56'),
(3, 'kalbo', NULL, '09453385748', 'poco x 7 pro', 'crack screen, not charging', 'Charging Port Issue + Display IC Issue + Display Issue', 'Charging Port, USB Connector, Charging Flex Cable, Charging IC, Power IC, Display Driver IC, Backlight IC, LCD Screen, Display Flex Cable, GPU IC, LCD/OLED Screen Assembly, Front Glass Digitizer, Screen Frame, Adhesive Seal Kit', '', '2026-03-27 05:13:57', '2026-03-27 05:13:57'),
(4, 'Yuan Malab', NULL, '09453385748', 'Samsung note20', 'nonresponsive screen', 'Display IC Issue + Touch Controller Issue + Software/OS Issue', 'Display Driver IC, Backlight IC, LCD Screen, Display Flex Cable, GPU IC, Touch Controller IC, Digitizer, Touch Flex Cable, LCD Screen Assembly, Power Management IC', '', '2026-03-27 06:27:26', '2026-03-27 06:27:26'),
(5, 'kulot', NULL, '09453385748', 'iphone 7', 'cant charge', 'Charging Port Issue', 'Charging Port, USB Connector, Charging Flex Cable, Charging IC, Power IC', '', '2026-03-27 06:48:38', '2026-03-27 06:48:38'),
(6, 'yuan', NULL, '9999999999', 'poco x 7 pro', 'lines on the screen, not charging', 'Charging Port Issue + Display IC Issue + Display Issue', 'Charging Port, USB Connector, Charging Flex Cable, Charging IC, Power IC, Display Driver IC, Backlight IC, LCD Screen, Display Flex Cable, GPU IC, LCD/OLED Screen Assembly, Front Glass Digitizer, Screen Frame, Adhesive Seal Kit', '', '2026-03-27 06:50:04', '2026-03-27 07:01:53'),
(7, 'Renante Kalbo', NULL, '0981123123', 'Cherry Mobile S3', 'ghost touch, battery draining fast', 'Battery Issue + Touch Controller Issue', 'Battery, Battery Connector, Power IC, Charging IC, Charging Flex Cable, Touch Controller IC, Digitizer, Touch Flex Cable, LCD Screen Assembly, Power Management IC', '', '2026-03-27 07:06:39', '2026-03-27 07:06:39');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `imei` varchar(50) DEFAULT NULL,
  `issue_description` text DEFAULT NULL,
  `technician` varchar(150) DEFAULT 'Unassigned',
  `status` enum('Pending','In Progress','Waiting Parts','Completed','Released') DEFAULT 'Pending',
  `priority_level` enum('Low','Medium','High') DEFAULT 'Low',
  `date_received` datetime DEFAULT current_timestamp(),
  `date_released` datetime DEFAULT NULL,
  `created` datetime DEFAULT current_timestamp(),
  `modified` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `customer_id`, `brand`, `model`, `imei`, `issue_description`, `technician`, `status`, `priority_level`, `date_received`, `date_released`, `created`, `modified`) VALUES
(1, 1, 'Test', '1', NULL, 'ghost touch', 'Rod Baclig', 'Completed', 'Medium', '2026-03-26 06:35:10', '2026-03-26 00:00:00', '2026-03-26 06:35:10', '2026-03-26 06:35:33'),
(2, 2, 'Test', '2', NULL, 'not charging', 'Rodel Baclig', 'Completed', 'Medium', '2026-03-26 06:39:56', '2026-03-26 00:00:00', '2026-03-26 06:39:56', '2026-03-26 07:36:27'),
(3, 3, 'poco', 'x 7 pro', NULL, 'crack screen, not charging', 'Rodel Baclig', 'Completed', 'Medium', '2026-03-27 05:13:57', '2026-03-27 00:00:00', '2026-03-27 05:13:57', '2026-03-27 05:22:32'),
(4, 4, 'Samsung', 'note20', NULL, 'nonresponsive screen', 'Rod Baclig', 'Completed', 'Medium', '2026-03-27 06:27:26', NULL, '2026-03-27 06:27:26', '2026-03-27 06:44:06'),
(5, 5, 'iphone', '7', NULL, 'cant charge', 'Rod Baclig', 'Completed', 'Medium', '2026-03-27 06:48:38', NULL, '2026-03-27 06:48:38', '2026-03-27 06:49:02'),
(6, 6, 'poco', 'x 7 pro', NULL, 'lines on the screen, not charging', 'Rod Baclig', 'Completed', 'Medium', '2026-03-27 06:50:04', '2026-03-27 00:00:00', '2026-03-27 06:50:04', '2026-03-27 07:03:42'),
(7, 7, 'Cherry', 'Mobile S3', NULL, 'ghost touch, battery draining fast', 'Rod Baclig', 'In Progress', 'Medium', '2026-03-27 07:06:39', NULL, '2026-03-27 07:06:39', '2026-03-27 07:09:52');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('Low Stock','Pending Repair','Priority Alert') DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `part_name` varchar(150) DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `phone_model` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `status` enum('Pending','Ordered','Received') DEFAULT 'Pending',
  `created` datetime DEFAULT current_timestamp(),
  `modified` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `part_name`, `customer_name`, `phone_model`, `notes`, `quantity`, `status`, `created`, `modified`) VALUES
(0, 'LCD', 'juan', 'samsung a35', 'screen not touching', 1, 'Pending', '2026-03-25 14:56:26', '2026-03-25 14:56:26');

-- --------------------------------------------------------

--
-- Table structure for table `parts`
--

CREATE TABLE `parts` (
  `id` int(11) NOT NULL,
  `part_name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `minimum_stock` int(11) DEFAULT 5,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `created` datetime DEFAULT current_timestamp(),
  `modified` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parts`
--

INSERT INTO `parts` (`id`, `part_name`, `category`, `stock_quantity`, `minimum_stock`, `unit_price`, `created`, `modified`) VALUES
(0, 'Speaker Module', 'Battery', 99, 0, 180.00, '2026-03-24 23:44:47', '2026-03-24 23:44:47'),
(1, 'LCD Screen', 'Screen Part', 99, 3, 650.00, '2026-03-02 17:04:17', '2026-03-27 07:03:30'),
(2, 'Charging Cable', 'Charging', 20, 5, 55.00, '2026-03-10 14:47:19', '2026-03-24 15:00:00'),
(3, 'Mainboard/Motherboard', 'Mainboard', 4, 2, 850.00, '2026-03-11 22:40:02', '2026-03-11 15:13:12'),
(4, 'CPU', 'Mainboard', 20, 2, 450.00, '2026-03-11 22:40:02', '2026-03-27 07:09:10'),
(5, 'RAM', 'Mainboard', 5, 2, 250.00, '2026-03-11 22:40:02', '2026-03-11 22:40:02'),
(6, 'Power IC', 'Power', 8, 3, 120.00, '2026-03-11 22:40:02', '2026-03-11 16:33:16'),
(7, 'Power Management IC', 'Power', 9, 3, 150.00, '2026-03-11 22:40:02', '2026-03-25 14:42:50'),
(8, 'Charging IC', 'Power', 7, 3, 95.00, '2026-03-11 22:40:02', '2026-03-27 05:22:17'),
(9, 'Battery', 'Battery', 15, 5, 180.00, '2026-03-11 22:40:02', '2026-03-27 07:09:52'),
(10, 'Battery Connector', 'Battery', 9, 3, 45.00, '2026-03-11 22:40:02', '2026-03-25 14:42:50'),
(11, 'Thermal Sensor IC', 'Battery', 6, 3, 60.00, '2026-03-11 22:40:02', '2026-03-24 15:25:22'),
(12, 'Charging Port', 'Charging', 7, 3, 75.00, '2026-03-11 22:40:02', '2026-03-27 07:03:30'),
(13, 'USB Connector', 'Charging', 8, 3, 55.00, '2026-03-11 22:40:02', '2026-03-27 05:22:17'),
(14, 'Digitizer', 'Screen Part', 7, 2, 220.00, '2026-03-11 22:40:02', '2026-03-25 14:43:37'),
(15, 'Touch Controller IC', 'Display', 5, 2, 135.00, '2026-03-11 22:40:02', '2026-03-27 07:07:07'),
(16, 'Display Driver IC', 'Display', 8, 2, 145.00, '2026-03-11 22:40:02', '2026-03-24 15:00:00'),
(17, 'Backlight IC', 'Display', 7, 2, 85.00, '2026-03-11 22:40:02', '2026-03-27 06:28:24'),
(18, 'Speaker Module', 'Audio', 10, 3, 90.00, '2026-03-11 22:40:02', '2026-03-24 15:25:11'),
(19, 'Microphone Module', 'Audio', 10, 3, 65.00, '2026-03-11 22:40:02', '2026-03-24 15:00:00'),
(20, 'Audio IC', 'Audio', 9, 3, 80.00, '2026-03-11 22:40:02', '2026-03-24 15:00:00'),
(21, 'SIM Card Slot', 'Connectivity', 10, 3, 50.00, '2026-03-11 22:40:02', '2026-03-11 22:40:02'),
(22, 'SIM IC', 'Connectivity', 8, 3, 70.00, '2026-03-11 22:40:02', '2026-03-24 15:00:00'),
(23, 'Baseband IC', 'Connectivity', 8, 2, 160.00, '2026-03-11 22:40:02', '2026-03-24 15:00:00'),
(24, 'Antenna Module', 'Connectivity', 8, 2, 55.00, '2026-03-11 22:40:02', '2026-03-24 15:00:00'),
(25, 'RF IC', 'Connectivity', 8, 2, 110.00, '2026-03-11 22:40:02', '2026-03-24 15:00:00'),
(26, 'Flex Cable', 'Flex/Connectors', 15, 5, 35.00, '2026-03-11 22:40:02', '2026-03-24 15:00:00'),
(27, 'Connector Flex', 'Flex/Connectors', 14, 5, 40.00, '2026-03-11 22:40:02', '2026-03-24 15:00:00'),
(28, 'Reflash Firmware', 'Software Service', 99, 0, 200.00, '2026-03-11 22:40:02', '2026-03-24 15:00:00'),
(30, 'Mainboard Check', 'Software Service', 99, 0, 100.00, '2026-03-11 22:40:02', '2026-03-11 22:40:02'),
(31, 'Phone Case', 'Accessories', 70, 30, 100.00, '2026-03-23 09:55:13', '2026-03-23 09:55:43'),
(32, 'Ear Speaker', 'Audio', 12, 3, 85.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(33, 'Audio Codec IC', 'Audio', 8, 2, 95.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(34, 'Speaker Flex Cable', 'Flex/Connectors', 15, 5, 40.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(35, 'Sub Board', 'Mainboard', 10, 3, 120.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(36, 'Microphone Mesh', 'Accessories', 25, 5, 15.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(37, 'Charging Flex Cable', 'Flex/Connectors', 18, 5, 55.00, '2026-03-24 15:00:00', '2026-03-27 05:22:17'),
(38, 'Touch Flex Cable', 'Flex/Connectors', 14, 5, 45.00, '2026-03-24 15:00:00', '2026-03-25 14:43:37'),
(39, 'GPU IC', 'Mainboard', 5, 2, 280.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(40, 'Display Flex Cable', 'Flex/Connectors', 15, 5, 50.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(42, 'Antenna Cable', 'Connectivity', 12, 3, 45.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(43, 'Signal Booster IC', 'Connectivity', 6, 2, 180.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(46, 'LCD/OLED Screen Assembly', 'Screen Part', 110, 5, 850.00, '2026-03-24 15:00:00', '2026-03-27 07:07:45'),
(47, 'Front Glass Digitizer', 'Screen Part', 8, 3, 450.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(48, 'Screen Frame', 'Screen Part', 12, 5, 150.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(49, 'Adhesive Seal Kit', 'Accessories', 20, 5, 25.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(53, 'Connector Replacement', 'Service', 99, 0, 150.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00'),
(54, 'Battery Replacement', 'Service', 99, 0, 180.00, '2026-03-24 15:00:00', '2026-03-24 15:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `repair_diagnoses`
--

CREATE TABLE `repair_diagnoses` (
  `id` int(11) NOT NULL,
  `job_id` varchar(50) NOT NULL,
  `device` varchar(100) DEFAULT NULL,
  `customer_description` longtext DEFAULT NULL,
  `ai_diagnosis` varchar(255) DEFAULT NULL,
  `ai_confidence` float DEFAULT NULL,
  `ai_rule_based` tinyint(1) DEFAULT NULL,
  `actual_diagnosis` varchar(255) DEFAULT NULL,
  `actual_root_cause` longtext DEFAULT NULL,
  `parts_replaced` longtext DEFAULT NULL,
  `diagnosis_correct` tinyint(1) DEFAULT NULL,
  `technician_notes` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_diagnoses`
--

INSERT INTO `repair_diagnoses` (`id`, `job_id`, `device`, `customer_description`, `ai_diagnosis`, `ai_confidence`, `ai_rule_based`, `actual_diagnosis`, `actual_root_cause`, `parts_replaced`, `diagnosis_correct`, `technician_notes`, `created_at`, `completed_at`) VALUES
(0, 'D4', NULL, NULL, 'Display IC Issue + Touch Controller Issue + Software/OS Issue', NULL, NULL, NULL, NULL, NULL, 1, '', '2026-03-27 06:28:46', '2026-03-27 06:28:46');

-- --------------------------------------------------------

--
-- Table structure for table `repair_parts_usage`
--

CREATE TABLE `repair_parts_usage` (
  `id` int(10) UNSIGNED NOT NULL,
  `device_id` int(10) UNSIGNED NOT NULL,
  `part_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `returned` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `modified` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_parts_usage`
--

INSERT INTO `repair_parts_usage` (`id`, `device_id`, `part_id`, `quantity`, `returned`, `created`, `modified`) VALUES
(0, 1, 15, 1, 1, '2026-03-26 06:35:23', '2026-03-27 07:09:52'),
(0, 2, 12, 1, 1, '2026-03-26 06:40:08', '2026-03-27 07:09:52'),
(0, 2, 37, 1, 1, '2026-03-26 06:40:08', '2026-03-27 07:09:52'),
(0, 2, 8, 1, 1, '2026-03-26 06:40:08', '2026-03-27 07:09:52'),
(0, 3, 12, 1, 1, '2026-03-27 05:22:17', '2026-03-27 07:09:52'),
(0, 3, 13, 1, 1, '2026-03-27 05:22:17', '2026-03-27 07:09:52'),
(0, 3, 37, 1, 1, '2026-03-27 05:22:17', '2026-03-27 07:09:52'),
(0, 3, 8, 1, 1, '2026-03-27 05:22:17', '2026-03-27 07:09:52'),
(0, 4, 17, 1, 1, '2026-03-27 06:28:24', '2026-03-27 07:09:52'),
(0, 5, 12, 1, 1, '2026-03-27 06:48:47', '2026-03-27 07:09:52'),
(0, 6, 12, 1, 1, '2026-03-27 07:03:30', '2026-03-27 07:09:52'),
(0, 6, 1, 1, 1, '2026-03-27 07:03:30', '2026-03-27 07:09:52'),
(0, 7, 9, 1, 1, '2026-03-27 07:07:07', '2026-03-27 07:09:52'),
(0, 7, 15, 1, 1, '2026-03-27 07:07:07', '2026-03-27 07:09:52');

-- --------------------------------------------------------

--
-- Table structure for table `repair_services_usage`
--

CREATE TABLE `repair_services_usage` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created` datetime DEFAULT current_timestamp(),
  `modified` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_services_usage`
--

INSERT INTO `repair_services_usage` (`id`, `device_id`, `service_id`, `notes`, `created`, `modified`) VALUES
(1, 6, 14, NULL, '2026-03-27 07:03:30', '2026-03-27 07:03:30');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created` datetime DEFAULT current_timestamp(),
  `modified` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `category`, `price`, `description`, `created`, `modified`) VALUES
(1, 'Firmware Reinstall', 'Software Service', 200.00, 'Reinstall device firmware/ROM', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(2, 'OS Update', 'Software Service', 150.00, 'Update operating system to latest version', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(3, 'Factory Reset', 'Software Service', 100.00, 'Factory reset device to default settings', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(4, 'System Reflash', 'Software Service', 200.00, 'Reflash system software/firmware', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(5, 'Data Backup', 'Software Service', 250.00, 'Backup user data before repair', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(6, 'Data Recovery', 'Software Service', 500.00, 'Recover data from damaged device', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(7, 'Ultrasonic Cleaning', 'Cleaning Service', 350.00, 'Ultrasonic cleaning of mainboard and components', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(8, 'Mainboard Cleaning', 'Cleaning Service', 200.00, 'Manual cleaning of mainboard with alcohol', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(9, 'Port Cleaning', 'Cleaning Service', 50.00, 'Cleaning of charging port and connectors', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(10, 'Speaker/Mic Cleaning', 'Cleaning Service', 50.00, 'Cleaning of speaker and microphone grilles', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(11, 'Full Diagnostic Test', 'Diagnostic', 100.00, 'Comprehensive diagnostic of all components', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(12, 'Battery Health Test', 'Diagnostic', 50.00, 'Test battery capacity and health', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(13, 'Water Damage Assessment', 'Diagnostic', 150.00, 'Full assessment of water damage', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(14, 'Screen Replacement Service', 'Labor', 500.00, 'Labor for screen/digitizer replacement', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(15, 'Battery Replacement Service', 'Labor', 180.00, 'Labor for battery replacement', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(16, 'Mainboard Repair', 'Labor', 1500.00, 'Mainboard repair/rework service', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(17, 'Connector Replacement Service', 'Labor', 150.00, 'Labor for connector replacement', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(18, 'IC Reballing', 'Labor', 800.00, 'IC reballing service', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(19, 'Charging Port Replacement', 'Labor', 200.00, 'Labor for charging port replacement', '2026-03-24 23:54:29', '2026-03-24 23:54:29'),
(20, 'Water Damage Repair', 'Labor', 500.00, 'Labor for water damage repair', '2026-03-24 23:54:29', '2026-03-24 23:54:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `specialty` varchar(150) DEFAULT NULL,
  `avatar` varchar(4) DEFAULT NULL,
  `role` enum('admin','technician') DEFAULT 'technician',
  `created` datetime DEFAULT current_timestamp(),
  `modified` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `specialty`, `avatar`, `role`, `created`, `modified`) VALUES
(0, 'Rod Baclig', '$2y$10$XB.yo4eXQpwwQHN3cL/47e9.P8VGG10wxyX4w88sth6Z4PIMc8jam', 'Rod Baclig', 'rod@gmail.com', 'all', NULL, 'technician', '2026-03-18 19:13:05', '2026-03-23 17:24:02'),
(1, 'admin', '$2y$10$VOivfStmblic33R4jjXQ/OOFrYCPATDgMwKMjnlBAnZswVULbLDEq', 'ADMIN', 'rod.baclig@rodgemson.com', 'Board-Level & General Repairs', 'RB', 'admin', '2026-02-28 00:54:57', '2026-03-23 09:53:30'),
(2, 'rodel', '$2y$10$examplehashforjanesmith0000000000000000000000000000000', 'Rodel Baclig', 'rodel.baclig@rodgemson.com', 'Mobile Screen & Battery', 'RB', 'technician', '2026-02-28 00:54:57', '2026-02-28 00:54:57'),
(3, 'raymark', '$2y$10$examplehashformarklee000000000000000000000000000000000', 'Raymark Santos', 'raymark.santos@rodgemson.com', 'Charging & Water Damage', 'RS', 'technician', '2026-02-28 00:54:57', '2026-02-28 00:54:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repair_diagnoses`
--
ALTER TABLE `repair_diagnoses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_id` (`job_id`),
  ADD KEY `idx_diagnosis_correct` (`diagnosis_correct`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `repair_services_usage`
--
ALTER TABLE `repair_services_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_device_id` (`device_id`),
  ADD KEY `idx_service_id` (`service_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_name` (`service_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `repair_services_usage`
--
ALTER TABLE `repair_services_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `repair_services_usage`
--
ALTER TABLE `repair_services_usage`
  ADD CONSTRAINT `repair_services_usage_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `repair_services_usage_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
