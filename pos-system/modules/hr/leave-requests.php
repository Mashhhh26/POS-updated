<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$role = $_SESSION['role'] ?? 'staff';
if (!isAdmin() && $role != 'hr') {
    redirect('../../pages/dashboard.php');
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Leave Requests</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveModal">File Leave Request</button>
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
                        try {
                            $leaves = $pdo->query("
                                SELECT l.*, e.first_name, e.last_name 
                                FROM leave_requests l
                                JOIN employees e ON l.employee_id = e.id
                                ORDER BY l.date_filed DESC
                            ");
                            while($row = $leaves->fetch()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
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
                                <a href="actions/approve-leave.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-success" onclick="return confirm('Approve this leave?')">
                                    <i class="bi bi-check"></i> Approve
                                </a>
                                <a href="actions/reject-leave.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger" onclick="return confirm('Reject this leave?')">
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

<!-- Add Leave Modal -->
<div class="modal fade" id="addLeaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add-leave.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php
                            $emps = $pdo->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY last_name");
                            while($e = $emps->fetch()):
                            ?>
                            <option value="<?php echo $e['id']; ?>"><?php echo $e['first_name'] . ' ' . $e['last_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Leave Type</label>
                        <select name="leave_type" class="form-select" required>
                            <option value="sick">Sick Leave</option>
                            <option value="vacation">Vacation Leave</option>
                            <option value="emergency">Emergency Leave</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Reason</label>
                        <textarea name="reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>