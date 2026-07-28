<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Load RBAC if exists
$rbac_file = __DIR__ . '/../includes/rbac/roles.php';
if (file_exists($rbac_file)) {
    require_once $rbac_file;
}

require_once '../includes/header.php';

// Load the correct sidebar based on role
$sidebar_file = getSidebar();
if (file_exists('../' . $sidebar_file)) {
    require_once '../' . $sidebar_file;
} else {
    require_once '../includes/sidebar/main-sidebar.php';
}
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Dashboard</h1>
        <span>
            Welcome, <?php echo $_SESSION['full_name']; ?>
            <span class="badge bg-<?php 
                echo $_SESSION['role'] == 'admin' ? 'danger' : 
                    ($_SESSION['role'] == 'co-admin' ? 'warning' : 
                        (isHR() ? 'success' : 
                            (isFinance() ? 'info' : 
                                (isSupply() ? 'warning' : 'secondary')))); 
            ?>">
                <?php 
                if (isHR()) echo 'HR';
                elseif (isFinance()) echo 'Finance';
                elseif (isSupply()) echo 'Supply';
                else echo ucfirst($_SESSION['role'] ?? 'Staff'); 
                ?>
            </span>
        </span>
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
                    confirmButtonText: 'Got it! 👋',
                    timerProgressBar: true
                });
            });
        </script>
        <?php unset($_SESSION['swal']); ?>
    <?php endif; ?>

    <?php
    // POS Stats
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_sales = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
    $today_sales = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales WHERE DATE(sale_date) = CURDATE()")->fetchColumn();
    $total_revenue = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales")->fetchColumn();
    
    // HR Stats (if HR module exists)
    try {
        $total_employees = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
        $active_employees = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn();
        $pending_leaves = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'")->fetchColumn();
        $today_attendance = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE()")->fetchColumn();
    } catch (PDOException $e) {
        $total_employees = 0;
        $active_employees = 0;
        $pending_leaves = 0;
        $today_attendance = 0;
    }
    
    // Finance Stats (if Finance module exists)
    try {
        $pending_payroll = $pdo->query("SELECT COALESCE(SUM(net_pay), 0) FROM payroll WHERE status = 'pending'")->fetchColumn();
        $pending_finance_requests = $pdo->query("SELECT COUNT(*) FROM finance_requests WHERE status = 'pending'")->fetchColumn();
    } catch (PDOException $e) {
        $pending_payroll = 0;
        $pending_finance_requests = 0;
    }
    
    // Supply Chain Stats (if Supply module exists)
    try {
        $pending_supply_requests = $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'pending'")->fetchColumn();
        $finance_approved = $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'finance_approved'")->fetchColumn();
        $ceo_approved = $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'ceo_approved'")->fetchColumn();
    } catch (PDOException $e) {
        $pending_supply_requests = 0;
        $finance_approved = 0;
        $ceo_approved = 0;
    }
    ?>

    <!-- POS Cards -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #3498db;">
                <div class="card-body">
                    <i class="bi bi-box stat-icon"></i>
                    <h6>Total Products</h6>
                    <h3><?php echo $total_products; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #2ecc71;">
                <div class="card-body">
                    <i class="bi bi-cart-check stat-icon"></i>
                    <h6>Total Sales</h6>
                    <h3><?php echo $total_sales; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #f39c12;">
                <div class="card-body">
                    <i class="bi bi-cash-stack stat-icon"></i>
                    <h6>Today's Sales</h6>
                    <h3><?php echo formatCurrency($today_sales); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #e74c3c;">
                <div class="card-body">
                    <i class="bi bi-graph-up-arrow stat-icon"></i>
                    <h6>Total Revenue</h6>
                    <h3><?php echo formatCurrency($total_revenue); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- HR Stats (Only if HR module exists) -->
    <?php if((isHR() || isAdmin()) && $total_employees > 0): ?>
    <div class="row">
        <div class="col-12">
            <h5 class="mt-3 mb-3"><i class="bi bi-people"></i> HR Overview</h5>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #8e44ad;">
                <div class="card-body">
                    <i class="bi bi-person-badge stat-icon"></i>
                    <h6>Total Employees</h6>
                    <h3><?php echo $total_employees; ?></h3>
                    <small><?php echo $active_employees; ?> active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #e67e22;">
                <div class="card-body">
                    <i class="bi bi-calendar-check stat-icon"></i>
                    <h6>Pending Leaves</h6>
                    <h3><?php echo $pending_leaves; ?></h3>
                    <?php if($pending_leaves > 0): ?>
                        <small class="text-warning">Needs approval</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #2ecc71;">
                <div class="card-body">
                    <i class="bi bi-clock stat-icon"></i>
                    <h6>Today's Attendance</h6>
                    <h3><?php echo $today_attendance; ?></h3>
                    <small>Logged in today</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Finance Stats (Only if Finance module exists) -->
    <?php if((isFinance() || isAdmin()) && ($pending_payroll > 0 || $pending_finance_requests > 0)): ?>
    <div class="row">
        <div class="col-12">
            <h5 class="mt-3 mb-3"><i class="bi bi-wallet2"></i> Finance Overview</h5>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #e74c3c;">
                <div class="card-body">
                    <i class="bi bi-cash stat-icon"></i>
                    <h6>Pending Payroll</h6>
                    <h3><?php echo formatCurrency($pending_payroll); ?></h3>
                    <?php if($pending_payroll > 0): ?>
                        <small class="text-warning">Needs processing</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #f39c12;">
                <div class="card-body">
                    <i class="bi bi-cash-stack stat-icon"></i>
                    <h6>Pending Finance Requests</h6>
                    <h3><?php echo $pending_finance_requests; ?></h3>
                    <?php if($pending_finance_requests > 0): ?>
                        <small class="text-warning">Needs approval</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Supply Chain Stats (Only if Supply module exists) -->
    <?php if((isSupply() || isAdmin()) && ($pending_supply_requests > 0 || $finance_approved > 0 || $ceo_approved > 0)): ?>
    <div class="row">
        <div class="col-12">
            <h5 class="mt-3 mb-3"><i class="bi bi-boxes"></i> Supply Chain Overview</h5>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #e67e22;">
                <div class="card-body">
                    <i class="bi bi-clock-history stat-icon"></i>
                    <h6>Pending Requests</h6>
                    <h3><?php echo $pending_supply_requests; ?></h3>
                    <?php if($pending_supply_requests > 0): ?>
                        <small class="text-warning">Needs Finance approval</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #3498db;">
                <div class="card-body">
                    <i class="bi bi-check-circle stat-icon"></i>
                    <h6>Finance Approved</h6>
                    <h3><?php echo $finance_approved; ?></h3>
                    <?php if($finance_approved > 0): ?>
                        <small class="text-info">Awaiting CEO approval</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #2ecc71;">
                <div class="card-body">
                    <i class="bi bi-check2-all stat-icon"></i>
                    <h6>CEO Approved</h6>
                    <h3><?php echo $ceo_approved; ?></h3>
                    <?php if($ceo_approved > 0): ?>
                        <small class="text-success">Ready for ordering</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Transactions -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="bi bi-clock-history"></i> Recent Transactions</h5>
            <a href="receipts.php" class="btn btn-sm btn-primary">View All Receipts</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent = $pdo->query("SELECT * FROM sales ORDER BY sale_date DESC LIMIT 10");
                        if($recent->rowCount() > 0):
                        while($row = $recent->fetch()):
                        ?>
                        <tr>
                            <td><strong><?php echo $row['invoice_number']; ?></strong></td>
                            <td><?php echo formatCurrency($row['grand_total']); ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($row['sale_date'])); ?></td>
                            <td>
                                <a href="view-receipt.php?id=<?php echo $row['id']; ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="view-receipt.php?id=<?php echo $row['id']; ?>&print=1" 
                                   target="_blank" 
                                   class="btn btn-sm btn-success">
                                    <i class="bi bi-printer"></i> Print
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No transactions yet</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</main>

<?php require_once '../includes/footer.php'; ?>