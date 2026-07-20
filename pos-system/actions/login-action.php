<?php
session_start();

require_once '../includes/functions.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Please enter username and password.';
        header("Location: ../pages/login.php");
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && $password == $user['password']) {
            // Check if account is active
            if (isset($user['is_active']) && $user['is_active'] == 0) {
                $_SESSION['login_error'] = 'Your account is deactivated. Please contact administrator.';
                header("Location: ../pages/login.php");
                exit();
            }
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login
            $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update->execute([$user['id']]);
            
            // Set success message for dashboard
            $_SESSION['swal'] = [
                'type' => 'success',
                'title' => 'Welcome ' . $user['full_name'] . '!',
                'text' => 'You are logged in as ' . ucfirst($user['role']) . '.'
            ];
            
            redirect('../pages/dashboard.php');
            
        } else {
            // Invalid login - set error message
            $_SESSION['login_error'] = 'Invalid username or password. Please try again.';
            header("Location: ../pages/login.php");
            exit();
        }
        
    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'Database error. Please try again later.';
        header("Location: ../pages/login.php");
        exit();
    }
    
} else {
    redirect('../pages/login.php');
}
?>