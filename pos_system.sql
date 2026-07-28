-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 28, 2026 at 05:22 AM
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
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half-day') DEFAULT 'present',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `status` enum('active','inactive','terminated') DEFAULT 'active',
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `first_name`, `last_name`, `middle_name`, `email`, `phone`, `position`, `department`, `date_hired`, `status`, `user_id`, `created_at`) VALUES
(1, 'EMP001', 'Juan', 'Dela Cruz', NULL, NULL, NULL, 'CEO', 'Management', NULL, 'active', 1, '2026-07-26 12:04:58'),
(2, 'EMP002', 'Maria', 'Santos', NULL, NULL, NULL, 'Finance Head', 'Finance', NULL, 'active', 2, '2026-07-26 12:04:58'),
(3, 'EMP003', 'Jose', 'Rizal', NULL, NULL, NULL, 'HR Manager', 'Human Resources', NULL, 'active', NULL, '2026-07-26 12:04:58');

-- --------------------------------------------------------

--
-- Table structure for table `finance_requests`
--

CREATE TABLE `finance_requests` (
  `id` int(11) NOT NULL,
  `request_type` enum('budget','reimbursement','advance','other') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `requested_by` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_processed` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('sick','vacation','emergency','maternity','paternity','other') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `date_filed` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_processed` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `gross_pay` decimal(10,2) DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `net_pay` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','paid') DEFAULT 'pending',
  `paid_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`, `module`, `description`, `created_at`) VALUES
