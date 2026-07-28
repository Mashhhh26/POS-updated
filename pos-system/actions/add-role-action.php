<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'Only administrators can create roles.'
    ];
    redirect('../pages/roles.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role_name = trim($_POST['role_name']);
    $description = trim($_POST['description'] ?? '');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
        $stmt->execute([$role_name, $description]);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Role Created!',
            'text' => 'Role "' . $role_name . '" has been created successfully.'
        ];
    } catch (PDOException $e) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to create role: ' . $e->getMessage()
        ];
    }
    
    redirect('../pages/roles.php');
}
?>