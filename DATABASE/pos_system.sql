-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2025 at 03:05 PM
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
-- Database: `pos_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Drinks', ''),
(2, 'Food', 'Food items and snacks'),
(3, 'Electronics', 'Electronic devices and accessories'),
(4, 'Clothing', 'Apparel and fashion items'),
(5, 'Books', 'Books and educational materials');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `transaction_type` enum('purchase','sale','return','adjustment','damage','expiry') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`transaction_id`, `product_id`, `quantity_change`, `transaction_type`, `reference_id`, `notes`, `user_id`, `transaction_date`) VALUES
(1, 3, 94, 'adjustment', NULL, 'Manual inventory adjustment', 101, '2025-05-22 22:37:55'),
(2, 3, -1, 'sale', 6, NULL, 114, '2025-05-22 22:49:56'),
(3, 3, -1, 'sale', 7, NULL, 114, '2025-05-22 22:56:04'),
(4, 3, -1, 'sale', 8, NULL, 114, '2025-05-22 23:05:36'),
(5, 6, -99, 'adjustment', NULL, 'Manual inventory adjustment', 101, '2025-05-25 13:30:07'),
(6, 6, 19, 'adjustment', NULL, 'Manual inventory adjustment', 101, '2025-05-25 13:30:16'),
(7, 3, -7, 'adjustment', NULL, 'Manual inventory adjustment', 101, '2025-05-25 13:35:02'),
(8, 6, -1, 'sale', 45, NULL, 101, '2025-05-25 13:36:29'),
(9, 5, -1, 'sale', 45, NULL, 101, '2025-05-25 13:36:29'),
(10, 3, -1, 'sale', 45, NULL, 101, '2025-05-25 13:36:29'),
(11, 6, 1, 'adjustment', NULL, 'Manual inventory adjustment', 101, '2025-05-25 13:37:02'),
(12, 3, 1, 'adjustment', NULL, 'Manual inventory adjustment', 101, '2025-05-25 13:37:09'),
(13, 6, -1, 'sale', 46, NULL, 114, '2025-05-26 15:20:06'),
(14, 3, -1, 'sale', 47, NULL, 114, '2025-05-26 16:42:32'),
(15, 3, -1, 'sale', 48, NULL, 114, '2025-05-26 16:43:18'),
(16, 3, -1, 'sale', 49, NULL, 114, '2025-05-26 17:04:16'),
(17, 3, -1, 'sale', 50, NULL, 114, '2025-05-26 17:04:23'),
(18, 6, -1, 'sale', 51, NULL, 114, '2025-05-26 17:15:59'),
(19, 3, -1, 'sale', 52, NULL, 114, '2025-05-26 17:33:17'),
(20, 7, -1, 'sale', 53, NULL, 114, '2025-05-26 19:01:20'),
(21, 3, -1, 'sale', 54, NULL, 114, '2025-05-27 13:43:49'),
(22, 3, -1, 'sale', 55, NULL, 114, '2025-05-27 21:28:58'),
(23, 6, -1, 'sale', 60, NULL, 114, '2025-05-30 04:07:41'),
(24, 7, -1, 'sale', 63, NULL, 114, '2025-05-30 16:54:11');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Paid','Cancelled') DEFAULT 'Pending',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `order_date`, `total_amount`, `payment_status`, `user_id`) VALUES
(3, NULL, '2025-05-21 15:26:18', 25.00, 'Paid', 114),
(4, NULL, '2025-05-22 22:05:43', 50.00, 'Paid', 114),
(5, NULL, '2025-05-22 22:21:05', 25.00, 'Paid', 114),
(6, NULL, '2025-05-22 22:49:56', 25.00, 'Paid', 114),
(7, NULL, '2025-05-22 22:56:04', 25.00, 'Paid', 114),
(8, NULL, '2025-05-22 23:05:36', 25.00, 'Paid', 114),
(9, NULL, '2025-05-24 06:42:58', 135.00, 'Paid', 114),
(10, NULL, '2025-05-24 01:39:27', 90.00, 'Paid', 114),
(11, NULL, '2025-05-24 08:48:11', 115.00, 'Paid', 114),
(12, NULL, '2025-05-24 04:13:46', 45000.00, 'Paid', 114),
(13, NULL, '2025-05-24 02:07:07', 715.00, 'Paid', 114),
(14, NULL, '2025-05-24 01:41:42', 790.00, 'Paid', 114),
(15, NULL, '2025-05-24 01:09:44', 1305.00, 'Paid', 114),
(16, NULL, '2025-05-24 04:34:51', 180.00, 'Paid', 114),
(17, NULL, '2025-05-24 02:50:36', 75.00, 'Paid', 114),
(18, NULL, '2025-05-24 03:41:44', 375.00, 'Paid', 114),
(19, NULL, '2025-05-22 01:06:09', 285.00, 'Paid', 114),
(20, NULL, '2025-05-22 11:44:55', 45.00, 'Paid', 114),
(21, NULL, '2025-05-22 08:41:30', 25.00, 'Paid', 114),
(22, NULL, '2025-05-22 06:23:15', 15000.00, 'Paid', 114),
(23, NULL, '2025-05-20 10:09:39', 55.00, 'Paid', 114),
(24, NULL, '2025-05-20 05:19:40', 330.00, 'Paid', 114),
(25, NULL, '2025-05-20 02:56:33', 790.00, 'Paid', 114),
(26, NULL, '2025-05-20 04:13:46', 350.00, 'Paid', 114),
(27, NULL, '2025-05-20 09:19:24', 45.00, 'Paid', 114),
(28, NULL, '2025-05-19 05:55:47', 15.00, 'Paid', 114),
(29, NULL, '2025-05-19 10:35:04', 30.00, 'Paid', 114),
(30, NULL, '2025-05-19 03:02:34', 30350.00, 'Paid', 114),
(31, NULL, '2025-05-19 01:31:55', 15015.00, 'Paid', 114),
(32, NULL, '2025-05-18 07:49:22', 30700.00, 'Paid', 114),
(33, NULL, '2025-05-18 04:52:05', 55.00, 'Paid', 114),
(34, NULL, '2025-05-18 07:31:18', 240.00, 'Paid', 114),
(35, NULL, '2025-05-18 02:30:11', 240.00, 'Paid', 114),
(36, NULL, '2025-05-18 10:57:24', 745.00, 'Paid', 114),
(37, NULL, '2025-05-18 10:16:30', 440.00, 'Paid', 114),
(38, NULL, '2025-05-18 05:14:13', 350.00, 'Paid', 114),
(39, NULL, '2025-05-18 08:27:00', 330.00, 'Paid', 114),
(40, NULL, '2025-05-17 05:28:11', 45.00, 'Paid', 114),
(41, NULL, '2025-05-17 01:02:26', 30000.00, 'Paid', 114),
(42, NULL, '2025-05-17 05:12:40', 120.00, 'Paid', 114),
(43, NULL, '2025-05-17 03:45:12', 350.00, 'Paid', 114),
(44, NULL, '2025-05-17 02:01:49', 285.00, 'Paid', 114),
(45, NULL, '2025-05-25 13:36:29', 85.00, 'Paid', 101),
(46, NULL, '2025-05-26 15:20:06', 15.00, 'Paid', 114),
(47, NULL, '2025-05-26 16:42:32', 25.00, 'Paid', 114),
(48, NULL, '2025-05-26 16:43:18', 25.00, 'Paid', 114),
(51, NULL, '2025-05-26 17:15:59', 15.00, 'Paid', 114),
(52, NULL, '2025-05-26 17:33:17', 25.00, 'Paid', 114),
(53, NULL, '2025-05-26 19:01:20', 15000.00, 'Paid', 114),
(54, NULL, '2025-05-27 13:43:49', 25.00, 'Paid', 114),
(55, NULL, '2025-05-27 21:28:58', 25.00, 'Paid', 114),
(57, NULL, '2025-05-27 22:01:03', 95.00, 'Paid', 114),
(58, NULL, '2025-05-27 22:04:05', 70.00, 'Paid', 114),
(59, NULL, '2025-05-30 04:06:12', 90.00, 'Paid', 114),
(60, NULL, '2025-05-30 04:07:41', 15.00, 'Paid', 114),
(61, NULL, '2025-05-30 04:14:00', 15000.00, 'Paid', 114),
(62, NULL, '2025-05-30 16:26:43', 30.00, 'Paid', 114),
(63, NULL, '2025-05-30 16:54:11', 15000.00, 'Paid', 114),
(64, NULL, '2025-05-30 17:35:04', 15000.00, 'Paid', 114);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `subtotal`) VALUES
(3, 3, 3, 1, 25.00),
(4, 4, 3, 2, 50.00),
(5, 5, 3, 1, 25.00),
(6, 6, 3, 1, 25.00),
(7, 7, 3, 1, 25.00),
(8, 8, 3, 1, 25.00),
(9, 9, 6, 1, 15.00),
(10, 9, 9, 1, 120.00),
(11, 10, 5, 2, 90.00),
(12, 11, 3, 1, 25.00),
(13, 11, 5, 2, 90.00),
(14, 12, 7, 3, 45000.00),
(15, 13, 6, 1, 15.00),
(16, 13, 8, 2, 700.00),
(17, 14, 5, 2, 90.00),
(18, 14, 8, 2, 700.00),
(19, 15, 6, 1, 15.00),
(20, 15, 8, 3, 1050.00),
(21, 15, 9, 2, 240.00),
(22, 16, 5, 3, 135.00),
(23, 16, 6, 3, 45.00),
(24, 17, 3, 3, 75.00),
(25, 18, 5, 3, 135.00),
(26, 18, 9, 2, 240.00),
(27, 19, 5, 1, 45.00),
(28, 19, 9, 2, 240.00),
(29, 20, 5, 1, 45.00),
(30, 21, 3, 1, 25.00),
(31, 22, 7, 1, 15000.00),
(32, 23, 3, 1, 25.00),
(33, 23, 6, 2, 30.00),
(34, 24, 5, 2, 90.00),
(35, 24, 9, 2, 240.00),
(36, 25, 5, 2, 90.00),
(37, 25, 8, 2, 700.00),
(38, 26, 8, 1, 350.00),
(39, 27, 5, 1, 45.00),
(40, 28, 6, 1, 15.00),
(41, 29, 6, 2, 30.00),
(42, 30, 7, 2, 30000.00),
(43, 30, 8, 1, 350.00),
(44, 31, 6, 1, 15.00),
(45, 31, 7, 1, 15000.00),
(46, 32, 7, 2, 30000.00),
(47, 32, 8, 2, 700.00),
(48, 33, 3, 1, 25.00),
(49, 33, 6, 2, 30.00),
(50, 34, 9, 2, 240.00),
(51, 35, 9, 2, 240.00),
(52, 36, 5, 1, 45.00),
(53, 36, 8, 2, 700.00),
(54, 37, 5, 2, 90.00),
(55, 37, 8, 1, 350.00),
(56, 38, 8, 1, 350.00),
(57, 39, 5, 2, 90.00),
(58, 39, 9, 2, 240.00),
(59, 40, 5, 1, 45.00),
(60, 41, 7, 2, 30000.00),
(61, 42, 9, 1, 120.00),
(62, 43, 8, 1, 350.00),
(63, 44, 5, 1, 45.00),
(64, 44, 9, 2, 240.00),
(65, 45, 6, 1, 15.00),
(66, 45, 5, 1, 45.00),
(67, 45, 3, 1, 25.00),
(68, 46, 6, 1, 15.00),
(69, 47, 3, 1, 25.00),
(70, 48, 3, 1, 25.00),
(73, 51, 6, 1, 15.00),
(74, 52, 3, 1, 25.00),
(75, 53, 7, 1, 15000.00),
(76, 54, 3, 1, 25.00),
(77, 55, 3, 1, 25.00),
(79, 57, 3, 2, 50.00),
(80, 57, 5, 1, 45.00),
(81, 58, 6, 3, 45.00),
(82, 58, 3, 1, 25.00),
(83, 59, 6, 6, 90.00),
(84, 60, 6, 1, 15.00),
(85, 61, 7, 1, 15000.00),
(86, 62, 6, 2, 30.00),
(87, 63, 7, 1, 15000.00),
(88, 64, 7, 1, 15000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('Cash','PayPal') NOT NULL,
  `paypal_transaction_id` varchar(255) DEFAULT NULL,
  `transaction_status` enum('Pending','Success','Failed') DEFAULT 'Pending',
  `cash_received` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `payment_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `cashier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `paypal_transaction_id`, `transaction_status`, `cash_received`, `change_amount`, `payment_time`, `cashier_id`) VALUES
(1, 3, 'Cash', NULL, '', 25.00, 0.00, '2025-05-21 15:26:18', 114),
(2, 4, 'Cash', NULL, '', 50.00, 0.00, '2025-05-22 22:05:43', 114),
(3, 5, 'Cash', NULL, '', 50.00, 25.00, '2025-05-22 22:21:05', 114),
(4, 6, 'Cash', NULL, '', 50.00, 25.00, '2025-05-22 22:49:56', 114),
(5, 7, 'Cash', NULL, '', 50.00, 25.00, '2025-05-22 22:56:04', 114),
(6, 8, 'Cash', NULL, '', 50.00, 25.00, '2025-05-22 23:05:36', 114),
(7, 9, 'Cash', NULL, 'Pending', 135.00, 0.00, '2025-05-24 06:42:58', 114),
(8, 10, 'Cash', NULL, 'Pending', 90.00, 0.00, '2025-05-24 01:39:27', 114),
(9, 11, 'Cash', NULL, 'Pending', 115.00, 0.00, '2025-05-24 08:48:11', 114),
(10, 12, 'Cash', NULL, 'Pending', 45000.00, 0.00, '2025-05-24 04:13:46', 114),
(11, 13, 'Cash', NULL, 'Pending', 715.00, 0.00, '2025-05-24 02:07:07', 114),
(12, 14, 'Cash', NULL, 'Pending', 790.00, 0.00, '2025-05-24 01:41:42', 114),
(13, 15, 'Cash', NULL, 'Pending', 1305.00, 0.00, '2025-05-24 01:09:44', 114),
(14, 16, 'Cash', NULL, 'Pending', 180.00, 0.00, '2025-05-24 04:34:51', 114),
(15, 17, 'Cash', NULL, 'Pending', 75.00, 0.00, '2025-05-24 02:50:36', 114),
(16, 18, 'Cash', NULL, 'Pending', 375.00, 0.00, '2025-05-24 03:41:44', 114),
(17, 19, 'Cash', NULL, 'Pending', 285.00, 0.00, '2025-05-22 01:06:09', 114),
(18, 20, 'Cash', NULL, 'Pending', 45.00, 0.00, '2025-05-22 11:44:55', 114),
(19, 21, 'Cash', NULL, 'Pending', 25.00, 0.00, '2025-05-22 08:41:30', 114),
(20, 22, 'Cash', NULL, 'Pending', 15000.00, 0.00, '2025-05-22 06:23:15', 114),
(21, 23, 'Cash', NULL, 'Pending', 55.00, 0.00, '2025-05-20 10:09:39', 114),
(22, 24, 'Cash', NULL, 'Pending', 330.00, 0.00, '2025-05-20 05:19:40', 114),
(23, 25, 'Cash', NULL, 'Pending', 790.00, 0.00, '2025-05-20 02:56:33', 114),
(24, 26, 'Cash', NULL, 'Pending', 350.00, 0.00, '2025-05-20 04:13:46', 114),
(25, 27, 'Cash', NULL, 'Pending', 45.00, 0.00, '2025-05-20 09:19:24', 114),
(26, 28, 'Cash', NULL, 'Pending', 15.00, 0.00, '2025-05-19 05:55:47', 114),
(27, 29, 'Cash', NULL, 'Pending', 30.00, 0.00, '2025-05-19 10:35:04', 114),
(28, 30, 'Cash', NULL, 'Pending', 30350.00, 0.00, '2025-05-19 03:02:34', 114),
(29, 31, 'Cash', NULL, 'Pending', 15015.00, 0.00, '2025-05-19 01:31:55', 114),
(30, 32, 'Cash', NULL, 'Pending', 30700.00, 0.00, '2025-05-18 07:49:22', 114),
(31, 33, 'Cash', NULL, 'Pending', 55.00, 0.00, '2025-05-18 04:52:05', 114),
(32, 34, 'Cash', NULL, 'Pending', 240.00, 0.00, '2025-05-18 07:31:18', 114),
(33, 35, 'Cash', NULL, 'Pending', 240.00, 0.00, '2025-05-18 02:30:11', 114),
(34, 36, 'Cash', NULL, 'Pending', 745.00, 0.00, '2025-05-18 10:57:24', 114),
(35, 37, 'Cash', NULL, 'Pending', 440.00, 0.00, '2025-05-18 10:16:30', 114),
(36, 38, 'Cash', NULL, 'Pending', 350.00, 0.00, '2025-05-18 05:14:13', 114),
(37, 39, 'Cash', NULL, 'Pending', 330.00, 0.00, '2025-05-18 08:27:00', 114),
(38, 40, 'Cash', NULL, 'Pending', 45.00, 0.00, '2025-05-17 05:28:11', 114),
(39, 41, 'Cash', NULL, 'Pending', 30000.00, 0.00, '2025-05-17 01:02:26', 114),
(40, 42, 'Cash', NULL, 'Pending', 120.00, 0.00, '2025-05-17 05:12:40', 114),
(41, 43, 'Cash', NULL, 'Pending', 350.00, 0.00, '2025-05-17 03:45:12', 114),
(42, 44, 'Cash', NULL, 'Pending', 285.00, 0.00, '2025-05-17 02:01:49', 114),
(43, 45, 'Cash', NULL, '', 100.00, 15.00, '2025-05-25 13:36:29', 101),
(44, 46, 'Cash', NULL, '', 20.00, 5.00, '2025-05-26 15:20:06', 114),
(45, 47, 'Cash', NULL, '', 50.00, 25.00, '2025-05-26 16:42:32', 114),
(46, 48, 'Cash', NULL, '', 50.00, 25.00, '2025-05-26 16:43:18', 114),
(49, 51, 'Cash', NULL, '', 20.00, 5.00, '2025-05-26 17:15:59', 114),
(50, 52, 'Cash', NULL, '', 50.00, 25.00, '2025-05-26 17:33:17', 114),
(51, 53, 'Cash', NULL, '', 15000.00, 0.00, '2025-05-26 19:01:20', 114),
(52, 54, 'Cash', NULL, '', 50.00, 25.00, '2025-05-27 13:43:49', 114),
(53, 55, 'Cash', NULL, '', 50.00, 25.00, '2025-05-27 21:28:58', 114),
(54, 57, 'PayPal', '68Y80104TM459592D', 'Success', 95.00, 0.00, '2025-05-27 22:01:03', 114),
(55, 58, 'PayPal', '67D89857G5451260E', 'Success', 70.00, 0.00, '2025-05-27 22:04:05', 114),
(56, 59, 'PayPal', '5JF02702PD535080W', 'Success', 90.00, 0.00, '2025-05-30 04:06:12', 114),
(57, 60, 'Cash', NULL, '', 20.00, 5.00, '2025-05-30 04:07:41', 114),
(58, 61, 'PayPal', '9UT43460VG137202R', 'Success', 15000.00, 0.00, '2025-05-30 04:14:00', 114),
(59, 62, 'PayPal', '5SV42873UB1094058', 'Success', 30.00, 0.00, '2025-05-30 16:26:43', 114),
(60, 63, 'Cash', NULL, '', 15000.00, 0.00, '2025-05-30 16:54:11', 114),
(61, 64, 'PayPal', '46K703543C799044K', 'Success', 15000.00, 0.00, '2025-05-30 17:35:04', 114);

-- --------------------------------------------------------

--
-- Table structure for table `paypal_transaction_details`
--

CREATE TABLE `paypal_transaction_details` (
  `detail_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `paypal_order_id` varchar(255) NOT NULL,
  `payer_id` varchar(255) DEFAULT NULL,
  `payer_email` varchar(255) DEFAULT NULL,
  `transaction_details` text DEFAULT NULL COMMENT 'JSON encoded PayPal order details',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores detailed PayPal transaction information';

