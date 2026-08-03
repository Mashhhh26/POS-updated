<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Access denied. Admin only.';
    redirect('../pages/roles.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid role ID.';
    redirect('../pages/roles.php');
    exit();
}

// Check if role has users
$check = $pdo->prepare("SELECT COUNT(*) FROM user_roles WHERE role_id = ?");
$check->execute([$id]);
if ($check->fetchColumn() > 0) {
    $_SESSION['error'] = 'Cannot delete role with users assigned.';
    redirect('../pages/roles.php');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Role deleted successfully.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

redirect('../pages/roles.php');
?>