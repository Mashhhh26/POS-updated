<?php
session_start();
require_once '../config/database.php';
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$db = getDB();

// Get widget data
$widgets = [];

// Sales Today
$widgets['sales_today'] = $db->query("
    SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total 
    FROM sales 
    WHERE DATE(created_at) = CURDATE()
")->fetch();

// Sales This Week
$widgets['sales_week'] = $db->query("
    SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total 
    FROM sales 
    WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())
")->fetch();

// Sales This Month
$widgets['sales_month'] = $db->query("
    SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total 
    FROM sales 
    WHERE MONTH(created_at) = MONTH(CURDATE()) 
    AND YEAR(created_at) = YEAR(CURDATE())
")->fetch();

// Low Stock Products
$widgets['low_stock'] = $db->query("
    SELECT COUNT(*) as count 
    FROM products 
    WHERE stock_quantity <= 5
")->fetchColumn();

// Total Products
$widgets['total_products'] = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Total Users
$widgets['total_users'] = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Pending Requisitions
$widgets['pending_reqs'] = $db->query("
    SELECT COUNT(*) 
    FROM requisitions 
    WHERE status = 'pending_budget'
")->fetchColumn();

// Pending POs
$widgets['pending_pos'] = $db->query("
    SELECT COUNT(*) 
    FROM purchase_orders 
    WHERE status = 'pending_approval'
")->fetchColumn();

// Monthly Sales Chart Data
$monthlySales = $db->query("
    SELECT 
        DATE_FORMAT(created_at, '%b') as month,
        COUNT(*) as count,
        COALESCE(SUM(total_amount), 0) as total
    FROM sales 
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
")->fetchAll();

$widgets['monthly_labels'] = array_column($monthlySales, 'month');
$widgets['monthly_data'] = array_column($monthlySales, 'total');

// Top 5 Products
$widgets['top_products'] = $db->query("
    SELECT 
        p.product_name,
        SUM(si.quantity) as total_sold,
        SUM(si.subtotal) as total_revenue
    FROM sales_items si
    JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE DATE(s.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll();

// Today's Activities
$widgets['activities'] = $db->query("
    SELECT al.*, u.full_name as user
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    WHERE DATE(al.created_at) = CURDATE()
    ORDER BY al.created_at DESC
    LIMIT 10
")->fetchAll();
?>