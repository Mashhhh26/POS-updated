<?php
session_start();
require_once '../config/database.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

// Check permission
if (!hasPermission('view_users')) {
    logActivity("Access denied: users.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

// ============================================
// HANDLE ADD USER - WITH AUTO-CREATE EMPLOYEE
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    if (!hasPermission('create_users')) {
        $error_message = "You don't have permission to create users!";
    } else {
        $username = sanitize($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $email = sanitize($_POST['email']);
        $full_name = sanitize($_POST['full_name']);
        $role_id = (int)$_POST['role_id'];
        
        // Employee fields
        $employee_id = sanitize($_POST['employee_id']);
        $position = sanitize($_POST['position']);
        $department = sanitize($_POST['department']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $hire_date = $_POST['hire_date'];
        $salary = (float)$_POST['salary'];
        $emp_status = sanitize($_POST['emp_status']);
        
        if (empty($username) || empty($password) || empty($email) || empty($full_name) || empty($role_id)) {
            $error_message = "All fields are required!";
        } else {
            try {
                $db->beginTransaction();
                
                // 1. Check if username already exists
                $check = $db->prepare("SELECT id FROM users WHERE username = ?");
                $check->execute([$username]);
                if ($check->rowCount() > 0) {
                    $error_message = "Username '{$username}' already exists!";
                    $db->rollBack();
                } else {
                    // 2. Insert User
                    $stmt = $db->prepare("INSERT INTO users (username, password, email, full_name, role_id, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$username, $password, $email, $full_name, $role_id]);
                    $user_id = $db->lastInsertId();
                    
                    // 3. Auto-create Employee record
                    $emp_id = !empty($employee_id) ? $employee_id : 'EMP-' . date('Ymd') . '-' . rand(1000, 9999);
                    $emp_position = !empty($position) ? $position : 'Staff';
                    $emp_department = !empty($department) ? $department : 'Operations';
                    $emp_hire_date = !empty($hire_date) ? $hire_date : date('Y-m-d');
                    $emp_salary = !empty($salary) ? $salary : 0;
                    $emp_status = !empty($emp_status) ? $emp_status : 'active';
                    
                    $stmt = $db->prepare("INSERT INTO employees (employee_id, first_name, last_name, email, phone, address, position, department, hire_date, salary, status, user_id) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $emp_id,
                        $full_name,
                        '',
                        $email,
                        $phone ?? '',
                        $address ?? '',
                        $emp_position,
                        $emp_department,
                        $emp_hire_date,
                        $emp_salary,
                        $emp_status,
                        $user_id
                    ]);
                    
                    $db->commit();
                    $success_message = "User <strong>{$username}</strong> created! Employee record auto-created.";
                    logActivity("Created user: {$username} with employee record");
                    header('Location: ' . BASE_PATH . 'modules/users.php?success=' . urlencode("User {$username} created! Employee auto-created."));
                    exit();
                }
            } catch(PDOException $e) {
                $db->rollBack();
                $error_message = "Database Error: " . $e->getMessage();
            }
        }
    }
}

// ============================================
// HANDLE EDIT USER - SYNC WITH EMPLOYEE
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    if (!hasPermission('edit_users')) {
        $error_message = "You don't have permission to edit users!";
    } else {
        $id = (int)$_POST['user_id'];
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $full_name = sanitize($_POST['full_name']);
        $role_id = (int)$_POST['role_id'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Employee fields
        $position = sanitize($_POST['position']);
        $department = sanitize($_POST['department']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $salary = (float)$_POST['salary'];
        $emp_status = sanitize($_POST['emp_status']);
        
        try {
            $db->beginTransaction();
            
            // Check if username already exists for other users
            $check = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check->execute([$username, $id]);
            if ($check->rowCount() > 0) {
                $error_message = "Username '{$username}' already exists!";
                $db->rollBack();
            } else {
                // 1. Update User
                $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, role_id = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$username, $email, $full_name, $role_id, $is_active, $id]);
                
                // 2. Update Employee (if exists)
                $stmt = $db->prepare("UPDATE employees SET 
                                       first_name = ?, 
                                       email = ?, 
                                       phone = ?, 
                                       address = ?, 
                                       position = ?, 
                                       department = ?, 
                                       salary = ?, 
                                       status = ? 
                                       WHERE user_id = ?");
                $stmt->execute([$full_name, $email, $phone, $address, $position, $department, $salary, $emp_status, $id]);
                
                $db->commit();
                $success_message = "User and Employee updated successfully!";
                logActivity("Updated user ID: {$id} and employee record");
                header('Location: ' . BASE_PATH . 'modules/users.php?success=' . urlencode('User and Employee updated!'));
                exit();
            }
        } catch(PDOException $e) {
            $db->rollBack();
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// ============================================
// HANDLE DELETE USER - WITH CASCADE
// ============================================
if (isset($_GET['delete']) && isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $id = (int)$_GET['delete'];
    
    if ($id == 1) {
        header('Location: ' . BASE_PATH . 'modules/users.php?error=' . urlencode('Cannot delete admin user!'));
        exit();
    }
    
    if (!hasPermission('delete_users')) {
        header('Location: ' . BASE_PATH . 'modules/users.php?error=' . urlencode('You don\'t have permission to delete users!'));
        exit();
    }
    
    try {
        $db->beginTransaction();
        
        // Get username for logging
        $user = $db->prepare("SELECT username FROM users WHERE id = ?");
        $user->execute([$id]);
        $username = $user->fetchColumn();
        
        // Delete employee record first (foreign key)
        $db->prepare("DELETE FROM employees WHERE user_id = ?")->execute([$id]);
        
        // Delete related records
        $db->prepare("DELETE FROM activity_logs WHERE user_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM attendance WHERE user_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM backup_logs WHERE created_by = ?")->execute([$id]);
        $db->prepare("DELETE FROM stock_movements WHERE user_id = ?")->execute([$id]);
        
        // Delete sales
        $sales = $db->prepare("SELECT id FROM sales WHERE user_id = ?");
        $sales->execute([$id]);
        while ($sale = $sales->fetch()) {
            $db->prepare("DELETE FROM sales_items WHERE sale_id = ?")->execute([$sale['id']]);
        }
        $db->prepare("DELETE FROM sales WHERE user_id = ?")->execute([$id]);
        
        // Delete requisitions
        $reqs = $db->prepare("SELECT id FROM requisitions WHERE requester_id = ? OR budget_owner_id = ?");
        $reqs->execute([$id, $id]);
        while ($req = $reqs->fetch()) {
            $db->prepare("DELETE FROM requisition_items WHERE requisition_id = ?")->execute([$req['id']]);
        }
        $db->prepare("DELETE FROM requisitions WHERE requester_id = ? OR budget_owner_id = ?")->execute([$id, $id]);
        
        // Delete other related records
        $db->prepare("DELETE FROM rfqs WHERE procurement_officer_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM purchase_orders WHERE procurement_officer_id = ? OR finance_approver_id = ?")->execute([$id, $id]);
        $db->prepare("DELETE FROM goods_receipts WHERE received_by = ?")->execute([$id]);
        $db->prepare("DELETE FROM payments WHERE processed_by = ?")->execute([$id]);
        $db->prepare("DELETE FROM supplier_performance WHERE evaluated_by = ?")->execute([$id]);
        $db->prepare("DELETE FROM returns WHERE created_by = ?")->execute([$id]);
        $db->prepare("DELETE FROM expenses WHERE created_by = ?")->execute([$id]);
        
        // Finally, delete the user
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        
        $db->commit();
        logActivity("Deleted user: {$username} (ID: {$id}) with all related records");
        header('Location: ' . BASE_PATH . 'modules/users.php?success=' . urlencode("User {$username} deleted successfully!"));
    } catch(Exception $e) {
        $db->rollBack();
        header('Location: ' . BASE_PATH . 'modules/users.php?error=' . urlencode($e->getMessage()));
    }
    exit();
}

// ============================================
// HANDLE TOGGLE USER STATUS
// ============================================
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    
    if (!hasPermission('edit_users')) {
        header('Location: ' . BASE_PATH . 'modules/users.php?error=' . urlencode('You don\'t have permission to edit users!'));
        exit();
    }
    
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        
        // Also update employee status
        $user = $db->prepare("SELECT is_active FROM users WHERE id = ?");
        $user->execute([$id]);
        $is_active = $user->fetchColumn();
        $emp_status = $is_active ? 'active' : 'terminated';
        $db->prepare("UPDATE employees SET status = ? WHERE user_id = ?")->execute([$emp_status, $id]);
        
        $db->commit();
        header('Location: ' . BASE_PATH . 'modules/users.php?success=' . urlencode('User status updated!'));
    } catch(Exception $e) {
        $db->rollBack();
        header('Location: ' . BASE_PATH . 'modules/users.php?error=' . urlencode($e->getMessage()));
    }
    exit();
}

// ============================================
// FETCH USER DATA FOR EDIT
// ============================================
$edit_user = null;
$edit_employee = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $edit_user = $stmt->fetch();
    
    if ($edit_user) {
        $stmt = $db->prepare("SELECT * FROM employees WHERE user_id = ?");
        $stmt->execute([$id]);
        $edit_employee = $stmt->fetch();
    }
}

// ============================================
// FETCH ALL USERS WITH EMPLOYEE INFO
// ============================================
$users = $db->query("
    SELECT u.*, r.role_name, e.id as emp_id, e.employee_id, e.position, e.department, e.status as emp_status 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    LEFT JOIN employees e ON u.id = e.user_id
    ORDER BY u.id
")->fetchAll();

$roles = $db->query("SELECT * FROM roles ORDER BY role_name")->fetchAll();

// Departments and positions
$departments = ['IT', 'HR', 'Finance', 'Operations', 'Sales', 'Marketing', 'Procurement', 'Warehouse'];
$positions = ['Manager', 'Supervisor', 'Staff', 'Assistant', 'Officer', 'Analyst', 'Specialist', 'Coordinator'];

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
    <title>User Management</title>
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
                <h4 class="mb-0"><i class="fas fa-users me-2 text-primary"></i> User Management</h4>
                <?php if (hasPermission('create_users')): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-1"></i> Add User
                </button>
                <?php endif; ?>
            </div>
            
            <!-- Flash Messages -->
            <?php if ($success_message): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo $success_message; ?>"></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo $error_message; ?>"></div>
            <?php endif; ?>
            
            <!-- Edit User Modal -->
            <?php if ($edit_user): ?>
            <div class="modal fade show" id="editUserModal" tabindex="-1" style="display:block; background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit me-2 text-warning"></i> Edit User & Employee</h5>
                            <a href="<?php echo BASE_PATH; ?>modules/users.php" class="btn-close"></a>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                                
                                <h6 class="border-bottom pb-2">User Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($edit_user['username']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Role</label>
                                        <select name="role_id" class="form-select" required>
                                            <?php foreach($roles as $role): ?>
                                                <option value="<?php echo $role['id']; ?>" <?php echo $role['id'] == $edit_user['role_id'] ? 'selected' : ''; ?>>
                                                    <?php echo $role['role_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?php echo $edit_user['is_active'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <h6 class="border-bottom pb-2 mt-3">Employee Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Employee ID</label>
                                        <input type="text" name="employee_id" class="form-control" value="<?php echo htmlspecialchars($edit_employee['employee_id'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Position</label>
                                        <select name="position" class="form-select">
                                            <option value="">Select Position</option>
                                            <?php foreach($positions as $pos): ?>
                                            <option value="<?php echo $pos; ?>" <?php echo ($edit_employee['position'] ?? '') == $pos ? 'selected' : ''; ?>><?php echo $pos; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Department</label>
                                        <select name="department" class="form-select">
                                            <option value="">Select Department</option>
                                            <?php foreach($departments as $dept): ?>
                                            <option value="<?php echo $dept; ?>" <?php echo ($edit_employee['department'] ?? '') == $dept ? 'selected' : ''; ?>><?php echo $dept; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($edit_employee['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Salary (₱)</label>
                                        <input type="number" step="0.01" name="salary" class="form-control" value="<?php echo htmlspecialchars($edit_employee['salary'] ?? 0); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Status</label>
                                        <select name="emp_status" class="form-select">
                                            <option value="active" <?php echo ($edit_employee['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="on_leave" <?php echo ($edit_employee['status'] ?? '') == 'on_leave' ? 'selected' : ''; ?>>On Leave</option>
                                            <option value="terminated" <?php echo ($edit_employee['status'] ?? '') == 'terminated' ? 'selected' : ''; ?>>Terminated</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($edit_employee['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="<?php echo BASE_PATH; ?>modules/users.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="edit_user" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Users Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i> Users List</h5>
                    <small class="text-muted">Total: <?php echo count($users); ?> users</small>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Employee #</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No users found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($users as $user): ?>
                            <tr id="user-row-<?php echo $user['id']; ?>">
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($user['role_name']); ?></span></td>
                                <td><?php echo htmlspecialchars($user['employee_id'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (hasPermission('edit_users')): ?>
                                    <a href="?toggle=<?php echo $user['id']; ?>" class="btn btn-sm <?php echo $user['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                        <i class="fas <?php echo $user['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('edit_users')): ?>
                                    <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('delete_users') && $user['id'] != 1): ?>
                                    <a href="?delete=<?php echo $user['id']; ?>&confirm=yes" class="btn btn-sm btn-danger delete-user" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['username']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i> Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <h6 class="border-bottom pb-2">User Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role_id" class="form-select" required>
                                    <option value="">Select Role</option>
                                    <?php foreach($roles as $role): ?>
                                        <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <h6 class="border-bottom pb-2 mt-3">Employee Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Employee ID</label>
                                <input type="text" name="employee_id" class="form-control" placeholder="EMP-001 (auto if empty)">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Position</label>
                                <select name="position" class="form-select">
                                    <option value="">Select Position</option>
                                    <?php foreach($positions as $pos): ?>
                                    <option value="<?php echo $pos; ?>"><?php echo $pos; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Department</label>
                                <select name="department" class="form-select">
                                    <option value="">Select Department</option>
                                    <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Hire Date</label>
                                <input type="date" name="hire_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Salary (₱)</label>
                                <input type="number" step="0.01" name="salary" class="form-control" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employee Status</label>
                                <select name="emp_status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="on_leave">On Leave</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_user" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>
    
    <script>
        // ============================================
        // CONFIRM DELETE WITH SWEETALERT
        // ============================================
        document.querySelectorAll('.delete-user').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.href;
                const name = this.dataset.name;
                
                Swal.fire({
                    title: 'Delete User?',
                    html: `
                        <div class="text-start">
                            <p><strong>User:</strong> ${name}</p>
                            <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i> This will also delete:</p>
                            <ul class="text-start text-muted">
                                <li>Employee record</li>
                                <li>Activity logs</li>
                                <li>Attendance records</li>
                                <li>Sales transactions</li>
                                <li>And more...</li>
                            </ul>
                            <p class="text-danger fw-bold">This action cannot be undone!</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete User!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });
        });
        
        // ============================================
        // CONFIRM TOGGLE STATUS
        // ============================================
        document.querySelectorAll('.btn-warning, .btn-success').forEach(btn => {
            if (btn.querySelector('.fa-pause') || btn.querySelector('.fa-play')) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.href;
                    const isActive = this.querySelector('.fa-pause') !== null;
                    const action = isActive ? 'deactivate' : 'activate';
                    
                    Swal.fire({
                        title: `${action.charAt(0).toUpperCase() + action.slice(1)} User?`,
                        text: `Are you sure you want to ${action} this user?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: `Yes, ${action}!`,
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = href;
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>