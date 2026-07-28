<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/rbac/database.php';

requirePermission('view_finance');

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
                        <a class="nav-link" href="payroll.php">
                            <i class="bi bi-wallet2"></i> Payroll
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="finance-requests.php">
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
                <h1>Finance Requests</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFinanceRequestModal">
                    <i class="bi bi-plus"></i> New Request
                </button>
            </div>

            <?php if(isset($_SESSION['swal']) && !empty($_SESSION['swal'])): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: '<?php echo $_SESSION['swal']['type']; ?>',
                            title: '<?php echo $_SESSION['swal']['title']; ?>',
                            text: '<?php echo addslashes($_SESSION['swal']['text']); ?>',
                            timer: 3000,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    });
                </script>
                <?php unset($_SESSION['swal']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Request Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Requested By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $requests = $pdo->query("
                                    SELECT fr.*, u.full_name as requested_by_name 
                                    FROM finance_requests fr
                                    JOIN users u ON fr.requested_by = u.id
                                    ORDER BY fr.date_requested DESC
                                ");
                                while($req = $requests->fetch()):
                                ?>
                                <tr>
                                    <td><?php echo ucfirst($req['request_type']); ?></td>
                                    <td><?php echo formatCurrency($req['amount']); ?></td>
                                    <td><?php echo substr($req['description'], 0, 50) . '...'; ?></td>
                                    <td><?php echo $req['requested_by_name']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $req['status'] == 'approved' ? 'success' : 
                                                ($req['status'] == 'rejected' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($req['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if(userCan('approve_finance_requests') && $req['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-success" onclick="approveFinanceRequest(<?php echo $req['id']; ?>)">
                                            <i class="bi bi-check"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectFinanceRequest(<?php echo $req['id']; ?>)">
                                            <i class="bi bi-x"></i> Reject
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

<!-- Add Finance Request Modal -->
<div class="modal fade" id="addFinanceRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Finance Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add-finance-request.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Request Type</label>
                        <select name="request_type" class="form-select" required>
                            <option value="budget">Budget</option>
                            <option value="reimbursement">Reimbursement</option>
                            <option value="advance">Cash Advance</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveFinanceRequest(id) {
    Swal.fire({
        title: 'Approve Request?',
        text: 'Are you sure you want to approve this finance request?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'actions/approve-finance-request.php?id=' + id;
        }
    });
}

function rejectFinanceRequest(id) {
    Swal.fire({
        title: 'Reject Request?',
        text: 'Are you sure you want to reject this finance request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, reject!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'actions/reject-finance-request.php?id=' + id;
        }
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>