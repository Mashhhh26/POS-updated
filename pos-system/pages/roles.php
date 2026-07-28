<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Load RBAC
$rbac_file = __DIR__ . '/../includes/rbac/roles.php';
if (file_exists($rbac_file)) {
    require_once $rbac_file;
} else {
    // Fallback functions
    function getAllRoles() {
        global $pdo;
        return $pdo->query("SELECT * FROM roles ORDER BY role_name")->fetchAll();
    }
    function getPermissionsByRole($role_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT p.* FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = ?");
        $stmt->execute([$role_id]);
        return $stmt->fetchAll();
    }
}

// Only admin can access
if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'Only administrators can access this page.'
    ];
    redirect('dashboard.php');
    exit();
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Role Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="bi bi-plus"></i> Create Role
        </button>
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
        <div class="card-header">
            <h5>Roles</h5>
        </div>
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
                        $roles = getAllRoles();
                        if(!empty($roles)):
                        foreach($roles as $role):
                            $perms = getPermissionsByRole($role['id']);
                            $user_count = $pdo->prepare("SELECT COUNT(*) FROM user_roles WHERE role_id = ?");
                            $user_count->execute([$role['id']]);
                            $count = $user_count->fetchColumn();
                        ?>
                        <tr>
                            <td><?php echo $role['id']; ?></td>
                            <td><strong><?php echo $role['role_name']; ?></strong></td>
                            <td><?php echo $role['description']; ?></td>
                            <td><span class="badge bg-primary"><?php echo count($perms); ?> permissions</span></td>
                            <td><span class="badge bg-info"><?php echo $count; ?> users</span></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editRole(<?php echo $role['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if($role['role_name'] != 'super_admin' && $role['role_name'] != 'admin'): ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteRole(<?php echo $role['id']; ?>, '<?php echo $role['role_name']; ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No roles found. Please create one.</td>
                        </tr>
                        <?php endif; ?>
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
                <h5 class="modal-title">Create New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/add-role-action.php" method="POST">
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
                    <button type="submit" class="btn btn-primary">Create Role</button>
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
                <h5 class="modal-title">Edit Role & Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/edit-role-action.php" method="POST" id="editRoleForm">
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
                        <!-- Permissions loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit Role
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
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to load role data.'
            });
        });
}

// Delete Role
function deleteRole(id, name) {
    Swal.fire({
        title: 'Delete Role?',
        text: 'Are you sure you want to delete "' + name + '"? This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../actions/delete-role-action.php?id=' + id;
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>