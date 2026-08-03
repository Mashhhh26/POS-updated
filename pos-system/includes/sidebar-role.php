<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="col-md-2 d-md-block sidebar">
    <div class="position-sticky">
        <h4 class="text-white text-center py-3">
            <i class="bi bi-shield-lock"></i> System Admin
        </h4>
        <hr class="text-white">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'roles.php' ? 'active' : ''; ?>" href="roles.php">
                    <i class="bi bi-shield-lock"></i> Roles
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'user-roles.php' ? 'active' : ''; ?>" href="user-roles.php">
                    <i class="bi bi-person-badge"></i> User Roles
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="dashboard.php">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="../actions/logout.php">
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
    .sidebar .nav-link.active { background: #3498db !important; }
    .sidebar .nav-link.active:hover { background: #2980b9 !important; }
</style>