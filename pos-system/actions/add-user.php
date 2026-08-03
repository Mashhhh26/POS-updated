<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    redirect('../pages/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'staff';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, full_name, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $full_name, $password, $role]);
        $_SESSION['success'] = 'User added successfully';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Username already exists';
    }
    
    redirect('../pages/users.php');
}
?>