<?php
session_start();
require_once 'config/database.php';

$db = getDB();

// Auto-create admin if not exists
$check = $db->query("SELECT * FROM users WHERE username = 'admin'");
if ($check->rowCount() == 0) {
    $hashed = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password, email, full_name, role_id, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $hashed, 'admin@pos.com', 'System Administrator', 1, 1]);
}

// Make sure admin has permissions
$adminCheck = $db->query("SELECT COUNT(*) FROM role_permissions WHERE role_id = 1")->fetchColumn();
if ($adminCheck == 0) {
    $perms = $db->query("SELECT id FROM permissions");
    while ($perm = $perms->fetch()) {
        $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (1, ?)")->execute([$perm['id']]);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $db->prepare("SELECT u.*, r.role_name FROM users u 
                           LEFT JOIN roles r ON u.role_id = r.id 
                           WHERE u.username = ? AND u.is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role_name'];
        $_SESSION['role_id'] = $user['role_id'];
        
        // Load permissions
        $permStmt = $db->prepare("SELECT p.permission_name FROM role_permissions rp 
                                   JOIN permissions p ON rp.permission_id = p.id 
                                   WHERE rp.role_id = ?");
        $permStmt->execute([$user['role_id']]);
        $permissions = $permStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // If admin and no permissions, grant all
        if (empty($permissions) && $user['role_id'] == 1) {
            $allPerms = $db->query("SELECT id FROM permissions");
            while ($perm = $allPerms->fetch()) {
                $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (1, ?)")->execute([$perm['id']]);
            }
            $permStmt->execute([$user['role_id']]);
            $permissions = $permStmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $_SESSION['permissions'] = $permissions;
        
        logActivity("User logged in: {$username}");
        
        header('Location: ' . BASE_PATH . 'index.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-store fa-3x text-primary"></i>
                            <h3 class="mt-2 fw-bold">POS System</h3>
                            <p class="text-muted">Please login to continue</p>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                            <div class="alert alert-success">You have been logged out successfully.</div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="username" class="form-control" value="admin" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" class="form-control" value="admin123" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i> Default: admin / admin123
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>