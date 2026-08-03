<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// HR or Admin can manage users
$role = $_SESSION['role'] ?? 'staff';
if (!isAdmin() && $role != 'hr') {
    $_SESSION['error'] = 'Access denied. HR or Admin only.';
    redirect('../../pages/dashboard.php');
    exit();
}

// Check if user can manage (both HR and Admin can)
$can_manage = true; // Both HR and Admin can manage

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>User Management</h1>
        <?php if($can_manage): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
        <?php endif; ?>
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
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                        while($u = $users->fetch()):
                        ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><strong><?php echo $u['username']; ?></strong></td>
                            <td><?php echo $u['full_name']; ?></td>
                            <td><?php echo $u['position'] ?? 'N/A'; ?></td>
                            <td><?php echo $u['department'] ?? 'N/A'; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $u['role'] == 'admin' ? 'danger' : 
                                        ($u['role'] == 'co-admin' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo ($u['status'] ?? 'active') == 'active' ? 'success' : 'secondary'; 
                                ?>">
                                    <?php echo ucfirst($u['status'] ?? 'active'); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($can_manage): ?>
                                <a href="edit-user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if($u['id'] != $_SESSION['user_id']): ?>
                                <a href="actions/delete-user.php?id=<?php echo $u['id']; ?>" 
                                   class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add-user.php" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Position</label>
                            <input type="text" name="position" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Department</label>
                            <input type="text" name="department" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="staff">Staff</option>
                                <option value="co-admin">Co-Admin</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>