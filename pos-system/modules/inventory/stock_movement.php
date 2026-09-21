<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('manage_products')) {
    logActivity("Access denied: stock_movement.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();
verify_csrf();

$success_message = null;
$error_message = null;

// Manual stock changes are requests. They never change inventory until approved.
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        $action=$_POST['action']??'';
        if(in_array($action,['stock_in','stock_out'],true)){
            require_permission('adjust_inventory');
            $pid=(int)($_POST['product_id']??0);$qty=(int)($_POST['quantity']??0);
            if($pid<=0||!valid_qty($qty,false)) throw new Exception('Quantity must be a positive whole number.');
            $ref=sanitize($_POST['reference']??'');$notes=trim($_POST['notes']??'');
            if($notes==='')throw new Exception('A reason/notes is required for stock adjustments.');
            $type=$action==='stock_in'?'in':'out';
            $db->prepare("INSERT INTO stock_adjustment_requests(product_id,user_id,type,quantity,reference,reason,status) VALUES(?,?,?,?,?,?,'pending')")
              ->execute([$pid,$_SESSION['user_id'],$type,$qty,$ref,$notes]);
            logActivity("Submitted stock {$type} adjustment request for product #{$pid}");
            header('Location: '.BASE_PATH.'modules/inventory/stock_movement.php?success='.urlencode('Stock adjustment submitted for approval.'));exit();
        }
        if($action==='review_stock'){
            if(!hasRole('Admin')&&!hasRole('Inventory')) throw new Exception('Only Inventory Manager/Admin can approve stock adjustments.');
            $id=(int)($_POST['request_id']??0);$status=$_POST['status']??'';
            if(!in_array($status,['approved','rejected'],true))throw new Exception('Invalid review action.');
            $db->beginTransaction();
            $q=$db->prepare("SELECT r.*,p.stock_quantity,p.product_name FROM stock_adjustment_requests r JOIN products p ON p.id=r.product_id WHERE r.id=? FOR UPDATE");$q->execute([$id]);$r=$q->fetch();
            if(!$r||$r['status']!=='pending')throw new Exception('Only Pending stock requests can be reviewed.');
            if($status==='approved'){
                $delta=$r['type']==='in'?(int)$r['quantity']:-((int)$r['quantity']);
                if($delta<0 && (int)$r['stock_quantity'] < abs($delta))throw new Exception('Insufficient stock for this adjustment.');
                $db->prepare("UPDATE products SET stock_quantity=stock_quantity+? WHERE id=?")->execute([$delta,$r['product_id']]);
                $db->prepare("INSERT INTO stock_movements(product_id,user_id,type,quantity,reference,notes) VALUES(?,?,?,?,?,?)")->execute([$r['product_id'],$_SESSION['user_id'],$r['type'],$r['quantity'],$r['reference'],'Approved adjustment: '.$r['reason']]);
            }
            $db->prepare("UPDATE stock_adjustment_requests SET status=?,reviewed_by=?,reviewed_at=NOW(),reviewer_note=? WHERE id=?")->execute([$status,$_SESSION['user_id'],trim($_POST['reviewer_note']??''),$id]);
            $db->commit();logActivity("Stock adjustment request #{$id} {$status}");
            header('Location: '.BASE_PATH.'modules/inventory/stock_movement.php?success='.urlencode('Stock request '.ucfirst($status).'.'));exit();
        }
    } catch(Throwable $e){if($db->inTransaction())$db->rollBack();$error_message=$e->getMessage();}
}

