<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/rbac/roles.php';

// Check if user is HR or Admin
if (!hasRole('hr') && !isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to access this page.'
    ];
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

    <?php
    // HR Stats
    $total_employees = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
    $active_employees = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn();
    $pending_leaves = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'")->fetchColumn();
    $today_attendance = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE()")->fetchColumn();
    $total_leave_requests = $pdo->query("SELECT COUNT(*) FROM leave_requests")->fetchColumn();
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
                    <h6>Total Leave Requests</h6>
                    <h3><?php echo $total_leave_requests; ?></h3>
                    <small>All time</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Leave Requests -->
    <div class="card mt-4">
        <div class="card-header">
            <h5><i class="bi bi-clock-history"></i> Recent Leave Requests</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent_leaves = $pdo->query("
                            SELECT l.*, e.first_name, e.last_name 
                            FROM leave_requests l
                            JOIN employees e ON l.employee_id = e.id
                            ORDER BY l.date_filed DESC LIMIT 10
                        ");
                        if($recent_leaves->rowCount() > 0):
                        while($row = $recent_leaves->fetch()):
                        ?>
                        <tr>
                            <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                            <td><?php echo ucfirst($row['leave_type']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['start_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['end_date'])); ?></td>
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
                                <a href="actions/approve-leave-action.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-success" onclick="return confirm('Approve this leave request?')">
                                    <i class="bi bi-check"></i>
                                </a>
                                <a href="actions/reject-leave-action.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger" onclick="return confirm('Reject this leave request?')">
                                    <i class="bi bi-x"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No leave requests found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>