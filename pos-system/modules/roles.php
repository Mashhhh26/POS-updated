<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('manage_roles')) {
    logActivity("Access denied: roles.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_role'])) {
    $role_name = sanitize($_POST['role_name']);
    $description = sanitize($_POST['description']);
    
    try {
        $stmt = $db->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
        $stmt->execute([$role_name, $description]);
        $success_message = "Role <strong>{$role_name}</strong> created successfully!";
        logActivity("Created role: {$role_name}");
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_permissions'])) {
    $role_id = (int)$_POST['role_id'];
    $permissions = $_POST['permissions'] ?? [];
    
    try {
        $db->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$role_id]);
        
        foreach ($permissions as $perm_id) {
            $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $stmt->execute([$role_id, $perm_id]);
        }
        
        $success_message = "Permissions updated successfully!";
        logActivity("Updated permissions for role ID: {$role_id}");
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $db->prepare("DELETE FROM roles WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Role deleted successfully!']);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

$roles = $db->query("SELECT * FROM roles")->fetchAll();
$permissions = $db->query("SELECT * FROM permissions ORDER BY module")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="fas fa-user-tag me-2 text-primary"></i> Role Management</h4>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i> Create New Role</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Role Name</label>
                                <input type="text" name="role_name" class="form-control" required placeholder="e.g. Manager">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control" placeholder="Brief description">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="add_role" class="btn btn-primary w-100">
                                    <i class="fas fa-plus-circle me-1"></i> Create
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php foreach($roles as $role): ?>
            <div class="card border-0 shadow-sm mb-4" id="role-card-<?php echo $role['id']; ?>">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-cog me-2 text-primary"></i> 
                        <?php echo $role['role_name']; ?>
                        <?php if ($role['id'] > 1): ?>
                        <button class="btn btn-sm btn-danger delete-role" data-id="<?php echo $role['id']; ?>" data-name="<?php echo $role['role_name']; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </h5>
                    <span class="text-muted small"><?php echo $role['description']; ?></span>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                        <div class="row">
                            <?php 
                            $currentPerms = $db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
                            $currentPerms->execute([$role['id']]);
                            $rolePerms = $currentPerms->fetchAll(PDO::FETCH_COLUMN);
                            
                            foreach($permissions as $perm): 
                            ?>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" 
                                           value="<?php echo $perm['id']; ?>"
                                           id="perm-<?php echo $role['id']; ?>-<?php echo $perm['id']; ?>"
                                           <?php echo in_array($perm['id'], $rolePerms) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="perm-<?php echo $role['id']; ?>-<?php echo $perm['id']; ?>">
                                        <?php echo $perm['permission_name']; ?>
                                        <small class="text-muted d-block"><?php echo $perm['module']; ?></small>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <button type="submit" name="assign_permissions" class="btn btn-primary btn-sm">
                                <i class="fas fa-save me-1"></i> Update Permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>
    
    <script>
        document.querySelectorAll('.delete-role').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.dataset.id;
                const name = this.dataset.name;
                
                confirmDelete(`Role "${name}" will be permanently deleted!`, function() {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    fetch(`?delete=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message,
                                    timer: 2000,
                                    timerProgressBar: true,
                                    showConfirmButton: true,
                                    confirmButtonColor: '#198754',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    document.getElementById(`role-card-${id}`).remove();
                                });
                            } else {
                                showError(data.message);
                            }
                        })
                        .catch(() => showError('Error deleting role!'));
                });
            });
        });
    </script>
</body>
</html>