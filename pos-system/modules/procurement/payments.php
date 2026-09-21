<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('manage_payments')) {
    logActivity("Access denied: payments.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();
verify_csrf();

$success_message = null;
$error_message = null;

// ============================================
// PROCESS PAYMENT - SIMPLIFIED DIRECT SUBMIT
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    try {
        $invoice_id=(int)($_POST['invoice_id']??0); $amountRaw=$_POST['amount']??'';
        if($invoice_id<=0||!valid_money($amountRaw,true)) throw new Exception('Enter a valid payment amount.');
        $db->beginTransaction();
        $q=$db->prepare("SELECT * FROM invoices WHERE id=? FOR UPDATE");$q->execute([$invoice_id]);$inv=$q->fetch();
        if(!$inv) throw new Exception('Invoice not found.');
        if($inv['match_status']!=='matched') throw new Exception('Invoice must pass 3-way match before payment.');
        $paid=$db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE invoice_id=? AND status='completed'");
        $paid->execute([$invoice_id]);$already=(float)$paid->fetchColumn();$balance=round((float)$inv['net_amount']-$already,2);
        $amount=(float)$amountRaw;
        if($amount<=0||$amount>$balance) throw new Exception('Payment exceeds the remaining invoice balance of ₱'.number_format($balance,2).'.');
        $payment_number='PAY-'.date('Ymd').'-'.random_int(1000,9999);
        $method=sanitize($_POST['payment_method']??'bank_transfer');$ref=sanitize($_POST['reference_number']??'');$date=$_POST['payment_date']??date('Y-m-d');
        if($date>date('Y-m-d')) throw new Exception('Payment date cannot be in the future.');
        $db->prepare("INSERT INTO payments(payment_number,invoice_id,amount,payment_date,payment_method,reference_number,processed_by,status) VALUES(?,?,?,?,?,?,?,'completed')")
          ->execute([$payment_number,$invoice_id,$amount,$date,$method,$ref,$_SESSION['user_id']]);
        $newBalance=round($balance-$amount,2);
        if($newBalance<=0){
            $db->prepare("UPDATE invoices SET status='paid',paid_at=NOW() WHERE id=?")->execute([$invoice_id]);
            $unpaid = $db->prepare("SELECT COUNT(*) FROM invoices WHERE po_id=? AND status<>'paid'");
            $unpaid->execute([$inv['po_id']]);
            if ((int)$unpaid->fetchColumn() === 0) {
                $db->prepare("UPDATE purchase_orders SET status='closed' WHERE id=? AND status='fulfilled'")->execute([$inv['po_id']]);
            }
        } else {
            $db->prepare("UPDATE invoices SET status='pending_payment',paid_at=NULL WHERE id=?")->execute([$invoice_id]);
        }
        $db->commit();
        logActivity("Processed payment #{$payment_number} for invoice #{$invoice_id}");
        header('Location: '.BASE_PATH.'modules/procurement/payments.php?success='.urlencode("Payment #{$payment_number} processed."));exit();
    } catch(Throwable $e){if($db->inTransaction())$db->rollBack();$error_message=$e->getMessage();}
}

// ============================================
// FETCH DATA
// ============================================
$pendingInvoices = $db->query("
    SELECT i.*, po.po_number, s.company_name, s.payment_terms
    FROM invoices i
    JOIN purchase_orders po ON i.po_id = po.id
    JOIN suppliers s ON i.supplier_id = s.id
    WHERE i.match_status = 'matched' AND i.status != 'paid'
    ORDER BY i.invoice_date ASC
")->fetchAll();

$payments = $db->query("
    SELECT p.*, i.invoice_number, po.po_number, s.company_name, u.full_name as processed_by_name
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.id
    JOIN purchase_orders po ON i.po_id = po.id
    JOIN suppliers s ON i.supplier_id = s.id
    JOIN users u ON p.processed_by = u.id
    ORDER BY p.created_at DESC
    LIMIT 50
")->fetchAll();

$summary = [
    'total_paid' => $db->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'")->fetchColumn() ?: 0,
    'pending_payments' => $db->query("SELECT COUNT(*) FROM invoices WHERE match_status = 'matched' AND status != 'paid'")->fetchColumn(),
    'total_payments' => $db->query("SELECT COUNT(*) FROM payments")->fetchColumn(),
];

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
    <title>Payments</title>
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
                <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i> Payments</h4>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-plus-circle me-1"></i> New Payment
                </button>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- STATS -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Paid</h6>
                            <h3 class="fw-bold text-success">₱<?php echo number_format($summary['total_paid'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Pending Payments</h6>
                            <h3 class="fw-bold text-warning"><?php echo $summary['pending_payments']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Transactions</h6>
                            <h3 class="fw-bold text-primary"><?php echo $summary['total_payments']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- PAYMENT HISTORY -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i> Payment History</h5>
                    <small class="text-muted">Total: <?php echo count($payments); ?></small>
                </div>
                <div class="card-body table-responsive">
                    <?php if (empty($payments)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No payments processed yet.
                        </div>
                    <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Invoice</th>
                                <th>Supplier</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($payments as $p): ?>
                            <tr>
                                <td><strong><?php echo $p['payment_number']; ?></strong></td>
                                <td><?php echo $p['invoice_number']; ?></td>
                                <td><?php echo $p['company_name']; ?></td>
                                <td class="fw-bold text-success">₱<?php echo number_format($p['amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $p['payment_method'] === 'bank_transfer' ? 'primary' : ($p['payment_method'] === 'cheque' ? 'warning' : 'info'); ?>">
                                        <?php echo str_replace('_', ' ', ucfirst($p['payment_method'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $p['status'] === 'completed' ? 'success' : ($p['status'] === 'processing' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($p['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($p['payment_date'])); ?></td>
                                <td><?php echo $p['processed_by_name']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PAYMENT MODAL - NO JAVASCRIPT INTERFERENCE -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-money-bill me-2 text-success"></i> Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="paymentForm">
<?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Invoice</label>
                            <select name="invoice_id" class="form-select" required>
                                <option value="">Select invoice to pay</option>
                                <?php foreach($pendingInvoices as $inv): ?>
                                <option value="<?php echo $inv['id']; ?>" 
                                        data-amount="<?php echo $inv['net_amount']; ?>">
                                    <?php echo $inv['invoice_number']; ?> - <?php echo $inv['company_name']; ?> 
                                    (₱<?php echo number_format($inv['net_amount'], 2); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($pendingInvoices)): ?>
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i> 
                                No pending invoices for payment.
                            </small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control" id="paymentAmount" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="bank_transfer">🏦 Bank Transfer</option>
                                    <option value="cheque">📝 Cheque</option>
                                    <option value="virtual_card">💳 Virtual Card</option>
                                    <option value="cash">💵 Cash</option>
                                    <option value="gcash">📱 GCash</option>
                                    <option value="paymaya">📱 PayMaya</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Transaction/Cheque #">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="process_payment" class="btn btn-success">
                            <i class="fas fa-check-circle me-1"></i> Process Payment
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
        // Auto-fill amount only - NO form interference
        document.querySelector('select[name="invoice_id"]').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            document.getElementById('paymentAmount').value = option.dataset.amount || 0;
        });
    </script>
</body>
</html>
