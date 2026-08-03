<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

// HR or Admin can delete users
$role = $_SESSION['role'] ?? 'staff';
if (!isAdmin() && $role != 'hr') {
    $_SESSION['error'] = 'Access denied. HR or Admin only.';
    redirect('../users.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = 'Cannot delete yourself.';
    redirect('../users.php');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'User deleted successfully.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

redirect('../users.php');
?>