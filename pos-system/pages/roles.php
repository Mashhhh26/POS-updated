<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Only Admin can access role management
if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied',
        'text' => 'Only Administrators can access Role Management.'
    ];
    redirect('dashboard.php');
    exit();
}

require_once '../includes/header.php';
require_once '../includes/sidebar-role.php';  // Use role sidebar
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Role Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">Create Role</button>
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
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $roles = $pdo->query("SELECT * FROM roles ORDER BY role_name");
                        while($role = $roles->fetch()):
                            $perms = $pdo->prepare("SELECT COUNT(*) FROM role_permissions WHERE role_id = ?");
                            $perms->execute([$role['id']]);
                            $perm_count = $perms->fetchColumn();
                            
                            $users = $pdo->prepare("SELECT COUNT(*) FROM user_roles WHERE role_id = ?");
                            $users->execute([$role['id']]);
                            $user_count = $users->fetchColumn();
                        ?>
                        <tr>
                            <td><?php echo $role['id']; ?></td>
                            <td><strong><?php echo $role['role_name']; ?></strong></td>
                            <td><?php echo $role['description']; ?></td>
                            <td><span class="badge bg-primary"><?php echo $perm_count; ?></span></td>
                            <td><span class="badge bg-info"><?php echo $user_count; ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editRole(<?php echo $role['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if($role['role_name'] != 'super_admin'): ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteRole(<?php echo $role['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
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

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/add-role.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Role Name</label>
                        <input type="text" name="role_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/edit-role.php" method="POST" id="editRoleForm">
                <div class="modal-body">
                    <input type="hidden" name="role_id" id="edit_role_id">
                    <div class="mb-3">
                        <label>Role Name</label>
                        <input type="text" name="role_name" id="edit_role_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" id="edit_role_description" class="form-control" rows="2"></textarea>
                    </div>
                    <hr>
                    <h6>Permissions</h6>
                    <div id="permissions_list" class="row">
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
function editRole(id) {
    fetch('../actions/get-role-data.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_role_id').value = data.id;
            document.getElementById('edit_role_name').value = data.role_name;
            document.getElementById('edit_role_description').value = data.description;
            
            let html = '';
            let current_module = '';
            data.permissions.forEach(perm => {
                if (current_module != perm.module) {
                    current_module = perm.module;
                    html += '<div class="col-12 mt-2"><strong>' + perm.module.toUpperCase() + '</strong></div>';
                }
                let checked = data.role_permissions.includes(perm.id) ? 'checked' : '';
                html += `
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" 
                                   name="permissions[]" value="${perm.id}" ${checked}>
                            <label class="form-check-label">${perm.permission_name}</label>
                        </div>
                    </div>
                `;
            });
            document.getElementById('permissions_list').innerHTML = html;
            
            var modal = new bootstrap.Modal(document.getElementById('editRoleModal'));
            modal.show();
        });
}

function deleteRole(id) {
    Swal.fire({
        title: 'Delete Role?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../actions/delete-role.php?id=' + id;
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>