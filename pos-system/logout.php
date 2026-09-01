<?php
session_start();

// Check if user confirmed logout
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    ?>
    <!DOCTYPE html>
    <html lang="en" data-bs-theme="light">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Logout</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="assets/css/custom.css">
    </head>
    <body class="bg-light">
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
                                <a href="logout.php?confirm=yes" class="btn btn-danger btn-lg">
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
        
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/script.js"></script>
        
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

// Process logout
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