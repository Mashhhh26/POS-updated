<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Access denied. Admin only.';
    redirect('../pages/roles.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role_id = $_POST['role_id'];
    $role_name = trim($_POST['role_name']);
    $description = trim($_POST['description'] ?? '');
    $permissions = $_POST['permissions'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE roles SET role_name = ?, description = ? WHERE id = ?");
        $stmt->execute([$role_name, $description, $role_id]);
        
        $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$role_id]);
        
        foreach ($permissions as $perm_id) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $stmt->execute([$role_id, $perm_id]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = 'Role updated successfully.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../pages/roles.php');
}
?>