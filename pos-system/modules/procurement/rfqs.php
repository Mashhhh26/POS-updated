<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_rfqs')) {
    logActivity("Access denied: rfqs.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

// ============================================
// CREATE RFQ
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_rfq'])) {
    $rfq_number = 'RFQ-' . date('Ymd') . '-' . rand(1000, 9999);
    $requisition_id = (int)$_POST['requisition_id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $submission_deadline = $_POST['submission_deadline'];
    $procurement_officer_id = $_SESSION['user_id'];
    $suppliers = $_POST['suppliers'] ?? [];
    
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("INSERT INTO rfqs (rfq_number, requisition_id, title, description, submission_deadline, status, procurement_officer_id) 
                               VALUES (?, ?, ?, ?, ?, 'sent', ?)");
        $stmt->execute([$rfq_number, $requisition_id, $title, $description, $submission_deadline, $procurement_officer_id]);
        $rfq_id = $db->lastInsertId();
        
        $db->prepare("UPDATE requisitions SET status = 'sourcing' WHERE id = ?")->execute([$requisition_id]);
        
        foreach ($suppliers as $supplier_id) {
            $stmt = $db->prepare("INSERT INTO rfq_suppliers (rfq_id, supplier_id, status) VALUES (?, ?, 'invited')");
            $stmt->execute([$rfq_id, $supplier_id]);
        }
        
        $db->commit();
        $success_message = "RFQ #$rfq_number created and sent to suppliers!";
        logActivity("Created RFQ #$rfq_number");
        header('Location: ' . BASE_PATH . 'modules/procurement/rfqs.php?success=' . urlencode("RFQ #$rfq_number created!"));
        exit();
    } catch(Exception $e) {
        $db->rollBack();
        $error_message = "Error: " . $e->getMessage();
    }
}

// ============================================
// ADD QUOTATION
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_quotation'])) {
    $rfq_id = (int)$_POST['rfq_id'];
    $supplier_id = (int)$_POST['supplier_id'];
    $quotation_number = 'QUOT-' . date('Ymd') . '-' . rand(1000, 9999);
    $total_amount = (float)$_POST['total_amount'];
    $delivery_lead_time = (int)$_POST['delivery_lead_time'];
    $payment_terms = sanitize($_POST['payment_terms']);
    $validity_days = (int)$_POST['validity_days'];
    $notes = sanitize($_POST['notes']);
    
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("INSERT INTO quotations (rfq_id, supplier_id, quotation_number, total_amount, delivery_lead_time, payment_terms, validity_days, status, notes) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'received', ?)");
        $stmt->execute([$rfq_id, $supplier_id, $quotation_number, $total_amount, $delivery_lead_time, $payment_terms, $validity_days, $notes]);
        $quotation_id = $db->lastInsertId();
        
        $db->prepare("UPDATE rfq_suppliers SET status = 'responded', responded_at = NOW() WHERE rfq_id = ? AND supplier_id = ?")
           ->execute([$rfq_id, $supplier_id]);
        
        $items = $_POST['items'] ?? [];
        foreach ($items as $item) {
            if (!empty($item['description']) && !empty($item['quantity']) && !empty($item['unit_price'])) {
                $total = (float)$item['quantity'] * (float)$item['unit_price'];
                $stmt = $db->prepare("INSERT INTO quotation_items (quotation_id, item_description, quantity, unit_price, total, lead_time, remarks) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$quotation_id, $item['description'], (int)$item['quantity'], (float)$item['unit_price'], $total, $item['lead_time'] ?? 0, $item['remarks'] ?? '']);
            }
        }
        
        $db->commit();
        $success_message = "Quotation #$quotation_number from supplier added!";
        logActivity("Added quotation #$quotation_number");
        header('Location: ' . BASE_PATH . 'modules/procurement/rfqs.php?success=' . urlencode("Quotation added!"));
        exit();
    } catch(Exception $e) {
        $db->rollBack();
        $error_message = "Error: " . $e->getMessage();
    }
}

