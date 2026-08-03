<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Access denied. Admin only.';
    redirect('../pages/user-roles.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $roles = $_POST['roles'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        foreach ($roles as $role_id) {
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $role_id]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = 'User roles updated successfully.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../pages/user-roles.php');
}
?>