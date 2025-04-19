-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 19, 2025 at 03:29 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('Cash','PayPal') NOT NULL,
  `paypal_transaction_id` varchar(255) DEFAULT NULL,
  `transaction_status` enum('Pending','Success','Failed') DEFAULT 'Pending'
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
  `description` text DEFAULT NULL
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
(19, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDUwNjkwMTksImV4cCI6MTc0NTY3MzgxOSwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.JFwGCiTwQcxpwDH_wShPGiNfl0WdShzS8F1U1tOInPw', '2025-04-26 15:23:39', 0, '2025-04-19 21:23:39');

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
(101, 'Admin', '21a450ca63e673188f62d47608211457ed9f61dc8184b39c38d8fdf4b9cbaa71', 'Draine', 'draine@gmail.com', '2025-03-04 02:31:19', '2025-04-19 13:23:39'),
(102, 'Cashier', '1123', 'micos', 'micos@gmail.com', '2025-03-04 02:31:19', '2025-03-09 14:21:28'),
(103, 'Admin', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'admin', 'admin@example.com', '2025-03-04 02:38:42', '2025-03-09 14:21:28'),
(105, 'Cashier', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'hezekiah', 'hezekiah@gmail.com', '2025-03-09 16:25:04', '2025-03-09 16:25:04'),
(110, 'Cashier', '4739ee3bd29e4f415da8ba9298a087e0fdc9c61378420ba8fbbab298bd74c4df', 'aman', 'aman1@gmail.com', '2025-04-19 12:28:01', '2025-04-19 12:28:01'),
(111, 'Cashier', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'newuser', 'newuser@example.com', '2025-04-19 12:43:08', '2025-04-19 12:43:08');

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
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `refund_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- Constraints for dumped tables
--

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
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

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
