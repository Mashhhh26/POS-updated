-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 03, 2026 at 03:06 AM
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
  `status` enum('present','absent','late') DEFAULT 'present',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `time_in`, `time_out`, `status`, `created_at`) VALUES
(1, 1, '2026-07-28', '19:01:00', '21:01:00', 'present', '2026-07-28 11:01:39');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `first_name`, `last_name`, `position`, `department`, `date_hired`, `status`, `created_at`) VALUES
(1, 'EMP001', 'Juan', 'Dela Cruz', 'Manager', 'Operations', NULL, 'active', '2026-07-28 10:48:13'),
(2, 'EMP002', 'Maria', 'Santos', 'Staff', 'HR', NULL, 'active', '2026-07-28 10:48:13'),
(3, 'EMP003', 'Jose', 'Rizal', 'Staff', 'Finance', NULL, 'active', '2026-07-28 10:48:13'),
(4, 'EMP004', 'Carlo Jake', 'Cancio', 'CEO', 'HR', '2010-06-29', 'active', '2026-07-28 11:53:53');

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
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finance_requests`
--

INSERT INTO `finance_requests` (`id`, `request_type`, `amount`, `description`, `requested_by`, `status`, `date_requested`) VALUES
(1, 'other', 110.23, 'kurakot ako\r\n', 1, 'approved', '2026-07-28 12:11:00');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('sick','vacation','emergency','other') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `date_filed` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `date_filed`) VALUES
(1, 1, 'vacation', '2026-07-28', '2026-07-30', 'Family vacation', 'rejected', '2026-07-28 10:48:13'),
(2, 2, 'sick', '2026-07-28', '2026-07-28', 'Flu', 'rejected', '2026-07-28 10:48:13');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`id`, `employee_id`, `pay_period_start`, `pay_period_end`, `gross_pay`, `deductions`, `net_pay`, `status`, `created_at`) VALUES
(1, 1, '2026-07-30', '2026-07-30', 48920.00, 5870.40, 43049.60, 'paid', '2026-07-30 04:08:36'),
(2, 2, '2026-07-30', '2026-07-30', 25559.00, 3067.08, 22491.92, 'paid', '2026-07-30 04:08:36'),
(3, 3, '2026-07-30', '2026-07-30', 27986.00, 3358.32, 24627.68, 'paid', '2026-07-30 04:08:36'),
(4, 4, '2026-07-30', '2026-07-30', 24290.00, 2914.80, 21375.20, 'paid', '2026-07-30 04:08:36'),
(5, 1, '2026-07-31', '2026-09-30', 38521.00, 4622.52, 33898.48, 'paid', '2026-07-30 04:09:11'),
(6, 2, '2026-07-31', '2026-09-30', 19063.00, 2287.56, 16775.44, 'paid', '2026-07-30 04:09:11'),
(7, 3, '2026-07-31', '2026-09-30', 29098.00, 3491.76, 25606.24, 'paid', '2026-07-30 04:09:11'),
(8, 4, '2026-07-31', '2026-09-30', 32949.00, 3953.88, 28995.12, 'paid', '2026-07-30 04:09:11');

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
(1, 'view_employees', 'hr', 'View employee list', '2026-08-02 15:03:35'),
(2, 'manage_employees', 'hr', 'Add, edit, delete employees', '2026-08-02 15:03:35'),
(3, 'view_attendance', 'hr', 'View attendance records', '2026-08-02 15:03:35'),
(4, 'manage_attendance', 'hr', 'Add, edit, delete attendance', '2026-08-02 15:03:35'),
(5, 'view_leave_requests', 'hr', 'View leave requests', '2026-08-02 15:03:35'),
(6, 'approve_leave_requests', 'hr', 'Approve or reject leave requests', '2026-08-02 15:03:35'),
(7, 'manage_leave_requests', 'hr', 'Add, edit, delete leave requests', '2026-08-02 15:03:35'),
(8, 'view_finance', 'finance', 'View finance dashboard', '2026-08-02 15:03:35'),
(9, 'manage_payroll', 'finance', 'Manage payroll', '2026-08-02 15:03:35'),
(10, 'view_payroll', 'finance', 'View payroll records', '2026-08-02 15:03:35'),
(11, 'approve_finance_requests', 'finance', 'Approve finance requests', '2026-08-02 15:03:35'),
(12, 'manage_finance_requests', 'finance', 'Add, edit, delete finance requests', '2026-08-02 15:03:35'),
(13, 'view_supply_requests', 'supply', 'View supply requests', '2026-08-02 15:03:35'),
(14, 'create_supply_requests', 'supply', 'Create supply requests', '2026-08-02 15:03:35'),
(15, 'approve_supply_requests', 'supply', 'Approve supply requests', '2026-08-02 15:03:35'),
(16, 'manage_supply_requests', 'supply', 'Edit, delete supply requests', '2026-08-02 15:03:35'),
(17, 'manage_roles', 'system', 'Create, edit, delete roles', '2026-08-02 15:03:35'),
(18, 'manage_users', 'system', 'Create, edit, delete users', '2026-08-02 15:03:35'),
(19, 'view_reports', 'system', 'View all reports', '2026-08-02 15:03:35');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_code`, `name`, `price`, `stock`, `category`, `created_at`) VALUES
(1, 'P001', 'Coca-Cola 1.5L', 85.00, 48, 'Beverages', '2026-07-28 10:48:13'),
(2, 'P002', 'Pancit Canton', 15.00, 100, 'Groceries', '2026-07-28 10:48:13'),
(3, 'P003', 'Sardines 155g', 25.00, 74, 'Canned Goods', '2026-07-28 10:48:13'),
(4, 'P004', 'Milo 1kg', 245.00, 30, 'Groceries', '2026-07-28 10:48:13'),
(5, 'P005', 'Nescafe 3-in-1', 65.00, 60, 'Beverages', '2026-07-28 10:48:13'),
(6, 'P252', 'Nestea', 25.26, 20, 'Powdered Juice', '2026-07-30 04:51:03'),
(7, 'P328', 'Lipton', 25.26, 20, 'Finance', '2026-07-30 05:01:06'),
(8, 'P612', 'ballpen', 15.00, 101, 'Finance', '2026-07-30 08:31:34'),
(9, 'P318', 'hatdog', 120.00, 11, 'Finance', '2026-07-30 08:34:33'),
(10, 'P828', 'hamburger', 52.00, 10, 'Finance', '2026-07-30 09:07:27'),
(11, 'P493', 'sadsdadsad', 120.00, 12, 'Finance', '2026-07-30 09:24:43');

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
(1, 'super_admin', 'Full system access - can manage everything', '2026-08-02 15:03:35'),
(2, 'admin', 'Administrator access', '2026-08-02 15:03:35'),
(3, 'hr', 'Human Resources access', '2026-08-02 15:03:35'),
(4, 'finance', 'Finance access', '2026-08-02 15:03:35'),
(5, 'supply', 'Supply Chain access', '2026-08-02 15:03:35'),
(6, 'cashier', 'Cashier access', '2026-08-02 15:03:35'),
(7, 'staff', 'Basic staff access', '2026-08-02 15:03:35');

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
(1, 1, 11, '2026-08-02 15:03:35'),
(2, 1, 6, '2026-08-02 15:03:35'),
(3, 1, 15, '2026-08-02 15:03:35'),
(4, 1, 14, '2026-08-02 15:03:35'),
(5, 1, 4, '2026-08-02 15:03:35'),
(6, 1, 2, '2026-08-02 15:03:35'),
(7, 1, 12, '2026-08-02 15:03:35'),
(8, 1, 7, '2026-08-02 15:03:35'),
(9, 1, 9, '2026-08-02 15:03:35'),
(10, 1, 17, '2026-08-02 15:03:35'),
(11, 1, 16, '2026-08-02 15:03:35'),
(12, 1, 18, '2026-08-02 15:03:35'),
(13, 1, 3, '2026-08-02 15:03:35'),
(14, 1, 1, '2026-08-02 15:03:35'),
(15, 1, 8, '2026-08-02 15:03:35'),
(16, 1, 5, '2026-08-02 15:03:35'),
(17, 1, 10, '2026-08-02 15:03:35'),
(18, 1, 19, '2026-08-02 15:03:35'),
(19, 1, 13, '2026-08-02 15:03:35'),
(32, 2, 11, '2026-08-02 15:03:35'),
(33, 2, 6, '2026-08-02 15:03:35'),
(34, 2, 15, '2026-08-02 15:03:35'),
(35, 2, 14, '2026-08-02 15:03:35'),
(36, 2, 4, '2026-08-02 15:03:35'),
(37, 2, 2, '2026-08-02 15:03:35'),
(38, 2, 12, '2026-08-02 15:03:35'),
(39, 2, 7, '2026-08-02 15:03:35'),
(40, 2, 9, '2026-08-02 15:03:35'),
(41, 2, 17, '2026-08-02 15:03:35'),
(42, 2, 16, '2026-08-02 15:03:35'),
(43, 2, 18, '2026-08-02 15:03:35'),
(44, 2, 3, '2026-08-02 15:03:35'),
(45, 2, 1, '2026-08-02 15:03:35'),
(46, 2, 8, '2026-08-02 15:03:35'),
(47, 2, 5, '2026-08-02 15:03:35'),
(48, 2, 10, '2026-08-02 15:03:35'),
(49, 2, 19, '2026-08-02 15:03:35'),
(50, 2, 13, '2026-08-02 15:03:35'),
(63, 3, 6, '2026-08-02 15:03:35'),
(64, 3, 4, '2026-08-02 15:03:35'),
(65, 3, 2, '2026-08-02 15:03:35'),
(66, 3, 7, '2026-08-02 15:03:35'),
(67, 3, 3, '2026-08-02 15:03:35'),
(68, 3, 1, '2026-08-02 15:03:35'),
(69, 3, 5, '2026-08-02 15:03:35'),
(70, 3, 19, '2026-08-02 15:03:35'),
(78, 4, 11, '2026-08-02 15:03:35'),
(79, 4, 12, '2026-08-02 15:03:35'),
(80, 4, 9, '2026-08-02 15:03:35'),
(81, 4, 8, '2026-08-02 15:03:35'),
(82, 4, 10, '2026-08-02 15:03:35'),
(83, 4, 19, '2026-08-02 15:03:35'),
(85, 5, 15, '2026-08-02 15:03:35'),
(86, 5, 14, '2026-08-02 15:03:35'),
(87, 5, 16, '2026-08-02 15:03:35'),
(88, 5, 19, '2026-08-02 15:03:35'),
(89, 5, 13, '2026-08-02 15:03:35'),
(92, 6, 19, '2026-08-02 15:03:35');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `payment_amount` decimal(10,2) DEFAULT 0.00,
  `change_amount` decimal(10,2) DEFAULT 0.00,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `invoice_number`, `user_id`, `grand_total`, `payment_amount`, `change_amount`, `sale_date`) VALUES
