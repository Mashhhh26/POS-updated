<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && $password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        // ============================================
        // CHECK ACTUAL ROLES FROM user_roles
        // ============================================
        $stmt = $pdo->prepare("
            SELECT r.role_name 
            FROM roles r 
            JOIN user_roles ur ON r.id = ur.role_id 
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$user['id']]);
        $actual_roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // ============================================
        // REDIRECT BASED ON ROLES (WITH FALLBACK)
        // ============================================
        if (in_array('admin', $actual_roles) || in_array('super_admin', $actual_roles)) {
            redirect('../pages/dashboard.php');
        } elseif (in_array('hr', $actual_roles)) {
            redirect('../modules/hr/index.php');
        } elseif (in_array('finance', $actual_roles)) {
            redirect('../modules/finance/index.php');
        } elseif (in_array('supply', $actual_roles)) {
            redirect('../modules/supply/index.php');
        } elseif (in_array('cashier', $actual_roles)) {
            redirect('../modules/cashier/index.php');
        } else {
            // ============================================
            // FALLBACK: If no roles, redirect to dashboard
            // ============================================
            $_SESSION['swal'] = [
                'type' => 'warning',
                'title' => 'No Role Assigned',
                'text' => 'Please contact administrator to assign your role.'
            ];
            redirect('../pages/dashboard.php');
        }
        
    } else {
        $_SESSION['login_error'] = 'Invalid username or password';
        redirect('../pages/login.php');
    }
} else {
    redirect('../pages/login.php');
}
?>