(1, 'view_employees', 'hr', 'View employee list and profiles', '2026-07-26 12:04:58'),
(2, 'manage_employees', 'hr', 'Create, edit, delete employees', '2026-07-26 12:04:58'),
(3, 'view_attendance', 'hr', 'View attendance records', '2026-07-26 12:04:58'),
(4, 'manage_attendance', 'hr', 'Create, edit, delete attendance', '2026-07-26 12:04:58'),
(5, 'view_leave_requests', 'hr', 'View leave requests', '2026-07-26 12:04:58'),
(6, 'approve_leave_requests', 'hr', 'Approve or reject leave requests', '2026-07-26 12:04:58'),
(7, 'manage_leave_requests', 'hr', 'Create, edit, delete leave requests', '2026-07-26 12:04:58'),
(8, 'view_finance', 'finance', 'View finance dashboard and reports', '2026-07-26 12:04:58'),
(9, 'manage_payroll', 'finance', 'Create, edit, process payroll', '2026-07-26 12:04:58'),
(10, 'view_payroll', 'finance', 'View payroll records', '2026-07-26 12:04:58'),
(11, 'approve_finance_requests', 'finance', 'Approve or reject finance requests', '2026-07-26 12:04:58'),
(12, 'manage_finance_requests', 'finance', 'Create, edit, delete finance requests', '2026-07-26 12:04:58'),
(13, 'view_supply_requests', 'supply', 'View supply requests', '2026-07-26 12:04:58'),
(14, 'create_supply_requests', 'supply', 'Create supply requests', '2026-07-26 12:04:58'),
(15, 'approve_supply_requests', 'supply', 'Approve or reject supply requests', '2026-07-26 12:04:58'),
(16, 'manage_supply_requests', 'supply', 'Edit, delete supply requests', '2026-07-26 12:04:58'),
(17, 'manage_roles', 'system', 'Create, edit, delete roles', '2026-07-26 12:04:58'),
(18, 'manage_users', 'system', 'Create, edit, delete users', '2026-07-26 12:04:58'),
(19, 'view_reports', 'system', 'View all reports', '2026-07-26 12:04:58');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `vat_type` enum('V','E','Z') DEFAULT 'V'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_code`, `name`, `description`, `original_price`, `selling_price`, `stock`, `category`, `created_at`, `vat_type`) VALUES
(1, 'P001', 'Coca-Cola 1.5L', 'Carbonated soft drink', 74.80, 83.78, 37, 'Beverages', '2026-07-04 10:33:13', 'V'),
(2, 'P002', 'Pancit Canton', 'Instant noodles', 13.39, 15.00, 99, 'Groceries', '2026-07-04 10:33:13', 'V'),
(4, 'P004', 'Milo 1kg', 'Chocolate malt drink', 218.75, 245.00, 23, 'Groceries', '2026-07-04 10:33:13', 'V'),
(5, 'P005', 'Nescafe 3-in-1', 'Coffee mix', 58.04, 65.00, 58, 'Beverages', '2026-07-04 10:33:13', 'V'),
(6, 'P003', 'Sardines 123g', '', 34.09, 38.18, 0, 'Canned Goods', '2026-07-05 10:05:37', 'V'),
(13, 'P007', 'Coca-Cola 2L', 'refreshing drinks', 80.36, 90.00, 120, 'Drinks', '2026-07-20 13:28:07', 'E');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `created_at`) VALUES
(1, 'super_admin', 'Full system access - can manage everything', '2026-07-26 12:04:58'),
(2, 'ceo', 'CEO/Manager - can approve supply requests and view all', '2026-07-26 12:04:58'),
(3, 'finance', 'Finance - manage finance module, approve finance requests', '2026-07-26 12:04:58'),
(4, 'hr', 'HR - manage employees, attendance, leave requests', '2026-07-26 12:04:58'),
(5, 'staff', 'Basic access - submit requests, view own data', '2026-07-26 12:04:58');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 11, '2026-07-26 12:04:58'),
(2, 1, 6, '2026-07-26 12:04:58'),
(3, 1, 15, '2026-07-26 12:04:58'),
(4, 1, 14, '2026-07-26 12:04:58'),
(5, 1, 4, '2026-07-26 12:04:58'),
(6, 1, 2, '2026-07-26 12:04:58'),
(7, 1, 12, '2026-07-26 12:04:58'),
(8, 1, 7, '2026-07-26 12:04:58'),
(9, 1, 9, '2026-07-26 12:04:58'),
(10, 1, 17, '2026-07-26 12:04:58'),
(11, 1, 16, '2026-07-26 12:04:58'),
(12, 1, 18, '2026-07-26 12:04:58'),
(13, 1, 3, '2026-07-26 12:04:58'),
(14, 1, 1, '2026-07-26 12:04:58'),
(15, 1, 8, '2026-07-26 12:04:58'),
(16, 1, 5, '2026-07-26 12:04:58'),
(17, 1, 10, '2026-07-26 12:04:58'),
(18, 1, 19, '2026-07-26 12:04:58'),
(19, 1, 13, '2026-07-26 12:04:58'),
(32, 2, 15, '2026-07-26 12:04:58'),
(33, 2, 3, '2026-07-26 12:04:58'),
(34, 2, 1, '2026-07-26 12:04:58'),
(35, 2, 8, '2026-07-26 12:04:58'),
(36, 2, 5, '2026-07-26 12:04:58'),
(37, 2, 10, '2026-07-26 12:04:58'),
(38, 2, 19, '2026-07-26 12:04:58'),
(39, 2, 13, '2026-07-26 12:04:58'),
(47, 3, 11, '2026-07-26 12:04:58'),
(48, 3, 12, '2026-07-26 12:04:58'),
(49, 3, 9, '2026-07-26 12:04:58'),
(50, 3, 8, '2026-07-26 12:04:58'),
(51, 3, 10, '2026-07-26 12:04:58'),
(52, 3, 19, '2026-07-26 12:04:58'),
(53, 3, 13, '2026-07-26 12:04:58'),
(54, 4, 6, '2026-07-26 12:04:58'),
(55, 4, 4, '2026-07-26 12:04:58'),
(56, 4, 2, '2026-07-26 12:04:58'),
(57, 4, 7, '2026-07-26 12:04:58'),
(58, 4, 3, '2026-07-26 12:04:58'),
(59, 4, 1, '2026-07-26 12:04:58'),
(60, 4, 5, '2026-07-26 12:04:58'),
(61, 4, 19, '2026-07-26 12:04:58'),
(69, 5, 14, '2026-07-26 12:04:58'),
(70, 5, 3, '2026-07-26 12:04:58'),
(71, 5, 1, '2026-07-26 12:04:58'),
(72, 5, 5, '2026-07-26 12:04:58'),
(73, 5, 13, '2026-07-26 12:04:58');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `subtotal_original` decimal(10,2) DEFAULT 0.00,
  `vat_amount` decimal(10,2) DEFAULT 0.00,
  `vatable_total` decimal(10,2) DEFAULT 0.00,
  `vat_exempt_total` decimal(10,2) DEFAULT 0.00,
  `vat_zero_total` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `discount_type` enum('none','senior_citizen') DEFAULT 'none',
  `senior_id` varchar(50) DEFAULT NULL,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `payment_amount` decimal(10,2) DEFAULT 0.00,
  `change_amount` decimal(10,2) DEFAULT 0.00,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `invoice_number`, `user_id`, `customer_name`, `subtotal_original`, `vat_amount`, `vatable_total`, `vat_exempt_total`, `vat_zero_total`, `discount_amount`, `discount_type`, `senior_id`, `grand_total`, `payment_amount`, `change_amount`, `sale_date`) VALUES
(3, 'INV-20260705-1214', 1, '', 218.75, 26.25, 218.75, 0.00, 0.00, 0.00, 'none', NULL, 245.00, 500.00, 255.00, '2026-07-05 09:30:31'),
(4, 'INV-20260705-7714', 1, '', 74.80, 8.98, 74.80, 0.00, 0.00, 0.00, 'none', NULL, 83.78, 100.00, 16.22, '2026-07-05 09:31:52'),
(5, 'INV-20260705-9630', 1, '', 74.80, 8.98, 74.80, 0.00, 0.00, 16.76, 'senior_citizen', NULL, 67.02, 100.00, 32.98, '2026-07-05 09:43:06'),
(6, 'INV-20260705-4484', 1, '', 74.80, 8.98, 74.80, 0.00, 0.00, 0.00, 'none', NULL, 83.78, 100.00, 16.22, '2026-07-05 09:50:01'),
(7, 'INV-20260705-9849', 1, '', 290.18, 34.82, 290.18, 0.00, 0.00, 0.00, 'none', NULL, 325.00, 400.00, 75.00, '2026-07-05 09:52:56'),
(8, 'INV-20260705-5548', 1, '', 326.67, 39.20, 326.67, 0.00, 0.00, 0.00, 'none', NULL, 365.87, 400.00, 34.13, '2026-07-05 10:06:58'),
(9, 'INV-20260705-9799', 1, '', 74.80, 8.98, 74.80, 0.00, 0.00, 16.76, 'senior_citizen', NULL, 67.02, 70.00, 2.98, '2026-07-05 10:42:20'),
(10, 'INV-20260706-7501', 1, '', 218.75, 26.25, 218.75, 0.00, 0.00, 0.00, 'none', NULL, 245.00, 0.00, -245.00, '2026-07-06 11:54:36'),
(11, 'INV-20260706-3675', 1, '', 74.80, 8.98, 74.80, 0.00, 0.00, 0.00, 'none', NULL, 83.78, 0.00, -83.78, '2026-07-06 12:02:38'),
(12, 'INV-20260706-1337', 2, 'Walk-in', 58.04, 6.96, 58.04, 0.00, 0.00, 13.00, 'senior_citizen', NULL, 52.00, 100.00, 48.00, '2026-07-06 12:30:26'),
(13, 'INV-20260706-2501', 1, 'Walk-in', 656.25, 78.75, 656.25, 0.00, 0.00, 0.00, 'none', NULL, 735.00, 1000.00, 265.00, '2026-07-06 13:20:53'),
(14, 'INV-20260720-1601', 2, NULL, 74.80, 8.98, 74.80, 0.00, 0.00, 0.00, 'none', NULL, 83.78, 90.00, 6.22, '2026-07-20 12:53:47'),
(15, 'INV-20260720-9839', 1, NULL, 218.75, 26.25, 218.75, 0.00, 0.00, 0.00, 'none', NULL, 245.00, 250.00, 5.00, '2026-07-20 13:05:12'),
(16, 'INV-20260720-3316', 1, NULL, 80.36, 0.00, 0.00, 80.36, 0.00, 0.00, 'none', NULL, 80.36, 100.00, 19.64, '2026-07-20 13:31:37'),
(17, 'INV-20260720-4733', 1, NULL, 160.72, 0.00, 0.00, 160.72, 0.00, 0.00, 'none', NULL, 160.72, 200.00, 39.28, '2026-07-20 13:50:50'),
(18, 'INV-20260721-3402', 1, NULL, 74.80, 8.98, 74.80, 0.00, 0.00, 0.00, 'none', '', 83.78, 0.00, -83.78, '2026-07-21 03:24:50'),
(19, 'INV-20260721-6281', 1, NULL, 34.09, 4.09, 34.09, 0.00, 0.00, 0.00, 'none', '', 38.18, 0.00, -38.18, '2026-07-21 03:26:33'),
(20, 'INV-20260721-2230', 1, NULL, 74.80, 8.98, 74.80, 0.00, 0.00, 0.00, 'none', '', 83.78, 0.00, -83.78, '2026-07-21 03:27:21'),
(22, 'INV-20260726-4492', 1, NULL, 74.80, 8.98, 74.80, 0.00, 0.00, 16.76, 'senior_citizen', '2023-985642', 67.02, 67.02, 0.00, '2026-07-26 13:32:15'),
(23, 'INV-20260726-4497', 1, NULL, 74.80, 8.98, 74.80, 0.00, 0.00, 0.00, 'none', '', 83.78, 83.78, 0.00, '2026-07-26 14:13:04');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `subtotal_original` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `original_price`, `selling_price`, `subtotal_original`) VALUES
(3, 3, 4, 1, 218.75, 245.00, 218.75),
(4, 4, 1, 1, 74.80, 83.78, 74.80),
(5, 5, 1, 1, 74.80, 83.78, 74.80),
(6, 6, 1, 1, 74.80, 83.78, 74.80),
(7, 7, 4, 1, 218.75, 245.00, 218.75),
(8, 7, 2, 1, 13.39, 15.00, 13.39),
(9, 7, 5, 1, 58.04, 65.00, 58.04),
(10, 8, 6, 3, 34.09, 38.18, 102.27),
(11, 8, 1, 3, 74.80, 83.78, 224.40),
(12, 9, 1, 1, 74.80, 83.78, 74.80),
(13, 10, 4, 1, 218.75, 245.00, 218.75),
(14, 11, 1, 1, 74.80, 83.78, 74.80),
(15, 12, 5, 1, 58.04, 65.00, 58.04),
(16, 13, 4, 3, 218.75, 245.00, 656.25),
(17, 14, 1, 1, 74.80, 83.78, 74.80),
(18, 15, 4, 1, 218.75, 245.00, 218.75),
(19, 16, 13, 1, 80.36, 90.00, 80.36),
(20, 17, 13, 2, 80.36, 90.00, 160.72),
(21, 18, 1, 1, 74.80, 83.78, 74.80),
(22, 19, 6, 1, 34.09, 38.18, 34.09),
(23, 20, 1, 1, 74.80, 83.78, 74.80),
(25, 22, 1, 1, 74.80, 83.78, 74.80),
(26, 23, 1, 1, 74.80, 83.78, 74.80);

-- --------------------------------------------------------

--
-- Table structure for table `supply_items`
--

CREATE TABLE `supply_items` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `total_price` decimal(10,2) DEFAULT 0.00,
  `category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supply_requests`
--

CREATE TABLE `supply_requests` (
  `id` int(11) NOT NULL,
  `request_number` varchar(20) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','finance_approved','ceo_approved','ordered','received','rejected') DEFAULT 'pending',
  `finance_approved_by` int(11) DEFAULT NULL,
  `ceo_approved_by` int(11) DEFAULT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_finance_approved` timestamp NULL DEFAULT NULL,
  `date_ceo_approved` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','co-admin','staff') DEFAULT 'staff',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `is_active`, `created_at`, `last_login`) VALUES
