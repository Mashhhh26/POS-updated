<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/rbac/database.php';

requirePermission('view_supply_requests');

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
                        <a class="nav-link active" href="requests.php">
                            <i class="bi bi-boxes"></i> Supply Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="approval.php">
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
                <h1>Supply Requests</h1>
                <?php if(userCan('create_supply_requests')): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplyRequestModal">
                    <i class="bi bi-plus"></i> New Request
                </button>
                <?php endif; ?>
            </div>

            <!-- Status Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo ($_GET['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="finance_approved" <?php echo ($_GET['status'] ?? '') == 'finance_approved' ? 'selected' : ''; ?>>Finance Approved</option>
                                <option value="ceo_approved" <?php echo ($_GET['status'] ?? '') == 'ceo_approved' ? 'selected' : ''; ?>>CEO Approved</option>
                                <option value="ordered" <?php echo ($_GET['status'] ?? '') == 'ordered' ? 'selected' : ''; ?>>Ordered</option>
                                <option value="received" <?php echo ($_GET['status'] ?? '') == 'received' ? 'selected' : ''; ?>>Received</option>
                                <option value="rejected" <?php echo ($_GET['status'] ?? '') == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Request #</th>
                                    <th>Department</th>
                                    <th>Priority</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $status = $_GET['status'] ?? '';
                                $sql = "SELECT sr.*, u.full_name as requested_by_name 
                                        FROM supply_requests sr
                                        JOIN users u ON sr.requested_by = u.id";
                                if (!empty($status)) {
                                    $sql .= " WHERE sr.status = '$status'";
                                }
                                $sql .= " ORDER BY sr.date_requested DESC";
                                
                                $requests = $pdo->query($sql);
                                while($req = $requests->fetch()):
                                ?>
                                <tr>
                                    <td><strong><?php echo $req['request_number']; ?></strong></td>
                                    <td><?php echo $req['department']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $req['priority'] == 'urgent' ? 'danger' : 
                                                ($req['priority'] == 'high' ? 'warning' : 
                                                    ($req['priority'] == 'medium' ? 'info' : 'secondary')); 
                                        ?>">
                                            <?php echo ucfirst($req['priority']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatCurrency($req['total_amount']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $req['status'] == 'received' ? 'success' : 
                                                ($req['status'] == 'rejected' ? 'danger' : 
                                                    ($req['status'] == 'ceo_approved' ? 'primary' : 
                                                        ($req['status'] == 'finance_approved' ? 'info' : 'warning'))); 
                                        ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($req['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($req['date_requested'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewRequest(<?php echo $req['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if($req['status'] == 'pending' && userCan('approve_supply_requests')): ?>
                                        <button class="btn btn-sm btn-success" onclick="approveRequest(<?php echo $req['id']; ?>)">
                                            <i class="bi bi-check"></i>
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

<script>
function viewRequest(id) {
    window.location.href = 'view-request.php?id=' + id;
}

function approveRequest(id) {
    Swal.fire({
        title: 'Approve Request?',
        text: 'Which level do you want to approve?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Finance Approval',
        cancelButtonText: 'CEO Approval'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'actions/approve-request.php?id=' + id + '&level=finance';
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            window.location.href = 'actions/approve-request.php?id=' + id + '&level=ceo';
        }
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>