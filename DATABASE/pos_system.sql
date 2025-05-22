-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2025 at 05:07 PM
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
  `transaction_status` enum('Pending','Success','Failed') DEFAULT 'Pending',
  `cash_received` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `payment_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `cashier_id` int(11) DEFAULT NULL
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

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `category_id`, `price`, `description`, `stock_quantity`, `cost_price`, `barcode`, `sku`, `product_image`, `is_food`, `expiry_date`, `min_stock_level`, `supplier_id`) VALUES
(3, 'coke', NULL, 25.00, 'beverages ', 6, 20.00, '101', '101', NULL, 1, '2025-05-31', 5, NULL);

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
(104, 114, '4c84ea0fbe37582ec32c9f34fd7f159d822d6ad7c2232e27dc49c618e130c797', '2025-05-28 16:42:32', 0, '2025-05-21 22:42:32');

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
  ADD KEY `cashier_id` (`cashier_id`);

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
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `po_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

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
