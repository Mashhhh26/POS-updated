<?php
require_once 'functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../pages/login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);

// ============================================
// PAGE ACCESS RULES
// ============================================

// 1. ADMIN ONLY PAGES (Full Admin only)
$admin_only_pages = [
    'users.php', 
    'add-user.php', 
    'edit-user.php',
    'delete-user-action.php',
    'add-user-action.php'
];

if (in_array($current_page, $admin_only_pages) && !isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'This page is for Administrators only.'
    ];
    redirect('dashboard.php');
    exit();
}

// 2. ADMIN OR CO-ADMIN PAGES (Reports REMOVED)
$admin_coadmin_pages = [
    'add-product.php', 
    'edit-product.php',
    'add-product-action.php',
    'edit-product-action.php',
    'delete-product-action.php'
    // 'reports.php' - REMOVED
];

if (in_array($current_page, $admin_coadmin_pages) && !isAdminOrCoAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'This page is for Admin or Co-Admin only.'
    ];
    redirect('dashboard.php');
    exit();
}

// 3. STAFF RESTRICTIONS
if (isStaff()) {
    $blocked_for_staff = [
        'add-product.php',
        'edit-product.php',
        'add-product-action.php',
        'edit-product-action.php',
        'delete-product-action.php',
        'users.php',
        'add-user.php',
        'edit-user.php',
        'add-user-action.php',
        'delete-user-action.php'
        // 'reports.php' - REMOVED
    ];
    
    if (in_array($current_page, $blocked_for_staff)) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Access Denied!',
            'text' => 'Staff cannot access this page.'
        ];
        redirect('dashboard.php');
        exit();
    }
}
?>