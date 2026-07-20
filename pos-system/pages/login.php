<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

// Check if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Check for login error
$login_error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            background: #f0f2f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .login-container .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-container .logo i {
            font-size: 50px;
            color: #3498db;
        }
        
        .login-container .logo h2 {
            margin-top: 10px;
            color: #2c3e50;
        }
        
        .login-container .logo p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .login-container .form-control {
            border-radius: 8px;
            padding: 12px 15px;
        }
        
        .login-container .btn-login {
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
        }
        
        .login-container .credentials {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="bi bi-shop"></i>
            <h2>POS System</h2>
            <p>Point of Sale Management</p>
        </div>
        
        <?php if(isset($login_error)): ?>
            <div id="loginError" style="display: none;"><?php echo $login_error; ?></div>
        <?php endif; ?>
        
        <form action="../actions/login-action.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>
        
        <div class="credentials">
            <small>
                <strong>Please Login Account</strong><br>
            </small>
        </div>
    </div>
    
    <script>
        // ============================================
        // CHECK FOR LOGIN ERROR
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            const errorDiv = document.getElementById('loginError');
            
            if (errorDiv) {
                const errorMessage = errorDiv.textContent.trim();
                if (errorMessage) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed! ❌',
                        text: errorMessage,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Try Again',
                        timer: 5000,
                        timerProgressBar: true
                    }).then(() => {
                        // Clear the error after showing
                        errorDiv.textContent = '';
                    });
                }
            }
        });
    </script>
</body>
</html>