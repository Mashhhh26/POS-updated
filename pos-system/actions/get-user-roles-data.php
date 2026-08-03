<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$user_id = $_GET['user_id'] ?? 0;

if ($user_id <= 0) {
    echo json_encode(['error' => 'Invalid user ID']);
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit();
}

$roles = $pdo->query("SELECT * FROM roles ORDER BY role_name")->fetchAll();

$user_roles = $pdo->prepare("SELECT role_id FROM user_roles WHERE user_id = ?");
$user_roles->execute([$user_id]);
$user_role_ids = $user_roles->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'full_name' => $user['full_name']
    ],
    'roles' => $roles,
    'user_roles' => $user_role_ids
]);
?>