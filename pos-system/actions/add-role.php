<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Access denied. Admin only.';
    redirect('../pages/roles.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role_name = trim($_POST['role_name']);
    $description = trim($_POST['description'] ?? '');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
        $stmt->execute([$role_name, $description]);
        $_SESSION['success'] = 'Role created successfully.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../pages/roles.php');
}
?>