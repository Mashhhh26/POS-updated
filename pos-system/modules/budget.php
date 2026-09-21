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

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    try {
        $action=$_POST['action']??'';
        if($action==='add_budget'){
            require_permission('manage_budget');
            $dept=sanitize($_POST['department']??''); $cat=sanitize($_POST['category']??'');
            $amount=$_POST['allocated_amount']??''; $year=(int)($_POST['fiscal_year']??0);
            if($dept===''||$cat===''||!valid_money($amount,false)||!valid_money($amount,true)||$year<date('Y')||$year>date('Y')+5) throw new Exception('Invalid budget details.');
            $code='BUD-'.date('YmdHis').'-'.random_int(100,999);
            $db->prepare("INSERT INTO budgets(budget_code,department,category,allocated_amount,used_amount,fiscal_year,status,created_by) VALUES(?,?,?,?,0,?,'pending_approval',?)")
              ->execute([$code,$dept,$cat,(float)$amount,$year,$_SESSION['user_id']]);
            logActivity("Submitted budget {$code} for approval");
            header('Location: '.BASE_PATH.'modules/budget.php?success='.urlencode('Budget submitted for Finance Manager approval.')); exit();
        }
        if($action==='review_budget'){
            if(!hasRole('Finance')&&!hasRole('Admin')) throw new Exception('Only Finance Manager/Admin can review budgets.');
            $id=(int)($_POST['budget_id']??0);$status=$_POST['status']??'';
            if($id<=0||!in_array($status,['active','rejected'],true)) throw new Exception('Invalid budget review.');
            $q=$db->prepare("SELECT * FROM budgets WHERE id=?");$q->execute([$id]);$b=$q->fetch();
            if(!$b||$b['status']!=='pending_approval') throw new Exception('Only Pending Approval budgets can be reviewed.');
            $db->prepare("UPDATE budgets SET status=?,approved_by=?,approved_at=NOW(),rejection_reason=? WHERE id=?")->execute([$status,$_SESSION['user_id'],$status==='rejected'?trim($_POST['reason']??''):null,$id]);
            logActivity("Budget #{$id} ".($status==='active'?'approved':'rejected'));
            header('Location: '.BASE_PATH.'modules/budget.php?success='.urlencode('Budget '.($status==='active'?'approved':'rejected').'.')); exit();
        }
    } catch(Throwable $e){$error_message=$e->getMessage();}
}

$budgets = $db->query("SELECT * FROM budgets ORDER BY fiscal_year DESC, department")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COALESCE(SUM(allocated_amount), 0) FROM budgets WHERE status = 'active'")->fetchColumn(),
    'used' => $db->query("SELECT COALESCE(SUM(used_amount), 0) FROM budgets WHERE status = 'active'")->fetchColumn(),
    'remaining' => 0,
    'revenue' => $db->query("SELECT COALESCE(SUM(total_amount - discount + tax),0) FROM sales WHERE status='completed'")->fetchColumn(),
];

$stats['remaining'] = $stats['total'] - $stats['used'];

$departments = ['IT', 'HR', 'Finance', 'Operations', 'Sales', 'Marketing', 'Procurement', 'Warehouse'];
$categories = ['Salaries', 'Equipment', 'Supplies', 'Training', 'Travel', 'Maintenance', 'Software', 'Other'];
$years = [date('Y'), date('Y')+1, date('Y')+2];
if(isset($_GET['success'])) $success_message=htmlspecialchars($_GET['success']);
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
                <?php if(hasPermission('manage_budget')): ?><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal"><i class="fas fa-plus me-1"></i> Add Budget</button><?php endif; ?>
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
                            <h6 class="text-muted">Total Budget</h6>
                            <h3 class="fw-bold text-primary">₱<?php echo number_format($stats['total'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Used</h6>
                            <h3 class="fw-bold text-warning">₱<?php echo number_format($stats['used'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><h6 class="text-muted">Remaining</h6><h3 class="fw-bold text-success">₱<?php echo number_format($stats['remaining'], 2); ?></h3></div></div></div>
                <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><h6 class="text-muted">Company Revenue</h6><h3 class="fw-bold text-info">₱<?php echo number_format($stats['revenue'], 2); ?></h3></div></div></div>
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
                                <th>Status</th><th>Actions</th>
                                
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
                                <td><span class="badge bg-<?php echo $b['status']==='active'?'success':($b['status']==='pending_approval'?'warning':'danger'); ?>"><?php echo h(ucwords(str_replace('_',' ',$b['status']))); ?></span></td>
                                <td><?php if($b['status']==='pending_approval' && (hasRole('Finance')||hasRole('Admin'))): ?>
                                <form method="POST" class="d-inline"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>"><input type="hidden" name="action" value="review_budget"><input type="hidden" name="budget_id" value="<?php echo (int)$b['id']; ?>"><input type="hidden" name="status" value="active"><button name="review_budget_submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button></form>
                                <form method="POST" class="d-inline"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>"><input type="hidden" name="action" value="review_budget"><input type="hidden" name="budget_id" value="<?php echo (int)$b['id']; ?>"><input type="hidden" name="status" value="rejected"><button name="review_budget_submit" class="btn btn-sm btn-danger"><i class="fas fa-xmark"></i> Reject</button></form>
                                <?php else: ?>—<?php endif; ?></td>
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
    


<div class="modal fade" id="addBudgetModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="fas fa-coins me-2"></i> Add Budget</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>"><input type="hidden" name="action" value="add_budget"><div class="modal-body">
<label class="form-label">Department</label><select name="department" class="form-select mb-3" required><?php foreach($departments as $x): ?><option><?php echo h($x); ?></option><?php endforeach; ?></select>
<label class="form-label">Category</label><select name="category" class="form-select mb-3" required><?php foreach($categories as $x): ?><option><?php echo h($x); ?></option><?php endforeach; ?></select>
<label class="form-label">Allocated Amount</label><input type="number" name="allocated_amount" class="form-control mb-3" min="0.01" max="9999999.99" step="0.01" required>
<label class="form-label">Fiscal Year</label><select name="fiscal_year" class="form-select"><?php foreach($years as $y): ?><option value="<?php echo $y; ?>"><?php echo $y; ?></option><?php endforeach; ?></select>
<div class="alert alert-warning small mt-3 mb-0">New budgets remain <strong>Pending Approval</strong> until reviewed by Finance Manager/Admin.</div></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button name="add_budget" class="btn btn-primary">Submit Budget</button></div></form></div></div></div>

</body>
</html>