--
-- Dumping data for table `paypal_transaction_details`
--

INSERT INTO `paypal_transaction_details` (`detail_id`, `payment_id`, `paypal_order_id`, `payer_id`, `payer_email`, `transaction_details`, `created_at`) VALUES
(1, 54, '68Y80104TM459592D', 'CPV3FGL6ADH2N', 'andreieslera@gmail.com', '{\"id\":\"68Y80104TM459592D\",\"intent\":\"CAPTURE\",\"status\":\"COMPLETED\",\"purchase_units\":[{\"reference_id\":\"default\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"95.00\"},\"payee\":{\"email_address\":\"amaneslera@gmail.com\",\"merchant_id\":\"HRDWQ47D2W7BC\"},\"description\":\"POS System Order - 2 items\",\"shipping\":{\"name\":{\"full_name\":\"Andrei Eslera\"},\"address\":{\"address_line_1\":\"1 Main St\",\"admin_area_2\":\"San Jose\",\"admin_area_1\":\"CA\",\"postal_code\":\"95131\",\"country_code\":\"US\"}},\"payments\":{\"captures\":[{\"id\":\"6NE65277Y69785906\",\"status\":\"COMPLETED\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"95.00\"},\"final_capture\":true,\"seller_protection\":{\"status\":\"ELIGIBLE\",\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"]},\"create_time\":\"2025-05-27T22:01:02Z\",\"update_time\":\"2025-05-27T22:01:02Z\"}]}}],\"payer\":{\"name\":{\"given_name\":\"Andrei\",\"surname\":\"Eslera\"},\"email_address\":\"andreieslera@gmail.com\",\"payer_id\":\"CPV3FGL6ADH2N\",\"address\":{\"country_code\":\"US\"}},\"create_time\":\"2025-05-27T22:00:44Z\",\"update_time\":\"2025-05-27T22:01:02Z\",\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/68Y80104TM459592D\",\"rel\":\"self\",\"method\":\"GET\"}]}', '2025-05-27 22:01:03'),
(2, 55, '67D89857G5451260E', 'CPV3FGL6ADH2N', 'andreieslera@gmail.com', '{\"id\":\"67D89857G5451260E\",\"intent\":\"CAPTURE\",\"status\":\"COMPLETED\",\"purchase_units\":[{\"reference_id\":\"default\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"70.00\"},\"payee\":{\"email_address\":\"amaneslera@gmail.com\",\"merchant_id\":\"HRDWQ47D2W7BC\"},\"description\":\"POS System Order - 2 items\",\"shipping\":{\"name\":{\"full_name\":\"Andrei Eslera\"},\"address\":{\"address_line_1\":\"1 Main St\",\"admin_area_2\":\"San Jose\",\"admin_area_1\":\"CA\",\"postal_code\":\"95131\",\"country_code\":\"US\"}},\"payments\":{\"captures\":[{\"id\":\"3EW028286J094540Y\",\"status\":\"COMPLETED\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"70.00\"},\"final_capture\":true,\"seller_protection\":{\"status\":\"ELIGIBLE\",\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"]},\"create_time\":\"2025-05-27T22:04:04Z\",\"update_time\":\"2025-05-27T22:04:04Z\"}]}}],\"payer\":{\"name\":{\"given_name\":\"Andrei\",\"surname\":\"Eslera\"},\"email_address\":\"andreieslera@gmail.com\",\"payer_id\":\"CPV3FGL6ADH2N\",\"address\":{\"country_code\":\"US\"}},\"create_time\":\"2025-05-27T22:03:57Z\",\"update_time\":\"2025-05-27T22:04:04Z\",\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/67D89857G5451260E\",\"rel\":\"self\",\"method\":\"GET\"}]}', '2025-05-27 22:04:05'),
(3, 56, '5JF02702PD535080W', 'CPV3FGL6ADH2N', 'andreieslera@gmail.com', '{\"id\":\"5JF02702PD535080W\",\"intent\":\"CAPTURE\",\"status\":\"COMPLETED\",\"purchase_units\":[{\"reference_id\":\"default\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"90.00\"},\"payee\":{\"email_address\":\"amaneslera@gmail.com\",\"merchant_id\":\"HRDWQ47D2W7BC\"},\"description\":\"POS System Order - 1 items\",\"shipping\":{\"name\":{\"full_name\":\"Andrei Eslera\"},\"address\":{\"address_line_1\":\"1 Main St\",\"admin_area_2\":\"San Jose\",\"admin_area_1\":\"CA\",\"postal_code\":\"95131\",\"country_code\":\"US\"}},\"payments\":{\"captures\":[{\"id\":\"43G13793RG926453A\",\"status\":\"COMPLETED\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"90.00\"},\"final_capture\":true,\"seller_protection\":{\"status\":\"ELIGIBLE\",\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"]},\"create_time\":\"2025-05-30T04:06:13Z\",\"update_time\":\"2025-05-30T04:06:13Z\"}]}}],\"payer\":{\"name\":{\"given_name\":\"Andrei\",\"surname\":\"Eslera\"},\"email_address\":\"andreieslera@gmail.com\",\"payer_id\":\"CPV3FGL6ADH2N\",\"address\":{\"country_code\":\"US\"}},\"create_time\":\"2025-05-30T04:05:46Z\",\"update_time\":\"2025-05-30T04:06:13Z\",\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/5JF02702PD535080W\",\"rel\":\"self\",\"method\":\"GET\"}]}', '2025-05-30 04:06:12'),
(4, 58, '9UT43460VG137202R', 'CPV3FGL6ADH2N', 'andreieslera@gmail.com', '{\"id\":\"9UT43460VG137202R\",\"intent\":\"CAPTURE\",\"status\":\"COMPLETED\",\"purchase_units\":[{\"reference_id\":\"default\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"15000.00\"},\"payee\":{\"email_address\":\"amaneslera@gmail.com\",\"merchant_id\":\"HRDWQ47D2W7BC\"},\"description\":\"POS System Order - 1 items\",\"soft_descriptor\":\"PAYPAL *TEST STORE\",\"shipping\":{\"name\":{\"full_name\":\"Andrei Eslera\"},\"address\":{\"address_line_1\":\"1 Main St\",\"admin_area_2\":\"San Jose\",\"admin_area_1\":\"CA\",\"postal_code\":\"95131\",\"country_code\":\"US\"}},\"payments\":{\"captures\":[{\"id\":\"64543959YM740591S\",\"status\":\"COMPLETED\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"15000.00\"},\"final_capture\":true,\"seller_protection\":{\"status\":\"ELIGIBLE\",\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"]},\"create_time\":\"2025-05-30T04:14:01Z\",\"update_time\":\"2025-05-30T04:14:01Z\"}]}}],\"payer\":{\"name\":{\"given_name\":\"Andrei\",\"surname\":\"Eslera\"},\"email_address\":\"andreieslera@gmail.com\",\"payer_id\":\"CPV3FGL6ADH2N\",\"address\":{\"country_code\":\"US\"}},\"create_time\":\"2025-05-30T04:13:45Z\",\"update_time\":\"2025-05-30T04:14:01Z\",\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/9UT43460VG137202R\",\"rel\":\"self\",\"method\":\"GET\"}]}', '2025-05-30 04:14:00'),
(5, 59, '5SV42873UB1094058', 'CPV3FGL6ADH2N', 'andreieslera@gmail.com', '{\"id\":\"5SV42873UB1094058\",\"intent\":\"CAPTURE\",\"status\":\"COMPLETED\",\"purchase_units\":[{\"reference_id\":\"default\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"30.00\"},\"payee\":{\"email_address\":\"amaneslera@gmail.com\",\"merchant_id\":\"HRDWQ47D2W7BC\"},\"description\":\"POS System Order - 1 items\",\"shipping\":{\"name\":{\"full_name\":\"Andrei Eslera\"},\"address\":{\"address_line_1\":\"1 Main St\",\"admin_area_2\":\"San Jose\",\"admin_area_1\":\"CA\",\"postal_code\":\"95131\",\"country_code\":\"US\"}},\"payments\":{\"captures\":[{\"id\":\"4R087891XX5844829\",\"status\":\"COMPLETED\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"30.00\"},\"final_capture\":true,\"seller_protection\":{\"status\":\"ELIGIBLE\",\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"]},\"create_time\":\"2025-05-30T16:26:43Z\",\"update_time\":\"2025-05-30T16:26:43Z\"}]}}],\"payer\":{\"name\":{\"given_name\":\"Andrei\",\"surname\":\"Eslera\"},\"email_address\":\"andreieslera@gmail.com\",\"payer_id\":\"CPV3FGL6ADH2N\",\"address\":{\"country_code\":\"US\"}},\"create_time\":\"2025-05-30T16:25:40Z\",\"update_time\":\"2025-05-30T16:26:43Z\",\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/5SV42873UB1094058\",\"rel\":\"self\",\"method\":\"GET\"}]}', '2025-05-30 16:26:43'),
(6, 61, '46K703543C799044K', 'CPV3FGL6ADH2N', 'andreieslera@gmail.com', '{\"id\":\"46K703543C799044K\",\"intent\":\"CAPTURE\",\"status\":\"COMPLETED\",\"purchase_units\":[{\"reference_id\":\"default\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"15000.00\"},\"payee\":{\"email_address\":\"amaneslera@gmail.com\",\"merchant_id\":\"HRDWQ47D2W7BC\"},\"description\":\"POS System Order - 1 items\",\"soft_descriptor\":\"PAYPAL *TEST STORE\",\"shipping\":{\"name\":{\"full_name\":\"Andrei Eslera\"},\"address\":{\"address_line_1\":\"Mabuhay\",\"admin_area_2\":\"GSC\",\"admin_area_1\":\"HI\",\"postal_code\":\"96804\",\"country_code\":\"US\"}},\"payments\":{\"captures\":[{\"id\":\"3V989945UJ8188109\",\"status\":\"COMPLETED\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"15000.00\"},\"final_capture\":true,\"seller_protection\":{\"status\":\"ELIGIBLE\",\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"]},\"create_time\":\"2025-05-30T17:35:03Z\",\"update_time\":\"2025-05-30T17:35:03Z\"}]}}],\"payer\":{\"name\":{\"given_name\":\"Andrei\",\"surname\":\"Eslera\"},\"email_address\":\"andreieslera@gmail.com\",\"payer_id\":\"CPV3FGL6ADH2N\",\"address\":{\"country_code\":\"US\"}},\"create_time\":\"2025-05-30T17:30:48Z\",\"update_time\":\"2025-05-30T17:35:03Z\",\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/46K703543C799044K\",\"rel\":\"self\",\"method\":\"GET\"}]}', '2025-05-30 17:35:04');

