<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$role = $_SESSION['role'] ?? 'staff';
if (!isAdmin() && $role != 'supply') {
    redirect('../../pages/dashboard.php');
    exit();
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Supply Approvals</h1>
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
            <h5>Pending & Finance Approved Requests</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Request Number</th>
                            <th>Department</th>
                            <th>Total Amount</th>
                            <th>Finance Approved</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // SIMPLE QUERY - Show pending and finance_approved
                            $requests = $pdo->query("
                                SELECT * FROM supply_requests 
                                WHERE status IN ('pending', 'finance_approved') 
                                ORDER BY date_requested ASC
                            ");
                            
                            if($requests->rowCount() > 0):
                            $counter = 1;
                            while($row = $requests->fetch()):
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><strong><?php echo $row['request_number']; ?></strong></td>
                            <td><?php echo $row['department']; ?></td>
                            <td><?php echo formatCurrency($row['total_amount']); ?></td>
                            <td>
                                <?php if($row['finance_approved'] == 1): ?>
                                    <span class="badge bg-success">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $row['status'] == 'finance_approved' ? 'info' : 'warning'; 
                                ?>">
                                    <?php echo str_replace('_', ' ', ucfirst($row['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['date_requested'])); ?></td>
                            <td>
                                <?php if($row['status'] == 'pending'): ?>
                                    <span class="text-muted">Waiting for Finance</span>
                                    <a href="actions/reject-request.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" onclick="return confirm('Reject?')">
                                        Reject
                                    </a>
                                <?php elseif($row['status'] == 'finance_approved'): ?>
                                    <a href="actions/approve-request.php?id=<?php echo $row['id']; ?>&level=supply" 
                                       class="btn btn-sm btn-success" onclick="return confirm('Approve? This will add items to Products.')">
                                        Approve
                                    </a>
                                    <a href="actions/reject-request.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" onclick="return confirm('Reject?')">
                                        Reject
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                            else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">No pending requests</td>
                        </tr>
                        <?php 
                            endif;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="8" class="text-center text-danger">Error: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>