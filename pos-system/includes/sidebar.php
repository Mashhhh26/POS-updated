<?php
// ============================================
// SIDEBAR NAVIGATION - UPDATED
// ============================================
$current_page = basename($_SERVER['PHP_SELF']);

// ============================================
// HAS ROLE FUNCTION - FALLBACK
// ============================================
if (!function_exists('hasRole')) {
    function hasRole($role_name) {
        if (function_exists('getUserRoles')) {
            $user_roles = getUserRoles($_SESSION['user_id'] ?? 0);
            foreach ($user_roles as $r) {
                if ($r['role_name'] == $role_name) {
                    return true;
                }
            }
            return false;
        }
        if (isset($_SESSION['role'])) {
            return $_SESSION['role'] == $role_name;
        }
        return false;
    }
}

// Get user's roles if RBAC available
$user_roles = [];
if (function_exists('getUserRoles')) {
    $user_roles = getUserRoles($_SESSION['user_id'] ?? 0);
}

// Determine user type
$is_admin = isAdmin() || hasRole('admin') || hasRole('super_admin');
$is_hr = hasRole('hr');
$is_finance = hasRole('finance');
$is_supply = hasRole('supply') || hasRole('ceo');

// Determine module access
$show_pos = !$is_hr && !$is_finance && !$is_supply;
$show_summary = !$is_hr && !$is_finance && !$is_supply;
$show_products = !$is_hr && !$is_finance && !$is_supply && isAdminOrCoAdmin();
$show_receipts = !$is_hr && !$is_finance && !$is_supply;
$show_hr = $is_hr || $is_admin;
$show_finance = $is_finance || $is_admin;
$show_supply = $is_supply || $is_admin;
$show_system = $is_admin;
?>