// ============================================
// EVALUATE QUOTATION - SIMPLIFIED
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['evaluate_quotation'])) {
    
    // Debug: Log the POST data
    error_log("=== EVALUATE QUOTATION POST DATA ===");
    error_log(print_r($_POST, true));
    
    $quotation_id = isset($_POST['quotation_id']) ? (int)$_POST['quotation_id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $technical_score = isset($_POST['technical_score']) ? (float)$_POST['technical_score'] : 0;
    $commercial_score = isset($_POST['commercial_score']) ? (float)$_POST['commercial_score'] : 0;
    
    // Validate
    if ($quotation_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid quotation ID!']);
        exit();
    }
    
    if (empty($status) || !in_array($status, ['accepted', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status!']);
        exit();
    }
    
    try {
        // Update quotation
        $stmt = $db->prepare("UPDATE quotations SET status = ?, technical_compliance = ?, commercial_score = ? WHERE id = ?");
        $result = $stmt->execute([$status, $technical_score, $commercial_score, $quotation_id]);
        
        if (!$result) {
            throw new Exception("Failed to update quotation!");
        }
        
        // If accepted, update rfq status
        if ($status === 'accepted') {
            $quotation = $db->prepare("SELECT rfq_id FROM quotations WHERE id = ?");
            $quotation->execute([$quotation_id]);
            $rfq_id = $quotation->fetchColumn();
            
            if ($rfq_id) {
                $db->prepare("UPDATE rfqs SET status = 'awarded' WHERE id = ?")->execute([$rfq_id]);
                error_log("RFQ #{$rfq_id} status updated to 'awarded'");
            }
        }
        
        logActivity("Evaluated quotation ID: {$quotation_id} - Status: {$status}");
        
        // Return success response
        echo json_encode(['success' => true, 'message' => 'Quotation evaluated successfully!']);
        exit();
        
    } catch(Exception $e) {
        error_log("EVALUATION ERROR: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}

// ============================================
// FETCH DATA
// ============================================
$requisitions = $db->query("SELECT id, req_number, description FROM requisitions WHERE status IN ('budget_approved', 'sourcing')")->fetchAll();
$suppliers = $db->query("SELECT id, company_name FROM suppliers WHERE is_active = 1")->fetchAll();

$rfqs = $db->query("
    SELECT r.*, req.req_number, u.full_name as officer 
    FROM rfqs r 
    JOIN requisitions req ON r.requisition_id = req.id
    JOIN users u ON r.procurement_officer_id = u.id
    ORDER BY r.created_at DESC
")->fetchAll();

$quotations = $db->query("
    SELECT q.*, s.company_name, rf.rfq_number 
    FROM quotations q 
    JOIN suppliers s ON q.supplier_id = s.id
    JOIN rfqs rf ON q.rfq_id = rf.id
    WHERE q.status = 'received'
    ORDER BY q.submitted_at DESC
")->fetchAll();

$rfq_details = null;
if (isset($_GET['add_quotation'])) {
    $rfq_id = (int)$_GET['add_quotation'];
    $rfq_details = $db->prepare("SELECT * FROM rfqs WHERE id = ?");
    $rfq_details->execute([$rfq_id]);
    $rfq_details = $rfq_details->fetch();
    
    if ($rfq_details) {
        $req_items = $db->prepare("SELECT * FROM requisition_items WHERE requisition_id = ?");
        $req_items->execute([$rfq_details['requisition_id']]);
        $rfq_items = $req_items->fetchAll();
    }
}

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
    <title>RFQ Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <h4 class="mb-4"><i class="fas fa-file-signature me-2 text-primary"></i> Request for Quotation</h4>
            
            <?php if ($success_message): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo $success_message; ?>"></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo $error_message; ?>"></div>
            <?php endif; ?>
            
            <!-- CREATE RFQ -->
            <?php if (hasPermission('manage_rfqs') && !isset($_GET['add_quotation'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i> Create RFQ</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Requisition</label>
                                <select name="requisition_id" class="form-select" required>
                                    <option value="">Select approved requisition</option>
                                    <?php foreach($requisitions as $req): ?>
                                    <option value="<?php echo $req['id']; ?>"><?php echo $req['req_number']; ?> - <?php echo substr($req['description'], 0, 50); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Submission Deadline</label>
                                <input type="datetime-local" name="submission_deadline" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invite Suppliers</label>
                                <div class="border p-2 rounded" style="max-height:150px;overflow-y:auto;">
                                    <?php foreach($suppliers as $sup): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="suppliers[]" value="<?php echo $sup['id']; ?>" id="sup-<?php echo $sup['id']; ?>">
                                        <label class="form-check-label" for="sup-<?php echo $sup['id']; ?>"><?php echo $sup['company_name']; ?></label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="create_rfq" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send RFQ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- ADD QUOTATION -->
            <?php if (isset($_GET['add_quotation']) && $rfq_details): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2 text-success"></i> Add Supplier Quotation</h5>
                    <small class="text-muted">RFQ: <?php echo $rfq_details['rfq_number']; ?> - <?php echo $rfq_details['title']; ?></small>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="rfq_id" value="<?php echo $rfq_details['id']; ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-select" required>
                                    <option value="">Select Supplier</option>
                                    <?php foreach($suppliers as $sup): ?>
                                    <option value="<?php echo $sup['id']; ?>"><?php echo $sup['company_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quotation Number</label>
                                <input type="text" name="quotation_number" class="form-control" value="QUOT-<?php echo date('Ymd') . '-' . rand(1000, 9999); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Amount (₱) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="total_amount" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Delivery Lead Time (days) <span class="text-danger">*</span></label>
                                <input type="number" name="delivery_lead_time" class="form-control" required value="7">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Validity (days)</label>
                                <input type="number" name="validity_days" class="form-control" value="30">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Terms</label>
                                <select name="payment_terms" class="form-select">
                                    <option value="Net 30">Net 30</option>
                                    <option value="Net 45">Net 45</option>
                                    <option value="Net 60">Net 60</option>
                                    <option value="COD">COD</option>
                                    <option value="50% Advance">50% Advance</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="Additional notes">
                            </div>
                            
                            <div class="col-12">
                                <h6 class="mt-2">Quotation Items</h6>
                                <div id="quotation-items-container">
                                    <?php if (isset($rfq_items) && !empty($rfq_items)): ?>
                                    <?php foreach($rfq_items as $index => $item): ?>
                                    <div class="row g-2 item-row mb-2">
                                        <div class="col-4">
                                            <input type="text" name="items[<?php echo $index; ?>][description]" class="form-control" value="<?php echo htmlspecialchars($item['item_description']); ?>" readonly>
                                        </div>
                                        <div class="col-2">
                                            <input type="number" name="items[<?php echo $index; ?>][quantity]" class="form-control" value="<?php echo $item['quantity']; ?>" readonly>
                                        </div>
                                        <div class="col-3">
                                            <input type="number" step="0.01" name="items[<?php echo $index; ?>][unit_price]" class="form-control" placeholder="Unit Price" required>
                                        </div>
                                        <div class="col-2">
                                            <input type="number" name="items[<?php echo $index; ?>][lead_time]" class="form-control" placeholder="Lead Time" value="7">
                                        </div>
                                        <div class="col-1">
                                            <input type="hidden" name="items[<?php echo $index; ?>][remarks]" value="">
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <div class="alert alert-warning">No items found in requisition.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" name="add_quotation" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Submit Quotation
                                </button>
                                <a href="<?php echo BASE_PATH; ?>modules/procurement/rfqs.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- RFQ LIST -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i> RFQ List</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>RFQ #</th>
                                <th>Requisition</th>
                                <th>Title</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($rfqs as $rfq): ?>
                            <tr>
                                <td><strong><?php echo $rfq['rfq_number']; ?></strong></td>
                                <td><?php echo $rfq['req_number']; ?></td>
                                <td><?php echo $rfq['title']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($rfq['submission_deadline'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $rfq['status'] === 'sent' ? 'primary' : ($rfq['status'] === 'evaluating' ? 'warning' : 'secondary'); ?>">
                                        <?php echo $rfq['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?add_quotation=<?php echo $rfq['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-file-invoice me-1"></i> Add Quotation
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- QUOTATIONS RECEIVED -->
            <?php if (!empty($quotations)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i> Supplier Quotations Received</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>RFQ #</th>
                                <th>Supplier</th>
                                <th>Total Amount</th>
                                <th>Lead Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($quotations as $q): ?>
                            <tr>
                                <td><?php echo $q['rfq_number']; ?></td>
                                <td><?php echo $q['company_name']; ?></td>
                                <td>₱<?php echo number_format($q['total_amount'], 2); ?></td>
                                <td><?php echo $q['delivery_lead_time']; ?> days</td>
                                <td>
                                    <span class="badge bg-<?php echo $q['status'] === 'received' ? 'warning' : 'success'; ?>">
                                        <?php echo $q['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="showEvaluationModal(<?php echo $q['id']; ?>)">
                                        <i class="fas fa-check-circle me-1"></i> Evaluate
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Evaluation Modal -->
    <div class="modal fade" id="evalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2 text-primary"></i> Evaluate Quotation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="evalForm">
                    <div class="modal-body">
                        <input type="hidden" name="quotation_id" id="quotation_id">
                        <div class="mb-3">
                            <label class="form-label">Technical Compliance (0-100)</label>
                            <input type="number" name="technical_score" step="0.01" min="0" max="100" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Commercial Score (0-100)</label>
                            <input type="number" name="commercial_score" step="0.01" min="0" max="100" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Decision</label>
                            <select name="status" class="form-select" required>
                                <option value="accepted">✅ Accept</option>
                                <option value="rejected">❌ Reject</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="evaluate_quotation" class="btn btn-primary" id="evalSubmitBtn">
                            <i class="fas fa-check me-1"></i> Submit Evaluation
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
        function showEvaluationModal(id) {
            document.getElementById('quotation_id').value = id;
            new bootstrap.Modal(document.getElementById('evalModal')).show();
        }
        
        // ============================================
        // SIMPLE EVALUATION SUBMIT - DIRECT FORM
        // ============================================
        document.getElementById('evalForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('evalSubmitBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';
            btn.disabled = true;
            
            // Get form data
            const quotation_id = document.getElementById('quotation_id').value;
            const technical_score = document.querySelector('input[name="technical_score"]').value;
            const commercial_score = document.querySelector('input[name="commercial_score"]').value;
            const status = document.querySelector('select[name="status"]').value;
            
            // Create URL encoded data
            const data = new URLSearchParams();
            data.append('evaluate_quotation', '1');
            data.append('quotation_id', quotation_id);
            data.append('technical_score', technical_score);
            data.append('commercial_score', commercial_score);
            data.append('status', status);
            
            // Send AJAX request
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: data
            })
            .then(response => response.json())
            .then(data => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('evalModal'));
                if (modal) modal.hide();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        confirmButtonColor: '#198754',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Error submitting evaluation!',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error submitting evaluation! Please try again.',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OK'
                });
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>