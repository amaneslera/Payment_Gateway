-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2025 at 07:19 PM
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
(49, 51, 'Cash', NULL, '', 20.00, 5.00, '2025-05-26 17:15:59', 114);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `cashier_id` (`cashier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
