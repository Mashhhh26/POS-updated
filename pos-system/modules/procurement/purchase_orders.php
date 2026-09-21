<?php
session_start();
require_once '../../config/database.php';
verify_csrf();

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_pos')) {
    logActivity("Access denied: purchase_orders.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

// Create PO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_po'])) {
    $po_number = 'PO-' . date('Ymd') . '-' . rand(1000, 9999);
    $requisition_id = (int)$_POST['requisition_id'];
    $supplier_id = (int)$_POST['supplier_id'];
    $quotation_id = (int)$_POST['quotation_id'];
    $total_amount = $_POST['total_amount'] ?? '';
    $tax = $_POST['tax'] ?? '';
    $shipping_cost = $_POST['shipping_cost'] ?? '';
    if (!valid_money($total_amount, true) || !valid_money($tax, true) || !valid_money($shipping_cost, true)) {
        $error_message = 'Invalid monetary value. Values must be between 0.00 and 9,999,999.99.';
    }
    $total_amount = (float)$total_amount;
    $tax = (float)$tax;
    $shipping_cost = (float)$shipping_cost;
    $grand_total = $total_amount + $tax + $shipping_cost;
    $payment_terms = sanitize($_POST['payment_terms']);
    $delivery_date = $_POST['delivery_date'];
    $procurement_officer_id = $_SESSION['user_id'];
    
    if ($error_message !== null) { goto skip_create_po; }
    $needs_approval = $grand_total > 50000;
    $status = $needs_approval ? 'pending_approval' : 'approved';
    
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("INSERT INTO purchase_orders 
                               (po_number, requisition_id, supplier_id, quotation_id, total_amount, tax, shipping_cost, grand_total, payment_terms, delivery_date, status, procurement_officer_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$po_number, $requisition_id, $supplier_id, $quotation_id, $total_amount, $tax, $shipping_cost, $grand_total, $payment_terms, $delivery_date, $status, $procurement_officer_id]);
        $po_id = $db->lastInsertId();
        
        $items = $db->prepare("SELECT * FROM quotation_items WHERE quotation_id = ?");
        $items->execute([$quotation_id]);
        while ($item = $items->fetch()) {
            $stmt = $db->prepare("INSERT INTO po_items (po_id, item_description, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$po_id, $item['item_description'], $item['quantity'], $item['unit_price'], $item['total']]);
        }
        
        $db->prepare("UPDATE requisitions SET status = 'completed' WHERE id = ?")->execute([$requisition_id]);
        
        $db->commit();
        $success_message = "PO #$po_number created successfully!";
        logActivity("Created PO #$po_number");
        header('Location: ' . BASE_PATH . 'modules/procurement/purchase_orders.php?success=' . urlencode("PO #$po_number created!"));
        exit();
    } catch(Exception $e) {
        $db->rollBack();
        $error_message = "Error: " . $e->getMessage();
    }
}

skip_create_po:

// Approve PO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_po'])) {
    require_permission('approve_purchase_orders');
    $po_id = (int)$_POST['po_id'];
    $action = $_POST['action'];
    $status = $action === 'approve' ? 'approved' : 'cancelled';
    
    $stmt = $db->prepare("UPDATE purchase_orders SET status = ?, approved_at = NOW(), finance_approver_id = ? WHERE id = ?");
    $stmt->execute([$status, $_SESSION['user_id'], $po_id]);
    $success_message = "PO " . ($action === 'approve' ? 'approved' : 'rejected') . "!";
    logActivity("PO #{$po_id} {$action}d by finance");
    header('Location: ' . BASE_PATH . 'modules/procurement/purchase_orders.php?success=' . urlencode("PO {$action}d!"));
    exit();
}

