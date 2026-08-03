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
        <h1>Supply Requests</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplyRequestModal">New Request</button>
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
                            <th>Request #</th>
                            <th>Department</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Finance Approved</th>
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
                                ORDER BY sr.date_requested DESC
                            ");
                            while($row = $requests->fetch()):
                        ?>
                        <tr>
                            <td><strong><?php echo $row['request_number']; ?></strong></td>
                            <td><?php echo $row['department']; ?></td>
                            <td><?php echo formatCurrency($row['total_amount']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $row['status'] == 'approved' ? 'success' : 
                                        ($row['status'] == 'rejected' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($row['finance_approved'] == 1): ?>
                                    <span class="badge bg-success">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['date_requested'])); ?></td>
                            <td>
                                <a href="view-request.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="7" class="text-center text-danger">Error: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Supply Request Modal -->
<div class="modal fade" id="addSupplyRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Supply Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add-request.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Department</label>
                        <input type="text" name="department" class="form-control" required>
                    </div>
                    <hr>
                    <h6>Items</h6>
                    <div id="itemsContainer">
                        <div class="row item-row">
                            <div class="col-md-5 mb-2">
                                <input type="text" name="items[]" class="form-control" placeholder="Item name" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="number" name="quantities[]" class="form-control" placeholder="Qty" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="number" name="prices[]" class="form-control" step="0.01" placeholder="Price" required>
                            </div>
                            <div class="col-md-1 mb-2">
                                <button type="button" class="btn btn-danger" onclick="removeItem(this)">x</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addItem()">
                        <i class="bi bi-plus"></i> Add Item
                    </button>
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
function addItem() {
    var container = document.getElementById('itemsContainer');
    var row = document.createElement('div');
    row.className = 'row item-row mt-2';
    row.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="items[]" class="form-control" placeholder="Item name" required>
        </div>
        <div class="col-md-3">
            <input type="number" name="quantities[]" class="form-control" placeholder="Qty" required>
        </div>
        <div class="col-md-3">
            <input type="number" name="prices[]" class="form-control" step="0.01" placeholder="Price" required>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger" onclick="removeItem(this)">x</button>
        </div>
    `;
    container.appendChild(row);
}

function removeItem(btn) {
    var row = btn.closest('.item-row');
    if (document.querySelectorAll('.item-row').length > 1) {
        row.remove();
    } else {
        alert('At least one item is required.');
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>