<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    redirect('../pages/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'] ?? 'staff';
    $is_active = $_POST['is_active'] ?? 1;
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        $sql = "UPDATE users SET username=?, full_name=?, role=?, is_active=?";
        $params = [$username, $full_name, $role, $is_active];
        
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $_SESSION['error'] = 'Passwords do not match';
                redirect('../pages/edit-user.php?id=' . $id);
            }
            $sql .= ", password=?";
            $params[] = $new_password;
        }
        
        $sql .= " WHERE id=?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($id == $_SESSION['user_id']) {
            $_SESSION['full_name'] = $full_name;
            $_SESSION['role'] = $role;
        }
        
        $_SESSION['success'] = 'User updated successfully';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../pages/users.php');
}
?>