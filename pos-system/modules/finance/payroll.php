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
        <h1>Payroll Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">Generate Payroll</button>
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
                            <th>Pay Period</th>
                            <th>Gross Pay</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $payroll = $pdo->query("
                                SELECT p.*, e.first_name, e.last_name 
                                FROM payroll p
                                JOIN employees e ON p.employee_id = e.id
                                ORDER BY p.created_at DESC
                                LIMIT 50
                            ");
                            while($row = $payroll->fetch()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo date('M d', strtotime($row['pay_period_start'])) . ' - ' . date('M d', strtotime($row['pay_period_end'])); ?></td>
                            <td><?php echo formatCurrency($row['gross_pay']); ?></td>
                            <td><?php echo formatCurrency($row['deductions']); ?></td>
                            <td><strong><?php echo formatCurrency($row['net_pay']); ?></strong></td>
                            <td>
                                <span class="badge bg-<?php echo $row['status'] == 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($row['status'] == 'pending'): ?>
                                <a href="actions/process-payroll.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-success" onclick="return confirm('Process this payroll?')">
                                    <i class="bi bi-check"></i> Process
                                </a>
                                <?php endif; ?>
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

<!-- Generate Payroll Modal -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/generate-payroll.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Pay Period Start</label>
                        <input type="date" name="pay_period_start" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Pay Period End</label>
                        <input type="date" name="pay_period_end" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Payroll</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>