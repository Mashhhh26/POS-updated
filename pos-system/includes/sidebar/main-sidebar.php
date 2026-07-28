<?php
$current_page = basename($_SERVER['PHP_SELF']);

// ============================================
// DETERMINE USER TYPE - FIXED!
// ============================================
$is_hr = false;
$is_finance = false;
$is_supply = false;

if (function_exists('isHR')) {
    $is_hr = isHR();
}
if (function_exists('isFinance')) {
    $is_finance = isFinance();
}
if (function_exists('isSupply')) {
    $is_supply = isSupply();
}

// Fallback: Check session role directly
if (!$is_hr && isset($_SESSION['role']) && $_SESSION['role'] == 'hr') {
    $is_hr = true;
}
if (!$is_finance && isset($_SESSION['role']) && $_SESSION['role'] == 'finance') {
    $is_finance = true;
}
if (!$is_supply && isset($_SESSION['role']) && ($_SESSION['role'] == 'supply' || $_SESSION['role'] == 'ceo')) {
    $is_supply = true;
}
?>

<nav class="col-md-2 d-md-block sidebar">
    <div class="position-sticky">
        <h4 class="text-white text-center py-3 brand-title">
            <i class="bi bi-shop"></i> POS System
        </h4>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <?php if(!$is_hr && !$is_finance && !$is_supply): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'sales.php' ? 'active' : ''; ?>" href="sales.php">
                    <i class="bi bi-cart"></i> Sales
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'cart.php' ? 'active' : ''; ?>" href="cart.php">
                    <i class="bi bi-cart-check"></i> Cart
                    <span class="badge bg-danger ms-auto"><?php echo count($_SESSION['cart'] ?? []); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'summary.php' ? 'active' : ''; ?>" href="summary.php">
                    <i class="bi bi-graph-up"></i> Summary
                </a>
            </li>
            <?php endif; ?>
            
            <?php if(isAdminOrCoAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>" href="products.php">
                    <i class="bi bi-box"></i> Products
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'receipts.php' ? 'active' : ''; ?>" href="receipts.php">
                    <i class="bi bi-receipt"></i> Receipts
                </a>
            </li>
            
            <?php if(isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'roles.php' ? 'active' : ''; ?>" href="roles.php">
                    <i class="bi bi-shield-lock"></i> Roles
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item mt-2">
                <a class="nav-link logout-link" href="../actions/logout-action.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
        
        <div class="mt-4 p-3 text-white user-info" style="border-top: 1px solid #34495e;">
            <div class="d-flex align-items-center">
                <i class="bi bi-person-circle" style="font-size: 30px;"></i>
                <div class="ms-2">
                    <div class="fw-bold"><?php echo $_SESSION['full_name'] ?? 'User'; ?></div>
                    <span class="badge bg-<?php 
                        $role = $_SESSION['role'] ?? 'staff';
                        echo $role == 'admin' ? 'danger' : 
                            ($role == 'co-admin' ? 'warning' : 
                                ($is_hr ? 'success' : 
                                    ($is_finance ? 'info' : 
                                        ($is_supply ? 'warning' : 'secondary')))); 
                    ?>">
                        <?php 
                        if ($is_hr) echo 'HR';
                        elseif ($is_finance) echo 'Finance';
                        elseif ($is_supply) echo 'Supply';
                        else echo ucfirst($role ?? 'Staff'); 
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.brand-title { animation: pulse 2s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
.sidebar .nav-link {
    padding: 10px 15px;
    border-radius: 8px;
    margin: 2px 0;
    color: #ecf0f1;
    transition: all 0.3s ease;
}
.sidebar .nav-link:hover {
    background: rgba(255,255,255,0.1);
    transform: translateX(5px);
    color: white;
}
.sidebar .nav-link.active {
    background: #3498db;
    box-shadow: 0 4px 15px rgba(52,152,219,0.4);
    transform: translateX(5px);
    color: white;
}
.logout-link {
    border-top: 1px solid rgba(255,255,255,0.1);
    padding-top: 15px !important;
    margin-top: 5px;
}
.logout-link:hover {
    background: rgba(231,76,60,0.2) !important;
    color: #e74c3c !important;
}
.user-info { animation: fadeInUp 0.5s ease; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@media (max-width: 768px) { .sidebar .nav-link { padding: 8px 12px; font-size: 14px; } }
</style>