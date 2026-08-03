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
        <h1>Employee Directory</h1>
        <span class="text-muted">View only</span>
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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>All Employees</h5>
            <span class="badge bg-primary"><?php echo $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?> total</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $employees = $pdo->query("
                            SELECT id, username, full_name, position, department, role, status, created_at 
                            FROM users 
                            ORDER BY created_at DESC
                        ");
                        while($emp = $employees->fetch()):
                        ?>
                        <tr>
                            <td><?php echo $emp['id']; ?></td>
                            <td><strong><?php echo $emp['username']; ?></strong></td>
                            <td><?php echo $emp['full_name']; ?></td>
                            <td><?php echo $emp['position'] ?? 'N/A'; ?></td>
                            <td><?php echo $emp['department'] ?? 'N/A'; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $emp['role'] == 'admin' ? 'danger' : 
                                        ($emp['role'] == 'co-admin' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo ucfirst($emp['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo ($emp['status'] ?? 'active') == 'active' ? 'success' : 'secondary'; 
                                ?>">
                                    <?php echo ucfirst($emp['status'] ?? 'active'); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body text-muted text-center">
            <small>
                <i class="bi bi-info-circle"></i> 
                To add, edit, or delete employees, go to 
                <a href="users.php" class="text-primary">User Management</a>
            </small>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>