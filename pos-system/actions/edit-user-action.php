<?php
session_start();

require_once '../includes/functions.php';
require_once '../config/database.php';

// Only Admin can edit users
if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'Only Administrators can edit users.'
    ];
    redirect('../pages/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? 0;
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'] ?? 'staff';
    $is_active = $_POST['is_active'] ?? 1;
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate
    if ($id <= 0) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Invalid user ID.'
        ];
        redirect('../pages/users.php');
        exit();
    }
    
    // Check if username already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $id]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Username already exists.'
        ];
        redirect('../pages/edit-user.php?id=' . $id);
        exit();
    }
    
    // Validate role
    if (!in_array($role, ['admin', 'co-admin', 'staff'])) {
        $role = 'staff';
    }
    
    try {
        // Start building the update query
        $sql = "UPDATE users SET 
                username = ?, 
                full_name = ?, 
                role = ?, 
                is_active = ?";
        
        $params = [$username, $full_name, $role, $is_active];
        
        // If password is provided, update it
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $_SESSION['swal'] = [
                    'type' => 'error',
                    'title' => 'Password Mismatch!',
                    'text' => 'New password and confirm password do not match.'
                ];
                redirect('../pages/edit-user.php?id=' . $id);
                exit();
            }
            
            // Check if password is at least 6 characters
            if (strlen($new_password) < 6) {
                $_SESSION['swal'] = [
                    'type' => 'error',
                    'title' => 'Password Too Short!',
                    'text' => 'Password must be at least 6 characters.'
                ];
                redirect('../pages/edit-user.php?id=' . $id);
                exit();
            }
            
            $sql .= ", password = ?";
            $params[] = $new_password;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // If updating current user's own account, update session
        if ($id == $_SESSION['user_id']) {
            $_SESSION['full_name'] = $full_name;
            $_SESSION['role'] = $role;
        }
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'User Updated!',
            'text' => 'User account has been updated successfully.'
        ];
        redirect('../pages/users.php');
        
    } catch (PDOException $e) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Database error: ' . $e->getMessage()
        ];
        redirect('../pages/edit-user.php?id=' . $id);
    }
} else {
    redirect('../pages/users.php');
}
?>