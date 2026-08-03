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
        <h1>Supply Chain Dashboard</h1>
        <span>Welcome, <?php echo $_SESSION['full_name']; ?></span>
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

    <?php
    try {
        $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $pending_requests = $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'pending'")->fetchColumn();
        $finance_approved = $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'finance_approved'")->fetchColumn();
        $approved_requests = $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'approved'")->fetchColumn();
        $rejected_requests = $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE status = 'rejected'")->fetchColumn();
        $total_requests = $pdo->query("SELECT COUNT(*) FROM supply_requests")->fetchColumn();
    } catch (PDOException $e) {
        $total_products = 0;
        $pending_requests = 0;
        $finance_approved = 0;
        $approved_requests = 0;
        $rejected_requests = 0;
        $total_requests = 0;
    }
    ?>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #0d6efd;">
                <div class="card-body">
                    <i class="bi bi-box stat-icon"></i>
                    <h6>Total Products</h6>
                    <h3><?php echo $total_products; ?></h3>
                    <small>In inventory</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #ffc107;">
                <div class="card-body">
                    <i class="bi bi-clock-history stat-icon"></i>
                    <h6>Pending Requests</h6>
                    <h3><?php echo $pending_requests; ?></h3>
                    <?php if($pending_requests > 0): ?>
                        <small class="text-warning">Needs approval</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #17a2b8;">
                <div class="card-body">
                    <i class="bi bi-check-circle stat-icon"></i>
                    <h6>Finance Approved</h6>
                    <h3><?php echo $finance_approved; ?></h3>
                    <small>Awaiting supply approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #28a745;">
                <div class="card-body">
                    <i class="bi bi-check2-all stat-icon"></i>
                    <h6>Fully Approved</h6>
                    <h3><?php echo $approved_requests; ?></h3>
                    <small>Completed requests</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="products.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-box" style="font-size: 24px;"></i><br>
                                Products
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="requests.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-box-seam" style="font-size: 24px;"></i><br>
                                Supply Requests
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="approvals.php" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-check2-circle" style="font-size: 24px;"></i><br>
                                Approvals
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="../../pages/receipts.php" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-receipt" style="font-size: 24px;"></i><br>
                                View Receipts
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Supply Requests -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Recent Supply Requests</h5>
            <a href="requests.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
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
                            $recent = $pdo->query("SELECT * FROM supply_requests ORDER BY date_requested DESC LIMIT 10");
                            if($recent->rowCount() > 0):
                            while($row = $recent->fetch()):
                        ?>
                        <tr>
                            <td><strong><?php echo $row['request_number']; ?></strong></td>
                            <td><?php echo $row['department']; ?></td>
                            <td><?php echo formatCurrency($row['total_amount']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $row['status'] == 'approved' ? 'success' : 
                                        ($row['status'] == 'rejected' ? 'danger' : 
                                            ($row['status'] == 'finance_approved' ? 'info' : 'warning')); 
                                ?>">
                                    <?php echo str_replace('_', ' ', ucfirst($row['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['date_requested'])); ?></td>
                        </tr>
                        <?php 
                            endwhile;
                            else:
                        ?>
                        <tr>
                            <td colspan="5" class="text-center">No supply requests yet</td>
                        </tr>
                        <?php 
                            endif;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="5" class="text-center text-danger">Error loading requests</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>