$products = $db->query("SELECT id, product_code, product_name, stock_quantity FROM products ORDER BY product_name")->fetchAll();
$movements = $db->query("
    SELECT sm.*, p.product_name, p.product_code, u.full_name as user 
    FROM stock_movements sm
    JOIN products p ON sm.product_id = p.id
    JOIN users u ON sm.user_id = u.id
    ORDER BY sm.created_at DESC
    LIMIT 100
")->fetchAll();

$pendingAdjustments=$db->query("SELECT r.*,p.product_name,p.product_code,u.full_name FROM stock_adjustment_requests r JOIN products p ON p.id=r.product_id JOIN users u ON u.id=r.user_id WHERE r.status='pending' ORDER BY r.created_at DESC")->fetchAll();

$stats = [
    'total_in' => $db->query("SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE type = 'in'")->fetchColumn(),
    'total_out' => $db->query("SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE type = 'out'")->fetchColumn(),
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
    <title>Stock Movement</title>
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
                <h4 class="mb-0"><i class="fas fa-exchange-alt me-2 text-primary"></i> Stock Movement</h4>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <?php if((hasRole('Inventory')||hasRole('Admin')) && $pendingAdjustments): ?><div class="card border-0 shadow-sm mb-4"><div class="card-header bg-transparent"><strong><i class="fas fa-hourglass-half me-2"></i>Pending Stock Adjustments</strong></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>Reason</th><th>Requested By</th><th>Action</th></tr></thead><tbody><?php foreach($pendingAdjustments as $r): ?><tr><td><?php echo h($r['product_name']); ?></td><td><?php echo strtoupper($r['type']); ?></td><td><?php echo (int)$r['quantity']; ?></td><td><?php echo h($r['reason']); ?></td><td><?php echo h($r['full_name']); ?></td><td><form method="post" class="d-inline"><?php echo csrf_field(); ?><input type="hidden" name="action" value="review_stock"><input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>"><input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-success">Approve</button></form> <form method="post" class="d-inline"><?php echo csrf_field(); ?><input type="hidden" name="action" value="review_stock"><input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>"><input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-danger">Reject</button></form></td></tr><?php endforeach; ?></tbody></table></div></div><?php endif; ?><div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Stock In</h6>
                            <h3 class="fw-bold text-success">+<?php echo number_format($stats['total_in']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Stock Out</h6>
                            <h3 class="fw-bold text-danger">-<?php echo number_format($stats['total_out']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0 text-success"><i class="fas fa-arrow-down me-2"></i> Stock In</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
<?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select" required>
                                        <option value="">Select Product</option>
                                        <?php foreach($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo $p['product_code']; ?> - <?php echo $p['product_name']; ?> (Stock: <?php echo $p['stock_quantity']; ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" name="quantity" class="form-control" required min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reference</label>
                                    <input type="text" name="reference" class="form-control" placeholder="e.g. PO-001, Return, etc.">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                                </div>
                                <button type="submit" name="action" value="stock_in" class="btn btn-success w-100">
                                    <i class="fas fa-plus-circle me-1"></i> Add Stock
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0 text-danger"><i class="fas fa-arrow-up me-2"></i> Stock Out</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
<?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select" required>
                                        <option value="">Select Product</option>
                                        <?php foreach($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo $p['product_code']; ?> - <?php echo $p['product_name']; ?> (Stock: <?php echo $p['stock_quantity']; ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" name="quantity" class="form-control" required min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reference</label>
                                    <input type="text" name="reference" class="form-control" placeholder="e.g. Sold, Return to supplier, etc.">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                                </div>
                                <button type="submit" name="action" value="stock_out" class="btn btn-danger w-100">
                                    <i class="fas fa-minus-circle me-1"></i> Remove Stock
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i> Stock Movement Log</h5>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>Reference</th>
                                        <th>User</th>
                                        <th>Date</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($movements as $mov): ?>
                                    <tr>
                                        <td><?php echo $mov['product_name']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $mov['type'] === 'in' ? 'success' : 'danger'; ?>">
                                                <?php echo strtoupper($mov['type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold <?php echo $mov['type'] === 'in' ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $mov['type'] === 'in' ? '+' : '-'; ?><?php echo $mov['quantity']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $mov['reference'] ?: '-'; ?></td>
                                        <td><?php echo $mov['user']; ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($mov['created_at'])); ?></td>
                                        <td><?php echo $mov['notes'] ?: '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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