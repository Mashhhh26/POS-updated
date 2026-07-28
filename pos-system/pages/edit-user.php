<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$rbac_file = __DIR__ . '/../includes/rbac/roles.php';
if (file_exists($rbac_file)) {
    require_once $rbac_file;
}

if (!isAdmin()) {
    redirect('dashboard.php');
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'User not found.'
    ];
    redirect('users.php');
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Edit User</h1>
        <a href="users.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="../actions/edit-user-action.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Role</label>
                        <select name="role" class="form-select">
                            <option value="staff" <?php echo $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" <?php echo ($user['is_active'] ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo ($user['is_active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <hr>
                        <h6>Change Password (Leave blank to keep current)</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>