<nav class="col-md-2 d-md-block sidebar">
    <div class="position-sticky">
        <!-- Brand -->
        <h4 class="text-white text-center py-3 brand-title">
            <i class="bi bi-shop"></i> POS System
        </h4>
        
        <ul class="nav flex-column">
            <!-- ========================================== -->
            <!-- DASHBOARD - All users                      -->
            <!-- ========================================== -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> 
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- ========================================== -->
            <!-- POS MODULE - All except HR, Finance, Supply -->
            <!-- ========================================== -->
            <?php if($show_pos): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'sales.php' ? 'active' : ''; ?>" href="sales.php">
                    <i class="bi bi-cart"></i> 
                    <span>Sales</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'cart.php' ? 'active' : ''; ?>" href="cart.php">
                    <i class="bi bi-cart-check"></i> 
                    <span>Cart</span>
                    <span class="badge bg-danger ms-auto bounce-badge"><?php echo count($_SESSION['cart'] ?? []); ?></span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- ========================================== -->
            <!-- SUMMARY - All except HR, Finance, Supply   -->
            <!-- ========================================== -->
            <?php if($show_summary): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'summary.php' ? 'active' : ''; ?>" href="summary.php">
                    <i class="bi bi-graph-up"></i> 
                    <span>Summary</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- ========================================== -->
            <!-- PRODUCTS - Admin/Co-Admin only             -->
            <!-- ========================================== -->
            <?php if($show_products): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>" href="products.php">
                    <i class="bi bi-box"></i> 
                    <span>Products</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- ========================================== -->
            <!-- RECEIPTS - All except HR, Finance, Supply  -->
            <!-- ========================================== -->
            <?php if($show_receipts): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'receipts.php' ? 'active' : ''; ?>" href="receipts.php">
                    <i class="bi bi-receipt"></i> 
                    <span>Receipts</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- ========================================== -->
            <!-- HR MODULE - HR and Admin only              -->
            <!-- HR DASHBOARD REMOVED - Direct links only   -->
            <!-- ========================================== -->
            <?php if($show_hr): ?>
            <li class="nav-item">
                <a class="nav-link has-dropdown <?php echo in_array($current_page, ['employees.php', 'attendance.php', 'leave-requests.php']) ? 'active' : ''; ?>" 
                   href="#hrMenu" data-bs-toggle="collapse" aria-expanded="false">
                    <i class="bi bi-people"></i> 
                    <span>HR Module</span>
                    <i class="bi bi-chevron-down float-end dropdown-icon"></i>
                </a>
                <ul class="nav flex-column collapse <?php echo in_array($current_page, ['employees.php', 'attendance.php', 'leave-requests.php']) ? 'show' : ''; ?>" id="hrMenu">
                    <li class="nav-item">
                        <a class="nav-link ms-3 <?php echo $current_page == 'employees.php' ? 'active' : ''; ?>" href="../modules/hr/employees.php">
                            <i class="bi bi-person"></i> 
                            <span>Employees</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ms-3 <?php echo $current_page == 'attendance.php' ? 'active' : ''; ?>" href="../modules/hr/attendance.php">
                            <i class="bi bi-clock"></i> 
                            <span>Attendance</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ms-3 <?php echo $current_page == 'leave-requests.php' ? 'active' : ''; ?>" href="../modules/hr/leave-requests.php">
                            <i class="bi bi-calendar-check"></i> 
                            <span>Leave Requests</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- ========================================== -->
            <!-- FINANCE MODULE - Finance and Admin only    -->
            <!-- ========================================== -->
            <?php if($show_finance): ?>
            <li class="nav-item">
                <a class="nav-link has-dropdown <?php echo in_array($current_page, ['payroll.php', 'finance-requests.php']) ? 'active' : ''; ?>" 
                   href="#financeMenu" data-bs-toggle="collapse" aria-expanded="false">
                    <i class="bi bi-wallet2"></i> 
                    <span>Finance Module</span>
                    <i class="bi bi-chevron-down float-end dropdown-icon"></i>
                </a>
                <ul class="nav flex-column collapse <?php echo in_array($current_page, ['payroll.php', 'finance-requests.php']) ? 'show' : ''; ?>" id="financeMenu">
                    <li class="nav-item">
                        <a class="nav-link ms-3 <?php echo $current_page == 'payroll.php' ? 'active' : ''; ?>" href="../modules/finance/payroll.php">
                            <i class="bi bi-cash"></i> 
                            <span>Payroll</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ms-3 <?php echo $current_page == 'finance-requests.php' ? 'active' : ''; ?>" href="../modules/finance/finance-requests.php">
                            <i class="bi bi-cash-stack"></i> 
                            <span>Finance Requests</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- ========================================== -->
            <!-- SUPPLY CHAIN - Supply, CEO, Admin only     -->
            <!-- ========================================== -->
            <?php if($show_supply): ?>
            <li class="nav-item">
                <a class="nav-link has-dropdown <?php echo in_array($current_page, ['requests.php', 'approval.php']) ? 'active' : ''; ?>" 
                   href="#supplyMenu" data-bs-toggle="collapse" aria-expanded="false">
                    <i class="bi bi-boxes"></i> 
                    <span>Supply Chain</span>
                    <i class="bi bi-chevron-down float-end dropdown-icon"></i>
                </a>
                <ul class="nav flex-column collapse <?php echo in_array($current_page, ['requests.php', 'approval.php']) ? 'show' : ''; ?>" id="supplyMenu">
                    <li class="nav-item">
                        <a class="nav-link ms-3 <?php echo $current_page == 'requests.php' ? 'active' : ''; ?>" href="../modules/supplychain/requests.php">
                            <i class="bi bi-box-seam"></i> 
                            <span>Supply Requests</span>
                        </a>
                    </li>
                    <?php if(hasRole('ceo') || $is_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link ms-3 <?php echo $current_page == 'approval.php' ? 'active' : ''; ?>" href="../modules/supplychain/approval.php">
                            <i class="bi bi-check2-circle"></i> 
                            <span>Approvals</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- ========================================== -->
            <!-- SYSTEM ADMIN - Admin only                  -->
            <!-- ========================================== -->
            <?php if($show_system): ?>
            <li class="nav-item">
                <a class="nav-link has-dropdown <?php echo in_array($current_page, ['roles.php', 'user-roles.php', 'users.php']) ? 'active' : ''; ?>" 
                   href="#systemMenu" data-bs-toggle="collapse" aria-expanded="false">
                    <i class="bi bi-gear"></i> 
                    <span>System</span>
                    <i class="bi bi-chevron-down float-end dropdown-icon"></i>
                </a>
                <ul class="nav flex-column collapse <?php echo in_array($current_page, ['roles.php', 'user-roles.php', 'users.php']) ? 'show' : ''; ?>" id="systemMenu">
                    <li class="nav-item">
                        <a class="nav-link ms-3 <?php echo $current_page == 'roles.php' ? 'active' : ''; ?>" href="roles.php">
                            <i class="bi bi-shield-lock"></i> 
                            <span>Roles</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ms-3 <?php echo $current_page == 'user-roles.php' ? 'active' : ''; ?>" href="user-roles.php">
                            <i class="bi bi-person-badge"></i> 
                            <span>User Roles</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ms-3 <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                            <i class="bi bi-people"></i> 
                            <span>Users</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- ========================================== -->
            <!-- LOGOUT - All users                        -->
            <!-- ========================================== -->
            <li class="nav-item mt-2">
                <a class="nav-link logout-link" href="../actions/logout-action.php">
                    <i class="bi bi-box-arrow-right"></i> 
                    <span>Logout</span>
                </a>
            </li>
        </ul>
        
        <!-- ========================================== -->
        <!-- USER INFO                                   -->
        <!-- ========================================== -->
        <div class="mt-4 p-3 text-white user-info" style="border-top: 1px solid #34495e;">
            <div class="d-flex align-items-center">
                <div class="avatar">
                    <i class="bi bi-person-circle" style="font-size: 30px;"></i>
                </div>
                <div class="ms-2">
                    <div class="fw-bold"><?php echo $_SESSION['full_name'] ?? 'User'; ?></div>
                    <div>
                        <span class="badge bg-<?php 
                            $role = $_SESSION['role'] ?? 'staff';
                            echo $role == 'admin' ? 'danger' : 
                                ($role == 'co-admin' ? 'warning' : 
                                    ($is_hr ? 'success' : 
                                        ($is_finance ? 'info' : 
                                            ($is_supply ? 'primary' : 'secondary')))); 
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
            <?php if(!empty($user_roles)): ?>
            <div class="mt-2">
                <small class="text-muted">
                    Roles: 
                    <?php foreach($user_roles as $r): ?>
                        <span class="badge bg-<?php 
                            echo $r['role_name'] == 'super_admin' ? 'danger' : 
                                ($r['role_name'] == 'admin' ? 'warning' : 
                                    ($r['role_name'] == 'ceo' ? 'primary' : 
                                        ($r['role_name'] == 'finance' ? 'info' : 
                                            ($r['role_name'] == 'hr' ? 'success' : 'secondary')))); 
                        ?>">
                            <?php echo $r['role_name']; ?>
                        </span>
                    <?php endforeach; ?>
                </small>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ========================================== -->
<!-- SIDEBAR STYLES                            -->
<!-- ========================================== -->
<style>
.brand-title {
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.sidebar .nav-link {
    position: relative;
    transition: all 0.3s ease;
    padding: 10px 15px;
    border-radius: 8px;
    margin: 2px 0;
    color: #ecf0f1;
}
.sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
    color: white;
}
.sidebar .nav-link.active {
    background: #3498db;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
    transform: translateX(5px);
    color: white;
}
.sidebar .nav-link.active:hover {
    background: #2980b9;
    transform: translateX(8px);
}

.dropdown-icon {
    transition: transform 0.3s ease;
}
.nav-link[aria-expanded="true"] .dropdown-icon {
    transform: rotate(180deg);
}

.collapse {
    transition: all 0.3s ease;
}
.collapse.show {
    animation: slideDown 0.3s ease forwards;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.sidebar .nav-link.ms-3 {
    padding-left: 35px !important;
    font-size: 14px;
}
.sidebar .nav-link.ms-3:hover {
    transform: translateX(8px);
}

.bounce-badge {
    animation: bounce 1s ease-in-out infinite;
}
@keyframes bounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.user-info {
    animation: fadeInUp 0.5s ease;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.logout-link {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 15px !important;
    margin-top: 5px;
}
.logout-link:hover {
    background: rgba(231, 76, 60, 0.2) !important;
    color: #e74c3c !important;
}

.sidebar .nav-link i {
    transition: all 0.3s ease;
    width: 20px;
    text-align: center;
}
.sidebar .nav-link:hover i {
    transform: scale(1.1);
}

.sidebar .nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 70%;
    background: white;
    border-radius: 0 4px 4px 0;
    animation: slideInLeft 0.3s ease;
}
@keyframes slideInLeft {
    from { height: 0; }
    to { height: 70%; }
}

@media (max-width: 768px) {
    .sidebar .nav-link {
        padding: 8px 12px;
        font-size: 14px;
    }
    .brand-title {
        font-size: 18px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const activeDropdown = document.querySelector('.nav-link.active');
    if (activeDropdown) {
        const parentCollapse = activeDropdown.closest('.collapse');
        if (parentCollapse) {
            parentCollapse.classList.add('show');
            const toggleBtn = parentCollapse.previousElementSibling;
            if (toggleBtn) {
                toggleBtn.setAttribute('aria-expanded', 'true');
            }
        }
    }
});
</script>