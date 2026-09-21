<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/mail.php';

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
    logActivity("Access denied: leaves.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

// ============================================
// 3. GET DATABASE CONNECTION
// ============================================
$db = getDB();
$isHrReviewer=hasRole('HRM')||hasRole('Admin')||hasPermission('manage_attendance');
$myEmployeeId=null;
if(!$isHrReviewer){$q=$db->prepare('SELECT id FROM employees WHERE user_id=? AND status<>\'terminated\' LIMIT 1');$q->execute([$_SESSION['user_id']]);$myEmployeeId=(int)($q->fetchColumn()?:0);if($myEmployeeId<=0){$error_message='No active employee record is linked to your account.';}}

$success_message = null;
$error_message = null;

// ============================================
// 4. HANDLE ADD LEAVE
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') { verify_csrf(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_leave'])) {
    try {
        $employee_id=$isHrReviewer?(int)($_POST['employee_id']??0):(int)$myEmployeeId;
        $leave_type=sanitize($_POST['leave_type']??'');
        $start_date=$_POST['start_date']??''; $end_date=$_POST['end_date']??'';
        $reason=trim($_POST['reason']??'');
        if($employee_id<=0 || !in_array($leave_type,['sick','vacation','emergency','maternity','paternity','bereavement'],true)) throw new Exception('Invalid leave request.');
        if(!$start_date||!$end_date||$end_date<$start_date) throw new Exception('Leave dates are invalid.');
        if($reason==='') throw new Exception('Leave reason is required.');
        $stmt=$db->prepare("INSERT INTO leaves(employee_id,leave_type,start_date,end_date,reason,status) VALUES(?,?,?,?,?,'pending')");
        $stmt->execute([$employee_id,$leave_type,$start_date,$end_date,$reason]);
        logActivity("Created pending leave request for employee #{$employee_id}");
        header('Location: '.BASE_PATH.'modules/hrm/leaves.php?success='.urlencode('Leave request submitted and is Pending.'));
        exit();
    } catch(Throwable $e){$error_message=$e->getMessage();}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    try {
        if(!hasRole('HRM') && !hasRole('Admin') && !hasPermission('manage_attendance')) throw new Exception('Only authorized HR reviewers can approve or reject leave requests.');
        $id=(int)($_POST['leave_id']??0); $status=$_POST['status']??'';
        if($id<=0 || !in_array($status,['approved','rejected'],true)) throw new Exception('Invalid leave action.');
        $stmt=$db->prepare("SELECT l.*,e.first_name,e.email FROM leaves l JOIN employees e ON e.id=l.employee_id WHERE l.id=? FOR UPDATE");
        $db->beginTransaction(); $stmt->execute([$id]); $leave=$stmt->fetch();
        if(!$leave) throw new Exception('Leave request not found.');
        if($leave['status']!=='pending') throw new Exception('Only Pending leave requests can be approved or rejected.');
        $db->prepare("UPDATE leaves SET status=? WHERE id=? AND status='pending'")->execute([$status,$id]);
        if($db->query('SELECT ROW_COUNT()')->fetchColumn()!=1) throw new Exception('Leave status was not changed.');
        $db->commit();
        if($leave && filter_var($leave['email']??'',FILTER_VALIDATE_EMAIL)){
            send_hr_email($leave['email'],'HRMS Leave Status: '.ucfirst($status),'<h2>Leave request update</h2><p>Hello '.h($leave['first_name']).', your leave request is now <strong>'.h(ucfirst($status)).'</strong>.</p>');
        }
        logActivity("Leave #{$id} {$status} by user ".($_SESSION['user_id']??''));
        header('Location: '.BASE_PATH.'modules/hrm/leaves.php?success='.urlencode('Leave request '.ucfirst($status).'.'));
        exit();
    } catch(Throwable $e){if($db->inTransaction())$db->rollBack();$error_message=$e->getMessage();}
}

