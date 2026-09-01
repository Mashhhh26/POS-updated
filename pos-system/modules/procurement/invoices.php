<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_invoices')) {
    logActivity("Access denied: invoices.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

// ============================================
// 3-WAY MATCH FUNCTION
// ============================================
function performThreeWayMatch($invoice_id) {
    global $db;
    
    $invoice = $db->prepare("SELECT * FROM invoices WHERE id = ?");
    $invoice->execute([$invoice_id]);
    $inv = $invoice->fetch();
    
    $grn = $db->prepare("SELECT * FROM goods_receipts WHERE po_id = ?");
    $grn->execute([$inv['po_id']]);
    $grnData = $grn->fetch();
    
    $match_status = 'pending';
    $status = 'received';
    
    if ($grnData) {
        $grnTotal = $db->prepare("SELECT SUM(accepted_quantity * pi.unit_price) as total 
                                   FROM gr_items gri 
                                   JOIN po_items pi ON gri.po_item_id = pi.id 
                                   WHERE gri.goods_receipt_id = ?");
        $grnTotal->execute([$grnData['id']]);
        $grnTotalAmount = $grnTotal->fetchColumn() ?? 0;
        
        $difference = abs($inv['net_amount'] - $grnTotalAmount);
        $tolerance = $inv['net_amount'] * 0.02;
        
        if ($difference <= $tolerance) {
            $match_status = 'matched';
            $status = 'matched';
        } else {
            $match_status = 'mismatch';
            $status = 'received';
        }
    }
    
    $stmt = $db->prepare("UPDATE invoices SET match_status = ?, status = ? WHERE id = ?");
    $stmt->execute([$match_status, $status, $invoice_id]);
    
    return $match_status;
}

// ============================================
// CREATE INVOICE
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_invoice'])) {
    $po_id = (int)$_POST['po_id'];
    $invoice_number = sanitize($_POST['invoice_number']);
    $invoice_date = $_POST['invoice_date'];
    $total_amount = (float)$_POST['total_amount'];
    $tax_amount = (float)$_POST['tax_amount'];
    $net_amount = $total_amount + $tax_amount;
    $notes = sanitize($_POST['notes']);
    
    $po = $db->prepare("SELECT supplier_id FROM purchase_orders WHERE id = ?");
    $po->execute([$po_id]);
    $supplier_id = $po->fetchColumn();
    
    if (!$supplier_id) {
        $error_message = "PO not found!";
    } else {
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare("INSERT INTO invoices (invoice_number, po_id, supplier_id, invoice_date, total_amount, tax_amount, net_amount, notes, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'received')");
            $stmt->execute([$invoice_number, $po_id, $supplier_id, $invoice_date, $total_amount, $tax_amount, $net_amount, $notes]);
            $invoice_id = $db->lastInsertId();
            
            $match_status = performThreeWayMatch($invoice_id);
            $db->commit();
            
            $success_message = "Invoice #{$invoice_number} created! Match status: " . ucfirst($match_status);
            logActivity("Created invoice #{$invoice_number} - Match: {$match_status}");
            
            header('Location: ' . BASE_PATH . 'modules/procurement/invoices.php?success=' . urlencode("Invoice #$invoice_number created!"));
            exit();
            
        } catch(Exception $e) {
            $db->rollBack();
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// ============================================
// MARK AS PAID
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_paid'])) {
    $invoice_id = (int)$_POST['invoice_id'];
    $stmt = $db->prepare("UPDATE invoices SET status = 'paid', paid_at = NOW() WHERE id = ?");
    $stmt->execute([$invoice_id]);
    $success_message = "Invoice marked as paid!";
    logActivity("Marked invoice #{$invoice_id} as paid");
    header('Location: ' . BASE_PATH . 'modules/procurement/invoices.php?success=' . urlencode("Invoice marked as paid!"));
    exit();
}

// ============================================
// REVIEW MISMATCH - FIXED
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review_mismatch'])) {
    $invoice_id = (int)$_POST['invoice_id'];
    $action = $_POST['action'];
    $remarks = sanitize($_POST['remarks']);
    
    if ($action === 'accept') {
        // Force match kahit may discrepancy
        $stmt = $db->prepare("UPDATE invoices SET match_status = 'matched', status = 'matched', notes = CONCAT(notes, ' | Force matched: ', ?) WHERE id = ?");
        $stmt->execute([$remarks, $invoice_id]);
        $success_message = "Invoice force matched!";
        logActivity("Force matched invoice #{$invoice_id}");
    } else {
        // Reject invoice
        $stmt = $db->prepare("UPDATE invoices SET status = 'rejected', notes = CONCAT(notes, ' | Rejected: ', ?) WHERE id = ?");
        $stmt->execute([$remarks, $invoice_id]);
        $success_message = "Invoice rejected!";
        logActivity("Rejected invoice #{$invoice_id}");
    }
    
    header('Location: ' . BASE_PATH . 'modules/procurement/invoices.php?success=' . urlencode($success_message));
    exit();
}

// ============================================
// FETCH DATA
// ============================================
$invoices = $db->query("
    SELECT i.*, po.po_number, s.company_name 
    FROM invoices i
    JOIN purchase_orders po ON i.po_id = po.id
    JOIN suppliers s ON i.supplier_id = s.id
    ORDER BY i.received_at DESC
")->fetchAll();

$availablePos = $db->query("
    SELECT po.*, s.company_name 
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.id
    WHERE po.status = 'fulfilled' 
    AND po.id NOT IN (SELECT po_id FROM invoices WHERE status NOT IN ('rejected'))
")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM invoices")->fetchColumn(),
    'paid' => $db->query("SELECT COUNT(*) FROM invoices WHERE status = 'paid'")->fetchColumn(),
    'matched' => $db->query("SELECT COUNT(*) FROM invoices WHERE match_status = 'matched'")->fetchColumn(),
    'mismatch' => $db->query("SELECT COUNT(*) FROM invoices WHERE match_status = 'mismatch'")->fetchColumn(),
];

if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i> Invoice Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                    <i class="fas fa-plus-circle me-1"></i> New Invoice
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
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Invoices</h6>
                            <h3 class="fw-bold text-primary"><?php echo $stats['total']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Paid</h6>
                            <h3 class="fw-bold text-success"><?php echo $stats['paid']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Matched</h6>
                            <h3 class="fw-bold text-info"><?php echo $stats['matched']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Mismatch</h6>
                            <h3 class="fw-bold text-danger"><?php echo $stats['mismatch']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- INVOICE TABLE -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i> Invoices List</h5>
                    <small class="text-muted">Total: <?php echo count($invoices); ?></small>
                </div>
                <div class="card-body table-responsive">
                    <?php if (empty($invoices)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No invoices found. 
                            <br><small>Make sure you have a <strong>fulfilled PO</strong> first.</small>
                        </div>
                    <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>PO #</th>
                                <th>Supplier</th>
                                <th>Net Amount</th>
                                <th>Match Status</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($invoices as $inv): ?>
                            <tr>
                                <td><strong><?php echo $inv['invoice_number']; ?></strong></td>
                                <td><?php echo $inv['po_number']; ?></td>
                                <td><?php echo $inv['company_name']; ?></td>
                                <td>₱<?php echo number_format($inv['net_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $inv['match_status'] === 'matched' ? 'success' : ($inv['match_status'] === 'mismatch' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($inv['match_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $inv['status'] === 'paid' ? 'success' : ($inv['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($inv['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($inv['invoice_date'])); ?></td>
                                <td>
                                    <?php if ($inv['status'] === 'matched'): ?>
                                    <button class="btn btn-sm btn-success mark-paid" data-id="<?php echo $inv['id']; ?>">
                                        <i class="fas fa-check"></i> Pay
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($inv['match_status'] === 'mismatch'): ?>
                                    <button class="btn btn-sm btn-warning review-mismatch" 
                                            data-id="<?php echo $inv['id']; ?>"
                                            data-invoice="<?php echo $inv['invoice_number']; ?>"
                                            data-supplier="<?php echo $inv['company_name']; ?>"
                                            data-amount="<?php echo number_format($inv['net_amount'], 2); ?>">
                                        <i class="fas fa-search"></i> Review
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ADD INVOICE MODAL -->
    <div class="modal fade" id="addInvoiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2 text-primary"></i> New Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Purchase Order</label>
                            <select name="po_id" class="form-select" required>
                                <option value="">Select PO</option>
                                <?php foreach($availablePos as $po): ?>
                                <option value="<?php echo $po['id']; ?>">
                                    <?php echo $po['po_number']; ?> - <?php echo $po['company_name']; ?> (₱<?php echo number_format($po['grand_total'], 2); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($availablePos)): ?>
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i> No fulfilled POs available. 
                                Please complete Goods Receipt first.
                            </small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control" required placeholder="e.g. INV-2024-001">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Invoice Date</label>
                                <input type="date" name="invoice_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Amount (₱)</label>
                                <input type="number" step="0.01" name="total_amount" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Tax Amount (₱)</label>
                            <input type="number" step="0.01" name="tax_amount" class="form-control" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_invoice" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Create Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- REVIEW MISMATCH MODAL -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-search me-2 text-warning"></i> Review Mismatch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="invoice_id" id="review_invoice_id">
                        
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>⚠️ 3-Way Match Mismatch</strong>
                            <br>
                            <small>The PO, GRN, and Invoice amounts do not match.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Invoice Details</label>
                            <div class="border p-2 rounded bg-light">
                                <p class="mb-1"><strong>Invoice:</strong> <span id="review_invoice_number"></span></p>
                                <p class="mb-1"><strong>Supplier:</strong> <span id="review_supplier"></span></p>
                                <p class="mb-0"><strong>Amount:</strong> ₱<span id="review_amount"></span></p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <select name="action" class="form-select" required>
                                <option value="accept">✅ Force Match (Accept)</option>
                                <option value="reject">❌ Reject Invoice</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Enter reason for action..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="review_mismatch" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i> Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>
    
    <script>
        // ============================================
        // MARK AS PAID
        // ============================================
        document.querySelectorAll('.mark-paid').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                
                Swal.fire({
                    title: 'Confirm Payment',
                    text: 'Are you sure you want to mark this invoice as paid?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Mark as Paid!',
                    cancelButtonText: 'Cancel'
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch('', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `mark_paid=1&invoice_id=${id}`
                        })
                        .then(() => {
                            showSuccess('Invoice marked as paid!');
                            setTimeout(() => location.reload(), 1500);
                        });
                    }
                });
            });
        });
        
        // ============================================
        // REVIEW MISMATCH - FIXED
        // ============================================
        document.querySelectorAll('.review-mismatch').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const invoice = this.dataset.invoice;
                const supplier = this.dataset.supplier;
                const amount = this.dataset.amount;
                
                document.getElementById('review_invoice_id').value = id;
                document.getElementById('review_invoice_number').textContent = invoice;
                document.getElementById('review_supplier').textContent = supplier;
                document.getElementById('review_amount').textContent = amount;
                
                new bootstrap.Modal(document.getElementById('reviewModal')).show();
            });
        });
    </script>
</body>
</html>