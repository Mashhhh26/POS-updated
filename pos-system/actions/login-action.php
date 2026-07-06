<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && $password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        redirect('../pages/dashboard.php');
    } else {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Login Failed!',
            'text' => 'Invalid username or password.'
        ];
        redirect('../pages/login.php');
    }
} else {
    redirect('../pages/login.php');
}
?>