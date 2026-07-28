<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="col-md-2 d-md-block sidebar hr-sidebar">
    <div class="position-sticky">
        <h4 class="text-white text-center py-3 brand-title">
            <i class="bi bi-people"></i> HR Module
        </h4>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="../../pages/dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'employees.php' ? 'active' : ''; ?>" href="../modules/hr/employees.php">
                    <i class="bi bi-person"></i> Employees
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'attendance.php' ? 'active' : ''; ?>" href="../modules/hr/attendance.php">
                    <i class="bi bi-clock"></i> Attendance
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'leave-requests.php' ? 'active' : ''; ?>" href="../modules/hr/leave-requests.php">
                    <i class="bi bi-calendar-check"></i> Leave Requests
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
                    <span class="badge bg-success">HR</span>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.hr-sidebar .nav-link.active { background: #28a745 !important; }
.hr-sidebar .nav-link.active:hover { background: #1e7e34 !important; }
.hr-sidebar .brand-title { color: #28a745; }
</style>