<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_pos')) {
    logActivity("Access denied: po_view.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$po_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$po_id) {
    header('Location: purchase_orders.php');
    exit();
}

$db = getDB();

$po = $db->prepare("
    SELECT po.*, 
           s.company_name, s.contact_person, s.email, s.phone, s.address,
           req.req_number, req.description as req_description,
           u.full_name as officer,
           f.full_name as finance_approver,
           q.quotation_number
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.id
    JOIN requisitions req ON po.requisition_id = req.id
    JOIN users u ON po.procurement_officer_id = u.id
    LEFT JOIN users f ON po.finance_approver_id = f.id
    LEFT JOIN quotations q ON po.quotation_id = q.id
    WHERE po.id = ?
");
$po->execute([$po_id]);
$poData = $po->fetch();

if (!$poData) {
    header('Location: purchase_orders.php');
    exit();
}

$items = $db->prepare("SELECT * FROM po_items WHERE po_id = ?");
$items->execute([$po_id]);
$poItems = $items->fetchAll();

$grn = $db->prepare("
    SELECT gr.*, u.full_name as receiver
    FROM goods_receipts gr
    JOIN users u ON gr.received_by = u.id
    WHERE gr.po_id = ?
");
$grn->execute([$po_id]);
$grnData = $grn->fetch();

$invoice = $db->prepare("SELECT * FROM invoices WHERE po_id = ?");
$invoice->execute([$po_id]);
$invoiceData = $invoice->fetch();

// ============================================
// FIX: Initialize paymentData as null
// ============================================
$paymentData = null;
if ($invoiceData) {
    $payment = $db->prepare("SELECT * FROM payments WHERE invoice_id = ?");
    $payment->execute([$invoiceData['id']]);
    $paymentData = $payment->fetch();
}

$performance = $db->prepare("SELECT * FROM supplier_performance WHERE po_id = ?");
$performance->execute([$po_id]);
$performanceData = $performance->fetch();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO #<?php echo $poData['po_number']; ?></title>
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
                <div>
                    <h4 class="mb-0"><i class="fas fa-file-pdf me-2 text-primary"></i> Purchase Order</h4>
                    <small class="text-muted">#<?php echo $poData['po_number']; ?></small>
                </div>
                <div>
                    <button class="btn btn-success" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
                    <a href="purchase_orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="d-flex flex-column align-items-center">
                                <div class="rounded-circle bg-<?php echo $poData['status'] != 'draft' ? 'success' : 'secondary'; ?> text-white p-2 mb-1" style="width:40px;height:40px;"><i class="fas fa-file-invoice"></i></div>
                                <small>Created</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="d-flex flex-column align-items-center">
                                <div class="rounded-circle bg-<?php echo $poData['status'] == 'approved' || $poData['status'] == 'sent' || $poData['status'] == 'acknowledged' || $poData['status'] == 'fulfilled' || $poData['status'] == 'closed' ? 'success' : 'secondary'; ?> text-white p-2 mb-1" style="width:40px;height:40px;"><i class="fas fa-check-circle"></i></div>
                                <small>Approved</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="d-flex flex-column align-items-center">
                                <div class="rounded-circle bg-<?php echo $poData['status'] == 'sent' || $poData['status'] == 'acknowledged' || $poData['status'] == 'fulfilled' || $poData['status'] == 'closed' ? 'success' : 'secondary'; ?> text-white p-2 mb-1" style="width:40px;height:40px;"><i class="fas fa-paper-plane"></i></div>
                                <small>Sent</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="d-flex flex-column align-items-center">
                                <div class="rounded-circle bg-<?php echo $poData['status'] == 'fulfilled' || $poData['status'] == 'closed' ? 'success' : 'secondary'; ?> text-white p-2 mb-1" style="width:40px;height:40px;"><i class="fas fa-warehouse"></i></div>
                                <small>Received</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="d-flex flex-column align-items-center">
                                <div class="rounded-circle bg-<?php echo $poData['status'] == 'closed' ? 'success' : 'secondary'; ?> text-white p-2 mb-1" style="width:40px;height:40px;"><i class="fas fa-check-double"></i></div>
                                <small>Closed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i> PO Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6"><strong>PO Number:</strong> <?php echo $poData['po_number']; ?></div>
                                <div class="col-md-6"><strong>Status:</strong> <span class="badge bg-<?php echo $poData['status'] === 'closed' ? 'success' : ($poData['status'] === 'approved' ? 'primary' : ($poData['status'] === 'pending_approval' ? 'warning' : 'secondary')); ?>"><?php echo str_replace('_', ' ', ucfirst($poData['status'])); ?></span></div>
                                <div class="col-md-6"><strong>Requisition:</strong> <?php echo $poData['req_number']; ?></div>
                                <div class="col-md-6"><strong>Quotation:</strong> <?php echo $poData['quotation_number'] ?: 'N/A'; ?></div>
                                <div class="col-md-6"><strong>Created:</strong> <?php echo date('M d, Y', strtotime($poData['created_at'])); ?></div>
                                <div class="col-md-6"><strong>Delivery Date:</strong> <?php echo date('M d, Y', strtotime($poData['delivery_date'])); ?></div>
                                <div class="col-md-6"><strong>Payment Terms:</strong> <?php echo $poData['payment_terms']; ?></div>
                                <div class="col-md-6"><strong>Procurement Officer:</strong> <?php echo $poData['officer']; ?></div>
                                <?php if ($poData['finance_approver']): ?>
                                <div class="col-12"><strong>Finance Approver:</strong> <?php echo $poData['finance_approver']; ?> <small class="text-muted">(<?php echo date('M d, Y', strtotime($poData['approved_at'])); ?>)</small></div>
                                <?php endif; ?>
                            </div>
                            
                            <h6 class="mt-3">Items</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Description</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-center">Received</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($poItems as $index => $item): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo $item['item_description']; ?></td>
                                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                                            <td class="text-end">₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                            <td class="text-end">₱<?php echo number_format($item['total'], 2); ?></td>
                                            <td class="text-center"><?php echo $item['received_quantity']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr><td colspan="4" class="text-end"><strong>Subtotal:</strong></td><td class="text-end">₱<?php echo number_format($poData['total_amount'], 2); ?></td><td></td></tr>
                                        <tr><td colspan="4" class="text-end"><strong>Tax:</strong></td><td class="text-end">₱<?php echo number_format($poData['tax'], 2); ?></td><td></td></tr>
                                        <tr><td colspan="4" class="text-end"><strong>Shipping:</strong></td><td class="text-end">₱<?php echo number_format($poData['shipping_cost'], 2); ?></td><td></td></tr>
                                        <tr class="table-primary"><td colspan="4" class="text-end"><strong>GRAND TOTAL:</strong></td><td class="text-end"><strong>₱<?php echo number_format($poData['grand_total'], 2); ?></strong></td><td></td></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-truck me-2 text-primary"></i> Supplier</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold"><?php echo $poData['company_name']; ?></h6>
                            <p class="mb-1"><i class="fas fa-user me-2"></i> <?php echo $poData['contact_person']; ?></p>
                            <p class="mb-1"><i class="fas fa-envelope me-2"></i> <?php echo $poData['email']; ?></p>
                            <p class="mb-1"><i class="fas fa-phone me-2"></i> <?php echo $poData['phone']; ?></p>
                            <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> <?php echo $poData['address']; ?></p>
                        </div>
                    </div>
                    
                    <?php if ($grnData): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-warehouse me-2 text-success"></i> Goods Receipt</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>GRN #:</strong> <?php echo $grnData['grn_number']; ?></p>
                            <p><strong>Received By:</strong> <?php echo $grnData['receiver']; ?></p>
                            <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($grnData['receipt_date'])); ?></p>
                            <p><strong>Status:</strong> <span class="badge bg-<?php echo $grnData['status'] === 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($grnData['status']); ?></span></p>
                            <?php if ($grnData['delivery_note']): ?><p><strong>Delivery Note:</strong> <?php echo $grnData['delivery_note']; ?></p><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($invoiceData): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-file-invoice me-2 text-warning"></i> Invoice</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Invoice #:</strong> <?php echo $invoiceData['invoice_number']; ?></p>
                            <p><strong>Amount:</strong> ₱<?php echo number_format($invoiceData['net_amount'], 2); ?></p>
                            <p><strong>Status:</strong> <span class="badge bg-<?php echo $invoiceData['status'] === 'paid' ? 'success' : ($invoiceData['match_status'] === 'matched' ? 'primary' : 'warning'); ?>"><?php echo ucfirst($invoiceData['status']); ?></span></p>
                            <?php if ($invoiceData['match_status'] === 'matched'): ?><p><span class="badge bg-success">✅ 3-Way Match Verified</span></p><?php elseif ($invoiceData['match_status'] === 'mismatch'): ?><p><span class="badge bg-danger">⚠️ Match Mismatch</span></p><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- ============================================ -->
                    <!-- PAYMENT - FIXED -->
                    <!-- ============================================ -->
                    <?php if (isset($paymentData) && $paymentData): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i> Payment</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Payment #:</strong> <?php echo $paymentData['payment_number']; ?></p>
                            <p><strong>Amount:</strong> ₱<?php echo number_format($paymentData['amount'], 2); ?></p>
                            <p><strong>Method:</strong> <?php echo str_replace('_', ' ', ucfirst($paymentData['payment_method'])); ?></p>
                            <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($paymentData['payment_date'])); ?></p>
                            <p><strong>Status:</strong> <span class="badge bg-<?php echo $paymentData['status'] === 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($paymentData['status']); ?></span></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($performanceData): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-star me-2 text-warning"></i> Performance Rating</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="display-4 mb-2"><?php $rating = $performanceData['overall_rating']; $stars = round($rating); for($i = 1; $i <= 5; $i++) { echo $i <= $stars ? '⭐' : '☆'; } ?></div>
                            <h4><?php echo number_format($rating, 1); ?> / 5.0</h4>
                            <div class="row mt-3">
                                <div class="col-4"><small>OTIF</small><div><strong><?php echo $performanceData['otif_score']; ?></strong></div></div>
                                <div class="col-4"><small>Quality</small><div><strong><?php echo $performanceData['quality_score']; ?></strong></div></div>
                                <div class="col-4"><small>Responsiveness</small><div><strong><?php echo $performanceData['responsiveness_score']; ?></strong></div></div>
                            </div>
                            <?php if ($performanceData['comments']): ?><p class="text-muted mt-2"><small>"<?php echo $performanceData['comments']; ?>"</small></p><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
</body>
</html>