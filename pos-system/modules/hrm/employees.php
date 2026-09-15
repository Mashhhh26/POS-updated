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
if(isset($_GET['attendance'])){header('Content-Type: application/json');$q=$db->prepare('SELECT date,time_in,time_out,status,notes FROM attendance WHERE user_id=? ORDER BY date DESC LIMIT 100');$q->execute([(int)$_GET['attendance']]);echo json_encode(['records'=>$q->fetchAll(PDO::FETCH_ASSOC)]);exit();}

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
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link href="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css?v=20260913">
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
                                    <?php if($emp['user_id']): ?><button class="btn btn-sm btn-info attendance-btn" data-user="<?php echo $emp['user_id']; ?>" data-name="<?php echo htmlspecialchars($emp['first_name'].' '.$emp['last_name'],ENT_QUOTES); ?>"><i class="fas fa-clock"></i></button><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
    
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
<div class="modal fade" id="attendanceModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Attendance — <span id="attName"></span></h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="attBody">Loading...</div></div></div></div><script>document.querySelectorAll('.attendance-btn').forEach(b=>b.onclick=async()=>{document.getElementById('attName').textContent=b.dataset.name;let m=new bootstrap.Modal(document.getElementById('attendanceModal'));m.show();let r=await fetch('?attendance='+b.dataset.user),d=await r.json();document.getElementById('attBody').innerHTML=d.records.length?'<div class="table-responsive"><table class="table"><tr><th>Date</th><th>In</th><th>Out</th><th>Status</th><th>Notes</th></tr>'+d.records.map(x=>`<tr><td>${x.date||''}</td><td>${x.time_in||'—'}</td><td>${x.time_out||'—'}</td><td>${x.status||''}</td><td>${x.notes||''}</td></tr>`).join('')+'</table></div>':'<div class="alert alert-info">No records.</div>'});</script></body>
</html>