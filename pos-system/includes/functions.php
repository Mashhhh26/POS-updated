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
// PERMISSION CHECKS
// ============================================

// Can manage users (Admin only)
function canManageUsers() {
    return isAdmin();
}

// Can manage products (Admin or Co-Admin)
function canManageProducts() {
    return isAdmin() || isCoAdmin();
}

// Can view reports (Admin or Co-Admin)
function canViewReports() {
    return isAdmin() || isCoAdmin();
}

// Can delete items (Admin or Co-Admin)
function canDelete() {
    return isAdmin() || isCoAdmin();
}

// Can add/edit products (Admin or Co-Admin)
function canEditProducts() {
    return isAdmin() || isCoAdmin();
}

// ============================================
// DISPLAY FUNCTIONS
// ============================================

function getRoleBadge($role) {
    $colors = [
        'admin' => 'danger',
        'co-admin' => 'warning',
        'staff' => 'info'
    ];
    $color = $colors[$role] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . ucfirst($role) . '</span>';
}

function getRoleIcon($role) {
    $icons = [
        'admin' => 'bi-shield-lock',
        'co-admin' => 'bi-shield',
        'staff' => 'bi-person'
    ];
    return $icons[$role] ?? 'bi-person';
}
?>