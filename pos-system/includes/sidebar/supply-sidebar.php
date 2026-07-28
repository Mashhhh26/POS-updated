<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="col-md-2 d-md-block sidebar supply-sidebar">
    <div class="position-sticky">
        <h4 class="text-white text-center py-3 brand-title">
            <i class="bi bi-boxes"></i> Supply Chain
        </h4>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="../../pages/dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'requests.php' ? 'active' : ''; ?>" href="../modules/supplychain/requests.php">
                    <i class="bi bi-box-seam"></i> Supply Requests
                </a>
            </li>
            
            <?php if(isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'ceo')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'approval.php' ? 'active' : ''; ?>" href="../modules/supplychain/approval.php">
                    <i class="bi bi-check2-circle"></i> Approvals
                </a>
            </li>
            <?php endif; ?>
            
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
                    <span class="badge bg-warning">Supply</span>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.supply-sidebar .nav-link.active { background: #ffc107 !important; color: #212529 !important; }
.supply-sidebar .nav-link.active:hover { background: #e0a800 !important; }
.supply-sidebar .brand-title { color: #ffc107; }
</style>