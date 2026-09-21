<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_expenses')) {
    logActivity("Access denied: expenses.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    try {
        require_permission('manage_expenses');
        $category=sanitize($_POST['category']??''); $description=trim($_POST['description']??'');
        $amountRaw=$_POST['amount']??''; $expense_date=$_POST['expense_date']??date('Y-m-d');
        $payment_method=sanitize($_POST['payment_method']??'');
        if($category===''||$description===''||!valid_money($amountRaw,false)||!valid_money($amountRaw,true)) throw new Exception('Enter a valid expense amount above ₱0.00.');
        if($expense_date>date('Y-m-d')) throw new Exception('Expense date cannot be in the future.');
        $expense_number='EXP-'.date('Ymd').'-'.random_int(1000,9999);
        $stmt=$db->prepare("INSERT INTO expenses(expense_number,category,description,amount,expense_date,payment_method,created_by,status) VALUES(?,?,?,?,?,?,?,'pending')");
        $stmt->execute([$expense_number,$category,$description,(float)$amountRaw,$expense_date,$payment_method,$_SESSION['user_id']]);
        logActivity("Submitted expense #{$expense_number} for approval");
        header('Location: '.BASE_PATH.'modules/expenses.php?success='.urlencode('Expense submitted for Finance Manager approval.'));
        exit();
    } catch(Throwable $e){$error_message=$e->getMessage();}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_expense'])) {
    try {
        if(!hasRole('Finance')&&!hasRole('Admin')) throw new Exception('Only Finance Manager/Admin can approve or reject expenses.');
        $id=(int)($_POST['expense_id']??0); $status=$_POST['status']??'';
        if($id<=0||!in_array($status,['approved','rejected'],true)) throw new Exception('Invalid expense action.');
        $db->beginTransaction();
        $q=$db->prepare("SELECT * FROM expenses WHERE id=? FOR UPDATE");$q->execute([$id]);$exp=$q->fetch();
        if(!$exp||$exp['status']!=='pending') throw new Exception('Only Pending expenses can be reviewed.');
        if($status==='approved'){
            $b=$db->prepare("SELECT id,allocated_amount,used_amount FROM budgets WHERE category=? AND fiscal_year=YEAR(?) AND status='active' ORDER BY id LIMIT 1 FOR UPDATE");
            $b->execute([$exp['category'],$exp['expense_date']]);$budget=$b->fetch();
            if(!$budget) throw new Exception('No active budget is available for this expense category.');
            $newUsed=(float)$budget['used_amount']+(float)$exp['amount'];
            if($newUsed>(float)$budget['allocated_amount']) throw new Exception('Expense exceeds the remaining budget allocation.');
            $db->prepare("UPDATE budgets SET used_amount=? WHERE id=?")->execute([$newUsed,$budget['id']]);
        }
        $db->prepare("UPDATE expenses SET status=? WHERE id=? AND status='pending'")->execute([$status,$id]);
        $db->commit();
        logActivity("Expense #{$id} {$status}");
        header('Location: '.BASE_PATH.'modules/expenses.php?success='.urlencode('Expense '.ucfirst($status).'.'));
        exit();
    } catch(Throwable $e){if($db->inTransaction())$db->rollBack();$error_message=$e->getMessage();}
}

$expenses = $db->query("
    SELECT e.*, u.full_name as created_by_name 
    FROM expenses e
    JOIN users u ON e.created_by = u.id
    ORDER BY e.created_at DESC
")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE status='approved'")->fetchColumn(),
    'count' => $db->query("SELECT COUNT(*) FROM expenses")->fetchColumn(),
    'this_month' => $db->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE status='approved' AND MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE())")->fetchColumn(),
];

$categories = ['Office Supplies', 'Utilities', 'Rent', 'Salaries', 'Transportation', 'Marketing', 'Maintenance', 'Other'];
$paymentMethods = ['Cash', 'Bank Transfer', 'Credit Card', 'Cheque', 'GCash', 'PayMaya'];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses</title>
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
                <h4 class="mb-0"><i class="fas fa-money-bill me-2 text-danger"></i> Expenses</h4>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fas fa-plus-circle me-1"></i> Add Expense
                </button>
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
                            <h6 class="text-muted">Total Expenses</h6>
                            <h3 class="fw-bold text-danger">₱<?php echo number_format($stats['total'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">This Month</h6>
                            <h3 class="fw-bold text-warning">₱<?php echo number_format($stats['this_month'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Transactions</h6>
                            <h3 class="fw-bold text-primary"><?php echo $stats['count']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Created By</th><th>Status</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($expenses as $exp): ?>
                            <tr id="exp-row-<?php echo $exp['id']; ?>">
                                <td><strong><?php echo $exp['expense_number']; ?></strong></td>
                                <td><span class="badge bg-secondary"><?php echo $exp['category']; ?></span></td>
                                <td><?php echo $exp['description']; ?></td>
                                <td class="text-danger fw-bold">₱<?php echo number_format($exp['amount'], 2); ?></td>
                                <td><?php echo $exp['payment_method']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($exp['expense_date'])); ?></td>
                                <td><?php echo h($exp['created_by_name']); ?></td>
                                <td><span class="badge bg-<?php echo $exp['status']==='approved'?'success':($exp['status']==='rejected'?'danger':'warning'); ?>"><?php echo ucfirst($exp['status']); ?></span></td>
                                <td>
                                <?php if($exp['status']==='pending' && (hasRole('Finance')||hasRole('Admin'))): ?>
                                  <form method="POST" class="d-inline"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>"><input type="hidden" name="expense_id" value="<?php echo (int)$exp['id']; ?>"><input type="hidden" name="status" value="approved"><button name="review_expense" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button></form>
                                  <form method="POST" class="d-inline"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>"><input type="hidden" name="expense_id" value="<?php echo (int)$exp['id']; ?>"><input type="hidden" name="status" value="rejected"><button name="review_expense" class="btn btn-sm btn-danger"><i class="fas fa-xmark"></i></button></form>
                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-danger"></i> Add Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount (₱)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required min="0.01" max="9999999.99" step="0.01">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expense Date</label>
                                <input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select" required>
                                    <?php foreach($paymentMethods as $method): ?>
                                    <option value="<?php echo $method; ?>"><?php echo $method; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_expense" class="btn btn-danger">
                            <i class="fas fa-save me-1"></i> Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
    

</body>
</html>
