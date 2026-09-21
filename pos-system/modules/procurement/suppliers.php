<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_suppliers')) {
    logActivity("Access denied: suppliers.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();
verify_csrf();

$success_message = null;
$error_message = null;
$edit_supplier = null;

// ============================================
// HANDLE ADD SUPPLIER
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_supplier'])) {
    require_permission('manage_suppliers');
    $supplier_code = sanitize($_POST['supplier_code']);
    $company_name = sanitize($_POST['company_name']);
    $contact_person = sanitize($_POST['contact_person']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $tax_id = sanitize($_POST['tax_id']);
    $payment_terms = sanitize($_POST['payment_terms']);
    $lead_time_days = (int)$_POST['lead_time_days'];
    
    try {
        $stmt = $db->prepare("INSERT INTO suppliers (supplier_code, company_name, contact_person, email, phone, address, tax_id, payment_terms, lead_time_days) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$supplier_code, $company_name, $contact_person, $email, $phone, $address, $tax_id, $payment_terms, $lead_time_days]);
        $success_message = "Supplier <strong>{$company_name}</strong> added successfully!";
        logActivity("Added supplier: {$company_name}");
        header('Location: ' . BASE_PATH . 'modules/procurement/suppliers.php?success=' . urlencode("Supplier added!"));
        exit();
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// ============================================
// HANDLE EDIT SUPPLIER - FIXED
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_supplier'])) {
    require_permission('manage_suppliers');
    $id = (int)$_POST['supplier_id'];
    $supplier_code = sanitize($_POST['supplier_code']);
    $company_name = sanitize($_POST['company_name']);
    $contact_person = sanitize($_POST['contact_person']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $payment_terms = sanitize($_POST['payment_terms']);
    $lead_time_days = (int)$_POST['lead_time_days'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $db->prepare("UPDATE suppliers SET 
                               supplier_code = ?, company_name = ?, contact_person = ?, email = ?, 
                               phone = ?, address = ?, payment_terms = ?, lead_time_days = ?, is_active = ?
                               WHERE id = ?");
        $stmt->execute([$supplier_code, $company_name, $contact_person, $email, $phone, $address, $payment_terms, $lead_time_days, $is_active, $id]);
        $success_message = "Supplier updated successfully!";
        logActivity("Updated supplier: {$company_name}");
        header('Location: ' . BASE_PATH . 'modules/procurement/suppliers.php?success=' . urlencode("Supplier updated!"));
        exit();
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// ============================================
// HANDLE DELETE SUPPLIER
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_supplier'])) {
    require_permission('manage_suppliers');
    header('Content-Type: application/json');
    $id = (int)($_POST['supplier_id'] ?? 0);
    try {
        if ($id <= 0) {
            throw new Exception('Invalid supplier.');
        }
        $supplier = $db->prepare("SELECT company_name FROM suppliers WHERE id = ?");
        $supplier->execute([$id]);
        $name = $supplier->fetchColumn();
        
        $db->prepare("DELETE FROM suppliers WHERE id = ?")->execute([$id]);
        logActivity("Deleted supplier: {$name}");
        echo json_encode(['success' => true, 'message' => 'Supplier deleted!']);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// ============================================
// FETCH DATA - including edit data
// ============================================
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    $edit_supplier = $stmt->fetch();
}

$suppliers = $db->query("
    SELECT s.*, 
            (SELECT COUNT(*) FROM purchase_orders WHERE supplier_id = s.id) as po_count,
            (SELECT AVG(overall_rating) FROM supplier_performance WHERE supplier_id = s.id) as avg_rating
    FROM suppliers s 
    ORDER BY s.company_name
")->fetchAll();

$paymentTerms = ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'COD', '50% Advance', 'Upon Delivery'];

$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM suppliers")->fetchColumn(),
    'active' => $db->query("SELECT COUNT(*) FROM suppliers WHERE is_active = 1")->fetchColumn(),
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
    <title>Supplier Management</title>
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
                <h4 class="mb-0"><i class="fas fa-truck me-2 text-primary"></i> Supplier Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                    <i class="fas fa-plus-circle me-1"></i> Add Supplier
                </button>
            </div>
            
            <?php if ($success_message): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo $success_message; ?>"></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo $error_message; ?>"></div>
            <?php endif; ?>
            
            <!-- Edit Supplier Modal -->
            <?php if ($edit_supplier): ?>
            <div class="modal fade show" id="editSupplierModal" tabindex="-1" style="display:block; background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit me-2 text-warning"></i> Edit Supplier</h5>
                            <a href="<?php echo BASE_PATH; ?>modules/procurement/suppliers.php" class="btn-close"></a>
                        </div>
                        <form method="POST">
<?php echo csrf_field(); ?>
                            <div class="modal-body">
                                <input type="hidden" name="supplier_id" value="<?php echo $edit_supplier['id']; ?>">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Supplier Code</label>
                                        <input type="text" name="supplier_code" class="form-control" value="<?php echo htmlspecialchars($edit_supplier['supplier_code']); ?>" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($edit_supplier['company_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Contact Person</label>
                                        <input type="text" name="contact_person" class="form-control" value="<?php echo htmlspecialchars($edit_supplier['contact_person']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_supplier['email']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($edit_supplier['phone']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tax ID</label>
                                        <input type="text" name="tax_id" class="form-control" value="<?php echo htmlspecialchars($edit_supplier['tax_id']); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($edit_supplier['address']); ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Payment Terms</label>
                                        <select name="payment_terms" class="form-select" required>
                                            <?php foreach($paymentTerms as $term): ?>
                                            <option value="<?php echo $term; ?>" <?php echo $term == $edit_supplier['payment_terms'] ? 'selected' : ''; ?>><?php echo $term; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Lead Time (days)</label>
                                        <input type="number" name="lead_time_days" class="form-control" value="<?php echo $edit_supplier['lead_time_days']; ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check mt-4">
                                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?php echo $edit_supplier['is_active'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="<?php echo BASE_PATH; ?>modules/procurement/suppliers.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="edit_supplier" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Supplier
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Suppliers</h6>
                            <h3 class="fw-bold text-primary"><?php echo $stats['total']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Active Suppliers</h6>
                            <h3 class="fw-bold text-success"><?php echo $stats['active']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Supplier Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Company</th>
                                <th>Contact</th>
                                <th>Payment Terms</th>
                                <th>Lead Time</th>
                                <th>Rating</th>
                                <th>POs</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($suppliers as $s): ?>
                            <tr id="supplier-row-<?php echo $s['id']; ?>">
                                <td><span class="badge bg-secondary"><?php echo $s['supplier_code']; ?></span></td>
                                <td><strong><?php echo $s['company_name']; ?></strong></td>
                                <td><?php echo $s['contact_person']; ?></td>
                                <td><span class="badge bg-info"><?php echo $s['payment_terms']; ?></span></td>
                                <td><?php echo $s['lead_time_days']; ?> days</td>
                                <td>
                                    <?php if ($s['avg_rating']): ?>
                                    <?php echo number_format($s['avg_rating'], 1); ?> ⭐
                                    <?php else: ?>
                                    <span class="text-muted">No ratings</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $s['po_count']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $s['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $s['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $s['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (hasPermission('manage_suppliers')): ?>
                                    <button class="btn btn-sm btn-danger delete-supplier" data-id="<?php echo $s['id']; ?>" data-name="<?php echo $s['company_name']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-truck me-2 text-primary"></i> Add Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
<?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Supplier Code</label>
                                <input type="text" name="supplier_code" class="form-control" required placeholder="SUP-001">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tax ID</label>
                                <input type="text" name="tax_id" class="form-control" placeholder="TIN">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Terms</label>
                                <select name="payment_terms" class="form-select" required>
                                    <?php foreach($paymentTerms as $term): ?>
                                    <option value="<?php echo $term; ?>"><?php echo $term; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lead Time (days)</label>
                                <input type="number" name="lead_time_days" class="form-control" value="7" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_supplier" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Supplier
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
        document.querySelectorAll('.delete-supplier').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                
                confirmDelete(`Supplier "${name}" will be permanently deleted!`, function() {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    fetch(window.location.pathname, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: new URLSearchParams({delete_supplier: '1', supplier_id: id, csrf_token: csrfToken})
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                showSuccess(data.message);
                                document.getElementById(`supplier-row-${id}`).remove();
                            } else {
                                showError(data.message);
                            }
                        })
                        .catch(() => showError('Error deleting supplier!'));
                });
            });
        });
    </script>
</body>
</html>
