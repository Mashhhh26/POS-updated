<?php
function formatCurrency($amount) {
    return 'Php ' . number_format($amount, 2);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

// ============================================
// ROLE CHECK FUNCTIONS
// ============================================

function getUserRoles($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.* FROM roles r
        JOIN user_roles ur ON r.id = ur.role_id
        WHERE ur.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function hasRole($role_name) {
    if (!isset($_SESSION['user_id'])) return false;
    
    $roles = getUserRoles($_SESSION['user_id']);
    foreach ($roles as $r) {
        if ($r['role_name'] == $role_name) {
            return true;
        }
    }
    return false;
}

function getRolesList() {
    if (!isset($_SESSION['user_id'])) return [];
    return getUserRoles($_SESSION['user_id']);
}

function getRoleBadges() {
    $roles = getRolesList();
    $badges = [];
    foreach ($roles as $r) {
        $colors = [
            'super_admin' => 'danger',
            'admin' => 'danger',
            'hr' => 'success',
            'finance' => 'info',
            'supply' => 'warning',
            'cashier' => 'primary',
            'staff' => 'secondary'
        ];
        $color = $colors[$r['role_name']] ?? 'secondary';
        $badges[] = '<span class="badge bg-' . $color . '">' . ucfirst($r['role_name']) . '</span>';
    }
    return implode(' ', $badges);
}

function hasAnyRole() {
    if (!isset($_SESSION['user_id'])) return false;
    $roles = getUserRoles($_SESSION['user_id']);
    return count($roles) > 0;
}
?>