// Fetch data
$pos = $db->query("
    SELECT po.*, s.company_name, req.req_number, u.full_name as officer 
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.id
    JOIN requisitions req ON po.requisition_id = req.id
    JOIN users u ON po.procurement_officer_id = u.id
    ORDER BY po.created_at DESC
")->fetchAll();

$acceptedQuotations = $db->query("
    SELECT q.*, s.company_name, req.id as req_id 
    FROM quotations q
    JOIN suppliers s ON q.supplier_id = s.id
    JOIN rfqs rf ON q.rfq_id = rf.id
    JOIN requisitions req ON rf.requisition_id = req.id
    WHERE q.status = 'accepted' AND q.id NOT IN (SELECT quotation_id FROM purchase_orders WHERE quotation_id IS NOT NULL)
")->fetchAll();

$suppliers = $db->query("SELECT id, company_name FROM suppliers WHERE is_active = 1")->fetchAll();

// Get flash messages
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
    <title>Purchase Orders</title>
    <link href="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css?v=20260913">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <h4 class="mb-4"><i class="fas fa-file-pdf me-2 text-primary"></i> Purchase Orders</h4>
            
            <?php if ($success_message): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo $success_message; ?>"></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo $error_message; ?>"></div>
            <?php endif; ?>
            
            <?php if (hasPermission('manage_pos') && !empty($acceptedQuotations)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i> Create Purchase Order</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                                <?php echo csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Select Quotation</label>
                                <select name="quotation_id" class="form-select" required onchange="loadQuotationData(this)">
                                    <option value="">Select quotation</option>
                                    <?php foreach($acceptedQuotations as $q): ?>
                                    <option value="<?php echo $q['id']; ?>" data-supplier="<?php echo $q['supplier_id']; ?>" data-req="<?php echo $q['req_id']; ?>" data-total="<?php echo $q['total_amount']; ?>">
                                        <?php echo $q['company_name']; ?> - ₱<?php echo number_format($q['total_amount'], 2); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="hidden" name="supplier_id" id="supplier_id">
                            <input type="hidden" name="requisition_id" id="requisition_id">
                            <input type="hidden" name="total_amount" id="total_amount">
                            <div class="col-md-3">
                                <label class="form-label">Tax (₱)</label>
                                <input type="number" step="0.01" name="tax" class="form-control" value="0" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Shipping (₱)</label>
                                <input type="number" step="0.01" name="shipping_cost" class="form-control" value="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payment Terms</label>
                                <input type="text" name="payment_terms" class="form-control" placeholder="e.g. Net 30" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Delivery Date</label>
                                <input type="date" name="delivery_date" class="form-control" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" name="create_po" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Create PO
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i> Purchase Orders List</h5>
                    <small class="text-muted">Total: <?php echo count($pos); ?></small>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>PO #</th>
                                <th>Supplier</th>
                                <th>Requisition</th>
                                <th>Grand Total</th>
                                <th>Payment Terms</th>
                                <th>Delivery Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pos)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No purchase orders found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($pos as $po): ?>
                            <tr>
                                <td><strong><?php echo $po['po_number']; ?></strong></td>
                                <td><?php echo $po['company_name']; ?></td>
                                <td><?php echo $po['req_number']; ?></td>
                                <td>₱<?php echo number_format($po['grand_total'], 2); ?></td>
                                <td><?php echo $po['payment_terms']; ?></td>
                                <td><?php echo $po['delivery_date']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $po['status'] === 'closed' ? 'success' : ($po['status'] === 'approved' ? 'primary' : ($po['status'] === 'pending_approval' ? 'warning' : 'secondary')); ?>">
                                        <?php echo str_replace('_', ' ', $po['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($po['status'] === 'pending_approval' && hasPermission('approve_purchase_orders')): ?>
                                    <button class="btn btn-sm btn-primary" onclick="showPOApproval(<?php echo $po['id']; ?>)">
                                        <i class="fas fa-check-circle me-1"></i> Approve
                                    </button>
                                    <?php endif; ?>
                                    <a href="po_view.php?id=<?php echo $po['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- PO Approval Modal -->
    <div class="modal fade" id="poApprovalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2 text-primary"></i> Finance Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                                <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" name="po_id" id="po_id">
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <select name="action" class="form-select" required>
                                <option value="approve">✅ Approve</option>
                                <option value="reject">❌ Reject</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="approve_po" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function loadQuotationData(select) {
            const option = select.options[select.selectedIndex];
            document.getElementById('supplier_id').value = option.dataset.supplier || '';
            document.getElementById('requisition_id').value = option.dataset.req || '';
            document.getElementById('total_amount').value = option.dataset.total || 0;
        }
        
        function showPOApproval(id) {
            document.getElementById('po_id').value = id;
            new bootstrap.Modal(document.getElementById('poApprovalModal')).show();
        }
    </script>
    
    <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
</body>
</html>