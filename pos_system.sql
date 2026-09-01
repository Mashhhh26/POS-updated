-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2026 at 04:10 AM
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
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `ip_address`, `created_at`) VALUES
(1, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:12:22'),
(2, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:12:23'),
(3, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:12:24'),
(4, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:12:24'),
(5, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:12:25'),
(6, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:12:48'),
(7, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:12:49'),
(8, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:14:31'),
(9, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:14:32'),
(10, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:14:33'),
(11, 1, 'Access denied: users.php - User: admin', '::1', '2026-08-31 05:18:31'),
(12, 1, 'Access denied: roles.php - User: admin', '::1', '2026-08-31 05:18:32'),
(13, 1, 'User logged in: admin', '::1', '2026-08-31 05:20:11'),
(14, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 06:12:11'),
(15, 1, 'User logged in: admin', '::1', '2026-08-31 06:16:05'),
(19, 1, 'User logged in: admin', '::1', '2026-08-31 06:27:54'),
(20, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 06:28:02'),
(21, 1, 'Updated permissions for role ID: 3', '::1', '2026-08-31 06:29:18'),
(36, 1, 'User logged in: admin', '::1', '2026-08-31 06:29:59'),
(37, 1, 'Updated permissions for role ID: 5', '::1', '2026-08-31 06:31:13'),
(38, 1, 'Updated permissions for role ID: 3', '::1', '2026-08-31 06:32:22'),
(54, 1, 'User logged in: admin', '::1', '2026-08-31 06:33:01'),
(55, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 06:33:30'),
(56, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 06:33:30'),
(57, 1, 'User logged in: admin', '::1', '2026-08-31 10:48:44'),
(58, 1, 'Deleted user: testuser (ID: 3) with all related records', '::1', '2026-08-31 10:53:06'),
(59, 1, 'Deleted user: user1 (ID: 5) with all related records', '::1', '2026-08-31 10:53:14'),
(60, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:17'),
(61, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:26'),
(62, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:31'),
(63, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 11:08:32'),
(64, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:32'),
(65, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 11:08:34'),
(66, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:35'),
(67, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:43'),
(68, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 11:08:43'),
(69, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:08:44'),
(70, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:10:07'),
(71, 1, 'Access denied: leaves.php - User: admin', '::1', '2026-08-31 11:10:08'),
(72, 1, 'User logged in: admin', '::1', '2026-08-31 11:10:49'),
(73, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:10:52'),
(74, 1, 'Access denied: employees.php - User: admin', '::1', '2026-08-31 11:10:53'),
(75, 1, 'Deleted user: user (ID: 6) with all related records', '::1', '2026-08-31 11:25:04'),
(76, 1, 'Created user: user with employee record', '::1', '2026-08-31 11:26:22'),
(77, 7, 'User logged in: user', '::1', '2026-08-31 11:33:15'),
(78, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:18'),
(79, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:19'),
(80, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:19'),
(81, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:24'),
(82, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:26'),
(83, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:26'),
(84, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:26'),
(85, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:26'),
(86, 7, 'Access denied: sales.php - User: user', '::1', '2026-08-31 11:33:27'),
(87, 1, 'User logged in: admin', '::1', '2026-08-31 11:33:31'),
(88, 1, 'Updated user ID: 7 and employee record', '::1', '2026-08-31 11:35:50'),
(89, 7, 'User logged in: user', '::1', '2026-08-31 11:36:01'),
(90, 7, 'Recorded attendance for user ID: 7 - Status: present', '::1', '2026-08-31 11:36:14'),
(91, 1, 'User logged in: admin', '::1', '2026-08-31 11:36:57'),
(92, 1, 'Updated permissions for role ID: 2', '::1', '2026-08-31 11:37:22'),
(93, 1, 'User logged in: admin', '::1', '2026-08-31 11:37:28'),
(94, 7, 'User logged in: user', '::1', '2026-08-31 11:37:39'),
(95, 7, 'Access denied: roles.php - User: user', '::1', '2026-08-31 11:37:43'),
(96, 7, 'Access denied: roles.php - User: user', '::1', '2026-08-31 11:37:44'),
(97, 7, 'Access denied: roles.php - User: user', '::1', '2026-08-31 11:37:45'),
(98, 7, 'Access denied: roles.php - User: user', '::1', '2026-08-31 11:37:45'),
(99, 7, 'Access denied: roles.php - User: user', '::1', '2026-08-31 11:37:55'),
(100, 1, 'User logged in: admin', '::1', '2026-08-31 11:38:12'),
(101, 1, 'Budget approved for requisition ID: 1', '::1', '2026-08-31 12:40:29'),
(102, 1, 'Budget rejectd for requisition ID: 2', '::1', '2026-08-31 12:54:19'),
(103, 1, 'User logged in: admin', '::1', '2026-08-31 13:15:08'),
(104, 1, 'Created requisition #REQ-20260831-7481', '::1', '2026-08-31 13:20:11'),
(105, 1, 'Created requisition #REQ-20260831-1717', '::1', '2026-08-31 13:20:51'),
(106, 1, 'Created requisition #REQ-20260831-6982', '::1', '2026-08-31 13:23:15'),
(107, 1, 'Created requisition #REQ-20260831-4037', '::1', '2026-08-31 13:23:40'),
(108, 1, 'Created requisition #REQ-20260831-5770', '::1', '2026-08-31 13:25:28'),
(109, 1, 'Created requisition #REQ-20260831-5447', '::1', '2026-08-31 13:26:33'),
(110, 1, 'Added supplier: Evo Logistic Hub', '::1', '2026-08-31 13:31:31'),
(111, 1, 'Created RFQ #RFQ-20260831-7611', '::1', '2026-08-31 13:35:30'),
(112, 1, 'Added supplier: Evo Logistic Hub', '::1', '2026-08-31 13:36:13'),
(113, 1, 'Added quotation #QUOT-20260831-4618', '::1', '2026-08-31 13:53:13'),
(114, 1, 'Evaluated quotation ID: 1', '::1', '2026-08-31 13:53:53'),
(115, 1, 'Evaluated quotation ID: 1', '::1', '2026-08-31 13:54:01'),
(116, 1, 'User logged in: admin', '::1', '2026-08-31 14:02:06'),
(117, 1, 'Created PO #PO-20260831-7268', '::1', '2026-08-31 14:03:04'),
(118, 1, 'PO #1 approved by finance', '::1', '2026-08-31 14:04:16'),
(119, 1, 'Added quotation #QUOT-20260831-6393', '::1', '2026-08-31 14:18:55'),
(120, 1, 'User logged in: admin', '::1', '2026-08-31 14:23:24'),
(121, 1, 'User logged in: admin', '::1', '2026-08-31 14:23:47'),
(122, 1, 'Created invoice #INV-2026-001 - Match: mismatch', '::1', '2026-08-31 14:26:38'),
(123, 1, 'User logged in: admin', '::1', '2026-09-01 01:13:46'),
(124, 1, 'Added quotation #QUOT-20260901-3194', '::1', '2026-09-01 01:14:58'),
(125, 1, 'Evaluated quotation ID: 3 - Status: accepted', '::1', '2026-09-01 01:15:56'),
(126, 1, 'Added quotation #QUOT-20260901-2832', '::1', '2026-09-01 01:16:57'),
(127, 1, 'Evaluated quotation ID: 4 - Status: accepted', '::1', '2026-09-01 01:17:04'),
(128, 1, 'Created PO #PO-20260901-7115', '::1', '2026-09-01 01:17:39'),
(129, 1, 'PO #2 approved by finance', '::1', '2026-09-01 01:17:54'),
(130, 1, 'Created PO #PO-20260901-1271', '::1', '2026-09-01 01:36:57'),
(131, 1, 'PO #3 approved by finance', '::1', '2026-09-01 01:37:01'),
(132, 1, 'Received goods for PO #3 - GRN #GRN-20260901-6342', '::1', '2026-09-01 01:37:12'),
(133, 1, 'Created invoice #INV-2026-002 - Match: mismatch', '::1', '2026-09-01 01:38:25'),
(134, 1, 'Force matched invoice #3', '::1', '2026-09-01 01:44:41'),
(135, 1, 'Created invoice #INV-2026-003 - Match: mismatch', '::1', '2026-09-01 01:45:07'),
(136, 1, 'Marked invoice #3 as paid', '::1', '2026-09-01 01:45:29'),
(137, 1, 'Force matched invoice #7', '::1', '2026-09-01 01:50:33'),
(138, 1, 'PO #2 closed after payment', '::1', '2026-09-01 01:57:19'),
(139, 1, 'Processed payment #PAY-20260901-4716', '::1', '2026-09-01 01:57:19'),
(140, 1, 'Force matched invoice #1', '::1', '2026-09-01 01:57:31'),
(141, 1, 'User logged in: admin', '::1', '2026-09-01 01:58:12'),
(142, 1, 'PO #1 closed after payment', '::1', '2026-09-01 02:01:28'),
(143, 1, 'Processed payment #PAY-20260901-8327', '::1', '2026-09-01 02:01:28'),
(144, 1, 'Created budget #BUD-2026-001 - ₱1,000,000.00', '::1', '2026-09-01 02:06:08'),
(145, 1, 'Added expense #EXP-20260901-3529 - ₱14,200.00', '::1', '2026-09-01 02:06:43');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` date DEFAULT curdate(),
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half-day') DEFAULT 'present',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `date`, `time_in`, `time_out`, `status`, `notes`) VALUES
(1, 7, '2026-08-31', '13:36:14', NULL, 'present', 'hatdog');

-- --------------------------------------------------------

--
-- Table structure for table `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL,
  `backup_file` varchar(255) DEFAULT NULL,
  `backup_size` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL,
  `budget_code` varchar(50) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `allocated_amount` decimal(12,2) DEFAULT NULL,
  `used_amount` decimal(12,2) DEFAULT 0.00,
  `fiscal_year` year(4) DEFAULT NULL,
  `status` enum('active','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `budget_code`, `department`, `category`, `allocated_amount`, `used_amount`, `fiscal_year`, `status`, `created_at`) VALUES
(1, 'BUD-2026-001', 'IT', 'Equipment', 1000000.00, 0.00, '2026', 'active', '2026-09-01 02:06:08');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_code` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','on_leave','terminated') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `first_name`, `last_name`, `middle_name`, `position`, `department`, `email`, `phone`, `address`, `hire_date`, `salary`, `status`, `created_at`, `user_id`) VALUES
(1, 'EMP-20260831-6065', 'Marry Juana Shabu', '', NULL, 'Staff', 'Sales', 'marryjuana7@gmail.com', '09874665458', 'asdasdasdasd st.', '2007-01-01', 2000.00, 'active', '2026-08-31 11:26:22', 7);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `expense_number` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `expense_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `expense_number`, `category`, `description`, `amount`, `expense_date`, `payment_method`, `receipt_file`, `created_by`, `status`, `created_at`) VALUES
(1, 'EXP-20260901-3529', 'Salaries', 'shaod', 14200.00, '2026-09-01', 'Cash', NULL, 1, 'approved', '2026-09-01 02:06:43');

-- --------------------------------------------------------

--
-- Table structure for table `goods_receipts`
--

CREATE TABLE `goods_receipts` (
  `id` int(11) NOT NULL,
  `grn_number` varchar(50) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `receipt_date` date DEFAULT curdate(),
  `delivery_note` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `goods_receipts`
--

INSERT INTO `goods_receipts` (`id`, `grn_number`, `po_id`, `received_by`, `receipt_date`, `delivery_note`, `status`, `notes`, `created_at`) VALUES
(1, 'GRN-TEST-20260831-8605', 1, 1, '2026-08-31', NULL, 'completed', NULL, '2026-08-31 14:16:04'),
(2, 'GRN-FINAL-20260901-1071', 2, 1, '2026-09-01', NULL, 'completed', NULL, '2026-09-01 01:23:45'),
(3, 'GRN-20260901-6342', 3, 1, '2026-09-01', '1234545', 'completed', 'aaaaaa', '2026-09-01 01:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `gr_items`
--

CREATE TABLE `gr_items` (
  `id` int(11) NOT NULL,
  `goods_receipt_id` int(11) DEFAULT NULL,
  `po_item_id` int(11) DEFAULT NULL,
  `received_quantity` int(11) DEFAULT NULL,
  `accepted_quantity` int(11) DEFAULT NULL,
  `rejected_quantity` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gr_items`
--

INSERT INTO `gr_items` (`id`, `goods_receipt_id`, `po_item_id`, `received_quantity`, `accepted_quantity`, `rejected_quantity`, `remarks`) VALUES
(1, 1, 1, 5, 5, 0, NULL),
(2, 2, 2, 5, 5, 0, NULL),
(3, 3, 3, 5, 5, 0, 'sdds');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('received','matched','pending_payment','paid','rejected') DEFAULT 'received',
  `match_status` enum('pending','matched','mismatch') DEFAULT 'pending',
  `received_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `po_id`, `supplier_id`, `invoice_date`, `total_amount`, `tax_amount`, `net_amount`, `status`, `match_status`, `received_at`, `paid_at`, `notes`) VALUES
(1, 'INV-2026-001', 1, 5, '2026-08-31', 145654.00, 145.00, 145799.00, 'paid', 'matched', '2026-08-31 14:26:38', '2026-09-01 02:01:28', 'sadfsdfsdf | Force matched: '),
(3, 'INV-2026-002', 3, 1, '2026-09-01', 14011234.00, 10000.00, 14021234.00, 'paid', 'matched', '2026-09-01 01:38:25', '2026-09-01 01:45:29', 'asdasd | Force matched: '),
(7, 'INV-2026-003', 2, 1, '2026-09-01', 123369.00, 123213.00, 246582.00, 'paid', 'matched', '2026-09-01 01:45:07', '2026-09-01 01:57:19', 'ASDASD | Force matched: ');

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `leave_type` enum('sick','vacation','emergency','maternity','paternity','bereavement') DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_number` varchar(50) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` enum('bank_transfer','cheque','virtual_card','cash') DEFAULT 'bank_transfer',
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('scheduled','processing','completed','failed') DEFAULT 'scheduled',
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_number`, `invoice_id`, `amount`, `payment_date`, `payment_method`, `reference_number`, `notes`, `status`, `processed_by`, `created_at`) VALUES
(1, 'PAY-20260901-4716', 7, 246582.00, '2026-09-01', 'cash', '', NULL, 'completed', 1, '2026-09-01 01:57:19'),
(2, 'PAY-20260901-8327', 1, 145799.00, '2026-09-01', 'cash', '', NULL, 'completed', 1, '2026-09-01 02:01:28');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`, `module`, `description`) VALUES
(1, 'view_users', 'users', NULL),
(2, 'create_users', 'users', NULL),
(3, 'edit_users', 'users', NULL),
(4, 'delete_users', 'users', NULL),
(5, 'view_roles', 'roles', NULL),
(6, 'manage_roles', 'roles', NULL),
(7, 'view_attendance', 'attendance', NULL),
(8, 'manage_attendance', 'attendance', NULL),
(9, 'view_products', 'products', NULL),
(10, 'manage_products', 'products', NULL),
(11, 'process_sales', 'sales', NULL),
(12, 'view_sales', 'sales', NULL),
(13, 'view_reports', 'reports', NULL),
(14, 'view_requisitions', 'procurement', NULL),
(15, 'create_requisitions', 'procurement', NULL),
(16, 'approve_requisitions', 'procurement', NULL),
(17, 'view_rfqs', 'procurement', NULL),
(18, 'manage_rfqs', 'procurement', NULL),
(19, 'view_suppliers', 'procurement', NULL),
(20, 'manage_suppliers', 'procurement', NULL),
(21, 'view_pos', 'procurement', NULL),
(22, 'manage_pos', 'procurement', NULL),
(23, 'view_invoices', 'procurement', NULL),
(24, 'manage_payments', 'procurement', NULL),
(25, 'view_performance', 'procurement', NULL),
(26, 'view_returns', 'sales', NULL),
(27, 'manage_returns', 'sales', NULL),
(28, 'view_expenses', 'finance', NULL),
(29, 'manage_expenses', 'finance', NULL),
(30, 'view_budget', 'finance', NULL),
(31, 'manage_budget', 'finance', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `po_items`
--

CREATE TABLE `po_items` (
  `id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `received_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `po_items`
--

INSERT INTO `po_items` (`id`, `po_id`, `item_description`, `quantity`, `unit_price`, `total`, `received_quantity`) VALUES
(1, 1, 'Test Item', 5, 1423.00, 7115.00, 0),
(2, 2, 'Test Item', 5, 1423.00, 7115.00, 0),
(3, 3, 'Test Item', 5, 1423.00, 7115.00, 5);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `low_stock_threshold` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT 0.00,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(12,2) DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','sent','acknowledged','fulfilled','closed','cancelled') DEFAULT 'draft',
  `finance_approver_id` int(11) DEFAULT NULL,
  `procurement_officer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_number`, `requisition_id`, `supplier_id`, `quotation_id`, `total_amount`, `tax`, `shipping_cost`, `grand_total`, `payment_terms`, `delivery_date`, `status`, `finance_approver_id`, `procurement_officer_id`, `created_at`, `approved_at`, `sent_at`) VALUES
(1, 'PO-20260831-7268', 1, 5, 1, 1700000.00, 145.00, 123.00, 1700268.00, 'COD', '2026-09-29', 'closed', 1, 1, '2026-08-31 14:03:04', '2026-08-31 14:04:16', NULL),
(2, 'PO-20260901-7115', 1, 1, 4, 123123.00, 123.00, 123.00, 123369.00, 'COD', '2026-09-29', 'closed', 1, 1, '2026-09-01 01:17:39', '2026-09-01 01:17:54', NULL),
(3, 'PO-20260901-1271', 1, 1, 2, 14000000.00, 10000.00, 1234.00, 14011234.00, 'COD', '2026-09-29', 'fulfilled', 1, 1, '2026-09-01 01:36:57', '2026-09-01 01:37:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `rfq_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quotation_number` varchar(50) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'PHP',
  `delivery_lead_time` int(11) DEFAULT NULL,
  `validity_days` int(11) DEFAULT 30,
  `payment_terms` varchar(50) DEFAULT NULL,
  `technical_compliance` decimal(5,2) DEFAULT 0.00,
  `commercial_score` decimal(5,2) DEFAULT 0.00,
  `status` enum('received','evaluating','accepted','rejected') DEFAULT 'received',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `rfq_id`, `supplier_id`, `quotation_number`, `total_amount`, `currency`, `delivery_lead_time`, `validity_days`, `payment_terms`, `technical_compliance`, `commercial_score`, `status`, `submitted_at`, `notes`) VALUES
(1, 1, 5, 'QUOT-20260831-4618', 1700000.00, 'PHP', 4, 30, 'Net 30', 12.00, 22.00, 'accepted', '2026-08-31 13:53:13', 'asdasdasd'),
(2, 1, 1, 'QUOT-20260831-6393', 14000000.00, 'PHP', 7, 30, 'Net 30', 85.00, 90.00, 'accepted', '2026-08-31 14:18:55', 'asdasdasd'),
(3, 1, 1, 'QUOT-20260901-3194', 123123123.00, 'PHP', 7, 30, 'Net 30', 11.00, 11.00, 'accepted', '2026-09-01 01:14:58', 'asdasdasd'),
(4, 1, 1, 'QUOT-20260901-2832', 123123.00, 'PHP', 7, 30, 'Net 30', 11.00, 11.00, 'accepted', '2026-09-01 01:16:57', 'asdasdasd');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `lead_time` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`id`, `quotation_id`, `item_description`, `quantity`, `unit_price`, `total`, `lead_time`, `remarks`) VALUES
(1, 1, 'Test Item', 5, 1423.00, 7115.00, 7, ''),
(2, 2, 'Test Item', 5, 1423.00, 7115.00, 7, ''),
(3, 3, 'Test Item', 5, 1423.00, 7115.00, 7, ''),
(4, 4, 'Test Item', 5, 1423.00, 7115.00, 7, '');

-- --------------------------------------------------------

--
-- Table structure for table `requisitions`
--

CREATE TABLE `requisitions` (
  `id` int(11) NOT NULL,
  `req_number` varchar(50) NOT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `cost_centre` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `estimated_value` decimal(12,2) DEFAULT NULL,
  `urgency` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('draft','pending_budget','budget_approved','budget_rejected','sourcing','completed') DEFAULT 'draft',
  `budget_owner_id` int(11) DEFAULT NULL,
  `finance_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `budget_code` varchar(50) DEFAULT NULL,
  `budget_used` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requisitions`
--

INSERT INTO `requisitions` (`id`, `req_number`, `requester_id`, `department`, `cost_centre`, `description`, `estimated_value`, `urgency`, `status`, `budget_owner_id`, `finance_remarks`, `created_at`, `updated_at`, `budget_code`, `budget_used`) VALUES
(1, 'REQ-TEST-20260831-1932', 1, 'IT', 'IT-001', 'Test requisition', 1000.00, 'medium', 'completed', NULL, 'asdasd', '2026-08-31 12:39:23', '2026-08-31 14:03:04', NULL, 0.00),
(2, 'REQ-TEST-20260831-6129', 1, 'IT', 'IT-001', 'Test from debug', 1000.00, 'medium', 'budget_rejected', NULL, '', '2026-08-31 12:52:25', '2026-08-31 12:54:19', NULL, 0.00),
(3, 'REQ-TEST-20260831-5418', 1, 'IT', 'IT-001', 'Test requisition from test form', 1500.00, 'medium', 'budget_rejected', NULL, '', '2026-08-31 13:00:33', '2026-08-31 13:20:24', NULL, 0.00),
(4, 'REQ-TEST-20260831-7612', 1, 'Finance', 'FN-001', 'Test requisition from test form', 100.00, 'medium', 'budget_rejected', NULL, '', '2026-08-31 13:01:23', '2026-08-31 13:20:28', NULL, 0.00),
(5, 'REQ-SIMPLE-20260831-3592', 1, 'IT', 'IT-001', 'Test requisition', 1000.00, 'medium', 'budget_rejected', NULL, '', '2026-08-31 13:18:30', '2026-08-31 13:20:32', NULL, 0.00),
(6, 'REQ-SIMPLE-20260831-8935', 1, 'asdasd', 'asdasd', 'asdasd', 1000.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:18:53', '2026-08-31 13:20:36', NULL, 0.00),
(7, 'REQ-20260831-7481', 1, 'IT', 'IT-001', 'asdasd', 1000000.00, 'medium', 'budget_rejected', NULL, '', '2026-08-31 13:20:11', '2026-08-31 13:20:40', NULL, 0.00),
(8, 'REQ-20260831-1717', 1, 'IT', 'IT-001', 'asdasdasd', 1000000.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:20:51', '2026-08-31 13:47:20', NULL, 0.00),
(9, 'REQ-20260831-6982', 1, 'Finance', 'FN-001', 'asdasd', 100.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:23:15', '2026-08-31 13:47:17', NULL, 0.00),
(10, 'REQ-20260831-4037', 1, 'Finance', 'FN-001', 'asdasdasd', 100.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:23:40', '2026-08-31 13:47:15', NULL, 0.00),
(11, 'REQ-20260831-5770', 1, 'Finance', 'FN-001', 'asdasd', 100.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:25:28', '2026-08-31 13:47:13', NULL, 0.00),
(12, 'REQ-20260831-5447', 1, 'Finance', 'FN-001', 'adadad', 100.00, 'medium', 'budget_approved', NULL, '', '2026-08-31 13:26:33', '2026-08-31 13:47:10', NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `requisition_items`
--

CREATE TABLE `requisition_items` (
  `id` int(11) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `estimated_price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `specifications` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requisition_items`
--

INSERT INTO `requisition_items` (`id`, `requisition_id`, `item_description`, `quantity`, `unit`, `estimated_price`, `total`, `specifications`) VALUES
(1, 1, 'Test Item', 5, 'pcs', 200.00, 1000.00, NULL),
(2, 3, 'Test Item', 5, 'pcs', 300.00, 1500.00, NULL),
(3, 4, 'ekyboard', 100, 'set', 1232.00, 123200.00, NULL),
(4, 5, 'Test Item', 5, 'pcs', 200.00, 1000.00, NULL),
(5, 6, 'Test Itemasdasd', 5, 'pcs', 200.00, 1000.00, NULL),
(6, 7, 'keyboard', 1000, 'set', 100.00, 100000.00, NULL),
(7, 8, 'keyboard', 1000, 'set', 100.00, 100000.00, NULL),
(8, 9, 'ekyboard', 100, 'set', 1232.00, 123200.00, NULL),
(9, 10, 'ekyboard', 100, 'set', 1232.00, 123200.00, NULL),
(10, 11, 'ekyboard', 100, 'set', 1232.00, 123200.00, NULL),
(11, 12, 'ekyboard', 100, 'set', 1232.00, 123200.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `return_number` varchar(50) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `reason` enum('defective','wrong_item','damaged','customer_request','other') DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_items`
--

CREATE TABLE `return_items` (
  `id` int(11) NOT NULL,
  `return_id` int(11) DEFAULT NULL,
  `sales_item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfqs`
--

CREATE TABLE `rfqs` (
  `id` int(11) NOT NULL,
  `rfq_number` varchar(50) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `submission_deadline` datetime DEFAULT NULL,
  `status` enum('draft','sent','evaluating','closed','awarded') DEFAULT 'draft',
  `procurement_officer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rfqs`
--

INSERT INTO `rfqs` (`id`, `rfq_number`, `requisition_id`, `title`, `description`, `submission_deadline`, `status`, `procurement_officer_id`, `created_at`) VALUES
(1, 'RFQ-20260831-7611', 1, 'hatdog', 'asdasd', '2026-10-21 00:00:00', 'awarded', 1, '2026-08-31 13:35:30');

-- --------------------------------------------------------

--
-- Table structure for table `rfq_suppliers`
--

CREATE TABLE `rfq_suppliers` (
  `id` int(11) NOT NULL,
  `rfq_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `invited_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL,
  `status` enum('invited','responded','no_response') DEFAULT 'invited'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rfq_suppliers`
--

INSERT INTO `rfq_suppliers` (`id`, `rfq_id`, `supplier_id`, `invited_at`, `responded_at`, `status`) VALUES
(1, 1, 1, '2026-08-31 13:35:30', '2026-09-01 01:16:57', 'responded'),
(2, 1, 2, '2026-08-31 13:35:30', NULL, 'invited'),
(3, 1, 3, '2026-08-31 13:35:30', NULL, 'invited');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
(1, 'Admin', 'Full system access'),
(2, 'HRM', 'Human Resource Management - handles attendance'),
(3, 'Cashier', 'Handles POS transactions'),
(4, 'Inventory', 'Manages products and stock'),
(5, 'Finance', 'Manages financial reports');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
(94, 1, 16),
(95, 1, 15),
(96, 1, 2),
(97, 1, 4),
(98, 1, 3),
(99, 1, 8),
(100, 1, 31),
(101, 1, 29),
(102, 1, 24),
(103, 1, 22),
(104, 1, 10),
(105, 1, 27),
(106, 1, 18),
(107, 1, 6),
(108, 1, 20),
(109, 1, 11),
(110, 1, 7),
(111, 1, 30),
(112, 1, 28),
(113, 1, 23),
(114, 1, 25),
(115, 1, 21),
(116, 1, 9),
(117, 1, 13),
(118, 1, 14),
(119, 1, 26),
(120, 1, 17),
(121, 1, 5),
(122, 1, 12),
(123, 1, 19),
(124, 1, 1),
(128, 5, 28),
(129, 5, 29),
(130, 5, 30),
(131, 5, 31),
(132, 5, 13),
(133, 5, 12),
(134, 5, 26),
(135, 3, 21),
(136, 3, 22),
(137, 3, 23),
(138, 3, 24),
(139, 3, 25),
(140, 3, 9),
(141, 3, 12),
(142, 3, 26),
(143, 1, 7),
(145, 2, 7),
(146, 2, 1),
(147, 2, 2),
(148, 2, 3),
(149, 2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_id` int(11) DEFAULT NULL,
  `return_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, -917613011, -1305464266, -1389350863, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('in','out') DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_code` varchar(50) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT 'Net 30',
  `lead_time_days` int(11) DEFAULT 7,
  `rating` decimal(3,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_code`, `company_name`, `contact_person`, `email`, `phone`, `address`, `tax_id`, `payment_terms`, `lead_time_days`, `rating`, `is_active`, `created_at`) VALUES
(1, 'SUP001', 'Office Depot PH', 'Maria Santos', 'maria@officedepot.ph', '02-8123-4567', NULL, NULL, 'Net 30', 5, 0.00, 1, '2026-08-23 11:43:10'),
(2, 'SUP002', 'Tech Solutions Inc.', 'John Cruz', 'john@techsolutions.com', '02-8765-4321', NULL, NULL, 'Net 15', 3, 0.00, 1, '2026-08-23 11:43:10'),
(3, 'SUP003', 'Global Logistics Corp', 'Anna Reyes', 'anna@globallogistics.com', '02-8987-6543', NULL, NULL, 'Net 45', 10, 0.00, 1, '2026-08-23 11:43:10'),
(5, 'SUP-004', 'Evo Logistic Hub', 'Mng. Cannor', 'cannor@gmail.com', '09874665458', 'tagay city', '14005', 'COD', 7, 0.00, 1, '2026-08-31 13:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_performance`
--

CREATE TABLE `supplier_performance` (
  `id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `otif_score` decimal(5,2) DEFAULT NULL,
  `quality_score` decimal(5,2) DEFAULT NULL,
  `responsiveness_score` decimal(5,2) DEFAULT NULL,
  `overall_rating` decimal(3,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `evaluated_by` int(11) DEFAULT NULL,
  `evaluation_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(100) DEFAULT 'POS System',
  `timezone` varchar(50) DEFAULT 'Asia/Manila',
  `currency` varchar(10) DEFAULT 'PHP',
  `tax_rate` decimal(5,2) DEFAULT 12.00,
  `po_threshold` decimal(12,2) DEFAULT 50000.00,
  `low_stock_alert` int(11) DEFAULT 10,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `site_name`, `timezone`, `currency`, `tax_rate`, `po_threshold`, `low_stock_alert`, `updated_at`) VALUES
(1, 'POS System', 'Asia/Manila', 'PHP', 12.00, 50000.00, 10, '2026-08-23 13:12:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role_id`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$2HKGFOmHySIAybPM7.0tS.nUom2h/Mf8I1P4zUDHSc8LfSys3mQYe', 'admin@pos.com', 'System Administrator', 1, 1, '2026-08-23 11:36:57'),
(7, 'user', '$2y$10$dalsz394UwWzPRlRTi2LW.15mlY2rmotpfVtdnZbT0TRc/xJyKrZG', 'marryjuana7@gmail.com', 'Marry Juana Shabu', 2, 1, '2026-08-31 11:26:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `budget_code` (`budget_code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expense_number` (`expense_number`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grn_number` (`grn_number`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `gr_items`
--
ALTER TABLE `gr_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goods_receipt_id` (`goods_receipt_id`),
  ADD KEY `po_item_id` (`po_item_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `po_items`
--
ALTER TABLE `po_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `requisition_id` (`requisition_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `finance_approver_id` (`finance_approver_id`),
  ADD KEY `procurement_officer_id` (`procurement_officer_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rfq_id` (`rfq_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`);

--
-- Indexes for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `req_number` (`req_number`),
  ADD KEY `requester_id` (`requester_id`),
  ADD KEY `budget_owner_id` (`budget_owner_id`);

--
-- Indexes for table `requisition_items`
--
ALTER TABLE `requisition_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requisition_id` (`requisition_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `return_number` (`return_number`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `return_items`
--
ALTER TABLE `return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `sales_item_id` (`sales_item_id`);

--
-- Indexes for table `rfqs`
--
ALTER TABLE `rfqs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfq_number` (`rfq_number`),
  ADD KEY `requisition_id` (`requisition_id`),
  ADD KEY `procurement_officer_id` (`procurement_officer_id`);

--
-- Indexes for table `rfq_suppliers`
--
ALTER TABLE `rfq_suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rfq_id` (`rfq_id`),
  ADD KEY `supplier_id` (`supplier_id`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_code` (`supplier_code`);

--
-- Indexes for table `supplier_performance`
--
ALTER TABLE `supplier_performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `evaluated_by` (`evaluated_by`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `backup_logs`
--
ALTER TABLE `backup_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gr_items`
--
ALTER TABLE `gr_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `po_items`
--
ALTER TABLE `po_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `requisitions`
--
ALTER TABLE `requisitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `requisition_items`
--
ALTER TABLE `requisition_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rfqs`
--
ALTER TABLE `rfqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rfq_suppliers`
--
ALTER TABLE `rfq_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplier_performance`
--
ALTER TABLE `supplier_performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD CONSTRAINT `backup_logs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD CONSTRAINT `goods_receipts_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`),
  ADD CONSTRAINT `goods_receipts_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `gr_items`
--
ALTER TABLE `gr_items`
  ADD CONSTRAINT `gr_items_ibfk_1` FOREIGN KEY (`goods_receipt_id`) REFERENCES `goods_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gr_items_ibfk_2` FOREIGN KEY (`po_item_id`) REFERENCES `po_items` (`id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `leaves_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `po_items`
--
ALTER TABLE `po_items`
  ADD CONSTRAINT `po_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_4` FOREIGN KEY (`finance_approver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_5` FOREIGN KEY (`procurement_officer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`),
  ADD CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD CONSTRAINT `requisitions_ibfk_1` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `requisitions_ibfk_2` FOREIGN KEY (`budget_owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `requisition_items`
--
ALTER TABLE `requisition_items`
  ADD CONSTRAINT `requisition_items_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `return_items`
--
ALTER TABLE `return_items`
  ADD CONSTRAINT `return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`),
  ADD CONSTRAINT `return_items_ibfk_2` FOREIGN KEY (`sales_item_id`) REFERENCES `sales_items` (`id`);

--
-- Constraints for table `rfqs`
--
ALTER TABLE `rfqs`
  ADD CONSTRAINT `rfqs_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`),
  ADD CONSTRAINT `rfqs_ibfk_2` FOREIGN KEY (`procurement_officer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rfq_suppliers`
--
ALTER TABLE `rfq_suppliers`
  ADD CONSTRAINT `rfq_suppliers_ibfk_1` FOREIGN KEY (`rfq_id`) REFERENCES `rfqs` (`id`),
  ADD CONSTRAINT `rfq_suppliers_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `sales_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `supplier_performance`
--
ALTER TABLE `supplier_performance`
  ADD CONSTRAINT `supplier_performance_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`),
  ADD CONSTRAINT `supplier_performance_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `supplier_performance_ibfk_3` FOREIGN KEY (`evaluated_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
