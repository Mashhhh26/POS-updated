<?php
$role = $_SESSION['role'] ?? 'staff';
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['SCRIPT_FILENAME']));

// ============================================
// DETERMINE BASE PATHS
// ============================================
$is_in_module = in_array($current_dir, ['hr', 'finance', 'supply', 'cashier']);

if ($is_in_module) {
    $dashboard_link = '../../pages/dashboard.php';
    $logout_link = '../../actions/logout.php';
} else {
    $dashboard_link = 'dashboard.php';
    $logout_link = '../actions/logout.php';
}

// ============================================
// IF ADMIN IS IN A MODULE, SHOW THAT MODULE'S SIDEBAR
// ============================================
if ($role == 'admin' && $is_in_module) {
    if ($current_dir == 'hr') {
        $role = 'hr';
    } elseif ($current_dir == 'finance') {
        $role = 'finance';
    } elseif ($current_dir == 'supply') {
        $role = 'supply';
    } elseif ($current_dir == 'cashier') {
        $role = 'cashier';
    }
}

// ============================================
// FINANCE SIDEBAR
// ============================================
if ($role == 'finance') {
    ?>
    <nav class="col-md-2 d-md-block sidebar finance-sidebar">
        <div class="position-sticky">
            <h4 class="text-white text-center py-3">
                <i class="bi bi-wallet2"></i> Finance Module
            </h4>
            <hr class="text-white">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'payroll.php' ? 'active' : ''; ?>" href="payroll.php">
                        <i class="bi bi-cash"></i> Payroll
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'requests.php' ? 'active' : ''; ?>" href="requests.php">
                        <i class="bi bi-cash-stack"></i> Finance Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'approvals.php' ? 'active' : ''; ?>" href="approvals.php">
                        <i class="bi bi-check2-circle"></i> Supply Approvals
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link text-danger" href="<?php echo $logout_link; ?>">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
            <hr class="text-white">
            <div class="text-white text-center small">
                <i class="bi bi-person-circle"></i><br>
                <?php echo $_SESSION['full_name'] ?? 'User'; ?>
                <br><span class="badge bg-info">Finance</span>
            </div>
        </div>
    </nav>
    <style>
        .finance-sidebar .nav-link.active { background: #17a2b8 !important; }
        .finance-sidebar .nav-link.active:hover { background: #0f7c8e !important; }
    </style>
    <?php
    return;
}

// ============================================
// HR SIDEBAR
// ============================================
if ($role == 'hr') {
    ?>
    <nav class="col-md-2 d-md-block sidebar hr-sidebar">
        <div class="position-sticky">
            <h4 class="text-white text-center py-3">
                <i class="bi bi-people"></i> HR Module
            </h4>
            <hr class="text-white">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'employees.php' ? 'active' : ''; ?>" href="employees.php">
                        <i class="bi bi-person"></i> Employees
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'attendance.php' ? 'active' : ''; ?>" href="attendance.php">
                        <i class="bi bi-clock"></i> Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'leave-requests.php' ? 'active' : ''; ?>" href="leave-requests.php">
                        <i class="bi bi-calendar-check"></i> Leave Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                        <i class="bi bi-people"></i> User Management
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link text-danger" href="<?php echo $logout_link; ?>">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
            <hr class="text-white">
            <div class="text-white text-center small">
                <i class="bi bi-person-circle"></i><br>
                <?php echo $_SESSION['full_name'] ?? 'User'; ?>
                <br><span class="badge bg-success">HR</span>
            </div>
        </div>
    </nav>
    <style>
        .hr-sidebar .nav-link.active { background: #28a745 !important; }
        .hr-sidebar .nav-link.active:hover { background: #1e7e34 !important; }
    </style>
    <?php
    return;
}

// ============================================
// SUPPLY SIDEBAR
// ============================================
if ($role == 'supply') {
    ?>
    <nav class="col-md-2 d-md-block sidebar supply-sidebar">
        <div class="position-sticky">
            <h4 class="text-white text-center py-3">
                <i class="bi bi-boxes"></i> Supply Chain
            </h4>
            <hr class="text-white">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>" href="products.php">
                        <i class="bi bi-box"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'requests.php' ? 'active' : ''; ?>" href="requests.php">
                        <i class="bi bi-box-seam"></i> Supply Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'approvals.php' ? 'active' : ''; ?>" href="approvals.php">
                        <i class="bi bi-check2-circle"></i> Approvals
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link text-danger" href="<?php echo $logout_link; ?>">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
            <hr class="text-white">
            <div class="text-white text-center small">
                <i class="bi bi-person-circle"></i><br>
                <?php echo $_SESSION['full_name'] ?? 'User'; ?>
                <br><span class="badge bg-warning text-dark">Supply</span>
            </div>
        </div>
    </nav>
    <style>
        .supply-sidebar .nav-link.active { background: #ffc107 !important; color: #212529 !important; }
        .supply-sidebar .nav-link.active:hover { background: #e0a800 !important; }
    </style>
    <?php
    return;
}

// ============================================
// CASHIER SIDEBAR
// ============================================
if ($role == 'cashier') {
    ?>
    <nav class="col-md-2 d-md-block sidebar cashier-sidebar">
        <div class="position-sticky">
            <h4 class="text-white text-center py-3">
                <i class="bi bi-cash"></i> Cashier
            </h4>
            <hr class="text-white">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'sales.php' ? 'active' : ''; ?>" href="sales.php">
                        <i class="bi bi-cart"></i> Sales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'cart.php' ? 'active' : ''; ?>" href="cart.php">
                        <i class="bi bi-cart-check"></i> Cart
                        <span class="badge bg-danger"><?php echo count($_SESSION['cart'] ?? []); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'receipts.php' ? 'active' : ''; ?>" href="receipts.php">
                        <i class="bi bi-receipt"></i> Receipts
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link text-danger" href="<?php echo $logout_link; ?>">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
            <hr class="text-white">
            <div class="text-white text-center small">
                <i class="bi bi-person-circle"></i><br>
                <?php echo $_SESSION['full_name'] ?? 'User'; ?>
                <br><span class="badge bg-primary">Cashier</span>
            </div>
        </div>
    </nav>
    <style>
        .cashier-sidebar .nav-link.active { background: #0d6efd !important; }
        .cashier-sidebar .nav-link.active:hover { background: #0a58ca !important; }
    </style>
    <?php
    return;
}
// ============================================
// ADMIN SIDEBAR - SIMPLE, NO ANIMATIONS
// ============================================
if ($role == 'admin') {
    ?>
    <nav class="col-md-2 d-md-block sidebar admin-sidebar">
        <div class="position-sticky">
            <h4 class="text-white text-center py-3">
                <i class="bi bi-shield-lock"></i> Admin Panel
            </h4>
            <hr class="text-white">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo $dashboard_link; ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_dir == 'hr' ? 'active' : ''; ?>" href="../modules/hr/">
                        <i class="bi bi-people"></i> HR Module
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_dir == 'finance' ? 'active' : ''; ?>" href="../modules/finance/">
                        <i class="bi bi-wallet2"></i> Finance Module
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_dir == 'supply' ? 'active' : ''; ?>" href="../modules/supply/">
                        <i class="bi bi-boxes"></i> Supply Module
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_dir == 'cashier' ? 'active' : ''; ?>" href="../modules/cashier/">
                        <i class="bi bi-cash"></i> Cashier Module
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="../modules/hr/users.php">
                        <i class="bi bi-people"></i> User Management
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link text-danger" href="<?php echo $logout_link; ?>">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
            <hr class="text-white">
            <div class="text-white text-center small">
                <i class="bi bi-person-circle"></i><br>
                <?php echo $_SESSION['full_name'] ?? 'User'; ?>
                <br><span class="badge bg-danger">Admin</span>
            </div>
        </div>
    </nav>
    <style>
        .admin-sidebar .nav-link.active { background: #3498db !important; }
        .admin-sidebar .nav-link.active:hover { background: #2980b9 !important; }
    </style>
    <?php
    return;
}

// ============================================
// DEFAULT SIDEBAR
// ============================================
?>
<nav class="col-md-2 d-md-block sidebar">
    <div class="position-sticky">
        <h4 class="text-white text-center py-3">
            <i class="bi bi-shop"></i> POS System
        </h4>
        <hr class="text-white">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo $dashboard_link; ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'sales.php' ? 'active' : ''; ?>" href="sales.php">
                    <i class="bi bi-cart"></i> Sales
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'cart.php' ? 'active' : ''; ?>" href="cart.php">
                    <i class="bi bi-cart-check"></i> Cart
                    <span class="badge bg-danger"><?php echo count($_SESSION['cart'] ?? []); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>" href="products.php">
                    <i class="bi bi-box"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'receipts.php' ? 'active' : ''; ?>" href="receipts.php">
                    <i class="bi bi-receipt"></i> Receipts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="<?php echo $logout_link; ?>">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
        <hr class="text-white">
        <div class="text-white text-center small">
            <i class="bi bi-person-circle"></i><br>
            <?php echo $_SESSION['full_name'] ?? 'User'; ?>
            <br><span class="badge bg-secondary"><?php echo ucfirst($role); ?></span>
        </div>
    </div>
</nav>
<style>
    .sidebar .nav-link.active { background: #3498db !important; }
    .sidebar .nav-link.active:hover { background: #2980b9 !important; }
</style>