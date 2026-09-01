<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_pos')) {
    logActivity("Access denied: goods_receipt.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

// ============================================
// GET PO DETAILS
// ============================================
if (isset($_GET['po_id'])) {
    $po_id = (int)$_GET['po_id'];
    $po = $db->prepare("
        SELECT po.*, s.company_name, u.full_name as officer 
        FROM purchase_orders po
        JOIN suppliers s ON po.supplier_id = s.id
        JOIN users u ON po.procurement_officer_id = u.id
        WHERE po.id = ?
    ");
    $po->execute([$po_id]);
    $poData = $po->fetch();
    
    if ($poData) {
        $items = $db->prepare("SELECT * FROM po_items WHERE po_id = ?");
        $items->execute([$po_id]);
        $poItems = $items->fetchAll();
    }
}

// ============================================
// PROCESS GOODS RECEIPT - DIRECT SUBMIT (NO AJAX)
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['receive_goods'])) {
    
    $po_id = isset($_POST['po_id']) ? (int)$_POST['po_id'] : 0;
    
    if ($po_id <= 0) {
        $error_message = "Invalid PO ID!";
    } else {
        try {
            $db->beginTransaction();
            
            $grn_number = 'GRN-' . date('Ymd') . '-' . rand(1000, 9999);
            $received_by = $_SESSION['user_id'];
            $delivery_note = sanitize($_POST['delivery_note'] ?? '');
            $notes = sanitize($_POST['notes'] ?? '');
            
            // Insert GRN
            $stmt = $db->prepare("INSERT INTO goods_receipts (grn_number, po_id, received_by, delivery_note, notes, status) 
                                   VALUES (?, ?, ?, ?, ?, 'completed')");
            $stmt->execute([$grn_number, $po_id, $received_by, $delivery_note, $notes]);
            $grn_id = $db->lastInsertId();
            
            // Get PO items
            $items = $db->prepare("SELECT * FROM po_items WHERE po_id = ?");
            $items->execute([$po_id]);
            $poItems = $items->fetchAll();
            
            foreach ($poItems as $item) {
                $received_qty = isset($_POST['items'][$item['id']]['received']) ? (int)$_POST['items'][$item['id']]['received'] : $item['quantity'];
                $accepted_qty = isset($_POST['items'][$item['id']]['accepted']) ? (int)$_POST['items'][$item['id']]['accepted'] : $received_qty;
                $rejected_qty = isset($_POST['items'][$item['id']]['rejected']) ? (int)$_POST['items'][$item['id']]['rejected'] : 0;
                $remarks = sanitize($_POST['items'][$item['id']]['remarks'] ?? '');
                
                $stmt = $db->prepare("INSERT INTO gr_items (goods_receipt_id, po_item_id, received_quantity, accepted_quantity, rejected_quantity, remarks) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$grn_id, $item['id'], $received_qty, $accepted_qty, $rejected_qty, $remarks]);
                
                $stmt = $db->prepare("UPDATE po_items SET received_quantity = received_quantity + ? WHERE id = ?");
                $stmt->execute([$accepted_qty, $item['id']]);
            }
            
            $db->prepare("UPDATE purchase_orders SET status = 'fulfilled' WHERE id = ?")->execute([$po_id]);
            
            $db->commit();
            $success_message = "Goods received successfully! GRN #{$grn_number}";
            logActivity("Received goods for PO #{$po_id} - GRN #{$grn_number}");
            
            header('Location: ' . BASE_PATH . 'modules/procurement/goods_receipt.php?success=' . urlencode("Goods received! GRN #$grn_number"));
            exit();
            
        } catch(Exception $e) {
            $db->rollBack();
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// ============================================
// FETCH DATA
// ============================================
$pendingPos = $db->query("
    SELECT po.*, s.company_name 
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.id
    WHERE po.status = 'approved'
    ORDER BY po.created_at DESC
")->fetchAll();

$grns = $db->query("
    SELECT gr.*, po.po_number, s.company_name, u.full_name as receiver
    FROM goods_receipts gr
    JOIN purchase_orders po ON gr.po_id = po.id
    JOIN suppliers s ON po.supplier_id = s.id
    JOIN users u ON gr.received_by = u.id
    ORDER BY gr.created_at DESC
    LIMIT 50
")->fetchAll();

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
    <title>Goods Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <h4 class="mb-4"><i class="fas fa-warehouse me-2 text-primary"></i> Goods Receipt</h4>
            
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
            
            <?php if (!isset($poData)): ?>
            <!-- LIST OF PENDING POS -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-truck me-2 text-primary"></i> Receive Purchase Order</h5>
                </div>
                <div class="card-body table-responsive">
                    <?php if (empty($pendingPos)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i> 
                            No pending POs for receiving. 
                            <br><small>Make sure you have an <strong>approved PO</strong> first.</small>
                        </div>
                    <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>PO #</th>
                                <th>Supplier</th>
                                <th>Total Amount</th>
                                <th>Delivery Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pendingPos as $po): ?>
                            <tr>
                                <td><strong><?php echo $po['po_number']; ?></strong></td>
                                <td><?php echo $po['company_name']; ?></td>
                                <td>₱<?php echo number_format($po['grand_total'], 2); ?></td>
                                <td><?php echo $po['delivery_date']; ?></td>
                                <td>
                                    <a href="?po_id=<?php echo $po['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-arrow-right me-1"></i> Receive
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- RECEIVE FORM -->
            <?php if (isset($poData)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">
                        <i class="fas fa-truck me-2 text-primary"></i> 
                        Receiving PO: <?php echo $poData['po_number']; ?>
                        <span class="badge bg-secondary ms-2"><?php echo $poData['company_name']; ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- SIMPLE FORM - DIRECT SUBMIT -->
                    <form method="POST">
                        <input type="hidden" name="po_id" value="<?php echo $poData['id']; ?>">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Delivery Note / ASN</label>
                                <input type="text" name="delivery_note" class="form-control" placeholder="Enter delivery note number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Ordered</th>
                                        <th>Received</th>
                                        <th>Accepted</th>
                                        <th>Rejected</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($poItems as $item): ?>
                                    <tr>
                                        <td><strong><?php echo $item['item_description']; ?></strong></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>
                                            <input type="number" name="items[<?php echo $item['id']; ?>][received]" 
                                                   class="form-control form-control-sm received-qty" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   max="<?php echo $item['quantity']; ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[<?php echo $item['id']; ?>][accepted]" 
                                                   class="form-control form-control-sm accepted-qty" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   max="<?php echo $item['quantity']; ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[<?php echo $item['id']; ?>][rejected]" 
                                                   class="form-control form-control-sm rejected-qty" 
                                                   value="0" max="<?php echo $item['quantity']; ?>" required>
                                        </td>
                                        <td>
                                            <input type="text" name="items[<?php echo $item['id']; ?>][remarks]" 
                                                   class="form-control form-control-sm" placeholder="Remarks">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" name="receive_goods" class="btn btn-success" id="receiveBtn">
                                <i class="fas fa-check-circle me-1"></i> Confirm Receipt
                            </button>
                            <a href="goods_receipt.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- RECENT GRNS -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i> Recent Goods Receipts</h5>
                </div>
                <div class="card-body table-responsive">
                    <?php if (empty($grns)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No goods receipts found.
                        </div>
                    <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>GRN #</th>
                                <th>PO #</th>
                                <th>Supplier</th>
                                <th>Received By</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($grns as $grn): ?>
                            <tr>
                                <td><strong><?php echo $grn['grn_number']; ?></strong></td>
                                <td><?php echo $grn['po_number']; ?></td>
                                <td><?php echo $grn['company_name']; ?></td>
                                <td><?php echo $grn['receiver']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($grn['receipt_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $grn['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($grn['status']); ?>
                                    </span>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>
    
    <script>
        // Auto-calculate accepted/rejected
        document.querySelectorAll('.received-qty').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('tr');
                const received = parseFloat(this.value) || 0;
                const rejected = parseFloat(row.querySelector('.rejected-qty').value) || 0;
                row.querySelector('.accepted-qty').value = Math.max(0, received - rejected);
            });
        });
        
        document.querySelectorAll('.rejected-qty').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('tr');
                const rejected = parseFloat(this.value) || 0;
                const received = parseFloat(row.querySelector('.received-qty').value) || 0;
                row.querySelector('.accepted-qty').value = Math.max(0, received - rejected);
            });
        });
    </script>
</body>
</html>