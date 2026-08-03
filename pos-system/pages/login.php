<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (isLoggedIn()) {
    // If already logged in, redirect based on role
    $role = $_SESSION['role'] ?? 'staff';
    
    if ($role == 'hr') {
        redirect('../modules/hr/index.php');
    } elseif ($role == 'finance') {
        redirect('../modules/finance/index.php');
    } elseif ($role == 'supply') {
        redirect('../modules/supply/index.php');
    } elseif ($role == 'cashier') {
        redirect('../modules/cashier/index.php');
    } else {
        redirect('dashboard.php');
    }
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { max-width: 400px; width: 100%; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .login-box h2 { text-align: center; margin-bottom: 30px; color: #2c3e50; }
        .login-box .logo { text-align: center; font-size: 50px; color: #3498db; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="logo"><i class="bi bi-shop"></i></div>
    <h2>POS System</h2>
    <?php if($error): ?>
        <div id="loginError" style="display:none;"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="../actions/login.php" method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="text-center mt-3 small text-muted">
        <strong>Please Login</strong>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const errorDiv = document.getElementById('loginError');
    if (errorDiv && errorDiv.textContent.trim()) {
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: errorDiv.textContent.trim(),
            confirmButtonColor: '#d33'
        });
    }
});
</script>
</body>
</html>