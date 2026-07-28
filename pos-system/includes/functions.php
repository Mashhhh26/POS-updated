<?php
// ============================================
// CALCULATION FUNCTIONS
// ============================================

function calculateVAT($amount) {
    return $amount * 0.12;
}

function applySeniorDiscount($amount) {
    return $amount * 0.20;
}

function generateInvoiceNumber() {
    return 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
}

function formatCurrency($amount) {
    return 'Php ' . number_format($amount, 2);
}

// ============================================
// ROLE FUNCTIONS
// ============================================

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isCoAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'co-admin';
}

function isAdminOrCoAdmin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'co-admin');
}

function isStaff() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'staff';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

// ============================================
// ROLE DETECTION FUNCTIONS
// ============================================

function hasRole($role_name) {
    // Try RBAC first
    if (function_exists('getUserRoles')) {
        $user_roles = getUserRoles($_SESSION['user_id'] ?? 0);
        foreach ($user_roles as $r) {
            if ($r['role_name'] == $role_name) {
                return true;
            }
        }
        return false;
    }
    // Fallback: Check session role
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'] == $role_name;
    }
    return false;
}

function isHR() {
    if (function_exists('hasRole')) {
        return hasRole('hr');
    }
    return isset($_SESSION['role']) && $_SESSION['role'] == 'hr';
}

function isFinance() {
    if (function_exists('hasRole')) {
        return hasRole('finance');
    }
    return isset($_SESSION['role']) && $_SESSION['role'] == 'finance';
}

function isSupply() {
    if (function_exists('hasRole')) {
        return hasRole('supply') || hasRole('ceo');
    }
    return isset($_SESSION['role']) && ($_SESSION['role'] == 'supply' || $_SESSION['role'] == 'ceo');
}

// ============================================
// SIDEBAR FUNCTION
// ============================================

function getSidebar() {
    if (isHR()) {
        return 'includes/sidebar/hr-sidebar.php';
    } elseif (isFinance()) {
        return 'includes/sidebar/finance-sidebar.php';
    } elseif (isSupply()) {
        return 'includes/sidebar/supply-sidebar.php';
    } else {
        return 'includes/sidebar/main-sidebar.php';
    }
}

// ============================================
// PERMISSION FUNCTIONS (Fallback if RBAC not loaded)
// ============================================

if (!function_exists('userCan')) {
    function userCan($permission) {
        return isAdmin() || isCoAdmin();
    }
}

// ============================================
// REMOVED: getUserRoles() - Nasa rbac/database.php na
// REMOVED: requirePermission() - Nasa rbac/database.php na
// ============================================
?>