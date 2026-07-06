<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Only Admin can edit users
if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'Only Administrators can edit users.'
    ];
    redirect('dashboard.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid user ID.'
    ];
    redirect('users.php');
    exit();
}

// Get user data
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
    exit();
}

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-md-block sidebar">
            <div class="position-sticky">
                <h4 class="text-white text-center py-3">POS System</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sales.php">
                            <i class="bi bi-cart"></i> Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="bi bi-cart-check"></i> Cart
                            <span class="badge bg-danger"><?php echo count($_SESSION['cart'] ?? []); ?></span>
                        </a>
                    </li>
                    <?php if(isAdminOrCoAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="bi bi-box"></i> Products
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="receipts.php">
                            <i class="bi bi-receipt"></i> Receipts
                        </a>
                    </li>
                    <?php if(isAdminOrCoAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="bi bi-file-text"></i> Reports
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../actions/logout-action.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
                
                <!-- User Info -->
                <div class="mt-4 p-3 text-white" style="border-top: 1px solid #34495e;">
                    <small>
                        <i class="bi bi-person"></i> <?php echo $_SESSION['full_name']; ?>
                        <br>
                        <span class="badge bg-<?php 
                            echo $_SESSION['role'] == 'admin' ? 'danger' : 
                                ($_SESSION['role'] == 'co-admin' ? 'warning' : 'info'); 
                        ?>">
                            <?php echo ucfirst($_SESSION['role']); ?>
                        </span>
                    </small>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Edit User</h1>
                <a href="users.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
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
                    <h5>Edit User: <?php echo $user['username']; ?></h5>
                </div>
                <div class="card-body">
                    <form action="../actions/edit-user-action.php" method="POST" id="editUserForm">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" 
                                       value="<?php echo $user['username']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Full Name</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?php echo $user['full_name']; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="staff" <?php echo $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                                    <option value="co-admin" <?php echo $user['role'] == 'co-admin' ? 'selected' : ''; ?>>Co-Admin</option>
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
                        </div>

                        <hr>
                        <h6>Change Password (Leave blank to keep current password)</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="button" onclick="confirmUpdate()" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update User
                            </button>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// ============================================
// CONFIRM UPDATE WITH SWEETALERT
// ============================================
function confirmUpdate() {
    let form = document.getElementById('editUserForm');
    let password = document.querySelector('input[name="new_password"]');
    let confirm = document.querySelector('input[name="confirm_password"]');
    
    // Check if passwords match (if password is filled)
    if (password.value && password.value !== confirm.value) {
        Swal.fire({
            icon: 'error',
            title: 'Password Mismatch!',
            text: 'New password and confirm password do not match.',
            timer: 3000,
            showConfirmButton: false
        });
        return;
    }
    
    Swal.fire({
        title: 'Update User?',
        text: 'Are you sure you want to update this user?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, update!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>