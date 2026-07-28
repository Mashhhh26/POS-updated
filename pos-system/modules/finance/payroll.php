<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/rbac/database.php';

requirePermission('view_payroll');

require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block sidebar">
            <div class="position-sticky">
                <h4 class="text-white text-center py-3">POS System</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="../../pages/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../pages/sales.php">
                            <i class="bi bi-cart"></i> Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../hr/employees.php">
                            <i class="bi bi-people"></i> Employees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="payroll.php">
                            <i class="bi bi-wallet2"></i> Payroll
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="finance-requests.php">
                            <i class="bi bi-cash"></i> Finance Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../pages/receipts.php">
                            <i class="bi bi-receipt"></i> Receipts
                        </a>
                    </li>
                    <?php if(isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../../pages/users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../../actions/logout-action.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
                <div class="mt-4 p-3 text-white" style="border-top: 1px solid #34495e;">
                    <small>
                        <i class="bi bi-person"></i> <?php echo $_SESSION['full_name']; ?>
                        <br>
                        <span class="badge bg-<?php 
                            echo $_SESSION['role'] == 'admin' ? 'danger' : 
                                ($_SESSION['role'] == 'co-admin' ? 'warning' : 'info'); 
                        ?>">
                            <?php echo ucfirst($_SESSION['role']); ?>
                        </span>
                    </small>
                </div>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Payroll Management</h1>
                <?php if(userCan('manage_payroll')): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">
                    <i class="bi bi-plus"></i> Generate Payroll
                </button>
                <?php endif; ?>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Total Payroll</h6>
                            <h3><?php 
                                $total = $pdo->query("SELECT COALESCE(SUM(net_pay), 0) FROM payroll WHERE status = 'pending'")->fetchColumn();
                                echo formatCurrency($total);
                            ?></h3>
                            <small>Pending</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Employees</h6>
                            <h3><?php echo $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn(); ?></h3>
                            <small>Active</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>This Month</h6>
                            <h3><?php 
                                $month = date('Y-m-01');
                                $total = $pdo->query("SELECT COALESCE(SUM(net_pay), 0) FROM payroll WHERE pay_period_start >= '$month' AND status = 'paid'")->fetchColumn();
                                echo formatCurrency($total);
                            ?></h3>
                            <small>Paid</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Total Paid</h6>
                            <h3><?php 
                                $total = $pdo->query("SELECT COALESCE(SUM(net_pay), 0) FROM payroll WHERE status = 'paid'")->fetchColumn();
                                echo formatCurrency($total);
                            ?></h3>
                            <small>All Time</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Pay Period</th>
                                    <th>Gross Pay</th>
                                    <th>Deductions</th>
                                    <th>Net Pay</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $payroll = $pdo->query("
                                    SELECT p.*, e.first_name, e.last_name 
                                    FROM payroll p
                                    JOIN employees e ON p.employee_id = e.id
                                    ORDER BY p.created_at DESC
                                    LIMIT 50
                                ");
                                while($row = $payroll->fetch()):
                                ?>
                                <tr>
                                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><?php echo date('M d', strtotime($row['pay_period_start'])) . ' - ' . date('M d', strtotime($row['pay_period_end'])); ?></td>
                                    <td><?php echo formatCurrency($row['gross_pay']); ?></td>
                                    <td><?php echo formatCurrency($row['deductions']); ?></td>
                                    <td><strong><?php echo formatCurrency($row['net_pay']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['status'] == 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($row['status'] == 'pending' && userCan('manage_payroll')): ?>
                                        <button class="btn btn-sm btn-success" onclick="processPayroll(<?php echo $row['id']; ?>)">
                                            <i class="bi bi-check"></i> Process
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Generate Payroll Modal -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/generate-payroll.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Pay Period Start</label>
                        <input type="date" name="pay_period_start" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Pay Period End</label>
                        <input type="date" name="pay_period_end" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Employees</label>
                        <select name="employee_id" class="form-select">
                            <option value="">All Active Employees</option>
                            <?php
                            $emps = $pdo->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY last_name");
                            while($e = $emps->fetch()):
                            ?>
                            <option value="<?php echo $e['id']; ?>">
                                <?php echo $e['first_name'] . ' ' . $e['last_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Payroll</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function processPayroll(id) {
    Swal.fire({
        title: 'Process Payroll?',
        text: 'Are you sure you want to mark this payroll as paid?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, process!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'actions/process-payroll.php?id=' + id;
        }
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>