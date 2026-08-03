<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$role = $_SESSION['role'] ?? 'staff';
if (!isAdmin() && $role != 'finance') {
    redirect('../../pages/dashboard.php');
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Supply Approvals (Finance)</h1>
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

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5>Pending Supply Requests</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Department</th>
                            <th>Total Amount</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $requests = $pdo->query("
                                SELECT sr.*, u.full_name as requested_by_name 
                                FROM supply_requests sr
                                JOIN users u ON sr.requested_by = u.id
                                WHERE sr.finance_approved = 0 AND sr.status = 'pending'
                                ORDER BY sr.date_requested ASC
                            ");
                            if($requests->rowCount() > 0):
                            while($row = $requests->fetch()):
                        ?>
                        <tr>
                            <td><strong><?php echo $row['request_number']; ?></strong></td>
                            <td><?php echo $row['department']; ?></td>
                            <td><?php echo formatCurrency($row['total_amount']); ?></td>
                            <td><?php echo $row['requested_by_name']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['date_requested'])); ?></td>
                            <td>
                                <a href="actions/approve-supply.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-success" onclick="return confirm('Approve this supply request as Finance?')">
                                    <i class="bi bi-check"></i> Approve
                                </a>
                                <a href="actions/reject-supply.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger" onclick="return confirm('Reject this supply request?')">
                                    <i class="bi bi-x"></i> Reject
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                            else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">No pending supply requests</td>
                        </tr>
                        <?php 
                            endif;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="6" class="text-center text-danger">Error: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Already Approved by Finance</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Department</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $requests = $pdo->query("
                                SELECT sr.*, u.full_name as requested_by_name 
                                FROM supply_requests sr
                                JOIN users u ON sr.requested_by = u.id
                                WHERE sr.finance_approved = 1 AND sr.status = 'finance_approved'
                                ORDER BY sr.date_requested DESC
                            ");
                            if($requests->rowCount() > 0):
                            while($row = $requests->fetch()):
                        ?>
                        <tr>
                            <td><strong><?php echo $row['request_number']; ?></strong></td>
                            <td><?php echo $row['department']; ?></td>
                            <td><?php echo formatCurrency($row['total_amount']); ?></td>
                            <td>
                                <span class="badge bg-info">Finance Approved</span>
                                <small class="text-muted">Awaiting Supply approval</small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['date_requested'])); ?></td>
                        </tr>
                        <?php 
                            endwhile;
                            else:
                        ?>
                        <tr>
                            <td colspan="5" class="text-center">No finance approved requests yet</td>
                        </tr>
                        <?php 
                            endif;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="5" class="text-center text-danger">Error: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>