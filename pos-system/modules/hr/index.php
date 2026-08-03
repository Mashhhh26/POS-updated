<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$role = $_SESSION['role'] ?? 'staff';
if (!isAdmin() && $role != 'hr') {
    redirect('../../pages/dashboard.php');
    exit();
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>HR Dashboard</h1>
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
        $total_employees = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
        $active_employees = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn();
        $pending_leaves = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'")->fetchColumn();
        $today_attendance = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE()")->fetchColumn();
    } catch (PDOException $e) {
        $total_employees = 0;
        $active_employees = 0;
        $pending_leaves = 0;
        $today_attendance = 0;
    }
    ?>

    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #8e44ad;">
                <div class="card-body">
                    <i class="bi bi-person-badge stat-icon"></i>
                    <h6>Total Employees</h6>
                    <h3><?php echo $total_employees; ?></h3>
                    <small><?php echo $active_employees; ?> active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #e67e22;">
                <div class="card-body">
                    <i class="bi bi-calendar-check stat-icon"></i>
                    <h6>Pending Leaves</h6>
                    <h3><?php echo $pending_leaves; ?></h3>
                    <?php if($pending_leaves > 0): ?>
                        <small class="text-warning">Needs approval</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #2ecc71;">
                <div class="card-body">
                    <i class="bi bi-clock stat-icon"></i>
                    <h6>Today's Attendance</h6>
                    <h3><?php echo $today_attendance; ?></h3>
                    <small>Logged in today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #3498db;">
                <div class="card-body">
                    <i class="bi bi-file-text stat-icon"></i>
                    <h6>Leave Requests</h6>
                    <h3><?php echo $pdo->query("SELECT COUNT(*) FROM leave_requests")->fetchColumn(); ?></h3>
                    <small>Total requests</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions - REMOVED Add Employee -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="employees.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-person" style="font-size: 24px;"></i><br>
                                Manage Employees
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="attendance.php" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-clock" style="font-size: 24px;"></i><br>
                                View Attendance
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="leave-requests.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-calendar-check" style="font-size: 24px;"></i><br>
                                Leave Requests
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>