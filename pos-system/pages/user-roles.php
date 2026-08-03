<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Only Admin can access user roles
if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied',
        'text' => 'Only Administrators can access User Roles.'
    ];
    redirect('dashboard.php');
    exit();
}

require_once '../includes/header.php';
require_once '../includes/sidebar-role.php';  // Use role sidebar
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>User Roles</h1>
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
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Current Roles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users = $pdo->query("SELECT * FROM users ORDER BY username");
                        while($user = $users->fetch()):
                            $user_roles = $pdo->prepare("
                                SELECT r.* FROM roles r 
                                JOIN user_roles ur ON r.id = ur.role_id 
                                WHERE ur.user_id = ?
                            ");
                            $user_roles->execute([$user['id']]);
                            $roles = $user_roles->fetchAll();
                        ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo $user['username']; ?></strong></td>
                            <td><?php echo $user['full_name']; ?></td>
                            <td>
                                <?php foreach($roles as $r): ?>
                                    <span class="badge bg-primary"><?php echo $r['role_name']; ?></span>
                                <?php endforeach; ?>
                                <?php if(empty($roles)): ?>
                                    <span class="text-muted">No roles assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editUserRoles(<?php echo $user['id']; ?>)">
                                    <i class="bi bi-pencil"></i> Assign Roles
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Edit User Roles Modal -->
<div class="modal fade" id="editUserRolesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Roles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/assign-user-roles.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="assign_user_id">
                    <div class="mb-3">
                        <label>User</label>
                        <input type="text" id="assign_user_name" class="form-control" readonly>
                    </div>
                    <hr>
                    <h6>Select Roles</h6>
                    <div id="roles_checkbox_list">
                        <!-- Loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUserRoles(userId) {
    fetch('../actions/get-user-roles-data.php?user_id=' + userId)
        .then(response => response.json())
        .then(data => {
            document.getElementById('assign_user_id').value = data.user.id;
            document.getElementById('assign_user_name').value = data.user.username + ' - ' + data.user.full_name;
            
            let html = '';
            data.roles.forEach(role => {
                let checked = data.user_roles.includes(role.id) ? 'checked' : '';
                html += `
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" 
                               name="roles[]" value="${role.id}" ${checked}>
                        <label class="form-check-label">${role.role_name}</label>
                    </div>
                `;
            });
            document.getElementById('roles_checkbox_list').innerHTML = html;
            
            var modal = new bootstrap.Modal(document.getElementById('editUserRolesModal'));
            modal.show();
        });
}
</script>

<?php require_once '../includes/footer.php'; ?>