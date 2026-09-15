<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_requisitions')) {
    logActivity("Access denied: procurement/dashboard.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$stats = [
    'requisitions' => $db->query("SELECT COUNT(*) FROM requisitions")->fetchColumn(),
    'pending_approval' => $db->query("SELECT COUNT(*) FROM requisitions WHERE status = 'pending_budget'")->fetchColumn(),
    'active_rfqs' => $db->query("SELECT COUNT(*) FROM rfqs WHERE status IN ('sent', 'evaluating')")->fetchColumn(),
    'pending_pos' => $db->query("SELECT COUNT(*) FROM purchase_orders WHERE status = 'pending_approval'")->fetchColumn(),
    'suppliers' => $db->query("SELECT COUNT(*) FROM suppliers WHERE is_active = 1")->fetchColumn(),
];

$recentReqs = $db->query("
    SELECT r.*, u.full_name as requester 
    FROM requisitions r 
    JOIN users u ON r.requester_id = u.id 
    ORDER BY r.created_at DESC 
    LIMIT 10
")->fetchAll();

$pendingReqs = $db->query("
    SELECT req_number, department, estimated_value, created_at 
    FROM requisitions 
    WHERE status = 'pending_budget'
    ORDER BY created_at ASC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement Dashboard</title>
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
                <h4 class="mb-0"><i class="fas fa-shopping-cart me-2 text-primary"></i> Procurement Dashboard</h4>
                <span class="text-muted"><?php echo date('F d, Y h:i A'); ?></span>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Requisitions</h6>
                            <h3 class="fw-bold text-primary"><?php echo $stats['requisitions']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Pending Approval</h6>
                            <h3 class="fw-bold text-warning"><?php echo $stats['pending_approval']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Active RFQs</h6>
                            <h3 class="fw-bold text-info"><?php echo $stats['active_rfqs']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Suppliers</h6>
                            <h3 class="fw-bold text-success"><?php echo $stats['suppliers']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i> Recent Requisitions</h5>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Req #</th>
                                        <th>Requester</th>
                                        <th>Department</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentReqs as $req): ?>
                                    <tr>
                                        <td><strong><?php echo $req['req_number']; ?></strong></td>
                                        <td><?php echo $req['requester']; ?></td>
                                        <td><?php echo $req['department']; ?></td>
                                        <td>₱<?php echo number_format($req['estimated_value'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $req['status'] === 'budget_approved' ? 'success' : ($req['status'] === 'pending_budget' ? 'warning' : 'secondary'); ?>">
                                                <?php echo str_replace('_', ' ', $req['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-clock me-2 text-warning"></i> Pending Requisitions</h5>
                        </div>
                        <div class="card-body" style="max-height:300px;overflow-y:auto;">
                            <?php if (empty($pendingReqs)): ?>
                            <p class="text-muted text-center">No pending requisitions</p>
                            <?php else: ?>
                            <?php foreach($pendingReqs as $req): ?>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <div>
                                    <strong><?php echo $req['req_number']; ?></strong>
                                    <br><small class="text-muted"><?php echo $req['department']; ?></small>
                                </div>
                                <div>
                                    <span class="badge bg-warning">₱<?php echo number_format($req['estimated_value'], 2); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
</body>
</html>