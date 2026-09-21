<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user confirmed logout
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    ?>
    <!DOCTYPE html>
    <html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
    <head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Logout</title>
        <link href="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
        <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css?v=20260913">
    </head>
    <body>
        <div class="container">
            <div class="row min-vh-100 align-items-center justify-content-center">
                <div class="col-md-5 col-lg-4">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <i class="fas fa-sign-out-alt text-danger fa-4x"></i>
                            </div>
                            <h4 class="mb-3">Logout Confirmation</h4>
                            <p class="text-muted mb-4">Are you sure you want to logout from the POS System?</p>
                            <div class="d-grid gap-2">
                                <a id="logoutBtn" href="<?php echo BASE_PATH; ?>logout.php?confirm=yes" class="btn btn-danger btn-lg">
                                    <i class="fas fa-sign-out-alt me-2"></i> Yes, Logout
                                </a>
                                <a href="javascript:history.back()" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
        <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
        <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
        
        <script>
            document.getElementById('logoutBtn')?.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.href;
                Swal.fire({
                    title: 'Logging out...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });
                setTimeout(() => window.location.href = href, 500);
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}

// Process logout: close the latest open attendance session for this employee.
if (isset($_SESSION['user_id'])) {
    try {
        $db=getDB();
        $stmt=$db->prepare("UPDATE attendance SET time_out=CURTIME(), notes=CASE WHEN notes IS NULL OR notes='' THEN 'Automatic time-out on logout' ELSE CONCAT(notes,' | Automatic time-out on logout') END WHERE id=(SELECT id FROM (SELECT id FROM attendance WHERE user_id=? AND date=CURDATE() AND time_out IS NULL ORDER BY id DESC LIMIT 1) x)");
        $stmt->execute([$_SESSION['user_id']]);
        logActivity('User logged out');
    } catch(Throwable $e) { error_log('Automatic attendance timeout error: '.$e->getMessage()); }
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
header("Location: login.php?logout=success");
exit();
?>