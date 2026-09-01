<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_budget')) {
    logActivity("Access denied: budget.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_budget'])) {
    $budget_code = sanitize($_POST['budget_code']);
    $department = sanitize($_POST['department']);
    $category = sanitize($_POST['category']);
    $allocated_amount = (float)$_POST['allocated_amount'];
    $fiscal_year = (int)$_POST['fiscal_year'];
    
    try {
        $stmt = $db->prepare("INSERT INTO budgets (budget_code, department, category, allocated_amount, fiscal_year) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$budget_code, $department, $category, $allocated_amount, $fiscal_year]);
        $success_message = "Budget #{$budget_code} created!";
        logActivity("Created budget #{$budget_code} - ₱" . number_format($allocated_amount, 2));
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $db->prepare("DELETE FROM budgets WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

$budgets = $db->query("SELECT * FROM budgets ORDER BY fiscal_year DESC, department")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COALESCE(SUM(allocated_amount), 0) FROM budgets WHERE status = 'active'")->fetchColumn(),
    'used' => $db->query("SELECT COALESCE(SUM(used_amount), 0) FROM budgets WHERE status = 'active'")->fetchColumn(),
    'remaining' => 0,
];

$stats['remaining'] = $stats['total'] - $stats['used'];

$departments = ['IT', 'HR', 'Finance', 'Operations', 'Sales', 'Marketing', 'Procurement', 'Warehouse'];
$categories = ['Salaries', 'Equipment', 'Supplies', 'Training', 'Travel', 'Maintenance', 'Software', 'Other'];
$years = [date('Y'), date('Y')+1, date('Y')+2];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management</title>
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
                <h4 class="mb-0"><i class="fas fa-coins me-2 text-success"></i> Budget Management</h4>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                    <i class="fas fa-plus-circle me-1"></i> Create Budget
                </button>
            </div>
            
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
                            <h6 class="text-muted">Total Budget</h6>
                            <h3 class="fw-bold text-primary">₱<?php echo number_format($stats['total'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Used</h6>
                            <h3 class="fw-bold text-warning">₱<?php echo number_format($stats['used'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Remaining</h6>
                            <h3 class="fw-bold text-success">₱<?php echo number_format($stats['remaining'], 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Budget Code</th>
                                <th>Department</th>
                                <th>Category</th>
                                <th>Allocated</th>
                                <th>Used</th>
                                <th>Remaining</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($budgets as $b): ?>
                            <tr id="budget-row-<?php echo $b['id']; ?>">
                                <td><strong><?php echo $b['budget_code']; ?></strong></td>
                                <td><?php echo $b['department']; ?></td>
                                <td><?php echo $b['category']; ?></td>
                                <td>₱<?php echo number_format($b['allocated_amount'], 2); ?></td>
                                <td>₱<?php echo number_format($b['used_amount'], 2); ?></td>
                                <td>
                                    <?php 
                                    $remaining = $b['allocated_amount'] - $b['used_amount'];
                                    $percent = $b['allocated_amount'] > 0 ? ($b['used_amount'] / $b['allocated_amount']) * 100 : 0;
                                    ?>
                                    <span class="badge bg-<?php echo $percent < 70 ? 'success' : ($percent < 90 ? 'warning' : 'danger'); ?>">
                                        ₱<?php echo number_format($remaining, 2); ?>
                                    </span>
                                </td>
                                <td><?php echo $b['fiscal_year']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $b['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($b['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-budget" data-id="<?php echo $b['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addBudgetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-coins me-2 text-success"></i> Create Budget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Budget Code</label>
                                <input type="text" name="budget_code" class="form-control" required placeholder="BUD-2024-001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fiscal Year</label>
                                <select name="fiscal_year" class="form-select" required>
                                    <?php foreach($years as $year): ?>
                                    <option value="<?php echo $year; ?>" <?php echo $year == date('Y') ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <select name="department" class="form-select" required>
                                    <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Allocated Amount (₱)</label>
                                <input type="number" step="0.01" name="allocated_amount" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_budget" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Create Budget
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
        document.querySelectorAll('.delete-budget').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                confirmDelete('Delete this budget?', function() {
                    fetch(`?delete=${id}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                showSuccess('Budget deleted!');
                                document.getElementById(`budget-row-${id}`).remove();
                            }
                        });
                });
            });
        });
    </script>
</body>
</html>