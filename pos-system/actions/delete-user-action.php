<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    redirect('../pages/dashboard.php');
}

$id = $_GET['id'] ?? 0;

if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = 'Cannot delete yourself';
} else {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = 'User deleted successfully';
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
}

redirect('../pages/users.php');
?>