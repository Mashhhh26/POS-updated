<?php
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/config/database.php';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?php echo BASE_PATH; ?>index.php">
            <i class="fas fa-store me-2"></i>POS System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <span class="nav-link text-white-50">
                        <i class="fas fa-user-circle me-1"></i> 
                        <?php echo $_SESSION['full_name'] ?? 'Guest'; ?>
                        <span class="badge bg-light text-dark ms-1"><?php echo $_SESSION['role'] ?? 'Unknown'; ?></span>
                    </span>
                </li>
                <li class="nav-item">
                    <button onclick="toggleTheme()" class="btn btn-light btn-sm ms-2">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_PATH; ?>logout.php" class="btn btn-danger btn-sm ms-2">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css">

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>