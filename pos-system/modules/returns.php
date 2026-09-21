<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_returns')) {
    logActivity("Access denied: returns.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();
verify_csrf();

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_return'])) {
    $sale_id = (int)$_POST['sale_id'];
    $reason = sanitize($_POST['reason']);
    $items = $_POST['items'] ?? [];
    
    try {
        $db->beginTransaction();
        
        $return_number = 'RET-' . date('Ymd') . '-' . rand(1000, 9999);
        $stmt = $db->prepare("INSERT INTO returns (return_number, sale_id, reason, status, created_by) 
                               VALUES (?, ?, ?, 'approved', ?)");
        $stmt->execute([$return_number, $sale_id, $reason, $_SESSION['user_id']]);
        $return_id = $db->lastInsertId();
        
        $total_refund = 0;
        foreach ($items as $item_id => $data) {
            if (isset($data['selected']) && $data['selected'] == 1) {
                $quantity = (int)$data['quantity'];
                if ($quantity > 0) {
                    $product = $db->prepare("SELECT product_id, unit_price FROM sales_items WHERE id = ?");
                    $product->execute([$item_id]);
                    $prod = $product->fetch();
                    
                    $refund = $quantity * $prod['unit_price'];
                    $total_refund += $refund;
                    
                    $stmt = $db->prepare("INSERT INTO return_items (return_id, sales_item_id, quantity, refund_amount) 
                                           VALUES (?, ?, ?, ?)");
                    $stmt->execute([$return_id, $item_id, $quantity, $refund]);
                    
                    $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
                    $stmt->execute([$quantity, $prod['product_id']]);
                }
            }
        }
        
        $db->prepare("UPDATE sales SET return_id = ? WHERE id = ?")->execute([$return_id, $sale_id]);
        
        $db->commit();
        $success_message = "Return #{$return_number} processed! Refund: ₱" . number_format($total_refund, 2);
        logActivity("Processed return #{$return_number} - ₱" . number_format($total_refund, 2));
    } catch(Exception $e) {
        $db->rollBack();
        $error_message = "Error: " . $e->getMessage();
    }
}

if (isset($_GET['get_items'])) {
    $sale_id = (int)$_GET['get_items'];
    $sale = $db->prepare("SELECT invoice_number, customer_name FROM sales WHERE id = ?");
    $sale->execute([$sale_id]);
    $saleData = $sale->fetch();
    
    $items = $db->prepare("
        SELECT si.*, p.product_name 
        FROM sales_items si
        JOIN products p ON si.product_id = p.id
        WHERE si.sale_id = ?
    ");
    $items->execute([$sale_id]);
    
    echo json_encode([
        'invoice' => $saleData['invoice_number'],
        'customer' => $saleData['customer_name'],
        'items' => $items->fetchAll()
    ]);
    exit();
}

$sales = $db->query("
    SELECT s.*, u.full_name as cashier 
    FROM sales s
    JOIN users u ON s.user_id = u.id
    WHERE s.status = 'completed' AND s.return_id IS NULL
    AND DATE(s.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ORDER BY s.created_at DESC
    LIMIT 50
")->fetchAll();

$returns = $db->query("
    SELECT r.*, s.invoice_number, u.full_name as created_by_name
    FROM returns r
    JOIN sales s ON r.sale_id = s.id
    JOIN users u ON r.created_by = u.id
    ORDER BY r.created_at DESC
    LIMIT 50
")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM returns")->fetchColumn(),
    'pending' => $db->query("SELECT COUNT(*) FROM returns WHERE status = 'pending'")->fetchColumn(),
    'refund' => $db->query("SELECT COALESCE(SUM(refund_amount), 0) FROM return_items")->fetchColumn(),
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
    <title>Sales Return</title>
    <link href="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css?v=20260913">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <h4 class="mb-4"><i class="fas fa-undo-alt me-2 text-warning"></i> Sales Return</h4>
            
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
                            <h6 class="text-muted">Total Returns</h6>
                            <h3 class="fw-bold text-warning"><?php echo $stats['total']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Pending</h6>
                            <h3 class="fw-bold text-danger"><?php echo $stats['pending']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Refunded</h6>
                            <h3 class="fw-bold text-success">₱<?php echo number_format($stats['refund'], 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-search me-2 text-primary"></i> Select Sale</h5>
                        </div>
                        <div class="card-body" style="max-height:400px;overflow-y:auto;">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($sales as $sale): ?>
                                    <tr>
                                        <td><strong><?php echo $sale['invoice_number']; ?></strong></td>
                                        <td><?php echo $sale['customer_name']; ?></td>
                                        <td>₱<?php echo number_format($sale['total_amount'] - $sale['discount'] + $sale['tax'], 2); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning select-sale" data-id="<?php echo $sale['id']; ?>">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm" id="returnForm" style="display:none;">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-undo-alt me-2 text-warning"></i> Process Return</h5>
                            <small id="saleInfo" class="text-muted"></small>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="returnFormSubmit">
<?php echo csrf_field(); ?>
                                <input type="hidden" name="sale_id" id="sale_id">
                                <div class="mb-3">
                                    <label class="form-label">Reason</label>
                                    <select name="reason" class="form-select" required>
                                        <option value="defective">Defective Product</option>
                                        <option value="wrong_item">Wrong Item</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="customer_request">Customer Request</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div id="returnItems"></div>
                                <button type="submit" name="process_return" class="btn btn-warning w-100">
                                    <i class="fas fa-check-circle me-1"></i> Process Return
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i> Return History</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Return #</th>
                                <th>Invoice</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Processed By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($returns as $ret): ?>
                            <tr>
                                <td><strong><?php echo $ret['return_number']; ?></strong></td>
                                <td><?php echo $ret['invoice_number']; ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $ret['reason'])); ?></td>
                                <td><span class="badge bg-<?php echo $ret['status'] === 'approved' ? 'success' : 'warning'; ?>"><?php echo ucfirst($ret['status']); ?></span></td>
                                <td><?php echo $ret['created_by_name']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($ret['created_at'])); ?></td>
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
        document.querySelectorAll('.select-sale').forEach(btn => {
            btn.addEventListener('click', function() {
                const saleId = this.dataset.id;
                document.getElementById('sale_id').value = saleId;
                document.getElementById('returnForm').style.display = 'block';
                
                fetch(`?get_items=${saleId}`)
                    .then(r => r.json())
                    .then(data => {
                        let html = '';
                        data.items.forEach(item => {
                            html += `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="items[${item.id}][selected]" value="1">
                                    <label class="form-check-label">
                                        ${item.product_name} - ₱${item.unit_price.toFixed(2)}
                                        <input type="number" name="items[${item.id}][quantity]" value="1" min="1" max="${item.quantity}" class="form-control form-control-sm d-inline-block" style="width:70px;">
                                    </label>
                                </div>
                            `;
                        });
                        document.getElementById('returnItems').innerHTML = html;
                        document.getElementById('saleInfo').textContent = `Sale: ${data.invoice} - Customer: ${data.customer}`;
                    });
            });
        });
        
        document.getElementById('returnFormSubmit').addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Confirm Return',
                text: 'Process this return?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Yes, Process!'
            }).then(result => {
                if (result.isConfirmed) this.submit();
            });
        });
    </script>
</body>
</html>