<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid role ID']);
    exit();
}

// Get role
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$id]);
$role = $stmt->fetch();

if (!$role) {
    echo json_encode(['error' => 'Role not found']);
    exit();
}

// Get all permissions
$permissions = $pdo->query("SELECT * FROM permissions ORDER BY module, permission_name")->fetchAll();

// Get role's permissions
$role_perms = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
$role_perms->execute([$id]);
$role_permission_ids = $role_perms->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'id' => $role['id'],
    'role_name' => $role['role_name'],
    'description' => $role['description'],
    'permissions' => $permissions,
    'role_permissions' => $role_permission_ids
]);
?>