<?php
session_start();

require_once '../includes/functions.php';
require_once '../config/database.php';

// Only Admin can add users
if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'Only Administrators can add users.'
    ];
    redirect('../pages/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    
    // Validate
    if (empty($username) || empty($full_name) || empty($password)) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Please fill in all required fields.'
        ];
        redirect('../pages/users.php');
        exit();
    }
    
    // Check if password is at least 6 characters
    if (strlen($password) < 6) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Password Too Short!',
            'text' => 'Password must be at least 6 characters.'
        ];
        redirect('../pages/users.php');
        exit();
    }
    
    // Validate role
    if (!in_array($role, ['admin', 'co-admin', 'staff'])) {
        $role = 'staff';
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, full_name, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $full_name, $password, $role]);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'User Added!',
            'text' => ucfirst($role) . ' account created successfully.'
        ];
    } catch (PDOException $e) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Username already exists or database error.'
        ];
    }
    
    redirect('../pages/users.php');
} else {
    redirect('../pages/users.php');
}
?>