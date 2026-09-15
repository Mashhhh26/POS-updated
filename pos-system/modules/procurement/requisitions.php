<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

// SIMPLIFIED VERSION - KOPYA NG TEST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_requisition'])) {
    
    $department = trim($_POST['department'] ?? '');
    $cost_centre = trim($_POST['cost_centre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $estimated_value = (float)($_POST['estimated_value'] ?? 0);
    $urgency = $_POST['urgency'] ?? 'medium';
    $requester_id = $_SESSION['user_id'];
    $req_number = 'REQ-' . date('Ymd') . '-' . rand(1000, 9999);
    
    if (empty($department) || empty($cost_centre) || empty($description) || $estimated_value <= 0) {
        $error_message = "Please fill in all required fields!";
    } else {
        try {
            $db->beginTransaction();
            
            // Insert requisition
            $stmt = $db->prepare("INSERT INTO requisitions (req_number, requester_id, department, cost_centre, description, estimated_value, urgency, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'pending_budget')");
            $stmt->execute([$req_number, $requester_id, $department, $cost_centre, $description, $estimated_value, $urgency]);
            $req_id = $db->lastInsertId();
            
            // Insert items
            $items = $_POST['items'] ?? [];
            $item_count = 0;
            foreach ($items as $item) {
                if (!empty($item['description']) && !empty($item['quantity']) && !empty($item['estimated_price'])) {
                    $total = (float)$item['quantity'] * (float)$item['estimated_price'];
                    $stmt = $db->prepare("INSERT INTO requisition_items (requisition_id, item_description, quantity, unit, estimated_price, total) 
                                           VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$req_id, $item['description'], (int)$item['quantity'], $item['unit'] ?? '', (float)$item['estimated_price'], $total]);
                    $item_count++;
                }
            }
            
            if ($item_count == 0) {
                throw new Exception("Please add at least one item!");
            }
            
            $db->commit();
            $success_message = "Requisition #$req_number created successfully!";
            logActivity("Created requisition #$req_number");
            
            header('Location: ' . BASE_PATH . 'modules/procurement/requisitions.php?success=' . urlencode("Requisition #$req_number created!"));
            exit();
            
        } catch(Exception $e) {
            $db->rollBack();
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// BUDGET APPROVAL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_budget'])) {
    $req_id = (int)$_POST['requisition_id'];
    $action = $_POST['action'];
    $remarks = sanitize($_POST['remarks']);
    
    $status = $action === 'approve' ? 'budget_approved' : 'budget_rejected';
    $stmt = $db->prepare("UPDATE requisitions SET status = ?, finance_remarks = ? WHERE id = ?");
    $stmt->execute([$status, $remarks, $req_id]);
    $success_message = "Budget " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully!";
    header('Location: ' . BASE_PATH . 'modules/procurement/requisitions.php?success=' . urlencode("Budget {$action}d!"));
    exit();
}

// FETCH DATA
$requisitions = $db->query("
    SELECT r.*, u.full_name as requester 
    FROM requisitions r 
    JOIN users u ON r.requester_id = u.id 
    ORDER BY r.created_at DESC
")->fetchAll();

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
    <title>Requisitions</title>
    <link href="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css?v=20260913">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <h4 class="mb-4"><i class="fas fa-file-invoice me-2 text-primary"></i> Requisitions</h4>
            
            <?php if ($success_message): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo $success_message; ?>"></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo $error_message; ?>"></div>
            <?php endif; ?>
            
            <!-- FORM -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i> New Requisition</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                <input type="text" name="department" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cost Centre <span class="text-danger">*</span></label>
                                <input type="text" name="cost_centre" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estimated Value (₱) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="estimated_value" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Urgency</label>
                                <select name="urgency" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <h6>Items <span class="text-danger">*</span></h6>
                                <div id="items-container">
                                    <div class="row g-2 item-row">
                                        <div class="col-4">
                                            <input type="text" name="items[0][description]" class="form-control" placeholder="Item description" required>
                                        </div>
                                        <div class="col-2">
                                            <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" required>
                                        </div>
                                        <div class="col-2">
                                            <input type="text" name="items[0][unit]" class="form-control" placeholder="Unit">
                                        </div>
                                        <div class="col-3">
                                            <input type="number" step="0.01" name="items[0][estimated_price]" class="form-control" placeholder="Price" required>
                                        </div>
                                        <div class="col-1">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.item-row').remove()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addItemRow()">
                                    <i class="fas fa-plus me-1"></i> Add Item
                                </button>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" name="create_requisition" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Requisition
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- LIST -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i> Requisition List</h5>
                    <small>Total: <?php echo count($requisitions); ?></small>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Requester</th>
                                <th>Department</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requisitions)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No requisitions found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($requisitions as $req): ?>
                            <tr>
                                <td><strong><?php echo $req['req_number']; ?></strong></td>
                                <td><?php echo $req['requester']; ?></td>
                                <td><?php echo $req['department']; ?></td>
                                <td>₱<?php echo number_format($req['estimated_value'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $req['status'] === 'budget_approved' ? 'success' : ($req['status'] === 'pending_budget' ? 'warning' : 'secondary'); ?>">
                                        <?php echo str_replace('_', ' ', $req['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                <td>
                                    <?php if ($req['status'] === 'pending_budget' && hasPermission('approve_requisitions')): ?>
                                    <button class="btn btn-sm btn-primary" onclick="showBudgetModal(<?php echo $req['id']; ?>)">
                                        <i class="fas fa-check-circle me-1"></i> Budget Check
                                    </button>
                                    <?php endif; ?>
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

    <!-- Budget Modal -->
    <div class="modal fade" id="budgetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-coins me-2 text-primary"></i> Budget Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="requisition_id" id="req_id">
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <select name="action" class="form-select" required>
                                <option value="approve">Approve</option>
                                <option value="reject">Reject</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="approve_budget" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = 1;
        
        function addItemRow() {
            const container = document.getElementById('items-container');
            const row = document.createElement('div');
            row.className = 'row g-2 item-row';
            row.innerHTML = `
                <div class="col-4">
                    <input type="text" name="items[${itemIndex}][description]" class="form-control" placeholder="Item description" required>
                </div>
                <div class="col-2">
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Qty" required>
                </div>
                <div class="col-2">
                    <input type="text" name="items[${itemIndex}][unit]" class="form-control" placeholder="Unit">
                </div>
                <div class="col-3">
                    <input type="number" step="0.01" name="items[${itemIndex}][estimated_price]" class="form-control" placeholder="Price" required>
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.item-row').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
            itemIndex++;
        }
        
        function showBudgetModal(id) {
            document.getElementById('req_id').value = id;
            new bootstrap.Modal(document.getElementById('budgetModal')).show();
        }
    </script>
    
    <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
</body>
</html>