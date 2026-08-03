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
        <h1>Finance Requests</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFinanceRequestModal">New Request</button>
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
                        try {
                            $requests = $pdo->query("
                                SELECT fr.*, u.full_name as requested_by_name 
                                FROM finance_requests fr
                                JOIN users u ON fr.requested_by = u.id
                                ORDER BY fr.date_requested DESC
                            ");
                            while($row = $requests->fetch()):
                        ?>
                        <tr>
                            <td><?php echo ucfirst($row['request_type']); ?></td>
                            <td><?php echo formatCurrency($row['amount']); ?></td>
                            <td><?php echo substr($row['description'], 0, 50) . '...'; ?></td>
                            <td><?php echo $row['requested_by_name']; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $row['status'] == 'approved' ? 'success' : 
                                        ($row['status'] == 'rejected' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($row['status'] == 'pending'): ?>
                                <a href="actions/approve-request.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-success" onclick="return confirm('Approve this request?')">
                                    <i class="bi bi-check"></i> Approve
                                </a>
                                <a href="actions/reject-request.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger" onclick="return confirm('Reject this request?')">
                                    <i class="bi bi-x"></i> Reject
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="6" class="text-center text-danger">Error: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Finance Request Modal -->
<div class="modal fade" id="addFinanceRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Finance Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add-request.php" method="POST">
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

<?php require_once '../../includes/footer.php'; ?>