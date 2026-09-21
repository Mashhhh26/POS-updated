<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_attendance') && !hasRole('HRM')) {
    logActivity("Access denied: attendance.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') verify_csrf();


$isHrOrAdmin=hasRole('HRM')||hasRole('Admin')||((int)($_SESSION['role_id']??0)===1);
if($isHrOrAdmin){
    $users=$db->query("SELECT id, full_name FROM users WHERE COALESCE(is_active,1)=1 ORDER BY full_name")->fetchAll();
    $attendance=$db->query("SELECT a.*,u.full_name FROM attendance a JOIN users u ON u.id=a.user_id ORDER BY a.date DESC,a.time_in DESC LIMIT 500")->fetchAll();
} else {
    $users=[];
    $q=$db->prepare("SELECT a.*,u.full_name FROM attendance a JOIN users u ON u.id=a.user_id WHERE a.user_id=? ORDER BY a.date DESC,a.time_in DESC LIMIT 100");
    $q->execute([$_SESSION['user_id']]); $attendance=$q->fetchAll();
}

$today = date('Y-m-d');
$stats = [
    'today' => $db->query("SELECT COUNT(*) FROM attendance WHERE date = '$today'")->fetchColumn(),
    'present' => $db->query("SELECT COUNT(*) FROM attendance WHERE date = '$today' AND status = 'present'")->fetchColumn(),
    'late' => $db->query("SELECT COUNT(*) FROM attendance WHERE date = '$today' AND status = 'late'")->fetchColumn(),
    'absent' => $db->query("SELECT COUNT(*) FROM attendance WHERE date = '$today' AND status = 'absent'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
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
                <h4 class="mb-0"><i class="fas fa-clipboard-check me-2 text-primary"></i> Attendance Management</h4>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Today's Total</h6>
                            <h3 class="fw-bold text-primary"><?php echo $stats['today']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Present</h6>
                            <h3 class="fw-bold text-success"><?php echo $stats['present']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Late</h6>
                            <h3 class="fw-bold text-warning"><?php echo $stats['late']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Absent</h6>
                            <h3 class="fw-bold text-danger"><?php echo $stats['absent']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-pen me-2 text-primary"></i> Record Attendance</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="attendanceForm">
                        <?php echo csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">User</label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">Select User</option>
                                    <?php foreach($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>"><?php echo $user['full_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="present">✅ Present</option>
                                    <option value="late">⏰ Late</option>
                                    <option value="half-day">🌓 Half Day</option>
                                    <option value="absent">❌ Absent</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="Optional notes...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="add_attendance" class="btn btn-primary w-100" id="recordBtn">
                                    <i class="fas fa-save me-1"></i> Record
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i> Attendance Records</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($attendance as $record): ?>
                            <tr>
                                <td><strong><?php echo $record['full_name']; ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($record['time_in'])); ?></td>
                                <td><?php echo $record['time_out'] ? date('h:i A', strtotime($record['time_out'])) : '-'; ?></td>
                                <td>
                                    <?php
                                    $badgeClass = [
                                        'present' => 'success',
                                        'late' => 'warning',
                                        'half-day' => 'info',
                                        'absent' => 'danger'
                                    ];
                                    $badge = $badgeClass[$record['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>">
                                        <?php echo ucfirst(str_replace('-', ' ', $record['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo $record['notes'] ?: '-'; ?></td>
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
        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('recordBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            btn.disabled = true;
        });
    </script>
</body>
</html>