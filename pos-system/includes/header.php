<?php
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/config/database.php';
}
$siteTitle = 'POS System';
?>
<meta name="csrf-token" content="<?php echo h(csrf_token()); ?>">
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm pos-topbar">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?php echo BASE_PATH; ?>index.php">
            <i class="fas fa-store me-2"></i><?php echo htmlspecialchars($siteTitle); ?>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <span class="nav-link text-white-50">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Guest'); ?>
                        <span class="badge bg-light text-dark ms-1"><?php echo htmlspecialchars($_SESSION['role'] ?? 'Unknown'); ?></span>
                    </span>
                </li>
                <li class="nav-item dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle ms-lg-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Theme settings">
                        <i class="fas fa-palette me-1"></i><span class="d-none d-sm-inline">Theme</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end theme-menu">
                        <div class="theme-label">Appearance</div>
                        <button class="theme-choice" type="button" onclick="setThemeMode('light')"><i class="fas fa-sun fa-fw"></i> Light mode</button>
                        <button class="theme-choice" type="button" onclick="setThemeMode('dark')"><i class="fas fa-moon fa-fw"></i> Dark mode</button>
                        <div class="dropdown-divider"></div>
                        <div class="theme-label">Accent color</div>
                        <?php foreach ([['indigo','Indigo'],['blue','Ocean Blue'],['emerald','Emerald'],['violet','Violet'],['rose','Rose'],['amber','Amber']] as $theme): ?>
                            <button class="theme-choice" type="button" data-palette="<?php echo $theme[0]; ?>">
                                <span class="theme-dot <?php echo $theme[0]; ?>"></span><?php echo $theme[1]; ?><i class="fas fa-check check"></i>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </li>
                <li class="nav-item">
                    <button onclick="toggleTheme()" class="btn btn-outline-light btn-sm ms-lg-1" title="Toggle dark/light mode">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_PATH; ?>logout.php" class="btn btn-danger btn-sm ms-lg-1">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
<div id="themeTransition" aria-hidden="true"></div>

<link href="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913" rel="stylesheet">
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css?v=20260913">
<script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
<script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
<script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
