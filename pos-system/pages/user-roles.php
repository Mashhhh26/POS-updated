<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Load RBAC
$rbac_file = __DIR__ . '/../includes/rbac/roles.php';
if (file_exists($rbac_file)) {
    require_once $rbac_file;
} else {
    function getUserRoles($user_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT r.* FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ?");
        $stmt->execute([$user_id]);
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
        <h1>User Role Assignment</h1>
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
                            $user_roles = getUserRoles($user['id']);
                        ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo $user['username']; ?></strong></td>
                            <td><?php echo $user['full_name']; ?></td>
                            <td>
                                <?php foreach($user_roles as $r): ?>
                                    <span class="badge bg-<?php 
                                        echo $r['role_name'] == 'super_admin' ? 'danger' : 
                                            ($r['role_name'] == 'admin' ? 'warning' : 
                                                ($r['role_name'] == 'ceo' ? 'primary' : 
                                                    ($r['role_name'] == 'finance' ? 'info' : 
                                                        ($r['role_name'] == 'hr' ? 'success' : 'secondary')))); 
                                    ?>">
                                        <?php echo $r['role_name']; ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if(empty($user_roles)): ?>
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
                <h5 class="modal-title">Assign Roles to User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/assign-user-roles.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="assign_user_id">
                    <div class="mb-3">
                        <label>User</label>
                        <input type="text" id="assign_user_name" class="form-control" readonly style="background: #f8f9fa;">
                    </div>
                    <hr>
                    <h6>Select Roles</h6>
                    <div id="roles_checkbox_list">
                        <!-- Roles loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Roles</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit User Roles
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
                        <label class="form-check-label">
                            <span class="badge bg-${role.role_name == 'super_admin' ? 'danger' : 
                                (role.role_name == 'admin' ? 'warning' : 
                                    (role.role_name == 'ceo' ? 'primary' : 
                                        (role.role_name == 'finance' ? 'info' : 
                                            (role.role_name == 'hr' ? 'success' : 'secondary'))))}">
                                ${role.role_name}
                            </span>
                            - ${role.description}
                        </label>
                    </div>
                `;
            });
            
            document.getElementById('roles_checkbox_list').innerHTML = html;
            
            var modal = new bootstrap.Modal(document.getElementById('editUserRolesModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to load user data.'
            });
        });
}
</script>

<?php require_once '../includes/footer.php'; ?>