// ============================================
// 6. FETCH DATA
// ============================================
$employees = $db->query("SELECT id, employee_id, first_name, last_name FROM employees WHERE status = 'active'")->fetchAll();
if($isHrReviewer){
    $leaves=$db->query("SELECT l.*,e.first_name,e.last_name,e.employee_id FROM leaves l JOIN employees e ON l.employee_id=e.id ORDER BY l.created_at DESC")->fetchAll();
} else {
    $q=$db->prepare("SELECT l.*,e.first_name,e.last_name,e.employee_id FROM leaves l JOIN employees e ON l.employee_id=e.id WHERE e.id=? ORDER BY l.created_at DESC");
    $q->execute([$myEmployeeId]); $leaves=$q->fetchAll();
}

$stats = [
    'pending' => $db->query("SELECT COUNT(*) FROM leaves WHERE status = 'pending'")->fetchColumn(),
    'approved' => $db->query("SELECT COUNT(*) FROM leaves WHERE status = 'approved'")->fetchColumn(),
    'rejected' => $db->query("SELECT COUNT(*) FROM leaves WHERE status = 'rejected'")->fetchColumn(),
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
    <title>Leave Management</title>
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
                <h4 class="mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i> Leave Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
                    <i class="fas fa-plus-circle me-1"></i> New Leave Request
                </button>
            </div>
            
            <?php if ($success_message): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo $success_message; ?>"></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo $error_message; ?>"></div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Pending Requests</h6>
                            <h3 class="fw-bold text-warning"><?php echo $stats['pending']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Approved</h6>
                            <h3 class="fw-bold text-success"><?php echo $stats['approved']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Rejected</h6>
                            <h3 class="fw-bold text-danger"><?php echo $stats['rejected']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Leave Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($leaves as $leave): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $leave['first_name'] . ' ' . $leave['last_name']; ?></strong>
                                    <br><small class="text-muted"><?php echo $leave['employee_id']; ?></small>
                                </td>
                                <td><span class="badge bg-info"><?php echo ucfirst($leave['leave_type']); ?></span></td>
                                <td><?php echo date('M d', strtotime($leave['start_date'])); ?></td>
                                <td><?php echo date('M d', strtotime($leave['end_date'])); ?></td>
                                <td><?php echo (strtotime($leave['end_date']) - strtotime($leave['start_date'])) / (60 * 60 * 24) + 1; ?> days</td>
                                <td><?php echo $leave['reason']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $leave['status'] === 'approved' ? 'success' : ($leave['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($leave['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isHrReviewer && $leave['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-success approve-leave" data-id="<?php echo $leave['id']; ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger reject-leave" data-id="<?php echo $leave['id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Leave Modal -->
    <div class="modal fade" id="addLeaveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus me-2 text-primary"></i> New Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">Select Employee</option>
                                <?php foreach($employees as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>">
                                    <?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?> (<?php echo $emp['employee_id']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Leave Type</label>
                            <select name="leave_type" class="form-select" required>
                                <option value="sick">Sick Leave</option>
                                <option value="vacation">Vacation Leave</option>
                                <option value="emergency">Emergency Leave</option>
                                <option value="maternity">Maternity Leave</option>
                                <option value="paternity">Paternity Leave</option>
                                <option value="bereavement">Bereavement Leave</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="alert alert-warning border-0 small mb-0"><i class="fas fa-hourglass-half me-2"></i>New leave requests always start as <strong>Pending</strong> and can only be approved or rejected by an authorized HR reviewer.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_leave" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
    
    <script>
        // Approve Leave
        document.querySelectorAll('.approve-leave').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                
                Swal.fire({
                    title: 'Approve Leave?',
                    text: 'Are you sure you want to approve this leave request?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Approve!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({update_status:'1', leave_id:id, status:'approved', csrf_token:'<?php echo h(csrf_token()); ?>'})
                        })
                        .then(r => r.text()).then(() => location.reload()).catch(()=>showError('Unable to update leave status.'));
                    }
                });
            });
        });
        
        // Reject Leave
        document.querySelectorAll('.reject-leave').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                
                Swal.fire({
                    title: 'Reject Leave?',
                    text: 'Are you sure you want to reject this leave request?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Reject!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({update_status:'1', leave_id:id, status:'rejected', csrf_token:'<?php echo h(csrf_token()); ?>'})
                        })
                        .then(r => r.text()).then(() => location.reload()).catch(()=>showError('Unable to update leave status.'));
                    }
                });
            });
        });
    </script>
</body>
</html>