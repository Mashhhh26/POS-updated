<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_budget')) {
    logActivity("Access denied: budget.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

$budgets = $db->query("SELECT * FROM budgets ORDER BY fiscal_year DESC, department")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COALESCE(SUM(allocated_amount), 0) FROM budgets WHERE status = 'active'")->fetchColumn(),
    'used' => $db->query("SELECT COALESCE(SUM(used_amount), 0) FROM budgets WHERE status = 'active'")->fetchColumn(),
    'remaining' => 0,
];

$stats['remaining'] = $stats['total'] - $stats['used'];

$departments = ['IT', 'HR', 'Finance', 'Operations', 'Sales', 'Marketing', 'Procurement', 'Warehouse'];
$categories = ['Salaries', 'Equipment', 'Supplies', 'Training', 'Travel', 'Maintenance', 'Software', 'Other'];
$years = [date('Y'), date('Y')+1, date('Y')+2];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management</title>
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
                <h4 class="mb-0"><i class="fas fa-coins me-2 text-success"></i> Budget Management</h4>
                <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-eye me-1"></i> View only</span>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Budget</h6>
                            <h3 class="fw-bold text-primary">₱<?php echo number_format($stats['total'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Used</h6>
                            <h3 class="fw-bold text-warning">₱<?php echo number_format($stats['used'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Remaining</h6>
                            <h3 class="fw-bold text-success">₱<?php echo number_format($stats['remaining'], 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Budget Code</th>
                                <th>Department</th>
                                <th>Category</th>
                                <th>Allocated</th>
                                <th>Used</th>
                                <th>Remaining</th>
                                <th>Year</th>
                                <th>Status</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($budgets as $b): ?>
                            <tr id="budget-row-<?php echo $b['id']; ?>">
                                <td><strong><?php echo $b['budget_code']; ?></strong></td>
                                <td><?php echo $b['department']; ?></td>
                                <td><?php echo $b['category']; ?></td>
                                <td>₱<?php echo number_format($b['allocated_amount'], 2); ?></td>
                                <td>₱<?php echo number_format($b['used_amount'], 2); ?></td>
                                <td>
                                    <?php 
                                    $remaining = $b['allocated_amount'] - $b['used_amount'];
                                    $percent = $b['allocated_amount'] > 0 ? ($b['used_amount'] / $b['allocated_amount']) * 100 : 0;
                                    ?>
                                    <span class="badge bg-<?php echo $percent < 70 ? 'success' : ($percent < 90 ? 'warning' : 'danger'); ?>">
                                        ₱<?php echo number_format($remaining, 2); ?>
                                    </span>
                                </td>
                                <td><?php echo $b['fiscal_year']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $b['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($b['status']); ?>
                                    </span>
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
    

</body>
</html>