<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'Only administrators can edit roles.'
    ];
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
        
        // Update role
        $stmt = $pdo->prepare("UPDATE roles SET role_name = ?, description = ? WHERE id = ?");
        $stmt->execute([$role_name, $description, $role_id]);
        
        // Remove all existing permissions
        $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$role_id]);
        
        // Add new permissions
        foreach ($permissions as $perm_id) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $stmt->execute([$role_id, $perm_id]);
        }
        
        $pdo->commit();
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Role Updated!',
            'text' => 'Role "' . $role_name . '" has been updated successfully.'
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to update role: ' . $e->getMessage()
        ];
    }
    
    redirect('../pages/roles.php');
}
?>