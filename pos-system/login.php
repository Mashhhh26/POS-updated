<?php
session_start();
require_once 'config/database.php';

$db = getDB();
$jobPostings=$db->query("SELECT id,title,department,employment_type,location FROM job_postings WHERE status='published' ORDER BY created_at DESC LIMIT 4")->fetchAll();

// Auto-create the development admin account if the database is empty.
$check = $db->query("SELECT * FROM users WHERE username = 'admin'");
if ($check->rowCount() === 0) {
    $hashed = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password, email, full_name, role_id, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $hashed, 'admin@pos.com', 'System Administrator', 1, 1]);
}

// Make sure Admin always has all permissions.
$adminCheck = $db->query("SELECT COUNT(*) FROM role_permissions WHERE role_id = 1")->fetchColumn();
if ((int)$adminCheck === 0) {
    $perms = $db->query("SELECT id FROM permissions");
    while ($perm = $perms->fetch()) {
        $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (1, ?)")->execute([$perm['id']]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT u.*, r.role_name FROM users u
                           LEFT JOIN roles r ON u.role_id = r.id
                           WHERE u.username = ? AND u.is_active = 1 AND COALESCE(u.account_status, 'active') = 'active'");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role_name'];
        $_SESSION['role_id'] = $user['role_id'];

        $permStmt = $db->prepare("SELECT p.permission_name FROM role_permissions rp
                                   JOIN permissions p ON rp.permission_id = p.id
                                   WHERE rp.role_id = ?");
        $permStmt->execute([$user['role_id']]);
        $permissions = $permStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($permissions) && (int)$user['role_id'] === 1) {
            $allPerms = $db->query("SELECT id FROM permissions");
            while ($perm = $allPerms->fetch()) {
                $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (1, ?)")->execute([$perm['id']]);
            }
            $permStmt->execute([$user['role_id']]);
            $permissions = $permStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $_SESSION['permissions'] = $permissions;

        // Automatic attendance: only employees linked to this account get a daily time-in.
        try {
            $empStmt=$db->prepare("SELECT id FROM employees WHERE user_id=? AND status='active' LIMIT 1");
            $empStmt->execute([$user['id']]); $employee=$empStmt->fetch();
            if($employee){
                $att=$db->prepare("SELECT id,time_out FROM attendance WHERE user_id=? AND date=CURDATE() ORDER BY id DESC LIMIT 1");
                $att->execute([$user['id']]); $today=$att->fetch();
                if(!$today){
                    $db->prepare("INSERT INTO attendance(user_id,date,time_in,status,notes) VALUES(?,CURDATE(),CURTIME(),'present','Automatic time-in on login')")->execute([$user['id']]);
                }
            }
        } catch(Throwable $attendanceError) {
            // Login must never fail because attendance logging fails.
            error_log('Automatic attendance error: '.$attendanceError->getMessage());
        }

        logActivity("User logged in: {$username}");
        header('Location: ' . BASE_PATH . 'index.php');
        exit();
    }

    $error = 'Invalid username or password, or this account is not active.';
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS System</title>
    <link href="assets/vendor/bootstrap/bootstrap.min.css?v=20260913" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/fontawesome/all.min.css?v=20260913">
    <link rel="stylesheet" href="assets/css/custom.css?v=20260913">
    <style>
        .login-shell{min-height:100vh;display:grid;place-items:center;padding:1.25rem;position:relative;overflow:hidden}
        .login-shell::before,.login-shell::after{content:"";position:absolute;border-radius:50%;filter:blur(4px);pointer-events:none}
        .login-shell::before{width:420px;height:420px;background:rgba(var(--pos-primary-rgb),.12);top:-180px;right:-120px}
        .login-shell::after{width:360px;height:360px;background:rgba(6,182,212,.10);bottom:-170px;left:-120px}
        .login-grid{width:min(1080px,100%);display:grid;grid-template-columns:1.05fr .95fr;position:relative;z-index:1;border:1px solid var(--pos-border);border-radius:26px;overflow:hidden;background:var(--pos-surface);box-shadow:0 28px 70px rgba(15,23,42,.14)}
        .login-hero{padding:3.5rem;background:linear-gradient(145deg,var(--pos-primary),var(--pos-primary-2));color:#fff;position:relative;overflow:hidden}
        .login-hero::after{content:"";position:absolute;width:280px;height:280px;border:1px solid rgba(255,255,255,.18);border-radius:50%;right:-90px;bottom:-100px;box-shadow:0 0 0 45px rgba(255,255,255,.04),0 0 0 90px rgba(255,255,255,.03)}
        .brand-mark{width:64px;height:64px;display:grid;place-items:center;border-radius:18px;background:rgba(255,255,255,.15);font-size:1.65rem;backdrop-filter:blur(8px)}
        .login-form{padding:3rem}
        .login-form .form-control{height:50px}
        .login-form .input-group-text{width:48px;justify-content:center}
        .login-badge{display:inline-flex;align-items:center;gap:.45rem;padding:.4rem .7rem;border-radius:999px;background:rgba(255,255,255,.13);font-size:.78rem}
        @media(max-width:800px){.login-grid{grid-template-columns:1fr}.login-hero{display:none}.login-form{padding:2rem}}
    </style>
</head>
<body>
<div class="login-shell">
    <div class="login-grid">
        <section class="login-hero d-flex flex-column justify-content-between">
            <div>
                <div class="brand-mark mb-4"><i class="fas fa-store"></i></div>
                <span class="login-badge"><i class="fas fa-shield-alt"></i> Secure business workspace</span>
                <h1 class="display-5 fw-bold mt-4 mb-3">Run your store with confidence.</h1>
                <p class="lead mb-3" style="opacity:.88">A single workspace for daily sales, stock control, purchasing, finance, and people operations.</p>
                <?php if($jobPostings): ?><div class="mt-4"><div class="small fw-bold text-uppercase mb-2" style="letter-spacing:.08em;opacity:.75"><i class="fas fa-briefcase me-1"></i> We are hiring</div><?php foreach($jobPostings as $j): ?><a class="d-block text-white text-decoration-none p-2 mb-2 rounded-3" style="background:rgba(255,255,255,.10)" href="apply.php?job_id=<?php echo (int)$j['id']; ?>"><strong><?php echo h($j['title']); ?></strong><span class="d-block small" style="opacity:.75"><?php echo h($j['department']); ?> · Apply now</span></a><?php endforeach; ?></div><?php endif; ?>
            </div>
            <div class="mt-5 d-flex flex-wrap gap-2 small" style="opacity:.82"><span class="login-badge"><i class="fas fa-bolt"></i> Fast local workspace</span><span class="login-badge"><i class="fas fa-shield-halved"></i> Protected access</span><span class="login-badge"><i class="fas fa-plug-circle-xmark"></i> Offline ready</span></div>
        </section>

        <section class="login-form">
            <div class="mb-4">
                <div class="text-primary fw-bold small text-uppercase" style="letter-spacing:.1em">Welcome back</div>
                <h2 class="fw-bold mt-1 mb-1">Sign in to POS System</h2>
                <p class="text-muted mb-0">Use your authorized account to continue.</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-circle-check me-2"></i>You have been logged out successfully.</div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" autocomplete="username" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" autocomplete="current-password" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm"><i class="fas fa-arrow-right-to-bracket me-2"></i> Sign in</button>
                </div>
            </form>

            <div class="mt-4 pt-3 border-top">
                <div class="d-flex align-items-start gap-2 text-muted small">
                    <i class="fas fa-circle-info mt-1"></i>
                    <span><strong>Hello and Welocome!</strong></span>
                </div>
            </div>
        </section>
    </div>
</div>
<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
<script src="assets/js/script.js?v=20260913"></script>
</body>
</html>
