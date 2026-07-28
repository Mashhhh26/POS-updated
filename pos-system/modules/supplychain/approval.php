<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/rbac/database.php';

requirePermission('approve_supply_requests');

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
                        <a class="nav-link" href="../finance/payroll.php">
                            <i class="bi bi-wallet2"></i> Payroll
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="requests.php">
                            <i class="bi bi-boxes"></i> Supply Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="approval.php">
                            <i class="bi bi-check2-circle"></i> Approvals
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
                <h1>Approvals Dashboard</h1>
            </div>

            <!-- Approval Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card card-stats" style="border-left: 4px solid #f39c12;">
                        <div class="card-body">
                            <h6>Pending Requests</h6>
                            <h3><?php echo $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'pending'")->fetchColumn(); ?></h3>
                            <small>Awaiting approval</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats" style="border-left: 4px solid #3498db;">
                        <div class="card-body">
                            <h6>Finance Approved</h6>
                            <h3><?php echo $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'finance_approved'")->fetchColumn(); ?></h3>
                            <small>Awaiting CEO approval</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats" style="border-left: 4px solid #2ecc71;">
                        <div class="card-body">
                            <h6>CEO Approved</h6>
                            <h3><?php echo $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'ceo_approved'")->fetchColumn(); ?></h3>
                            <small>Ready for order</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats" style="border-left: 4px solid #e74c3c;">
                        <div class="card-body">
                            <h6>Rejected</h6>
                            <h3><?php echo $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'rejected'")->fetchColumn(); ?></h3>
                            <small>Rejected requests</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Workflow Visualization -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Approval Workflow</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="border p-3 rounded">
                                <h4>📝</h4>
                                <p><strong>Step 1</strong><br>Submit Request</p>
                            </div>
                        </div>
                        <div class="col-1 d-flex align-items-center">
                            <span>→</span>
                        </div>
                        <div class="col-3">
                            <div class="border p-3 rounded bg-warning bg-opacity-10">
                                <h4>💰</h4>
                                <p><strong>Step 2</strong><br>Finance Approval</p>
                            </div>
                        </div>
                        <div class="col-1 d-flex align-items-center">
                            <span>→</span>
                        </div>
                        <div class="col-3">
                            <div class="border p-3 rounded bg-primary bg-opacity-10">
                                <h4>👔</h4>
                                <p><strong>Step 3</strong><br>CEO/Manager Approval</p>
                            </div>
                        </div>
                        <div class="col-1 d-flex align-items-center">
                            <span>→</span>
                        </div>
                        <div class="col-3">
                            <div class="border p-3 rounded bg-success bg-opacity-10">
                                <h4>📦</h4>
                                <p><strong>Step 4</strong><br>Order & Receive</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals Table -->
            <div class="card">
                <div class="card-header">
                    <h5>Pending Approvals</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Request #</th>
                                    <th>Department</th>
                                    <th>Total Amount</th>
                                    <th>Current Status</th>
                                    <th>Action Required</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $pending = $pdo->query("
                                    SELECT sr.*, u.full_name as requested_by_name 
                                    FROM supply_requests sr
                                    JOIN users u ON sr.requested_by = u.id
                                    WHERE sr.status IN ('pending', 'finance_approved')
                                    ORDER BY sr.date_requested ASC
                                ");
                                while($req = $pending->fetch()):
                                ?>
                                <tr>
                                    <td><strong><?php echo $req['request_number']; ?></strong></td>
                                    <td><?php echo $req['department']; ?></td>
                                    <td><?php echo formatCurrency($req['total_amount']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $req['status'] == 'pending' ? 'warning' : 'info'; 
                                        ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($req['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($req['status'] == 'pending'): ?>
                                            <span class="text-warning">Finance Approval</span>
                                        <?php elseif($req['status'] == 'finance_approved'): ?>
                                            <span class="text-primary">CEO Approval</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($req['status'] == 'pending' && userCan('approve_supply_requests')): ?>
                                        <button class="btn btn-sm btn-success" onclick="approveFinance(<?php echo $req['id']; ?>)">
                                            <i class="bi bi-check"></i> Approve Finance
                                        </button>
                                        <?php elseif($req['status'] == 'finance_approved' && userHasRole($_SESSION['user_id'], 'ceo')): ?>
                                        <button class="btn btn-sm btn-primary" onclick="approveCEO(<?php echo $req['id']; ?>)">
                                            <i class="bi bi-check"></i> Approve CEO
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-danger" onclick="rejectRequest(<?php echo $req['id']; ?>)">
                                            <i class="bi bi-x"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if($pending->rowCount() == 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No pending approvals</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function approveFinance(id) {
    Swal.fire({
        title: 'Finance Approval',
        text: 'Are you sure you want to approve this request for finance review?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'actions/approve-request.php?id=' + id + '&level=finance';
        }
    });
}

function approveCEO(id) {
    Swal.fire({
        title: 'CEO Approval',
        text: 'Are you sure you want to approve this request for CEO/Manager review?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'actions/approve-request.php?id=' + id + '&level=ceo';
        }
    });
}

function rejectRequest(id) {
    Swal.fire({
        title: 'Reject Request?',
        text: 'Are you sure you want to reject this request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, reject!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'actions/reject-request.php?id=' + id;
        }
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>