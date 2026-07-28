<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'Only administrators can assign user roles.'
    ];
    redirect('../pages/user-roles.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $roles = $_POST['roles'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        // Remove all existing roles
        $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Add new roles
        foreach ($roles as $role_id) {
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $role_id]);
        }
        
        $pdo->commit();
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Roles Updated!',
            'text' => 'User roles have been updated successfully.'
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to update user roles: ' . $e->getMessage()
        ];
    }
    
    redirect('../pages/user-roles.php');
}
?>