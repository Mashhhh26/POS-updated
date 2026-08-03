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
        <h1>Finance Dashboard</h1>
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
        $pending_payroll = $pdo->query("SELECT COALESCE(SUM(net_pay), 0) FROM payroll WHERE status = 'pending'")->fetchColumn();
        $total_payroll = $pdo->query("SELECT COUNT(*) FROM payroll")->fetchColumn();
        $pending_requests = $pdo->query("SELECT COUNT(*) FROM finance_requests WHERE status = 'pending'")->fetchColumn();
        $total_requests = $pdo->query("SELECT COUNT(*) FROM finance_requests")->fetchColumn();
        $pending_supply = $pdo->query("SELECT COUNT(*) FROM supply_requests WHERE finance_approved = 0 AND status = 'pending'")->fetchColumn();
    } catch (PDOException $e) {
        $pending_payroll = 0;
        $total_payroll = 0;
        $pending_requests = 0;
        $total_requests = 0;
        $pending_supply = 0;
    }
    ?>

    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #e74c3c;">
                <div class="card-body">
                    <i class="bi bi-cash stat-icon"></i>
                    <h6>Pending Payroll</h6>
                    <h3><?php echo formatCurrency($pending_payroll); ?></h3>
                    <small>Needs processing</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #3498db;">
                <div class="card-body">
                    <i class="bi bi-file-text stat-icon"></i>
                    <h6>Total Payroll Records</h6>
                    <h3><?php echo $total_payroll; ?></h3>
                    <small>All time</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #f39c12;">
                <div class="card-body">
                    <i class="bi bi-cash-stack stat-icon"></i>
                    <h6>Pending Requests</h6>
                    <h3><?php echo $pending_requests; ?></h3>
                    <small>Needs approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #8e44ad;">
                <div class="card-body">
                    <i class="bi bi-check2-circle stat-icon"></i>
                    <h6>Pending Supply Approvals</h6>
                    <h3><?php echo $pending_supply; ?></h3>
                    <small>Needs finance approval</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="payroll.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-cash" style="font-size: 24px;"></i><br>
                                Manage Payroll
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="requests.php" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-cash-stack" style="font-size: 24px;"></i><br>
                                Finance Requests
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="approvals.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-check2-circle" style="font-size: 24px;"></i><br>
                                Supply Approvals
                            </a>
                        </div>
                        <div class="col-md-3">
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
</main>

<?php require_once '../../includes/footer.php'; ?>