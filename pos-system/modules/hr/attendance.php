<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/rbac/roles.php';

if (!userCan('view_attendance')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to view attendance.'
    ];
    redirect('../../pages/dashboard.php');
    exit();
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Attendance Management</h1>
        <?php if(userCan('manage_attendance')): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAttendanceModal">
            <i class="bi bi-plus"></i> Log Attendance
        </button>
        <?php endif; ?>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $_GET['date'] ?? date('Y-m-d'); ?>">
                </div>
                <div class="col-md-4">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
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
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $date = $_GET['date'] ?? date('Y-m-d');
                        $sql = "SELECT a.*, e.first_name, e.last_name FROM attendance a 
                                JOIN employees e ON a.employee_id = e.id 
                                WHERE DATE(a.date) = ?
                                ORDER BY a.date DESC";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$date]);
                        
                        while($att = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($att['date'])); ?></td>
                            <td><?php echo $att['first_name'] . ' ' . $att['last_name']; ?></td>
                            <td><?php echo $att['time_in'] ? date('h:i A', strtotime($att['time_in'])) : '-'; ?></td>
                            <td><?php echo $att['time_out'] ? date('h:i A', strtotime($att['time_out'])) : '-'; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $att['status'] == 'present' ? 'success' : 
                                        ($att['status'] == 'late' ? 'warning' : 
                                            ($att['status'] == 'half-day' ? 'info' : 'danger')); 
                                ?>">
                                    <?php echo ucfirst($att['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if(userCan('manage_attendance')): ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteAttendance(<?php echo $att['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($stmt->rowCount() == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center">No attendance records found for this date</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Attendance Modal -->
<div class="modal fade" id="addAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add-attendance.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php
                            $emps = $pdo->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY last_name");
                            while($e = $emps->fetch()):
                            ?>
                            <option value="<?php echo $e['id']; ?>">
                                <?php echo $e['first_name'] . ' ' . $e['last_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Time In</label>
                        <input type="time" name="time_in" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Time Out</label>
                        <input type="time" name="time_out" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="half-day">Half-Day</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteAttendance(id) {
    Swal.fire({
        title: 'Delete Attendance Record?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'actions/delete-attendance.php?id=' + id;
        }
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>