(1, 'INV-20260728-3232', 1, 85.00, 85.00, 0.00, '2026-07-28 10:59:30'),
(2, 'INV-20260730-1892', 1, 110.00, 200.00, 90.00, '2026-07-30 04:33:23'),
(3, 'INV-20260802-9406', 1, 135.00, 135.00, 0.00, '2026-08-02 13:53:55');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 1, 1, 85.00, 85.00),
(2, 2, 3, 1, 25.00, 25.00),
(3, 2, 1, 1, 85.00, 85.00),
(4, 3, 8, 9, 15.00, 135.00);

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
  `total_price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supply_items`
--

INSERT INTO `supply_items` (`id`, `request_id`, `item_name`, `quantity`, `unit_price`, `total_price`) VALUES
(13, 13, 'ballpen', 10, 15.00, 150.00),
(14, 14, 'hanger', 7, 7.23, 50.61),
(15, 15, 'hatdog', 11, 120.00, 1320.00),
(16, 16, 'hamburger', 10, 52.00, 520.00),
(17, 17, 'sadsdadsad', 12, 120.00, 1440.00),
(18, 18, 'ballpen', 1000, 15.00, 15000.00),
(19, 19, 'ballpen', 99, 15.00, 1485.00),
(20, 20, 'ballpen', 100, 15.00, 1500.00);

