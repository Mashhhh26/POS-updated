<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Access denied. Admin only.';
    redirect('../employees.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $position = $_POST['position'] ?? '';
    $department = $_POST['department'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    
    // Validate
    if (empty($username) || empty($password) || empty($full_name)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        redirect('../employees.php');
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, full_name, position, department, role) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$username, $password, $full_name, $position, $department, $role]);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Employee Added!',
            'text' => $full_name . ' has been added as ' . ucfirst($role) . '.'
        ];
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../employees.php');
}
?>