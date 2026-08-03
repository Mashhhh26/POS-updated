<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

// HR or Admin can edit users
$role = $_SESSION['role'] ?? 'staff';
if (!isAdmin() && $role != 'hr') {
    $_SESSION['error'] = 'Access denied. HR or Admin only.';
    redirect('../users.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $position = $_POST['position'] ?? '';
    $department = $_POST['department'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $status = $_POST['status'] ?? 'active';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        $sql = "UPDATE users SET username=?, full_name=?, position=?, department=?, role=?, status=?";
        $params = [$username, $full_name, $position, $department, $role, $status];
        
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $_SESSION['error'] = 'Passwords do not match';
                redirect('../edit-user.php?id=' . $id);
                exit();
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
        
        $_SESSION['success'] = 'User updated successfully.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../users.php');
}
?>