-- --------------------------------------------------------

--
-- Table structure for table `supply_requests`
--

CREATE TABLE `supply_requests` (
  `id` int(11) NOT NULL,
  `request_number` varchar(20) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','finance_approved','approved','rejected') DEFAULT 'pending',
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  `finance_approved` tinyint(1) DEFAULT 0,
  `date_finance_approved` timestamp NULL DEFAULT NULL,
  `date_approved` timestamp NULL DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supply_requests`
--

INSERT INTO `supply_requests` (`id`, `request_number`, `requested_by`, `department`, `total_amount`, `status`, `date_requested`, `finance_approved`, `date_finance_approved`, `date_approved`, `priority`) VALUES
(13, 'SR-20260730-7037', 1, 'Finance', 150.00, 'approved', '2026-07-30 08:06:41', 1, NULL, NULL, 'medium'),
(14, 'SR-20260730-8958', 1, 'Finance', 50.61, 'rejected', '2026-07-30 08:14:54', 1, NULL, NULL, 'medium'),
(15, 'SR-20260730-1437', 1, 'Finance', 1320.00, 'approved', '2026-07-30 08:28:38', 1, NULL, NULL, 'medium'),
(16, 'SR-20260730-9503', 1, 'Finance', 520.00, 'approved', '2026-07-30 09:07:10', 1, NULL, NULL, 'medium'),
(17, 'SR-20260730-4021', 1, 'Finance', 1440.00, 'approved', '2026-07-30 09:23:14', 1, NULL, NULL, 'medium'),
(18, 'SR-20260802-6108', 1, 'Store', 15000.00, 'rejected', '2026-08-02 13:54:08', 0, NULL, NULL, 'medium'),
(19, 'SR-20260802-3097', 1, 'Store', 1485.00, 'rejected', '2026-08-02 13:54:16', 0, NULL, NULL, 'medium'),
(20, 'SR-20260802-7481', 1, 'Store', 1500.00, 'approved', '2026-08-02 13:57:21', 1, NULL, NULL, 'medium');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `role` enum('admin','staff','hr','finance','supply') DEFAULT 'staff',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `position`, `department`, `date_hired`, `role`, `is_active`, `created_at`, `status`) VALUES
(1, 'admin', 'admin123', 'Administrator', 'Staff', 'General', NULL, 'admin', 1, '2026-07-28 10:48:13', 'active'),
(2, 'hr', 'hr123', 'HR Manager', 'Staff', 'General', NULL, 'hr', 1, '2026-07-28 10:48:13', 'active'),
(3, 'finance', 'finance123', 'Finance Manager', 'Staff', 'General', NULL, 'finance', 1, '2026-07-28 10:48:13', 'active'),
(4, 'supply', 'supply123', 'Supply Manager', 'Staff', 'General', NULL, 'supply', 1, '2026-07-28 10:48:13', 'active'),
(5, 'user', 'asdasd', 'Marry Juana Syabu', '', '', NULL, 'staff', 1, '2026-08-02 15:15:43', 'active');

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
(2, 2, 3, '2026-08-02 15:03:35'),
(3, 3, 4, '2026-08-02 15:03:35'),
(4, 4, 5, '2026-08-02 15:03:35'),
(5, 1, 2, '2026-08-02 15:04:26'),
(6, 1, 1, '2026-08-02 15:04:26'),
(8, 1, 7, '2026-08-02 15:29:31'),
(9, 1, 3, '2026-08-02 15:29:31'),
(10, 1, 6, '2026-08-02 15:29:31'),
(11, 1, 7, '2026-08-02 15:30:39'),
(14, 5, 4, '2026-08-02 15:42:55');

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
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `finance_requests`
--
ALTER TABLE `finance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requested_by` (`requested_by`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

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
  ADD KEY `requested_by` (`requested_by`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `finance_requests`
--
ALTER TABLE `finance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `supply_items`
--
ALTER TABLE `supply_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `supply_requests`
--
ALTER TABLE `supply_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `finance_requests`
--
ALTER TABLE `finance_requests`
  ADD CONSTRAINT `finance_requests_ibfk_1` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `supply_requests_ibfk_1` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`);

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
