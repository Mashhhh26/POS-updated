<?php
session_start();
require_once '../../config/database.php';

// ============================================
// 1. CHECK IF LOGGED IN
// ============================================
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

// ============================================
// 2. CHECK PERMISSION - PINALAWAK
// ============================================
// Allow access if:
// - User has HRM role, OR
// - User has view_attendance permission, OR
// - User is Admin
// ============================================
if (!hasRole('HRM') && !hasPermission('view_attendance') && !hasRole('Admin')) {
    logActivity("Access denied: employees.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

// ============================================
// 3. GET DATABASE CONNECTION
// ============================================
$db = getDB();

$success_message = null;
$error_message = null;

// ============================================
// 4. HANDLE DELETE EMPLOYEE
// ============================================
if (isset($_GET['delete']) && isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $id = (int)$_GET['delete'];
    
    try {
        // Get employee name for logging
        $emp = $db->prepare("SELECT first_name, last_name FROM employees WHERE id = ?");
        $emp->execute([$id]);
        $employee = $emp->fetch();
        $name = $employee['first_name'] . ' ' . $employee['last_name'];
        
        $db->prepare("DELETE FROM employees WHERE id = ?")->execute([$id]);
        logActivity("Deleted employee: {$name} (ID: {$id})");
        header('Location: ' . BASE_PATH . 'modules/hrm/employees.php?success=' . urlencode("Employee {$name} deleted!"));
    } catch(Exception $e) {
        header('Location: ' . BASE_PATH . 'modules/hrm/employees.php?error=' . urlencode($e->getMessage()));
    }
    exit();
}

// ============================================
// 5. FETCH DATA
// ============================================
$employees = $db->query("
    SELECT e.*, u.username, u.email as user_email 
    FROM employees e 
    LEFT JOIN users u ON e.user_id = u.id 
    ORDER BY e.created_at DESC
")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM employees")->fetchColumn(),
    'active' => $db->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn(),
    'on_leave' => $db->query("SELECT COUNT(*) FROM employees WHERE status = 'on_leave'")->fetchColumn(),
    'terminated' => $db->query("SELECT COUNT(*) FROM employees WHERE status = 'terminated'")->fetchColumn(),
];

// Get flash messages
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
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
                <h4 class="mb-0"><i class="fas fa-users me-2 text-primary"></i> Employee Management</h4>
                <!-- ADD BUTTON REMOVED - Employees are auto-created from Users -->
                <span class="text-muted">
                    <i class="fas fa-info-circle me-1"></i> Employees are auto-created from Users
                </span>
            </div>
            
            <?php if ($success_message): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo $success_message; ?>"></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo $error_message; ?>"></div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Employees</h6>
                            <h3 class="fw-bold text-primary"><?php echo $stats['total']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Active</h6>
                            <h3 class="fw-bold text-success"><?php echo $stats['active']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">On Leave</h6>
                            <h3 class="fw-bold text-warning"><?php echo $stats['on_leave']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Terminated</h6>
                            <h3 class="fw-bold text-danger"><?php echo $stats['terminated']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Employee Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i> Employees List</h5>
                    <small class="text-muted">Linked to Users</small>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee #</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($employees as $emp): ?>
                            <tr id="emp-row-<?php echo $emp['id']; ?>">
                                <td><strong><?php echo $emp['employee_id']; ?></strong></td>
                                <td><?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?></td>
                                <td><?php echo $emp['position']; ?></td>
                                <td><span class="badge bg-info"><?php echo $emp['department']; ?></span></td>
                                <td><?php echo $emp['email']; ?></td>
                                <td>
                                    <?php if ($emp['username']): ?>
                                    <span class="badge bg-secondary"><?php echo $emp['username']; ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">No User</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $emp['status'] === 'active' ? 'success' : ($emp['status'] === 'on_leave' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $emp['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($emp['user_id']): ?>
                                    <a href="<?php echo BASE_PATH; ?>modules/users.php?edit=<?php echo $emp['user_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $emp['id']; ?>&confirm=yes" class="btn btn-sm btn-danger delete-employee" data-id="<?php echo $emp['id']; ?>" data-name="<?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>
    
    <script>
        // Confirm Delete
        document.querySelectorAll('.delete-employee').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.href;
                const name = this.dataset.name;
                
                Swal.fire({
                    title: 'Delete Employee?',
                    text: `Are you sure you want to delete employee "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });
        });
    </script>
</body>
</html>