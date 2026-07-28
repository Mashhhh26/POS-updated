<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="col-md-2 d-md-block sidebar finance-sidebar">
    <div class="position-sticky">
        <h4 class="text-white text-center py-3 brand-title">
            <i class="bi bi-wallet2"></i> Finance Module
        </h4>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="../../pages/dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'payroll.php' ? 'active' : ''; ?>" href="../modules/finance/payroll.php">
                    <i class="bi bi-cash"></i> Payroll
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'finance-requests.php' ? 'active' : ''; ?>" href="../modules/finance/finance-requests.php">
                    <i class="bi bi-cash-stack"></i> Finance Requests
                </a>
            </li>
            
            <li class="nav-item mt-2">
                <a class="nav-link logout-link" href="../../actions/logout-action.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
        
        <div class="mt-4 p-3 text-white user-info" style="border-top: 1px solid #34495e;">
            <div class="d-flex align-items-center">
                <i class="bi bi-person-circle" style="font-size: 30px;"></i>
                <div class="ms-2">
                    <div class="fw-bold"><?php echo $_SESSION['full_name'] ?? 'User'; ?></div>
                    <span class="badge bg-info">Finance</span>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.finance-sidebar .nav-link.active { background: #17a2b8 !important; }
.finance-sidebar .nav-link.active:hover { background: #0f7c8e !important; }
.finance-sidebar .brand-title { color: #17a2b8; }
</style>