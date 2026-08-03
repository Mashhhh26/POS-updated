<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: #f8f9fa; }
        .top-nav {
            background: #2c3e50;
            padding: 10px 20px;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .top-nav .brand {
            font-size: 20px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        .top-nav .brand:hover { color: #3498db; }
        .top-nav .nav-link {
            color: #ecf0f1;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .top-nav .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .top-nav .nav-link.active {
            background: #3498db;
            color: white;
        }
        .top-nav .user-info {
            color: #ecf0f1;
            font-size: 14px;
        }
        .top-nav .user-info .badge {
            margin-left: 5px;
        }
        .sidebar {
            min-height: calc(100vh - 70px);
            background: #2c3e50;
            padding: 20px;
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            background: #3498db;
        }
        .sidebar .nav-link i {
            width: 20px;
        }
        .main-content { padding: 20px; }
        .card-stats { border-left: 4px solid #3498db; }
        .card-stats h3 { font-size: 28px; font-weight: bold; }
        .card-stats .stat-icon { font-size: 40px; opacity: 0.3; float: right; }
        @media (max-width: 768px) {
            .top-nav .brand { font-size: 16px; }
            .top-nav .nav-link { padding: 5px 10px; font-size: 13px; }
        }
    </style>
</head>
<body>

<?php
$role = $_SESSION['role'] ?? 'staff';
$current_dir = basename(dirname($_SERVER['SCRIPT_FILENAME']));
$current_page = basename($_SERVER['PHP_SELF']);
$is_in_module = in_array($current_dir, ['hr', 'finance', 'supply', 'cashier']);

// ============================================
// PATHS
// ============================================
if ($is_in_module) {
    $dashboard_href = '../../pages/dashboard.php';
    $hr_href = '../../modules/hr/';
    $finance_href = '../../modules/finance/';
    $supply_href = '../../modules/supply/';
    $cashier_href = '../../modules/cashier/';
    $logout_href = '../../actions/logout.php';
} else {
    $dashboard_href = 'dashboard.php';
    $hr_href = '../modules/hr/';
    $finance_href = '../modules/finance/';
    $supply_href = '../modules/supply/';
    $cashier_href = '../modules/cashier/';
    $logout_href = '../actions/logout.php';
}
?>

<nav class="top-nav">
    <div class="container-fluid">
        <div class="row align-items-center">
            <!-- Brand / Logo -->
            <div class="col-md-2">
                <a href="#" class="brand">
                    <i class="bi bi-shop"></i> POS System
                </a>
            </div>
            
            <!-- Module Links - ROLES REMOVED -->
            <div class="col-md-8">
                <ul class="nav">
                    <!-- Dashboard (Admin only) -->
                    <?php if($role == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" 
                           href="<?php echo $dashboard_href; ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- HR Module -->
                    <?php if($role == 'hr' || $role == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_dir == 'hr' ? 'active' : ''; ?>" 
                           href="<?php echo $hr_href; ?>">
                            <i class="bi bi-people"></i> HR
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Finance Module -->
                    <?php if($role == 'finance' || $role == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_dir == 'finance' ? 'active' : ''; ?>" 
                           href="<?php echo $finance_href; ?>">
                            <i class="bi bi-wallet2"></i> Finance
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Supply Module -->
                    <?php if($role == 'supply' || $role == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_dir == 'supply' ? 'active' : ''; ?>" 
                           href="<?php echo $supply_href; ?>">
                            <i class="bi bi-boxes"></i> Supply
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Cashier Module -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_dir == 'cashier' ? 'active' : ''; ?>" 
                           href="<?php echo $cashier_href; ?>">
                            <i class="bi bi-cash"></i> Cashier
                        </a>
                    </li>
                    
                    <!-- ========================================== -->
                    <!-- ROLES & USER ROLES - REMOVED              -->
                    <!-- ========================================== -->
                </ul>
            </div>
            
            <!-- User Info & Logout -->
            <div class="col-md-2 text-end">
                <span class="user-info">
                    <i class="bi bi-person-circle"></i>
                    <?php echo $_SESSION['full_name'] ?? 'User'; ?>
                    <span class="badge bg-<?php 
                        echo $role == 'admin' ? 'danger' : 
                            ($role == 'hr' ? 'success' : 
                                ($role == 'finance' ? 'info' : 
                                    ($role == 'supply' ? 'warning' : 
                                        ($role == 'cashier' ? 'primary' : 'secondary')))); 
                    ?>">
                        <?php echo ucfirst($role); ?>
                    </span>
                </span>
                <a href="<?php echo $logout_href; ?>" class="btn btn-sm btn-danger ms-2">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
<div class="row">