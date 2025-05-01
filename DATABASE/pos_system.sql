-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2025 at 05:58 PM
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
(43, 101, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NDYxMTQ3ODAsImV4cCI6MTc0NjcxOTU4MCwiZGF0YSI6eyJ1c2VyX2lkIjoxMDEsInR5cGUiOiJyZWZyZXNoIn19.zJuCPJIzN7uATYN4IIMc5umfll_57615uyTnWwTD0f0', '2025-05-08 17:53:00', 0, '2025-05-01 23:53:00');

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
(105, 'Cashier', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'hezekiah', 'hezekiah@gmail.com', '2025-03-09 16:25:04', '2025-03-09 16:25:04'),
(113, 'Cashier', '4739ee3bd29e4f415da8ba9298a087e0fdc9c61378420ba8fbbab298bd74c4df', 'Tally', 'tally23@gmail.com', '2025-04-23 06:09:32', '2025-04-23 06:09:32');

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
  ADD KEY `order_id` (`order_id`);

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `po_items`
--
ALTER TABLE `po_items`
  MODIFY `po_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `po_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `refund_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

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
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

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
