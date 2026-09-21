<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_sales')) {
    logActivity("Access denied: customers.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();
verify_csrf();

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_customer'])) {
    $customer_code = sanitize($_POST['customer_code']);
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $company = sanitize($_POST['company']);
    $tax_id = sanitize($_POST['tax_id']);
    
    try {
        $stmt = $db->prepare("INSERT INTO customers (customer_code, first_name, last_name, email, phone, address, company, tax_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customer_code, $first_name, $last_name, $email, $phone, $address, $company, $tax_id]);
        $success_message = "Customer <strong>{$first_name} {$last_name}</strong> added successfully!";
        logActivity("Added customer: {$first_name} {$last_name}");
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_customer'])) {
    header('Content-Type: application/json');
    try {
        require_permission('view_sales');
        $id=(int)($_POST['customer_id']??0);
        if($id<=0) throw new Exception('Invalid customer.');
        $db->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
        echo json_encode(['success'=>true,'message'=>'Customer deleted successfully!']);
    } catch(Throwable $e) { echo json_encode(['success'=>false,'message'=>$e->getMessage()]); }
    exit();
}

$customers = $db->query("SELECT * FROM customers ORDER BY created_at DESC")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM customers")->fetchColumn(),
    'with_company' => $db->query("SELECT COUNT(*) FROM customers WHERE company IS NOT NULL AND company != ''")->fetchColumn(),
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
    <title>Customer Management</title>
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
                <h4 class="mb-0"><i class="fas fa-users me-2 text-primary"></i> Customer Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                    <i class="fas fa-plus-circle me-1"></i> Add Customer
                </button>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Customers</h6>
                            <h3 class="fw-bold text-primary"><?php echo $stats['total']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">With Company</h6>
                            <h3 class="fw-bold text-info"><?php echo $stats['with_company']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover" id="customerTable">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Company</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($customers as $c): ?>
                            <tr id="customer-row-<?php echo $c['id']; ?>">
                                <td><span class="badge bg-secondary"><?php echo $c['customer_code']; ?></span></td>
                                <td><strong><?php echo $c['first_name'] . ' ' . $c['last_name']; ?></strong></td>
                                <td><?php echo $c['email']; ?></td>
                                <td><?php echo $c['phone']; ?></td>
                                <td><?php echo $c['company'] ?: '-'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-customer" data-id="<?php echo $c['id']; ?>" data-name="<?php echo $c['first_name'] . ' ' . $c['last_name']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <a href="<?php echo BASE_PATH; ?>modules/sales.php?customer=<?php echo $c['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-cash-register"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i> Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
<?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer Code</label>
                                <input type="text" name="customer_code" class="form-control" required placeholder="CUST-001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company</label>
                                <input type="text" name="company" class="form-control" placeholder="Company name (optional)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Tax ID (TIN)</label>
                                <input type="text" name="tax_id" class="form-control" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_customer" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Customer
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
        document.querySelectorAll('.delete-customer').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                
                confirmDelete(`Customer "${name}" will be permanently deleted!`, function() {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    fetch('', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({delete_customer:'1',customer_id:id,csrf_token:'<?php echo h(csrf_token()); ?>'})})
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showSuccess(data.message);
                                document.getElementById(`customer-row-${id}`).remove();
                            } else {
                                showError(data.message);
                            }
                        })
                        .catch(() => showError('Error deleting customer!'));
                });
            });
        });
    </script>
</body>
</html>