<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// If user is not admin, redirect to their module
$role = $_SESSION['role'] ?? 'staff';
if ($role == 'hr') {
    redirect('../modules/hr/index.php');
    exit();
} elseif ($role == 'finance') {
    redirect('../modules/finance/index.php');
    exit();
} elseif ($role == 'supply') {
    redirect('../modules/supply/index.php');
    exit();
} elseif ($role == 'cashier') {
    redirect('../modules/cashier/index.php');
    exit();
}

// For Admin only
require_once '../includes/header.php';
?>

<main class="col-md-12 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Admin Dashboard</h1>
        <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
    </div>

    <?php if(isset($_SESSION['swal']) && !empty($_SESSION['swal'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?php echo $_SESSION['swal']['type']; ?>',
                    title: '<?php echo $_SESSION['swal']['title']; ?>',
                    text: '<?php echo addslashes($_SESSION['swal']['text']); ?>',
                    timer: 4000,
                    showConfirmButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Got it',
                    timerProgressBar: true
                });
            });
        </script>
        <?php unset($_SESSION['swal']); ?>
    <?php endif; ?>

    <!-- Module Cards -->
    <div class="row">
        <!-- HR Module Card -->
        <div class="col-md-3 mb-3">
            <a href="../modules/hr/" class="text-decoration-none">
                <div class="card text-center shadow-sm" style="border-top: 4px solid #28a745;">
                    <div class="card-body py-4">
                        <i class="bi bi-people" style="font-size: 50px; color: #28a745;"></i>
                        <h5 class="mt-3">HR Module</h5>
                        <small class="text-muted">Manage employees, attendance, leaves</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Finance Module Card -->
        <div class="col-md-3 mb-3">
            <a href="../modules/finance/" class="text-decoration-none">
                <div class="card text-center shadow-sm" style="border-top: 4px solid #17a2b8;">
                    <div class="card-body py-4">
                        <i class="bi bi-wallet2" style="font-size: 50px; color: #17a2b8;"></i>
                        <h5 class="mt-3">Finance Module</h5>
                        <small class="text-muted">Manage payroll, finance requests</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Supply Module Card -->
        <div class="col-md-3 mb-3">
            <a href="../modules/supply/" class="text-decoration-none">
                <div class="card text-center shadow-sm" style="border-top: 4px solid #ffc107;">
                    <div class="card-body py-4">
                        <i class="bi bi-boxes" style="font-size: 50px; color: #ffc107;"></i>
                        <h5 class="mt-3">Supply Module</h5>
                        <small class="text-muted">Manage supply requests, approvals</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cashier Module Card -->
        <div class="col-md-3 mb-3">
            <a href="../modules/cashier/" class="text-decoration-none">
                <div class="card text-center shadow-sm" style="border-top: 4px solid #0d6efd;">
                    <div class="card-body py-4">
                        <i class="bi bi-cash" style="font-size: 50px; color: #0d6efd;"></i>
                        <h5 class="mt-3">Cashier Module</h5>
                        <small class="text-muted">Point of Sale, receipts</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SYSTEM MANAGEMENT CARDS                    -->
    <!-- ========================================== -->
    <div class="row mt-3">
        <div class="col-12">
            <h5 class="mb-3"><i class="bi bi-shield-lock"></i> System Management</h5>
        </div>
        
        <!-- Roles Card -->
        <div class="col-md-3 mb-3">
            <a href="roles.php" class="text-decoration-none">
                <div class="card text-center shadow-sm" style="border-top: 4px solid #6f42c1;">
                    <div class="card-body py-3">
                        <i class="bi bi-shield-lock" style="font-size: 40px; color: #6f42c1;"></i>
                        <h6 class="mt-2">Role Management</h6>
                        <small class="text-muted">Create, edit, delete roles</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- User Roles Card -->
        <div class="col-md-3 mb-3">
            <a href="user-roles.php" class="text-decoration-none">
                <div class="card text-center shadow-sm" style="border-top: 4px solid #20c997;">
                    <div class="card-body py-3">
                        <i class="bi bi-person-badge" style="font-size: 40px; color: #20c997;"></i>
                        <h6 class="mt-2">User Roles</h6>
                        <small class="text-muted">Assign roles to users</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- User Management Card -->
        <div class="col-md-3 mb-3">
            <a href="../modules/hr/users.php" class="text-decoration-none">
                <div class="card text-center shadow-sm" style="border-top: 4px solid #dc3545;">
                    <div class="card-body py-3">
                        <i class="bi bi-people" style="font-size: 40px; color: #dc3545;"></i>
                        <h6 class="mt-2">User Management</h6>
                        <small class="text-muted">Add, edit, delete users</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Reports Card -->
        <div class="col-md-3 mb-3">
            <a href="reports.php" class="text-decoration-none">
                <div class="card text-center shadow-sm" style="border-top: 4px solid #fd7e14;">
                    <div class="card-body py-3">
                        <i class="bi bi-file-text" style="font-size: 40px; color: #fd7e14;"></i>
                        <h6 class="mt-2">Reports</h6>
                        <small class="text-muted">View system reports</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>System Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $total_employees = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
                        $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
                        $total_sales = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
                        $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                        $total_roles = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
                        $total_permissions = $pdo->query("SELECT COUNT(*) FROM permissions")->fetchColumn();
                        ?>
                        <div class="col-md-2 text-center">
                            <h3><?php echo $total_employees; ?></h3>
                            <small class="text-muted">Employees</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <h3><?php echo $total_products; ?></h3>
                            <small class="text-muted">Products</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <h3><?php echo $total_sales; ?></h3>
                            <small class="text-muted">Sales</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <h3><?php echo $total_users; ?></h3>
                            <small class="text-muted">Users</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <h3><?php echo $total_roles; ?></h3>
                            <small class="text-muted">Roles</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <h3><?php echo $total_permissions; ?></h3>
                            <small class="text-muted">Permissions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>