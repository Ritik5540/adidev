-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 06:01 PM
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
-- Database: `20260307_adidev`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `otp_value` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor','author') DEFAULT 'author',
  `remember_token` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `otp_enabled` tinyint(1) DEFAULT 0,
  `profile_image` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NULL DEFAULT NULL,
  `last_logout` varchar(50) DEFAULT NULL,
  `session_duration` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `otp_value`, `name`, `email`, `mobile`, `password`, `role`, `remember_token`, `status`, `otp_enabled`, `profile_image`, `last_login`, `login_attempts`, `last_attempt`, `last_logout`, `session_duration`, `created_at`, `updated_at`) VALUES
(1, 818678, 'Admin User', 'kfs211124@gmail.com', '9382347744', '$2y$10$.IjVFAVrDJ//x3wc/ae0q.HU5pqgGnMO4pjT.5H..YVgdaNIDaPYy', 'admin', NULL, 1, 1, '1770404030_hbmns.png', '2026-03-21 15:42:36', 0, '2026-03-09 18:54:15', '2026-03-10 02:44:56', '982', '2026-02-06 18:57:53', '2026-03-21 15:42:36');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) NOT NULL,
  `action` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bulk_inquiries`
--

CREATE TABLE `bulk_inquiries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `inquiry_number` varchar(50) NOT NULL,
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`products`)),
  `business_name` varchar(255) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `expected_monthly_volume` int(11) DEFAULT NULL,
  `target_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','contacted','quoted','converted','closed') DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `last_contacted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bulk_pricing_tiers`
--

CREATE TABLE `bulk_pricing_tiers` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `sub_category_id` int(11) DEFAULT NULL,
  `tier_name` varchar(100) DEFAULT NULL,
  `tier_level` int(11) DEFAULT 1,
  `min_quantity` int(11) NOT NULL,
  `max_quantity` int(11) DEFAULT NULL,
  `quantity_range` varchar(50) GENERATED ALWAYS AS (concat(`min_quantity`,'-',ifnull(`max_quantity`,'+'))) STORED,
  `price_per_piece` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `savings_text` varchar(255) DEFAULT NULL,
  `requires_min_order_value` tinyint(1) DEFAULT 0,
  `min_order_value` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `applies_to` enum('all','registered_only','wholesale_only') DEFAULT 'all',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `user_group_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Specific customer groups this tier applies to' CHECK (json_valid(`user_group_ids`)),
  `priority` int(11) DEFAULT 0
) ;

--
-- Dumping data for table `bulk_pricing_tiers`
--

INSERT INTO `bulk_pricing_tiers` (`id`, `product_id`, `variant_id`, `sub_category_id`, `tier_name`, `tier_level`, `min_quantity`, `max_quantity`, `price_per_piece`, `discount_percentage`, `discount_amount`, `savings_text`, `requires_min_order_value`, `min_order_value`, `is_active`, `is_featured`, `applies_to`, `created_at`, `updated_at`, `valid_from`, `valid_until`, `user_group_ids`, `priority`) VALUES
(1, 1, NULL, NULL, 'Small Pack', 1, 10, 49, 1500.00, 25.00, NULL, 'Save ₹500 per piece', 0, NULL, 1, 0, 'all', '2026-03-08 14:34:49', '2026-03-08 14:34:49', NULL, NULL, NULL, 0),
(2, 1, NULL, NULL, 'Medium Pack', 2, 50, 199, 1400.00, 30.00, NULL, 'Save ₹600 per piece', 0, NULL, 1, 0, 'wholesale_only', '2026-03-08 14:34:49', '2026-03-08 14:34:49', NULL, NULL, NULL, 0),
(3, 1, NULL, NULL, 'Large Pack', 3, 200, 499, 1300.00, 35.00, NULL, 'Save ₹700 per piece', 0, NULL, 1, 0, 'wholesale_only', '2026-03-08 14:34:49', '2026-03-08 14:34:49', NULL, NULL, NULL, 0),
(4, 1, NULL, NULL, 'Bulk Pack', 4, 500, NULL, 1200.00, 40.00, NULL, 'Save ₹800 per piece', 0, NULL, 1, 0, 'wholesale_only', '2026-03-08 14:34:49', '2026-03-08 14:34:49', NULL, NULL, NULL, 0),
(5, 2, NULL, NULL, 'Small Pack', 1, 10, 49, 1800.00, 25.00, NULL, 'Save ₹600 per piece', 0, NULL, 1, 0, 'all', '2026-03-08 14:34:50', '2026-03-08 14:34:50', NULL, NULL, NULL, 0),
(6, 2, NULL, NULL, 'Medium Pack', 2, 50, 199, 1700.00, 29.00, NULL, 'Save ₹700 per piece', 0, NULL, 1, 0, 'wholesale_only', '2026-03-08 14:34:50', '2026-03-08 14:34:50', NULL, NULL, NULL, 0),
(7, 2, NULL, NULL, 'Large Pack', 3, 200, 499, 1600.00, 33.00, NULL, 'Save ₹800 per piece', 0, NULL, 1, 0, 'wholesale_only', '2026-03-08 14:34:50', '2026-03-08 14:34:50', NULL, NULL, NULL, 0),
(8, 2, NULL, NULL, 'Bulk Pack', 4, 500, NULL, 1500.00, 38.00, NULL, 'Save ₹900 per piece', 0, NULL, 1, 0, 'wholesale_only', '2026-03-08 14:34:50', '2026-03-08 14:34:50', NULL, NULL, NULL, 0),
(9, 21, NULL, NULL, 'Small Pack', 1, 10, 49, 3000.00, 25.00, NULL, 'Save ₹1000 per piece', 0, NULL, 1, 0, 'all', '2026-03-08 14:34:50', '2026-03-08 14:34:50', NULL, NULL, NULL, 0),
(10, 21, NULL, NULL, 'Medium Pack', 2, 50, 199, 2800.00, 30.00, NULL, 'Save ₹1200 per piece', 0, NULL, 1, 0, 'wholesale_only', '2026-03-08 14:34:50', '2026-03-08 14:34:50', NULL, NULL, NULL, 0),
(11, 21, NULL, NULL, 'Large Pack', 3, 200, 499, 2600.00, 35.00, NULL, 'Save ₹1400 per piece', 0, NULL, 1, 0, 'wholesale_only', '2026-03-08 14:34:50', '2026-03-08 14:34:50', NULL, NULL, NULL, 0),
(12, 21, NULL, NULL, 'Bulk Pack', 4, 500, NULL, 2400.00, 40.00, NULL, 'Save ₹1600 per piece', 0, NULL, 1, 0, 'wholesale_only', '2026-03-08 14:34:50', '2026-03-08 14:34:50', NULL, NULL, NULL, 0),
(19, 156, NULL, NULL, 'Standard Bulk', 1, 10, 50, 80.00, 5.00, 5.00, 'Save ₹5 per piece on 10+ pieces', 0, NULL, 1, 0, 'all', '2026-03-20 16:44:56', '2026-03-20 16:44:56', NULL, NULL, NULL, 1),
(20, 156, NULL, NULL, 'Wholesale', 2, 51, 200, 75.00, 10.00, 10.00, 'Save ₹10 per piece on 51+ pieces', 0, NULL, 1, 1, 'all', '2026-03-20 16:44:56', '2026-03-20 16:44:56', NULL, NULL, NULL, 2),
(21, 156, NULL, NULL, 'Super Bulk', 3, 201, NULL, 72.00, 15.00, 13.00, 'Save ₹13 per piece on 201+ pieces', 0, NULL, 1, 0, 'all', '2026-03-20 16:44:56', '2026-03-20 16:44:56', NULL, NULL, NULL, 3),
(22, 157, NULL, NULL, 'Standard Bulk', 1, 10, 50, 270.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:47:22', '2026-03-20 16:47:22', NULL, NULL, NULL, 1),
(23, 157, NULL, NULL, 'Wholesale', 2, 51, 100, 255.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:47:22', '2026-03-20 16:47:22', NULL, NULL, NULL, 2),
(24, 157, NULL, NULL, 'Super Bulk', 3, 101, NULL, 240.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:47:22', '2026-03-20 16:47:22', NULL, NULL, NULL, 3),
(31, 158, NULL, NULL, 'Standard Bulk', 1, 10, 50, 80.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(32, 158, NULL, NULL, 'Wholesale', 2, 51, 200, 75.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(33, 158, NULL, NULL, 'Super Bulk', 3, 201, NULL, 72.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(34, 159, NULL, NULL, 'Standard Bulk', 1, 10, 50, 270.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(35, 159, NULL, NULL, 'Wholesale', 2, 51, 100, 255.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(36, 159, NULL, NULL, 'Super Bulk', 3, 101, NULL, 240.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(37, 160, NULL, NULL, 'Standard Bulk', 1, 10, 50, 141.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(38, 160, NULL, NULL, 'Wholesale', 2, 51, 100, 135.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(39, 160, NULL, NULL, 'Super Bulk', 3, 101, NULL, 126.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(40, 161, NULL, NULL, 'Standard Bulk', 1, 10, 50, 379.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(41, 161, NULL, NULL, 'Wholesale', 2, 51, 100, 360.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(42, 161, NULL, NULL, 'Super Bulk', 3, 101, NULL, 340.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(43, 162, NULL, NULL, 'Standard Bulk', 1, 10, 50, 616.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(44, 162, NULL, NULL, 'Wholesale', 2, 51, 80, 585.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(45, 162, NULL, NULL, 'Super Bulk', 3, 81, NULL, 551.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(46, 163, NULL, NULL, 'Standard Bulk', 1, 10, 50, 1139.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(47, 163, NULL, NULL, 'Wholesale', 2, 51, 70, 1080.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(48, 163, NULL, NULL, 'Super Bulk', 3, 71, NULL, 1019.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(49, 164, NULL, NULL, 'Standard Bulk', 1, 10, 50, 664.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(50, 164, NULL, NULL, 'Wholesale', 2, 51, 100, 629.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(51, 164, NULL, NULL, 'Super Bulk', 3, 101, NULL, 594.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(52, 165, NULL, NULL, 'Standard Bulk', 1, 10, 50, 1899.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(53, 165, NULL, NULL, 'Wholesale', 2, 51, 80, 1799.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(54, 165, NULL, NULL, 'Super Bulk', 3, 81, NULL, 1699.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(55, 166, NULL, NULL, 'Standard Bulk', 1, 10, 50, 3134.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(56, 166, NULL, NULL, 'Wholesale', 2, 51, 70, 2969.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(57, 166, NULL, NULL, 'Super Bulk', 3, 71, NULL, 2803.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(58, 167, NULL, NULL, 'Standard Bulk', 1, 10, 50, 6174.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(59, 167, NULL, NULL, 'Wholesale', 2, 51, 60, 5849.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(60, 167, NULL, NULL, 'Super Bulk', 3, 61, NULL, 5524.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(61, 168, NULL, NULL, 'Standard Bulk', 1, 10, 50, 141.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(62, 168, NULL, NULL, 'Wholesale', 2, 51, 100, 134.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(63, 168, NULL, NULL, 'Super Bulk', 3, 101, NULL, 126.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(64, 169, NULL, NULL, 'Standard Bulk', 1, 10, 50, 265.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(65, 169, NULL, NULL, 'Wholesale', 2, 51, 100, 251.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(66, 169, NULL, NULL, 'Super Bulk', 3, 101, NULL, 237.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(67, 170, NULL, NULL, 'Standard Bulk', 1, 10, 50, 502.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(68, 170, NULL, NULL, 'Wholesale', 2, 51, 80, 476.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(69, 170, NULL, NULL, 'Super Bulk', 3, 81, NULL, 449.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(70, 171, NULL, NULL, 'Standard Bulk', 1, 10, 50, 141.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(71, 171, NULL, NULL, 'Wholesale', 2, 51, 100, 134.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(72, 171, NULL, NULL, 'Super Bulk', 3, 101, NULL, 126.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(73, 172, NULL, NULL, 'Standard Bulk', 1, 10, 50, 265.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(74, 172, NULL, NULL, 'Wholesale', 2, 51, 100, 251.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(75, 172, NULL, NULL, 'Super Bulk', 3, 101, NULL, 237.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(76, 173, NULL, NULL, 'Standard Bulk', 1, 10, 50, 502.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(77, 173, NULL, NULL, 'Wholesale', 2, 51, 80, 476.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(78, 173, NULL, NULL, 'Super Bulk', 3, 81, NULL, 449.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(79, 174, NULL, NULL, 'Standard Bulk', 1, 10, 50, 1234.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(80, 174, NULL, NULL, 'Wholesale', 2, 51, 70, 1169.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(81, 174, NULL, NULL, 'Super Bulk', 3, 71, NULL, 1104.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(82, 175, NULL, NULL, 'Standard Bulk', 1, 10, 50, 2374.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(83, 175, NULL, NULL, 'Wholesale', 2, 51, 60, 2249.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(84, 175, NULL, NULL, 'Super Bulk', 3, 61, NULL, 2124.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(85, 176, NULL, NULL, 'Standard Bulk', 1, 10, 50, 94.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(86, 176, NULL, NULL, 'Wholesale', 2, 51, 100, 89.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(87, 176, NULL, NULL, 'Super Bulk', 3, 101, NULL, 84.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(88, 177, NULL, NULL, 'Standard Bulk', 1, 10, 50, 379.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(89, 177, NULL, NULL, 'Wholesale', 2, 51, 80, 359.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(90, 177, NULL, NULL, 'Super Bulk', 3, 81, NULL, 339.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(91, 178, NULL, NULL, 'Standard Bulk', 1, 10, 50, 711.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(92, 178, NULL, NULL, 'Wholesale', 2, 51, 70, 674.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(93, 178, NULL, NULL, 'Super Bulk', 3, 71, NULL, 636.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(94, 179, NULL, NULL, 'Standard Bulk', 1, 10, 50, 94.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(95, 179, NULL, NULL, 'Wholesale', 2, 51, 100, 89.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(96, 179, NULL, NULL, 'Super Bulk', 3, 101, NULL, 84.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(97, 180, NULL, NULL, 'Standard Bulk', 1, 10, 50, 94.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(98, 180, NULL, NULL, 'Wholesale', 2, 51, 100, 89.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(99, 180, NULL, NULL, 'Super Bulk', 3, 101, NULL, 84.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(100, 181, NULL, NULL, 'Standard Bulk', 1, 10, 50, 94.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(101, 181, NULL, NULL, 'Wholesale', 2, 51, 100, 89.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(102, 181, NULL, NULL, 'Super Bulk', 3, 101, NULL, 84.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(103, 182, NULL, NULL, 'Standard Bulk', 1, 10, 50, 379.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(104, 182, NULL, NULL, 'Wholesale', 2, 51, 100, 359.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(105, 182, NULL, NULL, 'Super Bulk', 3, 101, NULL, 339.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(106, 183, NULL, NULL, 'Standard Bulk', 1, 10, 50, 379.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(107, 183, NULL, NULL, 'Wholesale', 2, 51, 100, 359.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(108, 183, NULL, NULL, 'Super Bulk', 3, 101, NULL, 339.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(109, 184, NULL, NULL, 'Standard Bulk', 1, 10, 50, 379.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(110, 184, NULL, NULL, 'Wholesale', 2, 51, 100, 359.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(111, 184, NULL, NULL, 'Super Bulk', 3, 101, NULL, 339.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(112, 185, NULL, NULL, 'Standard Bulk', 1, 10, 100, 46.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(113, 185, NULL, NULL, 'Wholesale', 2, 101, 500, 44.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(114, 185, NULL, NULL, 'Super Bulk', 3, 501, NULL, 41.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(115, 186, NULL, NULL, 'Standard Bulk', 1, 10, 100, 189.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(116, 186, NULL, NULL, 'Wholesale', 2, 101, 300, 179.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(117, 186, NULL, NULL, 'Super Bulk', 3, 301, NULL, 169.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(118, 187, NULL, NULL, 'Standard Bulk', 1, 10, 100, 360.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(119, 187, NULL, NULL, 'Wholesale', 2, 101, 200, 341.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(120, 187, NULL, NULL, 'Super Bulk', 3, 201, NULL, 322.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(121, 188, NULL, NULL, 'Standard Bulk', 1, 10, 100, 56.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(122, 188, NULL, NULL, 'Wholesale', 2, 101, 500, 53.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(123, 188, NULL, NULL, 'Super Bulk', 3, 501, NULL, 50.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(124, 189, NULL, NULL, 'Standard Bulk', 1, 10, 100, 236.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(125, 189, NULL, NULL, 'Wholesale', 2, 101, 300, 224.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(126, 189, NULL, NULL, 'Super Bulk', 3, 301, NULL, 211.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(127, 190, NULL, NULL, 'Standard Bulk', 1, 10, 100, 46.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(128, 190, NULL, NULL, 'Wholesale', 2, 101, 500, 44.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(129, 190, NULL, NULL, 'Super Bulk', 3, 501, NULL, 41.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(130, 191, NULL, NULL, 'Standard Bulk', 1, 10, 100, 56.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(131, 191, NULL, NULL, 'Wholesale', 2, 101, 500, 53.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(132, 191, NULL, NULL, 'Super Bulk', 3, 501, NULL, 50.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(133, 192, NULL, NULL, 'Standard Bulk', 1, 10, 100, 75.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 1),
(134, 192, NULL, NULL, 'Wholesale', 2, 101, 500, 71.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 2),
(135, 192, NULL, NULL, 'Super Bulk', 3, 501, NULL, 67.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:53', '2026-03-20 16:54:53', NULL, NULL, NULL, 3),
(136, 193, NULL, NULL, 'Standard Bulk', 1, 10, 50, 122.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 1),
(137, 193, NULL, NULL, 'Wholesale', 2, 51, 100, 116.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 2),
(138, 193, NULL, NULL, 'Super Bulk', 3, 101, NULL, 109.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 3),
(139, 194, NULL, NULL, 'Standard Bulk', 1, 10, 50, 521.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 1),
(140, 194, NULL, NULL, 'Wholesale', 2, 51, 80, 494.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 2),
(141, 194, NULL, NULL, 'Super Bulk', 3, 81, NULL, 466.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 3),
(142, 195, NULL, NULL, 'Standard Bulk', 1, 10, 50, 949.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 1),
(143, 195, NULL, NULL, 'Wholesale', 2, 51, 70, 899.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 2),
(144, 195, NULL, NULL, 'Super Bulk', 3, 71, NULL, 849.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 3),
(145, 196, NULL, NULL, 'Standard Bulk', 1, 10, 50, 94.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 1),
(146, 196, NULL, NULL, 'Wholesale', 2, 51, 100, 89.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 2),
(147, 196, NULL, NULL, 'Super Bulk', 3, 101, NULL, 84.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 3),
(148, 197, NULL, NULL, 'Standard Bulk', 1, 10, 50, 379.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 1),
(149, 197, NULL, NULL, 'Wholesale', 2, 51, 80, 359.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 2),
(150, 197, NULL, NULL, 'Super Bulk', 3, 81, NULL, 339.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 3),
(151, 198, NULL, NULL, 'Standard Bulk', 1, 10, 50, 711.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 1),
(152, 198, NULL, NULL, 'Wholesale', 2, 51, 70, 674.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 2),
(153, 198, NULL, NULL, 'Super Bulk', 3, 71, NULL, 636.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 3),
(154, 199, NULL, NULL, 'Standard Bulk', 1, 10, 100, 84.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 1),
(155, 199, NULL, NULL, 'Wholesale', 2, 101, 500, 80.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 2),
(156, 199, NULL, NULL, 'Super Bulk', 3, 501, NULL, 76.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 3),
(157, 200, NULL, NULL, 'Standard Bulk', 1, 10, 100, 84.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 1),
(158, 200, NULL, NULL, 'Wholesale', 2, 101, 500, 80.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 2),
(159, 200, NULL, NULL, 'Super Bulk', 3, 501, NULL, 76.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 3),
(160, 201, NULL, NULL, 'Standard Bulk', 1, 10, 100, 84.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 1),
(161, 201, NULL, NULL, 'Wholesale', 2, 101, 500, 80.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 2),
(162, 201, NULL, NULL, 'Super Bulk', 3, 501, NULL, 76.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:54:54', '2026-03-20 16:54:54', NULL, NULL, NULL, 3),
(163, 202, NULL, NULL, 'Standard Bulk', 1, 10, 100, 179.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:51', '2026-03-20 16:57:51', NULL, NULL, NULL, 1),
(164, 202, NULL, NULL, 'Wholesale', 2, 101, 300, 170.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:57:51', '2026-03-20 16:57:51', NULL, NULL, NULL, 2),
(165, 202, NULL, NULL, 'Super Bulk', 3, 301, NULL, 161.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:51', '2026-03-20 16:57:51', NULL, NULL, NULL, 3),
(166, 203, NULL, NULL, 'Standard Bulk', 1, 10, 100, 179.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 1),
(167, 203, NULL, NULL, 'Wholesale', 2, 101, 300, 170.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 2),
(168, 203, NULL, NULL, 'Super Bulk', 3, 301, NULL, 161.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 3),
(169, 204, NULL, NULL, 'Standard Bulk', 1, 10, 100, 94.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 1),
(170, 204, NULL, NULL, 'Wholesale', 2, 101, 500, 89.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 2),
(171, 204, NULL, NULL, 'Super Bulk', 3, 501, NULL, 84.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 3),
(172, 205, NULL, NULL, 'Standard Bulk', 1, 10, 100, 103.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 1),
(173, 205, NULL, NULL, 'Wholesale', 2, 101, 500, 98.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 2),
(174, 205, NULL, NULL, 'Super Bulk', 3, 501, NULL, 92.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 3),
(175, 206, NULL, NULL, 'Standard Bulk', 1, 10, 100, 103.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 1),
(176, 206, NULL, NULL, 'Wholesale', 2, 101, 500, 98.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 2),
(177, 206, NULL, NULL, 'Super Bulk', 3, 501, NULL, 92.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 3),
(178, 207, NULL, NULL, 'Standard Bulk', 1, 10, 100, 37.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 1),
(179, 207, NULL, NULL, 'Wholesale', 2, 101, 500, 35.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 2),
(180, 207, NULL, NULL, 'Super Bulk', 3, 501, NULL, 33.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 3),
(181, 208, NULL, NULL, 'Standard Bulk', 1, 10, 100, 65.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 1),
(182, 208, NULL, NULL, 'Wholesale', 2, 101, 500, 62.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 2),
(183, 208, NULL, NULL, 'Super Bulk', 3, 501, NULL, 58.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 3),
(184, 209, NULL, NULL, 'Standard Bulk', 1, 10, 100, 284.00, 5.00, NULL, 'Save 5% on bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 1),
(185, 209, NULL, NULL, 'Wholesale', 2, 101, 300, 269.00, 10.00, NULL, 'Save 10% on wholesale orders', 0, NULL, 1, 1, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 2),
(186, 209, NULL, NULL, 'Super Bulk', 3, 301, NULL, 254.00, 15.00, NULL, 'Save 15% on super bulk orders', 0, NULL, 1, 0, 'all', '2026-03-20 16:57:52', '2026-03-20 16:57:52', NULL, NULL, NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `bulk_quotations`
--

CREATE TABLE `bulk_quotations` (
  `id` int(11) NOT NULL,
  `inquiry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `valid_until` date NOT NULL,
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`products`)),
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `payment_terms` varchar(500) DEFAULT NULL,
  `delivery_terms` varchar(500) DEFAULT NULL,
  `special_conditions` text DEFAULT NULL,
  `status` enum('draft','sent','viewed','accepted','rejected','expired') DEFAULT 'draft',
  `accepted_at` timestamp NULL DEFAULT NULL,
  `converted_to_order_id` int(11) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `status` enum('active','abandoned','converted','merged') DEFAULT 'active',
  `total_items` int(11) DEFAULT 0,
  `total_quantity` int(11) DEFAULT 0,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `coupon_discount` decimal(10,2) DEFAULT 0.00,
  `is_bulk_order` tinyint(1) DEFAULT 0,
  `bulk_discount_applied` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expired_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `session_id`, `status`, `total_items`, `total_quantity`, `subtotal`, `discount_amount`, `shipping_amount`, `tax_amount`, `grand_total`, `coupon_code`, `coupon_discount`, `is_bulk_order`, `bulk_discount_applied`, `created_at`, `updated_at`, `expired_at`) VALUES
(1, 2, NULL, 'active', 3, 5, 4244.00, 200.00, 100.00, 424.00, 4568.00, NULL, 0.00, 0, 0.00, '2026-03-06 08:47:50', '2026-03-08 08:47:50', '2026-03-13 08:47:50'),
(2, 3, NULL, 'active', 2, 75, 33675.00, 5051.25, 500.00, 3367.50, 32491.25, NULL, 0.00, 1, 0.00, '2026-03-07 08:47:50', '2026-03-08 08:47:50', '2026-03-14 08:47:50'),
(3, 4, NULL, 'active', 1, 2, 1798.00, 0.00, 50.00, 179.80, 2027.80, NULL, 0.00, 0, 0.00, '2026-03-07 20:47:50', '2026-03-08 08:47:50', '2026-03-15 08:47:50'),
(4, 2, NULL, 'abandoned', 2, 3, 2697.00, 0.00, 80.00, 269.70, 3046.70, NULL, 0.00, 0, 0.00, '2026-02-26 08:47:50', '2026-02-27 08:47:50', '2026-03-05 08:47:50'),
(5, 1, 'jhgnjht6b763udkfiro2511j7e', 'active', 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, 0.00, 0, 0.00, '2026-03-20 19:02:01', '2026-03-20 19:02:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `is_bulk_pricing_applied` tinyint(1) DEFAULT 0,
  `bulk_tier_id` int(11) DEFAULT NULL,
  `bulk_min_quantity` int(11) DEFAULT NULL,
  `regular_price` decimal(10,2) DEFAULT NULL,
  `savings_amount` decimal(10,2) DEFAULT NULL,
  `savings_percentage` decimal(5,2) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `product_size` varchar(100) DEFAULT NULL,
  `product_image` varchar(500) DEFAULT NULL,
  `custom_message` text DEFAULT NULL,
  `customization_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customization_details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `unit_price`, `total_price`, `is_bulk_pricing_applied`, `bulk_tier_id`, `bulk_min_quantity`, `regular_price`, `savings_amount`, `savings_percentage`, `product_name`, `product_sku`, `product_size`, `product_image`, `custom_message`, `customization_details`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 899.00, 1798.00, 0, NULL, NULL, NULL, NULL, NULL, 'Handcrafted Wooden Ganesha Idol', 'WDN-CRF-001', NULL, 'wooden-ganesha-1.jpg', NULL, NULL, '2026-03-08 08:47:51', '2026-03-08 08:47:51'),
(2, 1, 4, 2, 599.00, 1198.00, 0, NULL, NULL, NULL, NULL, NULL, 'Brass Diya Set (5 pcs)', 'MTL-BRS-001', NULL, 'brass-diya-1.jpg', NULL, NULL, '2026-03-08 08:47:51', '2026-03-08 08:47:51'),
(3, 1, 7, 1, 2499.00, 2499.00, 0, NULL, NULL, NULL, NULL, NULL, 'Blue Pottery Dinner Set (6 pcs)', 'CER-POT-001', NULL, 'blue-pottery-1.jpg', NULL, NULL, '2026-03-08 08:47:51', '2026-03-08 08:47:51'),
(4, 2, 10, 50, 449.00, 22450.00, 1, NULL, NULL, NULL, NULL, NULL, 'Pure Cotton Kalamkari Fabric', 'TEX-COT-001', 'Standard', 'kalamkari-1.jpg', NULL, NULL, '2026-03-08 08:47:51', '2026-03-08 08:47:51'),
(5, 2, 16, 25, 849.00, 21225.00, 1, 20, NULL, NULL, NULL, NULL, 'Premium Cashews (1kg)', 'FMCG-FOD-002', '1kg', 'cashew-1.jpg', NULL, NULL, '2026-03-08 08:47:51', '2026-03-08 08:47:51'),
(6, 3, 5, 2, 899.00, 1798.00, 0, NULL, NULL, NULL, NULL, NULL, 'Copper Water Bottle', 'MTL-BRS-002', '1000ml', 'copper-bottle-1.jpg', NULL, NULL, '2026-03-08 08:47:51', '2026-03-08 08:47:51'),
(7, 4, 11, 2, 1299.00, 2598.00, 0, NULL, NULL, NULL, NULL, NULL, 'Handloom Cotton Saree', 'TEX-COT-002', 'Free Size', 'cotton-saree-1.jpg', NULL, NULL, '2026-02-26 08:47:51', '2026-02-26 08:47:51'),
(8, 4, 2, 1, 1499.00, 1499.00, 0, NULL, NULL, NULL, NULL, NULL, 'Rosewood Jewelry Box', 'WDN-CRF-002', 'Medium', 'jewelry-box-1.jpg', NULL, NULL, '2026-02-26 08:47:51', '2026-02-26 08:47:51');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `applies_to` enum('all','category','sub_category','product') DEFAULT 'all',
  `applies_to_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applies_to_ids`)),
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `min_quantity` int(11) DEFAULT 1,
  `is_for_bulk_orders_only` tinyint(1) DEFAULT 0,
  `per_user_limit` int(11) DEFAULT 1,
  `total_usage_limit` int(11) DEFAULT NULL,
  `current_usage` int(11) DEFAULT 0,
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `coupon_code`, `description`, `discount_type`, `discount_value`, `max_discount_amount`, `applies_to`, `applies_to_ids`, `min_order_amount`, `min_quantity`, `is_for_bulk_orders_only`, `per_user_limit`, `total_usage_limit`, `current_usage`, `valid_from`, `valid_to`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'Welcome discount for new users', 'percentage', 10.00, 500.00, 'all', NULL, 500.00, 1, 0, 1, 1000, 0, '2026-03-08 13:59:15', '2026-04-07 13:59:15', 1, '2026-03-08 08:29:15', '2026-03-08 08:29:15'),
(2, 'BULKSAVE15', 'Bulk order discount', 'percentage', 15.00, 5000.00, 'all', NULL, 5000.00, 50, 1, 1, 500, 0, '2026-03-08 13:59:15', '2026-06-06 13:59:15', 1, '2026-03-08 08:29:15', '2026-03-08 08:29:15'),
(3, 'FESTIVE200', 'Festive season offer', 'fixed', 200.00, 200.00, 'all', NULL, 1000.00, 1, 0, 1, 500, 0, '2026-03-08 13:59:15', '2026-03-23 13:59:15', 1, '2026-03-08 08:29:15', '2026-03-08 08:29:15'),
(4, 'FIRSTORDER', 'First order discount', 'percentage', 5.00, 250.00, 'all', NULL, 250.00, 1, 0, 1, 2000, 0, '2026-03-08 13:59:15', '2026-05-07 13:59:15', 1, '2026-03-08 08:29:15', '2026-03-08 08:29:15');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupon_usage`
--

INSERT INTO `coupon_usage` (`id`, `coupon_id`, `user_id`, `cart_id`, `used_at`) VALUES
(1, 1, 4, 3, '2026-03-05 14:22:31'),
(2, 3, 2, 1, '2026-03-06 14:22:31'),
(3, 1, 2, 4, '2026-02-27 14:22:31'),
(4, 4, 3, 2, '2026-03-07 14:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `fraud_analysis_logs`
--

CREATE TABLE `fraud_analysis_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `risk_score` int(11) DEFAULT NULL,
  `risk_level` varchar(50) DEFAULT NULL,
  `flags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`flags`)),
  `recommended_action` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `action_taken` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `invoice_type` enum('original','proforma','credit_note','debit_note') DEFAULT 'original',
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_total` decimal(10,2) DEFAULT 0.00,
  `tax_total` decimal(10,2) DEFAULT 0.00,
  `shipping_total` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `balance_due` decimal(10,2) GENERATED ALWAYS AS (`grand_total` - `paid_amount`) STORED,
  `gst_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gst_details`)),
  `place_of_supply` varchar(100) DEFAULT NULL,
  `pdf_url` varchar(500) DEFAULT NULL,
  `is_email_sent` tinyint(1) DEFAULT 0,
  `email_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `order_id`, `user_id`, `invoice_number`, `invoice_type`, `invoice_date`, `due_date`, `subtotal`, `discount_total`, `tax_total`, `shipping_total`, `grand_total`, `paid_amount`, `gst_details`, `place_of_supply`, `pdf_url`, `is_email_sent`, `email_sent_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'INV-2026-001', 'original', '2026-02-16', '2026-03-03', 2997.00, 0.00, 299.70, 80.00, 3376.70, 3376.70, '{\"cgst\": 149.85, \"sgst\": 149.85, \"igst\": 0.00, \"gst_rate\": 10.00}', 'Delhi', '/invoices/INV-2026-001.pdf', 1, '2026-02-16 08:50:35', '2026-02-16 08:50:35', '2026-03-08 08:50:35'),
(2, 2, 3, 'INV-2026-002', 'proforma', '2026-02-26', '2026-03-28', 119800.00, 17970.00, 11980.00, 1000.00, 114810.00, 57405.00, '{\"cgst\": 0.00, \"sgst\": 0.00, \"igst\": 11980.00, \"gst_rate\": 10.00}', 'Maharashtra', '/invoices/INV-2026-002.pdf', 1, '2026-02-27 08:50:35', '2026-02-26 08:50:35', '2026-03-08 08:50:35'),
(3, 3, 4, 'INV-2026-003', 'original', '2026-03-06', '2026-03-23', 2995.00, 299.50, 299.50, 100.00, 3095.00, 3095.00, '{\"cgst\": 149.75, \"sgst\": 149.75, \"igst\": 0.00, \"gst_rate\": 10.00}', 'Gujarat', '/invoices/INV-2026-003.pdf', 0, NULL, '2026-03-06 08:50:35', '2026-03-08 08:50:35');

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('success','failed') DEFAULT 'failed',
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `email`, `action`, `ip_address`, `user_agent`, `status`, `details`, `created_at`) VALUES
(1, 1, 'kfs211124@gmail.com', 'OTP_SENT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'failed', 'OTP sent to email', '2026-03-19 18:06:00'),
(2, 1, 'kfs211124@gmail.com', 'LOGIN_SUCCESS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'User logged in successfully', '2026-03-19 18:07:28'),
(3, 1, 'kfs211124@gmail.com', 'OTP_VERIFIED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'OTP verified successfully', '2026-03-19 18:07:28'),
(4, 1, 'kfs211124@gmail.com', 'OTP_SENT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'failed', 'OTP sent to email', '2026-03-19 18:31:52'),
(5, 1, 'kfs211124@gmail.com', 'LOGIN_SUCCESS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'User logged in successfully', '2026-03-19 18:32:28'),
(6, 1, 'kfs211124@gmail.com', 'OTP_VERIFIED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'OTP verified successfully', '2026-03-19 18:32:28'),
(7, 1, 'kfs211124@gmail.com', 'OTP_SENT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'failed', 'OTP sent to email', '2026-03-20 01:34:49'),
(8, 1, 'kfs211124@gmail.com', 'LOGIN_SUCCESS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'User logged in successfully', '2026-03-20 01:35:44'),
(9, 1, 'kfs211124@gmail.com', 'OTP_VERIFIED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'OTP verified successfully', '2026-03-20 01:35:44'),
(10, 1, 'kfs211124@gmail.com', 'OTP_SENT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'failed', 'OTP sent to email', '2026-03-20 13:51:48'),
(11, 1, 'kfs211124@gmail.com', 'LOGIN_SUCCESS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'User logged in successfully', '2026-03-20 13:52:22'),
(12, 1, 'kfs211124@gmail.com', 'OTP_VERIFIED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'OTP verified successfully', '2026-03-20 13:52:22'),
(13, 1, 'kfs211124@gmail.com', 'OTP_SENT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'failed', 'OTP sent to email', '2026-03-20 14:40:10'),
(14, 1, 'kfs211124@gmail.com', 'LOGIN_SUCCESS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'User logged in successfully', '2026-03-20 14:40:21'),
(15, 1, 'kfs211124@gmail.com', 'OTP_VERIFIED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'OTP verified successfully', '2026-03-20 14:40:21'),
(16, 1, 'kfs211124@gmail.com', 'OTP_SENT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'failed', 'OTP sent to email', '2026-03-20 16:58:05'),
(17, 1, 'kfs211124@gmail.com', 'LOGIN_SUCCESS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'User logged in successfully', '2026-03-20 16:58:16'),
(18, 1, 'kfs211124@gmail.com', 'OTP_VERIFIED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'OTP verified successfully', '2026-03-20 16:58:16'),
(19, 1, 'kfs211124@gmail.com', 'OTP_SENT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'failed', 'OTP sent to email', '2026-03-20 18:03:34'),
(20, 1, 'kfs211124@gmail.com', 'LOGIN_SUCCESS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'User logged in successfully', '2026-03-20 18:03:47'),
(21, 1, 'kfs211124@gmail.com', 'OTP_VERIFIED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'OTP verified successfully', '2026-03-20 18:03:47'),
(22, 1, 'kfs211124@gmail.com', 'OTP_SENT', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'failed', 'OTP sent to email', '2026-03-21 15:42:06'),
(23, 1, 'kfs211124@gmail.com', 'LOGIN_SUCCESS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'User logged in successfully', '2026-03-21 15:42:36'),
(24, 1, 'kfs211124@gmail.com', 'OTP_VERIFIED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', 'OTP verified successfully', '2026-03-21 15:42:36');

-- --------------------------------------------------------

--
-- Table structure for table `main_categories`
--

CREATE TABLE `main_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category_code` enum('HAND','TEX','FMCG') NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `banner_image` varchar(500) DEFAULT NULL,
  `thumbnail_image` varchar(500) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `show_in_menu` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `default_min_bulk_quantity` int(11) DEFAULT 10,
  `default_bulk_pricing_model` enum('fixed','tiered','range') DEFAULT 'fixed',
  `total_sub_categories` int(11) DEFAULT 0,
  `total_products` int(11) DEFAULT 0,
  `is_featured` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `main_categories`
--

INSERT INTO `main_categories` (`id`, `name`, `slug`, `description`, `category_code`, `icon`, `banner_image`, `thumbnail_image`, `meta_title`, `meta_description`, `meta_keywords`, `is_active`, `show_in_menu`, `sort_order`, `default_min_bulk_quantity`, `default_bulk_pricing_model`, `total_sub_categories`, `total_products`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'Handicrafts', 'handicrafts', 'Traditional and modern handicraft items', 'HAND', 'assets/images/handicrafts-icon.png', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 10, 'tiered', 0, 8, 1, '2026-03-08 08:28:12', '2026-03-08 14:15:28'),
(2, 'Textiles', 'textiles', 'Premium quality fabrics and textiles', 'TEX', 'assets/images/textiles-icon.png', NULL, NULL, NULL, NULL, NULL, 1, 1, 2, 20, 'tiered', 0, 5, 1, '2026-03-08 08:28:12', '2026-03-20 02:25:12'),
(3, 'FMCG', 'fmcg', 'Fast-moving consumer goods', 'FMCG', 'assets/images/fmcg-icon.png', NULL, NULL, NULL, NULL, NULL, 1, 1, 3, 50, 'range', 0, 4, 0, '2026-03-08 08:28:12', '2026-03-20 02:02:04');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('order_confirmed','order_shipped','order_delivered','bulk_price_available','price_drop','back_in_stock','wishlist_offer','payment_received','invoice_generated') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `is_archived` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `action_url`, `image_url`, `data`, `is_read`, `is_archived`, `read_at`, `created_at`) VALUES
(1, 2, 'order_confirmed', 'Order #ORD-2026-001 Confirmed', 'Your order has been confirmed and will be processed soon.', '/orders/ORD-2026-001', '/images/order-confirmed.png', '{\"order_id\": 1, \"amount\": 3376.70}', 1, 0, '2026-02-13 08:52:58', '2026-02-12 08:52:58'),
(2, 2, 'order_shipped', 'Order #ORD-2026-001 Shipped', 'Your order has been shipped via Delhivery. Track: DLH12345678', '/orders/ORD-2026-001/track', '/images/shipped.png', '{\"order_id\": 1, \"tracking\": \"DLH12345678\"}', 1, 0, '2026-02-15 08:52:58', '2026-02-14 08:52:58'),
(3, 2, 'order_delivered', 'Order #ORD-2026-001 Delivered', 'Your order has been delivered successfully. Thank you for shopping!', '/orders/ORD-2026-001', '/images/delivered.png', '{\"order_id\": 1, \"delivered_at\": \"2026-02-16 14:22:58\"}', 0, 0, NULL, '2026-02-16 08:52:58'),
(4, 3, 'payment_received', 'Payment Received for Order #ORD-2026-002', 'We have received your advance payment of ₹57,405.00', '/orders/ORD-2026-002', '/images/payment.png', '{\"order_id\": 2, \"amount\": 57405.00}', 1, 0, '2026-03-01 08:52:58', '2026-02-28 08:52:58'),
(5, 3, 'invoice_generated', 'Invoice Generated for Order #ORD-2026-002', 'Your proforma invoice is now available for download.', '/invoices/INV-2026-002.pdf', '/images/invoice.png', '{\"invoice_id\": 2, \"invoice_number\": \"INV-2026-002\"}', 0, 0, NULL, '2026-02-27 08:52:58'),
(6, 3, 'bulk_price_available', 'New Bulk Pricing Available', 'New tiered pricing is now available for Kalamkari fabrics. Save up to 40%!', '/products/10', '/images/kalamkari-1-thumb.jpg', '{\"product_id\": 10, \"max_discount\": 40}', 0, 0, NULL, '2026-03-06 08:52:58'),
(7, 2, 'price_drop', 'Price Drop Alert: Blue Pottery Set', 'The item in your wishlist is now 10% off!', '/products/7', '/images/blue-pottery-1-thumb.jpg', '{\"product_id\": 7, \"old_price\": 2799.00, \"new_price\": 2499.00}', 1, 0, '2026-03-03 08:52:58', '2026-02-21 08:52:58'),
(8, 4, 'back_in_stock', 'Brass Diya Set Back in Stock', 'The Brass Diya Set is now available again.', '/products/4', '/images/brass-diya-1-thumb.jpg', '{\"product_id\": 4, \"stock\": 75}', 0, 0, NULL, '2026-03-07 08:52:58'),
(9, 2, 'wishlist_offer', 'Special Offer on Your Wishlist Item', 'Get 5% extra discount on Jewelry Box - limited time!', '/products/2?offer=wishlist5', '/images/jewelry-box-1-thumb.jpg', '{\"product_id\": 2, \"extra_discount\": 5}', 0, 0, NULL, '2026-03-08 08:52:58');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','payment_received','processing','confirmed','packed','shipped','out_for_delivery','delivered','cancelled','refunded','returned','on_hold') DEFAULT 'pending',
  `order_type` enum('regular','bulk','wholesale','sample') DEFAULT 'regular',
  `is_bulk_order` tinyint(1) DEFAULT 0,
  `customer_name` varchar(200) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_gst` varchar(50) DEFAULT NULL,
  `shipping_address_id` int(11) DEFAULT NULL,
  `billing_address_id` int(11) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `billing_address` text NOT NULL,
  `total_items` int(11) NOT NULL,
  `total_quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `bulk_discount_amount` decimal(10,2) DEFAULT 0.00,
  `coupon_discount_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `amount_due` decimal(10,2) GENERATED ALWAYS AS (`grand_total` - `amount_paid`) STORED,
  `payment_method` enum('cod','bank_transfer','card','upi','wallet','razorpay','paytm') DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `invoice_number` varchar(50) DEFAULT NULL,
  `invoice_generated_at` timestamp NULL DEFAULT NULL,
  `invoice_url` varchar(500) DEFAULT NULL,
  `bulk_order_contract_signed` tinyint(1) DEFAULT 0,
  `bulk_order_contract_url` varchar(500) DEFAULT NULL,
  `expected_dispatch_date` date DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `packed_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `status`, `order_type`, `is_bulk_order`, `customer_name`, `customer_email`, `customer_phone`, `customer_gst`, `shipping_address_id`, `billing_address_id`, `shipping_address`, `billing_address`, `total_items`, `total_quantity`, `subtotal`, `discount_amount`, `bulk_discount_amount`, `coupon_discount_amount`, `shipping_amount`, `tax_amount`, `grand_total`, `amount_paid`, `payment_method`, `payment_status`, `transaction_id`, `payment_details`, `invoice_number`, `invoice_generated_at`, `invoice_url`, `bulk_order_contract_signed`, `bulk_order_contract_url`, `expected_dispatch_date`, `order_date`, `confirmed_at`, `processed_at`, `packed_at`, `shipped_at`, `delivered_at`, `cancelled_at`, `cancelled_reason`, `created_at`, `updated_at`) VALUES
(1, 'ORD-2026-001', 2, 'delivered', 'regular', 0, 'Rajesh Kumar', 'rajesh.k@example.com', '9876543211', NULL, NULL, NULL, 'Shop No. 45, Wholesale Market, Delhi - 110001', 'Same as shipping', 2, 3, 2997.00, 0.00, 0.00, 0.00, 80.00, 299.70, 3376.70, 3376.70, 'cod', 'paid', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-02-11 08:48:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-11 08:48:17', '2026-02-16 08:48:17'),
(2, 'ORD-2026-002', 3, 'shipped', 'bulk', 1, 'Priya Sharma', 'priya.s@example.com', '9876543212', '27AABCT1234E1Z5', NULL, NULL, 'Mall Road, Shop No. 12, Mumbai - 400001', 'Same as shipping', 3, 200, 119800.00, 17970.00, 17970.00, 0.00, 1000.00, 11980.00, 114810.00, 57405.00, 'bank_transfer', '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-02-26 08:48:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-26 08:48:17', '2026-03-03 08:48:17'),
(3, 'ORD-2026-003', 4, 'processing', 'regular', 0, 'Amit Patel', 'amit.p@example.com', '9876543213', NULL, NULL, NULL, 'B-201, Sunrise Apartments, Ahmedabad - 380001', 'Same as shipping', 1, 5, 2995.00, 299.50, 0.00, 299.50, 100.00, 299.50, 3095.00, 3095.00, 'card', 'paid', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-05 08:48:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-05 08:48:17', '2026-03-06 08:48:17'),
(4, 'ORD-2026-004', 2, 'pending', 'regular', 0, 'Rajesh Kumar', 'rajesh.k@example.com', '9876543211', NULL, NULL, NULL, 'Shop No. 45, Wholesale Market, Delhi - 110001', 'Same as shipping', 2, 4, 4796.00, 0.00, 0.00, 0.00, 100.00, 479.60, 5375.60, 0.00, 'cod', 'pending', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-08 08:48:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-08 08:48:17', '2026-03-08 08:48:17'),
(5, 'ORD-2026-005', 3, 'cancelled', 'bulk', 1, 'Priya Sharma', 'priya.s@example.com', '9876543212', '27AABCT1234E1Z5', NULL, NULL, 'Mall Road, Shop No. 12, Mumbai - 400001', 'Same as shipping', 1, 50, 42450.00, 6367.50, 6367.50, 0.00, 500.00, 4245.00, 40827.50, 0.00, 'cod', '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-06 08:48:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-06 08:48:17', '2026-03-07 08:48:17');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_size` varchar(100) DEFAULT NULL,
  `product_image` varchar(500) DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `sub_category_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `is_bulk_item` tinyint(1) DEFAULT 0,
  `bulk_tier_applied` varchar(100) DEFAULT NULL,
  `bulk_min_quantity` int(11) DEFAULT NULL,
  `regular_price` decimal(10,2) DEFAULT NULL,
  `savings_amount` decimal(10,2) DEFAULT NULL,
  `item_discount` decimal(10,2) DEFAULT 0.00,
  `bulk_discount` decimal(10,2) DEFAULT 0.00,
  `coupon_discount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','confirmed','packed','shipped','delivered','cancelled','returned') DEFAULT 'pending',
  `is_return_requested` tinyint(1) DEFAULT 0,
  `return_reason` text DEFAULT NULL,
  `return_approved_at` timestamp NULL DEFAULT NULL,
  `return_completed_at` timestamp NULL DEFAULT NULL,
  `replacement_order_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_code`, `product_name`, `product_description`, `product_size`, `product_image`, `category_name`, `sub_category_name`, `quantity`, `unit_price`, `total_price`, `is_bulk_item`, `bulk_tier_applied`, `bulk_min_quantity`, `regular_price`, `savings_amount`, `item_discount`, `bulk_discount`, `coupon_discount`, `status`, `is_return_requested`, `return_reason`, `return_approved_at`, `return_completed_at`, `replacement_order_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'WDN-CRF-001', 'Handcrafted Wooden Ganesha Idol', NULL, NULL, 'wooden-ganesha-1.jpg', 'Handicrafts', 'Wooden Crafts', 2, 899.00, 1798.00, 0, NULL, NULL, 899.00, 0.00, 0.00, 0.00, 0.00, 'delivered', 0, NULL, NULL, NULL, NULL, '2026-02-11 08:48:18', '2026-02-16 08:48:18'),
(2, 1, 5, 'MTL-BRS-002', 'Copper Water Bottle', NULL, '1000ml', 'copper-bottle-1.jpg', 'Handicrafts', 'Metal Crafts', 1, 899.00, 899.00, 0, NULL, NULL, 899.00, 0.00, 0.00, 0.00, 0.00, 'delivered', 0, NULL, NULL, NULL, NULL, '2026-02-11 08:48:18', '2026-02-16 08:48:18'),
(3, 2, 10, 'TEX-COT-001', 'Pure Cotton Kalamkari Fabric', NULL, 'Standard', 'kalamkari-1.jpg', 'Textiles', 'Cotton Fabrics', 150, 404.00, 60600.00, 1, 'Wholesale Pack (20% off)', NULL, 599.00, 29250.00, 0.00, 0.00, 0.00, 'shipped', 0, NULL, NULL, NULL, NULL, '2026-02-26 08:48:18', '2026-03-03 08:48:18'),
(4, 2, 16, 'FMCG-FOD-002', 'Premium Cashews (1kg)', NULL, '1kg', 'cashew-1.jpg', 'FMCG', 'Packaged Foods', 50, 764.00, 38200.00, 1, 'Store Pack (10% off)', NULL, 849.00, 4250.00, 0.00, 0.00, 0.00, 'shipped', 0, NULL, NULL, NULL, NULL, '2026-02-26 08:48:18', '2026-03-03 08:48:18'),
(5, 3, 4, 'MTL-BRS-001', 'Brass Diya Set (5 pcs)', NULL, NULL, 'brass-diya-1.jpg', 'Handicrafts', 'Metal Crafts', 5, 479.20, 2396.00, 0, NULL, NULL, 599.00, 599.00, 0.00, 0.00, 0.00, '', 0, NULL, NULL, NULL, NULL, '2026-03-05 08:48:18', '2026-03-06 08:48:18'),
(6, 4, 2, 'WDN-CRF-002', 'Rosewood Jewelry Box', NULL, 'Medium', 'jewelry-box-1.jpg', 'Handicrafts', 'Wooden Crafts', 2, 1499.00, 2998.00, 0, NULL, NULL, 1499.00, 0.00, 0.00, 0.00, 0.00, 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-08 08:48:18', '2026-03-08 08:48:18'),
(7, 4, 7, 'CER-POT-001', 'Blue Pottery Dinner Set (6 pcs)', NULL, NULL, 'blue-pottery-1.jpg', 'Handicrafts', 'Ceramic & Pottery', 2, 2249.00, 4498.00, 1, 'Medium Order (10% off)', NULL, 2499.00, 500.00, 0.00, 0.00, 0.00, 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-08 08:48:18', '2026-03-08 08:48:18'),
(8, 5, 11, 'TEX-COT-002', 'Handloom Cotton Saree', NULL, 'Free Size', 'cotton-saree-1.jpg', 'Textiles', 'Cotton Fabrics', 50, 849.00, 42450.00, 1, 'Boutique Pack (30% off)', NULL, 1299.00, 22500.00, 0.00, 0.00, 0.00, 'cancelled', 0, NULL, NULL, NULL, NULL, '2026-03-06 08:48:18', '2026-03-07 08:48:18');

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_by_type` enum('system','admin','customer') DEFAULT 'system',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `old_status`, `new_status`, `changed_by`, `changed_by_type`, `notes`, `created_at`) VALUES
(1, 1, NULL, 'pending', 2, 'customer', 'Order placed', '2026-02-11 08:48:32'),
(2, 1, 'pending', 'confirmed', 1, 'admin', 'Order confirmed', '2026-02-12 08:48:32'),
(3, 1, 'confirmed', 'processed', 1, 'admin', 'Order processed', '2026-02-13 08:48:32'),
(4, 1, 'processed', 'packed', 1, 'admin', 'Items packed', '2026-02-14 08:48:32'),
(5, 1, 'packed', 'shipped', 1, 'admin', 'Shipped via Delhivery - AWB: DLH12345678', '2026-02-14 08:48:32'),
(6, 1, 'shipped', 'delivered', 2, 'customer', 'Delivered and received', '2026-02-16 08:48:32'),
(7, 2, NULL, 'pending', 3, 'customer', 'Bulk order placed', '2026-02-26 08:48:32'),
(8, 2, 'pending', 'confirmed', 1, 'admin', 'Bulk order confirmed', '2026-02-27 08:48:32'),
(9, 2, 'confirmed', 'payment_received', 1, 'system', '50% advance payment received', '2026-02-28 08:48:32'),
(10, 2, 'payment_received', 'processed', 1, 'admin', 'Processing started', '2026-03-01 08:48:32'),
(11, 2, 'processed', 'packed', 1, 'admin', 'Bulk order packed', '2026-03-02 08:48:32'),
(12, 2, 'packed', 'shipped', 1, 'admin', 'Shipped via Blue Dart - AWB: BD987654321', '2026-03-03 08:48:32'),
(13, 3, NULL, 'pending', 4, 'customer', 'Order placed', '2026-03-05 08:48:32'),
(14, 3, 'pending', 'payment_received', 1, 'system', 'Payment successful', '2026-03-05 08:48:32'),
(15, 3, 'payment_received', 'confirmed', 1, 'admin', 'Order confirmed', '2026-03-06 08:48:32'),
(16, 3, 'confirmed', 'processing', 1, 'admin', 'Processing started', '2026-03-06 08:48:32'),
(17, 5, NULL, 'pending', 3, 'customer', 'Bulk order placed', '2026-03-06 08:48:32'),
(18, 5, 'pending', 'cancelled', 3, 'customer', 'Customer cancelled due to pricing issue', '2026-03-07 08:48:32');

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking`
--

CREATE TABLE `order_tracking` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `status` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `courier_name` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `tracking_url` varchar(500) DEFAULT NULL,
  `status_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expected_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_by_type` enum('system','admin','customer','courier') DEFAULT 'system',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_tracking`
--

INSERT INTO `order_tracking` (`id`, `order_id`, `order_item_id`, `status`, `location`, `description`, `courier_name`, `tracking_number`, `tracking_url`, `status_date`, `expected_delivery_date`, `actual_delivery_date`, `updated_by`, `updated_by_type`, `notes`, `created_at`) VALUES
(1, 1, NULL, 'Delivered', 'Delhi', 'Package delivered successfully', 'Delhivery', 'DLH12345678', 'https://track.delhivery.com/DLH12345678', '2026-02-16 08:48:40', '2026-02-16', NULL, NULL, 'courier', NULL, '2026-02-16 08:48:40'),
(2, 2, NULL, 'In Transit', 'Mumbai Warehouse', 'Package arrived at Mumbai sorting center', 'Blue Dart', 'BD987654321', 'https://track.bluedart.com/BD987654321', '2026-03-03 08:48:40', '2026-03-10', NULL, NULL, 'courier', NULL, '2026-03-03 08:48:40'),
(3, 2, NULL, 'Out for Delivery', 'Mumbai - Andheri', 'Out for delivery in Andheri West', 'Blue Dart', 'BD987654321', 'https://track.bluedart.com/BD987654321', '2026-03-07 08:48:40', '2026-03-10', NULL, NULL, 'courier', NULL, '2026-03-07 08:48:40'),
(4, 3, NULL, 'Packed', 'Ahmedabad Warehouse', 'Order packed and ready for pickup', NULL, NULL, NULL, '2026-03-06 08:48:40', '2026-03-11', NULL, NULL, 'admin', NULL, '2026-03-06 08:48:40'),
(5, 2, 3, 'Packed', 'Mumbai Warehouse', '150 units of Kalamkari fabric packed', NULL, NULL, NULL, '2026-03-02 08:48:40', NULL, NULL, NULL, 'admin', NULL, '2026-03-02 08:48:40'),
(6, 2, 4, 'Packed', 'Mumbai Warehouse', '50 units of Cashews packed', NULL, NULL, NULL, '2026-03-02 08:48:40', NULL, NULL, NULL, 'admin', NULL, '2026-03-02 08:48:40');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('pending','success','failed','refunded') DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `refunded_amount` decimal(10,2) DEFAULT 0.00,
  `transaction_fee` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(10,2) GENERATED ALWAYS AS (`amount` - `refunded_amount` - `transaction_fee`) STORED,
  `transaction_id` varchar(255) DEFAULT NULL,
  `gateway_transaction_id` varchar(255) DEFAULT NULL,
  `bank_transaction_id` varchar(255) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `gateway_status_code` varchar(50) DEFAULT NULL,
  `refund_reason` text DEFAULT NULL,
  `refund_initiated_by` int(11) DEFAULT NULL,
  `refund_processed_at` timestamp NULL DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `user_id`, `payment_method`, `payment_status`, `amount`, `refunded_amount`, `transaction_fee`, `transaction_id`, `gateway_transaction_id`, `bank_transaction_id`, `payment_reference`, `gateway_response`, `gateway_status_code`, `refund_reason`, `refund_initiated_by`, `refund_processed_at`, `payment_date`, `created_at`, `updated_at`) VALUES
(5, 1, 2, 'cod', 'success', 3376.70, 0.00, 0.00, '1', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, '2026-02-16 08:50:13', '2026-02-11 08:50:13', '2026-03-08 08:50:13'),
(6, 2, 3, 'bank_transfer', 'success', 57405.00, 0.00, 115.00, '1', '1', NULL, '1', '{\"bank\": \"HDFC\", \"branch\": \"Mumbai Main\", \"reference\": \"NEFT123456\"}', NULL, NULL, NULL, NULL, '2026-02-28 08:50:13', '2026-02-28 08:50:13', '2026-03-08 08:50:13'),
(7, 3, 4, 'card', 'success', 3095.00, 0.00, 45.00, '1', '1', NULL, '1', '{\"gateway\": \"Razorpay\", \"card_type\": \"Visa\", \"last4\": \"4242\"}', NULL, NULL, NULL, NULL, '2026-03-05 08:50:13', '2026-03-05 08:50:13', '2026-03-08 08:50:13'),
(8, 4, 2, 'card', 'failed', 5375.60, 0.00, 0.00, '1', NULL, NULL, NULL, '{\"error\": \"insufficient_funds\", \"code\": \"E001\"}', NULL, NULL, NULL, NULL, '2026-03-08 08:50:13', '2026-03-08 08:50:13', '2026-03-08 08:50:13');

-- --------------------------------------------------------

--
-- Table structure for table `pincode_inquiries`
--

CREATE TABLE `pincode_inquiries` (
  `id` int(11) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pincode_inquiries`
--

INSERT INTO `pincode_inquiries` (`id`, `pincode`, `product_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, '110001', 7, '192.168.1.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', '2026-03-03 14:20:45'),
(2, '400001', 10, '192.168.1.110', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)', '2026-03-04 14:20:45'),
(3, '700001', 1, '192.168.1.115', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', '2026-03-05 14:20:45'),
(4, '560001', 16, '192.168.1.120', 'Mozilla/5.0 (Linux; Android 11; SM-G998B)', '2026-03-06 14:20:45'),
(5, '380001', 4, '192.168.1.125', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0)', '2026-03-07 14:20:45'),
(6, '110001', 5, '192.168.1.130', 'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X)', '2026-03-08 14:20:45');

-- --------------------------------------------------------

--
-- Table structure for table `price_history`
--

CREATE TABLE `price_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `new_price` decimal(10,2) NOT NULL,
  `price_type` enum('retail','wholesale','bulk') DEFAULT 'retail',
  `changed_by` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `effective_from` timestamp NOT NULL DEFAULT current_timestamp(),
  `effective_to` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `price_history`
--

INSERT INTO `price_history` (`id`, `product_id`, `variant_id`, `old_price`, `new_price`, `price_type`, `changed_by`, `reason`, `effective_from`, `effective_to`) VALUES
(1, 1, NULL, 799.00, 899.00, 'retail', 1, 'Diwali festive pricing ended', '2026-01-07 08:53:10', '2026-02-06 08:53:10'),
(2, 1, NULL, 899.00, 849.00, 'retail', 1, 'New year discount', '2026-02-06 08:53:10', '2026-02-21 08:53:10'),
(3, 1, NULL, 849.00, 899.00, 'retail', 1, 'Discount ended', '2026-02-21 08:53:10', NULL),
(4, 4, NULL, 549.00, 599.00, 'retail', 1, 'Price revision due to copper cost increase', '2026-01-22 08:53:10', NULL),
(5, 7, NULL, 2799.00, 2499.00, 'retail', 1, 'Festive season special', '2026-02-06 08:53:10', '2026-02-21 08:53:10'),
(6, 7, NULL, 2499.00, 2699.00, 'retail', 1, 'Post-festive price adjustment', '2026-02-21 08:53:10', '2026-03-23 08:53:10'),
(7, 2, 1, 1199.00, 1299.00, 'retail', 1, 'Small size price update', '2026-02-16 08:53:10', NULL),
(8, 2, 2, 1599.00, 1699.00, 'retail', 1, 'Medium size price update', '2026-02-16 08:53:10', NULL),
(9, 10, NULL, 449.00, 449.00, 'wholesale', 1, 'Wholesale price stable', '2026-02-06 08:53:10', NULL),
(10, 16, NULL, 799.00, 849.00, 'wholesale', 1, 'Cashew market price increased', '2026-02-21 08:53:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sub_category_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `product_type` enum('simple','variable','bundle') DEFAULT 'simple',
  `has_variants` tinyint(1) DEFAULT 0,
  `size` varchar(100) DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `base_retail_price` decimal(10,2) DEFAULT NULL,
  `base_wholesale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `selling_mode` enum('both','single_only','bulk_only') DEFAULT 'both',
  `min_order_quantity` int(11) DEFAULT 1,
  `max_order_quantity` int(11) DEFAULT NULL,
  `bulk_min_quantity` int(11) DEFAULT 10,
  `is_bulk_only` tinyint(1) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `track_inventory` tinyint(1) DEFAULT 1,
  `allow_backorder` tinyint(1) DEFAULT 0,
  `shipping_class` varchar(100) DEFAULT NULL,
  `shipping_weight` decimal(10,2) DEFAULT NULL,
  `free_shipping` tinyint(1) DEFAULT 0,
  `tax_class` varchar(100) DEFAULT NULL,
  `gst_rate` decimal(5,2) DEFAULT NULL,
  `main_image` varchar(500) DEFAULT NULL,
  `hover_image` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `is_on_sale` tinyint(1) DEFAULT 0,
  `is_trending` tinyint(1) DEFAULT 0,
  `is_bulk_item` tinyint(1) DEFAULT 0,
  `bulk_pricing_model` enum('fixed','tiered','range') DEFAULT 'fixed',
  `has_tiered_pricing` tinyint(1) DEFAULT 0,
  `total_sold` int(11) DEFAULT 0,
  `total_revenue` decimal(10,2) DEFAULT 0.00,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `canonical_url` varchar(500) DEFAULT NULL,
  `schema_markup` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`schema_markup`)),
  `search_keywords` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sub_category_id`, `product_code`, `name`, `slug`, `short_description`, `description`, `product_type`, `has_variants`, `size`, `weight`, `dimensions`, `material`, `color`, `base_retail_price`, `base_wholesale_price`, `cost_price`, `mrp`, `selling_mode`, `min_order_quantity`, `max_order_quantity`, `bulk_min_quantity`, `is_bulk_only`, `stock_quantity`, `low_stock_threshold`, `track_inventory`, `allow_backorder`, `shipping_class`, `shipping_weight`, `free_shipping`, `tax_class`, `gst_rate`, `main_image`, `hover_image`, `video_url`, `meta_title`, `meta_description`, `meta_keywords`, `is_active`, `is_featured`, `is_new`, `is_on_sale`, `is_trending`, `is_bulk_item`, `bulk_pricing_model`, `has_tiered_pricing`, `total_sold`, `total_revenue`, `average_rating`, `review_count`, `view_count`, `created_at`, `updated_at`, `canonical_url`, `schema_markup`, `search_keywords`) VALUES
(1, 1, 'WDN-ELE-001', 'Wooden Elephant', 'wooden-elephant-8-inch', 'Handcrafted wooden elephant decor piece', 'Beautiful handcarved wooden elephant, perfect for home decor and gifting', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 2000.00, 1500.00, NULL, 2500.00, 'both', 1, NULL, 10, 0, 100, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wooden-elephant-1.jpg', NULL, NULL, 'Buy Handcrafted Wooden Elephant Statue Online | Traditional Indian Decor', 'Buy beautiful handcrafted wooden elephant statue online. Traditional Indian decor piece, 8 inch size. Perfect for home decoration, gifting, and Vastu. Bulk orders available at wholesale price.', 'wooden elephant, wooden elephant statue, indian handicrafts, wooden decor, home decoration, vastu items, ganesha elephant, wooden figurine, traditional indian art, handcrafted elephant', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:15', NULL, NULL, NULL),
(2, 1, 'WDN-HOR-002', 'Wooden Horse', 'wooden-horse-10-inch', 'Handcrafted wooden horse decor piece', 'Elegant wooden horse with fine detailing, perfect for showcase', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 2400.00, 1800.00, NULL, 3000.00, 'both', 1, NULL, 10, 0, 75, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wooden-horse-1.jpg', NULL, NULL, 'Buy Handcrafted Wooden Horse Statue | Traditional Indian Decor', 'Shop authentic handcrafted wooden horse statue online. 10 inch decorative wooden horse with fine detailing. Perfect for home decor, gifts, and collection. Bulk wholesale price available.', 'wooden horse, wooden horse statue, decorative horse, indian handicrafts, wooden decor, home decoration, wooden figurine, traditional art, handcrafted horse, wooden sculpture', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:15', NULL, NULL, NULL),
(3, 1, 'WDN-JBX-003', 'Wooden Jewelry Box', 'wooden-jewelry-box-small', 'Handcrafted wooden jewelry box with velvet lining', 'Small wooden jewelry box with multiple compartments and soft velvet lining', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 900.00, 650.00, NULL, 1200.00, 'both', 1, NULL, 10, 0, 150, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'jewelry-box-small-1.jpg', NULL, NULL, 'Buy Small Wooden Jewelry Box Online | Handcrafted Storage Box', 'Shop beautiful small wooden jewelry box with velvet lining. Handcrafted storage box for jewelry, watches, and accessories. Multiple compartments. Ideal for gifting. Bulk order available.', 'wooden jewelry box, small jewelry box, wooden storage box, handcrafted box, jewelry organizer, wooden gift box, velvet lined box, indian handicrafts, jewellery box, wooden keepsake box', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(4, 1, 'WDN-JBX-004', 'Wooden Jewelry Box', 'wooden-jewelry-box-medium', 'Medium wooden jewelry box with multiple compartments', 'Medium sized jewelry box with intricate carvings and velvet interior', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1500.00, 1100.00, NULL, 2000.00, 'both', 1, NULL, 10, 0, 100, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'jewelry-box-medium-1.jpg', NULL, NULL, 'Buy Medium Wooden Jewelry Box Online | Handcrafted Organizer', 'Shop elegant medium wooden jewelry box with intricate carvings and velvet interior. Perfect for organizing jewelry and accessories. Handcrafted by Indian artisans. Bulk wholesale price.', 'wooden jewelry box medium, wooden jewellery box, handcrafted jewelry box, wooden storage, jewelry organizer, carved wooden box, gift box, indian handicrafts, wooden keepsake, velvet lined box', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(5, 1, 'WDN-KEY-005', 'Wooden Key Holder', 'wooden-key-holder-wall-mount', 'Wall mounted wooden key holder', 'Decorative wooden key holder with multiple hooks, perfect for entrance', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 600.00, 420.00, NULL, 800.00, 'both', 1, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'key-holder-1.jpg', NULL, NULL, 'Buy Wooden Key Holder Wall Mount | Decorative Key Rack Online', 'Shop decorative wooden key holder with multiple hooks. Wall mounted key rack for entrance. Handcrafted design keeps your keys organized. Perfect for home and office. Bulk order available.', 'wooden key holder, key rack, wall mounted key holder, key organiser, wooden key rack, decorative key holder, entryway organizer, home accessories, handcrafted key holder, wooden wall decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:15', NULL, NULL, NULL),
(6, 1, 'WDN-WAL-006', 'Wooden Wall Hanging', 'wooden-wall-hanging-medium', 'Decorative wooden wall hanging piece', 'Intricately carved wooden wall hanging with traditional design', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1200.00, 850.00, NULL, 1600.00, 'both', 1, NULL, 10, 0, 80, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wall-hanging-1.jpg', NULL, NULL, 'Buy Wooden Wall Hanging Online | Traditional Indian Wall Decor', 'Shop beautiful wooden wall hanging with intricate carvings. Traditional Indian design wall decor piece for living room, bedroom, or office. Handcrafted by skilled artisans. Bulk wholesale.', 'wooden wall hanging, wall decor, indian wall hanging, wooden wall art, traditional wall decor, handcrafted wall hanging, wooden carvings, home decoration, ethnic decor, wall accent', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:15', NULL, NULL, NULL),
(7, 1, 'WDN-TRA-007', 'Wooden Tray', 'wooden-tray-medium', 'Handcrafted wooden serving tray', 'Beautiful wooden tray with carved edges, perfect for serving', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1000.00, 750.00, NULL, 1400.00, 'both', 1, NULL, 10, 0, 120, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wooden-tray-1.jpg', NULL, NULL, 'Buy Handcrafted Wooden Serving Tray Online | Decorative Tray', 'Shop elegant handcrafted wooden serving tray with carved edges. Perfect for serving tea, coffee, snacks, or as decorative piece. Made from premium wood. Bulk wholesale price available.', 'wooden tray, serving tray, handcrafted tray, wooden serving platter, decorative tray, tea tray, snack tray, wooden serveware, indian handicrafts, gift tray, wooden kitchenware', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:15', NULL, NULL, NULL),
(8, 1, 'WDN-PEN-008', 'Wooden Pen Stand', 'wooden-pen-stand-small', 'Handcrafted wooden pen stand for desk', 'Elegant wooden pen stand with multiple sections for stationery', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 400.00, 280.00, NULL, 550.00, 'both', 1, NULL, 10, 0, 300, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'pen-stand-1.jpg', NULL, NULL, 'Buy Wooden Pen Stand Online | Handcrafted Desk Organizer', 'Shop elegant wooden pen stand with multiple sections. Handcrafted desk organizer for office, study, or home. Perfect for stationery organization. Bulk order available at wholesale price.', 'wooden pen stand, pen holder, desk organizer, wooden stationery, office accessories, handcrafted pen stand, wooden desk accessory, study table organizer, gift item, wooden office supply', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:15', NULL, NULL, NULL),
(9, 1, 'WDN-CLO-009', 'Wooden Clock', 'wooden-clock-medium', 'Decorative wooden wall clock', 'Modern wooden wall clock with silent movement mechanism', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 2000.00, 1450.00, NULL, 2800.00, 'both', 1, NULL, 10, 0, 50, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wooden-clock-1.jpg', NULL, NULL, 'Buy Decorative Wooden Wall Clock Online | Handcrafted Timepiece', 'Shop beautiful decorative wooden wall clock with silent movement mechanism. Handcrafted design adds warmth to any room. Perfect for living room, bedroom, or office. Bulk wholesale price.', 'wooden clock, wall clock, decorative clock, wooden wall clock, handcrafted clock, silent wall clock, wooden timepiece, home decor clock, indian handicrafts, wooden home decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:16', NULL, NULL, NULL),
(10, 1, 'WDN-TEM-010', 'Wooden Temple', 'wooden-temple-small', 'Small wooden temple for home', 'Handcrafted wooden temple with intricate carvings, perfect for home puja', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 7000.00, 5200.00, NULL, 9500.00, 'both', 1, NULL, 10, 0, 30, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wooden-temple-small-1.jpg', NULL, NULL, 'Buy Small Wooden Temple for Home Online | Handcrafted Mandir', 'Shop beautiful small wooden temple for home puja. Handcrafted mandir with intricate carvings and traditional design. Perfect for home worship. Bulk order available for temples and resellers.', 'wooden temple, home mandir, wooden mandir, puja temple, home temple, handcrafted temple, wooden pooja mandir, religious decor, hindu temple, worship place, wooden shrine', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:16', NULL, NULL, NULL),
(11, 1, 'WDN-TEM-011', 'Wooden Temple', 'wooden-temple-medium', 'Medium wooden temple for home', 'Medium sized wooden temple with detailed architecture and carvings', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 11500.00, 8500.00, NULL, 15000.00, 'both', 1, NULL, 10, 0, 20, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wooden-temple-medium-1.jpg', NULL, NULL, 'Buy Medium Wooden Temple for Home | Handcrafted Puja Mandir', 'Shop elegant medium wooden temple for home with detailed architecture and carvings. Spacious design for idols and puja items. Handcrafted by skilled artisans. Bulk wholesale price.', 'wooden temple medium, home mandir, wooden mandir, puja temple, home temple, handcrafted temple, wooden pooja mandir, religious furniture, hindu temple, worship shrine', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:16', NULL, NULL, NULL),
(12, 1, 'WDN-PAN-012', 'Decorative Wooden Panel', 'decorative-wooden-panel-large', 'Large decorative wooden wall panel', 'Intricately carved wooden panel for wall decoration, traditional design', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 8000.00, 6000.00, NULL, 11000.00, 'both', 1, NULL, 10, 0, 25, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wooden-panel-1.jpg', NULL, NULL, 'Buy Large Decorative Wooden Panel Online | Wall Art Panel', 'Shop large decorative wooden panel with intricate carvings. Traditional design wall art for living room, hotel lobby, or office. Statement piece for interior decoration. Bulk wholesale available.', 'wooden panel, decorative panel, wall art panel, wooden wall art, carved panel, large wall decor, wooden screen, room divider, traditional art, handcrafted panel, wooden mural', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:16', NULL, NULL, NULL),
(13, 1, 'WDN-FRM-013', 'Wooden Photo Frame', 'wooden-photo-frame-standard', 'Standard size wooden photo frame', 'Beautiful wooden photo frame with carved border, fits 8x10 inch photo', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 600.00, 420.00, NULL, 850.00, 'both', 1, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'photo-frame-1.jpg', NULL, NULL, 'Buy Wooden Photo Frame Online | Handcrafted Picture Frame', 'Shop beautiful wooden photo frame with carved border. Standard size fits 8x10 inch photos. Perfect for displaying family pictures, wedding photos, or art prints. Bulk order available.', 'wooden photo frame, picture frame, wooden frame, handcrafted frame, photo frame online, carved frame, wall frame, tabletop frame, gift frame, indian handicrafts, wooden picture frame', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:16', NULL, NULL, NULL),
(14, 1, 'WDN-BOW-014', 'Wooden Serving Bowl', 'wooden-serving-bowl-medium', 'Handcrafted wooden serving bowl', 'Medium sized wooden bowl for serving dry fruits or as decor', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 750.00, 550.00, NULL, 1000.00, 'both', 1, NULL, 10, 0, 90, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wooden-bowl-1.jpg', NULL, NULL, 'Buy Wooden Serving Bowl Online | Handcrafted Fruit Bowl', 'Shop handcrafted wooden serving bowl for dry fruits, snacks, or as centerpiece. Medium sized decorative bowl made from premium wood. Perfect for home and gifting. Bulk wholesale price.', 'wooden bowl, serving bowl, fruit bowl, wooden serving dish, handcrafted bowl, decorative bowl, centerpiece bowl, wooden kitchenware, indian handicrafts, wooden decor, snack bowl', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:16', NULL, NULL, NULL),
(15, 1, 'WDN-BOK-015', 'Wooden Book Stand', 'wooden-book-stand-medium', 'Decorative wooden book stand', 'Handcrafted wooden book stand for displaying religious or coffee table books', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1300.00, 950.00, NULL, 1800.00, 'both', 1, NULL, 10, 0, 60, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'book-stand-1.jpg', NULL, NULL, 'Buy Wooden Book Stand Online | Decorative Display Stand', 'Shop handcrafted wooden book stand for displaying religious books, coffee table books, or art pieces. Elegant design adds charm to any room. Perfect for home and library. Bulk wholesale.', 'wooden book stand, book holder, display stand, book rest, wooden easel, religious book stand, coffee table decor, handcrafted stand, wooden display, book pedestal, reading stand', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:16', NULL, NULL, NULL),
(16, 1, 'WDN-MAG-016', 'Wooden Magazine Holder', 'wooden-magazine-holder-large', 'Large wooden magazine holder', 'Decorative wooden magazine holder for living room organization', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1600.00, 1200.00, NULL, 2200.00, 'both', 1, NULL, 10, 0, 45, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'magazine-holder-1.jpg', NULL, NULL, 'Buy Wooden Magazine Holder Online | Decorative Storage Rack', 'Shop decorative wooden magazine holder for organizing magazines, newspapers, and mail. Large size with elegant design. Perfect for living room, office, or waiting area. Bulk wholesale price.', 'wooden magazine holder, magazine rack, newspaper holder, wooden storage, mail organizer, magazine stand, living room organizer, handcrafted holder, wooden rack, home organization', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:16', NULL, NULL, NULL),
(17, 1, 'WDN-DIA-017', 'Wooden Diary', 'wooden-diary-standard', 'Handmade wooden cover diary', 'Eco-friendly diary with wooden cover and handmade paper inside', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 500.00, 350.00, NULL, 700.00, 'both', 1, NULL, 10, 0, 150, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wooden-diary-1.jpg', NULL, NULL, 'Buy Handmade Wooden Cover Diary Online | Eco-friendly Journal', 'Shop eco-friendly handmade diary with wooden cover and handmade paper inside. Perfect for journaling, sketching, or as gift. Unique handcrafted stationery. Bulk order available.', 'wooden diary, handmade journal, wooden notebook, eco-friendly diary, handcrafted diary, wooden cover notebook, sustainable stationery, gift journal, artisan diary, wooden writing pad', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:16', NULL, NULL, NULL),
(18, 1, 'WDN-CAM-018', 'Wooden Camel', 'wooden-camel-8-inch', 'Handcrafted wooden camel figurine', 'Artistic wooden camel figurine, symbol of desert beauty', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1900.00, 1400.00, NULL, 2500.00, 'both', 1, NULL, 10, 0, 80, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'wooden-camel-1.jpg', NULL, NULL, 'Buy Handcrafted Wooden Camel Figurine | Traditional Decor', 'Shop artistic wooden camel figurine, symbol of desert beauty. Handcrafted by skilled artisans. Perfect for home decor, collection, and gifts. Bulk wholesale price available.', 'wooden camel, camel figurine, wooden animal statue, desert decor, handcrafted camel, wooden sculpture, traditional art, indian handicrafts, wooden animal, decorative camel, folk art', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:08', '2026-03-08 14:41:16', NULL, NULL, NULL),
(19, 2, 'BRS-DIY-019', 'Brass Diya', 'brass-diya-small', 'Small brass diya for daily puja', 'Traditional small brass diya with lotus design, perfect for daily worship', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 400.00, 280.00, NULL, 550.00, 'both', 1, NULL, 10, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'brass-diya-small-1.jpg', NULL, NULL, 'Buy Small Brass Diya Online | Traditional Puja Lamp', 'Shop traditional small brass diya with lotus design for daily worship. Perfect for home temple, festivals, and special occasions. Handcrafted by skilled artisans. Bulk wholesale price.', 'brass diya, small diya, puja lamp, brass lamp, traditional diya, religious lamp, temple diya, hindu worship, brass handicrafts, festival diya, diwali diya, handmade diya', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-20 17:22:04', NULL, NULL, NULL),
(20, 2, 'BRS-DIY-020', 'Brass Diya', 'brass-diya-medium', 'Medium brass diya for special occasions', 'Medium sized brass diya with intricate carvings, ideal for festivals', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 650.00, 450.00, NULL, 900.00, 'both', 1, NULL, 10, 0, 400, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'brass-diya-medium-1.jpg', NULL, NULL, 'Buy Medium Brass Diya Online | Decorative Puja Lamp', 'Shop medium brass diya with intricate carvings for special occasions and festivals. Beautiful traditional lamp for home temple and decoration. Handcrafted in India. Bulk wholesale available.', 'brass diya medium, decorative diya, brass lamp, puja lamp, traditional lamp, festival diya, temple lamp, religious decor, brass handicraft, diwali lamp, handcrafted diya', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-20 17:22:04', NULL, NULL, NULL),
(21, 2, 'BRS-GAN-021', 'Brass Ganesh Idol', 'brass-ganesh-idol-8-inch', 'Brass Ganesh idol for home temple', 'Beautiful brass Ganesh idol with fine detailing, 8 inch height', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 4000.00, 3000.00, NULL, 5500.00, 'both', 1, NULL, 10, 0, 60, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'brass-ganesh-1.jpg', NULL, NULL, 'Buy Brass Ganesh Idol Online | 8 Inch Hindu God Statue', 'Shop beautiful brass Ganesh idol with fine detailing. 8 inch height, perfect for home temple, office, or gifting. Brings prosperity and good luck. Handcrafted by skilled artisans.', 'brass ganesh, ganesh idol, brass statue, hindu god, ganesha murti, religious statue, home temple idol, brass handicraft, lakshmi ganesh, festival murti, ganesh ji, vinayaka', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(22, 2, 'BRS-LAK-022', 'Brass Lakshmi Idol', 'brass-lakshmi-idol-8-inch', 'Brass Lakshmi idol for prosperity', 'Elegant brass Lakshmi idol, 8 inch, perfect for Diwali and daily worship', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 3900.00, 2900.00, NULL, 5400.00, 'both', 1, NULL, 10, 0, 55, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'brass-lakshmi-1.jpg', NULL, NULL, 'Buy Brass Lakshmi Idol Online | Goddess of Wealth Statue', 'Shop elegant brass Lakshmi idol, goddess of wealth and prosperity. 8 inch statue perfect for Diwali, home temple, and business establishments. Handcrafted by skilled artisans.', 'brass lakshmi, lakshmi idol, goddess lakshmi, brass statue, hindu goddess, laxmi murti, wealth goddess, diwali idol, religious statue, home temple, brass handicraft, lakshmi ji', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(23, 2, 'BRS-THA-023', 'Brass Pooja Thali', 'brass-pooja-thali-standard', 'Standard brass pooja thali set', 'Complete brass pooja thali with kumkum box, diya, and accessories', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 2500.00, 1850.00, NULL, 3500.00, 'both', 1, NULL, 10, 0, 120, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'brass-thali-1.jpg', NULL, NULL, 'Buy Brass Pooja Thali Set Online | Complete Puja Thali', 'Shop complete brass pooja thali set with kumkum box, diya, and accessories. Traditional design for daily worship and festivals. Perfect for home temple and gifting. Bulk wholesale price.', 'brass pooja thali, puja thali set, worship thali, brass thali, religious plate, temple thali, pooja accessories, hindu worship, festival thali, diwali thali, handmade thali', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(24, 2, 'BRS-BEL-024', 'Brass Bell', 'brass-bell-small', 'Small brass bell for temple', 'Traditional small brass bell with clear resonant sound', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 900.00, 650.00, NULL, 1250.00, 'both', 1, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'brass-bell-small-1.jpg', NULL, NULL, 'Buy Small Brass Bell Online | Traditional Temple Bell', 'Shop traditional small brass bell with clear resonant sound for temple and home puja. Handcrafted design with carved handle. Perfect for daily worship. Bulk wholesale price available.', 'brass bell small, temple bell, puja bell, ghanti, religious bell, brass ghanti, home temple bell, worship bell, hindu bell, handmade bell, ringing bell, brass handicraft', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-20 17:22:04', NULL, NULL, NULL),
(25, 2, 'BRS-BEL-025', 'Brass Bell', 'brass-bell-medium', 'Medium brass bell for temple', 'Medium sized brass bell with carved handle, produces deep sound', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1600.00, 1200.00, NULL, 2200.00, 'both', 1, NULL, 10, 0, 150, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'brass-bell-medium-1.jpg', NULL, NULL, 'Buy Medium Brass Bell Online | Deep Sound Temple Bell', 'Shop medium brass bell with deep resonant sound for temples and home puja. Handcrafted with carved handle. Produces clear spiritual sound. Bulk wholesale price for temples and resellers.', 'brass bell medium, temple bell, puja bell, large ghanti, religious bell, brass ghanti, deep sound bell, temple ghanta, worship bell, hindu temple bell, handmade bell', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-20 17:22:04', NULL, NULL, NULL),
(26, 2, 'CPR-BOT-026', 'Copper Bottle', 'copper-bottle-1-liter', 'Pure copper water bottle', 'Ayurvedic copper water bottle, 1 liter capacity with brass cap', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1200.00, 850.00, NULL, 1700.00, 'both', 1, NULL, 10, 0, 180, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'copper-bottle-1.jpg', NULL, NULL, 'Buy Pure Copper Water Bottle Online | 1 Liter Ayurvedic Bottle', 'Shop pure copper water bottle with brass cap, 1 liter capacity. Ayurvedic health benefits, keeps water fresh and healthy. Perfect for home, office, and yoga. Bulk wholesale price available.', 'copper bottle, copper water bottle, ayurvedic bottle, pure copper bottle, drinking water bottle, copper vessel, health bottle, yoga bottle, copper utensil, alkaline water, metal bottle', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(27, 2, 'CPR-GLA-027', 'Copper Glass Set', 'copper-glass-set-4-pcs', 'Set of 4 copper glasses', 'Traditional copper glasses set of 4, perfect for serving water', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1600.00, 1200.00, NULL, 2200.00, 'both', 1, NULL, 10, 0, 140, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'copper-glass-1.jpg', NULL, NULL, 'Buy Copper Glass Set Online | Set of 4 Traditional Glasses', 'Shop traditional copper glass set of 4 pieces for serving water. Ayurvedic health benefits, keeps water naturally cool. Perfect for home, restaurants, and gifts. Bulk wholesale price.', 'copper glass set, copper glasses, copper tumblers, ayurvedic glasses, drinking glasses, copper vessel set, traditional glasses, metal glasses, copper serveware, wellness products', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(28, 2, 'MTL-ART-028', 'Metal Wall Art', 'metal-wall-art-medium', 'Decorative metal wall art piece', 'Handcrafted metal wall art with traditional motifs, medium size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 5000.00, 3800.00, NULL, 7000.00, 'both', 1, NULL, 10, 0, 40, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'metal-wall-art-1.jpg', NULL, NULL, 'Buy Metal Wall Art Online | Handcrafted Indian Decor', 'Shop handcrafted metal wall art with traditional motifs. Medium size decorative piece for living room, bedroom, or office. Made from high quality metal with antique finish. Bulk wholesale.', 'metal wall art, wall decor, metal art, indian wall art, handcrafted metal, decorative wall piece, metal wall hanging, traditional art, home decoration, metal craft, wrought iron art', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(29, 2, 'MTL-CLO-029', 'Metal Wall Clock', 'metal-wall-clock-large', 'Large metal wall clock', 'Decorative metal wall clock with antique finish, large size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 5600.00, 4200.00, NULL, 7800.00, 'both', 1, NULL, 10, 0, 35, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'metal-clock-1.jpg', NULL, NULL, 'Buy Large Metal Wall Clock Online | Antique Finish Timepiece', 'Shop large decorative metal wall clock with antique finish. Silent movement mechanism. Statement piece for living room, office, or hotel lobby. Handcrafted design. Bulk wholesale price.', 'metal clock, wall clock large, antique clock, decorative clock, metal wall clock, vintage clock, silent wall clock, home decor clock, industrial clock, metal timepiece, big clock', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(30, 2, 'IRN-LAN-030', 'Iron Lantern', 'iron-lantern-medium', 'Medium iron lantern for decor', 'Vintage style iron lantern with glass panels, medium size', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 2200.00, 1650.00, NULL, 3000.00, 'both', 1, NULL, 10, 0, 70, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'iron-lantern-medium-1.jpg', NULL, NULL, 'Buy Medium Iron Lantern Online | Vintage Style Decor', 'Shop medium iron lantern with glass panels. Vintage style for indoor and outdoor decor. Perfect for garden, patio, balcony, or living room. Handcrafted rustic design. Bulk wholesale available.', 'iron lantern, medium lantern, vintage lantern, metal lantern, outdoor lantern, garden lantern, rustic decor, candle lantern, patio lantern, iron decor, antique lantern, hanging lantern', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-20 17:22:04', NULL, NULL, NULL),
(31, 2, 'IRN-LAN-031', 'Iron Lantern', 'iron-lantern-large', 'Large iron lantern for outdoor decor', 'Large vintage iron lantern, perfect for garden or patio', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 3200.00, 2400.00, NULL, 4500.00, 'both', 1, NULL, 10, 0, 50, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'iron-lantern-large-1.jpg', NULL, NULL, 'Buy Large Iron Lantern Online | Vintage Outdoor Lantern', 'Shop large iron lantern with glass panels for outdoor decor. Perfect for garden, pathway, entrance, or patio. Vintage rustic design creates ambient lighting. Bulk wholesale price for hotels.', 'iron lantern large, outdoor lantern, vintage lantern, garden lantern, pathway lantern, metal lantern, large lantern, standing lantern, rustic decor, antique lantern, iron decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-20 17:22:04', NULL, NULL, NULL),
(32, 2, 'CAN-STD-032', 'Candle Stand', 'candle-stand-medium', 'Medium candle stand for decor', 'Elegant metal candle stand with traditional design, medium size', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1800.00, 1300.00, NULL, 2500.00, 'both', 1, NULL, 10, 0, 90, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'candle-stand-medium-1.jpg', NULL, NULL, 'Buy Medium Candle Stand Online | Decorative Metal Candle Holder', 'Shop elegant metal candle stand with traditional design. Medium size for pillar candles. Perfect for home decor, parties, weddings, and special occasions. Handcrafted. Bulk wholesale price.', 'candle stand, candle holder, metal candle stand, candle pedestal, decorative candle holder, medium candle stand, pillar candle holder, wedding decor, home decor, metal candlestick', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-20 17:22:04', NULL, NULL, NULL),
(33, 2, 'CAN-STD-033', 'Candle Stand', 'candle-stand-large', 'Large candle stand for floor decor', 'Tall metal candle stand, large size for floor decoration', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 3000.00, 2200.00, NULL, 4200.00, 'both', 1, NULL, 10, 0, 60, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'candle-stand-large-1.jpg', NULL, NULL, 'Buy Large Candle Stand Online | Floor Candle Holder', 'Shop large metal candle stand for floor decoration. Tall elegant design for living room, hotel lobby, wedding decoration, and events. Handcrafted with traditional motifs. Bulk wholesale available.', 'large candle stand, floor candle holder, tall candle stand, floor candlestick, wedding decor, event decor, metal candle holder, decorative stand, pillar candle holder, home decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-20 17:22:04', NULL, NULL, NULL),
(34, 2, 'FLW-VAS-034', 'Flower Vase', 'flower-vase-large', 'Large metal flower vase', 'Beautiful metal flower vase with intricate designs, large size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 3500.00, 2600.00, NULL, 4800.00, 'both', 1, NULL, 10, 0, 45, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'flower-vase-1.jpg', NULL, NULL, 'Buy Large Metal Flower Vase Online | Decorative Floor Vase', 'Shop large metal flower vase with intricate designs. Perfect floor vase for artificial or dried flowers. Statement piece for living room, hotel lobby, or office. Handcrafted. Bulk wholesale.', 'flower vase large, metal vase, floor vase, decorative vase, large vase, metal flower vase, home decor vase, floor decor, reception vase, hotel decor, artificial flower vase', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(35, 2, 'URL-BOW-035', 'Urli Bowl', 'urli-bowl-medium', 'Medium urli bowl for floral decor', 'Traditional urli bowl for floating flowers and candles, medium size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 5500.00, 4200.00, NULL, 7500.00, 'both', 1, NULL, 10, 0, 30, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'urli-bowl-1.jpg', NULL, NULL, 'Buy Medium Urli Bowl Online | Traditional Floating Bowl', 'Shop traditional urli bowl for floating flowers and candles. Medium size perfect for home entrance, living room, or garden decor. Handcrafted metal design. Bulk wholesale price available.', 'urli bowl, floating bowl, metal urli, traditional bowl, flower bowl, entrance decor, home decor bowl, floating candle bowl, brass urli, decorative bowl, metal urli, wedding decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(36, 2, 'BRS-URL-036', 'Brass Urli', 'brass-urli-large', 'Large brass urli for home entrance', 'Large decorative brass urli for home entrance or garden', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 8500.00, 6500.00, NULL, 11500.00, 'both', 1, NULL, 10, 0, 20, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'brass-urli-1.jpg', NULL, NULL, 'Buy Large Brass Urli Online | Decorative Entrance Bowl', 'Shop large decorative brass urli for home entrance, garden, or living room. Perfect for floating flowers and candles. Traditional design adds elegance to any space. Bulk wholesale price.', 'brass urli, large urli, brass bowl, entrance urli, decorative urli, metal urli, floating bowl, garden decor, home entrance decor, traditional bowl, brass decor, wedding urli', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(37, 2, 'MTL-PEA-037', 'Metal Peacock Decor', 'metal-peacock-decor-medium', 'Metal peacock decorative piece', 'Beautiful metal peacock figurine with intricate detailing, medium size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 3800.00, 2800.00, NULL, 5200.00, 'both', 1, NULL, 10, 0, 35, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'peacock-decor-1.jpg', NULL, NULL, 'Buy Metal Peacock Decor Online | Decorative Showpiece', 'Shop beautiful metal peacock decorative piece with intricate detailing. Medium size perfect for living room, office, or gift. Symbol of beauty and grace. Handcrafted. Bulk wholesale price.', 'metal peacock, peacock decor, peacock showpiece, metal art, peacock statue, decorative peacock, bird decor, metal figurine, indian art, home decor, peacock craft, metal sculpture', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(38, 2, 'BRS-INC-038', 'Brass Incense Holder', 'brass-incense-holder-small', 'Small brass incense stick holder', 'Traditional brass incense holder with ash collection tray', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 600.00, 420.00, NULL, 850.00, 'both', 1, NULL, 10, 0, 250, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'incense-holder-1.jpg', NULL, NULL, 'Buy Brass Incense Holder Online | Traditional Dhoop Stand', 'Shop small brass incense holder with ash collection tray. Traditional design for agarbatti and dhoop sticks. Perfect for home temple, meditation, and yoga. Bulk wholesale price available.', 'brass incense holder, agarbatti stand, dhoop holder, incense stick holder, brass dhoop stand, puja accessories, meditation supplies, yoga accessories, temple items, brass handicraft', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:17', '2026-03-08 14:41:28', NULL, NULL, NULL),
(39, 3, 'MRB-GAN-039', 'Marble Ganesh Idol', 'marble-ganesh-idol-6-inch', 'White marble Ganesh idol', 'Elegant white marble Ganesh idol with fine carving, 6 inch', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 3400.00, 2500.00, NULL, 4700.00, 'both', 1, NULL, 10, 0, 40, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'marble-ganesh-1.jpg', NULL, NULL, 'Buy White Marble Ganesh Idol Online | 6 Inch Hindu God Statue', 'Shop elegant white marble Ganesh idol with fine carving. 6 inch statue for home temple, office, or gifting. Pure white marble brings purity and prosperity. Handcrafted by skilled artisans.', 'marble ganesh, white marble idol, ganesh murti, marble statue, hindu god, ganesha idol, marble sculpture, home temple idol, religious statue, white marble art, marble handicraft', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(40, 3, 'MRB-TEM-040', 'Marble Temple', 'marble-temple-small', 'Small marble temple for home', 'Handcrafted marble temple with pillars and dome, small size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 10000.00, 7500.00, NULL, 14000.00, 'both', 1, NULL, 10, 0, 15, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'marble-temple-1.jpg', NULL, NULL, 'Buy Small Marble Temple Online | Handcrafted Mandir', 'Shop handcrafted small marble temple with pillars and dome. Beautiful mandir for home puja with space for idols. Pure white marble construction. Bulk wholesale price available.', 'marble temple, small mandir, marble mandir, home temple, white marble temple, pooja mandir, religious shrine, marble craft, temple decor, hindu temple, worship place, marble art', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(41, 3, 'STN-SHI-041', 'Stone Shivling', 'stone-shivling-small', 'Small stone Shivling for puja', 'Natural stone Shivling with base, small size for home temple', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1200.00, 900.00, NULL, 1700.00, 'both', 1, NULL, 10, 0, 60, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'stone-shivling-1.jpg', NULL, NULL, 'Buy Natural Stone Shivling Online | Small Puja Shivling', 'Shop natural stone Shivling with base for home puja and temple. Small size perfect for daily worship. Sacred symbol of Lord Shiva. Authentic stone from holy rivers. Bulk wholesale available.', 'stone shivling, shivling for puja, shiva lingam, natural stone linga, banalinga, narmada shivling, lord shiva, puja items, temple shivling, religious symbol, shiva worship', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(42, 3, 'STN-FOU-042', 'Stone Fountain', 'stone-fountain-medium', 'Medium stone fountain for garden', 'Beautiful stone fountain with water pump, medium size for garden', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 12800.00, 9500.00, NULL, 18000.00, 'both', 1, NULL, 10, 0, 10, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'stone-fountain-1.jpg', NULL, NULL, 'Buy Stone Fountain Online | Medium Water Fountain for Garden', 'Shop beautiful stone fountain with water pump. Medium size perfect for garden, patio, balcony, or indoor decor. Creates peaceful ambiance with flowing water. Bulk wholesale price.', 'stone fountain, water fountain, garden fountain, outdoor fountain, indoor fountain, decorative fountain, stone water feature, patio fountain, relaxing fountain, zen fountain, waterfall', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(43, 4, 'TER-POT-043', 'Terracotta Pot', 'terracotta-pot-medium', 'Medium terracotta pot for plants', 'Traditional terracotta pot, medium size, perfect for indoor plants', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 750.00, 550.00, NULL, 1000.00, 'both', 1, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'terracotta-pot-1.jpg', NULL, NULL, 'Buy Medium Terracotta Pot Online | Traditional Clay Pot', 'Shop traditional terracotta pot for indoor plants. Medium size natural clay pot perfect for succulents, herbs, and houseplants. Eco-friendly and breathable for plant health. Bulk wholesale.', 'terracotta pot, clay pot, medium plant pot, earthen pot, natural pot, gardening pot, indoor plant pot, terracotta planter, clay planter, eco-friendly pot, handmade pot, rustic pot', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(44, 4, 'TER-PLA-044', 'Terracotta Wall Plate', 'terracotta-wall-plate-large', 'Large terracotta wall plate', 'Decorative terracotta wall plate with traditional paintings, large', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1600.00, 1200.00, NULL, 2200.00, 'both', 1, NULL, 10, 0, 80, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'terracotta-plate-1.jpg', NULL, NULL, 'Buy Large Terracotta Wall Plate Online | Decorative Wall Art', 'Shop large terracotta wall plate with traditional paintings. Decorative wall art piece for living room, dining area, or outdoor. Handcrafted and hand-painted by artisans. Bulk wholesale.', 'terracotta plate, wall plate, terracotta wall art, clay wall hanging, decorative plate, hand painted plate, traditional art, ethnic wall decor, terracotta art, folk art, pottery art', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(45, 4, 'HAN-BAS-045', 'Handmade Basket', 'handmade-basket-medium', 'Medium handmade decorative basket', 'Eco-friendly handmade basket from natural fibers, medium size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 650.00, 480.00, NULL, 900.00, 'both', 1, NULL, 10, 0, 120, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'handmade-basket-1.jpg', NULL, NULL, 'Buy Handmade Decorative Basket Online | Natural Fiber Basket', 'Shop eco-friendly handmade basket from natural fibers. Medium size for storage, decor, or gifting. Perfect for organizing home or as planter. Handcrafted by rural artisans. Bulk wholesale.', 'handmade basket, decorative basket, natural fiber basket, eco-friendly basket, storage basket, wicker basket, handwoven basket, rustic decor, home organization, artisan basket', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(46, 4, 'JUT-BAS-046', 'Jute Basket', 'jute-basket-large', 'Large jute storage basket', 'Large jute basket for storage or laundry, eco-friendly', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1000.00, 750.00, NULL, 1400.00, 'both', 1, NULL, 10, 0, 90, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'jute-basket-1.jpg', NULL, NULL, 'Buy Large Jute Basket Online | Eco-friendly Storage Basket', 'Shop large jute basket for storage, laundry, or home decor. Eco-friendly and sustainable natural fiber basket with handles. Perfect for organizing home. Bulk wholesale price available.', 'jute basket, large basket, storage basket, jute storage, laundry basket, eco-friendly basket, natural basket, handwoven basket, home organization, rustic storage, jute decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(47, 5, 'RES-BUD-047', 'Resin Buddha Statue', 'resin-buddha-statue-12-inch', 'Resin Buddha statue for meditation space', 'Peaceful Buddha statue made of high-quality resin, 12 inch', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 4300.00, 3200.00, NULL, 6000.00, 'both', 1, NULL, 10, 0, 30, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'buddha-statue-1.jpg', NULL, NULL, 'Buy Resin Buddha Statue Online | 12 Inch Peaceful Buddha', 'Shop peaceful Buddha statue made of high-quality resin. 12 inch size perfect for meditation space, living room, or garden. Symbol of peace and enlightenment. Bulk wholesale price.', 'buddha statue, resin buddha, buddha figurine, meditation statue, buddha decor, peaceful buddha, zen decor, buddha idol, spiritual decor, meditation room decor, buddhist art', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(48, 5, 'RES-SHO-048', 'Resin Decorative Showpiece', 'resin-decorative-showpiece-medium', 'Medium resin decorative showpiece', 'Modern resin showpiece with abstract design, medium size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 2000.00, 1500.00, NULL, 2800.00, 'both', 1, NULL, 10, 0, 50, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'resin-showpiece-1.jpg', NULL, NULL, 'Buy Resin Decorative Showpiece Online | Modern Art Decor', 'Shop modern resin decorative showpiece with abstract design. Medium size perfect for living room, office desk, or shelf. Contemporary art piece adds style to any space. Bulk wholesale.', 'resin showpiece, decorative showpiece, modern decor, abstract art, resin art, contemporary decor, home accent, table decor, office decor, gift item, designer showpiece, art collectible', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(49, 5, 'HAN-MIR-049', 'Handcrafted Mirror Frame', 'handcrafted-mirror-frame-large', 'Large decorative mirror with frame', 'Beautiful handcrafted mirror frame with traditional art, large size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 5100.00, 3800.00, NULL, 7000.00, 'both', 1, NULL, 10, 0, 20, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'mirror-frame-1.jpg', NULL, NULL, 'Buy Large Handcrafted Mirror Frame Online | Decorative Wall Mirror', 'Shop beautiful handcrafted mirror frame with traditional art. Large decorative mirror for living room, bedroom, or hallway. Intricate design adds elegance to any space. Bulk wholesale.', 'mirror frame, decorative mirror, wall mirror, handcrafted mirror, large mirror, wooden mirror, traditional mirror, indian mirror, home decor mirror, carved mirror frame, accent mirror', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(50, 5, 'LEA-JOU-050', 'Leather Journal', 'leather-journal-standard', 'Handmade leather journal', 'Premium handmade leather journal with blank pages', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1300.00, 950.00, NULL, 1800.00, 'both', 1, NULL, 10, 0, 100, 5, 1, 0, NULL, NULL, 0, NULL, 18.00, 'leather-journal-1.jpg', NULL, NULL, 'Buy Handmade Leather Journal Online | Premium Writing Diary', 'Shop premium handmade leather journal with blank pages. Perfect for journaling, sketching, note-taking, or as gift. Genuine leather cover with rustic charm. Bulk wholesale price available.', 'leather journal, handmade diary, leather notebook, writing journal, genuine leather journal, vintage journal, sketchbook, leather bound diary, gift journal, artisan journal, rustic diary', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:29', '2026-03-08 14:41:43', NULL, NULL, NULL),
(51, 6, 'TEX-BED-051', 'Cotton Bedsheet', 'cotton-bedsheet-single', 'Single cotton bedsheet 90x100 inch', 'Pure cotton bedsheet with matching pillow covers, single size', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 500.00, 380.00, NULL, 700.00, 'both', 1, NULL, 10, 0, 300, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'bedsheet-single-1.jpg', NULL, NULL, 'Buy Single Cotton Bedsheet Online | 90x100 Inch Pure Cotton', 'Shop pure cotton bedsheet for single bed with matching pillow covers. 90x100 inch size, soft and breathable fabric. Perfect for daily use. Bulk wholesale price for hotels and resellers.', 'cotton bedsheet, single bedsheet, cotton sheet, bed linen, pure cotton bedding, single bed sheet, cotton bedcover, bedroom textiles, soft bedsheet, breathable fabric, hotel linens', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(52, 6, 'TEX-BED-052', 'Cotton Bedsheet', 'cotton-bedsheet-double', 'Double cotton bedsheet 90x108 inch', 'Pure cotton bedsheet set with 2 pillow covers, double size', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1000.00, 750.00, NULL, 1400.00, 'both', 1, NULL, 10, 0, 250, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'bedsheet-double-1.jpg', NULL, NULL, 'Buy Double Cotton Bedsheet Online | 90x108 Inch with Pillow Covers', 'Shop pure cotton bedsheet set for double bed with 2 pillow covers. 90x108 inch size, soft and comfortable fabric. Perfect for home and hospitality use. Bulk wholesale price available.', 'cotton bedsheet double, double bedsheet, cotton sheet set, double bed sheet, pure cotton bedding, bed linen set, cotton bedcover, double bed linen, soft sheet, hotel bedsheet', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(53, 6, 'TEX-BED-053', 'King Size Bedsheet', 'king-size-bedsheet', 'King size bedsheet 108x108 inch', 'Premium king size bedsheet with 2 pillow covers', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1550.00, 1150.00, NULL, 2100.00, 'both', 1, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'bedsheet-king-1.jpg', NULL, NULL, 'Buy King Size Bedsheet Online | 108x108 Inch Premium Cotton', 'Shop premium king size bedsheet with 2 pillow covers. 108x108 inch size for king bed. Soft and luxurious cotton fabric. Perfect for master bedroom. Bulk wholesale price for hotels.', 'king size bedsheet, king bedsheet, large bedsheet, king sheet set, premium bedding, king bed linen, cotton king sheet, luxury bedsheet, hotel bedding, oversized bedsheet', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(54, 6, 'TEX-BED-054', 'Printed Bedsheet', 'printed-bedsheet-double', 'Double printed bedsheet', 'Colorful printed cotton bedsheet for double bed', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 960.00, 720.00, NULL, 1350.00, 'both', 1, NULL, 10, 0, 180, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'printed-bedsheet-1.jpg', NULL, NULL, 'Buy Printed Double Bedsheet Online | Colorful Cotton Bedding', 'Shop colorful printed cotton bedsheet for double bed. Beautiful traditional and modern prints. Soft fabric adds style to bedroom. Perfect for home decor. Bulk wholesale price.', 'printed bedsheet, double printed sheet, cotton printed bedding, colorful bedsheet, traditional print sheet, floral bedsheet, designer bedsheet, bed linen print, ethnic bedding', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL);
INSERT INTO `products` (`id`, `sub_category_id`, `product_code`, `name`, `slug`, `short_description`, `description`, `product_type`, `has_variants`, `size`, `weight`, `dimensions`, `material`, `color`, `base_retail_price`, `base_wholesale_price`, `cost_price`, `mrp`, `selling_mode`, `min_order_quantity`, `max_order_quantity`, `bulk_min_quantity`, `is_bulk_only`, `stock_quantity`, `low_stock_threshold`, `track_inventory`, `allow_backorder`, `shipping_class`, `shipping_weight`, `free_shipping`, `tax_class`, `gst_rate`, `main_image`, `hover_image`, `video_url`, `meta_title`, `meta_description`, `meta_keywords`, `is_active`, `is_featured`, `is_new`, `is_on_sale`, `is_trending`, `is_bulk_item`, `bulk_pricing_model`, `has_tiered_pricing`, `total_sold`, `total_revenue`, `average_rating`, `review_count`, `view_count`, `created_at`, `updated_at`, `canonical_url`, `schema_markup`, `search_keywords`) VALUES
(55, 6, 'TEX-BED-055', 'Luxury Bedsheet', 'luxury-bedsheet-king', 'Luxury king size bedsheet', 'Premium quality luxury bedsheet with embroidery, king size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1900.00, 1400.00, NULL, 2600.00, 'both', 1, NULL, 10, 0, 120, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'luxury-bedsheet-1.jpg', NULL, NULL, 'Buy Luxury King Size Bedsheet Online | Premium Embroidered Sheet', 'Shop premium quality luxury king size bedsheet with embroidery. Soft and elegant design for master bedroom. Perfect for special occasions and gifting. Bulk wholesale price available.', 'luxury bedsheet, king size luxury, embroidered sheet, premium bedding, designer bedsheet, fancy bedsheet, wedding bedsheet, high thread count, luxury linen, embroidered bedding', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(56, 6, 'TEX-COM-056', 'Comforter', 'comforter-double', 'Double bed comforter', 'Soft and warm comforter for double bed with microfiber filling', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 2500.00, 1900.00, NULL, 3500.00, 'both', 1, NULL, 10, 0, 80, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'comforter-1.jpg', NULL, NULL, 'Buy Double Bed Comforter Online | Soft Microfiber Filling', 'Shop soft and warm comforter for double bed with microfiber filling. Lightweight yet cozy for comfortable sleep. Perfect for winter and air-conditioned rooms. Bulk wholesale price.', 'comforter double, bed comforter, duvet, quilted comforter, soft blanket, winter bedding, microfiber comforter, warm bed cover, cozy blanket, double bed comforter, bedding set', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(57, 6, 'TEX-QUI-057', 'Quilt (Rajai)', 'quilt-double', 'Double bed quilt', 'Traditional cotton quilt for winter, double bed size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 3500.00, 2600.00, NULL, 4800.00, 'both', 1, NULL, 10, 0, 70, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'quilt-1.jpg', NULL, NULL, 'Buy Double Bed Quilt Online | Traditional Cotton Rajai', 'Shop traditional cotton quilt (rajai) for double bed. Handcrafted with pure cotton filling for warmth in winter. Lightweight and comfortable. Traditional Indian bedding. Bulk wholesale.', 'quilt double, rajai, cotton quilt, traditional quilt, winter quilt, handcrafted quilt, cotton rajai, warm quilt, double bed quilt, indian bedding, winter bedding, cotton filling', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(58, 6, 'TEX-DOH-058', 'Dohar', 'dohar-double', 'Double bed dohar', 'Lightweight cotton dohar for summer, double bed', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1800.00, 1350.00, NULL, 2500.00, 'both', 1, NULL, 10, 0, 150, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'dohar-1.jpg', NULL, NULL, 'Buy Double Bed Dohar Online | Lightweight Cotton Summer Sheet', 'Shop lightweight cotton dohar for double bed. Perfect for summer use as light blanket or bed cover. Soft and breathable fabric. Traditional Indian textile. Bulk wholesale price available.', 'dohar double, cotton dohar, summer blanket, lightweight blanket, bed cover, cotton sheet, indian dohar, summer bedding, breathable blanket, double dohar, cotton summer sheet', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(59, 6, 'TEX-BLA-059', 'Blanket', 'blanket-single', 'Single winter blanket', 'Warm acrylic blanket for single bed', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1500.00, 1100.00, NULL, 2100.00, 'both', 1, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'blanket-single-1.jpg', NULL, NULL, 'Buy Single Winter Blanket Online | Warm Acrylic Blanket', 'Shop warm acrylic blanket for single bed. Soft and cozy for winter season. Perfect for home, hostel, and travel. Available in multiple colors. Bulk wholesale price for hotels.', 'blanket single, winter blanket, acrylic blanket, warm blanket, soft blanket, single bed blanket, cozy blanket, winter bedding, hotel blanket, fleece blanket, cold weather blanket', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(60, 6, 'TEX-BLA-060', 'Blanket', 'blanket-double', 'Double winter blanket', 'Warm acrylic blanket for double bed', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 2000.00, 1500.00, NULL, 2800.00, 'both', 1, NULL, 10, 0, 180, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'blanket-double-1.jpg', NULL, NULL, 'Buy Double Winter Blanket Online | Warm Acrylic Blanket', 'Shop warm acrylic blanket for double bed. Soft, cozy, and perfect for winter season. Ideal for home and hospitality use. Multiple colors available. Bulk wholesale price.', 'blanket double, winter blanket double, acrylic blanket, warm blanket, soft blanket, double bed blanket, cozy blanket, winter bedding, hotel blanket, fleece blanket, warm cover', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(61, 9, 'TEX-PIL-061', 'Pillow Cover Set', 'pillow-cover-set-2-pcs', 'Set of 2 pillow covers', 'Cotton pillow cover set with decorative prints, standard size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 300.00, 220.00, NULL, 420.00, 'both', 1, NULL, 10, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'pillow-cover-1.jpg', NULL, NULL, 'Buy Pillow Cover Set Online | Set of 2 Cotton Pillow Covers', 'Shop cotton pillow cover set of 2 pieces with decorative prints. Standard size, soft fabric, and elegant designs. Perfect for home decor and gifting. Bulk wholesale price available.', 'pillow cover set, pillow case set, cotton pillow cover, cushion cover, bed pillow covers, decorative pillow covers, bedroom accessories, home textiles, pillow case, bed linen', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(62, 9, 'TEX-CUS-062', 'Cushion Cover', 'cushion-cover-16-inch', '16x16 inch cushion cover', 'Cotton cushion cover with traditional print, 16x16 inch', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 230.00, 170.00, NULL, 320.00, 'both', 1, NULL, 10, 0, 600, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'cushion-16-1.jpg', NULL, NULL, 'Buy 16x16 Cotton Cushion Cover Online | Decorative Pillow Case', 'Shop 16x16 inch cotton cushion cover with traditional print. Perfect for sofa, bed, or chair decor. Soft fabric and vibrant colors. Handcrafted designs. Bulk wholesale price.', 'cushion cover 16x16, cushion case, decorative pillow cover, cotton cushion cover, sofa cushion cover, throw pillow cover, home decor, ethnic cushion cover, printed cushion', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(63, 9, 'TEX-CUS-063', 'Cushion Cover', 'cushion-cover-18-inch', '18x18 inch cushion cover', 'Cotton cushion cover with embroidery, 18x18 inch', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 260.00, 190.00, NULL, 360.00, 'both', 1, NULL, 10, 0, 550, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'cushion-18-1.jpg', NULL, NULL, 'Buy 18x18 Cotton Cushion Cover Online | Embroidered Pillow Case', 'Shop 18x18 inch cotton cushion cover with embroidery. Large size perfect for sofa and bed decor. Elegant designs add style to living room. Handcrafted by artisans. Bulk wholesale.', 'cushion cover 18x18, large cushion cover, embroidered cushion, decorative pillow, cotton cushion case, sofa decor, embroidered pillow cover, ethnic cushion, home accent', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(64, 9, 'TEX-CUR-064', 'Curtain Set', 'curtain-set-5-feet', '5 feet curtain set of 2 panels', 'Ready-made cotton curtain set, 5 feet length, 2 panels', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1150.00, 850.00, NULL, 1600.00, 'both', 1, NULL, 10, 0, 120, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'curtain-5ft-1.jpg', NULL, NULL, 'Buy 5 Feet Curtain Set Online | Ready-made Cotton Curtains', 'Shop ready-made cotton curtain set of 2 panels, 5 feet length. Perfect for windows, doors, and room dividers. Available in multiple colors and designs. Bulk wholesale price.', 'curtain set 5 feet, cotton curtains, ready-made curtains, window curtains, 5ft curtains, door curtains, home decor curtains, fabric curtains, room dividers, window treatments', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(65, 9, 'TEX-CUR-065', 'Curtain Set', 'curtain-set-7-feet', '7 feet curtain set of 2 panels', 'Ready-made cotton curtain set, 7 feet length, 2 panels', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1600.00, 1200.00, NULL, 2200.00, 'both', 1, NULL, 10, 0, 100, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'curtain-7ft-1.jpg', NULL, NULL, 'Buy 7 Feet Curtain Set Online | Floor Length Cotton Curtains', 'Shop ready-made cotton curtain set of 2 panels, 7 feet length. Floor length curtains for elegant window decor. Perfect for living room and bedroom. Bulk wholesale price available.', 'curtain set 7 feet, floor length curtains, long curtains, cotton curtains, 7ft curtains, window drapes, home decor, living room curtains, bedroom curtains, designer curtains', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(66, 9, 'TEX-CUR-066', 'Blackout Curtain', 'blackout-curtain-7-feet', '7 feet blackout curtain set', 'Thermal blackout curtains for better sleep, 7 feet', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 2200.00, 1600.00, NULL, 3000.00, 'both', 1, NULL, 10, 0, 80, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'blackout-curtain-1.jpg', NULL, NULL, 'Buy 7 Feet Blackout Curtain Online | Thermal Insulated Drapes', 'Shop thermal blackout curtains for better sleep, 7 feet length. Blocks light, reduces noise, and insulates room. Perfect for bedroom and nursery. Bulk wholesale price for hotels.', 'blackout curtain, thermal curtain, room darkening curtain, 7 feet blackout, insulated curtain, bedroom curtain, light blocking curtain, energy saving curtain, hotel curtain', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(67, 9, 'TEX-TBL-067', 'Table Cloth', 'table-cloth-4x6-feet', '4x6 feet table cloth', 'Cotton table cloth for dining table, 4x6 feet', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 600.00, 450.00, NULL, 850.00, 'both', 1, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'table-cloth-1.jpg', NULL, NULL, 'Buy Cotton Table Cloth Online | 4x6 Feet Dining Table Cover', 'Shop cotton table cloth for dining table, 4x6 feet size. Beautiful prints and colors protect your table and add style. Washable and durable. Bulk wholesale price for restaurants.', 'table cloth, cotton table cover, dining table cloth, 4x6 table cloth, table linen, printed table cover, home textiles, kitchen linens, restaurant table cloth, table protector', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(68, 9, 'TEX-TBL-068', 'Table Runner', 'table-runner-standard', 'Standard table runner', 'Cotton table runner with traditional design', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 340.00, 250.00, NULL, 480.00, 'both', 1, NULL, 10, 0, 300, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'table-runner-1.jpg', NULL, NULL, 'Buy Cotton Table Runner Online | Standard Size Dining Runner', 'Shop cotton table runner with traditional design. Standard size for dining table center. Adds elegance to table setting. Perfect for home and restaurants. Bulk wholesale price.', 'table runner, cotton runner, dining table runner, table linen, table centerpiece, decorative runner, indian runner, embroidered runner, home decor, table accessory', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(69, 9, 'TEX-SOF-069', 'Sofa Cover', 'sofa-cover-5-seater', '5 seater sofa cover', 'Stretchable sofa cover for 5 seater sofa', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 3400.00, 2500.00, NULL, 4700.00, 'both', 1, NULL, 10, 0, 60, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'sofa-cover-1.jpg', NULL, NULL, 'Buy 5 Seater Sofa Cover Online | Stretchable Furniture Cover', 'Shop stretchable sofa cover for 5 seater sofa. Protects furniture from dust, stains, and wear. Elastic fitting for snug look. Multiple colors available. Bulk wholesale price.', 'sofa cover, 5 seater sofa cover, furniture cover, stretchable sofa cover, sofa protector, couch cover, slipcover, furniture protector, living room cover, sofa slipcover', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(70, 9, 'TEX-CAR-070', 'Carpet', 'carpet-5x7-feet', '5x7 feet carpet', 'Soft and durable carpet for living room, 5x7 feet', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 4300.00, 3200.00, NULL, 6000.00, 'both', 1, NULL, 10, 0, 40, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'carpet-1.jpg', NULL, NULL, 'Buy 5x7 Feet Carpet Online | Soft Durable Living Room Carpet', 'Shop soft and durable carpet for living room, 5x7 feet size. Beautiful designs add warmth to your floor. Perfect for home and office. Bulk wholesale price available.', 'carpet 5x7, living room carpet, area rug, soft carpet, printed carpet, floor covering, home decor carpet, bedroom carpet, durable carpet, indian carpet, handwoven carpet', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(71, 9, 'TEX-RUG-071', 'Rug', 'rug-3x5-feet', '3x5 feet rug', 'Decorative rug for bedroom or living room, 3x5 feet', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1900.00, 1400.00, NULL, 2600.00, 'both', 1, NULL, 10, 0, 90, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'rug-1.jpg', NULL, NULL, 'Buy 3x5 Feet Rug Online | Decorative Accent Rug', 'Shop decorative rug for bedroom or living room, 3x5 feet size. Soft and stylish accent rug for floor decor. Perfect for bedside, doorway, or seating area. Bulk wholesale price.', 'rug 3x5, accent rug, area rug, bedroom rug, small rug, decorative rug, floor rug, soft rug, printed rug, doormat, entry rug, home decor rug, cotton rug, handwoven rug', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:53', NULL, NULL, NULL),
(72, 10, 'TEX-FAB-072', 'Cotton Fabric', 'cotton-fabric-per-meter', 'Cotton fabric per meter', 'Pure cotton fabric, 44 inch width, various colors', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 200.00, 150.00, NULL, 280.00, 'both', 5, NULL, 10, 0, 1000, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'cotton-fabric-1.jpg', NULL, NULL, 'Buy Cotton Fabric Online Per Meter | Pure Cotton 44 Inch Width', 'Shop pure cotton fabric per meter, 44 inch width. Available in multiple colors and prints. Perfect for dressmaking, quilting, and crafts. Wholesale price for bulk orders.', 'cotton fabric, fabric per meter, pure cotton cloth, cotton textile, dress material, quilting fabric, craft fabric, cotton cloth online, fabric by meter, unstitched fabric', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(73, 10, 'TEX-FAB-073', 'Silk Fabric', 'silk-fabric-per-meter', 'Silk fabric per meter', 'Pure silk fabric, 44 inch width, various colors', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1550.00, 1150.00, NULL, 2100.00, 'both', 3, NULL, 10, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'silk-fabric-1.jpg', NULL, NULL, 'Buy Pure Silk Fabric Online Per Meter | 44 Inch Width', 'Shop pure silk fabric per meter, 44 inch width. Luxurious silk for sarees, suits, and designer wear. Available in various colors and finishes. Bulk wholesale price available.', 'silk fabric, pure silk cloth, silk textile, fabric per meter, silk material, bridal fabric, designer fabric, silk cloth online, unstitched silk, luxury fabric, party wear fabric', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(74, 10, 'TEX-FAB-074', 'Linen Fabric', 'linen-fabric-per-meter', 'Linen fabric per meter', 'Pure linen fabric, 44 inch width, natural colors', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 800.00, 600.00, NULL, 1100.00, 'both', 3, NULL, 10, 0, 400, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'linen-fabric-1.jpg', NULL, NULL, 'Buy Pure Linen Fabric Online Per Meter | Natural Linen Cloth', 'Shop pure linen fabric per meter, 44 inch width. Natural, breathable, and sustainable fabric for clothing and home textiles. Available in earthy colors. Bulk wholesale price.', 'linen fabric, pure linen cloth, linen textile, natural fabric, breathable fabric, eco-friendly fabric, fabric per meter, linen material, summer fabric, sustainable textile', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(75, 10, 'TEX-FAB-075', 'Denim Fabric', 'denim-fabric-per-meter', 'Denim fabric per meter', 'Cotton denim fabric, 44 inch width, various washes', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 600.00, 450.00, NULL, 850.00, 'both', 5, NULL, 10, 0, 600, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'denim-fabric-1.jpg', NULL, NULL, 'Buy Denim Fabric Online Per Meter | Cotton Denim 44 Inch', 'Shop cotton denim fabric per meter, 44 inch width. Available in various washes and weights. Perfect for jeans, jackets, bags, and crafts. Wholesale price for bulk orders.', 'denim fabric, cotton denim, jean fabric, fabric per meter, denim cloth, jean material, heavyweight denim, light denim, raw denim, craft denim, apparel fabric, textile', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(76, 11, 'TEX-KUR-076', 'Ladies Kurti', 'ladies-kurti-m-xxl', 'Cotton ladies kurti', 'Comfortable cotton kurti with straight cut, sizes M to XXL', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1000.00, 750.00, NULL, 1400.00, 'both', 3, NULL, 10, 0, 300, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'kurti-1.jpg', NULL, NULL, 'Buy Cotton Ladies Kurti Online | Straight Cut Sizes M to XXL', 'Shop comfortable cotton ladies kurti with straight cut. Available in sizes M to XXL. Perfect for daily wear, office, and casual outings. Multiple colors. Bulk wholesale price.', 'ladies kurti, cotton kurti, women kurti, straight cut kurti, casual kurti, daily wear kurti, plus size kurti, indian tunic, ethnic wear, women clothing, kurti for women', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(77, 11, 'TEX-KUR-077', 'Designer Kurti', 'designer-kurti-m-xxl', 'Designer printed kurti', 'Designer printed kurti with embroidery, sizes M to XXL', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1500.00, 1100.00, NULL, 2100.00, 'both', 3, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'designer-kurti-1.jpg', NULL, NULL, 'Buy Designer Printed Kurti Online | Embroidered Women Kurti', 'Shop designer printed kurti with embroidery for women. Available in sizes M to XXL. Perfect for parties, festivals, and special occasions. Stylish ethnic wear. Bulk wholesale price.', 'designer kurti, printed kurti, embroidered kurti, party wear kurti, festive kurti, women ethnic wear, designer tunic, fancy kurti, ladies designer wear, indian dress', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(78, 11, 'TEX-SAR-078', 'Cotton Saree', 'cotton-saree-5-5-meter', 'Cotton saree with border', 'Pure cotton saree with contrast border, 5.5 meters', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1750.00, 1300.00, NULL, 2400.00, 'both', 1, NULL, 10, 0, 150, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'cotton-saree-1.jpg', NULL, NULL, 'Buy Cotton Saree Online | Pure Cotton Saree with Border', 'Shop pure cotton saree with contrast border, 5.5 meters. Comfortable and elegant for daily wear, office, and casual occasions. Handwoven quality. Bulk wholesale price available.', 'cotton saree, pure cotton saree, handloom saree, cotton sari, daily wear saree, casual saree, traditional saree, indian saree, cotton drape, women saree, ethnic saree', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 1, '2026-03-08 14:33:45', '2026-03-22 02:41:00', NULL, NULL, NULL),
(79, 11, 'TEX-SAR-079', 'Silk Saree', 'silk-saree-5-5-meter', 'Pure silk saree', 'Pure silk saree with zari border, 5.5 meters', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 6000.00, 4500.00, NULL, 8500.00, 'both', 1, NULL, 5, 0, 80, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'silk-saree-1.jpg', NULL, NULL, 'Buy Pure Silk Saree Online | Silk Saree with Zari Border', 'Shop pure silk saree with zari border, 5.5 meters. Luxurious and elegant for weddings, parties, and festive occasions. Traditional Indian weaves. Bulk wholesale price.', 'silk saree, pure silk saree, banarasi silk, kanchipuram saree, wedding saree, party wear saree, bridal saree, traditional silk, zari border saree, luxury saree, indian silk', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(80, 11, 'TEX-SAR-080', 'Designer Saree', 'designer-saree-5-5-meter', 'Designer saree with work', 'Designer saree with embroidery and sequin work', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 4300.00, 3200.00, NULL, 6000.00, 'both', 1, NULL, 5, 0, 60, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'designer-saree-1.jpg', NULL, NULL, 'Buy Designer Saree Online | Embroidered Party Wear Saree', 'Shop designer saree with embroidery and sequin work, 5.5 meters. Perfect for weddings, parties, and special events. Elegant and stylish. Bulk wholesale price available.', 'designer saree, embroidered saree, party wear saree, bridesmaid saree, festive saree, fancy saree, sequin saree, wedding guest saree, indian designer wear, couture saree', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:19:18', NULL, NULL, NULL),
(81, 12, 'TEX-SHT-081', 'Men\'s Shirt', 'mens-shirt-m-xxl', 'Cotton men\'s shirt', 'Regular fit cotton shirt for men, full sleeves', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 900.00, 650.00, NULL, 1250.00, 'both', 3, NULL, 10, 0, 250, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'mens-shirt-1.jpg', NULL, NULL, 'Buy Cotton Men\'s Shirt Online | Regular Fit Full Sleeves', 'Shop comfortable cotton men\'s shirt with full sleeves. Regular fit, available in sizes M to XXL. Perfect for casual and office wear. Multiple colors. Bulk wholesale price.', 'men shirt, cotton shirt, mens casual shirt, full sleeve shirt, regular fit shirt, office shirt, formal shirt, men clothing, cotton apparel, menswear, shirt for men', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(82, 12, 'TEX-SHT-082', 'Formal Shirt', 'formal-shirt-m-xxl', 'Formal men\'s shirt', 'Premium formal shirt for office wear', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 1100.00, 800.00, NULL, 1500.00, 'both', 3, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'formal-shirt-1.jpg', NULL, NULL, 'Buy Formal Men\'s Shirt Online | Premium Office Wear', 'Shop premium formal shirt for office wear. Available in sizes M to XXL. Crisp and professional look with comfortable fabric. Perfect for business meetings. Bulk wholesale price.', 'formal shirt, men formal shirt, office shirt, business shirt, professional wear, dress shirt, executive shirt, corporate wear, men formal wear, office attire, cotton formal', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(83, 12, 'TEX-TEE-083', 'T-Shirt', 't-shirt-m-xxl', 'Cotton t-shirt', 'Comfortable cotton t-shirt, round neck', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 500.00, 350.00, NULL, 700.00, 'both', 5, NULL, 20, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'tshirt-1.jpg', NULL, NULL, 'Buy Cotton T-Shirt Online | Round Neck Men\'s T-Shirt', 'Shop comfortable cotton t-shirt with round neck. Available in sizes M to XXL. Perfect for daily wear, gym, and casual outings. Multiple colors. Bulk wholesale price.', 't-shirt, cotton tshirt, men t-shirt, round neck tshirt, casual tshirt, daily wear tshirt, plain tshirt, basic tee, men casual wear, cotton tee, summer clothing, gym wear', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(84, 12, 'TEX-TEE-084', 'Polo T-Shirt', 'polo-t-shirt-m-xxl', 'Polo t-shirt', 'Polo collar t-shirt with half sleeves', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 600.00, 420.00, NULL, 850.00, 'both', 5, NULL, 20, 0, 400, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'polo-tshirt-1.jpg', NULL, NULL, 'Buy Polo T-Shirt Online | Cotton Half Sleeves Polo Collar', 'Shop polo collar t-shirt with half sleeves. Available in sizes M to XXL. Smart casual look for outings, sports, and semi-formal occasions. Bulk wholesale price available.', 'polo tshirt, polo shirt, cotton polo, half sleeve polo, men polo, casual polo, smart casual, sportswear, golf shirt, tennis shirt, collared tshirt, men polo tee', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(85, 13, 'TEX-SHA-085', 'Wool Shawl', 'wool-shawl-free-size', 'Warm wool shawl', 'Pure wool shawl for winter, free size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 2600.00, 1900.00, NULL, 3600.00, 'both', 1, NULL, 10, 0, 120, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'wool-shawl-1.jpg', NULL, NULL, 'Buy Pure Wool Shawl Online | Warm Winter Shawl Free Size', 'Shop pure wool shawl for winter, free size. Soft and warm for cold weather. Perfect for men and women. Ideal for travel, evening walks, and gifting. Bulk wholesale price.', 'wool shawl, pure wool shawl, winter shawl, warm shawl, unisex shawl, wool wrap, winter accessory, cold weather wear, men shawl, women shawl, woolen stole, pashmina alternative', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:54', NULL, NULL, NULL),
(86, 13, 'TEX-SHA-086', 'Pashmina Shawl', 'pashmina-shawl-free-size', 'Premium pashmina shawl', 'Soft pashmina shawl with embroidery, free size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 4700.00, 3500.00, NULL, 6500.00, 'both', 1, NULL, 5, 0, 60, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'pashmina-shawl-1.jpg', NULL, NULL, 'Buy Premium Pashmina Shawl Online | Embroidered Cashmere Wrap', 'Shop soft pashmina shawl with embroidery, free size. Premium quality cashmere blend for luxury warmth. Perfect for special occasions and gifting. Bulk wholesale price available.', 'pashmina shawl, cashmere shawl, embroidered shawl, luxury shawl, premium wrap, wedding shawl, party shawl, pashmina wrap, indian pashmina, kashmiri shawl, handmade shawl', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:54', NULL, NULL, NULL),
(87, 13, 'TEX-BAB-087', 'Baby Blanket', 'baby-blanket-small', 'Soft baby blanket', 'Soft cotton blanket for babies, small size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 600.00, 450.00, NULL, 850.00, 'both', 1, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'baby-blanket-1.jpg', NULL, NULL, 'Buy Soft Baby Blanket Online | Cotton Infant Blanket Small', 'Shop soft cotton baby blanket for infants, small size. Gentle on sensitive skin, perfect for swaddling, stroller, and crib. Safe and comfortable. Bulk wholesale price available.', 'baby blanket, infant blanket, soft baby wrap, cotton baby blanket, newborn blanket, swaddle blanket, crib blanket, baby essentials, nursery bedding, baby shower gift', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:54', NULL, NULL, NULL),
(88, 7, 'TEX-TOW-088', 'Cotton Towel', 'cotton-towel-medium', 'Medium cotton bath towel', 'Soft cotton bath towel, medium size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 400.00, 300.00, NULL, 550.00, 'both', 5, NULL, 20, 0, 400, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'towel-medium-1.jpg', NULL, NULL, 'Buy Medium Cotton Bath Towel Online | Soft Absorbent Towel', 'Shop soft cotton bath towel, medium size. Highly absorbent and quick-drying for daily use. Perfect for bathroom, gym, and travel. Bulk wholesale price for hotels and spas.', 'cotton towel, bath towel medium, soft towel, absorbent towel, bathroom linen, gym towel, hotel towel, cotton bath linen, quick dry towel, face towel, hand towel, spa towel', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:54', NULL, NULL, NULL),
(89, 7, 'TEX-TOW-089', 'Bath Towel', 'bath-towel-large', 'Large bath towel', 'Premium large bath towel, highly absorbent', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 900.00, 650.00, NULL, 1250.00, 'both', 5, NULL, 20, 0, 300, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'towel-large-1.jpg', NULL, NULL, 'Buy Large Bath Towel Online | Premium Cotton Luxury Towel', 'Shop premium large bath towel, highly absorbent and soft. Luxurious feel for spa-like experience at home. Perfect for bathroom and pool. Bulk wholesale price available.', 'bath towel large, luxury towel, premium cotton towel, oversized towel, bath sheet, spa towel, hotel quality towel, soft large towel, absorbent bath towel, bathroom linen', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:54', NULL, NULL, NULL),
(90, 8, 'TEX-KIT-090', 'Kitchen Towel', 'kitchen-towel-small', 'Small kitchen towel set', 'Set of 3 kitchen towels, highly absorbent', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 160.00, 120.00, NULL, 220.00, 'both', 10, NULL, 25, 0, 600, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'kitchen-towel-1.jpg', NULL, NULL, 'Buy Kitchen Towel Set Online | Set of 3 Cotton Towels', 'Shop set of 3 cotton kitchen towels, highly absorbent and durable. Perfect for drying dishes, hands, and kitchen surfaces. Easy to wash and reuse. Bulk wholesale price.', 'kitchen towel set, dish towel, cotton kitchen towel, drying towel, kitchen linen, tea towel, kitchen cloth, absorbent towel, cooking accessories, home kitchen essentials', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:54', NULL, NULL, NULL),
(91, 8, 'TEX-APR-091', 'Apron', 'apron-free-size', 'Cotton kitchen apron', 'Cotton apron with pocket, adjustable neck', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 300.00, 220.00, NULL, 420.00, 'both', 5, NULL, 20, 0, 250, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'apron-1.jpg', NULL, NULL, 'Buy Cotton Kitchen Apron Online | Adjustable Cooking Apron', 'Shop cotton kitchen apron with pocket, adjustable neck strap. Protects clothes while cooking, baking, or gardening. Unisex design. Bulk wholesale price for restaurants.', 'apron, kitchen apron, cooking apron, cotton apron, chef apron, adjustable apron, apron with pocket, kitchen wear, cooking accessory, restaurant apron, gardening apron', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-08 14:41:54', NULL, NULL, NULL),
(92, 8, 'TEX-NAP-092', 'Table Napkin Set', 'table-napkin-set-6-pcs', 'Set of 6 table napkins', 'Cotton table napkin set of 6, with storage pouch', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 400.00, 300.00, NULL, 550.00, 'both', 5, NULL, 20, 0, 350, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'napkin-1.jpg', NULL, NULL, 'Buy Table Napkin Set Online | Set of 6 Cotton Napkins', 'Shop cotton table napkin set of 6 pieces with storage pouch. Perfect for dining table, parties, and special occasions. Elegant and reusable. Bulk wholesale price for restaurants.', 'table napkin set, cloth napkin, cotton napkin, dinner napkin, party napkin, reusable napkin, table linen, dining accessories, restaurant napkin, fabric napkin, eco-friendly', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 2, '2026-03-08 14:33:45', '2026-03-22 02:57:59', NULL, NULL, NULL),
(93, 14, 'TEX-DUP-093', 'Dupatta', 'dupatta-2-5-meter', 'Cotton dupatta', 'Cotton dupatta with tassels, 2.5 meters', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 900.00, 650.00, NULL, 1250.00, 'both', 3, NULL, 10, 0, 180, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'dupatta-1.jpg', NULL, NULL, 'Buy Cotton Dupatta Online | 2.5 Meter Ethnic Scarf', 'Shop cotton dupatta with tassels, 2.5 meters. Perfect complement for kurtis, suits, and ethnic wear. Available in multiple colors and prints. Bulk wholesale price available.', 'cotton dupatta, dupatta online, ethnic scarf, ladies dupatta, printed dupatta, traditional scarf, suit dupatta, kurti accessory, indian dupatta, women scarf, chunni, odhni', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(94, 14, 'TEX-STO-094', 'Stole', 'stole-standard', 'Cotton stole', 'Cotton stole with printed design, standard size', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 600.00, 450.00, NULL, 850.00, 'both', 3, NULL, 10, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 12.00, 'stole-1.jpg', NULL, NULL, 'Buy Cotton Stole Online | Printed Scarf Standard Size', 'Shop cotton stole with printed design, standard size. Versatile accessory for both casual and formal wear. Perfect for office, travel, and everyday use. Bulk wholesale price.', 'cotton stole, printed stole, women stole, scarf, wrap, shawl alternative, lightweight stole, summer scarf, office scarf, fashion accessory, ladies stole, unisex scarf', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:33:45', '2026-03-20 17:22:04', NULL, NULL, NULL),
(95, 15, 'JUT-BAG-095', 'Jute Shopping Bag', 'jute-shopping-bag-medium', 'Medium jute shopping bag', 'Eco-friendly jute shopping bag with long handles, medium size', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 160.00, 120.00, NULL, 220.00, 'both', 10, NULL, 25, 0, 1000, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-bag-medium-1.jpg', NULL, NULL, 'Buy Medium Jute Shopping Bag Online | Eco-friendly Tote', 'Shop eco-friendly jute shopping bag with long handles. Medium size perfect for grocery shopping and daily use. Sustainable alternative to plastic bags. Bulk wholesale price available.', 'jute shopping bag, eco-friendly bag, jute tote, reusable shopping bag, grocery bag, sustainable bag, jute carry bag, natural fiber bag, green bag, environment friendly bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(96, 15, 'JUT-BAG-096', 'Jute Shopping Bag', 'jute-shopping-bag-large', 'Large jute shopping bag', 'Large jute bag for heavy shopping, reinforced handles', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 220.00, 160.00, NULL, 300.00, 'both', 10, NULL, 25, 0, 800, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-bag-large-1.jpg', NULL, NULL, 'Buy Large Jute Shopping Bag Online | Heavy Duty Grocery Bag', 'Shop large jute bag for heavy shopping with reinforced handles. Strong and durable for carrying groceries, books, and more. Eco-friendly alternative. Bulk wholesale price.', 'large jute bag, heavy duty jute bag, jute grocery bag, large shopping bag, reusable grocery bag, strong jute bag, eco shopping bag, jute carryall, farmers market bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(97, 15, 'JUT-BAG-097', 'Printed Jute Bag', 'printed-jute-bag-medium', 'Printed jute shopping bag', 'Jute bag with colorful print, medium size', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 200.00, 150.00, NULL, 280.00, 'both', 10, NULL, 25, 0, 600, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-printed-1.jpg', NULL, NULL, 'Buy Printed Jute Bag Online | Colorful Eco-friendly Tote', 'Shop jute bag with colorful print, medium size. Stylish and sustainable for shopping, college, and outings. Unique designs make a fashion statement. Bulk wholesale price.', 'printed jute bag, colorful jute bag, designer jute bag, jute tote bag, printed eco bag, jute shopping bag, custom print bag, sustainable fashion, jute accessory, jute craft', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(98, 15, 'JUT-BAG-098', 'Jute Grocery Bag', 'jute-grocery-bag-large', 'Large jute grocery bag', 'Sturdy jute bag for grocery shopping, large size', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 240.00, 180.00, NULL, 330.00, 'both', 10, NULL, 25, 0, 700, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-grocery-1.jpg', NULL, NULL, 'Buy Large Jute Grocery Bag Online | Sturdy Shopping Carrier', 'Shop sturdy jute bag for grocery shopping, large size. Perfect for carrying vegetables, fruits, and other items. Strong handles and durable construction. Bulk wholesale price.', 'jute grocery bag, grocery jute bag, large grocery bag, vegetable bag, shopping carrier, farmers market bag, eco grocery bag, reusable produce bag, jute shopping carrier', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(99, 15, 'JUT-BAG-099', 'Jute Tote Bag', 'jute-tote-bag-standard', 'Standard jute tote bag', 'Fashionable jute tote bag for daily use', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 190.00, 140.00, NULL, 260.00, 'both', 10, NULL, 25, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-tote-1.jpg', NULL, NULL, 'Buy Jute Tote Bag Online | Standard Fashionable Carrier', 'Shop fashionable jute tote bag for daily use. Standard size perfect for college, work, and casual outings. Eco-friendly and stylish. Bulk wholesale price available.', 'jute tote bag, jute tote, fashion jute bag, everyday jute bag, college jute bag, work jute bag, casual jute tote, eco-friendly tote, sustainable bag, jute handbag alternative', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(100, 15, 'JUT-BAG-100', 'Designer Jute Handbag', 'designer-jute-handbag-medium', 'Designer jute handbag', 'Stylish jute handbag with fabric lining and magnetic closure', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 300.00, 220.00, NULL, 420.00, 'both', 5, NULL, 20, 0, 300, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-handbag-1.jpg', NULL, NULL, 'Buy Designer Jute Handbag Online | Stylish Fabric Lined Bag', 'Shop stylish designer jute handbag with fabric lining and magnetic closure. Perfect for parties, shopping, and special occasions. Sustainable fashion accessory. Bulk wholesale price.', 'designer jute handbag, jute handbag, stylish jute bag, lined jute bag, fashion jute bag, jute purse, eco-friendly handbag, sustainable handbag, jute tote, women jute bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(101, 15, 'JUT-BAG-101', 'Jute Office Bag', 'jute-office-bag-laptop', 'Jute office bag for laptop', 'Professional jute bag with laptop compartment, fits up to 15 inch', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 600.00, 450.00, NULL, 850.00, 'both', 5, NULL, 15, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-office-1.jpg', NULL, NULL, 'Buy Jute Office Bag Online | Professional Laptop Tote', 'Shop professional jute office bag with laptop compartment, fits up to 15 inch. Perfect for work, meetings, and business use. Eco-friendly corporate accessory. Bulk wholesale price.', 'jute office bag, jute laptop bag, professional jute bag, work bag, corporate bag, laptop jute tote, office accessory, eco-friendly office bag, sustainable work bag, meeting bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(102, 15, 'JUT-BAG-102', 'Jute Laptop Bag', 'jute-laptop-bag-15-inch', 'Jute laptop bag 15 inch', 'Padded jute laptop bag with shoulder strap, 15 inch', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 650.00, 480.00, NULL, 900.00, 'both', 5, NULL, 15, 0, 180, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-laptop-1.jpg', NULL, NULL, 'Buy Jute Laptop Bag Online | 15 Inch Padded Sleeve', 'Shop padded jute laptop bag with shoulder strap, fits 15 inch laptop. Protects your device while looking stylish. Eco-friendly alternative to neoprene. Bulk wholesale price available.', 'jute laptop bag, laptop sleeve, padded jute bag, 15 inch laptop bag, jute laptop case, eco laptop bag, sustainable laptop sleeve, laptop carrier, jute computer bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(103, 15, 'JUT-BAG-103', 'Jute Conference Bag', 'jute-conference-bag-large', 'Large jute conference bag', 'Corporate jute bag for conferences and events', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 340.00, 250.00, NULL, 470.00, 'both', 10, NULL, 25, 0, 400, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-conference-1.jpg', NULL, NULL, 'Buy Jute Conference Bag Online | Corporate Event Tote', 'Shop large jute conference bag for corporate events, seminars, and meetings. Perfect for branded giveaways and promotional items. Custom logo available. Bulk wholesale price.', 'jute conference bag, corporate jute bag, event bag, seminar bag, promotional jute bag, branded tote, corporate giveaway, conference swag, jute promotional bag, meeting bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(104, 15, 'JUT-BAG-104', 'Jute Wine Bag', 'jute-wine-bag-single', 'Single jute wine bag', 'Jute wine bottle bag with handle, single bottle', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 120.00, 90.00, NULL, 170.00, 'both', 10, NULL, 30, 0, 800, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-wine-single-1.jpg', NULL, NULL, 'Buy Single Jute Wine Bag Online | Bottle Gift Carrier', 'Shop jute wine bottle bag with handle for single bottle. Perfect for gifting wine, champagne, or spirits. Eco-friendly and reusable. Bulk wholesale price for wineries and gift shops.', 'jute wine bag, wine bottle bag, single wine bag, bottle carrier, gift bag for wine, wine tote, jute bottle bag, reusable wine bag, eco wine bag, winery accessory, alcohol gift', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(105, 15, 'JUT-BAG-105', 'Jute Wine Bag', 'jute-wine-bag-double', 'Double jute wine bag', 'Jute bag for 2 wine bottles with partition', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 180.00, 130.00, NULL, 250.00, 'both', 10, NULL, 30, 0, 600, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-wine-double-1.jpg', NULL, NULL, 'Buy Double Jute Wine Bag Online | Two Bottle Carrier', 'Shop jute bag for 2 wine bottles with partition. Perfect for gifting wine sets or carrying multiple bottles. Eco-friendly and stylish. Bulk wholesale price available.', 'jute wine bag double, two bottle wine bag, double wine carrier, wine bottle tote, gift bag for wine set, jute bottle carrier, wine gift bag, reusable wine bag, eco wine carrier', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(106, 15, 'JUT-BAG-106', 'Jute Lunch Bag', 'jute-lunch-bag-medium', 'Medium jute lunch bag', 'Insulated jute lunch bag with zip closure', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 220.00, 160.00, NULL, 300.00, 'both', 10, NULL, 25, 0, 350, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-lunch-1.jpg', NULL, NULL, 'Buy Insulated Jute Lunch Bag Online | Medium Reusable Lunch Tote', 'Shop insulated jute lunch bag with zip closure. Keeps food fresh and at right temperature. Perfect for office, school, and picnics. Eco-friendly alternative. Bulk wholesale price.', 'jute lunch bag, insulated lunch bag, reusable lunch bag, jute lunch tote, eco-friendly lunch bag, office lunch bag, school lunch bag, picnic bag, food carrier, jute cooler bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(107, 15, 'JUT-BAG-107', 'Jute Backpack', 'jute-backpack-standard', 'Standard jute backpack', 'Jute backpack with drawstring and zip pocket', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 650.00, 480.00, NULL, 900.00, 'both', 5, NULL, 15, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-backpack-1.jpg', NULL, NULL, 'Buy Jute Backpack Online | Standard Eco-friendly Rucksack', 'Shop jute backpack with drawstring and zip pocket. Perfect for college, travel, and daily use. Sustainable and stylish alternative to synthetic backpacks. Bulk wholesale price available.', 'jute backpack, eco backpack, jute rucksack, sustainable backpack, jute school bag, college jute bag, natural fiber backpack, eco-friendly backpack, jute travel bag, boho backpack', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(108, 15, 'JUT-BAG-108', 'Jute School Bag', 'jute-school-bag-large', 'Large jute school bag', 'Jute school bag with multiple compartments', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 700.00, 520.00, NULL, 980.00, 'both', 5, NULL, 15, 0, 250, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-school-1.jpg', NULL, NULL, 'Buy Large Jute School Bag Online | Student Eco-friendly Bag', 'Shop large jute school bag with multiple compartments for students. Perfect for carrying books, notebooks, and supplies. Durable and sustainable. Bulk wholesale price available.', 'jute school bag, school jute bag, student bag, large jute backpack, eco-friendly school bag, sustainable student bag, jute book bag, college jute bag, natural fiber school bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(109, 15, 'JUT-BAG-109', 'Jute Drawstring Bag', 'jute-drawstring-bag-standard', 'Standard jute drawstring bag', 'Simple jute drawstring bag for gifts or storage', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 150.00, 110.00, NULL, 210.00, 'both', 10, NULL, 30, 0, 900, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-drawstring-1.jpg', NULL, NULL, 'Buy Jute Drawstring Bag Online | Standard Gift Pouch', 'Shop simple jute drawstring bag for gifts, favors, and storage. Perfect for packaging small items, jewelry, or as party favors. Eco-friendly and reusable. Bulk wholesale price.', 'jute drawstring bag, jute pouch, drawstring pouch, gift bag, favor bag, jute gift bag, party favor bag, eco-friendly gift wrap, jute packaging, natural fiber pouch, wedding favor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(110, 15, 'JUT-BAG-110', 'Jute Promotional Bag', 'jute-promotional-bag-medium', 'Medium jute promotional bag', 'Customizable jute bag for promotions and events', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 180.00, 130.00, NULL, 250.00, 'both', 10, NULL, 30, 0, 1000, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-promo-1.jpg', NULL, NULL, 'Buy Jute Promotional Bag Online | Customizable Event Bag', 'Shop customizable jute bag for promotions and events. Perfect for branding, corporate gifts, and trade shows. Eco-friendly marketing tool. Bulk wholesale price with custom logo.', 'jute promotional bag, custom jute bag, branded jute bag, promotional tote, event giveaway bag, corporate gift bag, custom logo bag, eco-friendly promotional item, marketing bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(111, 15, 'JUT-BAG-111', 'Jute Beach Bag', 'jute-beach-bag-large', 'Large jute beach bag', 'Spacious jute bag for beach essentials', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 320.00, 240.00, NULL, 450.00, 'both', 10, NULL, 25, 0, 300, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-beach-1.jpg', NULL, NULL, 'Buy Large Jute Beach Bag Online | Summer Vacation Tote', 'Shop spacious jute beach bag for all your summer essentials. Perfect for carrying towels, sunscreen, books, and snacks. Eco-friendly and stylish. Bulk wholesale price available.', 'jute beach bag, beach tote, summer bag, large jute bag, vacation bag, beach accessory, jute summer bag, eco-friendly beach bag, pool bag, jute travel bag, straw bag alternative', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(112, 15, 'JUT-BAG-112', 'Jute Travel Bag', 'jute-travel-bag-large', 'Large jute travel bag', 'Jute travel duffel bag with zip closure', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 750.00, 550.00, NULL, 1050.00, 'both', 5, NULL, 15, 0, 150, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-travel-1.jpg', NULL, NULL, 'Buy Large Jute Travel Bag Online | Eco-friendly Duffel', 'Shop jute travel duffel bag with zip closure. Spacious for weekend trips and getaways. Sustainable alternative to synthetic luggage. Strong and durable. Bulk wholesale price.', 'jute travel bag, jute duffel, weekend bag, eco-friendly luggage, sustainable travel bag, jute carry on, natural fiber duffel, jute overnight bag, vacation bag, travel accessory', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL);
INSERT INTO `products` (`id`, `sub_category_id`, `product_code`, `name`, `slug`, `short_description`, `description`, `product_type`, `has_variants`, `size`, `weight`, `dimensions`, `material`, `color`, `base_retail_price`, `base_wholesale_price`, `cost_price`, `mrp`, `selling_mode`, `min_order_quantity`, `max_order_quantity`, `bulk_min_quantity`, `is_bulk_only`, `stock_quantity`, `low_stock_threshold`, `track_inventory`, `allow_backorder`, `shipping_class`, `shipping_weight`, `free_shipping`, `tax_class`, `gst_rate`, `main_image`, `hover_image`, `video_url`, `meta_title`, `meta_description`, `meta_keywords`, `is_active`, `is_featured`, `is_new`, `is_on_sale`, `is_trending`, `is_bulk_item`, `bulk_pricing_model`, `has_tiered_pricing`, `total_sold`, `total_revenue`, `average_rating`, `review_count`, `view_count`, `created_at`, `updated_at`, `canonical_url`, `schema_markup`, `search_keywords`) VALUES
(113, 15, 'JUT-BAG-113', 'Jute File Folder Bag', 'jute-file-folder-bag-a4', 'A4 jute file folder bag', 'Jute bag designed for A4 files and documents', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 130.00, 95.00, NULL, 180.00, 'both', 10, NULL, 30, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-file-1.jpg', NULL, NULL, 'Buy Jute File Folder Bag Online | A4 Document Carrier', 'Shop jute bag designed for A4 files and documents. Perfect for office, meetings, and carrying paperwork. Professional and eco-friendly. Bulk wholesale price available.', 'jute file folder bag, document bag, A4 jute bag, office file bag, jute document carrier, professional jute bag, meeting bag, eco-friendly office bag, jute briefcase alternative', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(114, 15, 'JUT-BAG-114', 'Jute Gift Bag', 'jute-gift-bag-small', 'Small jute gift bag', 'Small jute bag for gifts and favors', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 95.00, 70.00, NULL, 130.00, 'both', 10, NULL, 40, 0, 1500, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-gift-small-1.jpg', NULL, NULL, 'Buy Small Jute Gift Bag Online | Eco-friendly Favor Bag', 'Shop small jute gift bag for favors, small presents, and party gifts. Perfect for weddings, birthdays, and corporate events. Reusable and sustainable. Bulk wholesale price.', 'jute gift bag small, favor bag, small jute bag, party favor bag, wedding favor bag, eco-friendly gift bag, jute goody bag, sustainable gift wrap, jute packaging, gift pouch', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(115, 15, 'JUT-BAG-115', 'Jute Gift Bag', 'jute-gift-bag-medium', 'Medium jute gift bag', 'Medium jute gift bag with handles', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 130.00, 95.00, NULL, 180.00, 'both', 10, NULL, 40, 0, 1200, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-gift-medium-1.jpg', NULL, NULL, 'Buy Medium Jute Gift Bag Online | Reusable Present Carrier', 'Shop medium jute gift bag with handles. Perfect for presents, gifts, and hampers. Eco-friendly alternative to paper gift bags. Reusable and sustainable. Bulk wholesale price available.', 'jute gift bag medium, jute gift bag, present bag, gift carrier, eco-friendly gift bag, reusable gift bag, jute gift wrap, sustainable packaging, jute tote bag, gift hamper bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(116, 15, 'JUT-BAG-116', 'Jute Gift Bag', 'jute-gift-bag-large', 'Large jute gift bag', 'Large jute gift bag for presents', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 180.00, 130.00, NULL, 250.00, 'both', 10, NULL, 40, 0, 800, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-gift-large-1.jpg', NULL, NULL, 'Buy Large Jute Gift Bag Online | Premium Present Carrier', 'Shop large jute gift bag for big presents and gift hampers. Elegant and eco-friendly packaging solution. Perfect for corporate gifts and special occasions. Bulk wholesale price.', 'jute gift bag large, large jute bag, gift hamper bag, premium gift bag, jute present bag, eco-friendly gift wrap, sustainable packaging, corporate gift bag, jute tote large', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(117, 15, 'JUT-BAG-117', 'Jute Carry Bag with Zip', 'jute-carry-bag-zip-medium', 'Medium jute zip bag', 'Jute bag with zip closure for security', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 260.00, 190.00, NULL, 360.00, 'both', 10, NULL, 25, 0, 400, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-zip-1.jpg', NULL, NULL, 'Buy Jute Zip Bag Online | Medium Secure Carry Bag', 'Shop jute bag with zip closure for security. Medium size perfect for daily use, shopping, and travel. Keeps items safe while being eco-friendly. Bulk wholesale price available.', 'jute zip bag, jute bag with zipper, secure jute bag, medium jute bag, jute carry bag, zippered jute tote, eco-friendly zipper bag, jute shopping bag, jute handbag with zip', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(118, 15, 'JUT-BAG-118', 'Jute Market Bag', 'jute-market-bag-large', 'Large jute market bag', 'Sturdy jute bag for farmers market shopping', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 280.00, 210.00, NULL, 390.00, 'both', 10, NULL, 25, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-market-1.jpg', NULL, NULL, 'Buy Large Jute Market Bag Online | Farmers Market Tote', 'Shop sturdy jute bag for farmers market shopping. Large size perfect for fresh produce, bread, and artisanal goods. Eco-friendly and durable. Bulk wholesale price available.', 'jute market bag, farmers market bag, large jute tote, market shopping bag, produce bag, grocery jute bag, eco-friendly market bag, sustainable shopping bag, jute vegetable bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 2, '2026-03-08 14:34:08', '2026-03-21 16:26:25', NULL, NULL, NULL),
(119, 15, 'JUT-BAG-119', 'Jute Vegetable Bag', 'jute-vegetable-bag-medium', 'Medium jute vegetable bag', 'Mesh style jute bag for vegetables', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 170.00, 125.00, NULL, 240.00, 'both', 10, NULL, 30, 0, 600, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, '1774108125_main_1255.webp', '1774108133_hover_5739.jpg', NULL, 'Buy Jute Vegetable Bag Online | Medium Mesh Produce Bag', 'Shop mesh style jute bag for vegetables and fruits. Medium size allows produce to breathe. Perfect for grocery shopping and farmers market. Eco-friendly alternative. Bulk wholesale price.', 'jute vegetable bag, produce bag, mesh jute bag, vegetable shopping bag, fruit bag, eco-friendly produce bag, grocery jute bag, reusable vegetable bag, farmers market bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 35, '2026-03-08 14:34:08', '2026-03-21 19:11:05', NULL, NULL, NULL),
(120, 16, 'JUT-DEC-120', 'Jute Carpet', 'jute-carpet-4x6-feet', '4x6 feet jute carpet', 'Natural jute carpet for living room, 4x6 feet', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1300.00, 950.00, NULL, 1800.00, 'both', 5, NULL, 15, 0, 100, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-carpet-1.jpg', NULL, NULL, 'Buy Jute Carpet Online | 4x6 Feet Natural Fiber Rug', 'Shop natural jute carpet for living room, 4x6 feet. Eco-friendly, durable, and stylish floor covering. Adds texture and warmth to any space. Bulk wholesale price available.', 'jute carpet, natural fiber carpet, 4x6 jute rug, eco-friendly carpet, living room carpet, jute floor covering, sustainable rug, handwoven jute, boho carpet, natural rug', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(121, 16, 'JUT-DEC-121', 'Jute Rug', 'jute-rug-3x5-feet', '3x5 feet jute rug', 'Handwoven jute rug for bedroom, 3x5 feet', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1000.00, 720.00, NULL, 1400.00, 'both', 5, NULL, 15, 0, 120, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-rug-1.jpg', NULL, NULL, 'Buy Jute Rug Online | 3x5 Feet Handwoven Area Rug', 'Shop handwoven jute rug for bedroom or living room, 3x5 feet. Soft and durable natural fiber rug. Perfect for adding bohemian touch to decor. Bulk wholesale price available.', 'jute rug, handwoven jute rug, area rug, 3x5 jute rug, natural fiber rug, boho rug, eco-friendly rug, bedroom rug, living room rug, jute floor mat, sustainable rug', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(122, 16, 'JUT-DEC-122', 'Jute Door Mat', 'jute-door-mat-standard', 'Standard jute door mat', 'Durable jute door mat for entrance', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 250.00, 180.00, NULL, 350.00, 'both', 10, NULL, 30, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-mat-1.jpg', NULL, NULL, 'Buy Jute Door Mat Online | Standard Natural Entrance Mat', 'Shop durable jute door mat for entrance. Standard size scrapes dirt and adds rustic charm to doorstep. Eco-friendly and long-lasting. Bulk wholesale price available.', 'jute door mat, entrance mat, natural doormat, jute doormat, rustic doormat, eco-friendly mat, coir alternative, doorstep mat, outdoor mat, home entrance decor, jute rug', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(123, 16, 'JUT-DEC-123', 'Jute Floor Mat', 'jute-floor-mat-large', 'Large jute floor mat', 'Large jute mat for floor decoration', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 350.00, 260.00, NULL, 490.00, 'both', 10, NULL, 30, 0, 300, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-floormat-1.jpg', NULL, NULL, 'Buy Large Jute Floor Mat Online | Decorative Area Mat', 'Shop large jute mat for floor decoration. Perfect for living room, bedroom, or hallway. Adds natural texture and style to your home. Bulk wholesale price available.', 'jute floor mat, large jute mat, decorative floor mat, area mat, natural fiber mat, jute rug, floor covering, home decor mat, eco-friendly mat, boho decor, jute carpet', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(124, 16, 'JUT-DEC-124', 'Jute Wall Hanging', 'jute-wall-hanging-medium', 'Medium jute wall hanging', 'Decorative jute wall hanging with tassels', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 470.00, 350.00, NULL, 650.00, 'both', 5, NULL, 20, 0, 150, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-wall-1.jpg', NULL, NULL, 'Buy Jute Wall Hanging Online | Decorative Boho Wall Art', 'Shop decorative jute wall hanging with tassels. Medium size adds bohemian charm to any room. Handcrafted by artisans. Eco-friendly wall decor. Bulk wholesale price available.', 'jute wall hanging, boho wall decor, macrame style, jute wall art, natural wall hanging, fiber wall art, bohemian decor, handcrafted wall hanging, eco-friendly decor, jute tapestry', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(125, 16, 'JUT-DEC-125', 'Jute Wall Art Panel', 'jute-wall-art-panel-large', 'Large jute wall art panel', 'Jute wall art panel with traditional design', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 650.00, 480.00, NULL, 900.00, 'both', 5, NULL, 15, 0, 80, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-art-1.jpg', NULL, NULL, 'Buy Jute Wall Art Panel Online | Large Traditional Design', 'Shop jute wall art panel with traditional design, large size. Statement piece for living room or office. Handcrafted by skilled artisans. Sustainable decor. Bulk wholesale price.', 'jute wall art panel, large jute wall art, traditional jute decor, fiber art panel, jute wall hanging large, eco-friendly wall art, handmade jute panel, sustainable wall decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(126, 16, 'JUT-DEC-126', 'Jute Lamp Shade', 'jute-lamp-shade-medium', 'Medium jute lamp shade', 'Handmade jute lamp shade for table lamp', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 600.00, 420.00, NULL, 850.00, 'both', 5, NULL, 15, 0, 120, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-lamp-1.jpg', NULL, NULL, 'Buy Jute Lamp Shade Online | Medium Handmade Light Cover', 'Shop handmade jute lamp shade for table lamp. Creates warm, diffused light and adds natural texture to your room. Eco-friendly lighting solution. Bulk wholesale price available.', 'jute lamp shade, jute lampshade, handmade lamp cover, natural lamp shade, eco-friendly lighting, boho lamp, jute pendant alternative, fiber lampshade, table lamp decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(127, 16, 'JUT-DEC-127', 'Jute Pendant Light Cover', 'jute-pendant-light-cover', 'Standard jute pendant light cover', 'Jute cover for pendant light fixture', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 600.00, 450.00, NULL, 850.00, 'both', 5, NULL, 15, 0, 90, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-pendant-1.jpg', NULL, NULL, 'Buy Jute Pendant Light Cover Online | Standard Ceiling Fixture', 'Shop jute cover for pendant light fixture. Adds warm, natural glow to dining or living area. Eco-friendly alternative to synthetic shades. Bulk wholesale price available.', 'jute pendant light, pendant light cover, jute ceiling light, hanging lamp shade, natural fiber pendant, eco-friendly lighting, boho pendant light, jute lamp shade, dining light', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(128, 16, 'JUT-DEC-128', 'Jute Table Runner', 'jute-table-runner-standard', 'Standard jute table runner', 'Natural jute table runner for dining table', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 190.00, 140.00, NULL, 260.00, 'both', 10, NULL, 30, 0, 400, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-runner-1.jpg', NULL, NULL, 'Buy Jute Table Runner Online | Standard Natural Dining Runner', 'Shop natural jute table runner for dining table. Adds rustic elegance to your table setting. Perfect for everyday use and special occasions. Bulk wholesale price available.', 'jute table runner, natural table runner, jute dining runner, rustic table decor, eco-friendly table linen, jute centerpiece, boho table decor, fiber runner, sustainable dining', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:20', NULL, NULL, NULL),
(129, 16, 'JUT-DEC-129', 'Jute Table Cloth', 'jute-table-cloth-4x6-feet', '4x6 feet jute table cloth', 'Jute table cloth for outdoor dining', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 520.00, 380.00, NULL, 720.00, 'both', 5, NULL, 20, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-tablecloth-1.jpg', NULL, NULL, 'Buy Jute Table Cloth Online | 4x6 Feet Outdoor Dining Cover', 'Shop jute table cloth for outdoor dining, 4x6 feet. Perfect for patio, garden parties, and picnics. Natural, durable, and easy to clean. Bulk wholesale price available.', 'jute table cloth, outdoor table cover, jute dining cloth, picnic table cover, garden party decor, natural tablecloth, eco-friendly table linen, rustic outdoor dining, patio decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(130, 16, 'JUT-DEC-130', 'Jute Table Mat Set', 'jute-table-mat-set-6-pcs', 'Set of 6 jute table mats', 'Natural jute placemat set of 6 pieces', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 300.00, 220.00, NULL, 420.00, 'both', 10, NULL, 30, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-mat-set-1.jpg', NULL, NULL, 'Buy Jute Table Mat Set Online | Set of 6 Placemats', 'Shop natural jute placemat set of 6 pieces. Protects your table while adding rustic charm. Perfect for daily dining and special occasions. Bulk wholesale price available.', 'jute table mat set, jute placemats, natural placemats, dining mats, table mats set, eco-friendly tableware, rustic dining accessories, jute placemat set, sustainable dining', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(131, 16, 'JUT-DEC-131', 'Jute Cushion Cover', 'jute-cushion-cover-16x16', '16x16 inch jute cushion cover', 'Jute cushion cover with button closure', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 100.00, 75.00, NULL, 140.00, 'both', 10, NULL, 40, 0, 600, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-cushion-16-1.jpg', NULL, NULL, 'Buy 16x16 Jute Cushion Cover Online | Button Closure', 'Shop jute cushion cover with button closure, 16x16 inch. Adds natural texture to sofa or bed decor. Eco-friendly and stylish. Bulk wholesale price available.', 'jute cushion cover, 16x16 jute cover, jute pillow case, natural cushion cover, boho cushion, eco-friendly decor, jute throw pillow, rustic cushion, fiber cushion cover', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(132, 16, 'JUT-DEC-132', 'Jute Cushion Cover', 'jute-cushion-cover-18x18', '18x18 inch jute cushion cover', 'Jute cushion cover with embroidery', 'variable', 1, NULL, NULL, NULL, NULL, NULL, 120.00, 90.00, NULL, 170.00, 'both', 10, NULL, 40, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-cushion-18-1.jpg', NULL, NULL, 'Buy 18x18 Jute Cushion Cover Online | Embroidered Design', 'Shop jute cushion cover with embroidery, 18x18 inch. Large size perfect for sofa and bed decor. Handcrafted with traditional designs. Bulk wholesale price available.', 'jute cushion cover 18x18, embroidered jute cover, large jute cushion, jute pillow case, natural cushion cover, boho decor, ethnic jute cover, handcrafted cushion, fiber art', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 17:22:04', NULL, NULL, NULL),
(133, 16, 'JUT-DEC-133', 'Jute Pouf', 'jute-pouf-standard', 'Standard jute pouf', 'Round jute pouf for seating or footrest', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1150.00, 850.00, NULL, 1600.00, 'both', 5, NULL, 15, 0, 60, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-pouf-1.jpg', NULL, NULL, 'Buy Jute Pouf Online | Standard Round Footstool', 'Shop round jute pouf for seating or footrest. Adds extra seating and natural texture to living room. Handcrafted and sturdy. Eco-friendly furniture. Bulk wholesale price available.', 'jute pouf, jute ottoman, footstool, jute stool, natural fiber pouf, boho seating, eco-friendly furniture, floor cushion, jute cube alternative, living room accent, pouffe', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(134, 16, 'JUT-DEC-134', 'Jute Bean Bag', 'jute-bean-bag-large', 'Large jute bean bag', 'Large jute bean bag with inner filling', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 1600.00, 1200.00, NULL, 2200.00, 'both', 5, NULL, 10, 0, 40, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-beanbag-1.jpg', NULL, NULL, 'Buy Jute Bean Bag Online | Large Eco-friendly Seating', 'Shop large jute bean bag with inner filling. Comfortable and sustainable seating for living room, game room, or dorm. Natural fiber cover. Bulk wholesale price available.', 'jute bean bag, natural fiber bean bag, eco-friendly bean bag, large bean bag, jute sack chair, boho seating, sustainable furniture, bean bag chair, jute lounger, relaxed seating', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(135, 17, 'JUT-STO-135', 'Jute Plant Hanger', 'jute-plant-hanger-medium', 'Medium jute plant hanger', 'Macrame style jute plant hanger for pots', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 115.00, 85.00, NULL, 160.00, 'both', 10, NULL, 40, 0, 800, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-hanger-1.jpg', NULL, NULL, 'Buy Jute Plant Hanger Online | Macrame Style Pot Holder', 'Shop macrame style jute plant hanger for pots. Medium size perfect for hanging indoor plants. Adds bohemian charm to your home. Eco-friendly and handmade. Bulk wholesale price.', 'jute plant hanger, macrame plant hanger, hanging planter, jute pot holder, indoor plant hanger, boho plant decor, diy style hanger, natural fiber hanger, plant accessory', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(136, 17, 'JUT-STO-136', 'Jute Hanging Basket', 'jute-hanging-basket-medium', 'Medium jute hanging basket', 'Jute basket for hanging storage', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 190.00, 140.00, NULL, 260.00, 'both', 10, NULL, 30, 0, 400, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-hanging-1.jpg', NULL, NULL, 'Buy Jute Hanging Basket Online | Medium Storage Basket', 'Shop jute basket for hanging storage. Perfect for organizing small items, plants, or bathroom essentials. Eco-friendly and stylish. Bulk wholesale price available.', 'jute hanging basket, hanging storage, jute wall basket, bathroom organizer, plant basket, jute wall decor, eco-friendly storage, natural fiber basket, hanging organizer', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(137, 17, 'JUT-STO-137', 'Jute Storage Box', 'jute-storage-box-large', 'Large jute storage box', 'Jute box with lid for storage', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 430.00, 320.00, NULL, 600.00, 'both', 5, NULL, 20, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-box-1.jpg', NULL, NULL, 'Buy Large Jute Storage Box Online | Lid Storage Container', 'Shop jute box with lid for storage. Large size perfect for blankets, clothes, toys, or magazines. Eco-friendly and stylish home organization. Bulk wholesale price available.', 'jute storage box, large jute box, jute container with lid, storage basket, eco-friendly storage, natural fiber box, home organization, blanket storage, toy box, jute hamper', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(138, 17, 'JUT-STO-138', 'Jute Organizer Box', 'jute-organizer-box-medium', 'Medium jute organizer box', 'Jute organizer with compartments', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 320.00, 240.00, NULL, 450.00, 'both', 5, NULL, 20, 0, 250, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-organizer-1.jpg', NULL, NULL, 'Buy Medium Jute Organizer Box Online | Compartment Storage', 'Shop jute organizer with compartments for small items. Perfect for desk supplies, cosmetics, or craft materials. Eco-friendly and practical. Bulk wholesale price available.', 'jute organizer box, compartment storage, jute desk organizer, cosmetic organizer, craft storage, eco-friendly organizer, natural fiber box, home organization, jute caddy', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(139, 17, 'JUT-STO-139', 'Jute Magazine Holder', 'jute-magazine-holder-large', 'Large jute magazine holder', 'Jute holder for magazines and books', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 380.00, 280.00, NULL, 530.00, 'both', 5, NULL, 20, 0, 180, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-magazine-1.jpg', NULL, NULL, 'Buy Large Jute Magazine Holder Online | Book and Mail Organizer', 'Shop jute holder for magazines, books, and mail. Large size keeps reading materials tidy and accessible. Eco-friendly home organization. Bulk wholesale price available.', 'jute magazine holder, book organizer, mail holder, magazine rack, jute book stand, eco-friendly organizer, living room storage, reading nook, natural fiber holder, home decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(140, 17, 'JUT-STO-140', 'Jute Bread Basket', 'jute-bread-basket-medium', 'Medium jute bread basket', 'Jute basket for serving bread', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 200.00, 150.00, NULL, 280.00, 'both', 10, NULL, 30, 0, 300, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-bread-1.jpg', NULL, NULL, 'Buy Jute Bread Basket Online | Medium Serving Basket', 'Shop jute basket for serving bread at the table. Perfect for dinner parties and everyday meals. Natural and rustic presentation. Bulk wholesale price available.', 'jute bread basket, bread serving basket, jute bread holder, table serving, rustic kitchenware, eco-friendly serveware, dinner party accessory, jute basket, bread presentation', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(141, 17, 'JUT-STO-141', 'Jute Fruit Basket', 'jute-fruit-basket-large', 'Large jute fruit basket', 'Jute basket for fruit storage', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 300.00, 220.00, NULL, 420.00, 'both', 10, NULL, 30, 0, 250, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-fruit-1.jpg', NULL, NULL, 'Buy Large Jute Fruit Basket Online | Kitchen Fruit Storage', 'Shop jute basket for fruit storage, large size. Keeps fruits fresh and organized in kitchen or dining area. Eco-friendly alternative to plastic. Bulk wholesale price available.', 'jute fruit basket, fruit storage basket, large jute basket, kitchen fruit holder, eco-friendly fruit basket, natural fiber basket, produce basket, countertop organizer', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 13:59:29', NULL, NULL, NULL),
(142, 17, 'JUT-STO-142', 'Jute Laundry Basket', 'jute-laundry-basket-large', 'Large jute laundry basket', 'Jute basket for laundry with handles', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 520.00, 380.00, NULL, 720.00, 'both', 5, NULL, 15, 0, 150, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-laundry-1.jpg', NULL, NULL, 'Buy Large Jute Laundry Basket Online | Eco-friendly Hamper', 'Shop jute basket for laundry with handles. Large size perfect for collecting and carrying laundry. Sustainable alternative to plastic hampers. Bulk wholesale price available.', 'jute laundry basket, laundry hamper, jute clothes basket, large laundry basket, eco-friendly hamper, natural fiber basket, bathroom storage, bedroom organizer, jute hamper', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(143, 17, 'JUT-STO-143', 'Jute Storage Basket', 'jute-storage-basket-medium', 'Medium jute storage basket', 'Versatile jute basket for storage', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 350.00, 260.00, NULL, 490.00, 'both', 5, NULL, 20, 0, 280, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-storage-1.jpg', NULL, NULL, 'Buy Medium Jute Storage Basket Online | Versatile Organizer', 'Shop versatile jute basket for storage. Perfect for toys, blankets, linens, or pantry items. Eco-friendly and stylish home organization. Bulk wholesale price available.', 'jute storage basket, medium jute basket, storage organizer, home organization basket, eco-friendly storage, natural fiber basket, pantry organizer, shelf basket, jute bin', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(144, 18, 'JUT-ACC-144', 'Jute Pouch', 'jute-pouch-small', 'Small jute pouch', 'Small jute pouch with drawstring', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 80.00, 60.00, NULL, 110.00, 'both', 20, NULL, 50, 0, 1500, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-pouch-1.jpg', NULL, NULL, 'Buy Small Jute Pouch Online | Drawstring Gift Pouch', 'Shop small jute pouch with drawstring. Perfect for jewelry, small gifts, or as party favors. Eco-friendly packaging alternative. Bulk wholesale price available.', 'jute pouch, drawstring pouch, small jute bag, gift pouch, favor pouch, eco-friendly packaging, jewelry pouch, party favor bag, jute gift bag, natural fiber pouch, sustainable gift', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(145, 18, 'JUT-ACC-145', 'Jute Cosmetic Bag', 'jute-cosmetic-bag-medium', 'Medium jute cosmetic bag', 'Jute bag for cosmetics with lining', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 130.00, 95.00, NULL, 180.00, 'both', 10, NULL, 40, 0, 600, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-cosmetic-1.jpg', NULL, NULL, 'Buy Jute Cosmetic Bag Online | Medium Lined Makeup Pouch', 'Shop jute bag for cosmetics with lining. Medium size perfect for makeup, toiletries, and travel essentials. Eco-friendly and stylish. Bulk wholesale price available.', 'jute cosmetic bag, makeup pouch, jute toiletry bag, travel cosmetic case, eco-friendly makeup bag, natural fiber pouch, jute vanity bag, sustainable beauty accessory, cosmetic pouch', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(146, 18, 'JUT-ACC-146', 'Jute Travel Pouch', 'jute-travel-pouch-medium', 'Medium jute travel pouch', 'Jute pouch for travel essentials', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 150.00, 110.00, NULL, 210.00, 'both', 10, NULL, 40, 0, 500, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-travelpouch-1.jpg', NULL, NULL, 'Buy Jute Travel Pouch Online | Medium Essentials Carrier', 'Shop jute pouch for travel essentials. Perfect for organizing cables, chargers, toiletries, or documents. Eco-friendly travel accessory. Bulk wholesale price available.', 'jute travel pouch, travel organizer, jute essentials bag, cable organizer, document pouch, eco-friendly travel accessory, natural fiber pouch, sustainable travel, jute kit bag', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(147, 18, 'JUT-ACC-147', 'Jute Diary Cover', 'jute-diary-cover-standard', 'Standard jute diary cover', 'Jute cover for standard diary', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 160.00, 120.00, NULL, 220.00, 'both', 10, NULL, 30, 0, 400, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-diary-1.jpg', NULL, NULL, 'Buy Jute Diary Cover Online | Standard Journal Protector', 'Shop jute cover for standard diary or journal. Protects your notebook with natural, textured style. Perfect for personal or corporate gifts. Bulk wholesale price available.', 'jute diary cover, journal cover, notebook protector, jute book cover, eco-friendly stationery, sustainable gift, natural fiber cover, jute notebook sleeve, personalized diary', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(148, 18, 'JUT-ACC-148', 'Jute File Folder', 'jute-file-folder-a4', 'A4 jute file folder', 'Jute folder for A4 documents', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 115.00, 85.00, NULL, 160.00, 'both', 10, NULL, 40, 0, 700, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-folder-1.jpg', NULL, NULL, 'Buy Jute File Folder Online | A4 Document Organizer', 'Shop jute folder for A4 documents and papers. Perfect for office, school, or presentations. Eco-friendly alternative to plastic folders. Bulk wholesale price available.', 'jute file folder, document folder, A4 jute folder, paper organizer, eco-friendly office supply, sustainable stationery, jute presentation folder, file holder, natural fiber folder', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(149, 18, 'JUT-ACC-149', 'Jute Keychain', 'jute-keychain-standard', 'Standard jute keychain', 'Jute keychain with metal ring', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 35.00, 25.00, NULL, 50.00, 'both', 25, NULL, 100, 0, 5000, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-keychain-1.jpg', NULL, NULL, 'Buy Jute Keychain Online | Standard Natural Fiber Keyring', 'Shop jute keychain with metal ring. Eco-friendly and unique accessory for keys, bags, or as gift. Perfect for promotional giveaways. Bulk wholesale price available.', 'jute keychain, eco-friendly keychain, jute keyring, natural fiber keychain, sustainable accessory, promotional keychain, gift item, jute tassel keychain, handmade keychain', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(150, 18, 'JUT-ACC-150', 'Jute Pen Stand', 'jute-pen-stand-standard', 'Standard jute pen stand', 'Jute pen stand for desk organization', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 100.00, 75.00, NULL, 140.00, 'both', 10, NULL, 40, 0, 800, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-penstand-1.jpg', NULL, NULL, 'Buy Jute Pen Stand Online | Standard Desk Organizer', 'Shop jute pen stand for desk organization. Eco-friendly and stylish holder for pens, pencils, and office supplies. Perfect for home or office. Bulk wholesale price available.', 'jute pen stand, desk organizer, jute pen holder, eco-friendly stationery, sustainable office supply, natural fiber pen stand, desktop accessory, jute pencil holder, office decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(151, 18, 'JUT-ACC-151', 'Jute Coaster Set', 'jute-coaster-set-6-pcs', 'Set of 6 jute coasters', 'Jute coaster set with holder', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 120.00, 90.00, NULL, 170.00, 'both', 10, NULL, 40, 0, 1000, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-coaster-1.jpg', NULL, NULL, 'Buy Jute Coaster Set Online | Set of 6 with Holder', 'Shop jute coaster set of 6 pieces with holder. Protects tables from heat and moisture while adding rustic charm. Eco-friendly and absorbent. Bulk wholesale price available.', 'jute coaster set, drink coasters, jute placemats, table protectors, eco-friendly coasters, natural fiber coasters, rustic drinkware, jute table accessories, sustainable home decor', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(152, 18, 'JUT-ACC-152', 'Jute Gift Hamper Basket', 'jute-gift-hamper-large', 'Large jute gift hamper basket', 'Jute basket for gift hampers', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 600.00, 450.00, NULL, 850.00, 'both', 5, NULL, 15, 0, 200, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-hamper-1.jpg', NULL, NULL, 'Buy Large Jute Gift Hamper Basket Online | Premium Gift Container', 'Shop large jute basket for gift hampers. Perfect for creating beautiful gift presentations for weddings, corporate events, and special occasions. Bulk wholesale price available.', 'jute gift hamper, hamper basket, large jute basket, gift container, wedding hamper, corporate gift basket, eco-friendly gift packaging, jute presentation basket, luxury hamper', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(153, 18, 'JUT-ACC-153', 'Jute Water Bottle Cover', 'jute-bottle-cover-standard', 'Standard jute water bottle cover', 'Jute cover for water bottle with strap', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 110.00, 80.00, NULL, 150.00, 'both', 10, NULL, 40, 0, 600, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-bottlecover-1.jpg', NULL, NULL, 'Buy Jute Water Bottle Cover Online | Standard Bottle Sleeve', 'Shop jute cover for water bottle with strap. Protects bottle and adds style while keeping drinks cool. Eco-friendly accessory for daily use. Bulk wholesale price available.', 'jute bottle cover, water bottle sleeve, jute bottle holder, eco-friendly bottle accessory, reusable bottle cover, natural fiber sleeve, jute bottle bag, sustainable drinkware', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-08 14:44:21', NULL, NULL, NULL),
(154, 18, 'JUT-ACC-154', 'Custom Printed Jute Bag', 'custom-printed-jute-bag-medium', 'Custom printed jute bag', 'Personalized jute bag with custom logo print', 'simple', 0, NULL, NULL, NULL, NULL, NULL, 260.00, 190.00, NULL, 360.00, 'both', 10, NULL, 25, 1, 300, 5, 1, 0, NULL, NULL, 0, NULL, 5.00, 'jute-custom-1.jpg', NULL, NULL, 'Buy Custom Printed Jute Bag Online | Personalized Promotional Tote', 'Shop personalized jute bag with custom logo print. Perfect for corporate gifts, events, and brand promotion. Eco-friendly marketing tool. Bulk wholesale price with customization.', 'custom jute bag, printed jute bag, personalized tote, promotional jute bag, branded bag, corporate gift bag, custom logo bag, eco-friendly promotion, event giveaway, jute printing', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 15:56:01', NULL, NULL, NULL),
(155, 18, 'JUT-ACC-155', 'Jute Export Handbag', 'jute-export-handbag-premium', 'Premium jute export handbag', 'High-quality jute handbag for export quality', 'simple', 0, 'Medium', 12.00, '10x20x30', 'Cottom', 'Red', 700.00, 520.00, NULL, 980.00, 'both', 5, NULL, 15, 1, 120, 5, 1, 0, '', NULL, 0, '', 12.00, 'jute-export-1.jpg', NULL, NULL, 'Buy Premium Jute Export Handbag Online | High-quality Designer Bag', 'Shop high-quality jute handbag for export quality. Premium design with fine finishing, perfect for fashion-conscious customers. Sustainable luxury accessory. Bulk wholesale price available.', 'jute export handbag, premium jute bag, designer jute handbag, high-quality jute bag, luxury jute tote, sustainable fashion, eco-friendly handbag, artisan jute bag, export quality', 1, 0, 0, 0, 0, 1, 'fixed', 0, 0, 0.00, 0.00, 0, 0, '2026-03-08 14:34:08', '2026-03-20 15:53:29', '', NULL, ''),
(156, 19, 'JAM-200-STR', 'Strawberry Jam - 200gm', 'strawberry-jam-200gm', 'Delicious strawberry jam made from fresh strawberries. Perfect for breakfast.', 'Our homemade strawberry jam is made from fresh, ripe strawberries with no artificial preservatives. Perfect for spreading on toast, parathas, or using in desserts.', 'simple', 0, '200gm', 0.20, '7x7x5 cm', 'Glass Jar', 'Red', 85.00, 75.00, 60.00, 99.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 0.25, 0, 'standard', 5.00, NULL, NULL, NULL, 'Strawberry Jam 200gm - Homemade Natural Fruit Jam', 'Buy homemade strawberry jam 200gm. Made with fresh strawberries, no preservatives. Perfect for breakfast.', 'strawberry jam, fruit jam, homemade jam, strawberry preserve', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'strawberry jam, fruit jam, homemade jam, preserve'),
(157, 19, 'JAM-900-STR', 'Strawberry Jam - 900gm', 'strawberry-jam-900gm', 'Delicious strawberry jam made from fresh strawberries. Family pack.', 'Our homemade strawberry jam is made from fresh, ripe strawberries with no artificial preservatives. Perfect for spreading on toast, parathas, or using in desserts. Family size pack.', 'simple', 0, '900gm', 0.90, '12x12x10 cm', 'Glass Jar', 'Red', 285.00, 255.00, 200.00, 349.00, 'both', 1, NULL, 10, 0, 300, 30, 1, 0, 'Standard', 1.00, 0, 'standard', 5.00, NULL, NULL, NULL, 'Strawberry Jam 900gm - Family Pack', 'Buy homemade strawberry jam 900gm family pack. Made with fresh strawberries, no preservatives.', 'strawberry jam, fruit jam, family pack jam, homemade jam', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'strawberry jam, fruit jam, family pack, preserve'),
(158, 19, 'JAM-200-MIX', 'Mixed Fruit Jam - 200gm', 'mixed-fruit-jam-200gm', 'Delicious mixed fruit jam with pineapple, apple, and papaya.', 'Our mixed fruit jam combines the goodness of pineapple, apple, and papaya. Made with natural ingredients and no preservatives.', 'simple', 0, '200gm', 0.20, '7x7x5 cm', 'Glass Jar', 'Golden', 85.00, 75.00, 60.00, 99.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 0.25, 0, 'standard', 5.00, NULL, NULL, NULL, 'Mixed Fruit Jam 200gm - Homemade', 'Buy mixed fruit jam 200gm made with pineapple, apple, and papaya. Natural taste.', 'mixed fruit jam, fruit jam, homemade jam, tropical jam', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'mixed fruit jam, tropical jam, fruit preserve'),
(159, 19, 'JAM-900-MIX', 'Mixed Fruit Jam - 900gm', 'mixed-fruit-jam-900gm', 'Delicious mixed fruit jam - family pack.', 'Our mixed fruit jam combines the goodness of pineapple, apple, and papaya. Made with natural ingredients and no preservatives. Family size pack.', 'simple', 0, '900gm', 0.90, '12x12x10 cm', 'Glass Jar', 'Golden', 285.00, 255.00, 200.00, 349.00, 'both', 1, NULL, 10, 0, 300, 30, 1, 0, 'Standard', 1.00, 0, 'standard', 5.00, NULL, NULL, NULL, 'Mixed Fruit Jam 900gm - Family Pack', 'Buy mixed fruit jam 900gm family pack. Perfect for large families.', 'mixed fruit jam, family pack jam, homemade jam', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 1, '2026-03-20 16:20:40', '2026-03-21 16:39:32', NULL, NULL, 'mixed fruit jam, family pack, fruit preserve'),
(160, 20, 'PICK-1-MAN', 'Mango Pickle - 1kg', 'mango-pickle-1kg', 'Traditional homemade mango pickle with authentic spices.', 'Our mango pickle is made with raw mangoes and a special blend of traditional spices. Aged to perfection for authentic taste.', 'simple', 0, '1kg', 1.00, '15x10x10 cm', 'Glass Jar', 'Yellow', 149.00, 135.00, 100.00, 199.00, 'both', 1, NULL, 10, 0, 400, 40, 1, 0, 'Standard', 1.10, 0, 'standard', 5.00, NULL, NULL, NULL, 'Mango Pickle 1kg - Homemade Traditional', 'Buy traditional mango pickle 1kg. Made with raw mangoes and authentic spices.', 'mango pickle, achar, homemade pickle, mango achar', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'mango pickle, achar, traditional pickle'),
(161, 20, 'PICK-3-MAN', 'Mango Pickle - 3kg', 'mango-pickle-3kg', 'Traditional homemade mango pickle - family pack.', 'Our mango pickle is made with raw mangoes and a special blend of traditional spices. Aged to perfection. Family size pack.', 'simple', 0, '3kg', 3.00, '20x15x15 cm', 'Plastic Jar', 'Yellow', 399.00, 360.00, 280.00, 549.00, 'both', 1, NULL, 10, 0, 200, 20, 1, 0, 'Standard', 3.20, 0, 'standard', 5.00, NULL, NULL, NULL, 'Mango Pickle 3kg - Family Pack', 'Buy mango pickle 3kg family pack. Perfect for large families.', 'mango pickle, family pack pickle, mango achar', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'mango pickle, family pack, achar'),
(162, 20, 'PICK-5-MAN', 'Mango Pickle - 5kg', 'mango-pickle-5kg', 'Traditional homemade mango pickle - bulk pack.', 'Our mango pickle is made with raw mangoes and a special blend of traditional spices. Bulk pack for commercial use.', 'simple', 0, '5kg', 5.00, '25x20x20 cm', 'Plastic Jar', 'Yellow', 649.00, 585.00, 450.00, 899.00, 'both', 1, NULL, 10, 0, 100, 15, 1, 0, 'Standard', 5.30, 0, 'standard', 5.00, NULL, NULL, NULL, 'Mango Pickle 5kg - Bulk Pack', 'Buy mango pickle 5kg bulk pack. Perfect for restaurants and commercial use.', 'mango pickle, bulk pickle, commercial pickle', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'mango pickle, bulk pack, commercial pickle'),
(163, 20, 'PICK-10-MAN', 'Mango Pickle - 10kg', 'mango-pickle-10kg', 'Traditional homemade mango pickle - super bulk pack.', 'Our mango pickle is made with raw mangoes and a special blend of traditional spices. Super bulk pack for wholesale.', 'simple', 0, '10kg', 10.00, '30x25x25 cm', 'Plastic Container', 'Yellow', 1199.00, 1080.00, 850.00, 1699.00, 'both', 1, NULL, 10, 0, 50, 10, 1, 0, 'Standard', 10.50, 0, 'standard', 5.00, NULL, NULL, NULL, 'Mango Pickle 10kg - Super Bulk', 'Buy mango pickle 10kg super bulk pack. Best for wholesale buyers.', 'mango pickle, super bulk, wholesale pickle', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'mango pickle, wholesale, super bulk'),
(164, 21, 'GHEE-1', 'Pure Cow Ghee - 1kg', 'pure-cow-ghee-1kg', 'Premium A2 cow ghee made using traditional bilona method.', 'Our pure cow ghee is made from A2 milk using the traditional bilona method. Rich in nutrients with a natural aroma.', 'simple', 0, '1kg', 1.00, '12x12x8 cm', 'Glass Jar', 'Golden', 699.00, 629.00, 450.00, 899.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 1.10, 0, 'standard', 5.00, NULL, NULL, NULL, 'Pure Cow Ghee 1kg - A2 Desi Ghee', 'Buy pure A2 cow ghee 1kg. Made using traditional bilona method.', 'ghee, cow ghee, desi ghee, A2 ghee, pure ghee', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'ghee, cow ghee, desi ghee, clarified butter'),
(165, 21, 'GHEE-3', 'Pure Cow Ghee - 3kg', 'pure-cow-ghee-3kg', 'Premium A2 cow ghee - family pack.', 'Our pure cow ghee is made from A2 milk using the traditional bilona method. Family pack for regular use.', 'simple', 0, '3kg', 3.00, '20x15x12 cm', 'Plastic Jar', 'Golden', 1999.00, 1799.00, 1300.00, 2599.00, 'both', 1, NULL, 10, 0, 300, 30, 1, 0, 'Standard', 3.20, 0, 'standard', 5.00, NULL, NULL, NULL, 'Pure Cow Ghee 3kg - Family Pack', 'Buy pure A2 cow ghee 3kg family pack. Best value for families.', 'ghee family pack, cow ghee, desi ghee', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'ghee, family pack, cow ghee'),
(166, 21, 'GHEE-5', 'Pure Cow Ghee - 5kg', 'pure-cow-ghee-5kg', 'Premium A2 cow ghee - bulk pack.', 'Our pure cow ghee is made from A2 milk using the traditional bilona method. Bulk pack for commercial use.', 'simple', 0, '5kg', 5.00, '25x20x15 cm', 'Plastic Container', 'Golden', 3299.00, 2969.00, 2100.00, 4299.00, 'both', 1, NULL, 10, 0, 150, 20, 1, 0, 'Standard', 5.30, 0, 'standard', 5.00, NULL, NULL, NULL, 'Pure Cow Ghee 5kg - Bulk Pack', 'Buy pure A2 cow ghee 5kg bulk pack. Perfect for restaurants.', 'ghee bulk pack, cow ghee, commercial ghee', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'ghee, bulk pack, commercial use'),
(167, 21, 'GHEE-10', 'Pure Cow Ghee - 10kg', 'pure-cow-ghee-10kg', 'Premium A2 cow ghee - super bulk pack.', 'Our pure cow ghee is made from A2 milk using the traditional bilona method. Super bulk pack for wholesale.', 'simple', 0, '10kg', 10.00, '35x25x20 cm', 'Plastic Container', 'Golden', 6499.00, 5849.00, 4200.00, 8499.00, 'both', 1, NULL, 10, 0, 80, 15, 1, 0, 'Standard', 10.50, 0, 'standard', 5.00, NULL, NULL, NULL, 'Pure Cow Ghee 10kg - Super Bulk', 'Buy pure A2 cow ghee 10kg super bulk pack. Best for wholesale buyers.', 'ghee wholesale, super bulk ghee, cow ghee', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:40', '2026-03-20 16:20:40', NULL, NULL, 'ghee, wholesale, super bulk'),
(168, 22, 'BUT-500', 'Fresh Butter - 500gm', 'fresh-butter-500gm', 'Creamy fresh butter made from pure cow milk.', 'Our fresh butter is made from pure cow milk using traditional churning method. Rich, creamy, and delicious.', 'simple', 0, '500gm', 0.50, '10x10x5 cm', 'Plastic Container', 'White', 149.00, 134.00, 100.00, 199.00, 'both', 1, NULL, 10, 0, 400, 40, 1, 0, 'Refrigerated', 0.55, 0, 'standard', 5.00, NULL, NULL, NULL, 'Fresh Butter 500gm - Pure Cow Milk', 'Buy fresh butter 500gm made from pure cow milk. Creamy and delicious.', 'butter, fresh butter, white butter, dairy butter', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'butter, fresh butter, dairy butter'),
(169, 22, 'BUT-1', 'Fresh Butter - 1kg', 'fresh-butter-1kg', 'Creamy fresh butter - family pack.', 'Our fresh butter is made from pure cow milk. Family pack for regular use.', 'simple', 0, '1kg', 1.00, '15x10x8 cm', 'Plastic Container', 'White', 279.00, 251.00, 190.00, 379.00, 'both', 1, NULL, 10, 0, 300, 30, 1, 0, 'Refrigerated', 1.10, 0, 'standard', 5.00, NULL, NULL, NULL, 'Fresh Butter 1kg - Family Pack', 'Buy fresh butter 1kg family pack. Perfect for large families.', 'butter family pack, fresh butter, white butter', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'butter, family pack'),
(170, 22, 'BUT-2', 'Fresh Butter - 2kg', 'fresh-butter-2kg', 'Creamy fresh butter - bulk pack.', 'Our fresh butter is made from pure cow milk. Bulk pack for commercial use.', 'simple', 0, '2kg', 2.00, '20x15x10 cm', 'Plastic Container', 'White', 529.00, 476.00, 360.00, 729.00, 'both', 1, NULL, 10, 0, 150, 20, 1, 0, 'Refrigerated', 2.20, 0, 'standard', 5.00, NULL, NULL, NULL, 'Fresh Butter 2kg - Bulk Pack', 'Buy fresh butter 2kg bulk pack. Perfect for restaurants and bakeries.', 'butter bulk pack, fresh butter, commercial butter', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'butter, bulk pack, commercial'),
(171, 23, 'PAN-500', 'Fresh Paneer - 500gm', 'fresh-paneer-500gm', 'Soft and fresh paneer made from pure cow milk.', 'Our paneer is made from pure cow milk, fresh daily. Soft, creamy, and perfect for Indian cuisine.', 'simple', 0, '500gm', 0.50, '12x8x5 cm', 'Plastic Pack', 'White', 149.00, 134.00, 100.00, 199.00, 'both', 1, NULL, 10, 0, 400, 40, 1, 0, 'Refrigerated', 0.55, 0, 'standard', 5.00, NULL, NULL, NULL, 'Fresh Paneer 500gm - Pure Cow Milk', 'Buy fresh paneer 500gm made from pure cow milk. Soft and creamy.', 'paneer, cottage cheese, fresh paneer, Indian cheese', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 2, '2026-03-20 16:20:41', '2026-03-21 18:45:19', NULL, NULL, 'paneer, cottage cheese, fresh paneer'),
(172, 23, 'PAN-1', 'Fresh Paneer - 1kg', 'fresh-paneer-1kg', 'Soft and fresh paneer - family pack.', 'Our paneer is made from pure cow milk. Family pack for regular use.', 'simple', 0, '1kg', 1.00, '15x10x8 cm', 'Plastic Pack', 'White', 279.00, 251.00, 190.00, 379.00, 'both', 1, NULL, 10, 0, 300, 30, 1, 0, 'Refrigerated', 1.10, 0, 'standard', 5.00, NULL, NULL, NULL, 'Fresh Paneer 1kg - Family Pack', 'Buy fresh paneer 1kg family pack. Perfect for large families.', 'paneer family pack, fresh paneer, cottage cheese', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'paneer, family pack');
INSERT INTO `products` (`id`, `sub_category_id`, `product_code`, `name`, `slug`, `short_description`, `description`, `product_type`, `has_variants`, `size`, `weight`, `dimensions`, `material`, `color`, `base_retail_price`, `base_wholesale_price`, `cost_price`, `mrp`, `selling_mode`, `min_order_quantity`, `max_order_quantity`, `bulk_min_quantity`, `is_bulk_only`, `stock_quantity`, `low_stock_threshold`, `track_inventory`, `allow_backorder`, `shipping_class`, `shipping_weight`, `free_shipping`, `tax_class`, `gst_rate`, `main_image`, `hover_image`, `video_url`, `meta_title`, `meta_description`, `meta_keywords`, `is_active`, `is_featured`, `is_new`, `is_on_sale`, `is_trending`, `is_bulk_item`, `bulk_pricing_model`, `has_tiered_pricing`, `total_sold`, `total_revenue`, `average_rating`, `review_count`, `view_count`, `created_at`, `updated_at`, `canonical_url`, `schema_markup`, `search_keywords`) VALUES
(173, 23, 'PAN-2', 'Fresh Paneer - 2kg', 'fresh-paneer-2kg', 'Soft and fresh paneer - bulk pack.', 'Our paneer is made from pure cow milk. Bulk pack for commercial use.', 'simple', 0, '2kg', 2.00, '20x15x10 cm', 'Plastic Pack', 'White', 529.00, 476.00, 360.00, 729.00, 'both', 1, NULL, 10, 0, 150, 20, 1, 0, 'Refrigerated', 2.20, 0, 'standard', 5.00, NULL, NULL, NULL, 'Fresh Paneer 2kg - Bulk Pack', 'Buy fresh paneer 2kg bulk pack. Perfect for restaurants.', 'paneer bulk pack, fresh paneer, commercial paneer', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'paneer, bulk pack, commercial'),
(174, 23, 'PAN-5', 'Fresh Paneer - 5kg', 'fresh-paneer-5kg', 'Soft and fresh paneer - wholesale pack.', 'Our paneer is made from pure cow milk. Wholesale pack for bulk buyers.', 'simple', 0, '5kg', 5.00, '30x20x15 cm', 'Plastic Pack', 'White', 1299.00, 1169.00, 900.00, 1799.00, 'both', 1, NULL, 10, 0, 80, 15, 1, 0, 'Refrigerated', 5.30, 0, 'standard', 5.00, NULL, NULL, NULL, 'Fresh Paneer 5kg - Wholesale Pack', 'Buy fresh paneer 5kg wholesale pack. Best for caterers.', 'paneer wholesale, fresh paneer, bulk paneer', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'paneer, wholesale, bulk'),
(175, 23, 'PAN-10', 'Fresh Paneer - 10kg', 'fresh-paneer-10kg', 'Soft and fresh paneer - super bulk pack.', 'Our paneer is made from pure cow milk. Super bulk pack for wholesale.', 'simple', 0, '10kg', 10.00, '40x30x20 cm', 'Plastic Pack', 'White', 2499.00, 2249.00, 1700.00, 3499.00, 'both', 1, NULL, 10, 0, 50, 10, 1, 0, 'Refrigerated', 10.50, 0, 'standard', 5.00, NULL, NULL, NULL, 'Fresh Paneer 10kg - Super Bulk', 'Buy fresh paneer 10kg super bulk pack. Best for wholesale buyers.', 'paneer super bulk, wholesale paneer, bulk paneer', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'paneer, super bulk, wholesale'),
(176, 24, 'DISH-1L', 'Dishwashing Liquid - 1L', 'dishwashing-liquid-1l', 'Effective dishwashing liquid with lemon freshness.', 'Our dishwashing liquid cuts through grease and leaves dishes sparkling clean. Gentle on hands with lemon freshness.', 'simple', 0, '1L', 1.00, '15x8x8 cm', 'Plastic Bottle', 'Yellow', 99.00, 89.00, 65.00, 149.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 1.10, 0, 'standard', 18.00, NULL, NULL, NULL, 'Dishwashing Liquid 1L - Lemon Fresh', 'Buy dishwashing liquid 1L with lemon freshness. Cuts through grease effectively.', 'dishwash, dishwashing liquid, dish soap, kitchen cleaner', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'dishwash, liquid soap, kitchen cleaner'),
(177, 24, 'DISH-5L', 'Dishwashing Liquid - 5L', 'dishwashing-liquid-5l', 'Effective dishwashing liquid - bulk pack.', 'Our dishwashing liquid cuts through grease and leaves dishes sparkling clean. Bulk pack for commercial use.', 'simple', 0, '5L', 5.00, '25x15x15 cm', 'Plastic Jar', 'Yellow', 399.00, 359.00, 280.00, 549.00, 'both', 1, NULL, 10, 0, 200, 25, 1, 0, 'Standard', 5.20, 0, 'standard', 18.00, NULL, NULL, NULL, 'Dishwashing Liquid 5L - Bulk Pack', 'Buy dishwashing liquid 5L bulk pack. Perfect for restaurants and hotels.', 'dishwash bulk, commercial dishwash, kitchen cleaner bulk', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'dishwash bulk, commercial cleaner'),
(178, 24, 'DISH-10L', 'Dishwashing Liquid - 10L', 'dishwashing-liquid-10l', 'Effective dishwashing liquid - super bulk pack.', 'Our dishwashing liquid cuts through grease and leaves dishes sparkling clean. Super bulk pack for wholesale.', 'simple', 0, '10L', 10.00, '35x20x20 cm', 'Plastic Container', 'Yellow', 749.00, 674.00, 530.00, 1049.00, 'both', 1, NULL, 10, 0, 100, 15, 1, 0, 'Standard', 10.30, 0, 'standard', 18.00, NULL, NULL, NULL, 'Dishwashing Liquid 10L - Super Bulk', 'Buy dishwashing liquid 10L super bulk pack. Best for wholesale buyers.', 'dishwash wholesale, super bulk dishwash, industrial cleaner', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'dishwash wholesale, industrial cleaner'),
(179, 25, 'INC-100-SAN', 'Sandalwood Incense Sticks - 100pc', 'sandalwood-incense-sticks-100pc', 'Premium sandalwood incense sticks for aromatherapy.', 'Our sandalwood incense sticks are hand-rolled with natural ingredients. Creates a calming and spiritual atmosphere.', 'simple', 0, '100pc', 0.20, '25x10x5 cm', 'Paper Pack', 'Brown', 99.00, 89.00, 65.00, 149.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 0.25, 0, 'standard', 5.00, NULL, NULL, NULL, 'Sandalwood Incense Sticks 100pc - Premium Quality', 'Buy sandalwood incense sticks 100pc pack. Natural fragrance for meditation.', 'incense sticks, agarbatti, sandalwood incense, aromatic sticks', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'incense, agarbatti, sandalwood'),
(180, 25, 'INC-100-ROS', 'Rose Incense Sticks - 100pc', 'rose-incense-sticks-100pc', 'Premium rose incense sticks for home fragrance.', 'Our rose incense sticks are hand-rolled with natural rose petals. Fills your home with a sweet floral fragrance.', 'simple', 0, '100pc', 0.20, '25x10x5 cm', 'Paper Pack', 'Pink', 99.00, 89.00, 65.00, 149.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 0.25, 0, 'standard', 5.00, NULL, NULL, NULL, 'Rose Incense Sticks 100pc - Floral Fragrance', 'Buy rose incense sticks 100pc pack. Sweet floral fragrance for home.', 'rose incense, agarbatti, floral incense, home fragrance', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'rose incense, floral fragrance'),
(181, 25, 'INC-100-LAV', 'Lavender Incense Sticks - 100pc', 'lavender-incense-sticks-100pc', 'Premium lavender incense sticks for relaxation.', 'Our lavender incense sticks are hand-rolled with natural lavender. Promotes relaxation and stress relief.', 'simple', 0, '100pc', 0.20, '25x10x5 cm', 'Paper Pack', 'Purple', 99.00, 89.00, 65.00, 149.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 0.25, 0, 'standard', 5.00, NULL, NULL, NULL, 'Lavender Incense Sticks 100pc - Relaxation', 'Buy lavender incense sticks 100pc pack. Promotes relaxation and calm.', 'lavender incense, relaxation incense, aromatherapy', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'lavender incense, aromatherapy'),
(182, 26, 'CAN-100-VAN', 'Vanilla Scented Candles - 100pc', 'vanilla-scented-candles-100pc', 'Beautiful vanilla scented candles for home decor.', 'Our vanilla scented candles are hand-poured with natural wax. Creates a warm and inviting atmosphere.', 'simple', 0, '100pc', 0.50, '30x20x10 cm', 'Box', 'Cream', 399.00, 359.00, 280.00, 549.00, 'both', 1, NULL, 10, 0, 300, 30, 1, 0, 'Standard', 0.60, 0, 'standard', 5.00, NULL, NULL, NULL, 'Vanilla Scented Candles 100pc - Bulk Pack', 'Buy vanilla scented candles 100pc bulk pack. Perfect for home decor and gifting.', 'scented candles, vanilla candles, decorative candles, aroma candles', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'scented candles, vanilla, decorative'),
(183, 26, 'CAN-100-LAV', 'Lavender Scented Candles - 100pc', 'lavender-scented-candles-100pc', 'Beautiful lavender scented candles for relaxation.', 'Our lavender scented candles are hand-poured with natural wax. Promotes relaxation and calm.', 'simple', 0, '100pc', 0.50, '30x20x10 cm', 'Box', 'Purple', 399.00, 359.00, 280.00, 549.00, 'both', 1, NULL, 10, 0, 300, 30, 1, 0, 'Standard', 0.60, 0, 'standard', 5.00, NULL, NULL, NULL, 'Lavender Scented Candles 100pc - Relaxation', 'Buy lavender scented candles 100pc bulk pack. Perfect for relaxation and meditation.', 'lavender candles, scented candles, relaxation candles', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'lavender candles, relaxation'),
(184, 26, 'CAN-100-CIT', 'Citrus Scented Candles - 100pc', 'citrus-scented-candles-100pc', 'Beautiful citrus scented candles for freshness.', 'Our citrus scented candles are hand-poured with natural wax. Fills your home with fresh, energizing fragrance.', 'simple', 0, '100pc', 0.50, '30x20x10 cm', 'Box', 'Orange', 399.00, 359.00, 280.00, 549.00, 'both', 1, NULL, 10, 0, 300, 30, 1, 0, 'Standard', 0.60, 0, 'standard', 5.00, NULL, NULL, NULL, 'Citrus Scented Candles 100pc - Fresh Fragrance', 'Buy citrus scented candles 100pc bulk pack. Energizing and fresh fragrance.', 'citrus candles, scented candles, fresh fragrance candles', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'citrus candles, fresh fragrance'),
(185, 27, 'SP-TUR-100', 'Turmeric Powder - 100gm', 'turmeric-powder-100gm', 'Pure turmeric powder with high curcumin content.', 'Our turmeric powder is made from fresh, high-quality turmeric roots. Rich in curcumin and natural color.', 'simple', 0, '100gm', 0.10, '10x8x2 cm', 'Plastic Pouch', 'Yellow', 49.00, 44.00, 35.00, 79.00, 'both', 1, NULL, 10, 0, 800, 80, 1, 0, 'Standard', 0.12, 0, 'standard', 5.00, NULL, NULL, NULL, 'Turmeric Powder 100gm - Pure & Natural', 'Buy pure turmeric powder 100gm. High curcumin content, natural color.', 'turmeric, haldi, turmeric powder, yellow spice', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'turmeric, haldi, yellow powder'),
(186, 27, 'SP-TUR-500', 'Turmeric Powder - 500gm', 'turmeric-powder-500gm', 'Pure turmeric powder - family pack.', 'Our turmeric powder is made from fresh, high-quality turmeric roots. Family pack for regular use.', 'simple', 0, '500gm', 0.50, '15x12x5 cm', 'Plastic Pouch', 'Yellow', 199.00, 179.00, 140.00, 299.00, 'both', 1, NULL, 10, 0, 400, 40, 1, 0, 'Standard', 0.55, 0, 'standard', 5.00, NULL, NULL, NULL, 'Turmeric Powder 500gm - Family Pack', 'Buy turmeric powder 500gm family pack. Best value for families.', 'turmeric family pack, haldi powder', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'turmeric, family pack'),
(187, 27, 'SP-TUR-1', 'Turmeric Powder - 1kg', 'turmeric-powder-1kg', 'Pure turmeric powder - bulk pack.', 'Our turmeric powder is made from fresh, high-quality turmeric roots. Bulk pack for commercial use.', 'simple', 0, '1kg', 1.00, '20x15x8 cm', 'Plastic Pouch', 'Yellow', 379.00, 341.00, 270.00, 549.00, 'both', 1, NULL, 10, 0, 200, 25, 1, 0, 'Standard', 1.10, 0, 'standard', 5.00, NULL, NULL, NULL, 'Turmeric Powder 1kg - Bulk Pack', 'Buy turmeric powder 1kg bulk pack. Perfect for restaurants.', 'turmeric bulk, haldi bulk', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'turmeric bulk, commercial'),
(188, 27, 'SP-CUM-100', 'Cumin Seeds - 100gm', 'cumin-seeds-100gm', 'Premium cumin seeds with strong aroma.', 'Our cumin seeds are sourced from the finest farms. Strong aroma and distinct flavor.', 'simple', 0, '100gm', 0.10, '10x8x2 cm', 'Plastic Pouch', 'Brown', 59.00, 53.00, 42.00, 89.00, 'both', 1, NULL, 10, 0, 800, 80, 1, 0, 'Standard', 0.12, 0, 'standard', 5.00, NULL, NULL, NULL, 'Cumin Seeds 100gm - Premium Quality', 'Buy premium cumin seeds 100gm. Strong aroma and distinct flavor.', 'cumin, jeera, cumin seeds, spice', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'cumin, jeera, seeds'),
(189, 27, 'SP-CUM-500', 'Cumin Seeds - 500gm', 'cumin-seeds-500gm', 'Premium cumin seeds - family pack.', 'Our cumin seeds are sourced from the finest farms. Family pack for regular use.', 'simple', 0, '500gm', 0.50, '15x12x5 cm', 'Plastic Pouch', 'Brown', 249.00, 224.00, 180.00, 349.00, 'both', 1, NULL, 10, 0, 400, 40, 1, 0, 'Standard', 0.55, 0, 'standard', 5.00, NULL, NULL, NULL, 'Cumin Seeds 500gm - Family Pack', 'Buy cumin seeds 500gm family pack. Best value.', 'cumin family pack, jeera family pack', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'cumin, family pack'),
(190, 27, 'SP-COR-100', 'Coriander Powder - 100gm', 'coriander-powder-100gm', 'Pure coriander powder for daily cooking.', 'Our coriander powder is made from fresh coriander seeds. Adds flavor to Indian dishes.', 'simple', 0, '100gm', 0.10, '10x8x2 cm', 'Plastic Pouch', 'Green-Brown', 49.00, 44.00, 35.00, 79.00, 'both', 1, NULL, 10, 0, 800, 80, 1, 0, 'Standard', 0.12, 0, 'standard', 5.00, NULL, NULL, NULL, 'Coriander Powder 100gm - Pure', 'Buy coriander powder 100gm. Perfect for Indian cooking.', 'coriander, dhaniya, coriander powder', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'coriander, dhaniya, powder'),
(191, 27, 'SP-RED-100', 'Red Chilli Powder - 100gm', 'red-chilli-powder-100gm', 'Pure red chilli powder with vibrant color.', 'Our red chilli powder is made from high-quality red chillies. Adds heat and color to dishes.', 'simple', 0, '100gm', 0.10, '10x8x2 cm', 'Plastic Pouch', 'Red', 59.00, 53.00, 42.00, 89.00, 'both', 1, NULL, 10, 0, 800, 80, 1, 0, 'Standard', 0.12, 0, 'standard', 5.00, NULL, NULL, NULL, 'Red Chilli Powder 100gm - Pure', 'Buy red chilli powder 100gm. Pure and vibrant color.', 'red chilli, lal mirch, chilli powder', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'chilli, lal mirch, spice'),
(192, 27, 'SP-GAR-100', 'Garam Masala - 100gm', 'garam-masala-100gm', 'Authentic garam masala blend.', 'Our garam masala is a blend of 12 premium spices. Adds authentic flavor to Indian dishes.', 'simple', 0, '100gm', 0.10, '10x8x2 cm', 'Plastic Pouch', 'Brown', 79.00, 71.00, 55.00, 119.00, 'both', 1, NULL, 10, 0, 600, 60, 1, 0, 'Standard', 0.12, 0, 'standard', 5.00, NULL, NULL, NULL, 'Garam Masala 100gm - Authentic Blend', 'Buy authentic garam masala 100gm. Blend of 12 premium spices.', 'garam masala, spice blend, masala', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:20:41', '2026-03-20 16:20:41', NULL, NULL, 'garam masala, spice mix'),
(193, 28, 'DET-1L', 'Liquid Detergent - 1L', 'liquid-detergent-1l', 'Powerful liquid detergent for effective stain removal.', 'Our liquid detergent is specially formulated to remove tough stains while being gentle on fabrics. Perfect for all washing machines.', 'simple', 0, '1L', 1.00, '15x8x8 cm', 'Plastic Bottle', 'Blue', 129.00, 116.00, 85.00, 199.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 1.10, 0, 'standard', 18.00, NULL, NULL, NULL, 'Liquid Detergent 1L - Powerful Stain Removal', 'Buy liquid detergent 1L for effective stain removal. Gentle on fabrics.', 'liquid detergent, laundry detergent, washing liquid, fabric cleaner', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'liquid detergent, laundry, stain remover'),
(194, 28, 'DET-5L', 'Liquid Detergent - 5L', 'liquid-detergent-5l', 'Powerful liquid detergent - bulk pack.', 'Our liquid detergent is specially formulated for commercial use. Removes tough stains effectively.', 'simple', 0, '5L', 5.00, '25x15x15 cm', 'Plastic Jar', 'Blue', 549.00, 494.00, 380.00, 799.00, 'both', 1, NULL, 10, 0, 200, 25, 1, 0, 'Standard', 5.20, 0, 'standard', 18.00, NULL, NULL, NULL, 'Liquid Detergent 5L - Bulk Pack', 'Buy liquid detergent 5L bulk pack. Perfect for laundromats and hotels.', 'liquid detergent bulk, commercial laundry detergent, washing liquid bulk', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'liquid detergent bulk, commercial detergent'),
(195, 28, 'DET-10L', 'Liquid Detergent - 10L', 'liquid-detergent-10l', 'Powerful liquid detergent - super bulk pack.', 'Our liquid detergent is specially formulated for industrial use. Superior stain removal power.', 'simple', 0, '10L', 10.00, '35x20x20 cm', 'Plastic Container', 'Blue', 999.00, 899.00, 700.00, 1499.00, 'both', 1, NULL, 10, 0, 100, 15, 1, 0, 'Standard', 10.30, 0, 'standard', 18.00, NULL, NULL, NULL, 'Liquid Detergent 10L - Super Bulk', 'Buy liquid detergent 10L super bulk pack. Best for industrial use.', 'liquid detergent wholesale, industrial detergent, super bulk laundry', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 1, '2026-03-20 16:25:34', '2026-03-21 19:10:25', NULL, NULL, 'liquid detergent wholesale, industrial use'),
(196, 29, 'FLOOR-1L', 'Floor Cleaner - 1L', 'floor-cleaner-1l', 'Effective floor cleaner for sparkling clean floors.', 'Our floor cleaner removes dirt, grime, and leaves a fresh fragrance. Safe for all floor types.', 'simple', 0, '1L', 1.00, '15x8x8 cm', 'Plastic Bottle', 'Green', 99.00, 89.00, 65.00, 149.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 1.10, 0, 'standard', 18.00, NULL, NULL, NULL, 'Floor Cleaner 1L - Fresh Fragrance', 'Buy floor cleaner 1L for sparkling clean floors. Removes dirt and leaves fresh fragrance.', 'floor cleaner, floor disinfectant, floor wash, tile cleaner', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'floor cleaner, disinfectant, tile cleaner'),
(197, 29, 'FLOOR-5L', 'Floor Cleaner - 5L', 'floor-cleaner-5l', 'Effective floor cleaner - bulk pack.', 'Our floor cleaner removes dirt and grime. Bulk pack for commercial use.', 'simple', 0, '5L', 5.00, '25x15x15 cm', 'Plastic Jar', 'Green', 399.00, 359.00, 280.00, 549.00, 'both', 1, NULL, 10, 0, 200, 25, 1, 0, 'Standard', 5.20, 0, 'standard', 18.00, NULL, NULL, NULL, 'Floor Cleaner 5L - Bulk Pack', 'Buy floor cleaner 5L bulk pack. Perfect for offices and commercial spaces.', 'floor cleaner bulk, commercial floor cleaner, disinfectant bulk', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'floor cleaner bulk, commercial use'),
(198, 29, 'FLOOR-10L', 'Floor Cleaner - 10L', 'floor-cleaner-10l', 'Effective floor cleaner - super bulk pack.', 'Our floor cleaner removes dirt and grime. Super bulk pack for industrial use.', 'simple', 0, '10L', 10.00, '35x20x20 cm', 'Plastic Container', 'Green', 749.00, 674.00, 530.00, 1049.00, 'both', 1, NULL, 10, 0, 100, 15, 1, 0, 'Standard', 10.30, 0, 'standard', 18.00, NULL, NULL, NULL, 'Floor Cleaner 10L - Super Bulk', 'Buy floor cleaner 10L super bulk pack. Best for large facilities.', 'floor cleaner wholesale, industrial floor cleaner, super bulk disinfectant', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'floor cleaner wholesale, industrial use'),
(199, 30, 'FRESH-200-LAV', 'Lavender Room Freshener - 200ml', 'lavender-room-freshener-200ml', 'Long-lasting lavender room freshener.', 'Our lavender room freshener creates a calming atmosphere. Long-lasting fragrance that eliminates odors.', 'simple', 0, '200ml', 0.20, '15x5x5 cm', 'Plastic Bottle', 'Purple', 89.00, 80.00, 60.00, 129.00, 'both', 1, NULL, 10, 0, 600, 60, 1, 0, 'Standard', 0.25, 0, 'standard', 18.00, NULL, NULL, NULL, 'Lavender Room Freshener 200ml - Calming Fragrance', 'Buy lavender room freshener 200ml. Creates a calming atmosphere.', 'room freshener, lavender freshener, air freshener, home fragrance', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'lavender freshener, room spray'),
(200, 30, 'FRESH-200-CIT', 'Citrus Room Freshener - 200ml', 'citrus-room-freshener-200ml', 'Energizing citrus room freshener.', 'Our citrus room freshener fills your home with fresh, energizing fragrance. Eliminates odors effectively.', 'simple', 0, '200ml', 0.20, '15x5x5 cm', 'Plastic Bottle', 'Orange', 89.00, 80.00, 60.00, 129.00, 'both', 1, NULL, 10, 0, 600, 60, 1, 0, 'Standard', 0.25, 0, 'standard', 18.00, NULL, NULL, NULL, 'Citrus Room Freshener 200ml - Fresh Fragrance', 'Buy citrus room freshener 200ml. Energizing and fresh fragrance.', 'citrus freshener, room freshener, air freshener, home spray', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'citrus freshener, air spray'),
(201, 30, 'FRESH-200-ROS', 'Rose Room Freshener - 200ml', 'rose-room-freshener-200ml', 'Floral rose room freshener.', 'Our rose room freshener fills your home with sweet floral fragrance. Long-lasting and refreshing.', 'simple', 0, '200ml', 0.20, '15x5x5 cm', 'Plastic Bottle', 'Pink', 89.00, 80.00, 60.00, 129.00, 'both', 1, NULL, 10, 0, 600, 60, 1, 0, 'Standard', 0.25, 0, 'standard', 18.00, NULL, NULL, NULL, 'Rose Room Freshener 200ml - Floral Fragrance', 'Buy rose room freshener 200ml. Sweet floral fragrance for your home.', 'rose freshener, floral freshener, room freshener, air freshener', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'rose freshener, floral spray'),
(202, 30, 'FRESH-500-LAV', 'Lavender Room Freshener - 500ml', 'lavender-room-freshener-500ml', 'Long-lasting lavender freshener - family pack.', 'Our lavender room freshener in family pack size. Perfect for larger rooms.', 'simple', 0, '500ml', 0.50, '20x8x8 cm', 'Plastic Bottle', 'Purple', 189.00, 170.00, 130.00, 279.00, 'both', 1, NULL, 10, 0, 400, 40, 1, 0, 'Standard', 0.60, 0, 'standard', 18.00, NULL, NULL, NULL, 'Lavender Room Freshener 500ml - Family Pack', 'Buy lavender room freshener 500ml family pack. Best value.', 'lavender freshener family pack, room freshener large, air freshener', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'lavender family pack, large freshener'),
(203, 30, 'FRESH-500-CIT', 'Citrus Room Freshener - 500ml', 'citrus-room-freshener-500ml', 'Energizing citrus freshener - family pack.', 'Our citrus room freshener in family pack size. Fresh and energizing.', 'simple', 0, '500ml', 0.50, '20x8x8 cm', 'Plastic Bottle', 'Orange', 189.00, 170.00, 130.00, 279.00, 'both', 1, NULL, 10, 0, 400, 40, 1, 0, 'Standard', 0.60, 0, 'standard', 18.00, NULL, NULL, NULL, 'Citrus Room Freshener 500ml - Family Pack', 'Buy citrus room freshener 500ml family pack. Fresh and energizing fragrance.', 'citrus freshener family pack, room freshener large, air freshener', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'citrus family pack, large freshener'),
(204, 31, 'PAP-100-PLA', 'Plain Papad - 100pc', 'plain-papad-100pc', 'Crispy plain papad made from urad dal.', 'Our plain papad is made from high-quality urad dal. Crispy and delicious, perfect as appetizer.', 'simple', 0, '100pc', 0.25, '20x15x5 cm', 'Plastic Pack', 'White', 99.00, 89.00, 70.00, 149.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 0.30, 0, 'standard', 5.00, NULL, NULL, NULL, 'Plain Papad 100pc - Crispy & Delicious', 'Buy plain papad 100pc pack. Crispy and perfect as appetizer.', 'papad, plain papad, appalam, poppadom', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'papad, plain, appalam'),
(205, 31, 'PAP-100-JEE', 'Jeera Papad - 100pc', 'jeera-papad-100pc', 'Crispy jeera papad with cumin seeds.', 'Our jeera papad is made with roasted cumin seeds. Adds extra flavor to your meal.', 'simple', 0, '100pc', 0.25, '20x15x5 cm', 'Plastic Pack', 'Brown', 109.00, 98.00, 78.00, 169.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 0.30, 0, 'standard', 5.00, NULL, NULL, NULL, 'Jeera Papad 100pc - Cumin Flavored', 'Buy jeera papad 100pc pack. Crispy with roasted cumin seeds.', 'jeera papad, cumin papad, flavored papad', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'jeera papad, cumin flavor'),
(206, 31, 'PAP-100-MIR', 'Mirch Papad - 100pc', 'mirch-papad-100pc', 'Spicy mirch papad with red chilli flakes.', 'Our mirch papad is made with red chilli flakes. Adds a spicy kick to your meal.', 'simple', 0, '100pc', 0.25, '20x15x5 cm', 'Plastic Pack', 'Red', 109.00, 98.00, 78.00, 169.00, 'both', 1, NULL, 10, 0, 500, 50, 1, 0, 'Standard', 0.30, 0, 'standard', 5.00, NULL, NULL, NULL, 'Mirch Papad 100pc - Spicy Flavor', 'Buy mirch papad 100pc pack. Spicy and crispy.', 'mirch papad, spicy papad, chilli papad', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'mirch papad, spicy flavor'),
(207, 32, 'SODA-500', 'Soda Powder - 500gm', 'soda-powder-500gm', 'Food grade soda powder for cooking.', 'Our soda powder is food grade and safe for consumption. Perfect for cooking, baking, and cleaning.', 'simple', 0, '500gm', 0.50, '15x10x5 cm', 'Plastic Pouch', 'White', 39.00, 35.00, 25.00, 59.00, 'both', 1, NULL, 10, 0, 800, 80, 1, 0, 'Standard', 0.55, 0, 'standard', 5.00, NULL, NULL, NULL, 'Soda Powder 500gm - Food Grade', 'Buy soda powder 500gm. Food grade quality for cooking and cleaning.', 'soda powder, baking soda, cooking soda, sodium bicarbonate', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'soda powder, baking soda'),
(208, 32, 'SODA-1', 'Soda Powder - 1kg', 'soda-powder-1kg', 'Food grade soda powder - family pack.', 'Our soda powder is food grade and safe for consumption. Family pack for regular use.', 'simple', 0, '1kg', 1.00, '20x15x8 cm', 'Plastic Pouch', 'White', 69.00, 62.00, 48.00, 99.00, 'both', 1, NULL, 10, 0, 600, 60, 1, 0, 'Standard', 1.10, 0, 'standard', 5.00, NULL, NULL, NULL, 'Soda Powder 1kg - Family Pack', 'Buy soda powder 1kg family pack. Best value for regular use.', 'soda powder family pack, baking soda bulk, cooking soda', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'soda powder family pack'),
(209, 32, 'SODA-5', 'Soda Powder - 5kg', 'soda-powder-5kg', 'Food grade soda powder - bulk pack.', 'Our soda powder is food grade and safe for consumption. Bulk pack for commercial use.', 'simple', 0, '5kg', 5.00, '30x25x15 cm', 'Plastic Bag', 'White', 299.00, 269.00, 210.00, 449.00, 'both', 1, NULL, 10, 0, 300, 30, 1, 0, 'Standard', 5.20, 0, 'standard', 5.00, NULL, NULL, NULL, 'Soda Powder 5kg - Bulk Pack', 'Buy soda powder 5kg bulk pack. Perfect for bakeries and restaurants.', 'soda powder bulk, baking soda wholesale, commercial soda', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'soda powder bulk, commercial'),
(210, 33, 'BAG-500', 'Grocery Bag - 500gm Capacity', 'grocery-bag-500gm', 'Eco-friendly grocery bag with 500gm capacity.', 'Our eco-friendly grocery bags are strong, durable, and reusable. Perfect for shopping and daily use.', 'simple', 0, '500gm', 0.05, '30x25x10 cm', 'Cotton', 'Natural', 49.00, 44.00, 30.00, 79.00, 'both', 1, NULL, 10, 0, 1000, 100, 1, 0, 'Standard', 0.10, 0, 'standard', 5.00, NULL, NULL, NULL, 'Eco-Friendly Grocery Bag 500gm Capacity', 'Buy eco-friendly grocery bag with 500gm capacity. Strong and reusable.', 'grocery bag, shopping bag, eco-friendly bag, reusable bag', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'grocery bag, eco-friendly, shopping bag'),
(211, 33, 'BAG-1000', 'Grocery Bag - 1000gm Capacity', 'grocery-bag-1000gm', 'Eco-friendly grocery bag with 1000gm capacity.', 'Our eco-friendly grocery bags are strong, durable, and reusable. Large capacity for heavy shopping.', 'simple', 0, '1000gm', 0.08, '35x30x12 cm', 'Cotton', 'Natural', 79.00, 71.00, 50.00, 129.00, 'both', 1, NULL, 10, 0, 800, 80, 1, 0, 'Standard', 0.15, 0, 'standard', 5.00, NULL, NULL, NULL, 'Eco-Friendly Grocery Bag 1000gm Capacity', 'Buy eco-friendly grocery bag with 1000gm capacity. Large and durable.', 'large grocery bag, heavy duty bag, eco-friendly bag, shopping bag', 1, 1, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'large grocery bag, heavy duty'),
(212, 33, 'BAG-500-SET', 'Grocery Bag Set - 5pcs (500gm)', 'grocery-bag-set-5pcs-500gm', 'Set of 5 eco-friendly grocery bags.', 'Our eco-friendly grocery bags set includes 5 bags. Perfect for family shopping.', 'simple', 0, '5pcs', 0.25, '30x25x15 cm', 'Cotton', 'Assorted', 199.00, 179.00, 140.00, 299.00, 'both', 1, NULL, 10, 0, 300, 30, 1, 0, 'Standard', 0.60, 0, 'standard', 5.00, NULL, NULL, NULL, 'Grocery Bag Set - 5 Eco-Friendly Bags', 'Buy set of 5 eco-friendly grocery bags. Perfect for family shopping.', 'grocery bag set, reusable bag set, shopping bag set', 1, 0, 1, 0, 0, 0, 'tiered', 1, 0, 0.00, 0.00, 0, 0, '2026-03-20 16:25:34', '2026-03-20 16:25:34', NULL, NULL, 'bag set, reusable set');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `medium_url` varchar(500) DEFAULT NULL,
  `large_url` varchar(500) DEFAULT NULL,
  `image_type` enum('main','gallery','hover','zoom','variant','thumbnail') DEFAULT 'gallery',
  `alt_text` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `variant_id`, `image_url`, `thumbnail_url`, `medium_url`, `large_url`, `image_type`, `alt_text`, `title`, `caption`, `file_name`, `file_size`, `mime_type`, `width`, `height`, `sort_order`, `is_primary`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'wooden-ganesha-1.jpg', 'wooden-ganesha-1-thumb.jpg', 'wooden-ganesha-1-medium.jpg', NULL, 'main', 'Handcrafted Wooden Ganesha Idol - Front View', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, '2026-03-08 08:38:10', '2026-03-08 08:38:10'),
(2, 1, NULL, 'wooden-ganesha-2.jpg', 'wooden-ganesha-2-thumb.jpg', 'wooden-ganesha-2-medium.jpg', NULL, 'gallery', 'Handcrafted Wooden Ganesha Idol - Side View', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, 1, '2026-03-08 08:38:10', '2026-03-08 08:38:10'),
(3, 1, NULL, 'wooden-ganesha-3.jpg', 'wooden-ganesha-3-thumb.jpg', 'wooden-ganesha-3-medium.jpg', NULL, 'gallery', 'Handcrafted Wooden Ganesha Idol - Back View', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, '2026-03-08 08:38:10', '2026-03-08 08:38:10'),
(4, 2, NULL, 'jewelry-box-1.jpg', 'jewelry-box-1-thumb.jpg', 'jewelry-box-1-medium.jpg', NULL, 'main', 'Rosewood Jewelry Box - Closed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, '2026-03-08 08:38:10', '2026-03-08 08:38:10'),
(5, 2, NULL, 'jewelry-box-2.jpg', 'jewelry-box-2-thumb.jpg', 'jewelry-box-2-medium.jpg', NULL, 'gallery', 'Rosewood Jewelry Box - Open', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, 1, '2026-03-08 08:38:10', '2026-03-08 08:38:10'),
(6, 2, NULL, 'jewelry-box-3.jpg', 'jewelry-box-3-thumb.jpg', 'jewelry-box-3-medium.jpg', NULL, 'gallery', 'Rosewood Jewelry Box - Compartments', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, '2026-03-08 08:38:10', '2026-03-08 08:38:10'),
(7, 4, NULL, 'brass-diya-1.jpg', 'brass-diya-1-thumb.jpg', 'brass-diya-1-medium.jpg', NULL, 'main', 'Brass Diya Set - Full Set', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, '2026-03-08 08:38:10', '2026-03-08 08:38:10'),
(8, 4, NULL, 'brass-diya-2.jpg', 'brass-diya-2-thumb.jpg', 'brass-diya-2-medium.jpg', NULL, 'gallery', 'Brass Diya - Single', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, 1, '2026-03-08 08:38:10', '2026-03-08 08:38:10'),
(9, 119, NULL, '1774108144_thumbnail_4031.jpg', NULL, NULL, NULL, 'thumbnail', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, '2026-03-21 15:49:04', '2026-03-21 15:54:59'),
(12, 119, NULL, '1774108194_gallery_0_7580.jpg', NULL, NULL, NULL, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, '2026-03-21 15:49:54', '2026-03-21 15:49:54'),
(13, 119, NULL, '1774108194_gallery_1_6413.jpg', NULL, NULL, NULL, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 1, '2026-03-21 15:49:54', '2026-03-21 15:49:54'),
(14, 119, NULL, '1774108194_gallery_2_5189.webp', NULL, NULL, NULL, 'gallery', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 0, 1, '2026-03-21 15:49:54', '2026-03-21 15:49:54');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(255) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `pros` text DEFAULT NULL,
  `cons` text DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `video_url` varchar(500) DEFAULT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `helpful_count` int(11) DEFAULT 0,
  `not_helpful_count` int(11) DEFAULT 0,
  `admin_response` text DEFAULT NULL,
  `admin_responded_at` timestamp NULL DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `order_id`, `order_item_id`, `rating`, `title`, `review`, `pros`, `cons`, `images`, `video_url`, `is_verified_purchase`, `is_anonymous`, `status`, `helpful_count`, `not_helpful_count`, `admin_response`, `admin_responded_at`, `admin_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 1, 5, 'Exquisite craftsmanship', 'The wooden Ganesha idol is beautifully carved. Perfect for my home temple. The wood finish is excellent.', 'Beautiful design, Good finish, Perfect size', 'None', '[\"review-ganesha-1.jpg\", \"review-ganesha-2.jpg\"]', NULL, 1, 0, 'approved', 15, 0, NULL, NULL, NULL, '2026-02-18 08:50:52', '2026-03-08 08:50:52'),
(2, 5, 2, 1, 2, 4, 'Good quality copper bottle', 'The copper bottle is of good quality. Water tastes fresh. However, the cap could be tighter.', 'Good material, Health benefits', 'Cap could be better', NULL, NULL, 1, 0, 'approved', 8, 0, NULL, NULL, NULL, '2026-02-19 08:50:52', '2026-03-08 08:50:52'),
(3, 10, 3, 2, 3, 5, 'Excellent fabric for boutique', 'Bought 150 meters for my boutique. The Kalamkari prints are authentic and colors are vibrant. My customers love it.', 'Authentic prints, Vibrant colors, Good fabric weight', 'None', '[\"review-fabric-1.jpg\", \"review-fabric-2.jpg\"]', NULL, 1, 0, 'approved', 25, 0, NULL, NULL, NULL, '2026-03-01 08:50:52', '2026-03-08 08:50:52'),
(4, 16, 3, 2, 4, 5, 'Premium quality cashews', 'The W180 cashews are huge and fresh. Perfect for gifting during festive season. Good packaging.', 'Large size, Fresh taste, Good packaging', 'Slightly expensive', NULL, NULL, 1, 0, 'approved', 12, 0, NULL, NULL, NULL, '2026-03-02 08:50:52', '2026-03-08 08:50:52'),
(5, 7, 4, NULL, NULL, 5, 'Beautiful dinner set', 'I saw this at a friend\'s place and loved it. Planning to buy soon. The blue pottery work is stunning.', 'Unique design, Good quality', 'Fragile - need careful handling', NULL, NULL, 0, 0, 'pending', 0, 0, NULL, NULL, NULL, '2026-03-05 08:50:52', '2026-03-08 08:50:52'),
(6, 2, 2, NULL, NULL, 4, 'Nice jewelry box', 'Good craftsmanship but a bit pricey. The velvet lining is good quality.', 'Multiple compartments, Good finish', 'Price could be lower', NULL, NULL, 0, 0, 'approved', 3, 0, NULL, NULL, NULL, '2026-02-26 08:50:52', '2026-03-08 08:50:52');

-- --------------------------------------------------------

--
-- Table structure for table `product_shipping_restrictions`
--

CREATE TABLE `product_shipping_restrictions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `is_serviceable` tinyint(1) DEFAULT 1,
  `delivery_days` int(11) DEFAULT NULL,
  `additional_charge` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_shipping_restrictions`
--

INSERT INTO `product_shipping_restrictions` (`id`, `product_id`, `pincode`, `is_serviceable`, `delivery_days`, `additional_charge`, `created_at`, `updated_at`) VALUES
(1, 1, '110001', 1, 3, 0.00, '2026-03-08 14:11:09', NULL),
(2, 1, '400001', 1, 4, 0.00, '2026-03-08 14:11:09', NULL),
(3, 1, '700001', 1, 5, 0.00, '2026-03-08 14:11:09', NULL),
(4, 1, '600001', 1, 5, 0.00, '2026-03-08 14:11:09', NULL),
(5, 7, '110001', 1, 4, 50.00, '2026-03-08 14:11:09', NULL),
(6, 7, '400001', 1, 5, 50.00, '2026-03-08 14:11:09', NULL),
(7, 7, '560001', 1, 5, 50.00, '2026-03-08 14:11:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `variant_code` varchar(50) DEFAULT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`attributes`)),
  `size` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `pattern` varchar(100) DEFAULT NULL,
  `style` varchar(100) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `gallery_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery_images`)),
  `retail_price` decimal(10,2) NOT NULL,
  `wholesale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `mrp` decimal(10,2) DEFAULT NULL,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `is_bulk_only` tinyint(1) DEFAULT 0,
  `bulk_min_quantity` int(11) DEFAULT 10,
  `stock_quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `track_inventory` tinyint(1) DEFAULT 1,
  `weight` decimal(10,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_default` tinyint(1) DEFAULT 0,
  `total_sold` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `variant_code`, `attributes`, `size`, `color`, `material`, `pattern`, `style`, `image`, `gallery_images`, `retail_price`, `wholesale_price`, `cost_price`, `mrp`, `price_adjustment`, `is_bulk_only`, `bulk_min_quantity`, `stock_quantity`, `low_stock_threshold`, `track_inventory`, `weight`, `dimensions`, `is_active`, `is_default`, `total_sold`, `created_at`, `updated_at`) VALUES
(1, 3, 'WDN-JBX-SML-BRN', 'JBX-SML-BRN-001', '{\"color\": \"Brown\", \"finish\": \"Matte\", \"compartments\": 3}', 'Small', 'Brown', 'Sheesham Wood', 'Traditional Carved', NULL, 'jewelry-box-small-brown-1.jpg', NULL, 900.00, 650.00, 400.00, 1200.00, 0.00, 0, 10, 25, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:50:00', '2026-03-08 14:50:00'),
(2, 3, 'WDN-JBX-SML-MAP', 'JBX-SML-MAP-001', '{\"color\": \"Maple\", \"finish\": \"Glossy\", \"compartments\": 3}', 'Small', 'Maple', 'Maple Wood', 'Simple', NULL, 'jewelry-box-small-maple-1.jpg', NULL, 950.00, 680.00, 420.00, 1250.00, 0.00, 0, 10, 20, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:00', '2026-03-08 14:50:00'),
(3, 3, 'WDN-JBX-SML-WAL', 'JBX-SML-WAL-001', '{\"color\": \"Walnut\", \"finish\": \"Matte\", \"compartments\": 3}', 'Small', 'Walnut', 'Walnut Wood', 'Floral Carved', NULL, 'jewelry-box-small-walnut-1.jpg', NULL, 1000.00, 720.00, 450.00, 1300.00, 0.00, 0, 10, 15, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:00', '2026-03-08 14:50:00'),
(4, 3, 'WDN-JBX-SML-RED', 'JBX-SML-RED-001', '{\"color\": \"Red\", \"finish\": \"Glossy\", \"compartments\": 3}', 'Small', 'Red', 'Rosewood', 'Traditional', NULL, 'jewelry-box-small-red-1.jpg', NULL, 980.00, 700.00, 430.00, 1280.00, 0.00, 0, 10, 18, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:00', '2026-03-08 14:50:00'),
(5, 4, 'WDN-JBX-MED-BRN', 'JBX-MED-BRN-001', '{\"color\": \"Brown\", \"finish\": \"Matte\", \"compartments\": 5}', 'Medium', 'Brown', 'Sheesham Wood', 'Traditional Carved', NULL, 'jewelry-box-medium-brown-1.jpg', NULL, 1500.00, 1100.00, 700.00, 2000.00, 0.00, 0, 10, 20, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:50:00', '2026-03-08 14:50:00'),
(6, 4, 'WDN-JBX-MED-MAP', 'JBX-MED-MAP-001', '{\"color\": \"Maple\", \"finish\": \"Glossy\", \"compartments\": 5}', 'Medium', 'Maple', 'Maple Wood', 'Simple', NULL, 'jewelry-box-medium-maple-1.jpg', NULL, 1550.00, 1150.00, 730.00, 2050.00, 0.00, 0, 10, 15, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:00', '2026-03-08 14:50:00'),
(7, 4, 'WDN-JBX-MED-WAL', 'JBX-MED-WAL-001', '{\"color\": \"Walnut\", \"finish\": \"Matte\", \"compartments\": 5}', 'Medium', 'Walnut', 'Walnut Wood', 'Floral Carved', NULL, 'jewelry-box-medium-walnut-1.jpg', NULL, 1600.00, 1200.00, 750.00, 2100.00, 0.00, 0, 10, 12, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:00', '2026-03-08 14:50:00'),
(8, 19, 'BRS-DIY-SML-LOT', 'DIY-SML-LOT-001', '{\"design\": \"Lotus\", \"finish\": \"Polished\", \"weight\": \"150g\"}', 'Small', NULL, NULL, 'Lotus Design', NULL, 'brass-diya-lotus-1.jpg', NULL, 400.00, 280.00, 180.00, 550.00, 0.00, 0, 10, 100, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(9, 19, 'BRS-DIY-SML-PLA', 'DIY-SML-PLA-001', '{\"design\": \"Plain\", \"finish\": \"Matte\", \"weight\": \"140g\"}', 'Small', NULL, NULL, 'Plain', NULL, 'brass-diya-plain-1.jpg', NULL, 380.00, 260.00, 165.00, 520.00, 0.00, 0, 10, 120, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(10, 19, 'BRS-DIY-SML-KAL', 'DIY-SML-KAL-001', '{\"design\": \"Kalash\", \"finish\": \"Polished\", \"weight\": \"160g\"}', 'Small', NULL, NULL, 'Kalash Design', NULL, 'brass-diya-kalash-1.jpg', NULL, 420.00, 300.00, 190.00, 580.00, 0.00, 0, 10, 80, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(11, 20, 'BRS-DIY-MED-LOT', 'DIY-MED-LOT-001', '{\"design\": \"Lotus\", \"finish\": \"Polished\", \"weight\": \"250g\"}', 'Medium', NULL, NULL, 'Lotus Design', NULL, 'brass-diya-medium-lotus-1.jpg', NULL, 650.00, 450.00, 280.00, 900.00, 0.00, 0, 10, 80, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(12, 20, 'BRS-DIY-MED-PEA', 'DIY-MED-PEA-001', '{\"design\": \"Peacock\", \"finish\": \"Antique\", \"weight\": \"270g\"}', 'Medium', NULL, NULL, 'Peacock Design', NULL, 'brass-diya-peacock-1.jpg', NULL, 700.00, 500.00, 310.00, 950.00, 0.00, 0, 10, 60, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(13, 20, 'BRS-DIY-MED-ELE', 'DIY-MED-ELE-001', '{\"design\": \"Elephant\", \"finish\": \"Polished\", \"weight\": \"260g\"}', 'Medium', NULL, NULL, 'Elephant Design', NULL, 'brass-diya-elephant-1.jpg', NULL, 680.00, 480.00, 300.00, 930.00, 0.00, 0, 10, 70, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(14, 24, 'BRS-BEL-SML-PLA', 'BEL-SML-PLA-001', '{\"design\": \"Plain\", \"sound\": \"Clear\", \"weight\": \"200g\"}', 'Small', NULL, NULL, 'Plain', NULL, 'brass-bell-small-plain-1.jpg', NULL, 900.00, 650.00, 400.00, 1250.00, 0.00, 0, 10, 60, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(15, 24, 'BRS-BEL-SML-HAN', 'BEL-SML-HAN-001', '{\"design\": \"Hanuman\", \"sound\": \"Clear\", \"weight\": \"220g\"}', 'Small', NULL, NULL, 'Hanuman Design', NULL, 'brass-bell-small-hanuman-1.jpg', NULL, 1000.00, 720.00, 450.00, 1350.00, 0.00, 0, 10, 40, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(16, 24, 'BRS-BEL-SML-SNA', 'BEL-SML-SNA-001', '{\"design\": \"Snake\", \"sound\": \"Deep\", \"weight\": \"210g\"}', 'Small', NULL, NULL, 'Snake Design', NULL, 'brass-bell-small-snake-1.jpg', NULL, 950.00, 680.00, 420.00, 1300.00, 0.00, 0, 10, 50, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(17, 25, 'BRS-BEL-MED-PLA', 'BEL-MED-PLA-001', '{\"design\": \"Plain\", \"sound\": \"Deep\", \"weight\": \"350g\"}', 'Medium', NULL, NULL, 'Plain', NULL, 'brass-bell-medium-plain-1.jpg', NULL, 1600.00, 1200.00, 750.00, 2200.00, 0.00, 0, 10, 45, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(18, 25, 'BRS-BEL-MED-LAK', 'BEL-MED-LAK-001', '{\"design\": \"Lakshmi\", \"sound\": \"Deep\", \"weight\": \"380g\"}', 'Medium', NULL, NULL, 'Lakshmi Design', NULL, 'brass-bell-medium-lakshmi-1.jpg', NULL, 1800.00, 1350.00, 850.00, 2400.00, 0.00, 0, 10, 30, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(19, 25, 'BRS-BEL-MED-GAN', 'BEL-MED-GAN-001', '{\"design\": \"Ganesh\", \"sound\": \"Deep\", \"weight\": \"370g\"}', 'Medium', NULL, NULL, 'Ganesh Design', NULL, 'brass-bell-medium-ganesh-1.jpg', NULL, 1750.00, 1300.00, 820.00, 2350.00, 0.00, 0, 10, 35, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:50:46', '2026-03-08 14:50:46'),
(20, 30, 'IRN-LAN-MED-BLK', 'LAN-MED-BLK-001', '{\"color\": \"Black\", \"finish\": \"Matte\", \"glass\": \"Clear\"}', 'Medium', NULL, NULL, 'Black', NULL, 'iron-lantern-medium-black-1.jpg', NULL, 2200.00, 1650.00, 1000.00, 3000.00, 0.00, 0, 10, 25, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(21, 30, 'IRN-LAN-MED-BRN', 'LAN-MED-BRN-001', '{\"color\": \"Brown\", \"finish\": \"Rustic\", \"glass\": \"Clear\"}', 'Medium', NULL, NULL, 'Brown', NULL, 'iron-lantern-medium-brown-1.jpg', NULL, 2300.00, 1700.00, 1050.00, 3100.00, 0.00, 0, 10, 20, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(22, 30, 'IRN-LAN-MED-WHT', 'LAN-MED-WHT-001', '{\"color\": \"White\", \"finish\": \"Painted\", \"glass\": \"Clear\"}', 'Medium', NULL, NULL, 'White', NULL, 'iron-lantern-medium-white-1.jpg', NULL, 2100.00, 1550.00, 950.00, 2900.00, 0.00, 0, 10, 30, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(23, 31, 'IRN-LAN-LRG-BLK', 'LAN-LRG-BLK-001', '{\"color\": \"Black\", \"finish\": \"Matte\", \"glass\": \"Clear\"}', 'Large', NULL, NULL, 'Black', NULL, 'iron-lantern-large-black-1.jpg', NULL, 3200.00, 2400.00, 1500.00, 4500.00, 0.00, 0, 10, 18, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(24, 31, 'IRN-LAN-LRG-BRN', 'LAN-LRG-BRN-001', '{\"color\": \"Brown\", \"finish\": \"Rustic\", \"glass\": \"Clear\"}', 'Large', NULL, NULL, 'Brown', NULL, 'iron-lantern-large-brown-1.jpg', NULL, 3300.00, 2500.00, 1550.00, 4600.00, 0.00, 0, 10, 15, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(25, 31, 'IRN-LAN-LRG-GRN', 'LAN-LRG-GRN-001', '{\"color\": \"Green\", \"finish\": \"Antique\", \"glass\": \"Clear\"}', 'Large', NULL, NULL, 'Green', NULL, 'iron-lantern-large-green-1.jpg', NULL, 3400.00, 2600.00, 1600.00, 4700.00, 0.00, 0, 10, 12, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(26, 32, 'CAN-STD-MED-SIM', 'STD-MED-SIM-001', '{\"design\": \"Simple\", \"height\": \"12 inches\", \"material\": \"Iron\"}', 'Medium', NULL, NULL, 'Simple', NULL, 'iron-candle-stand-simple-1.jpg', NULL, 1800.00, 1300.00, 800.00, 2500.00, 0.00, 0, 10, 40, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(27, 32, 'CAN-STD-MED-TWI', 'STD-MED-TWI-001', '{\"design\": \"Twisted\", \"height\": \"12 inches\", \"material\": \"Iron\"}', 'Medium', NULL, NULL, 'Twisted', NULL, 'iron-candle-stand-twisted-1.jpg', NULL, 1900.00, 1400.00, 850.00, 2600.00, 0.00, 0, 10, 35, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(28, 32, 'CAN-STD-MED-FLO', 'STD-MED-FLO-001', '{\"design\": \"Floral\", \"height\": \"12 inches\", \"material\": \"Iron\"}', 'Medium', NULL, NULL, 'Floral', NULL, 'iron-candle-stand-floral-1.jpg', NULL, 1950.00, 1450.00, 880.00, 2700.00, 0.00, 0, 10, 30, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(29, 33, 'CAN-STD-LRG-SIM', 'STD-LRG-SIM-001', '{\"design\": \"Simple\", \"height\": \"24 inches\", \"material\": \"Iron\"}', 'Large', NULL, NULL, 'Simple', NULL, 'iron-candle-stand-large-simple-1.jpg', NULL, 3000.00, 2200.00, 1400.00, 4200.00, 0.00, 0, 10, 25, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(30, 33, 'CAN-STD-LRG-TWI', 'STD-LRG-TWI-001', '{\"design\": \"Twisted\", \"height\": \"24 inches\", \"material\": \"Iron\"}', 'Large', NULL, NULL, 'Twisted', NULL, 'iron-candle-stand-large-twisted-1.jpg', NULL, 3200.00, 2400.00, 1500.00, 4400.00, 0.00, 0, 10, 20, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(31, 33, 'CAN-STD-LRG-ARC', 'STD-LRG-ARC-001', '{\"design\": \"Arch\", \"height\": \"24 inches\", \"material\": \"Iron\"}', 'Large', NULL, NULL, 'Arch', NULL, 'iron-candle-stand-large-arch-1.jpg', NULL, 3300.00, 2500.00, 1550.00, 4500.00, 0.00, 0, 10, 18, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:20', '2026-03-08 14:54:20'),
(32, 51, 'TEX-BED-SGL-WHT', 'BED-SGL-WHT-001', '{\"color\": \"White\", \"thread_count\": \"180\", \"pillow_covers\": 1}', 'Single 90x100', 'White', '100% Cotton', 'Solid', NULL, 'bedsheet-single-white-1.jpg', NULL, 500.00, 380.00, 250.00, 700.00, 0.00, 0, 10, 100, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(33, 51, 'TEX-BED-SGL-BLU', 'BED-SGL-BLU-001', '{\"color\": \"Light Blue\", \"thread_count\": \"180\", \"pillow_covers\": 1}', 'Single 90x100', 'Light Blue', '100% Cotton', 'Solid', NULL, 'bedsheet-single-blue-1.jpg', NULL, 500.00, 380.00, 250.00, 700.00, 0.00, 0, 10, 80, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(34, 51, 'TEX-BED-SGL-PNK', 'BED-SGL-PNK-001', '{\"color\": \"Pink\", \"thread_count\": \"180\", \"pillow_covers\": 1}', 'Single 90x100', 'Pink', '100% Cotton', 'Solid', NULL, 'bedsheet-single-pink-1.jpg', NULL, 500.00, 380.00, 250.00, 700.00, 0.00, 0, 10, 75, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(35, 51, 'TEX-BED-SGL-GRN', 'BED-SGL-GRN-001', '{\"color\": \"Green\", \"thread_count\": \"180\", \"pillow_covers\": 1}', 'Single 90x100', 'Green', '100% Cotton', 'Solid', NULL, 'bedsheet-single-green-1.jpg', NULL, 500.00, 380.00, 250.00, 700.00, 0.00, 0, 10, 85, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(36, 52, 'TEX-BED-DBL-WHT', 'BED-DBL-WHT-001', '{\"color\": \"White\", \"thread_count\": \"180\", \"pillow_covers\": 2}', 'Double 90x108', 'White', '100% Cotton', 'Solid', NULL, 'bedsheet-double-white-1.jpg', NULL, 1000.00, 750.00, 500.00, 1400.00, 0.00, 0, 10, 90, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(37, 52, 'TEX-BED-DBL-BLU', 'BED-DBL-BLU-001', '{\"color\": \"Light Blue\", \"thread_count\": \"180\", \"pillow_covers\": 2}', 'Double 90x108', 'Light Blue', '100% Cotton', 'Solid', NULL, 'bedsheet-double-blue-1.jpg', NULL, 1000.00, 750.00, 500.00, 1400.00, 0.00, 0, 10, 85, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(38, 52, 'TEX-BED-DBL-BGE', 'BED-DBL-BGE-001', '{\"color\": \"Beige\", \"thread_count\": \"180\", \"pillow_covers\": 2}', 'Double 90x108', 'Beige', '100% Cotton', 'Solid', NULL, 'bedsheet-double-beige-1.jpg', NULL, 1000.00, 750.00, 500.00, 1400.00, 0.00, 0, 10, 80, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(39, 53, 'TEX-BED-KNG-WHT', 'BED-KNG-WHT-001', '{\"color\": \"White\", \"thread_count\": \"200\", \"pillow_covers\": 2}', 'King 108x108', 'White', '100% Cotton', 'Solid', NULL, 'bedsheet-king-white-1.jpg', NULL, 1550.00, 1150.00, 750.00, 2100.00, 0.00, 0, 10, 60, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(40, 53, 'TEX-BED-KNG-IVY', 'BED-KNG-IVY-001', '{\"color\": \"Ivory\", \"thread_count\": \"200\", \"pillow_covers\": 2}', 'King 108x108', 'Ivory', '100% Cotton', 'Solid', NULL, 'bedsheet-king-ivory-1.jpg', NULL, 1550.00, 1150.00, 750.00, 2100.00, 0.00, 0, 10, 55, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(41, 53, 'TEX-BED-KNG-GRY', 'BED-KNG-GRY-001', '{\"color\": \"Grey\", \"thread_count\": \"200\", \"pillow_covers\": 2}', 'King 108x108', 'Grey', '100% Cotton', 'Solid', NULL, 'bedsheet-king-grey-1.jpg', NULL, 1550.00, 1150.00, 750.00, 2100.00, 0.00, 0, 10, 50, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(42, 59, 'TEX-BLA-SGL-BRN', 'BLA-SGL-BRN-001', '{\"color\": \"Brown\", \"material\": \"Acrylic\", \"weight\": \"1.5kg\"}', 'Single', 'Brown', 'Acrylic', 'Solid', NULL, 'blanket-single-brown-1.jpg', NULL, 1500.00, 1100.00, 700.00, 2100.00, 0.00, 0, 10, 70, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(43, 59, 'TEX-BLA-SGL-BLU', 'BLA-SGL-BLU-001', '{\"color\": \"Blue\", \"material\": \"Acrylic\", \"weight\": \"1.5kg\"}', 'Single', 'Blue', 'Acrylic', 'Solid', NULL, 'blanket-single-blue-1.jpg', NULL, 1500.00, 1100.00, 700.00, 2100.00, 0.00, 0, 10, 65, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(44, 59, 'TEX-BLA-SGL-GRY', 'BLA-SGL-GRY-001', '{\"color\": \"Grey\", \"material\": \"Acrylic\", \"weight\": \"1.5kg\"}', 'Single', 'Grey', 'Acrylic', 'Solid', NULL, 'blanket-single-grey-1.jpg', NULL, 1500.00, 1100.00, 700.00, 2100.00, 0.00, 0, 10, 68, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(45, 60, 'TEX-BLA-DBL-BRN', 'BLA-DBL-BRN-001', '{\"color\": \"Brown\", \"material\": \"Acrylic\", \"weight\": \"2.5kg\"}', 'Double', 'Brown', 'Acrylic', 'Solid', NULL, 'blanket-double-brown-1.jpg', NULL, 2000.00, 1500.00, 950.00, 2800.00, 0.00, 0, 10, 55, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(46, 60, 'TEX-BLA-DBL-BLU', 'BLA-DBL-BLU-001', '{\"color\": \"Blue\", \"material\": \"Acrylic\", \"weight\": \"2.5kg\"}', 'Double', 'Blue', 'Acrylic', 'Solid', NULL, 'blanket-double-blue-1.jpg', NULL, 2000.00, 1500.00, 950.00, 2800.00, 0.00, 0, 10, 50, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(47, 60, 'TEX-BLA-DBL-MRN', 'BLA-DBL-MRN-001', '{\"color\": \"Maroon\", \"material\": \"Acrylic\", \"weight\": \"2.5kg\"}', 'Double', 'Maroon', 'Acrylic', 'Solid', NULL, 'blanket-double-maroon-1.jpg', NULL, 2000.00, 1500.00, 950.00, 2800.00, 0.00, 0, 10, 48, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:36', '2026-03-08 14:54:36'),
(48, 62, 'TEX-CUS-16-RED', 'CUS-16-RED-001', '{\"color\": \"Red\", \"pattern\": \"Traditional\", \"closure\": \"Zipper\"}', '16x16', 'Red', NULL, 'Traditional', NULL, 'cushion-16-red-1.jpg', NULL, 230.00, 170.00, 100.00, 320.00, 0.00, 0, 10, 120, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:48', '2026-03-08 14:54:48'),
(49, 62, 'TEX-CUS-16-BLU', 'CUS-16-BLU-001', '{\"color\": \"Blue\", \"pattern\": \"Traditional\", \"closure\": \"Zipper\"}', '16x16', 'Blue', NULL, 'Traditional', NULL, 'cushion-16-blue-1.jpg', NULL, 230.00, 170.00, 100.00, 320.00, 0.00, 0, 10, 115, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:48', '2026-03-08 14:54:48'),
(50, 62, 'TEX-CUS-16-GRN', 'CUS-16-GRN-001', '{\"color\": \"Green\", \"pattern\": \"Traditional\", \"closure\": \"Zipper\"}', '16x16', 'Green', NULL, 'Traditional', NULL, 'cushion-16-green-1.jpg', NULL, 230.00, 170.00, 100.00, 320.00, 0.00, 0, 10, 110, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:48', '2026-03-08 14:54:48'),
(51, 62, 'TEX-CUS-16-YEL', 'CUS-16-YEL-001', '{\"color\": \"Yellow\", \"pattern\": \"Traditional\", \"closure\": \"Zipper\"}', '16x16', 'Yellow', NULL, 'Traditional', NULL, 'cushion-16-yellow-1.jpg', NULL, 230.00, 170.00, 100.00, 320.00, 0.00, 0, 10, 105, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:48', '2026-03-08 14:54:48'),
(52, 63, 'TEX-CUS-18-RED', 'CUS-18-RED-001', '{\"color\": \"Red\", \"pattern\": \"Embroidered\", \"closure\": \"Button\"}', '18x18', 'Red', NULL, 'Embroidered', NULL, 'cushion-18-red-1.jpg', NULL, 260.00, 190.00, 120.00, 360.00, 0.00, 0, 10, 100, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:48', '2026-03-08 14:54:48'),
(53, 63, 'TEX-CUS-18-BLU', 'CUS-18-BLU-001', '{\"color\": \"Blue\", \"pattern\": \"Embroidered\", \"closure\": \"Button\"}', '18x18', 'Blue', NULL, 'Embroidered', NULL, 'cushion-18-blue-1.jpg', NULL, 260.00, 190.00, 120.00, 360.00, 0.00, 0, 10, 95, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:48', '2026-03-08 14:54:48'),
(54, 63, 'TEX-CUS-18-GRN', 'CUS-18-GRN-001', '{\"color\": \"Green\", \"pattern\": \"Embroidered\", \"closure\": \"Button\"}', '18x18', 'Green', NULL, 'Embroidered', NULL, 'cushion-18-green-1.jpg', NULL, 260.00, 190.00, 120.00, 360.00, 0.00, 0, 10, 90, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:48', '2026-03-08 14:54:48'),
(55, 63, 'TEX-CUS-18-PNK', 'CUS-18-PNK-001', '{\"color\": \"Pink\", \"pattern\": \"Embroidered\", \"closure\": \"Button\"}', '18x18', 'Pink', NULL, 'Embroidered', NULL, 'cushion-18-pink-1.jpg', NULL, 260.00, 190.00, 120.00, 360.00, 0.00, 0, 10, 88, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:48', '2026-03-08 14:54:48'),
(56, 72, 'TEX-FAB-COT-WHT', 'FAB-COT-WHT-001', '{\"color\": \"White\", \"width\": \"44 inches\", \"gsm\": \"150\"}', NULL, 'White', NULL, 'Solid', NULL, 'cotton-fabric-white-1.jpg', NULL, 200.00, 150.00, 90.00, 280.00, 0.00, 0, 10, 500, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(57, 72, 'TEX-FAB-COT-BLK', 'FAB-COT-BLK-001', '{\"color\": \"Black\", \"width\": \"44 inches\", \"gsm\": \"150\"}', NULL, 'Black', NULL, 'Solid', NULL, 'cotton-fabric-black-1.jpg', NULL, 200.00, 150.00, 90.00, 280.00, 0.00, 0, 10, 450, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(58, 72, 'TEX-FAB-COT-RED', 'FAB-COT-RED-001', '{\"color\": \"Red\", \"width\": \"44 inches\", \"gsm\": \"150\"}', NULL, 'Red', NULL, 'Solid', NULL, 'cotton-fabric-red-1.jpg', NULL, 200.00, 150.00, 90.00, 280.00, 0.00, 0, 10, 480, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(59, 72, 'TEX-FAB-COT-BLU', 'FAB-COT-BLU-001', '{\"color\": \"Blue\", \"width\": \"44 inches\", \"gsm\": \"150\"}', NULL, 'Blue', NULL, 'Solid', NULL, 'cotton-fabric-blue-1.jpg', NULL, 200.00, 150.00, 90.00, 280.00, 0.00, 0, 10, 460, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(60, 72, 'TEX-FAB-COT-GRN', 'FAB-COT-GRN-001', '{\"color\": \"Green\", \"width\": \"44 inches\", \"gsm\": \"150\"}', NULL, 'Green', NULL, 'Solid', NULL, 'cotton-fabric-green-1.jpg', NULL, 200.00, 150.00, 90.00, 280.00, 0.00, 0, 10, 440, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(61, 73, 'TEX-FAB-SIL-WHT', 'FAB-SIL-WHT-001', '{\"color\": \"White\", \"width\": \"44 inches\", \"gsm\": \"80\"}', NULL, 'White', NULL, 'Solid', NULL, 'silk-fabric-white-1.jpg', NULL, 1550.00, 1150.00, 750.00, 2100.00, 0.00, 0, 10, 200, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(62, 73, 'TEX-FAB-SIL-RED', 'FAB-SIL-RED-001', '{\"color\": \"Red\", \"width\": \"44 inches\", \"gsm\": \"80\"}', NULL, 'Red', NULL, 'Solid', NULL, 'silk-fabric-red-1.jpg', NULL, 1550.00, 1150.00, 750.00, 2100.00, 0.00, 0, 10, 180, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(63, 73, 'TEX-FAB-SIL-GLD', 'FAB-SIL-GLD-001', '{\"color\": \"Gold\", \"width\": \"44 inches\", \"gsm\": \"80\"}', NULL, 'Gold', NULL, 'Solid', NULL, 'silk-fabric-gold-1.jpg', NULL, 1600.00, 1200.00, 780.00, 2200.00, 0.00, 0, 10, 150, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(64, 73, 'TEX-FAB-SIL-GRN', 'FAB-SIL-GRN-001', '{\"color\": \"Green\", \"width\": \"44 inches\", \"gsm\": \"80\"}', NULL, 'Green', NULL, 'Solid', NULL, 'silk-fabric-green-1.jpg', NULL, 1550.00, 1150.00, 750.00, 2100.00, 0.00, 0, 10, 170, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(65, 74, 'TEX-FAB-LIN-NAT', 'FAB-LIN-NAT-001', '{\"color\": \"Natural\", \"width\": \"44 inches\", \"gsm\": \"180\"}', NULL, 'Natural', NULL, 'Solid', NULL, 'linen-fabric-natural-1.jpg', NULL, 800.00, 600.00, 380.00, 1100.00, 0.00, 0, 10, 300, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(66, 74, 'TEX-FAB-LIN-BGE', 'FAB-LIN-BGE-001', '{\"color\": \"Beige\", \"width\": \"44 inches\", \"gsm\": \"180\"}', NULL, 'Beige', NULL, 'Solid', NULL, 'linen-fabric-beige-1.jpg', NULL, 800.00, 600.00, 380.00, 1100.00, 0.00, 0, 10, 280, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(67, 74, 'TEX-FAB-LIN-GRY', 'FAB-LIN-GRY-001', '{\"color\": \"Grey\", \"width\": \"44 inches\", \"gsm\": \"180\"}', NULL, 'Grey', NULL, 'Solid', NULL, 'linen-fabric-grey-1.jpg', NULL, 800.00, 600.00, 380.00, 1100.00, 0.00, 0, 10, 260, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(68, 75, 'TEX-FAB-DEN-LGT', 'FAB-DEN-LGT-001', '{\"color\": \"Light Blue\", \"width\": \"44 inches\", \"weight\": \"Medium\"}', NULL, 'Light Blue', NULL, 'Solid', NULL, 'denim-fabric-light-1.jpg', NULL, 600.00, 450.00, 280.00, 850.00, 0.00, 0, 10, 350, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(69, 75, 'TEX-FAB-DEN-DRK', 'FAB-DEN-DRK-001', '{\"color\": \"Dark Blue\", \"width\": \"44 inches\", \"weight\": \"Heavy\"}', NULL, 'Dark Blue', NULL, 'Solid', NULL, 'denim-fabric-dark-1.jpg', NULL, 650.00, 480.00, 300.00, 900.00, 0.00, 0, 10, 320, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(70, 75, 'TEX-FAB-DEN-BLK', 'FAB-DEN-BLK-001', '{\"color\": \"Black\", \"width\": \"44 inches\", \"weight\": \"Medium\"}', NULL, 'Black', NULL, 'Solid', NULL, 'denim-fabric-black-1.jpg', NULL, 620.00, 460.00, 290.00, 870.00, 0.00, 0, 10, 330, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:54:56', '2026-03-08 14:54:56'),
(71, 76, 'TEX-KUR-COT-M', 'KUR-COT-M-001', '{\"size\": \"M\", \"length\": \"44 inches\", \"sleeve\": \"Full\"}', 'M', 'Assorted', NULL, 'Solid', NULL, 'kurti-m-1.jpg', NULL, 1000.00, 750.00, 450.00, 1400.00, 0.00, 0, 10, 50, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(72, 76, 'TEX-KUR-COT-L', 'KUR-COT-L-001', '{\"size\": \"L\", \"length\": \"45 inches\", \"sleeve\": \"Full\"}', 'L', 'Assorted', NULL, 'Solid', NULL, 'kurti-l-1.jpg', NULL, 1000.00, 750.00, 450.00, 1400.00, 0.00, 0, 10, 60, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(73, 76, 'TEX-KUR-COT-XL', 'KUR-COT-XL-001', '{\"size\": \"XL\", \"length\": \"46 inches\", \"sleeve\": \"Full\"}', 'XL', 'Assorted', NULL, 'Solid', NULL, 'kurti-xl-1.jpg', NULL, 1000.00, 750.00, 450.00, 1400.00, 0.00, 0, 10, 55, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(74, 76, 'TEX-KUR-COT-XXL', 'KUR-COT-XXL-001', '{\"size\": \"XXL\", \"length\": \"47 inches\", \"sleeve\": \"Full\"}', 'XXL', 'Assorted', NULL, 'Solid', NULL, 'kurti-xxl-1.jpg', NULL, 1050.00, 780.00, 470.00, 1450.00, 0.00, 0, 10, 40, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(75, 77, 'TEX-KUR-DES-M', 'KUR-DES-M-001', '{\"size\": \"M\", \"length\": \"44 inches\", \"embroidery\": \"Yes\"}', 'M', 'Multicolor', NULL, 'Embroidered', NULL, 'designer-kurti-m-1.jpg', NULL, 1500.00, 1100.00, 680.00, 2100.00, 0.00, 0, 10, 40, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(76, 77, 'TEX-KUR-DES-L', 'KUR-DES-L-001', '{\"size\": \"L\", \"length\": \"45 inches\", \"embroidery\": \"Yes\"}', 'L', 'Multicolor', NULL, 'Embroidered', NULL, 'designer-kurti-l-1.jpg', NULL, 1500.00, 1100.00, 680.00, 2100.00, 0.00, 0, 10, 45, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(77, 77, 'TEX-KUR-DES-XL', 'KUR-DES-XL-001', '{\"size\": \"XL\", \"length\": \"46 inches\", \"embroidery\": \"Yes\"}', 'XL', 'Multicolor', NULL, 'Embroidered', NULL, 'designer-kurti-xl-1.jpg', NULL, 1500.00, 1100.00, 680.00, 2100.00, 0.00, 0, 10, 35, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(78, 78, 'TEX-SAR-COT-RED', 'SAR-COT-RED-001', '{\"color\": \"Red\", \"border\": \"Contrast\", \"fabric\": \"Cotton\"}', '5.5 meters', 'Red', NULL, 'Traditional', NULL, 'cotton-saree-red-1.jpg', NULL, 1750.00, 1300.00, 800.00, 2400.00, 0.00, 0, 10, 30, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(79, 78, 'TEX-SAR-COT-GRN', 'SAR-COT-GRN-001', '{\"color\": \"Green\", \"border\": \"Contrast\", \"fabric\": \"Cotton\"}', '5.5 meters', 'Green', NULL, 'Traditional', NULL, 'cotton-saree-green-1.jpg', NULL, 1750.00, 1300.00, 800.00, 2400.00, 0.00, 0, 10, 28, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(80, 78, 'TEX-SAR-COT-BLU', 'SAR-COT-BLU-001', '{\"color\": \"Blue\", \"border\": \"Contrast\", \"fabric\": \"Cotton\"}', '5.5 meters', 'Blue', NULL, 'Traditional', NULL, 'cotton-saree-blue-1.jpg', NULL, 1750.00, 1300.00, 800.00, 2400.00, 0.00, 0, 10, 32, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(81, 79, 'TEX-SAR-SIL-RED', 'SAR-SIL-RED-001', '{\"color\": \"Red\", \"border\": \"Zari\", \"fabric\": \"Silk\"}', '5.5 meters', 'Red', NULL, 'Traditional', NULL, 'silk-saree-red-1.jpg', NULL, 6000.00, 4500.00, 2800.00, 8500.00, 0.00, 0, 10, 20, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(82, 79, 'TEX-SAR-SIL-GLD', 'SAR-SIL-GLD-001', '{\"color\": \"Gold\", \"border\": \"Zari\", \"fabric\": \"Silk\"}', '5.5 meters', 'Gold', NULL, 'Traditional', NULL, 'silk-saree-gold-1.jpg', NULL, 6200.00, 4600.00, 2900.00, 8700.00, 0.00, 0, 10, 18, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(83, 79, 'TEX-SAR-SIL-GRN', 'SAR-SIL-GRN-001', '{\"color\": \"Green\", \"border\": \"Zari\", \"fabric\": \"Silk\"}', '5.5 meters', 'Green', NULL, 'Traditional', NULL, 'silk-saree-green-1.jpg', NULL, 6000.00, 4500.00, 2800.00, 8500.00, 0.00, 0, 10, 22, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(84, 80, 'TEX-SAR-DES-PNK', 'SAR-DES-PNK-001', '{\"color\": \"Pink\", \"work\": \"Embroidered\", \"fabric\": \"Georgette\"}', '5.5 meters', 'Pink', NULL, 'Embroidered', NULL, 'designer-saree-pink-1.jpg', NULL, 4300.00, 3200.00, 2000.00, 6000.00, 0.00, 0, 10, 15, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(85, 80, 'TEX-SAR-DES-BLU', 'SAR-DES-BLU-001', '{\"color\": \"Blue\", \"work\": \"Embroidered\", \"fabric\": \"Georgette\"}', '5.5 meters', 'Blue', NULL, 'Embroidered', NULL, 'designer-saree-blue-1.jpg', NULL, 4300.00, 3200.00, 2000.00, 6000.00, 0.00, 0, 10, 14, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(86, 80, 'TEX-SAR-DES-MRN', 'SAR-DES-MRN-001', '{\"color\": \"Maroon\", \"work\": \"Embroidered\", \"fabric\": \"Georgette\"}', '5.5 meters', 'Maroon', NULL, 'Embroidered', NULL, 'designer-saree-maroon-1.jpg', NULL, 4300.00, 3200.00, 2000.00, 6000.00, 0.00, 0, 10, 16, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(87, 81, 'TEX-SHT-MEN-M', 'SHT-MEN-M-001', '{\"size\": \"M\", \"chest\": \"38 inches\", \"sleeve\": \"Full\"}', 'M', 'Assorted', NULL, 'Solid', NULL, 'mens-shirt-m-1.jpg', NULL, 900.00, 650.00, 400.00, 1250.00, 0.00, 0, 10, 45, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(88, 81, 'TEX-SHT-MEN-L', 'SHT-MEN-L-001', '{\"size\": \"L\", \"chest\": \"40 inches\", \"sleeve\": \"Full\"}', 'L', 'Assorted', NULL, 'Solid', NULL, 'mens-shirt-l-1.jpg', NULL, 900.00, 650.00, 400.00, 1250.00, 0.00, 0, 10, 50, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(89, 81, 'TEX-SHT-MEN-XL', 'SHT-MEN-XL-001', '{\"size\": \"XL\", \"chest\": \"42 inches\", \"sleeve\": \"Full\"}', 'XL', 'Assorted', NULL, 'Solid', NULL, 'mens-shirt-xl-1.jpg', NULL, 900.00, 650.00, 400.00, 1250.00, 0.00, 0, 10, 40, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(90, 81, 'TEX-SHT-MEN-XXL', 'SHT-MEN-XXL-001', '{\"size\": \"XXL\", \"chest\": \"44 inches\", \"sleeve\": \"Full\"}', 'XXL', 'Assorted', NULL, 'Solid', NULL, 'mens-shirt-xxl-1.jpg', NULL, 950.00, 680.00, 420.00, 1300.00, 0.00, 0, 10, 30, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(91, 82, 'TEX-SHT-FRM-M', 'SHT-FRM-M-001', '{\"size\": \"M\", \"chest\": \"38 inches\", \"fit\": \"Regular\"}', 'M', 'White/Blue', NULL, 'Solid', NULL, 'formal-shirt-m-1.jpg', NULL, 1100.00, 800.00, 500.00, 1500.00, 0.00, 0, 10, 40, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(92, 82, 'TEX-SHT-FRM-L', 'SHT-FRM-L-001', '{\"size\": \"L\", \"chest\": \"40 inches\", \"fit\": \"Regular\"}', 'L', 'White/Blue', NULL, 'Solid', NULL, 'formal-shirt-l-1.jpg', NULL, 1100.00, 800.00, 500.00, 1500.00, 0.00, 0, 10, 45, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(93, 82, 'TEX-SHT-FRM-XL', 'SHT-FRM-XL-001', '{\"size\": \"XL\", \"chest\": \"42 inches\", \"fit\": \"Regular\"}', 'XL', 'White/Blue', NULL, 'Solid', NULL, 'formal-shirt-xl-1.jpg', NULL, 1100.00, 800.00, 500.00, 1500.00, 0.00, 0, 10, 35, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(94, 83, 'TEX-TEE-COT-M', 'TEE-COT-M-001', '{\"size\": \"M\", \"chest\": \"38 inches\", \"neck\": \"Round\"}', 'M', 'Assorted', NULL, 'Solid', NULL, 'tshirt-m-1.jpg', NULL, 500.00, 350.00, 200.00, 700.00, 0.00, 0, 10, 100, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(95, 83, 'TEX-TEE-COT-L', 'TEE-COT-L-001', '{\"size\": \"L\", \"chest\": \"40 inches\", \"neck\": \"Round\"}', 'L', 'Assorted', NULL, 'Solid', NULL, 'tshirt-l-1.jpg', NULL, 500.00, 350.00, 200.00, 700.00, 0.00, 0, 10, 110, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(96, 83, 'TEX-TEE-COT-XL', 'TEE-COT-XL-001', '{\"size\": \"XL\", \"chest\": \"42 inches\", \"neck\": \"Round\"}', 'XL', 'Assorted', NULL, 'Solid', NULL, 'tshirt-xl-1.jpg', NULL, 500.00, 350.00, 200.00, 700.00, 0.00, 0, 10, 90, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(97, 83, 'TEX-TEE-COT-XXL', 'TEE-COT-XXL-001', '{\"size\": \"XXL\", \"chest\": \"44 inches\", \"neck\": \"Round\"}', 'XXL', 'Assorted', NULL, 'Solid', NULL, 'tshirt-xxl-1.jpg', NULL, 550.00, 380.00, 220.00, 750.00, 0.00, 0, 10, 70, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(98, 84, 'TEX-TEE-POL-M', 'TEE-POL-M-001', '{\"size\": \"M\", \"chest\": \"38 inches\", \"collar\": \"Polo\"}', 'M', 'Assorted', NULL, 'Solid', NULL, 'polo-tshirt-m-1.jpg', NULL, 600.00, 420.00, 250.00, 850.00, 0.00, 0, 10, 80, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(99, 84, 'TEX-TEE-POL-L', 'TEE-POL-L-001', '{\"size\": \"L\", \"chest\": \"40 inches\", \"collar\": \"Polo\"}', 'L', 'Assorted', NULL, 'Solid', NULL, 'polo-tshirt-l-1.jpg', NULL, 600.00, 420.00, 250.00, 850.00, 0.00, 0, 10, 85, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(100, 84, 'TEX-TEE-POL-XL', 'TEE-POL-XL-001', '{\"size\": \"XL\", \"chest\": \"42 inches\", \"collar\": \"Polo\"}', 'XL', 'Assorted', NULL, 'Solid', NULL, 'polo-tshirt-xl-1.jpg', NULL, 600.00, 420.00, 250.00, 850.00, 0.00, 0, 10, 75, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(101, 93, 'TEX-DUP-COT-RED', 'DUP-COT-RED-001', '{\"color\": \"Red\", \"length\": \"2.5 meters\", \"border\": \"Tassels\"}', '2.5 meters', 'Red', NULL, 'Solid', NULL, 'dupatta-red-1.jpg', NULL, 900.00, 650.00, 400.00, 1250.00, 0.00, 0, 10, 60, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(102, 93, 'TEX-DUP-COT-GRN', 'DUP-COT-GRN-001', '{\"color\": \"Green\", \"length\": \"2.5 meters\", \"border\": \"Tassels\"}', '2.5 meters', 'Green', NULL, 'Solid', NULL, 'dupatta-green-1.jpg', NULL, 900.00, 650.00, 400.00, 1250.00, 0.00, 0, 10, 55, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(103, 93, 'TEX-DUP-COT-BLU', 'DUP-COT-BLU-001', '{\"color\": \"Blue\", \"length\": \"2.5 meters\", \"border\": \"Tassels\"}', '2.5 meters', 'Blue', NULL, 'Solid', NULL, 'dupatta-blue-1.jpg', NULL, 900.00, 650.00, 400.00, 1250.00, 0.00, 0, 10, 58, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(104, 94, 'TEX-STO-COT-RED', 'STO-COT-RED-001', '{\"color\": \"Red\", \"length\": \"Standard\", \"pattern\": \"Printed\"}', 'Standard', 'Red', NULL, 'Printed', NULL, 'stole-red-1.jpg', NULL, 600.00, 450.00, 280.00, 850.00, 0.00, 0, 10, 70, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(105, 94, 'TEX-STO-COT-BLU', 'STO-COT-BLU-001', '{\"color\": \"Blue\", \"length\": \"Standard\", \"pattern\": \"Printed\"}', 'Standard', 'Blue', NULL, 'Printed', NULL, 'stole-blue-1.jpg', NULL, 600.00, 450.00, 280.00, 850.00, 0.00, 0, 10, 65, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(106, 94, 'TEX-STO-COT-GRN', 'STO-COT-GRN-001', '{\"color\": \"Green\", \"length\": \"Standard\", \"pattern\": \"Printed\"}', 'Standard', 'Green', NULL, 'Printed', NULL, 'stole-green-1.jpg', NULL, 600.00, 450.00, 280.00, 850.00, 0.00, 0, 10, 68, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:17', '2026-03-08 14:57:17'),
(107, 95, 'JUT-BAG-MED-NAT', 'BAG-MED-NAT-001', '{\"color\": \"Natural\", \"handles\": \"Long\", \"capacity\": \"5kg\"}', 'Medium', 'Natural', NULL, 'Plain', NULL, 'jute-bag-medium-natural-1.jpg', NULL, 160.00, 120.00, 70.00, 220.00, 0.00, 0, 10, 300, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(108, 95, 'JUT-BAG-MED-RED', 'BAG-MED-RED-001', '{\"color\": \"Red\", \"handles\": \"Long\", \"capacity\": \"5kg\"}', 'Medium', 'Red', NULL, 'Colorful', NULL, 'jute-bag-medium-red-1.jpg', NULL, 180.00, 135.00, 80.00, 250.00, 0.00, 0, 10, 250, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(109, 95, 'JUT-BAG-MED-BLU', 'BAG-MED-BLU-001', '{\"color\": \"Blue\", \"handles\": \"Long\", \"capacity\": \"5kg\"}', 'Medium', 'Blue', NULL, 'Colorful', NULL, 'jute-bag-medium-blue-1.jpg', NULL, 180.00, 135.00, 80.00, 250.00, 0.00, 0, 10, 240, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(110, 96, 'JUT-BAG-LRG-NAT', 'BAG-LRG-NAT-001', '{\"color\": \"Natural\", \"handles\": \"Reinforced\", \"capacity\": \"10kg\"}', 'Large', 'Natural', NULL, 'Plain', NULL, 'jute-bag-large-natural-1.jpg', NULL, 220.00, 160.00, 95.00, 300.00, 0.00, 0, 10, 250, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(111, 96, 'JUT-BAG-LRG-GRN', 'BAG-LRG-GRN-001', '{\"color\": \"Green\", \"handles\": \"Reinforced\", \"capacity\": \"10kg\"}', 'Large', 'Green', NULL, 'Colorful', NULL, 'jute-bag-large-green-1.jpg', NULL, 240.00, 175.00, 105.00, 330.00, 0.00, 0, 10, 200, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(112, 97, 'JUT-BAG-PRI-FLO', 'BAG-PRI-FLO-001', '{\"print\": \"Floral\", \"color\": \"Multicolor\", \"capacity\": \"5kg\"}', 'Medium', 'Multicolor', NULL, 'Floral Print', NULL, 'jute-printed-floral-1.jpg', NULL, 200.00, 150.00, 90.00, 280.00, 0.00, 0, 10, 200, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(113, 97, 'JUT-BAG-PRI-GEO', 'BAG-PRI-GEO-001', '{\"print\": \"Geometric\", \"color\": \"Multicolor\", \"capacity\": \"5kg\"}', 'Medium', 'Multicolor', NULL, 'Geometric Print', NULL, 'jute-printed-geometric-1.jpg', NULL, 200.00, 150.00, 90.00, 280.00, 0.00, 0, 10, 180, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(114, 97, 'JUT-BAG-PRI-ETH', 'BAG-PRI-ETH-001', '{\"print\": \"Ethnic\", \"color\": \"Multicolor\", \"capacity\": \"5kg\"}', 'Medium', 'Multicolor', NULL, 'Ethnic Print', NULL, 'jute-printed-ethnic-1.jpg', NULL, 210.00, 155.00, 95.00, 290.00, 0.00, 0, 10, 170, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(115, 104, 'JUT-WIN-SGL-NAT', 'WIN-SGL-NAT-001', '{\"design\": \"Plain\", \"handle\": \"Yes\", \"capacity\": \"1 bottle\"}', 'Single', 'Natural', NULL, 'Plain', NULL, 'jute-wine-single-natural-1.jpg', NULL, 120.00, 90.00, 50.00, 170.00, 0.00, 0, 10, 250, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(116, 104, 'JUT-WIN-SGL-RED', 'WIN-SGL-RED-001', '{\"design\": \"Red\", \"handle\": \"Yes\", \"capacity\": \"1 bottle\"}', 'Single', 'Red', NULL, 'Colorful', NULL, 'jute-wine-single-red-1.jpg', NULL, 130.00, 95.00, 55.00, 180.00, 0.00, 0, 10, 200, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(117, 105, 'JUT-WIN-DBL-NAT', 'WIN-DBL-NAT-001', '{\"design\": \"Plain\", \"handle\": \"Yes\", \"capacity\": \"2 bottles\"}', 'Double', 'Natural', NULL, 'Plain', NULL, 'jute-wine-double-natural-1.jpg', NULL, 180.00, 130.00, 75.00, 250.00, 0.00, 0, 10, 180, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(118, 105, 'JUT-WIN-DBL-BRN', 'WIN-DBL-BRN-001', '{\"design\": \"Brown\", \"handle\": \"Yes\", \"capacity\": \"2 bottles\"}', 'Double', 'Brown', NULL, 'Colorful', NULL, 'jute-wine-double-brown-1.jpg', NULL, 190.00, 140.00, 80.00, 260.00, 0.00, 0, 10, 160, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(119, 114, 'JUT-GFT-SML-NAT', 'GFT-SML-NAT-001', '{\"color\": \"Natural\", \"closure\": \"None\", \"size\": \"6x8 inches\"}', 'Small', 'Natural', NULL, 'Plain', NULL, 'jute-gift-small-natural-1.jpg', NULL, 95.00, 70.00, 40.00, 130.00, 0.00, 0, 10, 400, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(120, 114, 'JUT-GFT-SML-PNK', 'GFT-SML-PNK-001', '{\"color\": \"Pink\", \"closure\": \"None\", \"size\": \"6x8 inches\"}', 'Small', 'Pink', NULL, 'Colorful', NULL, 'jute-gift-small-pink-1.jpg', NULL, 100.00, 75.00, 43.00, 140.00, 0.00, 0, 10, 350, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(121, 115, 'JUT-GFT-MED-NAT', 'GFT-MED-NAT-001', '{\"color\": \"Natural\", \"closure\": \"None\", \"size\": \"8x10 inches\"}', 'Medium', 'Natural', NULL, 'Plain', NULL, 'jute-gift-medium-natural-1.jpg', NULL, 130.00, 95.00, 55.00, 180.00, 0.00, 0, 10, 350, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(122, 115, 'JUT-GFT-MED-RED', 'GFT-MED-RED-001', '{\"color\": \"Red\", \"closure\": \"None\", \"size\": \"8x10 inches\"}', 'Medium', 'Red', NULL, 'Colorful', NULL, 'jute-gift-medium-red-1.jpg', NULL, 140.00, 100.00, 58.00, 190.00, 0.00, 0, 10, 300, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(123, 116, 'JUT-GFT-LRG-NAT', 'GFT-LRG-NAT-001', '{\"color\": \"Natural\", \"closure\": \"None\", \"size\": \"10x12 inches\"}', 'Large', 'Natural', NULL, 'Plain', NULL, 'jute-gift-large-natural-1.jpg', NULL, 180.00, 130.00, 75.00, 250.00, 0.00, 0, 10, 250, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(124, 116, 'JUT-GFT-LRG-BLU', 'GFT-LRG-BLU-001', '{\"color\": \"Blue\", \"closure\": \"None\", \"size\": \"10x12 inches\"}', 'Large', 'Blue', NULL, 'Colorful', NULL, 'jute-gift-large-blue-1.jpg', NULL, 190.00, 140.00, 80.00, 260.00, 0.00, 0, 10, 220, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(125, 131, 'JUT-CUS-16-NAT', 'CUS-16-NAT-001', '{\"color\": \"Natural\", \"closure\": \"Button\", \"size\": \"16x16\"}', '16x16', 'Natural', NULL, 'Plain', NULL, 'jute-cushion-16-natural-1.jpg', NULL, 100.00, 75.00, 45.00, 140.00, 0.00, 0, 10, 200, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(126, 131, 'JUT-CUS-16-BRN', 'CUS-16-BRN-001', '{\"color\": \"Brown\", \"closure\": \"Button\", \"size\": \"16x16\"}', '16x16', 'Brown', NULL, 'Plain', NULL, 'jute-cushion-16-brown-1.jpg', NULL, 110.00, 80.00, 48.00, 150.00, 0.00, 0, 10, 180, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(127, 132, 'JUT-CUS-18-NAT', 'CUS-18-NAT-001', '{\"color\": \"Natural\", \"pattern\": \"Plain\", \"closure\": \"Button\"}', '18x18', 'Natural', NULL, 'Plain', NULL, 'jute-cushion-18-natural-1.jpg', NULL, 120.00, 90.00, 52.00, 170.00, 0.00, 0, 10, 180, 5, 1, NULL, NULL, 1, 1, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33'),
(128, 132, 'JUT-CUS-18-EMB', 'CUS-18-EMB-001', '{\"color\": \"Natural\", \"pattern\": \"Embroidered\", \"closure\": \"Button\"}', '18x18', 'Natural', NULL, 'Embroidered', NULL, 'jute-cushion-18-embroidered-1.jpg', NULL, 150.00, 110.00, 65.00, 200.00, 0.00, 0, 10, 150, 5, 1, NULL, NULL, 1, 0, 0, '2026-03-08 14:57:33', '2026-03-08 14:57:33');

-- --------------------------------------------------------

--
-- Table structure for table `return_requests`
--

CREATE TABLE `return_requests` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `return_reason` varchar(255) NOT NULL,
  `return_quantity` int(11) NOT NULL,
  `return_amount` decimal(10,2) DEFAULT NULL,
  `request_status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `pickup_address_id` int(11) DEFAULT NULL,
  `pickup_scheduled` date DEFAULT NULL,
  `pickup_status` varchar(50) DEFAULT NULL,
  `refund_transaction_id` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_requests`
--

INSERT INTO `return_requests` (`id`, `order_id`, `order_item_id`, `user_id`, `return_reason`, `return_quantity`, `return_amount`, `request_status`, `pickup_address_id`, `pickup_scheduled`, `pickup_status`, `refund_transaction_id`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 'Damaged during shipping - small crack on base', 1, 899.00, 'approved', 1, '2026-02-21', 'completed', '1', 'Item had crack on base. Return approved.', '2026-02-14 08:51:01', '2026-02-21 08:51:01'),
(2, 5, 5, 3, 'Quality not as expected - fabric has printing defects', 25, 21225.00, 'pending', 2, '2026-03-10', 'scheduled', NULL, '25 pieces have printing defects. Awaiting inspection.', '2026-03-07 08:51:01', '2026-03-08 08:51:01'),
(3, 3, 3, 4, 'Wrong size delivered - ordered 5 pieces but got 3 pieces', 2, 958.40, 'approved', 3, '2026-03-09', 'scheduled', NULL, 'Only 3 diya sets delivered instead of 5.', '2026-03-06 08:51:01', '2026-03-08 08:51:01');

-- --------------------------------------------------------

--
-- Table structure for table `review_helpfulness`
--

CREATE TABLE `review_helpfulness` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_helpful` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_helpfulness`
--

INSERT INTO `review_helpfulness` (`id`, `review_id`, `user_id`, `is_helpful`, `created_at`) VALUES
(1, 1, 3, 1, '2026-02-22 08:41:29'),
(2, 1, 4, 1, '2026-02-23 08:41:29'),
(3, 2, 2, 1, '2026-02-27 08:41:29'),
(4, 3, 2, 1, '2026-02-17 08:41:29'),
(5, 3, 3, 1, '2026-02-18 08:41:29'),
(6, 4, 3, 1, '2026-03-02 08:41:29'),
(7, 4, 4, 1, '2026-03-03 08:41:29');

-- --------------------------------------------------------

--
-- Table structure for table `serviceable_pincodes`
--

CREATE TABLE `serviceable_pincodes` (
  `id` int(11) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `delivery_days` int(11) DEFAULT 5,
  `cod_available` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `serviceable_pincodes`
--

INSERT INTO `serviceable_pincodes` (`id`, `pincode`, `city`, `state`, `region`, `delivery_days`, `cod_available`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '110001', 'Delhi', 'Delhi', 'North', 2, 1, 1, NULL, NULL),
(2, '110016', 'Delhi', 'Delhi', 'North', 2, 1, 1, NULL, NULL),
(3, '400001', 'Mumbai', 'Maharashtra', 'West', 3, 1, 1, NULL, NULL),
(4, '380001', 'Ahmedabad', 'Gujarat', 'West', 3, 1, 1, NULL, NULL),
(5, '700001', 'Kolkata', 'West Bengal', 'East', 4, 1, 1, NULL, NULL),
(6, '600001', 'Chennai', 'Tamil Nadu', 'South', 4, 1, 1, NULL, NULL),
(7, '500001', 'Hyderabad', 'Telangana', 'South', 3, 1, 1, NULL, NULL),
(8, '560001', 'Bangalore', 'Karnataka', 'South', 3, 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sub_categories`
--

CREATE TABLE `sub_categories` (
  `id` int(11) NOT NULL,
  `main_category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_code` varchar(10) DEFAULT NULL,
  `full_category_code` varchar(20) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `banner_image` varchar(500) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `path` varchar(500) DEFAULT NULL,
  `min_order_quantity` int(11) DEFAULT 1,
  `allows_single_purchase` tinyint(1) DEFAULT 1,
  `allows_bulk_purchase` tinyint(1) DEFAULT 1,
  `default_bulk_min_quantity` int(11) DEFAULT 10,
  `bulk_pricing_model` enum('fixed','tiered','range') DEFAULT 'fixed',
  `bulk_tier_description` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `show_in_menu` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `total_products` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_categories`
--

INSERT INTO `sub_categories` (`id`, `main_category_id`, `name`, `slug`, `description`, `category_code`, `full_category_code`, `icon`, `image`, `banner_image`, `parent_id`, `level`, `path`, `min_order_quantity`, `allows_single_purchase`, `allows_bulk_purchase`, `default_bulk_min_quantity`, `bulk_pricing_model`, `bulk_tier_description`, `meta_title`, `meta_description`, `meta_keywords`, `is_active`, `show_in_menu`, `is_featured`, `sort_order`, `total_products`, `created_at`, `updated_at`) VALUES
(1, 1, 'Wooden Items', 'wooden-items', 'Handcrafted wooden decor, furniture, and utility items', NULL, NULL, NULL, '1774029963_9167.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, '2026-03-08 14:25:15', '2026-03-20 18:06:03'),
(2, 1, 'Brass & Metal Items', 'brass-metal-items', 'Brass, copper, and metal handicrafts for home and pooja', NULL, NULL, NULL, '1774030000_5395.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 2, 0, '2026-03-08 14:25:15', '2026-03-20 18:06:40'),
(3, 1, 'Marble & Stone Items', 'marble-stone-items', 'Marble idols, stone decor and fountains', NULL, NULL, NULL, '1774030133_7325.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 3, 0, '2026-03-08 14:25:15', '2026-03-20 18:08:53'),
(4, 1, 'Terracotta & Pottery', 'terracotta-pottery', 'Traditional terracotta pots, wall plates and decorative items', NULL, NULL, NULL, '1774030264_7251.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 4, 0, '2026-03-08 14:25:15', '2026-03-20 18:11:04'),
(5, 1, 'Resin & Decorative Items', 'resin-decorative-items', 'Modern resin showpieces, Buddha statues and decorative items', NULL, NULL, NULL, '1774030394_2692.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 5, 0, '2026-03-08 14:25:15', '2026-03-20 18:13:14'),
(6, 2, 'Bed Linens', 'bed-linens', 'Bedsheets, comforters, quilts and dohars', NULL, NULL, NULL, '1774029905_5018.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, '2026-03-08 14:25:15', '2026-03-20 18:05:05'),
(7, 2, 'Bath Linens', 'bath-linens', 'Towels, bathrobes and bathroom textiles', NULL, NULL, NULL, '1774029975_4023.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 2, 0, '2026-03-08 14:25:15', '2026-03-20 18:06:15'),
(8, 2, 'Kitchen Linens', 'kitchen-linens', 'Aprons, kitchen towels, table napkins', NULL, NULL, NULL, '1774030080_5583.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 3, 0, '2026-03-08 14:25:15', '2026-03-20 18:08:00'),
(9, 2, 'Home Decor Textiles', 'home-decor-textiles', 'Curtains, cushion covers, table cloths, rugs', NULL, NULL, NULL, '1774030191_4706.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 4, 0, '2026-03-08 14:25:15', '2026-03-20 18:09:51'),
(10, 2, 'Fabrics', 'fabrics', 'Cotton, silk, linen and denim fabrics by meter', NULL, NULL, NULL, '1774030304_9835.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 5, 0, '2026-03-08 14:25:15', '2026-03-20 18:11:44'),
(11, 2, 'Apparel - Women', 'apparel-women', 'Kurtis, sarees, night suits for women', NULL, NULL, NULL, '1774030430_8897.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 6, 0, '2026-03-08 14:25:15', '2026-03-20 18:13:50'),
(12, 2, 'Apparel - Men', 'apparel-men', 'Shirts, t-shirts, track suits for men', NULL, NULL, NULL, '1774031365_2436.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 7, 0, '2026-03-08 14:25:15', '2026-03-20 18:29:25'),
(13, 2, 'Winter Wear', 'winter-wear', 'Shawls, blankets, baby blankets', NULL, NULL, NULL, '1774030930_6908.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 8, 0, '2026-03-08 14:25:15', '2026-03-20 18:22:10'),
(14, 2, 'Accessories', 'accessories', 'Dupattas, stoles, scarves', NULL, NULL, NULL, '1774030945_1699.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 9, 0, '2026-03-08 14:25:15', '2026-03-20 18:22:25'),
(15, 1, 'Jute Bags', 'jute-bags', 'Shopping bags, promotional bags, gift bags and more', NULL, NULL, NULL, '1774029951_9762.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, '2026-03-08 14:25:15', '2026-03-20 18:05:51'),
(16, 1, 'Jute Home Decor', 'jute-home-decor', 'Jute carpets, rugs, wall hangings, lampshades', NULL, NULL, NULL, '1774030029_8197.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 2, 0, '2026-03-08 14:25:15', '2026-03-20 18:07:09'),
(17, 1, 'Jute Storage & Organization', 'jute-storage-organization', 'Storage boxes, baskets, organizers', NULL, NULL, NULL, '1774030057_2907.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 3, 0, '2026-03-08 14:25:15', '2026-03-20 18:07:37'),
(18, 1, 'Jute Accessories', 'jute-accessories', 'Pouches, keychains, coasters, small jute items', NULL, NULL, NULL, '1774030210_9995.png', NULL, NULL, 1, NULL, 1, 1, 1, 10, 'fixed', NULL, NULL, NULL, NULL, 1, 1, 0, 4, 0, '2026-03-08 14:25:15', '2026-03-20 18:10:10'),
(19, 3, 'Homemade Jams', 'homemade-jams', 'Delicious homemade jams made from fresh fruits. Available in 200gm and 900gm variants. Perfect for breakfast and snacks.', 'FMCG', 'FMCG-JAM', NULL, '1774029921_6444.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Homemade Jams - Natural Fruit Preserves', 'Buy delicious homemade jams in 200gm and 900gm packs. Made from fresh fruits with no preservatives.', 'jam, homemade jam, fruit jam, strawberry jam, mixed fruit jam', 1, 1, 1, 1, 0, '2026-03-20 16:11:41', '2026-03-20 18:05:21'),
(20, 3, 'Pickles', 'pickles', 'Traditional homemade pickles in various flavors. Available in 1kg, 3kg, 5kg, and 10kg packs. Authentic taste with natural ingredients.', 'FMCG', 'FMCG-PICKLE', NULL, '1774030044_5111.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Traditional Homemade Pickles', 'Buy authentic homemade pickles in 1kg, 3kg, 5kg, and 10kg packs. Mango, lemon, mixed vegetable pickles available.', 'pickle, achar, mango pickle, lemon pickle, mixed pickle, homemade pickle', 1, 1, 1, 2, 0, '2026-03-20 16:11:41', '2026-03-20 18:07:24'),
(21, 3, 'Pure Ghee', 'pure-ghee', 'Premium quality pure cow ghee. Available in 1kg, 3kg, 5kg, and 10kg packs. Made from A2 milk using traditional methods.', 'FMCG', 'FMCG-GHEE', NULL, '1774030156_9894.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Pure Cow Ghee - A2 Desi Ghee', 'Buy pure cow ghee in 1kg, 3kg, 5kg, and 10kg packs. Traditional bilona method ghee with rich aroma.', 'ghee, desi ghee, cow ghee, A2 ghee, pure ghee, clarified butter', 1, 1, 1, 3, 0, '2026-03-20 16:11:41', '2026-03-20 18:09:16'),
(22, 3, 'Fresh Butter', 'fresh-butter', 'Creamy fresh butter made from pure milk. Available in 500gm to 2kg packs. Perfect for daily use.', 'FMCG', 'FMCG-BUTTER', NULL, '1774030167_6445.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Fresh Creamy Butter', 'Buy fresh butter in 500gm to 2kg packs. Made from pure milk with rich taste and creamy texture.', 'butter, fresh butter, white butter, dairy butter, unsalted butter', 1, 1, 1, 4, 0, '2026-03-20 16:11:41', '2026-03-20 18:09:27'),
(23, 3, 'Fresh Paneer', 'fresh-paneer', 'Soft and fresh paneer made from pure cow milk. Available up to 10kg packs. Perfect for Indian cuisine.', 'FMCG', 'FMCG-PANEER', NULL, '1774030345_1615.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Fresh Cottage Cheese (Paneer)', 'Buy fresh paneer up to 10kg packs. Soft, creamy, and perfect for all Indian dishes.', 'paneer, cottage cheese, fresh paneer, homemade paneer, Indian cheese', 1, 1, 1, 5, 0, '2026-03-20 16:11:41', '2026-03-20 18:12:25'),
(24, 3, 'Dishwashing Liquid', 'dishwashing-liquid', 'Effective dishwashing liquid that cuts through grease. Available in 1L, 5L, and 10L packs. Gentle on hands.', 'FMCG', 'FMCG-DISHWASH', NULL, '1774030826_5412.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Dishwashing Liquid - Grease Cutter', 'Buy dishwashing liquid in 1L, 5L, and 10L packs. Effective grease cutting formula with lemon freshness.', 'dishwash, dishwashing liquid, dish soap, kitchen cleaner, dish detergent', 1, 1, 1, 6, 0, '2026-03-20 16:11:41', '2026-03-20 18:20:26'),
(25, 3, 'Incense Sticks', 'incense-sticks', 'Premium quality incense sticks for aromatherapy and religious purposes. Available in packs of 100 sticks.', 'FMCG', 'FMCG-INCENSE', NULL, '1774030870_6726.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Premium Incense Sticks', 'Buy incense sticks in packs of 100. Available in various fragrances like sandalwood, rose, lavender.', 'incense sticks, agarbatti, dhoop, fragrance sticks, aromatic sticks', 1, 1, 1, 7, 0, '2026-03-20 16:11:41', '2026-03-20 18:21:10'),
(26, 3, 'Scented Candles', 'scented-candles', 'Beautiful scented candles for home decor and aromatherapy. Available in packs of 100 pieces. Various fragrances available.', 'FMCG', 'FMCG-CANDLES', NULL, '1774030902_4609.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Scented Decorative Candles', 'Buy scented candles in bulk packs of 100 pieces. Perfect for home decor, gifting, and aromatherapy.', 'scented candles, decorative candles, aroma candles, pillar candles, tea lights', 1, 1, 1, 8, 0, '2026-03-20 16:11:41', '2026-03-20 18:21:42'),
(27, 3, 'Spices & Masala', 'spices-masala', 'Pure and aromatic spices for authentic Indian cooking. Includes turmeric, cumin, coriander, and more.', 'FMCG', 'FMCG-SPICES', NULL, '1774031000_6763.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Pure Spices & Masala', 'Buy pure spices like हल्दी (turmeric), जीरा (cumin), धनिया (coriander) and more. Perfect for Indian cuisine.', 'spices, masala, turmeric, haldi, cumin, jeera, coriander, dhaniya, garam masala', 1, 1, 1, 9, 0, '2026-03-20 16:11:41', '2026-03-20 18:23:20'),
(28, 3, 'Liquid Detergents', 'liquid-detergents', 'Powerful liquid detergent for laundry. Effective stain removal with gentle care for fabrics.', 'FMCG', 'FMCG-DETERGENT', NULL, '1774031019_2468.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Liquid Detergent for Laundry', 'Buy liquid detergent for effective stain removal. Gentle on fabrics and suitable for all washing machines.', 'liquid detergent, laundry detergent, washing liquid, fabric cleaner', 1, 1, 1, 10, 0, '2026-03-20 16:11:41', '2026-03-20 18:23:39'),
(29, 3, 'Floor Cleaners', 'floor-cleaners', 'Effective floor cleaners for sparkling clean floors. Removes dirt and leaves fresh fragrance.', 'FMCG', 'FMCG-FLOOR', NULL, '1774031038_6670.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Floor Cleaners - Floor Disinfectant', 'Buy floor cleaners for sparkling clean floors. Removes dirt, disinfects, and leaves fresh fragrance.', 'floor cleaner, floor disinfectant, floor wash, tile cleaner, floor polish', 1, 1, 1, 11, 0, '2026-03-20 16:11:41', '2026-03-20 18:23:58'),
(30, 3, 'Room Fresheners', 'room-fresheners', 'Long-lasting room fresheners for a pleasant home environment. Available in various fragrances.', 'FMCG', 'FMCG-ROOMFRESH', NULL, '1774031077_3967.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Room Fresheners - Air Fresheners', 'Buy room fresheners for a pleasant home environment. Available in various fragrances like lavender, citrus, floral.', 'room freshener, air freshener, home fragrance, odor eliminator, spray freshener', 1, 1, 1, 12, 0, '2026-03-20 16:11:41', '2026-03-20 18:24:37'),
(31, 3, 'Papad', 'papad', 'Crispy and delicious papad made from urad dal and spices. Available in jeera, mirch, and plain flavors.', 'FMCG', 'FMCG-PAPAD', NULL, '1774031092_8235.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Crispy Papad - Indian Appetizer', 'Buy crispy papad in jeera, mirch, and plain flavors. Perfect as appetizer or side dish.', 'papad, papadam, appalam, poppadom, jeera papad, mirch papad', 1, 1, 1, 13, 0, '2026-03-20 16:11:41', '2026-03-20 18:24:52'),
(32, 3, 'Soda Powder', 'soda-powder', 'Pure soda powder for cooking and cleaning purposes. Food grade quality.', 'FMCG', 'FMCG-SODA', NULL, '1774031113_9352.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Food Grade Soda Powder', 'Buy pure soda powder for cooking and cleaning. Food grade quality safe for consumption.', 'soda powder, baking soda, cooking soda, sodium bicarbonate, meetha soda', 1, 1, 1, 14, 0, '2026-03-20 16:11:41', '2026-03-20 18:25:13'),
(33, 1, 'Grocery Bags', 'grocery-bags', 'Eco-friendly grocery bags made from high-quality material. Available in 500gm and 1000gm capacities.', 'FMCG', 'FMCG-BAGS', NULL, '1774031130_1735.png', NULL, NULL, 1, '3', 1, 1, 1, 10, 'tiered', 'Bulk discounts available for larger quantities', 'Eco-Friendly Grocery Bags', 'Buy eco-friendly grocery bags in 500gm and 1000gm capacities. Strong, durable, and reusable.', 'grocery bags, shopping bags, carry bags, reusable bags, eco-friendly bags', 1, 1, 1, 15, 0, '2026-03-20 16:11:41', '2026-03-20 18:25:30');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `category` enum('order_issue','payment_problem','product_query','shipping_delivery','return_refund','technical_support','bulk_inquiry','account_issue','feedback','other') DEFAULT 'other',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','in_progress','awaiting_reply','resolved','closed') DEFAULT 'open',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  `first_response_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `source` enum('website','mobile_app','email','chatbot','phone') DEFAULT 'website',
  `language` varchar(10) DEFAULT 'en',
  `sla_deadline` datetime DEFAULT NULL,
  `sla_breached` tinyint(1) DEFAULT 0,
  `rating` tinyint(1) DEFAULT NULL COMMENT '1-5 stars',
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `ticket_number`, `user_id`, `guest_email`, `guest_name`, `order_id`, `subject`, `category`, `priority`, `status`, `created_at`, `updated_at`, `closed_at`, `first_response_at`, `resolved_at`, `assigned_to`, `assigned_at`, `ip_address`, `user_agent`, `source`, `language`, `sla_deadline`, `sla_breached`, `rating`, `feedback`) VALUES
(5, '1', 2, NULL, NULL, 1, 'Return status inquiry for damaged Ganesha idol', 'return_refund', 'medium', 'resolved', '2026-03-01 14:22:21', '2026-03-02 14:22:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'website', 'en', NULL, 0, NULL, NULL),
(6, '1', 3, NULL, NULL, 2, 'Bulk order pricing clarification needed', 'bulk_inquiry', 'high', 'in_progress', '2026-03-03 14:22:21', '2026-03-04 14:22:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'website', 'en', NULL, 0, NULL, NULL),
(7, '1', 4, NULL, NULL, 3, 'Payment failed but amount deducted', 'payment_problem', 'urgent', 'open', '2026-03-05 14:22:21', '2026-03-06 14:22:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'website', 'en', NULL, 0, NULL, NULL),
(8, '1', NULL, 'customer@gmail.com', 'Shyam', NULL, 'Product availability for bulk purchase', 'product_query', 'low', 'open', '2026-03-08 14:22:21', '2026-03-08 14:22:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'email', 'en', NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_type` enum('customer','wholesaler','retailer','admin') DEFAULT 'customer',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `display_name` varchar(200) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `alternate_phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `token_expiry` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `pan_number` varchar(50) DEFAULT NULL,
  `business_type` enum('individual','partnership','pvt_ltd','llp','other') DEFAULT NULL,
  `year_of_establishment` year(4) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_blocked` tinyint(1) DEFAULT 0,
  `account_balance` decimal(10,2) DEFAULT 0.00,
  `prefers_bulk_pricing` tinyint(1) DEFAULT 0,
  `default_bulk_min_quantity` int(11) DEFAULT 10,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `registered_from_ip` varchar(45) DEFAULT NULL,
  `referral_code` varchar(50) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_type`, `first_name`, `last_name`, `display_name`, `email`, `phone`, `alternate_phone`, `password_hash`, `remember_token`, `token_expiry`, `email_verified_at`, `phone_verified_at`, `business_name`, `gst_number`, `pan_number`, `business_type`, `year_of_establishment`, `is_active`, `is_blocked`, `account_balance`, `prefers_bulk_pricing`, `default_bulk_min_quantity`, `last_login_at`, `last_login_ip`, `registered_from_ip`, `referral_code`, `referred_by`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Admin', 'User', NULL, 'admin@example.com', '9876543210', NULL, '$2y$10$YourHashedPasswordHere', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00, 0, 10, NULL, NULL, NULL, NULL, NULL, '2026-03-08 08:28:50', '2026-03-08 08:28:50'),
(2, 'wholesaler', 'Rajesh', 'Kumar', NULL, 'rajesh.k@example.com', '9876543211', NULL, '$2y$10$YourHashedPasswordHere', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00, 1, 50, NULL, NULL, NULL, NULL, NULL, '2026-03-08 08:28:50', '2026-03-08 08:28:50'),
(3, 'retailer', 'Priya', 'Sharma', NULL, 'priya.s@example.com', '9876543212', NULL, '$2y$10$YourHashedPasswordHere', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00, 1, 25, NULL, NULL, NULL, NULL, NULL, '2026-03-08 08:28:50', '2026-03-08 08:28:50'),
(4, 'customer', 'Amit', 'Patel', NULL, 'amit.p@example.com', '9876543213', NULL, '$2y$10$YourHashedPasswordHere', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00, 0, 10, NULL, NULL, NULL, NULL, NULL, '2026-03-08 08:28:50', '2026-03-08 08:28:50'),
(5, 'customer', 'Gourav', 'Vishwakarma', 'Gourav Vishwakarma', 'gouravvish.itscient@gmail.com', '7979831032', NULL, '$2y$10$tc1iJSR.5UirSYUlhdbUxO6yOYZ9XwTJ/GQWigaCMkUYym2cLSskG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0.00, 0, 10, '2026-03-21 15:20:58', '::1', '::1', NULL, NULL, '2026-03-08 09:06:35', '2026-03-21 15:20:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_type` enum('home','work','business','other') DEFAULT 'home',
  `is_default` tinyint(1) DEFAULT 0,
  `is_billing_address` tinyint(1) DEFAULT 0,
  `is_shipping_address` tinyint(1) DEFAULT 1,
  `name` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `alternate_phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(20) NOT NULL,
  `country` varchar(100) DEFAULT 'India',
  `company_name` varchar(255) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `address_type`, `is_default`, `is_billing_address`, `is_shipping_address`, `name`, `phone`, `alternate_phone`, `address_line1`, `address_line2`, `landmark`, `city`, `state`, `pincode`, `country`, `company_name`, `gst_number`, `delivery_instructions`, `created_at`, `updated_at`) VALUES
(1, 2, 'business', 1, 1, 1, 'Rajesh Kumar', '9876543211', NULL, 'Shop No. 45, Wholesale Market', NULL, NULL, 'Delhi', 'Delhi', '110001', 'India', NULL, NULL, NULL, '2026-03-08 08:28:58', '2026-03-08 08:28:58'),
(2, 2, 'home', 0, 0, 1, 'Rajesh Kumar', '9876543211', NULL, 'House No. 123, Green Park', NULL, NULL, 'Delhi', 'Delhi', '110016', 'India', NULL, NULL, NULL, '2026-03-08 08:28:58', '2026-03-08 08:28:58'),
(3, 3, 'business', 1, 1, 1, 'Priya Sharma', '9876543212', NULL, 'Mall Road, Shop No. 12', NULL, NULL, 'Mumbai', 'Maharashtra', '400001', 'India', NULL, NULL, NULL, '2026-03-08 08:28:58', '2026-03-08 08:28:58'),
(4, 4, 'home', 1, 1, 1, 'Amit Patel', '9876543213', NULL, 'B-201, Sunrise Apartments', NULL, NULL, 'Ahmedabad', 'Gujarat', '380001', 'India', NULL, NULL, NULL, '2026-03-08 08:28:58', '2026-03-08 08:28:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_type` enum('regular','wholesale','retailer','distributor') DEFAULT 'regular',
  `min_purchase_amount` decimal(10,2) DEFAULT 0.00,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `requires_approval` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`id`, `group_name`, `group_type`, `min_purchase_amount`, `discount_percentage`, `requires_approval`, `created_at`) VALUES
(1, 'Regular Customers', 'regular', 0.00, 0.00, 0, '2026-03-08 08:28:34'),
(2, 'Wholesale Buyers', 'wholesale', 10000.00, 15.00, 1, '2026-03-08 08:28:34'),
(3, 'Retail Partners', 'retailer', 5000.00, 10.00, 1, '2026-03-08 08:28:34'),
(4, 'Distributors', 'distributor', 50000.00, 25.00, 1, '2026-03-08 08:28:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_group_members`
--

CREATE TABLE `user_group_members` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `expires_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_wishlist`
--

CREATE TABLE `user_wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_wanted` int(11) DEFAULT 1,
  `priority` enum('high','medium','low') DEFAULT 'medium',
  `notes` text DEFAULT NULL,
  `notify_when_in_stock` tinyint(1) DEFAULT 0,
  `notify_when_price_drops` tinyint(1) DEFAULT 0,
  `target_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `shared_with` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Share wishlist with other users' CHECK (json_valid(`shared_with`)),
  `is_public` tinyint(1) DEFAULT 0,
  `expiry_date` date DEFAULT NULL COMMENT 'For event-based wishlists'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_wishlist`
--

INSERT INTO `user_wishlist` (`id`, `user_id`, `product_id`, `quantity_wanted`, `priority`, `notes`, `notify_when_in_stock`, `notify_when_price_drops`, `target_price`, `created_at`, `updated_at`, `shared_with`, `is_public`, `expiry_date`) VALUES
(1, 2, 7, 1, 'high', NULL, 0, 1, 2200.00, '2026-02-26 08:41:36', '2026-03-08 08:41:36', NULL, 0, NULL),
(2, 2, 5, 2, 'medium', NULL, 1, 0, NULL, '2026-02-28 08:41:36', '2026-03-08 08:41:36', NULL, 0, NULL),
(3, 3, 1, 1, 'high', NULL, 0, 1, 750.00, '2026-02-21 08:41:36', '2026-03-08 08:41:36', NULL, 0, NULL),
(4, 3, 10, 50, 'high', NULL, 0, 1, 400.00, '2026-03-03 08:41:36', '2026-03-08 08:41:36', NULL, 0, NULL),
(5, 4, 4, 5, 'low', NULL, 1, 0, NULL, '2026-03-05 08:41:36', '2026-03-08 08:41:36', NULL, 0, NULL),
(6, 1, 80, 1, 'medium', NULL, 0, 0, NULL, '2026-03-20 19:01:45', '2026-03-20 19:01:45', NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_record` (`table_name`,`record_id`),
  ADD KEY `idx_audit_time` (`changed_at`);

--
-- Indexes for table `bulk_inquiries`
--
ALTER TABLE `bulk_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inquiry_number` (`inquiry_number`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `bulk_pricing_tiers`
--
ALTER TABLE `bulk_pricing_tiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_tier_range` (`product_id`,`min_quantity`),
  ADD UNIQUE KEY `unique_variant_tier_range` (`variant_id`,`min_quantity`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_variant` (`variant_id`),
  ADD KEY `idx_sub_category` (`sub_category_id`),
  ADD KEY `idx_min_quantity` (`min_quantity`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `bulk_quotations`
--
ALTER TABLE `bulk_quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotation_number` (`quotation_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `converted_to_order_id` (`converted_to_order_id`),
  ADD KEY `idx_inquiry` (`inquiry_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bulk_tier_id` (`bulk_tier_id`),
  ADD KEY `idx_cart` (`cart_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_code` (`coupon_code`),
  ADD KEY `idx_coupon_code` (`coupon_code`),
  ADD KEY `idx_validity` (`valid_from`,`valid_to`);

--
-- Indexes for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coupon` (`coupon_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_used_at` (`used_at`);

--
-- Indexes for table `fraud_analysis_logs`
--
ALTER TABLE `fraud_analysis_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_risk` (`risk_level`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_invoice_number` (`invoice_number`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `main_categories`
--
ALTER TABLE `main_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_category_code` (`category_code`);
ALTER TABLE `main_categories` ADD FULLTEXT KEY `idx_search` (`name`,`description`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_order_date` (`order_date`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `replacement_order_id` (`replacement_order_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_history` (`order_id`,`created_at`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_tracking_number` (`tracking_number`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `refund_initiated_by` (`refund_initiated_by`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_status` (`payment_status`);

--
-- Indexes for table `pincode_inquiries`
--
ALTER TABLE `pincode_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pincode` (`pincode`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `price_history`
--
ALTER TABLE `price_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_price_product` (`product_id`,`effective_from`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_sub_category` (`sub_category_id`),
  ADD KEY `idx_product_code` (`product_code`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_product_type` (`product_type`),
  ADD KEY `idx_selling_mode` (`selling_mode`);
ALTER TABLE `products` ADD FULLTEXT KEY `idx_search` (`name`,`short_description`,`description`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_variant` (`variant_id`),
  ADD KEY `idx_is_primary` (`is_primary`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product_review` (`user_id`,`product_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `product_shipping_restrictions`
--
ALTER TABLE `product_shipping_restrictions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_pincode` (`product_id`,`pincode`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `unique_product_variant` (`product_id`,`attributes`(255)),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_sku` (`sku`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_is_default` (`is_default`),
  ADD KEY `idx_size` (`size`),
  ADD KEY `idx_color` (`color`);

--
-- Indexes for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `review_helpfulness`
--
ALTER TABLE `review_helpfulness`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_review_vote` (`user_id`,`review_id`),
  ADD KEY `review_id` (`review_id`);

--
-- Indexes for table `serviceable_pincodes`
--
ALTER TABLE `serviceable_pincodes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pincode` (`pincode`),
  ADD KEY `idx_pincode_active` (`pincode`,`is_active`);

--
-- Indexes for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_main_category` (`main_category_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_level` (`level`);
ALTER TABLE `sub_categories` ADD FULLTEXT KEY `idx_search` (`name`,`description`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_assigned` (`assigned_to`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `referred_by` (`referred_by`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_user_type` (`user_type`),
  ADD KEY `idx_business_type` (`business_type`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_default` (`user_id`,`is_default`),
  ADD KEY `idx_pincode` (`pincode`);

--
-- Indexes for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_group_members`
--
ALTER TABLE `user_group_members`
  ADD PRIMARY KEY (`user_id`,`group_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bulk_inquiries`
--
ALTER TABLE `bulk_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bulk_pricing_tiers`
--
ALTER TABLE `bulk_pricing_tiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bulk_quotations`
--
ALTER TABLE `bulk_quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fraud_analysis_logs`
--
ALTER TABLE `fraud_analysis_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `main_categories`
--
ALTER TABLE `main_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pincode_inquiries`
--
ALTER TABLE `pincode_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `price_history`
--
ALTER TABLE `price_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_shipping_restrictions`
--
ALTER TABLE `product_shipping_restrictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `return_requests`
--
ALTER TABLE `return_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `review_helpfulness`
--
ALTER TABLE `review_helpfulness`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `serviceable_pincodes`
--
ALTER TABLE `serviceable_pincodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sub_categories`
--
ALTER TABLE `sub_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bulk_inquiries`
--
ALTER TABLE `bulk_inquiries`
  ADD CONSTRAINT `bulk_inquiries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_inquiries_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bulk_quotations`
--
ALTER TABLE `bulk_quotations`
  ADD CONSTRAINT `bulk_quotations_ibfk_1` FOREIGN KEY (`inquiry_id`) REFERENCES `bulk_inquiries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_quotations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_quotations_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_quotations_ibfk_4` FOREIGN KEY (`converted_to_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`bulk_tier_id`) REFERENCES `bulk_pricing_tiers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`replacement_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_tracking_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_tracking_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`refund_initiated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_images_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_reviews_ibfk_4` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_reviews_ibfk_5` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD CONSTRAINT `return_requests_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `return_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `review_helpfulness`
--
ALTER TABLE `review_helpfulness`
  ADD CONSTRAINT `review_helpfulness_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `product_reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_helpfulness_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD CONSTRAINT `sub_categories_ibfk_1` FOREIGN KEY (`main_category_id`) REFERENCES `main_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sub_categories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `fk_tickets_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_group_members`
--
ALTER TABLE `user_group_members`
  ADD CONSTRAINT `user_group_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_group_members_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`);

--
-- Constraints for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  ADD CONSTRAINT `user_wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
