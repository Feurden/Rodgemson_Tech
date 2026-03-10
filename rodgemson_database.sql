-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2026 at 05:09 PM
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
  `suggested_part_replacement` text DEFAULT NULL,
  `created` datetime DEFAULT current_timestamp(),
  `modified` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `customer_info`, `contact_no`, `phone_model`, `phone_issue`, `diagnostic`, `suggested_part_replacement`, `created`, `modified`) VALUES
(1, 'John Paul Guillermo', NULL, '09811836521', 'Iphone 7s', 'Touch Controller Issue', '', '', '2026-03-02 17:00:47', '2026-03-02 17:00:47'),
(2, 'Jaspher Dela Cruz', NULL, '0123123112', 'Xiomi 23', 'Microphone Issue', '', '', '2026-03-02 17:06:01', '2026-03-02 17:06:01'),
(3, 'David Galiza', NULL, '01231231', 'Infinix HOT10s', 'ghost touch, no mic, overheating', 'Mainboard Issue', '', '2026-03-05 17:03:05', '2026-03-05 17:03:05'),
(4, 'Renante', NULL, '0445115', 'Oppo A3s', 'not charging, no sound, no mic', 'Mainboard Issue', 'Charging Port (the plug-in hole), Charging Cable Connector, Battery, Power Button Ribbon, Speaker, Audio Chip, Speaker Ribbon Cable, Microphone, Audio Chip, Microphone Ribbon Cable', '2026-03-05 17:10:31', '2026-03-05 17:10:31'),
(5, 'John Paul Guillermo', NULL, '1223456', 'iphone 14', 'charging ports and lcd', 'Charging Port Issue', 'Charging Port (the plug-in hole), Charging Cable Connector, Battery, Power Button Ribbon, LCD Screen with Touch Layer, Touch Glass (Digitizer), Screen Ribbon Cable', '2026-03-06 04:34:50', '2026-03-10 14:50:18'),
(6, 'Jamaicah Lanuza', NULL, '01231231', 'Infinix HOT10s', 'ghost touch, screen flickers, no sound', 'Touch Controller Issue', 'LCD Screen with Touch Layer, Touch Glass (Digitizer), Screen Ribbon Cable, Speaker, Audio Chip, Speaker Ribbon Cable, LCD Screen / Display, Screen Backlight, Screen Ribbon Cable', '2026-03-10 15:00:28', '2026-03-10 15:00:28');

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
(1, 0, 'Iphone', '7s', NULL, 'Touch Controller Issue', 'Unassigned', 'Pending', 'Medium', '2026-03-02 17:00:47', NULL, '2026-03-02 17:00:47', '2026-03-02 17:00:47'),
(2, 2, 'Xiomi', '23', NULL, 'Microphone Issue', 'Rod Baclig', 'In Progress', 'Medium', '2026-03-02 17:06:01', NULL, '2026-03-02 17:06:01', '2026-03-10 15:01:27'),
(3, 3, 'Infinix', 'HOT10s', NULL, 'ghost touch, no mic, overheating', 'Rod Baclig', 'Pending', 'Medium', '2026-03-05 17:03:05', NULL, '2026-03-05 17:03:05', '2026-03-05 17:03:05'),
(4, 4, 'Oppo', 'A3s', NULL, 'not charging, no sound, no mic', 'Rod Baclig', 'Pending', 'Medium', '2026-03-05 17:10:31', NULL, '2026-03-05 17:10:31', '2026-03-06 03:57:09'),
(5, 5, 'iphone', '14', NULL, 'not charging, ghost touching', 'Rod Baclig', 'Pending', 'Medium', '2026-03-06 04:34:50', NULL, '2026-03-06 04:34:50', '2026-03-06 04:46:55'),
(6, 6, 'Infinix', 'HOT10s', NULL, 'ghost touch, screen flickers, no sound', 'Rod Baclig', 'Completed', 'Medium', '2026-03-10 15:00:28', '2026-03-10 00:00:00', '2026-03-10 15:00:28', '2026-03-10 15:02:01');

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
  `quantity` int(11) DEFAULT 1,
  `status` enum('Pending','Ordered','Received') DEFAULT 'Pending',
  `created` datetime DEFAULT current_timestamp(),
  `modified` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'LCD', 'Screen Part', 123, 123, 123.00, '2026-03-02 17:04:17', '2026-03-02 17:04:17'),
(2, 'charging cable', 'charging parts', 123, 123, 12.00, '2026-03-10 14:47:19', '2026-03-10 14:47:19');

-- --------------------------------------------------------

--
-- Table structure for table `repair_parts`
--

CREATE TABLE `repair_parts` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `part_id` int(11) NOT NULL,
  `quantity_used` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'admin', '$2y$10$VOivfStmblic33R4jjXQ/OOFrYCPATDgMwKMjnlBAnZswVULbLDEq', 'John Doe', 'john.doe@example.com', 'Laptop & Mobile Repairs', 'JD', 'technician', '2026-02-28 00:54:57', '2026-02-28 00:54:57'),
(2, 'janesmith', '$2y$10$examplehashforjanesmith0000000000000000000000000000000', 'Jane Smith', 'jane.smith@example.com', 'Mobile Screen & Battery', 'JS', 'technician', '2026-02-28 00:54:57', '2026-02-28 00:54:57'),
(3, 'marklee', '$2y$10$examplehashformarklee000000000000000000000000000000000', 'Mark Lee', 'mark.lee@example.com', 'Board-Level Repairs', 'ML', 'technician', '2026-02-28 00:54:57', '2026-02-28 00:54:57');

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
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
