<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'Only administrators can delete roles.'
    ];
    redirect('../pages/roles.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid role ID.'
    ];
    redirect('../pages/roles.php');
    exit();
}

// Check if role has users
$user_count = $pdo->prepare("SELECT COUNT(*) FROM user_roles WHERE role_id = ?");
$user_count->execute([$id]);
if ($user_count->fetchColumn() > 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Cannot Delete!',
        'text' => 'This role has users assigned. Please remove users first.'
    ];
    redirect('../pages/roles.php');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Role Deleted!',
        'text' => 'Role has been deleted successfully.'
    ];
} catch (PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to delete role: ' . $e->getMessage()
    ];
}

redirect('../pages/roles.php');
?>