-- --------------------------------------------------------

--
-- Table structure for table `po_items`
--

CREATE TABLE `po_items` (
  `po_item_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_ordered` int(11) NOT NULL,
  `quantity_received` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `is_food` tinyint(1) DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `min_stock_level` int(11) DEFAULT 5,
  `supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `category_id`, `price`, `description`, `stock_quantity`, `cost_price`, `barcode`, `sku`, `product_image`, `is_food`, `expiry_date`, `min_stock_level`, `supplier_id`) VALUES
(3, 'coke', 1, 25.00, 'beverages ', 83, 20.00, '101', '101', NULL, 1, '2025-05-31', 5, NULL),
(5, 'Sandwich', 2, 45.00, 'Ham and cheese sandwich', 49, 25.00, '102', '102', NULL, 1, '2025-05-25', 5, NULL),
(6, 'Chips', 2, 15.00, 'Potato chips', 17, 8.00, '103', '103', NULL, 1, '2025-12-31', 5, NULL),
(7, 'Smartphone', 3, 15000.00, 'Android smartphone', 18, 12000.00, '201', '201', NULL, 0, NULL, 5, NULL),
(8, 'T-shirt', 4, 350.00, 'Cotton t-shirt', 30, 200.00, '301', '301', NULL, 0, NULL, 5, NULL),
(9, 'Notebook', 5, 120.00, 'Spiral notebook', 40, 80.00, '401', '401', NULL, 0, NULL, 5, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `po_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expected_delivery_date` date DEFAULT NULL,
  `status` enum('pending','partial','complete','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--

CREATE TABLE `refresh_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token`, `expires_at`, `revoked`, `created_at`) VALUES
(1, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjYyMDgsImV4cCI6MTc0NTY3MTAwOCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.H7Dixu4Shv843YJK5yRmUwprvlNBxwwLRxtVW-D4qws', '2025-04-26 14:36:48', 0, '2025-04-19 20:36:48'),
(2, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjYyMTQsImV4cCI6MTc0NTY3MTAxNCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.NMx0Dm__jl8jtS8x1EWqNgqpl7cd2B-ihlZmvAyM0Ls', '2025-04-26 14:36:54', 0, '2025-04-19 20:36:54'),
(3, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjYzODEsImV4cCI6MTc0NTY3MTE4MSwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.YRXaT-2Jz01dZyr8i6zqW-tiAZG9zaiT_2PvD4r2Ggg', '2025-04-26 14:39:41', 0, '2025-04-19 20:39:41'),
(4, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjY2NDQsImV4cCI6MTc0NTY3MTQ0NCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.ylM-kBOXAxerwCgrGWfbFady36DHZ6ria8oJ7YtjhiI', '2025-04-26 14:44:04', 0, '2025-04-19 20:44:04'),
(5, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjY4NzUsImV4cCI6MTc0NTY3MTY3NSwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.TwCH5O0WbnqKbXowh72UVV-D0efCZIL3qDUF0OrjxFE', '2025-04-26 14:47:55', 0, '2025-04-19 20:47:55'),
(6, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjY4OTcsImV4cCI6MTc0NTY3MTY5NywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.YHVDO4kwyBC31u4RI-w-P6OHwxVcNvkfGWU4-WqEtho', '2025-04-26 14:48:17', 0, '2025-04-19 20:48:17'),
(7, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjcxMzEsImV4cCI6MTc0NTY3MTkzMSwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.k4s6C05XxbCbOPMSpdNnJWwrviMyWu4pAfmmyJkDYm8', '2025-04-26 14:52:11', 0, '2025-04-19 20:52:11'),
(8, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjcyNjMsImV4cCI6MTc0NTY3MjA2MywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.RHi9n4ettT1IlKEqb6wREfzd6uAG5v9uB1E10hHK7_E', '2025-04-26 14:54:23', 0, '2025-04-19 20:54:23'),
(9, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjc3NjcsImV4cCI6MTc0NTY3MjU2NywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.n6gg69_Os29cHRGngwBC1WtiYjX1VP7y98rgLIcFebI', '2025-04-26 15:02:47', 0, '2025-04-19 21:02:47'),
(10, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjc5MjgsImV4cCI6MTc0NTY3MjcyOCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.7GBT1tVP_Mvk49zHlyLgwgaWrZPn6C61ob7K1_p4z8Q', '2025-04-26 15:05:28', 0, '2025-04-19 21:05:28'),
(11, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjc5NDQsImV4cCI6MTc0NTY3Mjc0NCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.nLNEY2xK2Em9BHtnPIcoycOoq8hJJVe7xXNFfnOEww0', '2025-04-26 15:05:44', 0, '2025-04-19 21:05:44'),
(12, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjc5OTMsImV4cCI6MTc0NTY3Mjc5MywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.CAuXGVn550K8k0SlxCMzA2_E-6qsl155SCKoqBvPvfA', '2025-04-26 15:06:33', 0, '2025-04-19 21:06:33'),
(13, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjgyOTQsImV4cCI6MTc0NTY3MzA5NCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.P-1Tray-G7tXGNJRIOFCeQwj8_6sFJnalTz5Pfq-LBw', '2025-04-26 15:11:34', 0, '2025-04-19 21:11:34'),
(14, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjgzNzMsImV4cCI6MTc0NTY3MzE3MywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.XrMlNEqVQgeZaUborFw3x63SyBGBjc-F2abvNit7Pe4', '2025-04-26 15:12:53', 0, '2025-04-19 21:12:53'),
(15, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjgzODAsImV4cCI6MTc0NTY3MzE4MCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.PDrwtEfj8aN3vbc0EyuF_R_eHKT1YxJZod-F3zrORPw', '2025-04-26 15:13:00', 0, '2025-04-19 21:13:00'),
(16, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjgzOTYsImV4cCI6MTc0NTY3MzE5NiwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19._gF1X-839gPCMBSSEes0oFspDO_eIcl5TPYSm1rScl8', '2025-04-26 15:13:16', 0, '2025-04-19 21:13:16'),
(17, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjg1ODgsImV4cCI6MTc0NTY3MzM4OCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.jj1MWXNmbtEQPSvVZSX7r9A6rthqGKEXQ-3CZA_gd1I', '2025-04-26 15:16:28', 0, '2025-04-19 21:16:28'),
(18, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjg5NzMsImV4cCI6MTc0NTY3Mzc3MywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.zuj5qe40-KaZHz7ssms9hIVHkMZ5bPMOOrN8uH41OvE', '2025-04-26 15:22:53', 0, '2025-04-19 21:22:53'),
(19, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjkwMTksImV4cCI6MTc0NTY3MzgxOSwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.JFwGCiTwQcxpwDH_wShPGiNfl0WdShzS8F1U1tOInPw', '2025-04-26 15:23:39', 0, '2025-04-19 21:23:39'),
(20, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzNzcwNTMsImV4cCI6MTc0NTk4MTg1MywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.LJcJjYrlhYqpRclhb913j2JDDXopl0dPXqWjGuxk5Mk', '2025-04-30 04:57:33', 0, '2025-04-23 10:57:33'),
(21, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzNzc2MDQsImV4cCI6MTc0NTk4MjQwNCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19._-eQDU_Rf145Wf4kF1CST_uHx7FRSgFK5CAmexjRYSk', '2025-04-30 05:06:44', 0, '2025-04-23 11:06:44'),
(22, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzNzgyNjQsImV4cCI6MTc0NTk4MzA2NCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.idGbkfclRHjEgXplFPbX0OOpxnSEw8tdAHnexJUWg6Y', '2025-04-30 05:17:44', 0, '2025-04-23 11:17:44'),
(23, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzNzgzNzksImV4cCI6MTc0NTk4MzE3OSwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.WsgPvEuBk0YFqWu3y4Le_CNtazjsX7c0TWTitQ25Ols', '2025-04-30 05:19:39', 0, '2025-04-23 11:19:39'),
(24, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzNzkxNzUsImV4cCI6MTc0NTk4Mzk3NSwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.lD353eZvrwos6YQzate6Vq-JVAcspmrtCLMqN07SrDs', '2025-04-30 05:32:55', 0, '2025-04-23 11:32:55'),
(25, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzNzkxODgsImV4cCI6MTc0NTk4Mzk4OCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.2VuQQl2osa4XV-8w2whzfe8MgeWEAzzt0A2EsUNRkSw', '2025-04-30 05:33:08', 0, '2025-04-23 11:33:08'),
(26, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzNzkzNjYsImV4cCI6MTc0NTk4NDE2NiwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.rh6xTeFp_fAuBSRIggir4G6RruG9sg4lqSJyGiwLTsg', '2025-04-30 05:36:06', 0, '2025-04-23 11:36:06'),
(27, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzNzk0ODIsImV4cCI6MTc0NTk4NDI4MiwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.vAzGFDWk0RXD5VajN7s4OrNiqILoNA5o_Cp0q4yvg5s', '2025-04-30 05:38:02', 0, '2025-04-23 11:38:02'),
(28, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzODY0MzMsImV4cCI6MTc0NTk5MTIzMywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.l_-UJj2DNWJ9Ud-tjrfavxMMnmW2Gplv9u8JC6km4-0', '2025-04-30 07:33:53', 0, '2025-04-23 13:33:53'),
(29, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzODY0ODcsImV4cCI6MTc0NTk5MTI4NywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.iGS9BaJjHOhJrJ7Q30A4S9-WxEaSJ4Pha3GaztctLDI', '2025-04-30 07:34:47', 0, '2025-04-23 13:34:47'),
(30, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzODcxMTEsImV4cCI6MTc0NTk5MTkxMSwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.apwl_WuG324fS9N87pCS5tbGT6Etrcx1k9-Q_PuvLNs', '2025-04-30 07:45:11', 0, '2025-04-23 13:45:11'),
(31, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzODc4NzYsImV4cCI6MTc0NTk5MjY3NiwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.-PLPBZoilgl00HQ6mXTLQrVGeEQirGrZpn-ZbFNprvU', '2025-04-30 07:57:56', 0, '2025-04-23 13:57:56'),
(32, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzODg0NDYsImV4cCI6MTc0NTk5MzI0NiwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.zwcf1xbqg4O8_5FZhEcJc993jDjtr5QgAkuBJMWjYr8', '2025-04-30 08:07:26', 0, '2025-04-23 14:07:26'),
(33, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzOTIxOTgsImV4cCI6MTc0NTk5Njk5OCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.d11hcsFSy_QO3VRi68DO4wBz8aPPj2oQhIpf4ADClzw', '2025-04-30 09:09:58', 0, '2025-04-23 15:09:58'),
(34, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUzOTI0NDAsImV4cCI6MTc0NTk5NzI0MCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.btKCHGqiTKWqPXq_SECr1W6iAT6z6hJsDDE89HQ8hr4', '2025-04-30 09:14:00', 0, '2025-04-23 15:14:00'),
(35, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDU0OTM2MTMsImV4cCI6MTc0NjA5ODQxMywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.92XjTdeHRBhyGUyop6k2WmrEkv4j5O9ZKTi9WL8aPu4', '2025-05-01 13:20:13', 0, '2025-04-24 19:20:13'),
(36, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDYxMDYzODAsImV4cCI6MTc0NjcxMTE4MCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.BJct8CxdFic7hHkI7I3TGOUG66F6WyjAZzck34uA5ns', '2025-05-08 15:33:00', 0, '2025-05-01 21:33:00'),
(37, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDYxMDc5NDYsImV4cCI6MTc0NjcxMjc0NiwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.-YsUpN6Fzn5AGSULkG41b09r4RxC7-caOXa2_gSiFHo', '2025-05-08 15:59:06', 0, '2025-05-01 21:59:06'),
(38, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDYxMDg3MDcsImV4cCI6MTc0NjcxMzUwNywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.em9bELuMA7IYy1XJjj8hXOCCwsxrZA2LRzBFbon--ec', '2025-05-08 16:11:47', 0, '2025-05-01 22:11:47'),
(39, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDYxMDg3MjQsImV4cCI6MTc0NjcxMzUyNCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.Z7vA1S3X-38MsUlzy2_Prmn1eMAfDAhOVoALFec8New', '2025-05-08 16:12:04', 0, '2025-05-01 22:12:04'),
(40, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDYxMDk3NTMsImV4cCI6MTc0NjcxNDU1MywiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.wSjsL36bJklkmBeW9tyzZl4t8l0B5mq3et9I5BprgHo', '2025-05-08 16:29:13', 0, '2025-05-01 22:29:13'),
(41, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDYxMDk4MDksImV4cCI6MTc0NjcxNDYwOSwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.DNOsDm8vgGePIDk6dW7zHrrUBUDvXchWKSMR2mWySzc', '2025-05-08 16:30:09', 0, '2025-05-01 22:30:09'),
(42, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDYxMTQxMzUsImV4cCI6MTc0NjcxODkzNSwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.7h8ir946aB28k7oPxsCE_ebs3U6LPCzRw4K4wHl0FgA', '2025-05-08 17:42:15', 0, '2025-05-01 23:42:15'),
(43, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDYxMTQ3ODAsImV4cCI6MTc0NjcxOTU4MCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.zJuCPJIzN7uATYN4IIMc5umfll_57615uyTnWwTD0f0', '2025-05-08 17:53:00', 0, '2025-05-01 23:53:00'),
(44, 101, '5c57b523f7af3ebd019a3b7dca6f8bb81e389d5e0bf808f67bf9350123a0f0dc', '2025-05-08 20:12:16', 0, '2025-05-02 02:12:16'),
(45, 101, 'c845732a2aa3792aa213cfafaecd63d20212ba0b7b7f0a85621a1c6ee0d4ec32', '2025-05-08 20:13:42', 0, '2025-05-02 02:13:42'),
(46, 101, 'a24c0f206feec936e52e5027cc78ff99710b493db43851ee3ce4e8ae8d8b9803', '2025-05-08 20:22:36', 0, '2025-05-02 02:22:36'),
(47, 101, 'f6b1357ed64e600e7f15bbaa87bc7be861601580cfb8e5d52abbad0580e8b245', '2025-05-08 21:29:30', 0, '2025-05-02 03:29:30'),
(48, 101, 'f33faec8fa539889fcc33eb87cc55fb9e1279b5136cc91403bb7c70dd279c835', '2025-05-08 21:31:47', 0, '2025-05-02 03:31:47'),
(49, 101, 'b8bdda0eda36a3717c69ec6d585653fe8d776022b5ba91aca0338ec8dd099f36', '2025-05-08 21:33:59', 0, '2025-05-02 03:33:59'),
(50, 101, 'b5e72445581f7317866309ef0a731348f5ded31dad0c5b14f97d4924fce4e56c', '2025-05-08 21:37:58', 0, '2025-05-02 03:37:58'),
(51, 101, '09849cff2d1766417640ac62c6b9c86fbb7a5b2577f3c2e714c37c8b985f6c45', '2025-05-08 21:42:14', 0, '2025-05-02 03:42:14'),
(52, 101, 'c40479c540a20184af8bd60f05e70de286749a35da961319b93b12bb5502e042', '2025-05-08 21:47:40', 0, '2025-05-02 03:47:40'),
(53, 101, '6351b060bbef8b269dd1b2e2dbc39efc60e10c30e654d48f78504d4e94ff87e4', '2025-05-08 21:49:13', 0, '2025-05-02 03:49:13'),
(54, 101, 'd65ad8342ed0ad1e365e638156a314d5dd4b477b31c27321619af28393935a0b', '2025-05-08 21:51:58', 0, '2025-05-02 03:51:58'),
(55, 101, '87956839d041da5c40e1942ca75432d9bc218a3368ad6236ad917e2353e78b00', '2025-05-08 21:54:49', 0, '2025-05-02 03:54:49'),
(56, 101, '59b6befd1a1023b70888939ddb3a6d38e819a85741d87a158f410a5aa8d3a723', '2025-05-08 22:02:58', 0, '2025-05-02 04:02:58'),
(57, 101, '7dc560579b3e230df11ae165e5e9b506d31423857a57824e71e5a53163ff9603', '2025-05-08 22:04:43', 0, '2025-05-02 04:04:43'),
(58, 101, 'e562b5fca80bbf41974bcbffd0ed492f217b09a35dd0c818dcca9c6fc33af398', '2025-05-19 16:10:25', 0, '2025-05-12 22:10:25'),
(59, 101, '17cf0256c713559dbc7ecff6d216ae077578dd3bf32039bfd88180e5be9bbcbb', '2025-05-19 16:31:11', 0, '2025-05-12 22:31:11'),
(60, 101, '38c7100d96f80100cbd40799d823ba3717e7cdf1f932285b415c844d6b4723ce', '2025-05-19 16:36:26', 0, '2025-05-12 22:36:26'),
(61, 101, '849c37666a9558be475cd8f760494aecbc5e833c22fe2e102b590d7f8b85252b', '2025-05-19 16:50:26', 0, '2025-05-12 22:50:26'),
(62, 101, 'c88abd495f98444e017378bb24031e9f3afae594159f8b8dae57b299a673d1b4', '2025-05-19 16:51:16', 0, '2025-05-12 22:51:16'),
(63, 101, '6a0ee2a7bc29427f9c47bb1c82d82794ca31e4f4745e87dd4821134eeccd253f', '2025-05-19 16:54:06', 0, '2025-05-12 22:54:06'),
(64, 101, 'e770bf4d6cc76a655ee497c143b80e11ec1064af7d3af3a41050b8cde0cd365e', '2025-05-19 16:57:25', 0, '2025-05-12 22:57:25'),
(65, 101, 'b030a2e8c9c14666276827af7d7ccb97c26baef21a90ec4adb80feb75290493d', '2025-05-19 17:02:40', 0, '2025-05-12 23:02:40'),
(66, 101, '68560045b157e230d8c1208157089bd515076e3ac912b914255028bf9901a9b9', '2025-05-19 17:03:14', 0, '2025-05-12 23:03:14'),
(67, 101, 'cf6b30035b7543718e166868dfa8dc161cd475b48d65c8c6229e84b2a6e0901f', '2025-05-19 17:07:25', 0, '2025-05-12 23:07:25'),
(68, 101, '2bd14b2d6dcd88fe46fd574befd1a9c916e69f091be9c9c2d5fbbf02c227056d', '2025-05-19 17:10:56', 0, '2025-05-12 23:10:56'),
(69, 101, 'f125fb719dc023cb9479671a0855d40b486e886b98c98d48928697532a98c6ac', '2025-05-19 17:13:05', 0, '2025-05-12 23:13:05'),
(70, 101, '609e63c609bb1ab1d9b25dc44444a5d20afb6e2e220cdc99e9f11c002df927f4', '2025-05-19 17:13:48', 0, '2025-05-12 23:13:48'),
(71, 101, 'c526bd0aac1212ed88b6e3cb4dc820bbaa1e675b15fd12f9cf20b94201091e45', '2025-05-19 17:14:50', 0, '2025-05-12 23:14:50'),
(72, 101, '80043a70d7c0eeb9f2874192c1961f4f41a4991383726789e8af4077d2ca2910', '2025-05-19 17:16:37', 0, '2025-05-12 23:16:37'),
(73, 101, '058f164dc8eaba29f3eac7b60bd99620488659408cdd2b699e6bac145a9fa81e', '2025-05-19 17:19:19', 0, '2025-05-12 23:19:19'),
(74, 101, '93bb6c3043911eadf0fc7b12c9815377de79c4cd46f4f8b68236fe10a23888b2', '2025-05-19 17:19:46', 0, '2025-05-12 23:19:46'),
(75, 101, '8a81d63117f02e239b90d543d1db789007ea7556f0f1fb00101c7f440694333b', '2025-05-19 17:23:52', 0, '2025-05-12 23:23:52'),
(76, 101, '5a8b4e4206e2afa77a6a5644e0c9779b357cc090937bba42a34d7149ff6bb9f5', '2025-05-21 16:48:04', 0, '2025-05-14 22:48:04'),
(77, 101, 'd69ebbc4534211f9cf27418ba11efe7a50c92834dfc3950101637c1a19dba7ee', '2025-05-21 17:08:44', 0, '2025-05-14 23:08:44'),
(78, 101, 'f16e946e54b9b12f4f447c6f048f2fe0c6647e63dddcfa079d7c614f0199aecd', '2025-05-21 17:19:43', 0, '2025-05-14 23:19:43'),
(79, 101, '165aa8222ca309506b13d27d6507fa387ac6ad002ee9ba480582e610e4395a78', '2025-05-21 19:49:14', 0, '2025-05-15 01:49:14'),
(80, 101, '2be5bd39f4bbda94f967bfa1c824816e1a8b39e78990f65b315f4ea09d549ac8', '2025-05-21 19:49:37', 0, '2025-05-15 01:49:37'),
(81, 101, 'e09b3bcef2a81f19854fd4b4fe99c8b0435e41cc4ff517dd6c69e8605ca385ae', '2025-05-27 13:05:27', 0, '2025-05-20 19:05:27'),
(82, 101, '657c670831826e084742146f0728aaa32a7b94ab9860776ccc17b15a584b5fee', '2025-05-27 13:34:38', 0, '2025-05-20 19:34:38'),
(83, 101, 'b7ef1c69fc357747cb50d15e8362c0d2dafd6b896d8264c388556f7eb1081fd5', '2025-05-27 14:05:08', 0, '2025-05-20 20:05:08'),
(84, 114, '0403a84a7b4b8aae41312e1a873e226d7b7a2cc31a3ea60210b9a1b98bf2f215', '2025-05-27 14:48:27', 0, '2025-05-20 20:48:27'),
(85, 114, '688e32b45c2946ca44a7250ea2d00c835d328b9baf4a9568b51eedca739ac855', '2025-05-27 14:48:37', 0, '2025-05-20 20:48:37'),
(86, 101, '2fe19c655aeb790be9b5c2e497eb768d665965ab42902b08921affca1477bc39', '2025-05-27 14:56:31', 0, '2025-05-20 20:56:31'),
(87, 114, '83c1366b22b398593f1b1c3b7491f6b965465a290b68eea59de3daa7e553ab45', '2025-05-27 14:58:07', 0, '2025-05-20 20:58:07'),
(88, 114, '17ca9a67ec34db4934f4dc41c72a8472724f6078866e2bd3f6f5f011b785f30d', '2025-05-27 15:00:48', 0, '2025-05-20 21:00:48'),
(89, 114, 'b1954f08e118ff62cbed7ad80cf9edbd0813d016758f9478f2977e83250dd5d2', '2025-05-27 15:00:58', 0, '2025-05-20 21:00:58'),
(90, 101, '77fe0b494ad0492ae3eb7b4f33af9e77c6d7db3005e421ec4f903e064829dd9d', '2025-05-27 15:03:00', 0, '2025-05-20 21:03:00'),
(91, 114, '91afd937ebb82db682b7c5c460ac9cdc402cdb5002e9c703997650983135e694', '2025-05-27 15:03:11', 0, '2025-05-20 21:03:11'),
(92, 101, '83007edcf19439e9a715b4c01c2a008a6330b2a7d96f1e58b2ed35f3361601e1', '2025-05-27 15:03:34', 0, '2025-05-20 21:03:34'),
(93, 114, 'e1b035e7e0dfeb80d8630e3c4db29a63dd3d7063640498d15f8eb3c0ed9aff2d', '2025-05-27 15:04:26', 0, '2025-05-20 21:04:26'),
(94, 101, 'ddd1248daf0dba36e029f8fb51284a48e273cc28fb367c55aaf79097e413942f', '2025-05-27 15:17:13', 0, '2025-05-20 21:17:13'),
(95, 114, '50c6f15e98bf11fcc8b894a4d2c44656a2249a02e83db8c45670def15a9237ea', '2025-05-27 15:21:52', 0, '2025-05-20 21:21:52'),
(96, 101, '2fc2e707955ae43ace8662ee7eb5ba56ad6ad49ae99534eac7ceb0bd8ac79d15', '2025-05-27 16:28:47', 0, '2025-05-20 22:28:47'),
(97, 114, 'c14de5fa43afd2e5ef641b4f894768d78d90020ffbe7801d4cf013de6ffe0019', '2025-05-27 16:37:23', 0, '2025-05-20 22:37:23'),
(98, 101, '2f98aca3e99576c94b93cf4c3de0e6333d2fa143fdab35b4da4c58074d18263b', '2025-05-27 16:42:44', 0, '2025-05-20 22:42:44'),
(99, 114, '788acea5ba5b2852267625ab8eb4df48cd1efafd2ccfcecdd1feffc35c44f363', '2025-05-27 16:43:48', 0, '2025-05-20 22:43:48'),
(100, 114, 'f8ef35658fe599a1c2e1102183d95a7e185a7cf64f4437af667a5561474bc66d', '2025-05-27 16:50:17', 0, '2025-05-20 22:50:17'),
(101, 114, 'e96685cefd56fc060e28e368dfce0690d9b68a4e25bb5fdd386c14dddf55738f', '2025-05-27 16:52:27', 0, '2025-05-20 22:52:27'),
(102, 101, 'b963d02a8cc5f4c70e7987fd44b8d98c53b90a72be60452b238b48530e49feec', '2025-05-28 16:18:46', 0, '2025-05-21 22:18:46'),
(103, 101, '071bd15470d2adbe18f31e9465e3ba1aece09f17f3df6acacd68a48069776026', '2025-05-28 16:21:58', 0, '2025-05-21 22:21:58'),
(104, 114, '4c84ea0fbe37582ec32c9f34fd7f159d822d6ad7c2232e27dc49c618e130c797', '2025-05-28 16:42:32', 0, '2025-05-21 22:42:32'),
(105, 114, '6b4c0b2f4266475aa6baa520c7801168afdeaed35e74b8c0f46ab046094f68dd', '2025-05-28 17:10:51', 0, '2025-05-21 23:10:51'),
(106, 101, '8165e784fd154d6f0c8a7c5a128b52f26bc786f6fdb6969bbbd6ea8dbdf65568', '2025-05-28 17:26:41', 0, '2025-05-21 23:26:41'),
(107, 101, 'a6a0eb862e5cc36a431587f4801698984f7289e2bb48b076c0d209416cb179c3', '2025-05-28 17:30:25', 0, '2025-05-21 23:30:25'),
(108, 101, 'cef5c958e853ead37ba9462203ef6baaaf8867134940b50c52d4f9ef70614dbd', '2025-05-28 17:34:37', 0, '2025-05-21 23:34:37'),
(109, 101, '1f3a641f990512eab865989a7f580aeb1acfb447d217f3cba0619ac8ea2a656e', '2025-05-28 17:35:29', 0, '2025-05-21 23:35:29'),
(110, 101, 'aaf002798ee4548b2e1cbdd284d46d92811278b23357027e5ce44608baec379d', '2025-05-28 17:46:27', 0, '2025-05-21 23:46:27'),
(111, 101, '92c2d4a2ffcc8b2f7d75f1fb13932284d959372a6fe4d3e8ce8149cc9cbc44a1', '2025-05-28 18:06:54', 0, '2025-05-22 00:06:54'),
(112, 101, '264a714ad578306cccae1a3f291358a7ac5579aee61b514169d60a9959e51a87', '2025-05-28 18:16:35', 0, '2025-05-22 00:16:35'),
(113, 101, 'cc3f4577eb79e4a193011c1fc2fa245f82569e3358e893405aaa9289f8a7144b', '2025-05-28 18:20:51', 0, '2025-05-22 00:20:51'),
(114, 101, '05d41a6c1759631985c57e80dea6e072a0cb3232420f1d78887d5ac308dcbada', '2025-05-28 18:29:01', 0, '2025-05-22 00:29:01'),
(115, 101, '84b6832e18db6f0832f1c4a51dbeb91dc92f28711dda829cd3407c26b2fc6c0c', '2025-05-28 18:32:50', 0, '2025-05-22 00:32:50'),
(116, 101, 'a79b6261b2148774a6e6ef52cd2af483d4064fda15f11e353eec62382d98887a', '2025-05-28 18:38:56', 0, '2025-05-22 00:38:56'),
(117, 101, '706b9e74b04e6c088b5ea23ff9b0516d431f79c5b46f366ee1f1638bb4f20645', '2025-05-28 18:46:21', 0, '2025-05-22 00:46:21'),
(118, 101, '8a715edf9f89a7a27e8d506e4e6dcfa4747a7cfd5676789464ff7e75113f0e5f', '2025-05-28 18:48:31', 0, '2025-05-22 00:48:31'),
(119, 101, 'c417fffe001096561ad18b3c101a638eece6cbef689dea12c1e2990966c4a088', '2025-05-29 02:18:30', 0, '2025-05-22 08:18:30'),
(120, 101, '34938971c856edbd363d487d21e4afcadc8ea061c9e6449fd81dd35d12d8f0e2', '2025-05-29 02:20:34', 0, '2025-05-22 08:20:34'),
(121, 101, '694ba6d7d9381aa9d197e347526538652ee10ae0ccb058281466346cab0d5564', '2025-05-29 02:38:12', 0, '2025-05-22 08:38:12'),
(122, 101, '143f58bb43872241d1913b9091726fb40dde72eafdeb927bc9be83805a615ff5', '2025-05-29 02:39:57', 0, '2025-05-22 08:39:57'),
(123, 101, '4e3ed5883517c5d05991471da0781a1cb64d7c904cc4e52109d38130b3f9098b', '2025-05-29 03:18:38', 0, '2025-05-22 09:18:38'),
(124, 101, 'ce1eb79182112f0ba4c288c5f727e7e4698e16e36e02be383f6693f521d6aab1', '2025-05-29 23:44:16', 0, '2025-05-23 05:44:16'),
(125, 101, '01f257787d86e8485785bcb5087f357fa4a09d5ed50154c34c26e05e3fb3c331', '2025-05-29 23:45:50', 0, '2025-05-23 05:45:50'),
(126, 101, 'f90680e50023e643245624f5a259e83f73182cdf891992d21e7ee62ae24750a3', '2025-05-30 00:01:58', 0, '2025-05-23 06:01:58'),
(127, 114, '89dd97012ad3926723af856da3b9462fe92dd0e530a0fe29d1ab39c5596da57f', '2025-05-30 00:05:20', 0, '2025-05-23 06:05:20'),
(128, 101, '89f39a01bef94c0e20c29e07224814c36e58541e3791d372a0fc94e7cf6854f1', '2025-05-30 00:06:01', 0, '2025-05-23 06:06:01'),
(129, 114, '2ba17307d09c2eaf0bd45a71770e6dcd1e0ace17586e062182474c0b7690463d', '2025-05-30 00:20:51', 0, '2025-05-23 06:20:51'),
(130, 101, 'f0102c6163eeb756d4317f51faad9d30460b0c2231c6193295c3e059f284b35f', '2025-05-30 00:21:22', 0, '2025-05-23 06:21:22'),
(131, 101, '02b7940c13cf9097008dfa872386bfa82335229b92b73fd3e0eac07ea2e4fd7f', '2025-05-30 00:23:27', 0, '2025-05-23 06:23:27'),
(132, 114, '78b50f19871620e6ec8a4bfef57beeb862613865de149901a82d31e3b08a1962', '2025-05-30 00:49:26', 0, '2025-05-23 06:49:26'),
(133, 101, '82b6db0c358e9a41d869172d4572269aa1ae09feb34dca6b4c019322d2a713b1', '2025-05-30 00:50:16', 0, '2025-05-23 06:50:16'),
(134, 101, 'c5a684288057686242af892a3a3c09bd9d4dc66d0697569e8a5b86158616994d', '2025-05-30 00:54:36', 0, '2025-05-23 06:54:36'),
(135, 114, '047b7ffc7e0874613b90814ae6e41453616bd9e7a3adab1de3bb19c8e50a7fe0', '2025-05-30 00:55:49', 0, '2025-05-23 06:55:49'),
(136, 101, '94cef7e24f2b7c25ef2dbef53df0ea7a1c24861b340c4aaa3a10b5dd5872b6e4', '2025-05-30 00:56:17', 0, '2025-05-23 06:56:17'),
(137, 114, '0b0d6e585c52b1b1079a251947583e254e34b8d92f01d6ea86b0d4d39246da2d', '2025-05-30 01:05:12', 0, '2025-05-23 07:05:12'),
(138, 101, 'e193ce1ce552417fdd6cc5d1dcd4ff7d02916d86a5b009b4f414a41580a19f06', '2025-05-30 01:05:47', 0, '2025-05-23 07:05:47'),
(139, 101, '7799920646fb1532eed867880493dfb87f1b95a0ae98d88ebc92225e45970229', '2025-05-31 15:15:55', 0, '2025-05-24 21:15:55'),
(140, 101, '85c9aec69dbfad0378e6e7220a6db7712b23decc9db680900906a19ffc3f405f', '2025-05-31 15:28:30', 0, '2025-05-24 21:28:30'),
(141, 101, '9940469869aded35f6d283b2bbebd2b8a0ad3786618119db050104016305c054', '2025-05-31 15:39:32', 0, '2025-05-24 21:39:32'),
(142, 101, '09fe0ccbbdb32f75fe5383fbba574c42a562b9f49e758e6e8b491aa916bf8d12', '2025-05-31 15:51:55', 0, '2025-05-24 21:51:55'),
(143, 101, '5f884043f6a21a51082a6c5f85c079f45217ee6a47dd2eedac18b6a0c25262fd', '2025-05-31 15:54:52', 0, '2025-05-24 21:54:52'),
(144, 101, '676e866e4ac28d2e3ccac90c3e6afe60ebc46ef4da643eb1ae8c13e16ae2f7c5', '2025-05-31 15:58:28', 0, '2025-05-24 21:58:28'),
(145, 101, '4f1a191f40123c7223d5c4c336a82b7b207b8f458f8fa71be8d1a8caa4680025', '2025-05-31 16:00:46', 0, '2025-05-24 22:00:46'),
(146, 101, '3e94ef76b0aff00307c85e43d62079d380806c688357f61f80fa2d718927d886', '2025-05-31 16:00:55', 0, '2025-05-24 22:00:55'),
(147, 101, '88224930adef0842b7440a8a23cb382250b676d635f2d5645913297c8671c1db', '2025-05-31 16:05:25', 0, '2025-05-24 22:05:25'),
(148, 101, 'b94f52148e64dc5cc3d10c08c45af018325d327fee2bdf3141e289d2ac855d1d', '2025-05-31 16:14:47', 0, '2025-05-24 22:14:47'),
(149, 101, '12b27c8953db3c57fc93f863d30cf18edb39eab59819e74f34325ee8236ecacd', '2025-05-31 16:16:50', 0, '2025-05-24 22:16:50'),
(150, 101, '775a64454e925c8cea88f1839e6144b7ec01c307850a25080a2f1326ad6f7a6a', '2025-05-31 16:18:01', 0, '2025-05-24 22:18:01'),
(151, 101, '97892bc34f645510e60229bfb2c6e3f1ad5d8083661ffb1c52da0d6a165b576d', '2025-05-31 16:21:52', 0, '2025-05-24 22:21:52'),
(152, 101, '11c2ecaf03a638f81c4949c146f36a26546e50e347907bc524448976a6ce0973', '2025-05-31 16:27:39', 0, '2025-05-24 22:27:39'),
(153, 101, '776fc5a4e752b8c0d5f1ddbd8f4e638de2fe9d710d35b2da8e9571fe84053ffa', '2025-05-31 16:27:50', 0, '2025-05-24 22:27:50'),
(154, 101, '005fcee45807a9ded128cb7614072b8b8b750a5215bbde7b801926051add1e86', '2025-05-31 16:28:03', 0, '2025-05-24 22:28:03'),
(155, 101, '5b4a45898e60507d5214af3a440d609bfd9f569b99a60466b4b8e339dfc14a00', '2025-05-31 16:30:31', 0, '2025-05-24 22:30:31'),
(156, 101, '0b33041f83f4617da201c325ea2afe308a076b9d4ede0e6bab2ee8d5169beaac', '2025-05-31 16:32:09', 0, '2025-05-24 22:32:09'),
(157, 101, '727fe0058d2132110b2fb61d6bee5b7716501df67db25c6c8536fd8c9978a4a0', '2025-05-31 16:33:13', 0, '2025-05-24 22:33:13'),
(158, 101, '8d586953963f3a5c12e640738599f280506c4b9dec9dd9182682262275301c36', '2025-05-31 16:35:24', 0, '2025-05-24 22:35:24'),
(159, 101, 'b65401c7acaf5205854ff32d4e51bc0781852d12d71a07ae89ce628e53f2f4aa', '2025-05-31 16:37:44', 0, '2025-05-24 22:37:44'),
(160, 101, '70b6578e66e12e59e05bb516495eb582f2d5f0df57ed73b7364a66d7367fd5ce', '2025-05-31 16:37:58', 0, '2025-05-24 22:37:58'),
(161, 101, '1c73eb8a5602bdca11a1d2bda50ed256dc323463bd682b4660cb2a1541aa6e9e', '2025-05-31 16:38:18', 0, '2025-05-24 22:38:18'),
(162, 101, '74fb357d9a1fb09d7f12dd3f1c74d3826a03c1811342fc817e3027fdb8c851a6', '2025-05-31 16:38:30', 0, '2025-05-24 22:38:30'),
(163, 101, '4231dd92f610915d650275e37d35b24a5924160b1885aae6d5d8adb490173d20', '2025-05-31 16:38:43', 0, '2025-05-24 22:38:43'),
(164, 101, '1056b977f3209ed9fe92c113852674a3425e06665cb595db054753922b23762d', '2025-05-31 16:42:23', 0, '2025-05-24 22:42:23'),
(165, 101, 'f06d76796d4df21cb0573cc260457cb356ea6b44835d5834827cd3a36e8d7f6a', '2025-05-31 16:42:31', 0, '2025-05-24 22:42:31'),
(166, 101, '3a40fcf73c7a0bfc72d7ba719f1c48212fe2c4af930badb291c7606e4ec98ab4', '2025-05-31 16:44:13', 0, '2025-05-24 22:44:13'),
(167, 101, '610b9425db89d6262e2817c46179dcf994912c9b233b00d4970a7789d60c3d3d', '2025-05-31 16:49:45', 0, '2025-05-24 22:49:45'),
(168, 101, 'caaef6aa94a916222ce9f2fe0c8b34c26147f4da1720fc6b3e0b7717ff28b8be', '2025-05-31 16:51:49', 0, '2025-05-24 22:51:49'),
(169, 101, 'b64f4eb9c5a7e9feea31ae804646a03365327362ed610273a9642fcb3de73b0b', '2025-05-31 16:55:50', 0, '2025-05-24 22:55:50'),
(170, 101, 'dcadd03bd12fed1f08f316c5d50350f44ed58c780806b6b1ba80671bd2585c19', '2025-05-31 17:09:38', 0, '2025-05-24 23:09:38'),
(171, 101, '84e9356da7340361bd82b61a940c421781c90f500d35f11733e0f88d8a46b126', '2025-05-31 17:12:28', 0, '2025-05-24 23:12:28'),
(172, 101, '61ac44c0c82a4b0fcdf77672e78900f13dda34a0e1d7e5adfe429e2106e1bdc6', '2025-05-31 17:12:38', 0, '2025-05-24 23:12:38'),
(173, 101, '32dbbbbfb7e956ce48d2c4994e9a15958a401ed5ef4231ecab545c884c20e4eb', '2025-05-31 17:17:17', 0, '2025-05-24 23:17:17'),
(174, 101, '368d03555c1185a59d671e7fbcfc958450acf182bc81ebfb76e5129ee6f706bc', '2025-05-31 17:17:26', 0, '2025-05-24 23:17:26'),
(175, 101, 'a0d50cc140bcf70d3639b7561421e5aab85aa60911d839c27dca5f9e924f77a5', '2025-05-31 17:19:55', 0, '2025-05-24 23:19:55'),
(176, 101, '469567e2b3a563379762a68f0ce5f713593e1ba33de85093ce397b10601d7139', '2025-05-31 17:25:11', 0, '2025-05-24 23:25:11'),
(177, 101, '73e8b4e866902d5e9f565ec175ea97b0617be5f675cddc13dc193d906f24327c', '2025-05-31 17:25:40', 0, '2025-05-24 23:25:40'),
(178, 101, 'e463f0ab684defc11d901caf388df7624b48518e26a6755f18d402f8a6fda42e', '2025-05-31 17:37:11', 0, '2025-05-24 23:37:11'),
(179, 101, '4099993ea6fa5795cfaa34debda75520e483bf73dd78fb10063ba80220fda01b', '2025-05-31 17:37:26', 0, '2025-05-24 23:37:26'),
(180, 101, '9698b73ad076e7ce2e01ae2ecdb5901215a87f49c3f065846314310fbf043075', '2025-06-01 11:36:14', 0, '2025-05-25 17:36:14'),
(181, 101, '8a03798d32fca1068dd060240cdefc12aed7eee4bb42fcfd31b771a054746325', '2025-06-01 11:42:16', 0, '2025-05-25 17:42:16'),
(182, 101, 'cae0c60fb9042f7ca3cd1f4f80db329fdcbb735a39560247c31085bfe8ace7cc', '2025-06-01 13:54:26', 0, '2025-05-25 19:54:26'),
(183, 101, '9612e97399a639664f1812b5dfed07ee0e11f516050ae02713b62c6e2a7a7ec6', '2025-06-01 14:13:16', 0, '2025-05-25 20:13:16'),
(184, 101, '1f9f818bacc3a1387600697152d0311d7fbd06297379edad35462782876a886f', '2025-06-01 14:34:46', 0, '2025-05-25 20:34:46'),
(185, 101, '10c55da63fa491d56d3b1ffdbf108c9b68d088d8dd42f7a80bdd71841602566c', '2025-06-01 14:37:00', 0, '2025-05-25 20:37:00'),
(186, 101, 'f12a18f9336c76d7690cb209545eef19103dc9d243dbc74c42392c8dd7919c6e', '2025-06-01 14:42:57', 0, '2025-05-25 20:42:57'),
(187, 101, 'a978939df7b399a96221959702d368c18942035d845ab151d91543c15f46de87', '2025-06-01 14:44:43', 0, '2025-05-25 20:44:43'),
(188, 101, 'e4a9bcc35c6f969d2b7c35c7225baff27b4f147d59bf97da4312de1a5fda2dcf', '2025-06-01 14:47:46', 0, '2025-05-25 20:47:46'),
(189, 101, 'c298406f594d0c828b7bf25714f0a1f057272f813c47fed61b89d65c65a75143', '2025-06-01 15:10:08', 0, '2025-05-25 21:10:08'),
(190, 101, 'de02db98725d429e40ed2d6d7f562731e8cec576c070c185839ced560cb713fd', '2025-06-01 15:12:31', 0, '2025-05-25 21:12:31'),
(191, 114, '804f0e2d80df66fbc95e5bbc281c75334520968ea2cb60db5056c8d159a90b5f', '2025-06-01 15:32:31', 0, '2025-05-25 21:32:31'),
(192, 101, '061b35c14aec5e7bb69461ff674cc4d293bc20c968cf95dc60965205e3aea400', '2025-06-01 15:34:10', 0, '2025-05-25 21:34:10'),
(193, 114, '87c87a33e7eefc17ef1819e6294498251483a46d49dbb271792f97f199bc1d3d', '2025-06-01 15:35:24', 0, '2025-05-25 21:35:24'),
(194, 101, 'cdb1037eec815b71d0066aea24ff2b15058d2ea9239403c0d7d1fe9dfd1aabba', '2025-06-01 15:35:41', 0, '2025-05-25 21:35:41'),
(195, 101, '2613c95379ef2bffb38c57d5c78ccf8415ea16aa88dae4029d3d397a2a00ca17', '2025-06-02 16:38:04', 0, '2025-05-26 22:38:04'),
(196, 101, 'edabc8cfb4652619223cfee16148faec4616fe12914dcf941442b171c2e3d528', '2025-06-02 16:55:33', 0, '2025-05-26 22:55:33'),
(197, 101, '53cd7009d5e46fca6b3f7c26d476f579b3dfbb76d462078f9b91c7f297e7bbe1', '2025-06-02 17:00:06', 0, '2025-05-26 23:00:06'),
(198, 101, 'c5aa58d62ade3b387e8be124e537ee44701667eb78ec9f818759824ef71c290c', '2025-06-02 17:00:56', 0, '2025-05-26 23:00:56'),
(199, 101, 'e980ff190d5ce74edd1da28a3572cb5f95ab1cc71d773eff8972be452530bce1', '2025-06-02 17:11:04', 0, '2025-05-26 23:11:04'),
(200, 101, '91478b069829fa24f63e52a1cc6be6966b0b08781c6d5de9dc66e604f1b04834', '2025-06-02 17:15:21', 0, '2025-05-26 23:15:21'),
(201, 114, '491d2b9a5b79c686e78bda81fd8c7ba41b39c6bbb25f56d41250065696d27cd4', '2025-06-02 17:19:50', 0, '2025-05-26 23:19:50'),
(202, 101, 'a39dcb86ffb35a27d1cd5b02f6e81098f6d1c14c7df4e8c4bdd1c3a13d012754', '2025-06-02 17:20:14', 0, '2025-05-26 23:20:14'),
(203, 101, '06492e335b2680c58eee80710d37a1d141614ad7b6055ba369609402ed0f26cd', '2025-06-02 17:26:49', 0, '2025-05-26 23:26:49'),
(204, 101, '413e4fcf6d806caf0caa22c5111d9876b7d426d829a378c9802fef845c99fb68', '2025-06-02 17:28:57', 0, '2025-05-26 23:28:57'),
(205, 114, 'fe1e5cd2d8b46a701f9cfa581e62a3ade225f15fa286d402ec89dc0da80eec18', '2025-06-02 17:29:38', 0, '2025-05-26 23:29:38'),
(206, 101, 'da690e7bf523c05256608853551ecc3c67d340b608da09f800eb0ef8a7b5c74d', '2025-06-02 17:29:44', 0, '2025-05-26 23:29:44'),
(207, 114, 'f06c1f0a98835bae4cf19948d6446f618ee599d880d33c79a76ddb4052247589', '2025-06-02 17:31:33', 0, '2025-05-26 23:31:33'),
(208, 101, 'fc6f973bb988a13301b79ca852f5ec38eaf28f88a229092452d3509eb1e2b50b', '2025-06-02 17:31:42', 0, '2025-05-26 23:31:42'),
(209, 101, 'da814a1a73c558dd683e940540cf614f08049ceaa7a3913fdc93764a5df26e4e', '2025-06-02 17:45:09', 0, '2025-05-26 23:45:09'),
(210, 101, '5d7d006647c8465039d98ef9985ec33844254e34e9641c5722bb5e135562e118', '2025-06-02 17:52:02', 0, '2025-05-26 23:52:02'),
(211, 114, '08f1dd18ea0df268d66b40cbbc75c4c846fe8f3149734072195c7ff1dacd6cda', '2025-06-02 18:42:24', 0, '2025-05-27 00:42:24'),
(212, 101, '359f474b4bf0181bc38ae9fcd11b6cb966697b04d8361ea801a134190f6f3be9', '2025-06-02 18:42:53', 0, '2025-05-27 00:42:53'),
(213, 114, '0dbdd737773f7931fda41ca0b68ad8f0164247eb39d1fce7defd47785c2483b8', '2025-06-02 18:43:09', 0, '2025-05-27 00:43:09'),
(214, 101, 'c41ff894f83e171ebea3ba77695f9aad62b43d42598472240a8228a5b2728ec4', '2025-06-02 18:43:33', 0, '2025-05-27 00:43:33'),
(215, 101, 'a7f45d93c4b5d3be343d5d4d8a4cb8a63ce2035cf9e773b7718c738ed2179121', '2025-06-02 18:49:30', 0, '2025-05-27 00:49:30'),
(216, 101, '092f3b9ec3221d30ba58b2cc8a9a7c5b605f757f3e62bfdd59eb3aa8b2335c53', '2025-06-02 19:00:43', 0, '2025-05-27 01:00:43'),
(217, 114, '193f25ed23e302f9bdd5654aba2f297e66b074e40301d458ce38fff80b96805a', '2025-06-02 19:04:07', 0, '2025-05-27 01:04:07'),
(218, 101, 'd77c3a6105e32466c42fe8de61d12a48e0c379b43eba3eda4b272d5b059fae8f', '2025-06-02 19:11:24', 0, '2025-05-27 01:11:24'),
(219, 101, 'e3013e0fa5f75778b501c12f46cfc9f4396ad2ba97097cc1e98e6d8966f6d5f2', '2025-06-02 19:13:51', 0, '2025-05-27 01:13:51'),
(220, 114, '9591089e2f079746487ea4f2e7deda9306cddda5069b472cec3925dd358181f7', '2025-06-02 19:15:40', 0, '2025-05-27 01:15:40'),
(221, 101, '61bd4a0f91a8b661b095b0bf95303ad9034f344ef5152ac19a1482a6d1b96d84', '2025-06-02 19:16:10', 0, '2025-05-27 01:16:10'),
(222, 101, '7f2647fa6de6fd4169b67454e873e9b6de4601f6d097074747d2d92519081a24', '2025-06-02 19:26:25', 0, '2025-05-27 01:26:25'),
(223, 101, '214a660f0833998bb1bedbf1a9aefc4f19ee9baaf0dd3833b1da724f28038552', '2025-06-02 19:29:59', 0, '2025-05-27 01:29:59'),
(224, 101, '7ade490267754bd5d212f437cf9c536e6580e5304a470d9eca000951c04a3f3d', '2025-06-02 19:32:37', 0, '2025-05-27 01:32:37'),
(225, 114, '60a4db373e5a9d0f3b4a8393edd2a9b7e4c9ffeaf338b3fff520c4a99b3ea617', '2025-06-02 19:33:04', 0, '2025-05-27 01:33:04'),
(226, 101, '12471b55a23e41b20d9cc3726eaa90ff33e1e3e694a927034f9e976afa4971e9', '2025-06-02 19:33:26', 0, '2025-05-27 01:33:26'),
(227, 101, 'ddef096d0bec62765775a228e719bbca735a981684253257e91763abf01d6bbb', '2025-06-02 19:49:51', 0, '2025-05-27 01:49:51'),
(228, 101, 'e80db9212dc5fe5d216b9f2e313ffdbe355787f3d7ee47eff5ddc398d476edd8', '2025-06-02 20:19:26', 0, '2025-05-27 02:19:26'),
(229, 101, '301df1956074bd8ddd2da1854a30ff64d14ab21ac0b79dd1336b8f97d8561f77', '2025-06-02 20:31:01', 0, '2025-05-27 02:31:01'),
(230, 101, 'e2791187cea4e64d19e749c84d0b6018d33abca145ff13423803c57a0a677f33', '2025-06-02 20:37:56', 0, '2025-05-27 02:37:56'),
(231, 101, '525074610a3f2e67e144df00f7d65601cc6951b4f7ed393b9c1e4097fda15e8e', '2025-06-02 20:42:11', 0, '2025-05-27 02:42:11'),
(232, 101, '3fa4c9e3984de6d95e6aee9b83f5124c914a16c2aa6f7b2b2f5be8d67221882e', '2025-06-02 20:49:07', 0, '2025-05-27 02:49:07'),
(233, 101, '56d85b01941a75634f538d366f73cf3e6309de82e425188461f18d15b523ff64', '2025-06-02 20:51:39', 0, '2025-05-27 02:51:39'),
(234, 101, 'f39369a301d7e716612b9518bb0f2b5fd7876a73ffda97e2c23b83313cd1a133', '2025-06-02 20:52:31', 0, '2025-05-27 02:52:31'),
(235, 114, '88692b8410ca855e42053e32688f189037647cb4cd45c3ee8f13d720059b1e75', '2025-06-03 03:01:02', 0, '2025-05-27 03:01:02'),
(236, 101, 'c4d9fb093ebaf43c38509f07d2431d908fe816b132e7202f729b7715f6651914', '2025-06-03 03:01:30', 0, '2025-05-27 03:01:30'),
(237, 101, '80fe7b6cf8df28e7d8f9f26501703cc4149c2415c26222c5b3de0adb40aeef6a', '2025-06-03 17:28:31', 0, '2025-05-27 17:28:31'),
(238, 101, 'a264b5d134b9a7b0549860fb630b082d581c02b27f255c3e39e5502e6b3a3dd9', '2025-06-03 18:38:57', 0, '2025-05-27 18:38:57'),
(239, 101, '42e7d423935940f9a5cb20cadc6c2a920d2c977e858668b53609a4fd5e049122', '2025-06-03 18:47:29', 0, '2025-05-27 18:47:29'),
(240, 101, '4c35c7d44ee28782d487c18a62ee3db0ea83629d7e5aea08ebbe244f86ddeae3', '2025-06-03 18:49:17', 0, '2025-05-27 18:49:17'),
(241, 101, 'b75628768df68912fded98904c0a4f9b3529af08728506700ba5106d100932b5', '2025-06-03 18:49:58', 0, '2025-05-27 18:49:58'),
(242, 101, 'bfa310832105ddf72e9e6ec3bd3d6c36cdd98626dbcb32a24b9b62af6de3696c', '2025-06-03 18:51:21', 0, '2025-05-27 18:51:21'),
(243, 101, 'bd772b1b9f7dcd6ea2bbe955f8f30e7091f99be51d29fc973806f64046b8d6a8', '2025-06-03 18:51:35', 0, '2025-05-27 18:51:35'),
(244, 101, '3b6f95fe4b0c655684e0a20b433f26a7de13abe50cd00a2522aa1cd8b6b856a7', '2025-06-03 18:52:02', 0, '2025-05-27 18:52:02'),
(245, 101, '1d4298ae963a07c83bd8b7272cec728990eae77029530bbbc73e073b56c8a11d', '2025-06-03 18:52:12', 0, '2025-05-27 18:52:12'),
(246, 101, 'dd6ce029be3f2683512b0a60366d6e4f35301ca9d7abc725b0a5c516c0388153', '2025-06-03 18:52:18', 0, '2025-05-27 18:52:18'),
(247, 101, '3f085b21a8e12cb0531c0181c7700e9c39608ba8650245903a451f713003cb3e', '2025-06-03 18:52:42', 0, '2025-05-27 18:52:42'),
(248, 101, '52a467d81a0fba7831ab344058484ea743980e225817735e10b5d61c0b7992ad', '2025-06-03 21:43:13', 0, '2025-05-27 21:43:13'),
(249, 114, '4965f6770acb1dd9d8bb05f573ec7c257740e316aa125b111231a26954b679b1', '2025-06-03 21:43:27', 0, '2025-05-27 21:43:27'),
(250, 101, '4fb83bc09c3d7e5891f8eb881624e71bbdbfa1ba308eea4ebfcd2a8486af9455', '2025-06-03 21:44:24', 0, '2025-05-27 21:44:24'),
(251, 101, '6e6849d6fbeb02639e600cc5efcf34988b0ee52108177f9a2a3d3f0f4591cbbf', '2025-06-04 01:22:37', 0, '2025-05-28 01:22:37'),
(252, 101, '7b276d7b061e8fa421051784d6ad251c9eeb6952f7563f7d2fc1aebe6a457256', '2025-06-04 04:28:37', 0, '2025-05-28 04:28:37'),
(253, 114, '244228716a4a1a8131cf2a9b3390a4a55ceb0d99b285faccd2ca7cba7c03eb9f', '2025-06-04 05:05:57', 0, '2025-05-28 05:05:57'),
(254, 101, '817de6ea376ea7333c90f804533f9cb4ab4bafc3693255a94b58411a36acaf0c', '2025-06-04 05:47:10', 0, '2025-05-28 05:47:10'),
(255, 101, 'c61bdaa217acdc4bc5e76cf2bda2e697da7a90ca8783c6057eae247071222973', '2025-06-04 06:02:23', 0, '2025-05-28 06:02:23'),
(256, 114, '214411ad68bc5036cd97c5c75681ca9ec484a9d3e449ed87abbf3afb05a713ef', '2025-06-04 06:03:39', 0, '2025-05-28 06:03:39'),
(257, 101, '8550259653e5f3a1a525493e0bd2213de73b4b4a98db4be67ee707978ccd5e26', '2025-06-04 06:04:17', 0, '2025-05-28 06:04:17'),
(258, 101, '6ef0d2c42ea27baba9e2b46d7a634c5082be992826c93fc0d4b2ab04e1c2669d', '2025-06-06 12:04:13', 0, '2025-05-30 12:04:13'),
(259, 114, '7b027907d5c5bb87e20066dfb7017b798a8932d09a05552807a1c2f39426dadb', '2025-06-06 12:04:37', 0, '2025-05-30 12:04:37'),
(260, 101, 'ac904545647d06c6762593e548d035d60712d6dac9ab927e3ae58f28954060f5', '2025-06-06 12:06:33', 0, '2025-05-30 12:06:33'),
(261, 114, 'f5ff6f696942fbd4e2a3d17458979ba311bbcd2311cba75fe95e0528e4d2c84a', '2025-06-06 12:07:15', 0, '2025-05-30 12:07:15'),
(262, 101, 'c9683778c8f10f86d0692f28bb47601da1e39ecd58472ad6e663d989a112843d', '2025-06-06 12:08:13', 0, '2025-05-30 12:08:13'),
(263, 114, 'cb7fcd38914f3bb1856f8dddb29801a62cbec06fbf95c1373e661e8cb9970ed8', '2025-06-06 12:10:30', 0, '2025-05-30 12:10:30'),
(264, 101, '726c2c3f53b4f274089c3763a94abbc91e3a710b24452ac309b68ea2637795e6', '2025-06-06 12:14:24', 0, '2025-05-30 12:14:24'),
(265, 114, 'e2c6b09704512f2bc207937ca8fff51b049dd39e4e7da3576d27d457704c8763', '2025-06-07 00:06:32', 0, '2025-05-31 00:06:32'),
(266, 101, '80236dfba7df390b0b6a065a47b0fc630c9898bd3568661d61d43a75ed5f1807', '2025-06-07 00:06:43', 0, '2025-05-31 00:06:43'),
(267, 101, '863babeac749f72e4755fe7368479a6a13c7df2a02d604d66acb990999957397', '2025-06-07 00:08:55', 0, '2025-05-31 00:08:55'),
(268, 114, '154a1100c773c0f7697f6ce687973613bae64248bcb9b83cf6bceaa3f2637ed0', '2025-06-07 00:17:30', 0, '2025-05-31 00:17:30'),
(269, 101, '463ba8211ca6a3c0ff1d733e37a717d631b4c9d79cd0e9ef195df6edcf922c12', '2025-06-07 00:19:40', 0, '2025-05-31 00:19:40'),
(270, 114, 'c5da70acf78b9dd704fafc022a593d947e782ce75e03b75faecac9a6e04cf122', '2025-06-07 00:19:53', 0, '2025-05-31 00:19:53'),
(271, 101, '7ee794748384a443224bf8f54bb0bf43d7cdebe4e395f00d21f99ed9fd717200', '2025-06-07 00:20:37', 0, '2025-05-31 00:20:37'),
(272, 101, '8e7989222cee8527f3a2a158cab7849c95778c85085c4a7276e1463f2d3b76f4', '2025-06-07 00:23:01', 0, '2025-05-31 00:23:01'),
(273, 101, '2cda1c2b47681a6e6810ad7eb8f01614e604725c219b617aab21a3f40223ee8e', '2025-06-07 00:23:17', 0, '2025-05-31 00:23:17'),
(274, 101, 'b3899ab2a99fe544d1f70c6a3f580db73459903c697e175e86776bd9abe05421', '2025-06-07 00:24:24', 0, '2025-05-31 00:24:24'),
(275, 101, '1d95670c9fc4168317a84b7933589bf5d3d163246e542b3c24a6e06e96cce2fd', '2025-06-07 00:24:44', 0, '2025-05-31 00:24:44'),
(276, 114, 'ab3fce226a868887ffbb20d8fb8b0f64a97de6a25f30c2fe75794f44e52b09c2', '2025-06-07 00:24:58', 0, '2025-05-31 00:24:58'),
(277, 114, 'afc1ed90f61b05473f5a7158645555780c9a449ec8e78951bbeb00e396d57e79', '2025-06-07 00:25:10', 0, '2025-05-31 00:25:10'),
(278, 101, '0631d5f14fe2d18e7cd55a5e081397496710f2be17b1d364e23f1f6f748d8900', '2025-06-07 00:29:03', 0, '2025-05-31 00:29:03'),
(279, 114, 'a5234fedf78b93cfe4a44fd282cb538eaf0ba3f428001c0f38a66b640c1882ff', '2025-06-07 00:53:37', 0, '2025-05-31 00:53:37'),
(280, 101, 'e0192c9f7d188096198a0e25cc26df6b7c9cb1805033e321dc0fef7ebad25c64', '2025-06-07 00:54:28', 0, '2025-05-31 00:54:28'),
(281, 114, '1d6e532ad5dc029cae6e8650763ef18708021a02b6e4753f7e93ceadad2b110f', '2025-06-07 01:30:27', 0, '2025-05-31 01:30:27'),
(282, 101, '1691e28d52f8afb12188c306d24dd2d5ac14ec4489aa91b9c9f8895ffe2ed7a3', '2025-06-07 01:35:28', 0, '2025-05-31 01:35:28'),
(283, 101, '00f68d669ab8720ad18418606cc28e9b4a1b23c87f52d997649160b022feacfc', '2025-06-07 01:59:28', 0, '2025-05-31 01:59:28'),
(284, 101, '033865d0e2e5588fcea594a47c435e8bd7b8d756881c3a60d64cb6838b2abce5', '2025-06-07 02:09:37', 0, '2025-05-31 02:09:37'),
(285, 114, '0219b9a91fa01fb67b67746380e16c55436529d2ba597162c2a87dcc6b493a48', '2025-06-07 02:10:38', 0, '2025-05-31 02:10:38'),
(286, 101, '55ebe6ca0a0c8dc10f73a2bbbbee7149610b6cc4df6d1419159c86694f8b21a3', '2025-06-10 21:02:17', 0, '2025-06-03 21:02:17');

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `refund_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `refund_reason` text NOT NULL,
  `refund_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `name`, `contact_person`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 'Coka Cola Corps.', 'example', '09123456781', '', 'example street', '2025-05-22 22:37:15');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `role` enum('Admin','Cashier','Manager') NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `role`, `password_hash`, `username`, `email`, `created_at`, `updated_at`) VALUES
(101, 'Admin', '21a450ca63e673188f62d47608211457ed9f61dc8184b39c38d8fdf4b9cbaa71', 'Draine', 'draine@gmail.com', '2025-03-04 02:31:19', '2025-05-01 15:53:00'),
(102, 'Cashier', '1123', 'micos', 'micos@gmail.com', '2025-03-04 02:31:19', '2025-03-09 14:21:28'),
(103, 'Admin', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'admin', 'admin@example.com', '2025-03-04 02:38:42', '2025-03-09 14:21:28'),
(114, 'Cashier', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'aman', 'aman1@gmail.com', '2025-05-20 12:46:19', '2025-05-20 12:46:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `staff_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `staff_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `cashier_id` (`cashier_id`),
  ADD KEY `idx_paypal_transaction_id` (`paypal_transaction_id`),
  ADD KEY `idx_payment_method` (`payment_method`);

--
-- Indexes for table `paypal_transaction_details`
--
ALTER TABLE `paypal_transaction_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_paypal_order_id` (`paypal_order_id`);

--
-- Indexes for table `po_items`
--
ALTER TABLE `po_items`
  ADD PRIMARY KEY (`po_item_id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `products_ibfk_2` (`supplier_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`po_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`refund_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `paypal_transaction_details`
--
ALTER TABLE `paypal_transaction_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `po_items`
--
ALTER TABLE `po_items`
  MODIFY `po_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `po_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `refund_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `paypal_transaction_details`
--
ALTER TABLE `paypal_transaction_details`
  ADD CONSTRAINT `fk_paypal_details_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`) ON DELETE CASCADE;

--
-- Constraints for table `po_items`
--
ALTER TABLE `po_items`
  ADD CONSTRAINT `po_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `po_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD CONSTRAINT `refresh_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