(1, 'admin', 'admin123', 'Administrator', 'admin', 1, '2026-07-04 10:33:13', '2026-07-28 03:14:47'),
(2, 'staff1', 'asdasd', 'tung tung sahur', 'co-admin', 1, '2026-07-06 12:22:50', '2026-07-28 03:19:54'),
(6, 'user1', '123123', 'tralalelo tropa lang', 'staff', 1, '2026-07-20 13:07:13', '2026-07-28 03:20:21');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `created_at`) VALUES
(1, 2, 4, '2026-07-28 01:11:07'),
(2, 6, 3, '2026-07-28 01:49:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `finance_requests`
--
ALTER TABLE `finance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `supply_items`
--
ALTER TABLE `supply_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `supply_requests`
--
ALTER TABLE `supply_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_number` (`request_number`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `finance_approved_by` (`finance_approved_by`),
  ADD KEY `ceo_approved_by` (`ceo_approved_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `finance_requests`
--
ALTER TABLE `finance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `supply_items`
--
ALTER TABLE `supply_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supply_requests`
--
ALTER TABLE `supply_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `finance_requests`
--
ALTER TABLE `finance_requests`
  ADD CONSTRAINT `finance_requests_ibfk_1` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `finance_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `supply_items`
--
ALTER TABLE `supply_items`
  ADD CONSTRAINT `supply_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `supply_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supply_requests`
--
ALTER TABLE `supply_requests`
  ADD CONSTRAINT `supply_requests_ibfk_1` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supply_requests_ibfk_2` FOREIGN KEY (`finance_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supply_requests_ibfk_3` FOREIGN KEY (`ceo_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
